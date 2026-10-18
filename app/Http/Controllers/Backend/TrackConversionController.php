<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TrackConversion;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\TrackConversionTableQuery;
use App\Support\Attribution\AttributionTestMode;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TrackConversionController extends Controller
{
    public function __construct()
    {
        // El permiso del modulo aplica al panel admin (index/tableData/export), NO a store():
        // store() es el endpoint publico POST /track-conversion que llama el sitio sin sesion.
        // Sin este except, un visitante anonimo causaba 500 (->canAccessModule() sobre null).
        $this->middleware('can-access-module:ads')->except('store');
    }

    //
    public function index()
    {
        return view('admin-ui.track-conversion.index');
    }

    /**
     * Resumen para el panel superior de la pantalla: tarjetas y desgloses del rango elegido
     * (range = today | 7 | 30 | 90 | all). "Visitas" = type page_visit, "Conversiones" = el resto.
     */
    public function summary(Request $request)
    {
        $range = (string) $request->input('range', '30');
        $since = match ($range) {
            'today' => now()->startOfDay(),
            '7' => now()->subDays(7)->startOfDay(),
            '90' => now()->subDays(90)->startOfDay(),
            'all' => null,
            default => now()->subDays(30)->startOfDay(),
        };

        // Solo eventos reales: los del Laboratorio de atribucion (is_test) no cuentan en ninguna metrica.
        $base = fn () => TrackConversion::query()->where('is_test', false)->when($since, fn ($q) => $q->where('created_at', '>=', $since));
        $hasGoogleSql = "((gclid IS NOT NULL AND gclid != '') OR (gbraid IS NOT NULL AND gbraid != '') OR (wbraid IS NOT NULL AND wbraid != ''))";
        $isVisitSql = "type = '" . TrackConversion::VISIT_TYPE . "'";
        $nonConversionSql = "'" . implode("','", TrackConversion::nonConversionTypes()) . "'";
        $engagementSql = "'" . implode("','", TrackConversion::ENGAGEMENT_TYPES) . "'";
        // Visitas = page_visit; interacciones = carrito/cotización intermedia; conversiones = el resto.
        $counts = "SUM({$isVisitSql}) AS visits, SUM(type NOT IN ({$nonConversionSql})) AS conversions, SUM(type IN ({$engagementSql})) AS interactions";

        $totals = $base()->selectRaw("{$counts}, COUNT(DISTINCT session_id) AS sessions, "
            . "SUM({$isVisitSql} AND {$hasGoogleSql}) AS google_visits, "
            . "SUM(type NOT IN ({$nonConversionSql}) AND {$hasGoogleSql}) AS google_conversions")->first();

        $visits = (int) ($totals->visits ?? 0);
        $conversions = (int) ($totals->conversions ?? 0);

        // Cotizaciones por sesión: iniciadas, generadas y abandonadas (iniciada sin ninguna generada en la misma sesión).
        $quoteStarted = (int) $base()->where('type', 'quote_start')->whereNotNull('session_id')->distinct()->count('session_id');
        $quoteSubmitted = (int) $base()->where('type', 'quote_submit')->whereNotNull('session_id')->distinct()->count('session_id');
        $quoteAbandoned = (int) $base()->where('type', 'quote_start')->whereNotNull('session_id')
            ->whereNotIn('session_id', TrackConversion::query()->select('session_id')->where('is_test', false)->where('type', 'quote_submit')->whereNotNull('session_id'))
            ->distinct()->count('session_id');

        $byType = $base()
            ->selectRaw("type, COUNT(*) AS total, SUM({$hasGoogleSql}) AS with_google")
            ->groupBy('type')->orderByDesc('total')->get()
            ->map(fn ($r) => ['type' => $r->type, 'total' => (int) $r->total, 'with_google' => (int) $r->with_google]);

        $bySource = $base()
            ->selectRaw("COALESCE(NULLIF(utm_source, ''), '(directo / sin utm)') AS source, COALESCE(NULLIF(utm_medium, ''), '-') AS medium, {$counts}")
            ->groupBy('source', 'medium')->orderByRaw('(visits + conversions) DESC')->limit(10)->get()
            ->map(fn ($r) => ['source' => $r->source, 'medium' => $r->medium, 'visits' => (int) $r->visits, 'conversions' => (int) $r->conversions]);

        $byCampaign = $base()
            ->whereNotNull('utm_campaign')->where('utm_campaign', '!=', '')
            ->selectRaw("utm_campaign AS campaign, {$counts}")
            ->groupBy('utm_campaign')->orderByRaw('(visits + conversions) DESC')->limit(10)->get()
            ->map(fn ($r) => ['campaign' => $r->campaign, 'visits' => (int) $r->visits, 'conversions' => (int) $r->conversions]);

        $byLanding = $base()
            ->whereNotNull('landing_page')->where('landing_page', '!=', '')
            ->selectRaw("landing_page, {$counts}")
            ->groupBy('landing_page')->orderByRaw('(visits + conversions) DESC')->limit(10)->get()
            ->map(fn ($r) => ['landing_page' => $r->landing_page, 'visits' => (int) $r->visits, 'conversions' => (int) $r->conversions]);

        $daily = $base()
            ->selectRaw("DATE(created_at) AS day, {$counts}")
            ->groupBy('day')->orderBy('day')->get()
            ->map(fn ($r) => ['day' => $r->day, 'visits' => (int) $r->visits, 'conversions' => (int) $r->conversions]);

        return response()->json([
            'range' => $range,
            'totals' => [
                'visits' => $visits,
                'conversions' => $conversions,
                'interactions' => (int) ($totals->interactions ?? 0),
                'quote_started' => $quoteStarted,
                'quote_submitted' => $quoteSubmitted,
                'quote_abandoned' => $quoteAbandoned,
                'sessions' => (int) ($totals->sessions ?? 0),
                'google_visits' => (int) ($totals->google_visits ?? 0),
                'google_conversions' => (int) ($totals->google_conversions ?? 0),
                'conversion_rate' => $visits > 0 ? round($conversions / $visits * 100, 1) : null,
            ],
            'by_type' => $byType,
            'by_source' => $bySource,
            'by_campaign' => $byCampaign,
            'by_landing' => $byLanding,
            'daily' => $daily,
        ]);
    }

    /** Detalle de un registro + el resto de eventos de su misma sesión (recorrido del visitante). */
    public function detailsFragment(string $id)
    {
        $record = TrackConversion::findOrFail($id);

        $sessionEvents = $record->session_id
            // Una sesion real solo ve eventos reales y una de prueba solo de prueba (nunca se mezclan).
            ? TrackConversion::where('session_id', $record->session_id)->where('is_test', (bool) $record->is_test)->orderBy('created_at')->orderBy('id')->limit(100)->get()
            : collect();

        return view('admin-ui.track-conversion._details', compact('record', 'sessionEvents'));
    }

    /** JSON data source for the custom admin table (replaces TrackConversionDataTable). */
    public function tableData(Request $request, TrackConversionTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /**
     * Excel/CSV/PDF export of every conversion matching the current filter/search
     * (replaces the Yajra-Buttons export).
     */
    public function export(Request $request, TrackConversionTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Seguimiento de Conversiones',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('seguimiento-conversiones.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "seguimiento-conversiones.{$extension}", $writerType);
    }

    // Note: no bulkAction() here — the old view/controller never exposed a
    // delete/destroy action for this module (read-only tracking log fed by
    // store(), used by the public-facing tracking pixel), so there is nothing
    // for a bulk-delete bar to call.

    /**
     * Endpoint publico POST /track-conversion (sin sesion ni CSRF, ver routes/web.php).
     *
     * Validacion BLANDA: solo `type` es obligatorio (422 JSON si falta o es invalido). Todo lo
     * demas se normaliza o se descarta sin rechazar el evento: es analitica, no vale la pena
     * perderla por un campo mal formado. Sigue aceptando el payload viejo (sin contact_folio ni
     * event_at).
     */
    public function store(Request $request)
    {
        $input = self::payload($request);

        $validator = Validator::make($input, [
            'type' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Modo prueba del Laboratorio de atribucion: se decide SOLO en el servidor. El flag `test` del
        // payload lo manda el navegador, pero solo cuenta si ademas hay una cookie de prueba valida
        // (admin con acceso a 'site'). Un `test` sin cookie valida es trafico ajeno: 204 sin guardar.
        $cookieOk = AttributionTestMode::active($request);
        $flag = self::isTruthy($input['test'] ?? null);
        if ($flag && !$cookieOk) {
            return response()->noContent();
        }
        $isTest = $cookieOk && $flag;

        // Respaldo: un gclid con el prefijo exacto de prueba (sensible a mayusculas; Google nunca emite
        // uno asi) siempre se marca como prueba, aunque la cookie haya expirado entre la visita y el clic.
        $rawGclid = $input['gclid'] ?? null;
        if (is_string($rawGclid) && str_starts_with(trim($rawGclid), TrackConversion::TEST_GCLID_PREFIX)) {
            $isTest = true;
        }

        $now = Carbon::now();
        $data = [
            'type' => (string) $input['type'],
            'utm_source' => $this->cleanString($input['utm_source'] ?? null, 255),
            'utm_medium' => $this->cleanString($input['utm_medium'] ?? null, 255),
            'utm_campaign' => $this->cleanString($input['utm_campaign'] ?? null, 255),
            'utm_term' => $this->cleanString($input['utm_term'] ?? null, 255),
            'utm_content' => $this->cleanString($input['utm_content'] ?? null, 255),
            'fbclid' => $this->cleanString($input['fbclid'] ?? null, 255),
            // URLs largas se guardan truncadas en lugar de rechazarse (limite de cada columna).
            'landing_page' => $this->cleanString($input['landing_page'] ?? null, 255),
            'referrer' => $this->cleanString($input['referrer'] ?? null, 500),
            'page_url' => $this->cleanString($input['page_url'] ?? null, 500),
            'session_id' => $this->cleanString($input['session_id'] ?? null, 64),
            'event_id' => $this->cleanString($input['event_id'] ?? null, 64),
            'captured_at' => $this->parseDate($input['captured_at'] ?? null),
        ];

        // meta: solo claves conocidas, valores escalares y acotados (evita basura o payloads enormes).
        $meta = [];
        $rawMeta = is_array($input['meta'] ?? null) ? $input['meta'] : [];
        foreach (array_intersect_key($rawMeta, array_flip(TrackConversion::META_KEYS)) as $key => $value) {
            if (is_scalar($value) && $value !== '' && $value !== false) {
                $meta[$key] = mb_substr((string) $value, 0, 300);
            }
        }

        // Click ids: dos reglas (gclid estricto, gbraid/wbraid mas laxo). Un valor que no cumple se
        // guarda como null y se deja constancia en meta para poder contar en produccion cuantos
        // "gtm_..." o numericos llegan, sin atribuirles nada.
        foreach (['gclid' => 'isValidGclid', 'gbraid' => 'isValidBraid', 'wbraid' => 'isValidBraid'] as $field => $check) {
            $raw = $input[$field] ?? null;
            $raw = is_scalar($raw) && !is_bool($raw) ? trim((string) $raw) : '';
            if ($raw === '') {
                $data[$field] = null;
            } elseif (TrackConversion::$check($raw)) {
                $data[$field] = $raw;
            } else {
                $data[$field] = null;
                if (!isset($meta['click_id_rejected'])) {
                    $meta['click_id_rejected'] = '1';
                    $meta['click_id_rejected_val'] = mb_substr($raw, 0, 40);
                }
            }
        }

        // Folio de contacto: mayusculas y recortado; si no cumple el formato, null (no 422). Solo se
        // conserva en eventos de contacto (WhatsApp, correo, llamada) y de cotizacion formal (quote_start,
        // quote_submit; el mismo MN viaja en ambos): en cualquier otro tipo se descarta.
        $folio = $input['contact_folio'] ?? null;
        $folio = is_scalar($folio) && !is_bool($folio) ? strtoupper(trim((string) $folio)) : '';
        $isContactType = in_array(TrackConversion::canonicalType($data['type']), ['whatsapp_click', 'email_click', 'phone_click', 'quote_start', 'quote_submit'], true);
        $data['contact_folio'] = $isContactType && preg_match(TrackConversion::FOLIO_REGEX, $folio) === 1 ? $folio : null;

        // event_at: hora del evento segun el navegador, recortada a [ahora - 7 dias, ahora] para que un
        // reloj desajustado no la deje en el futuro ni lejos en el pasado. Sin valor valido queda null
        // (la API de consulta usa created_at).
        $eventAt = $this->parseDate($input['event_at'] ?? null);
        if ($eventAt) {
            $floor = $now->copy()->subDays(7);
            $eventAt = $eventAt->greaterThan($now) ? $now->copy() : ($eventAt->lessThan($floor) ? $floor : $eventAt);
        }
        $data['event_at'] = $eventAt;

        // captured_at (hora del ultimo toque de atribucion) tampoco puede quedar en el futuro.
        if ($data['captured_at'] && $data['captured_at']->greaterThan($now)) {
            $data['captured_at'] = $now->copy();
        }

        $data['meta'] = $meta ?: null;

        // is_test solo se incluye cuando es true: el default de la BD (0) cubre las filas reales.
        if ($isTest) {
            $data['is_test'] = true;
        }

        $saved = false;
        try {
            if (!empty($data['event_id'])) {
                // Idempotencia: el mismo event_id (reintento) no duplica el registro.
                $saved = TrackConversion::firstOrCreate(['event_id' => $data['event_id']], $data)->wasRecentlyCreated;
            } else {
                TrackConversion::create($data);
                $saved = true;
            }
        } catch (QueryException $e) {
            // Duplicado por un reintento concurrente con el mismo event_id: se considera exito.
            // Cualquier otro error (incluidas otras violaciones 23000) se relanza.
            if (!$this->isUniqueViolation($e)) {
                throw $e;
            }
        }

        // Solo type y event_id: nada de click ids, folios ni URLs en el log. Solo si se guardo la fila.
        if ($saved) {
            Log::info('Conversion Rastreada', ['type' => $data['type'], 'event_id' => $data['event_id']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Cuerpo del request como arreglo. Si no es JSON declarado pero el cuerpo empieza con "{"
     * (navigator.sendBeacon con text/plain), se decodifica aunque haya query string. Lo usa tambien
     * el limitador por sesion (RouteServiceProvider) para leer el mismo session_id.
     */
    public static function payload(Request $request): array
    {
        $input = $request->all();

        if (!$request->isJson()) {
            $body = trim((string) $request->getContent());
            if ($body !== '' && $body[0] === '{') {
                $decoded = json_decode($body, true);
                if (is_array($decoded)) {
                    $input = array_merge($input, $decoded);
                }
            }
        }

        return $input;
    }

    /** 1 / true / "1" / "true" (el flag `test` del payload); cualquier otra cosa es falso. */
    private static function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value === 1;
        }

        return is_string($value) && in_array(strtolower(trim($value)), ['1', 'true'], true);
    }

    /** Solo la violacion de UNIQUE (MySQL 1062 / SQLite "UNIQUE constraint failed") cuenta como duplicado. */
    private function isUniqueViolation(QueryException $e): bool
    {
        return ($e->errorInfo[1] ?? null) === 1062
            || str_contains($e->getMessage(), 'UNIQUE constraint failed');
    }

    /** Texto escalar recortado y truncado; vacio o no escalar -> null. */
    private function cleanString(mixed $value, int $max): ?string
    {
        if (!is_scalar($value) || is_bool($value)) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    /**
     * Fecha ISO 8601 (YYYY-MM-DD…) a UTC. Rechaza texto relativo ("tomorrow", "+1 year"), no escalar y
     * anios fuera de 2000..2037 (el tope de TIMESTAMP en MySQL es 2038; antes provocaba un 500).
     */
    private function parseDate(mixed $value): ?Carbon
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return null;
        }
        try {
            $date = Carbon::parse($value)->utc();
        } catch (\Throwable $e) {
            return null;
        }

        return $date->year >= 2000 && $date->year <= 2037 ? $date : null;
    }
}

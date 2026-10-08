<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TrackConversion;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\TrackConversionTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $base = fn () => TrackConversion::query()->when($since, fn ($q) => $q->where('created_at', '>=', $since));
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
            ->whereNotIn('session_id', TrackConversion::query()->select('session_id')->where('type', 'quote_submit')->whereNotNull('session_id'))
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
            ? TrackConversion::where('session_id', $record->session_id)->orderBy('created_at')->orderBy('id')->limit(100)->get()
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'gclid' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'landing_page' => ['nullable', 'string', 'max:255'],
            'gbraid' => ['nullable', 'string', 'max:255'],
            'wbraid' => ['nullable', 'string', 'max:255'],
            'fbclid' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string'],
            'page_url' => ['nullable', 'string'],
            'session_id' => ['nullable', 'string', 'max:64'],
            'event_id' => ['nullable', 'string', 'max:64'],
            'captured_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array', 'max:20'],
        ]);

        // meta: solo claves conocidas, valores escalares y acotados (evita basura o payloads enormes).
        $meta = [];
        foreach (array_intersect_key((array) ($data['meta'] ?? []), array_flip(TrackConversion::META_KEYS)) as $key => $value) {
            if (is_scalar($value) && $value !== '') {
                $meta[$key] = mb_substr((string) $value, 0, 300);
            }
        }
        $data['meta'] = $meta ?: null;

        // URLs largas se guardan truncadas en lugar de rechazarse.
        foreach (['referrer', 'page_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = mb_substr($data[$field], 0, 500);
            }
        }

        try {
            if (!empty($data['event_id'])) {
                // Idempotencia: el mismo event_id (reintento) no duplica el registro.
                TrackConversion::firstOrCreate(['event_id' => $data['event_id']], $data);
            } else {
                TrackConversion::create($data);
            }
        } catch (QueryException $e) {
            // Duplicado por un reintento concurrente con el mismo event_id: se considera exito.
            if ((string) $e->getCode() !== '23000') {
                throw $e;
            }
        }

        Log::info('Conversion Rastreada', $data);

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AttributionApiTokenMiddleware;
use App\Models\Product;
use App\Models\TrackConversion;
use App\Support\Attribution\AttributionLookupService;
use App\Support\Attribution\AttributionTestMode;
use App\Support\Attribution\FolioExtractor;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Laboratorio de atribucion (admin): activa el "modo prueba" del sitio publico en ESTE navegador y
 * permite revisar, sin tocar visitantes reales, el flujo gclid -> clic de WhatsApp -> fila -> endpoint de n8n.
 *
 * Seguridad:
 *  - Todo el controlador exige el modulo 'site' (como SliderController); el grupo de rutas ya esta
 *    bajo web + auth + role:admin.
 *  - Todas las respuestas son JSON {status:'success'|'error', message?, ...}, salvo index().
 *  - El token del endpoint de n8n (config attribution.api_token) JAMAS sale de aqui: el lookup se
 *    ejecuta en el servidor llamando al servicio, no al endpoint HTTP, y status() solo informa
 *    `configured` / `length_ok`.
 *  - El probador solo ve filas is_test por defecto. Para leer eventos reales (gclid/UTM de clientes)
 *    hace falta ademas el modulo 'ads' (scope=real).
 */
class AttributionLabController extends Controller
{
    /** Zona en la que el equipo lee las horas. */
    private const TZ_MX = 'America/Mexico_City';

    /** Maximo de eventos de prueba que devuelve events(). */
    private const EVENTS_LIMIT = 50;

    public function __construct(private readonly AttributionLookupService $lookup)
    {
        $this->middleware('can-access-module:site');
    }

    /** Pagina del laboratorio. La vista (D2) lee SOLO valores publicos: nunca el token. */
    public function index(Request $request)
    {
        $route = fn (string $name, array $params = []) => route('admin.attribution-lab.' . $name, $params, false);

        $config = [
            'routes' => [
                'status' => $route('status'),
                'events' => $route('events'),
                // __ID__ se sustituye en el navegador por el id de la fila.
                'event' => $route('event', ['id' => '__ID__']),
                'lookup' => $route('lookup'),
                'extract' => $route('extract'),
                'productsSearch' => $route('products-search'),
                'activate' => $route('activate'),
                'deactivate' => $route('deactivate'),
                'destroyTests' => $route('destroy-tests'),
            ],
            'csrf' => csrf_token(),
            'site_url' => url('/'),
            'api_lookup_url' => url('/api/attribution/lookup'),
            'can_real' => $this->canReal($request),
            'test_mode_active' => AttributionTestMode::active($request),
            // Solo se LEE la ruta publica con url(); no se modifica ninguna ruta del sitio.
            'categorias_url' => url('/categorias'),
        ];

        return view('admin-ui.attribution-lab.index', ['lab' => $config]);
    }

    /** Tarjetas de estado y advertencias. Nunca incluye el token ni su longitud exacta. */
    public function status(Request $request): JsonResponse
    {
        $payload = AttributionTestMode::payload($request);

        $tokenLength = strlen((string) config('attribution.api_token'));
        $productionNumber = trim((string) config('contact.whatsapp.number'));
        $testNumber = AttributionTestMode::configuredNumber();
        $testNumberInvalid = AttributionTestMode::configuredNumberInvalid();
        $invisible = (bool) config('attribution.invisible_folio');

        $testActive = $payload !== null;

        $warnings = [];
        if ($productionNumber === '') {
            $warnings[] = ['level' => 'danger', 'message' => 'El número de WhatsApp de producción (contact.whatsapp.number) no está definido: los enlaces de WhatsApp no tendrán a dónde apuntar.'];
        }
        if ($tokenLength === 0) {
            $warnings[] = ['level' => 'danger', 'message' => 'ATTRIBUTION_API_TOKEN no está configurado: el endpoint de n8n responde 503.'];
        } elseif ($tokenLength < AttributionApiTokenMiddleware::MIN_TOKEN_LENGTH) {
            $warnings[] = ['level' => 'danger', 'message' => 'ATTRIBUTION_API_TOKEN es demasiado corto: el endpoint de n8n lo trata como no configurado (503).'];
        }
        if ($invisible) {
            $warnings[] = ['level' => 'warning', 'message' => 'El folio invisible está encendido. Debe permanecer apagado hasta que el aviso de privacidad lo cubra.'];
        }
        if ($testNumberInvalid) {
            $warnings[] = ['level' => 'warning', 'message' => 'ATTRIBUTION_TEST_WHATSAPP_NUMBER no es válido (10 a 15 dígitos): se ignora y el botón abrirá WhatsApp sin número.'];
        }
        if ($testActive) {
            $warnings[] = ['level' => 'warning', 'message' => 'El modo prueba está activo en este navegador: el sitio público no envía analítica y guarda los eventos como de prueba.'];
        }

        // Un editor con solo 'site' no ve metricas del trafico real: ultimo evento y conteos solo de pruebas.
        $canReal = $this->canReal($request);
        $lastEvent = TrackConversion::query()->when(!$canReal, fn ($q) => $q->test())->latest('id')->first(['id', 'created_at']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'test_mode' => [
                    'active' => $testActive,
                    'expires_at_mx' => $payload ? $this->mx(Carbon::createFromTimestampUTC($payload['e'])) : null,
                    'minutes_left' => $payload ? max(0, (int) ceil(($payload['e'] - time()) / 60)) : null,
                ],
                'folio' => [
                    'enabled' => (bool) config('attribution.folio_enabled'),
                    'placements' => (string) config('attribution.folio_placements'),
                    'invisible' => $invisible,
                ],
                'token' => [
                    'configured' => $tokenLength > 0,
                    'length_ok' => $tokenLength >= AttributionApiTokenMiddleware::MIN_TOKEN_LENGTH,
                ],
                'whatsapp' => [
                    'production_number' => $productionNumber,
                    'test_number' => $testNumber,
                    'test_number_invalid' => $testNumberInvalid,
                    // Lo que abriran los botones en modo prueba ('' = wa.me/?text= sin numero).
                    'effective_in_test' => $testNumber,
                ],
                'assets' => [
                    'contact_js_version' => $this->fileVersion('frontend/js/mdn-contact.js'),
                    'events_js_version' => $this->fileVersion('frontend/js/mdn-events.js'),
                ],
                'last_event_at_mx' => $lastEvent?->created_at ? $this->mx($lastEvent->created_at) : null,
                'today' => $this->todayCounts($canReal),
                'warnings' => $warnings,
            ],
        ]);
    }

    /** Ultimos 50 eventos de prueba (id desc). `?after_id=` devuelve solo los mas nuevos (polling). */
    public function events(Request $request): JsonResponse
    {
        $query = TrackConversion::query()->test()->orderByDesc('id');

        $afterId = $request->query('after_id');
        if (is_numeric($afterId) && (int) $afterId > 0) {
            $query->where('id', '>', (int) $afterId);
        }

        $rows = $query->limit(self::EVENTS_LIMIT)->get();

        $events = $rows->map(function (TrackConversion $e) {
            $meta = (array) $e->meta;
            $eventAt = $e->event_at ?: $e->created_at;

            return [
                'id' => $e->id,
                'created_at' => $this->iso($e->created_at),
                'created_at_mx' => $e->created_at ? $this->mx($e->created_at) : null,
                'event_at' => $this->iso($eventAt),
                'event_at_mx' => $eventAt ? $this->mx($eventAt) : null,
                'type' => $e->type,
                'canonical_type' => TrackConversion::canonicalType($e->type),
                'placement' => $this->text($meta['placement'] ?? null),
                'contact_folio' => $e->contact_folio,
                'gclid_short' => $this->short($e->gclid, 8),
                'utm_campaign' => $e->utm_campaign,
                'product' => $this->text($meta['product_name'] ?? null) ?? $this->text($meta['sku'] ?? null),
                'session_short' => $this->short($e->session_id, 8),
                // De donde sale la atribucion de esta fila segun el servicio de lookup (event|session|none).
                'attribution_source' => $this->lookup->resolveAttribution($e)[0] ?? null,
            ];
        })->values()->all();

        return response()->json([
            'status' => 'success',
            'latest_id' => (int) (TrackConversion::query()->test()->max('id') ?? 0),
            'events' => $events,
        ]);
    }

    /** Detalle de UNA fila de prueba (misma forma que el endpoint de n8n). Filas reales -> 404. */
    public function event(Request $request, int $id): JsonResponse
    {
        $event = TrackConversion::query()->test()->find($id);
        if (!$event) {
            return $this->error('No existe un evento de prueba con ese id.', 404);
        }

        return response()->json([
            'status' => 'success',
            'event' => $this->lookup->byEvent($event),
        ]);
    }

    /**
     * Consulta como lo haria n8n, pero ejecutada en el servidor (sin token).
     *
     * Entrada: {mode:'folio'|'window', folio?, type?, from?, to?, tz?, message_at?, scope?:'test'|'real'}.
     */
    public function lookup(Request $request): JsonResponse
    {
        $mode = $request->input('mode');
        if (!in_array($mode, ['folio', 'window'], true)) {
            return $this->error('mode debe ser "folio" o "window".', 422);
        }

        $scope = $request->input('scope', 'test');
        if (!in_array($scope, ['test', 'real'], true)) {
            return $this->error('scope debe ser "test" o "real".', 422);
        }
        if ($scope === 'real' && !$this->canReal($request)) {
            return $this->error('No tienes acceso al módulo de anuncios para consultar eventos reales.', 403);
        }

        $tz = $this->resolveTimezone($request->input('tz'));
        if ($tz === null) {
            return $this->error('tz no es una zona horaria válida.', 422);
        }

        $params = $mode === 'folio'
            ? [
                'folio' => $this->stringOrNull($request->input('folio')),
                'message_at' => $this->withZone($this->stringOrNull($request->input('message_at')), $tz),
            ]
            : [
                'type' => $this->stringOrNull($request->input('type')),
                'from' => $this->withZone($this->stringOrNull($request->input('from')), $tz),
                'to' => $this->withZone($this->stringOrNull($request->input('to')), $tz),
            ];
        $params = array_filter($params, fn ($v) => $v !== null);

        $run = $this->runLookup($params, $mode, $scope);

        return response()->json([
            'status' => 'success',
            'http_status' => $run['http_status'],
            'elapsed_ms' => $run['elapsed_ms'],
            'body' => $run['body'],
            'scope' => $scope,
        ]);
    }

    /**
     * Prueba de extraccion de folio sobre un texto (lo que haria n8n sobre el mensaje de WhatsApp).
     * Si la extraccion tolerante encuentra un folio, hace ademas el lookup (scope test).
     */
    public function extract(Request $request): JsonResponse
    {
        $text = $request->input('text');
        if (!is_string($text)) {
            return $this->error('Falta el texto del mensaje.', 422);
        }
        if (mb_strlen($text) > 5000) {
            return $this->error('El texto es demasiado largo (máximo 5000 caracteres).', 422);
        }

        $extraction = FolioExtractor::extract($text);

        $lookup = null;
        if (!empty($extraction['tolerant'])) {
            $run = $this->runLookup(['folio' => $extraction['tolerant']], 'folio', 'test');
            $lookup = ['http_status' => $run['http_status'], 'body' => $run['body']];
        }

        return response()->json([
            'status' => 'success',
            'extraction' => $extraction,
            // La "regex exacta de n8n": si es false, n8n NO encontraria el folio aunque el probador si.
            'n8n_would_extract' => ($extraction['exact_n8n'] ?? null) !== null,
            'lookup' => $lookup,
        ]);
    }

    /** Typeahead de productos publicos para el generador de visita (id, name, sku, slug). */
    public function productsSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['status' => 'success', 'results' => []]);
        }

        // Escape con '!' (no con '\'): ESCAPE '!' funciona igual en MySQL y en SQLite.
        $like = '%' . preg_replace('/([%_!])/', '!$1', $q) . '%';

        // Mismo criterio publico que FrontendProductController: status=1 e is_approved=1.
        $results = Product::query()
            ->where('status', 1)
            ->where('is_approved', 1)
            ->where(function ($w) use ($like) {
                $w->whereRaw("name LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("sku LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("productModel LIKE ? ESCAPE '!'", [$like]);
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'sku', 'slug'])
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'slug' => $p->slug,
            ])
            ->all();

        return response()->json(['status' => 'success', 'results' => $results]);
    }

    /** Enciende el modo prueba en este navegador (cookie cifrada HttpOnly de 2 h). */
    public function activate(Request $request): JsonResponse
    {
        Cookie::queue(AttributionTestMode::cookie($request, $request->user()));

        // La cookie recien encolada no viaja en ESTE request, asi que no se consulta active():
        // la respuesta calcula la expiracion con el mismo TTL con que se emite.
        $ttl = max(1, (int) config('attribution.test_mode.ttl_minutes', 120));

        return response()->json([
            'status' => 'success',
            'message' => 'Modo prueba activado en este navegador.',
            'test_mode' => [
                'active' => true,
                'expires_at_mx' => $this->mx(Carbon::now()->addMinutes($ttl)),
            ],
        ]);
    }

    /** Apaga el modo prueba (expira la cookie). */
    public function deactivate(Request $request): JsonResponse
    {
        Cookie::queue(AttributionTestMode::forgetCookie($request));

        return response()->json([
            'status' => 'success',
            'message' => 'Modo prueba desactivado en este navegador.',
            'test_mode' => ['active' => false, 'expires_at_mx' => null],
        ]);
    }

    /** Borra TODAS las filas de prueba. El request no aporta ids ni filtros: el WHERE es fijo. */
    public function destroyTests(): JsonResponse
    {
        $deleted = TrackConversion::where('is_test', true)->delete();

        return response()->json(['status' => 'success', 'deleted' => $deleted]);
    }

    // ------------------------------------------------------------------ lookup interno

    /**
     * Ejecuta respond() y aplica el filtro de scope.
     *
     * Como se filtra a "solo pruebas" (scope=test): respond() devuelve filas con la clave `is_test`
     * (B la incluye en present()/byEvent()). En modo folio, si la fila devuelta NO es de prueba se
     * sustituye por un 404 equivalente, de modo que un editor con solo 'site' nunca recibe el
     * gclid/UTM de un cliente real. En modo ventana se descartan de `events` las filas que no son de
     * prueba y se recalcula `count`. Si por alguna razon la fila no trae `is_test`, se confirma contra
     * la tabla por `event_id` (nunca se asume que es de prueba). Nota: en ventana respond() ya aplico
     * su tope de lookup_max a TODAS las filas; si hay muchas reales mas cercanas a `to`, alguna de
     * prueba podria quedar fuera: el probador es para ventanas de laboratorio, no para el trafico real.
     *
     * @param  array<string,mixed>  $params
     * @return array{http_status:int, elapsed_ms:int, body:array}
     */
    private function runLookup(array $params, string $mode, string $scope): array
    {
        $start = microtime(true);
        if ($mode !== 'folio') {
            // Ventana: en scope=test se piden SOLO las filas de prueba (el tope de lookup_max se aplica a ellas).
            $params['include_test'] = $scope === 'test' ? 'only' : null;
        }
        $result = $this->lookup->respond($params);
        $elapsed = (int) round((microtime(true) - $start) * 1000);

        $status = (int) $result['status'];
        $body = (array) $result['body'];

        if ($scope === 'test' && $status === 200) {
            if ($mode === 'folio') {
                if (!$this->isTestRow($body)) {
                    // Mismo cuerpo que un folio inexistente: no se confirma la existencia de folios reales.
                    $status = 404;
                    $body = ['status' => 'error', 'message' => 'Folio no encontrado.'];
                }
            } else {
                $events = array_values(array_filter((array) ($body['events'] ?? []), fn ($row) => is_array($row) && $this->isTestRow($row)));
                $body['events'] = $events;
                $body['count'] = count($events);
            }
        }

        return ['http_status' => $status, 'elapsed_ms' => $elapsed, 'body' => $body];
    }

    /** True solo si la fila presentada es de prueba (clave is_test; si falta, se verifica en BD). */
    private function isTestRow(array $row): bool
    {
        if (array_key_exists('is_test', $row)) {
            return $row['is_test'] === true;
        }

        $eventId = $row['event_id'] ?? null;
        if (!is_string($eventId) || $eventId === '') {
            return false;
        }

        return TrackConversion::query()->test()->where('event_id', $eventId)->exists();
    }

    // ------------------------------------------------------------------ utilidades

    private function canReal(Request $request): bool
    {
        return (bool) $request->user()?->canAccessModule('ads');
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], $status);
    }

    private function resolveTimezone(mixed $tz): ?string
    {
        if ($tz === null || $tz === '') {
            return self::TZ_MX;
        }
        if (!is_string($tz)) {
            return null;
        }
        try {
            new DateTimeZone($tz);
        } catch (\Throwable $e) {
            return null;
        }

        return $tz;
    }

    /**
     * Fecha "ingenua" (sin Z ni offset) -> ISO UTC con Z interpretandola en $tz. Si ya trae zona, o no
     * parece una fecha, se devuelve igual para que respond() la valide (y responda 422 si procede).
     */
    private function withZone(?string $value, string $tz): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?$/', $value)) {
            return $value;
        }
        try {
            return Carbon::parse($value, $tz)->utc()->format('Y-m-d\TH:i:s\Z');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function text(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function short(?string $value, int $length): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_strlen($value) > $length ? mb_substr($value, 0, $length) . '…' : $value;
    }

    /** Fecha 'Y-m-d H:i:s' en hora de Mexico. */
    private function mx(\DateTimeInterface $date): string
    {
        return Carbon::instance($date)->setTimezone(self::TZ_MX)->format('Y-m-d H:i:s');
    }

    /** ISO 8601 UTC con Z. */
    private function iso(?\DateTimeInterface $date): ?string
    {
        return $date ? Carbon::instance($date)->utc()->format('Y-m-d\TH:i:s\Z') : null;
    }

    /** Fecha (hora de Mexico) de la ultima modificacion de un JS publico; null si no existe. */
    private function fileVersion(string $publicPath): ?string
    {
        $mtime = @filemtime(public_path($publicPath));

        return $mtime ? $this->mx(Carbon::createFromTimestampUTC($mtime)) : null;
    }

    /**
     * Conteo de eventos de HOY (dia en America/Mexico_City convertido a UTC) por tipo canonico,
     * separado en reales y de prueba.
     *
     * @return array{real:array<string,int>, test:array<string,int>}
     */
    private function todayCounts(bool $includeReal = true): array
    {
        $start = Carbon::now(self::TZ_MX)->startOfDay()->utc()->format('Y-m-d H:i:s');
        $end = Carbon::now(self::TZ_MX)->endOfDay()->utc()->format('Y-m-d H:i:s');

        $rows = TrackConversion::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('type, is_test, COUNT(*) as total')
            ->groupBy('type', 'is_test')
            ->get();

        $out = ['real' => [], 'test' => []];
        foreach ($rows as $row) {
            if (!$includeReal && !$row->is_test) {
                continue;
            }
            $bucket = $row->is_test ? 'test' : 'real';
            $canonical = (string) (TrackConversion::canonicalType($row->type) ?? 'unknown');
            $out[$bucket][$canonical] = ($out[$bucket][$canonical] ?? 0) + (int) $row->total;
        }

        return $out;
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AttributionApiTokenMiddleware;
use App\Models\AttributionLabSetting;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
                'settings' => $route('settings'),
                'n8nTest' => $route('n8n-test'),
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
            $warnings[] = ['level' => 'warning', 'message' => 'El número de WhatsApp de prueba (guardado en el panel o en ATTRIBUTION_TEST_WHATSAPP_NUMBER) no es válido (10 a 15 dígitos): se ignora y el botón abrirá WhatsApp sin número.'];
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
                'settings' => $this->settingsPayload(),
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

    // ------------------------------------------------------------------ ajustes (BD) y prueba de n8n

    /**
     * Guarda los ajustes del laboratorio en la BD. Solo se tocan las claves presentes en el body;
     * '' o null borra el ajuste (el numero vuelve a depender del .env).
     *
     * Entrada: {test_whatsapp_number?: string|null, n8n_url?: string|null}.
     */
    public function settings(Request $request): JsonResponse
    {
        $hasNumber = $request->has('test_whatsapp_number');
        $hasUrl = $request->has('n8n_url');
        if (!$hasNumber && !$hasUrl) {
            return $this->error('No se envió ningún ajuste para guardar.', 422);
        }

        $errors = [];
        $number = null;
        $url = null;

        if ($hasNumber) {
            [$number, $err] = $this->normalizeNumberInput($request->input('test_whatsapp_number'));
            if ($err) {
                $errors['test_whatsapp_number'] = $err;
            }
        }
        if ($hasUrl) {
            [$url, $err] = $this->normalizeUrlInput($request->input('n8n_url'));
            if ($err) {
                $errors['n8n_url'] = $err;
            }
        }

        if ($errors) {
            return response()->json([
                'status' => 'error',
                'message' => reset($errors),
                'errors' => $errors,
            ], 422);
        }

        try {
            $changed = [];
            if ($hasNumber) {
                AttributionLabSetting::put(AttributionLabSetting::KEY_TEST_WHATSAPP, $number);
                $changed[] = AttributionLabSetting::KEY_TEST_WHATSAPP;
            }
            if ($hasUrl) {
                AttributionLabSetting::put(AttributionLabSetting::KEY_N8N_URL, $url);
                $changed[] = AttributionLabSetting::KEY_N8N_URL;
            }
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 500);
        }

        // Solo se registra QUE clave cambio: la URL de n8n puede llevar un token en la ruta.
        Log::info('Laboratorio de atribución: ajustes modificados', [
            'keys' => $changed,
            'user_id' => $request->user()?->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ajustes guardados.',
            'settings' => $this->settingsPayload(),
        ]);
    }

    /**
     * Envia UNA peticion POST JSON de prueba al webhook de n8n guardado, con el folio del evento de prueba
     * mas reciente. Nunca incluye el token de la API ni datos de clientes reales (solo filas is_test).
     *
     * Seguridad: https obligatorio (http solo en local/testing), sin redirecciones y con guardia SSRF
     * (el host debe resolver SOLO a IPs publicas).
     */
    public function n8nTest(): JsonResponse
    {
        $url = trim((string) AttributionLabSetting::get(AttributionLabSetting::KEY_N8N_URL, ''));
        if ($url === '') {
            return $this->error('Aún no hay una URL de n8n guardada: guárdala en los ajustes primero.', 422);
        }

        // Defensa en profundidad: la URL guardada se revalida (p. ej. si el entorno ya no admite http).
        $urlError = $this->validateN8nUrl($url);
        if ($urlError) {
            return $this->error($urlError, 422);
        }

        $event = TrackConversion::query()->test()
            ->whereNotNull('contact_folio')->where('contact_folio', '!=', '')
            ->orderByDesc('id')->first(['id', 'contact_folio']);
        if (!$event) {
            return $this->error('Aún no hay un evento de prueba con folio: haz clic en un botón de WhatsApp en modo prueba primero.', 422);
        }

        $parts = parse_url($url);
        $host = strtolower(trim((string) ($parts['host'] ?? ''), '[]'));

        $options = ['allow_redirects' => false];
        if (!$this->privateN8nAllowed()) {
            $ips = $this->resolveHostIps($host);
            if (!$ips) {
                return $this->error('No se pudo resolver el servidor de la URL de n8n.', 422);
            }
            foreach ($ips as $ip) {
                if (!$this->isPublicIp($ip)) {
                    return $this->error('La URL de n8n apunta a una dirección no pública (red local o reservada): por seguridad no se envía.', 422);
                }
            }
            // Evita el "DNS rebinding": curl usa la misma IP que se acaba de validar.
            if (!filter_var($host, FILTER_VALIDATE_IP) && defined('CURLOPT_RESOLVE')) {
                $port = (int) ($parts['port'] ?? (strtolower((string) $parts['scheme']) === 'https' ? 443 : 80));
                $options['curl'] = [CURLOPT_RESOLVE => [$host . ':' . $port . ':' . $ips[0]]];
            }
        }

        $folio = (string) $event->contact_folio;
        $text = 'Hola, quiero más información. Folio: ' . $folio;
        $payload = [
            'source' => 'attribution-lab',
            'is_test' => true,
            'folio' => $folio,
            'text' => $text,
            'message_at' => Carbon::now('UTC')->format('Y-m-d\TH:i:s\Z'),
            'lookup_url' => url('/api/attribution/lookup') . '?folio=' . $folio,
        ];
        $sent = ['folio' => $folio, 'text' => $text];

        $start = microtime(true);
        try {
            $response = Http::timeout(10)->acceptJson()->withOptions($options)->post($url, $payload);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo conectar con n8n: ' . $this->shortNetworkError($e, $url, $host),
                'http_status' => null,
                'elapsed_ms' => (int) round((microtime(true) - $start) * 1000),
                'response_excerpt' => '',
                'sent' => $sent,
            ]);
        }
        $elapsed = (int) round((microtime(true) - $start) * 1000);

        $code = $response->status();
        $ok = $response->successful();
        if ($ok) {
            $message = 'n8n respondió HTTP ' . $code . '.';
        } elseif ($code >= 300 && $code < 400) {
            $message = 'n8n respondió HTTP ' . $code . ' (redirección): no se siguen redirecciones; revisa la URL del webhook.';
        } else {
            $message = 'n8n respondió con error HTTP ' . $code . '.';
        }

        return response()->json([
            'status' => $ok ? 'success' : 'error',
            'message' => $message,
            'http_status' => $code,
            'elapsed_ms' => $elapsed,
            // mb_convert_encoding limpia bytes invalidos: json_encode fallaria con un cuerpo binario.
            'response_excerpt' => mb_substr(mb_convert_encoding((string) $response->body(), 'UTF-8', 'UTF-8'), 0, 500),
            'sent' => $sent,
        ]);
    }

    /** Ajustes efectivos que ve el panel (misma forma en status.data.settings y en la respuesta de settings()). */
    private function settingsPayload(): array
    {
        return [
            'test_whatsapp_number' => AttributionTestMode::configuredNumber(),
            'test_whatsapp_source' => AttributionTestMode::configuredNumberSource(),
            'test_whatsapp_db' => (string) AttributionLabSetting::get(AttributionLabSetting::KEY_TEST_WHATSAPP, ''),
            'n8n_url' => (string) AttributionLabSetting::get(AttributionLabSetting::KEY_N8N_URL, ''),
        ];
    }

    /** @return array{0:?string,1:?string} [valor normalizado (solo digitos; null = borrar), error] */
    private function normalizeNumberInput(mixed $value): array
    {
        if ($value === null) {
            return [null, null];
        }
        if (is_int($value)) {
            $value = (string) $value;
        }
        if (!is_string($value)) {
            return [null, 'El número debe ser texto.'];
        }
        // Espacios, +, guiones, parentesis y puntos son solo formato.
        $clean = preg_replace('/[\s+\-().]+/u', '', $value);
        if ($clean === '') {
            return [null, null];
        }
        if (!preg_match('/^\d{10,15}$/', $clean)) {
            return [null, 'El número debe tener de 10 a 15 dígitos (solo números, con lada de país; p. ej. 5218112345678).'];
        }

        return [$clean, null];
    }

    /** @return array{0:?string,1:?string} [URL recortada (null = borrar), error] */
    private function normalizeUrlInput(mixed $value): array
    {
        if ($value === null) {
            return [null, null];
        }
        if (!is_string($value)) {
            return [null, 'La URL debe ser texto.'];
        }
        $value = trim($value);
        if ($value === '') {
            return [null, null];
        }
        $error = $this->validateN8nUrl($value);

        return $error ? [null, $error] : [$value, null];
    }

    /** Mensaje de error si la URL del webhook no es aceptable; null si lo es (solo sintaxis, la IP se valida al enviar). */
    private function validateN8nUrl(string $url): ?string
    {
        if (mb_strlen($url) > 500) {
            return 'La URL es demasiado larga (máximo 500 caracteres).';
        }
        if (preg_match('/[\s\x00-\x1F\x7F]/', $url)) {
            return 'La URL no puede contener espacios ni caracteres de control.';
        }
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return 'La URL no es válida (debe empezar con https:// y llevar un servidor).';
        }

        $scheme = strtolower($parts['scheme']);
        $httpOk = app()->environment('local', 'testing');
        if ($scheme !== 'https' && !($scheme === 'http' && $httpOk)) {
            return 'La URL debe usar https://.';
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            return 'La URL no puede incluir usuario ni contraseña (user:pass@).';
        }
        if (array_key_exists('fragment', $parts) || str_contains($url, '#')) {
            return 'La URL no puede llevar fragmento (#...).';
        }

        $host = strtolower(trim($parts['host'], '[]'));
        $isIp = (bool) filter_var($host, FILTER_VALIDATE_IP);
        // Un host sin letras que no sea una IP valida (p. ej. 2130706433 o 0x7f.1) es una IP disfrazada.
        if (!$isIp && (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) || !preg_match('/[a-z]/', $host))) {
            return 'El servidor de la URL no es válido.';
        }

        return null;
    }

    private function privateN8nAllowed(): bool
    {
        return app()->environment('local', 'testing') && (bool) config('attribution.test_mode.allow_private_n8n', false);
    }

    /** IPs (v4 y v6) a las que resuelve el host; si ya es una IP literal, esa misma. */
    private function resolveHostIps(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $ips = [];
        $v4 = @gethostbynamel($host);
        if (is_array($v4)) {
            $ips = $v4;
        }
        $v6 = @dns_get_record($host, DNS_AAAA);
        if (is_array($v6)) {
            foreach ($v6 as $record) {
                if (!empty($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        return array_values(array_unique($ips));
    }

    /** True solo para IPs publicas: no privadas, no reservadas/loopback/link-local, no CGNAT, ni v4 mapeadas a v6 que lo sean. */
    private function isPublicIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        $bin = @inet_pton($ip);
        if ($bin !== false && strlen($bin) === 16 && str_starts_with($bin, str_repeat("\0", 10) . "\xff\xff")) {
            // ::ffff:a.b.c.d -> se valida la IPv4 embebida.
            return $this->isPublicIp((string) inet_ntop(substr($bin, 12)));
        }
        if ($bin !== false && strlen($bin) === 4) {
            $long = ip2long($ip);
            // 100.64.0.0/10 (CGNAT): PHP no lo marca como privado.
            if ($long !== false && $long >= ip2long('100.64.0.0') && $long <= ip2long('100.127.255.255')) {
                return false;
            }
        }

        return true;
    }

    /** Mensaje de red corto y sin la URL (puede llevar un token en la ruta) ni el servidor. */
    private function shortNetworkError(\Throwable $e, string $url, string $host): string
    {
        $msg = (string) $e->getMessage();
        $msg = str_replace($url, '', $msg);
        $msg = preg_replace('~\(see https?://\S+\)~i', '', $msg);
        $msg = preg_replace('~https?://\S+~i', '', $msg);
        if ($host !== '') {
            $msg = str_ireplace($host, 'el servidor', $msg);
        }
        $msg = trim(preg_replace('/\s+/', ' ', $msg), " .:\t");

        return mb_substr($msg !== '' ? $msg : 'error de red o tiempo de espera agotado', 0, 160);
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

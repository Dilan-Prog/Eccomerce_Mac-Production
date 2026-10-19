<?php

namespace App\Support\Attribution;

use App\Models\TrackConversion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Logica de GET /api/attribution/lookup: encuentra el evento de contacto (por folio o por hora),
 * resuelve de donde viene su atribucion y arma la respuesta con claves FIJAS.
 *
 * Solo lectura. La respuesta NUNCA incluye IP, user-agent, correo, telefono, href, link_text ni el
 * referrer completo: esos datos no se exponen en present().
 */
class AttributionLookupService
{
    /** Claves de meta que se exponen (lista blanca). */
    public const META_WHITELIST = ['sku', 'product_name', 'placement', 'legacy_type', 'folio'];

    /** Valores validos de click_channel. */
    private const CHANNELS = ['google', 'meta', 'none'];

    /**
     * Punto de entrada unico de GET /api/attribution/lookup (lo usan el controlador API y el laboratorio
     * del admin). `$params` son los query params crudos; aqui se replica lo que hacen los middleware
     * TrimStrings y ConvertEmptyStringsToNull (trim y '' -> null) para que dar los parametros directos
     * y pasar por HTTP responda exactamente lo mismo.
     *
     *  folio[+message_at]      evento por folio de contacto (MN…) o de cotizacion (COT-…)
     *  type + from + to        eventos del tipo en una ventana de hora (llamadas)
     *
     * @param  array<string,mixed> $params
     * @return array{status:int,body:array<string,mixed>}
     */
    public function respond(array $params): array
    {
        $params = $this->cleanParams($params);

        $folioParam = $params['folio'] ?? null;
        if (is_string($folioParam) && trim($folioParam) !== '') {
            $folio = $this->normalizeFolio($folioParam);

            // Tolerancia: si n8n manda el mensaje o el folio con comillas / "Folio: " / saltos de linea, el
            // valor normalizado no tiene la forma MN+5 y antes daba 404 aunque el folio existiera. Se intenta
            // extraer el folio valido que contenga (el mismo extractor que el simulador del laboratorio).
            if (!str_starts_with($folio, 'COT-') && preg_match(TrackConversion::FOLIO_REGEX, $folio) !== 1) {
                $found = FolioExtractor::extract($folioParam)['tolerant'] ?? null;
                if (is_string($found) && preg_match(TrackConversion::FOLIO_REGEX, $found) === 1) {
                    $folio = $found;
                }
            }

            return $this->respondByFolio($params, $folio);
        }

        if (($params['type'] ?? null) !== null || ($params['from'] ?? null) !== null || ($params['to'] ?? null) !== null) {
            return $this->respondByTimeWindow($params);
        }

        return $this->fail('Indica folio, o bien type + from + to.', 422);
    }

    /**
     * Eventos por folio de contacto (MN…) o de cotizacion (COT-…). Devuelve null si no existe.
     *
     * @return array<string,mixed>|null
     */
    public function byFolio(string $folio, ?Carbon $messageAt = null): ?array
    {
        $query = TrackConversion::query();
        if (str_starts_with($folio, 'COT-')) {
            $query->where('meta->folio', $folio);
        } else {
            $query->where('contact_folio', $folio);
        }

        $rows = $query->orderByDesc('id')->limit(200)->get();
        if ($rows->isEmpty()) {
            return null;
        }

        // Evento elegido entre las filas del folio: se PREFIERE la de tipo quote_submit (el mismo MN llega
        // en quote_start y quote_submit, y un COT- en quote_submit y quote_dismiss; la cotizacion
        // generada es la que cuenta). Dentro del conjunto preferido (o de todas, si no hay submit): el
        // mas cercano al mensaje si se indico message_at; si no, el mas reciente.
        $pool = $rows->filter(fn (TrackConversion $r) => TrackConversion::canonicalType($r->type) === 'quote_submit');
        if ($pool->isEmpty()) {
            $pool = $rows;
        }
        $event = $pool->first();
        if ($messageAt) {
            $event = $pool->sortBy(fn (TrackConversion $r) => abs($this->eventTime($r)->diffInSeconds($messageAt, false)))->first();
        }

        // Un folio de prueba y uno real con el mismo valor no se mezclan: solo cuentan las filas del
        // mismo tipo (real/prueba) que el evento elegido.
        $candidates = $this->countCandidates($rows->filter(fn (TrackConversion $r) => (bool) $r->is_test === (bool) $event->is_test));

        return $this->decorate($event, $folio, $candidates, $messageAt);
    }

    /**
     * Misma respuesta que el modo folio, pero para UNA fila concreta (el laboratorio la muestra al elegir
     * un evento). ambiguous/candidates salen de las filas con el mismo contact_folio y el mismo is_test;
     * una fila sin folio responde false/1.
     *
     * @return array<string,mixed>
     */
    public function byEvent(TrackConversion $e, ?Carbon $messageAt = null): array
    {
        $candidates = 1;
        if ($e->contact_folio) {
            $rows = TrackConversion::query()
                ->where('contact_folio', $e->contact_folio)
                ->where('is_test', (bool) $e->is_test)
                ->orderByDesc('id')
                ->limit(200)
                ->get();
            $candidates = max(1, $this->countCandidates($rows));
        }

        return $this->decorate($e, $e->contact_folio, $candidates, $messageAt);
    }

    /**
     * Arma la respuesta de una fila en modo folio: present() + folio, ambiguedad y distancia al mensaje.
     *
     * @return array<string,mixed>
     */
    private function decorate(TrackConversion $event, ?string $folio, int $candidates, ?Carbon $messageAt): array
    {
        $data = $this->present($event);
        $data['folio'] = $folio;
        $data['ambiguous'] = $candidates > 1;
        $data['candidates'] = $candidates;
        $data['message_at'] = $messageAt ? $this->iso($messageAt) : null;
        // Positivo = el mensaje llego despues del evento.
        $data['message_to_event_seconds'] = $messageAt ? (int) $this->eventTime($event)->diffInSeconds($messageAt, false) : null;

        return $data;
    }

    /**
     * "Ambiguo" = el mismo folio en sesiones DISTINTAS (colision real). Un doble clic de la misma sesion
     * no cuenta; una fila sin session_id (legacy) se cuenta como su propia sesion.
     *
     * @param \Illuminate\Support\Collection<int,TrackConversion> $rows
     */
    private function countCandidates($rows): int
    {
        return $rows->map(fn (TrackConversion $r) => $r->session_id ?: 'row:' . $r->id)->unique()->count();
    }
    /**
     * Eventos del tipo canonico `$type` cuya hora cae en [$from, $to], ordenados por cercania a $to
     * (la hora de la llamada). Para contactos que no pueden llevar folio (llamadas).
     *
     * @return array<int,array<string,mixed>>
     */
    public function byTimeWindow(string $type, Carbon $from, Carbon $to, int $max, string $testScope = 'real'): array
    {
        $canonical = (string) TrackConversion::canonicalType($type);

        $query = TrackConversion::query();
        // Por defecto solo eventos REALES: una fila del laboratorio cercana a una llamada real no debe
        // desplazarla (el tope es lookup_max). 'only' = solo pruebas, 'all' = ambas.
        if ($testScope === 'real') {
            $query->where('is_test', false);
        } elseif ($testScope === 'only') {
            $query->where('is_test', true);
        }
        $this->constrainType($query, $canonical);
        $fromSql = $from->copy()->utc()->format('Y-m-d H:i:s');
        $toSql = $to->copy()->utc()->format('Y-m-d H:i:s');
        // Un evento reenviado desde la cola del navegador llega tarde: created_at es posterior a su
        // event_at real, asi que se busca por cualquiera de las dos horas y se confirma abajo.
        $query->where(function (Builder $w) use ($fromSql, $toSql) {
            $w->whereBetween('created_at', [$fromSql, $toSql])
                ->orWhereBetween('event_at', [$fromSql, $toSql]);
        });

        return $query->orderByDesc('id')->limit(500)->get()
            ->filter(function (TrackConversion $r) use ($from, $to) {
                $t = $this->eventTime($r);

                return $t->greaterThanOrEqualTo($from) && $t->lessThanOrEqualTo($to);
            })
            ->sortBy(fn (TrackConversion $r) => abs($this->eventTime($r)->diffInSeconds($to, false)))
            ->take($max)
            ->values()
            ->map(function (TrackConversion $r) use ($to) {
                $data = $this->present($r);
                $data['seconds_before_call'] = (int) $this->eventTime($r)->diffInSeconds($to, false);
                $data['match_type'] = 'time_window';

                return $data;
            })
            ->all();
    }

    /**
     * Arma las claves fijas de un evento, con la atribucion ya resuelta.
     *
     * @return array<string,mixed>
     */
    public function present(TrackConversion $e): array
    {
        [$source, $attr] = $this->resolveAttribution($e);

        $gclid = $attr && TrackConversion::isValidGclid($attr->gclid) ? $attr->gclid : null;
        $gbraid = $attr && TrackConversion::isValidBraid($attr->gbraid) ? $attr->gbraid : null;
        $wbraid = $attr && TrackConversion::isValidBraid($attr->wbraid) ? $attr->wbraid : null;
        $fbclid = $attr ? $this->nonEmpty($attr->fbclid) : null;

        $googleType = $gclid ? 'gclid' : ($gbraid ? 'gbraid' : ($wbraid ? 'wbraid' : null));

        $eventAt = $this->eventTime($e);
        $attrMeta = $attr ? (array) $attr->meta : [];
        $ownMeta = (array) $e->meta;

        $clickIdAt = $this->parseIso($attrMeta['click_id_at'] ?? null);
        // Un click_id_at mas de 5 min POSTERIOR al evento es imposible (reloj del cliente desajustado o
        // forjado): se ignora la edad. Entre 0 y 5 min de adelanto se toma como 0 dias.
        $clickAgeDays = null;
        if ($clickIdAt) {
            $secondsAfterClick = (int) $clickIdAt->diffInSeconds($eventAt, false);
            $clickAgeDays = $secondsAfterClick < -300 ? null : (int) ceil(max(0, $secondsAfterClick) / 86400);
        }
        $channel = $attrMeta['click_channel'] ?? null;

        return [
            'folio' => $e->contact_folio,
            'event_id' => $e->event_id,
            'is_test' => (bool) $e->is_test,
            'session_id' => $e->session_id,
            'type' => TrackConversion::canonicalType($e->type),
            'placement' => $this->nonEmpty($ownMeta['placement'] ?? null),
            'event_at' => $this->iso($eventAt),
            'captured_at' => $e->captured_at ? $this->iso($e->captured_at) : null,
            'received_at' => $e->created_at ? $this->iso($e->created_at) : null,
            'gclid' => $gclid,
            'gbraid' => $gbraid,
            'wbraid' => $wbraid,
            'fbclid' => $fbclid,
            'utm_source' => $attr ? $this->nonEmpty($attr->utm_source) : null,
            'utm_medium' => $attr ? $this->nonEmpty($attr->utm_medium) : null,
            'utm_campaign' => $attr ? $this->nonEmpty($attr->utm_campaign) : null,
            'utm_term' => $attr ? $this->nonEmpty($attr->utm_term) : null,
            'utm_content' => $attr ? $this->nonEmpty($attr->utm_content) : null,
            'landing_page' => $this->stripUrl($e->landing_page),
            'page_url' => $this->stripUrl($e->page_url),
            'referrer_host' => $this->host($e->referrer),
            'product' => [
                'sku' => $this->nonEmpty($ownMeta['sku'] ?? null),
                'name' => $this->nonEmpty($ownMeta['product_name'] ?? null),
            ],
            'attribution_source' => $source,
            'has_google_click_id' => $googleType !== null,
            'google_click_id_type' => $googleType,
            'click_id_at' => $clickIdAt ? $this->iso($clickIdAt) : null,
            'click_age_days' => $clickAgeDays,
            'click_channel' => is_string($channel) && in_array($channel, self::CHANNELS, true) ? $channel : null,
            'touch_conflict' => ($attrMeta['touch_conflict'] ?? '0') === '1',
            'session_has_fresh_click' => $gclid ? $this->sessionHasFreshClick($e, $gclid, $clickIdAt) : false,
            'meta' => $this->whitelistedMeta($ownMeta),
        ];
    }

    /**
     * Quien aporta la atribucion: el propio evento ('event'), un evento previo de la misma sesion
     * ('session') o nadie ('none'). Un click id con formato invalido cuenta como ausente.
     *
     * @return array{0:string,1:?TrackConversion}
     */
    public function resolveAttribution(TrackConversion $e): array
    {
        if ($this->hasAttribution($e)) {
            return ['event', $e];
        }

        if ($e->session_id) {
            $prior = TrackConversion::query()
                ->where('session_id', $e->session_id)
                ->where('is_test', (bool) $e->is_test)
                ->where('id', '<', $e->id)
                ->where(function (Builder $w) {
                    foreach (['gclid', 'gbraid', 'wbraid', 'fbclid', 'utm_source', 'utm_campaign'] as $col) {
                        $w->orWhere(fn (Builder $b) => $b->whereNotNull($col)->where($col, '!=', ''));
                    }
                })
                ->orderByDesc('id')
                ->limit(50)
                ->get();

            foreach ($prior as $p) {
                if ($this->hasAttribution($p)) {
                    return ['session', $p];
                }
            }
        }

        return ['none', null];
    }

    /** Click id valido (gclid/gbraid/wbraid/fbclid) o utm_source/utm_campaign no vacio. */
    public function hasAttribution(TrackConversion $r): bool
    {
        return TrackConversion::isValidGclid($r->gclid)
            || TrackConversion::isValidBraid($r->gbraid)
            || TrackConversion::isValidBraid($r->wbraid)
            || $this->nonEmpty($r->fbclid) !== null
            || $this->nonEmpty($r->utm_source) !== null
            || $this->nonEmpty($r->utm_campaign) !== null;
    }

    /**
     * El click id ENTRO en esta sesion: hubo un page_visit previo de la misma sesion con ese gclid Y el
     * click id se capturo (click_id_at) a mas tardar 5 min antes de la primera fila de la sesion. El
     * gclid "pegajoso" (hasta 90 dias) viaja en el page_visit de cada sesion, asi que el page_visit
     * solo no basta: un visitante que vuelve a los 60 dias no es un clic fresco. Sin click_id_at -> false.
     */
    private function sessionHasFreshClick(TrackConversion $e, string $gclid, ?Carbon $clickIdAt): bool
    {
        if (!$e->session_id || !$clickIdAt) {
            return false;
        }

        $firstAt = TrackConversion::query()->where('session_id', $e->session_id)->where('is_test', (bool) $e->is_test)->min('created_at');
        if (!$firstAt || $clickIdAt->lessThan(Carbon::parse($firstAt, 'UTC')->subMinutes(5))) {
            return false;
        }

        return TrackConversion::query()
            ->where('session_id', $e->session_id)
            ->where('is_test', (bool) $e->is_test)
            ->where('type', TrackConversion::VISIT_TYPE)
            ->where('gclid', $gclid)
            ->where('id', '<', $e->id)
            ->exists();
    }

    /**
     * Folio de contacto: mayusculas y sin espacios, guiones ni caracteres invisibles (U+200B a U+200D,
     * U+2060, U+00A0), asi "mn-7k4q2" o un folio copiado con invisibles se reconoce. Los COT-… (folio
     * de cotizacion) conservan sus guiones.
     */
    private function normalizeFolio(string $raw): string
    {
        $folio = strtoupper(trim($raw));
        if (str_starts_with($folio, 'COT-')) {
            return $folio;
        }

        return strtoupper((string) preg_replace('/[\s\-\x{200B}-\x{200D}\x{2060}\x{00A0}]/u', '', $folio));
    }

    /**
     * Replica TrimStrings + ConvertEmptyStringsToNull sobre los parametros (recursivo): trim, y '' -> null.
     *
     * @param  array<mixed> $params
     * @return array<mixed>
     */
    private function cleanParams(array $params): array
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $params[$key] = $this->cleanParams($value);
            } elseif (is_string($value)) {
                // Mismo recorte que TrimStrings de Laravel 10 (espacios, U+FEFF y U+200B en los extremos).
                $value = preg_replace('~^[\s\x{FEFF}\x{200B}]+|[\s\x{FEFF}\x{200B}]+$~u', '', $value) ?? trim($value);
                $params[$key] = $value === '' ? null : $value;
            }
        }

        return $params;
    }

    /**
     * @param  array<string,mixed> $params
     * @return array{status:int,body:array<string,mixed>}
     */
    private function respondByFolio(array $params, string $folio): array
    {
        // Formato invalido: 404 sin consultar la BD (ni un COT- absurdamente largo).
        $isCot = str_starts_with($folio, 'COT-');
        if ($isCot ? strlen($folio) > 64 : preg_match(TrackConversion::FOLIO_REGEX, $folio) !== 1) {
            $this->logMiss('formato_invalido', $folio, $params);

            return $this->fail('Folio no encontrado.', 404);
        }

        $messageAt = null;
        if (($params['message_at'] ?? null) !== null && $params['message_at'] !== '') {
            $messageAt = $this->parseWithZone($params['message_at']);
            if (!$messageAt) {
                return $this->fail('message_at debe ser una fecha ISO 8601 con zona (Z o ±hh:mm).', 422);
            }
        }

        $data = $this->byFolio($folio, $messageAt);
        if ($data === null) {
            $this->logMiss('no_encontrado', $folio, $params);

            return $this->fail('Folio no encontrado.', 404);
        }

        return ['status' => 200, 'body' => $data];
    }

    /**
     * Diagnostico de un 404 por folio (solo corre en la rama de fallo, ya detras de auth y throttle).
     * Registra lo que de verdad llego (en hex, delata comillas, saltos de linea e invisibles), el valor
     * normalizado y que base/tabla ve la aplicacion, para distinguir una peticion mal armada de una base
     * distinta. NUNCA registra tokens ni cabeceras. Un fallo del propio log no debe afectar la respuesta.
     *
     * @param  array<string,mixed> $params
     */
    private function logMiss(string $reason, string $folio, array $params): void
    {
        try {
            $context = [
                'reason' => $reason,
                'normalized' => mb_substr($folio, 0, 80),
                'raw_hex' => bin2hex(mb_substr((string) ($params['folio'] ?? ''), 0, 80)),
            ];
            if ($reason === 'no_encontrado') {
                $context += [
                    'db' => DB::connection()->getDatabaseName(),
                    'rows_total' => TrackConversion::query()->count(),
                    'max_id' => TrackConversion::query()->max('id'),
                    'rows_like' => TrackConversion::query()->where('contact_folio', 'like', '%' . substr($folio, 2) . '%')->count(),
                ];
            }
            Log::warning('attribution.lookup.folio_' . $reason, $context);
        } catch (\Throwable $e) {
            // Sin efecto en la respuesta.
        }
    }

    /**
     * @param  array<string,mixed> $params
     * @return array{status:int,body:array<string,mixed>}
     */
    private function respondByTimeWindow(array $params): array
    {
        $type = $params['type'] ?? null;
        if (!is_string($type) || trim($type) === '') {
            return $this->fail('Falta el parámetro type.', 422);
        }

        $from = $this->parseWithZone($params['from'] ?? null);
        $to = $this->parseWithZone($params['to'] ?? null);
        if (!$from || !$to) {
            return $this->fail('from y to son obligatorios y deben ser fechas ISO 8601 con zona (Z o ±hh:mm).', 422);
        }
        if ($to->lessThan($from)) {
            return $this->fail('to debe ser posterior a from.', 422);
        }

        $windowMinutes = (int) config('attribution.time_window_minutes', 15);
        if ($from->diffInSeconds($to) > $windowMinutes * 60) {
            return $this->fail("La ventana entre from y to no puede pasar de {$windowMinutes} minutos.", 422);
        }

        $max = (int) config('attribution.lookup_max', 20);
        // include_test: omitido = solo reales; 'only' = solo pruebas (laboratorio); '1' = reales y pruebas.
        $include = strtolower((string) ($params['include_test'] ?? ''));
        $testScope = $include === 'only' ? 'only' : (in_array($include, ['1', 'true', 'all'], true) ? 'all' : 'real');
        $events = $this->byTimeWindow(trim($type), $from, $to, $max, $testScope);

        // Una ventana sin eventos NO es un error: responde 200 con count 0.
        return ['status' => 200, 'body' => [
            'type' => TrackConversion::canonicalType(trim($type)),
            'from' => $from->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
            'to' => $to->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
            'count' => count($events),
            'events' => $events,
        ]];
    }

    /** ISO 8601 con zona obligatoria; fecha ingenua o invalida -> null. */
    private function parseWithZone(mixed $value): ?Carbon
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);

        // En una query string un "+" sin codificar llega como espacio ("…T10:00:00 06:00").
        if (str_contains($value, 'T')) {
            $value = preg_replace('/(\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?) (\d{2}:?\d{2})$/', '$1+$2', $value);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?(?:Z|[+-]\d{2}:?\d{2})$/i', $value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->utc();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** @return array{status:int,body:array{status:string,message:string}} */
    private function fail(string $message, int $status): array
    {
        return ['status' => $status, 'body' => ['status' => 'error', 'message' => $message]];
    }
    /** Restringe por la familia del tipo canonico (los tipos viejos se agrupan por prefijo). */
    private function constrainType(Builder $query, string $canonical): void
    {
        match ($canonical) {
            'whatsapp_click' => $query->where('type', 'like', 'whatsapp%'),
            'phone_click' => $query->where(fn (Builder $w) => $w->where('type', 'like', 'telefono%')->orWhere('type', 'phone_click')),
            'email_click' => $query->where(fn (Builder $w) => $w->where('type', 'like', 'correo%')->orWhere('type', 'email_click')),
            default => $query->where('type', $canonical),
        };
    }

    /** Hora del evento: la del navegador (recortada al guardar) o, si falta, la del servidor. */
    private function eventTime(TrackConversion $r): Carbon
    {
        return Carbon::instance($r->event_at ?? $r->created_at)->utc();
    }

    private function iso(\DateTimeInterface $d): string
    {
        return Carbon::instance($d)->utc()->format('Y-m-d\TH:i:s\Z');
    }

    private function parseIso(mixed $value): ?Carbon
    {
        if (!is_string($value) || $value === '') {
            return null;
        }
        try {
            return Carbon::parse($value)->utc();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nonEmpty(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** @return array<string,?string> */
    private function whitelistedMeta(array $meta): array
    {
        $out = [];
        foreach (self::META_WHITELIST as $key) {
            $out[$key] = $this->nonEmpty($meta[$key] ?? null);
        }

        return $out;
    }

    /** URL sin query ni fragment (la query puede traer click ids y datos del visitante). */
    private function stripUrl(?string $url): ?string
    {
        $url = $this->nonEmpty($url);
        if ($url === null) {
            return null;
        }

        return preg_split('/[?#]/', $url, 2)[0] ?: null;
    }

    private function host(?string $url): ?string
    {
        $url = $this->nonEmpty($url);
        if ($url === null) {
            return null;
        }
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : null;
    }
}

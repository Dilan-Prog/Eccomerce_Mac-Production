<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrackConversion;
use App\Support\Attribution\AttributionLookupService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/attribution/lookup — consulta de atribucion para n8n (solo lectura).
 * Protegido por `throttle:attribution-api` + `attribution.token` (ver routes/api.php).
 *
 *  ?folio=MN7K4Q2[&message_at=ISO8601]   evento por folio de contacto, o COT-… (folio de cotizacion)
 *  ?type=phone_click&from=ISO&to=ISO     eventos del tipo en una ventana de hora (llamadas);
 *                                        `to` es la hora de la llamada y ordena por cercania a ella
 *
 * Las fechas llevan SIEMPRE zona (Z o ±hh:mm): una fecha sin zona es ambigua y responde 422.
 */
class AttributionLookupController extends Controller
{
    public function __construct(private readonly AttributionLookupService $lookup)
    {
    }

    public function lookup(Request $request): JsonResponse
    {
        $folioParam = $request->query('folio');
        if (is_string($folioParam) && trim($folioParam) !== '') {
            return $this->byFolio($request, $this->normalizeFolio($folioParam));
        }

        if ($request->query('type') !== null || $request->query('from') !== null || $request->query('to') !== null) {
            return $this->byTimeWindow($request);
        }

        return $this->error('Indica folio, o bien type + from + to.', 422);
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

    private function byFolio(Request $request, string $folio): JsonResponse
    {
        // Formato invalido: 404 sin consultar la BD (ni un COT- absurdamente largo).
        $isCot = str_starts_with($folio, 'COT-');
        if ($isCot ? strlen($folio) > 64 : preg_match(TrackConversion::FOLIO_REGEX, $folio) !== 1) {
            return $this->error('Folio no encontrado.', 404);
        }

        $messageAt = null;
        if ($request->query('message_at') !== null && $request->query('message_at') !== '') {
            $messageAt = $this->parseWithZone($request->query('message_at'));
            if (!$messageAt) {
                return $this->error('message_at debe ser una fecha ISO 8601 con zona (Z o ±hh:mm).', 422);
            }
        }

        $data = $this->lookup->byFolio($folio, $messageAt);
        if ($data === null) {
            return $this->error('Folio no encontrado.', 404);
        }

        return response()->json($data);
    }

    private function byTimeWindow(Request $request): JsonResponse
    {
        $type = $request->query('type');
        if (!is_string($type) || trim($type) === '') {
            return $this->error('Falta el parámetro type.', 422);
        }

        $from = $this->parseWithZone($request->query('from'));
        $to = $this->parseWithZone($request->query('to'));
        if (!$from || !$to) {
            return $this->error('from y to son obligatorios y deben ser fechas ISO 8601 con zona (Z o ±hh:mm).', 422);
        }
        if ($to->lessThan($from)) {
            return $this->error('to debe ser posterior a from.', 422);
        }

        $windowMinutes = (int) config('attribution.time_window_minutes', 15);
        if ($from->diffInSeconds($to) > $windowMinutes * 60) {
            return $this->error("La ventana entre from y to no puede pasar de {$windowMinutes} minutos.", 422);
        }

        $max = (int) config('attribution.lookup_max', 20);
        $events = $this->lookup->byTimeWindow(trim($type), $from, $to, $max);

        return response()->json([
            'type' => TrackConversion::canonicalType(trim($type)),
            'from' => $from->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
            'to' => $to->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
            'count' => count($events),
            'events' => $events,
        ]);
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

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], $status);
    }
}

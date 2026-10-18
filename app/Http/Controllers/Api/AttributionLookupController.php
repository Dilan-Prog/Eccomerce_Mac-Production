<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Attribution\AttributionLookupService;
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
 * Toda la logica vive en AttributionLookupService::respond(), que tambien usa el laboratorio del admin.
 */
class AttributionLookupController extends Controller
{
    public function __construct(private readonly AttributionLookupService $lookup)
    {
    }

    public function lookup(Request $request): JsonResponse
    {
        $r = $this->lookup->respond($request->query());

        return response()->json($r['body'], $r['status'])->header('Cache-Control', 'no-store');
    }
}

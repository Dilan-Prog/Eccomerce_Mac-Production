<?php

namespace App\Http\Controllers\AspelSync;

use App\Http\Controllers\Controller;
use App\Models\CotizacionMonedaAspel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Sin middleware de acceso a módulo a propósito: este controlador solo tiene
 * `sync()`, llamado por el script externo de Aspel vía routes/api.php sin
 * sesión de admin — `can-access-module:*` requiere `$request->user()` (que
 * ahí siempre es null) y truena con 500 en vez de proteger nada. La
 * protección real pendiente es un token compartido, no este middleware.
 */
class CotizacionMonedaSyncController extends Controller
{
    /**
     * Recibe el push diario completo de monedas de Aspel (mismo shape que
     * monedas_aspel), pero lo guarda en cotizacion_monedas_aspel — tabla
     * aislada, exclusiva de Cotizaciones (ver App\Support\CotizacionExchange).
     * monedas_aspel (catálogo/ecommerce) no se toca. Mismo patrón que
     * AspelSyncController::sync()/AspelClientSyncController::sync(): aplana,
     * valida, upsert por item con try/catch individual.
     */
    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $flat = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item[0]) && is_array($item[0])) {
                foreach ($item as $row) { $flat[] = $row; }
            } else {
                $flat[] = $item;
            }
        }
        $request->merge(['items' => $flat]);

        $request->validate([
            'items' => 'required|array',
            'items.*.num_moneda' => 'required|integer',
            'items.*.descripcion' => 'nullable|string|max:255',
            'items.*.simbolo' => 'nullable|string|max:255',
            'items.*.tipo_cambio' => 'nullable|numeric',
            'items.*.status' => 'nullable|boolean',
            'items.*.cve_moned' => 'nullable|string|max:10',
        ]);

        $synced = 0;
        $errors = [];

        foreach ($request->items as $index => $item) {
            try {
                CotizacionMonedaAspel::updateOrCreate(
                    ['num_moneda' => $item['num_moneda']],
                    [
                        'num_moneda' => $item['num_moneda'],
                        'descripcion' => $item['descripcion'] ?? null,
                        'simbolo' => $item['simbolo'] ?? null,
                        'tipo_cambio' => $item['tipo_cambio'] ?? null,
                        'status' => $item['status'] ?? null,
                        'cve_moned' => $item['cve_moned'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                Log::error("Error sincronizando moneda {$index}: " . $e->getMessage());
                $errors[] = "Item {$index} (num_moneda: {$item['num_moneda']}): " . $e->getMessage();
            }
        }

        return response()->json([
            'status' => $errors ? 'PARTIAL' : 'OK',
            'synced' => $synced,
            'total' => count($request->items),
            'errors' => $errors,
        ]);
    }
}

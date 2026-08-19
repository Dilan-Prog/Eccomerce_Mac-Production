<?php

namespace App\Http\Controllers\AspelSync;

use App\Http\Controllers\Controller;
use App\Models\AspelClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Sin middleware de acceso a módulo a propósito: este controlador solo tiene
 * `sync()`, llamado por el script externo de Aspel vía routes/api.php sin
 * sesión de admin — `can-access-module:*` requiere `$request->user()` (que
 * ahí siempre es null) y truena con 500 en vez de proteger nada. La
 * protección real pendiente es un token compartido, no este middleware.
 */
class AspelClientSyncController extends Controller
{
    /** Mirrors AspelSyncController::sync() exactly: flatten, validate, per-item upsert. */
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
            'items.*.clave' => 'required|string|max:10',
            'items.*.status' => 'nullable|string|size:1',
            'items.*.nombre' => 'nullable|string|max:254',
            'items.*.nombre_comercial' => 'nullable|string|max:254',
            'items.*.rfc' => 'nullable|string|max:15',
            'items.*.tipo_empresa' => 'nullable|string|size:1',
            'items.*.uso_cfdi' => 'nullable|string|max:5',
            'items.*.regimen_fiscal' => 'nullable|string|max:4',
            'items.*.email' => 'nullable|string|max:512',
            'items.*.telefono' => 'nullable|string|max:25',
            'items.*.calle' => 'nullable|string|max:80',
            'items.*.num_ext' => 'nullable|string|max:15',
            'items.*.colonia' => 'nullable|string|max:50',
            'items.*.municipio' => 'nullable|string|max:50',
            'items.*.estado' => 'nullable|string|max:50',
            'items.*.codigo_postal' => 'nullable|string|max:5',
            'items.*.lista_precio' => 'nullable|integer',
            'items.*.descuento' => 'nullable|numeric',
            'items.*.cve_vendedor' => 'nullable|string|max:5',
        ]);

        $synced = 0;
        $errors = [];

        foreach ($request->items as $index => $item) {
            try {
                AspelClient::updateOrCreate(
                    ['clave' => $item['clave']],
                    [
                        'clave' => $item['clave'],
                        'status' => $item['status'] ?? null,
                        'nombre' => $item['nombre'] ?? null,
                        'nombre_comercial' => $item['nombre_comercial'] ?? null,
                        'rfc' => $item['rfc'] ?? null,
                        'tipo_empresa' => $item['tipo_empresa'] ?? null,
                        'uso_cfdi' => $item['uso_cfdi'] ?? null,
                        'regimen_fiscal' => $item['regimen_fiscal'] ?? null,
                        'email' => $item['email'] ?? null,
                        'telefono' => $item['telefono'] ?? null,
                        'calle' => $item['calle'] ?? null,
                        'num_ext' => $item['num_ext'] ?? null,
                        'colonia' => $item['colonia'] ?? null,
                        'municipio' => $item['municipio'] ?? null,
                        'estado' => $item['estado'] ?? null,
                        'codigo_postal' => $item['codigo_postal'] ?? null,
                        'lista_precio' => $item['lista_precio'] ?? null,
                        'descuento' => $item['descuento'] ?? null,
                        'cve_vendedor' => $item['cve_vendedor'] ?? null,
                        'remote_updated_at' => now(),
                        'sync_hash' => md5(($item['clave'] ?? '') . ($item['nombre'] ?? '') . ($item['rfc'] ?? '')),
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                Log::error("Error sincronizando cliente {$index}: " . $e->getMessage());
                $errors[] = "Item {$index} (CLAVE: {$item['clave']}): " . $e->getMessage();
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

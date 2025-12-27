<?php

namespace App\Http\Controllers\AspelSync;

use App\Http\Controllers\Controller;
use App\Models\PrecioXProductAspel;
use App\Models\AspelSync;
use App\Models\Precios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrecioXProductoController extends Controller
{
    public function precioXProducto(Request $request)
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
            'items.*.cve_art' => 'required|string|max:50',
            'items.*.cve_precio' => 'required|integer',
            'items.*.precio' => 'required|numeric',
        ]);

        $synced = 0;
        $errors = [];

        foreach ($request->items as $index => $item) {
            try {
                // Validar que exista el producto
                $producto = AspelSync::where('cve_art', $item['cve_art'])->first();
                if (!$producto) {
                    throw new \Exception("Producto con cve_art '{$item['cve_art']}' no existe");
                }

                // Validar que exista el precio
                $precio = Precios::where('cve_precio', $item['cve_precio'])->first();
                if (!$precio) {
                    throw new \Exception("Precio con cve_precio '{$item['cve_precio']}' no existe");
                }

                // Crear o actualizar el registro
                PrecioXProductAspel::updateOrCreate(
                    [
                        'cve_art' => $item['cve_art'],
                        'cve_precio' => $item['cve_precio']
                    ],
                    [
                        'precio' => $item['precio']
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                Log::error("Error sincronizando item {$index}: " . $e->getMessage());
                $errors[] = "Item {$index} (CVE_ART: {$item['cve_art']}, CVE_PRECIO: {$item['cve_precio']}): " . $e->getMessage();
            }
        }

        return response()->json([
            'status' => $errors ? 'PARTIAL' : 'OK',
            'synced' => $synced,
            'total' => count($request->items),
            'errors' => $errors
        ]);
    }
}

<?php

namespace App\Http\Controllers\AspelSync;

use App\Http\Controllers\Controller;
use App\Models\PrecioXProductAspel;
use App\Models\AspelSync;
use App\Models\Precios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PrecioXProductoController extends Controller
{
    public function precioXProducto(Request $request)
    {
        $items = $request->input('items', []);
        if (!is_array($items)) {
            $items = []; // items no es una lista: se deja vacio y la validacion responde 422 (antes: error 500 en el foreach)
        }
        $flat = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item[0]) && is_array($item[0])) {
                foreach ($item as $row) { $flat[] = $row; }
            } else {
                $flat[] = $item;
            }
        }
        $request->merge(['items' => $flat]);

        // Solo se valida la ESTRUCTURA del request; cada precio se valida por separado dentro del ciclo: un registro
        // invalido se rechaza y se reporta, pero NO tumba el lote completo.
        $request->validate(['items' => 'required|array']);
        $rules = [
            'cve_art' => 'required|string|max:50',
            'cve_precio' => 'required|integer',
            'precio' => 'required|numeric',
        ];

        $synced = 0;
        $errors = [];
        $rejected = 0;

        foreach ($request->items as $index => $item) {
            $validator = is_array($item) ? Validator::make($item, $rules) : null;
            if (!$validator || $validator->fails()) {
                $rejected++;
                $sku = is_array($item) ? ($item['cve_art'] ?? '?') : '?';
                $detail = $validator ? implode(' | ', array_slice($validator->errors()->all(), 0, 3)) : 'el registro no es un objeto';
                $errors[] = "Item {$index} (CVE_ART: {$sku}): datos invalidos: " . $detail;
                continue;
            }

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

        if ($rejected > 0) {
            Log::warning('Aspel precios: registros rechazados por validacion', ['rejected' => $rejected, 'total' => count($request->items), 'muestra' => array_slice($errors, 0, 5)]);
        }

        $payload = [
            'status' => $errors ? 'PARTIAL' : 'OK',
            'synced' => $synced,
            'total' => count($request->items),
            'rejected' => $rejected,
            'errors' => $errors
        ];

        // Si NINGUN registro se pudo guardar porque todos eran invalidos, se mantiene el 422 de siempre.
        $status = ($synced === 0 && $rejected > 0 && $rejected === count($request->items)) ? 422 : 200;

        return response()->json($payload, $status);
    }
}

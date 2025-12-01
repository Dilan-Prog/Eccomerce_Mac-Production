<?php

namespace App\Http\Controllers\AspelSync;

use App\Http\Controllers\Controller;
use App\Models\AspelSync;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class AspelSyncController extends Controller
{
    public function syncData(Request $request){
        // Seguridad simple por token
        if($request->query('token') !== config('service.aspel.token')){
            Log::warning('AspelSync: Token Invalido en intento de sincronizacion', [
                'ip' => $request->ip(),
                'provided_token' => $request->query('token'),
            ]);
            abort(401, 'No Autorizado');
        }

        $items = $request->json()->all();

        if(!is_array($items)){
            Log::warning('Formato invalido recibido en AspelSync');
            return response()->json(['error' => 'Formato invalido'], 422);
        }

        $updated = 0;
        $skipped = 0;

         foreach ($items as $item) {

            if (!isset($item['sku'], $item['price'], $item['stock'], $item['updated_at'])) {
                Log::error('AspelSync - item inválido', ['item' => $item]);
                continue;
            }

            $hash = md5($item['sku'].$item['price'].$item['stock'].$item['updated_at']);

            $product = Product::where('sku', $item['sku'])->first();

            if (!$product) {
                Log::info('AspelSync - producto no encontrado', ['sku' => $item['sku']]);
                continue;
            }

            if ($product->sync_hash === $hash) {
                $skipped++;
                continue;
            }

            $product->update([
                'price' => $item['price'],
                'stock' => $item['stock'],
                'remote_updated_at' => $item['updated_at'],
                'sync_hash' => $hash
            ]);

            $updated++;
        }

        Log::info('AspelSync finalizado', [
            'updated' => $updated,
            'skipped' => $skipped,
        ]);

        return response()->json([
            'status' => 'ok',
            'updated' => $updated,
            'skipped' => $skipped
        ]);
    }



    public function sync(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.sku' => 'required|string',
            'items.*.nombre' => 'nullable|string',
            'items.*.price' => 'required|numeric',
            'items.*.stock' => 'required|numeric'
        ]);

        foreach ($request->items as $item) {
            AspelSync::updateOrCreate(   // ← MODELO CORRECTO
                ['sku' => $item['sku']],
                [
                    'nombre' => $item['nombre'] ?? null,
                    'price'  => $item['price'],
                    'stock'  => $item['stock'],
                    'sync_hash' => md5($item['sku'].$item['price'].$item['stock'])
                ]
            );
        }

        return response()->json([
            'status' => 'OK',
            'received' => $request->all()
        ]);
    }


}

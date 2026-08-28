<?php

namespace App\Http\Controllers\AspelSync;

use App\Http\Controllers\Controller;
use App\Models\AspelSale;
use App\Models\AspelSaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Sin middleware de acceso a módulo a propósito, igual que
 * AspelClientSyncController/AspelSyncController: lo llama el script externo
 * de Aspel sin sesión de admin, vía routes/api.php dentro del grupo
 * `aspel.token`. La protección real es el token compartido (ver
 * App\Http\Middleware\AspelApiTokenMiddleware).
 *
 * Espejo de FACTF01 (encabezado, TIP_DOC='F') + PAR_FACTF01 (partidas).
 * Payload esperado:
 * {
 *   "ventas": [ { cve_doc, cve_clpv, fecha_doc, fecha_cancela, importe, rfc, num_moned, tipcamb, uuid }, ... ],
 *   "partidas": [ { cve_doc, num_par, cve_art, cant, prec, tot_partida, descr_art }, ... ]
 * }
 * Mismo patrón que los otros sync de Aspel: aplanar (array plano o
 * anidado por lote), validar, updateOrCreate por item dentro de try/catch
 * individual (un item que falla no aborta el lote), respuesta
 * {status: OK|PARTIAL, synced, total, errors[]} — reportado por bloque
 * (ventas y partidas) y combinado.
 */
class AspelSalesSyncController extends Controller
{
    public function sync(Request $request)
    {
        $ventas = $this->flatten($request->input('ventas', []));
        $partidas = $this->flatten($request->input('partidas', []));

        $request->merge(['ventas' => $ventas, 'partidas' => $partidas]);

        $request->validate([
            'ventas' => 'array',
            'ventas.*.cve_doc' => 'required_with:ventas|string|max:20',
            'ventas.*.cve_clpv' => 'nullable|string|max:10',
            'ventas.*.fecha_doc' => 'nullable|date',
            'ventas.*.fecha_cancela' => 'nullable|date',
            'ventas.*.importe' => 'nullable|numeric',
            'ventas.*.rfc' => 'nullable|string|max:15',
            'ventas.*.num_moned' => 'nullable|integer',
            'ventas.*.tipcamb' => 'nullable|numeric',
            'ventas.*.uuid' => 'nullable|string|max:50',

            'partidas' => 'array',
            'partidas.*.cve_doc' => 'required_with:partidas|string|max:20',
            'partidas.*.num_par' => 'required_with:partidas|integer',
            'partidas.*.cve_art' => 'required_with:partidas|string|max:16',
            'partidas.*.cant' => 'nullable|numeric',
            'partidas.*.prec' => 'nullable|numeric',
            'partidas.*.tot_partida' => 'nullable|numeric',
            'partidas.*.descr_art' => 'nullable|string|max:40',
        ]);

        [$ventasSynced, $ventasErrors] = $this->syncVentas($request->input('ventas', []));
        [$partidasSynced, $partidasErrors] = $this->syncPartidas($request->input('partidas', []));

        $errors = array_merge($ventasErrors, $partidasErrors);

        return response()->json([
            'status' => $errors ? 'PARTIAL' : 'OK',
            'synced' => $ventasSynced + $partidasSynced,
            'total' => count($request->input('ventas', [])) + count($request->input('partidas', [])),
            'errors' => $errors,
        ]);
    }

    /** Aplana un array de items que puede venir plano o anidado por lote (mismo patrón que los demás sync de Aspel). */
    protected function flatten(array $items): array
    {
        $flat = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item[0]) && is_array($item[0])) {
                foreach ($item as $row) {
                    $flat[] = $row;
                }
            } else {
                $flat[] = $item;
            }
        }

        return $flat;
    }

    /** @return array{0: int, 1: array<int, string>} */
    protected function syncVentas(array $ventas): array
    {
        $synced = 0;
        $errors = [];

        foreach ($ventas as $index => $item) {
            try {
                AspelSale::updateOrCreate(
                    ['cve_doc' => $item['cve_doc']],
                    [
                        'cve_doc' => $item['cve_doc'],
                        'cve_clpv' => $item['cve_clpv'] ?? null,
                        'fecha_doc' => $item['fecha_doc'] ?? null,
                        'fecha_cancela' => $item['fecha_cancela'] ?? null,
                        'importe' => $item['importe'] ?? null,
                        'rfc' => $item['rfc'] ?? null,
                        'num_moned' => $item['num_moned'] ?? null,
                        'tipcamb' => $item['tipcamb'] ?? null,
                        'uuid' => $item['uuid'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                Log::error("Error sincronizando venta {$index}: " . $e->getMessage());
                $errors[] = "Venta {$index} (CVE_DOC: {$item['cve_doc']}): " . $e->getMessage();
            }
        }

        return [$synced, $errors];
    }

    /** @return array{0: int, 1: array<int, string>} */
    protected function syncPartidas(array $partidas): array
    {
        $synced = 0;
        $errors = [];

        foreach ($partidas as $index => $item) {
            try {
                AspelSaleItem::updateOrCreate(
                    ['cve_doc' => $item['cve_doc'], 'num_par' => $item['num_par']],
                    [
                        'cve_doc' => $item['cve_doc'],
                        'num_par' => $item['num_par'],
                        'cve_art' => $item['cve_art'],
                        'cant' => $item['cant'] ?? null,
                        'prec' => $item['prec'] ?? null,
                        'tot_partida' => $item['tot_partida'] ?? null,
                        'descr_art' => $item['descr_art'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                Log::error("Error sincronizando partida {$index}: " . $e->getMessage());
                $errors[] = "Partida {$index} (CVE_DOC: {$item['cve_doc']}, NUM_PAR: {$item['num_par']}): " . $e->getMessage();
            }
        }

        return [$synced, $errors];
    }
}

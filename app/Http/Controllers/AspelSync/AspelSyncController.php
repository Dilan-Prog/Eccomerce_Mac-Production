<?php

namespace App\Http\Controllers\AspelSync;

use App\DataTables\AspelSyncDataTable;
use App\Http\Controllers\Controller;
use App\Models\AspelSync;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AspelSyncController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.cve_art' => 'required|string|max:50',
            'items.*.descr' => 'nullable|string|max:255',
            'items.*.lin_prod' => 'nullable|string|max:50',
            'items.*.con_serie' => 'nullable|string|size:1',
            'items.*.uni_med' => 'nullable|string|max:50',
            'items.*.uni_emp' => 'nullable|numeric',
            'items.*.ctrl_alm' => 'nullable|string|max:50',
            'items.*.tiem_surt' => 'nullable|numeric',
            'items.*.stock_min' => 'nullable|numeric',
            'items.*.stock_max' => 'nullable|numeric',
            'items.*.exist' => 'nullable|numeric',
            'items.*.pend_surt' => 'nullable|numeric',
            'items.*.apart' => 'nullable|numeric',
            'items.*.comp_x_rec' => 'nullable|numeric',
            'items.*.tip_costeo' => 'nullable|string|size:1',
            'items.*.num_mon' => 'nullable|string|max:10',
            'items.*.costo_prom' => 'nullable|numeric',
            'items.*.ult_costo' => 'nullable|numeric',
            'items.*.fch_ultcom' => 'nullable|date_format:Y-m-d H:i:s',
            'items.*.fch_ultvta' => 'nullable|date_format:Y-m-d H:i:s',
            'items.*.cve_obs' => 'nullable|string|max:50',
            'items.*.tipo_ele' => 'nullable|string|size:1',
            'items.*.uni_alt' => 'nullable|string|max:50',
            'items.*.fac_conv' => 'nullable|numeric',
            'items.*.con_lote' => 'nullable|string|size:1',
            'items.*.con_pedimento' => 'nullable|string|size:1',
            'items.*.peso' => 'nullable|numeric',
            'items.*.volumen' => 'nullable|numeric',
            'items.*.cve_esqimpu' => 'nullable|string|max:50',
            'items.*.cve_bita' => 'nullable|string|max:50',
            'items.*.cuent_cont' => 'nullable|string|max:50',
            'items.*.cve_prodserv' => 'nullable|string|max:50',
            'items.*.cve_unidad' => 'nullable|string|max:50',
            'items.*.vtas_anl_c' => 'nullable|numeric',
            'items.*.vtas_anl_m' => 'nullable|numeric',
            'items.*.comp_anl_c' => 'nullable|numeric',
            'items.*.comp_anl_m' => 'nullable|numeric',
            'items.*.prefijo' => 'nullable|string|max:50',
            'items.*.talla' => 'nullable|string|max:50',
            'items.*.color' => 'nullable|string|max:50',
            'items.*.cve_imagen' => 'nullable|string|max:255',
            'items.*.blk_cst_ext' => 'nullable|string|size:1',
            'items.*.status' => 'nullable|string|size:1',
            'items.*.man_ieps' => 'nullable|string|size:1',
            'items.*.apl_man_imp' => 'nullable|integer',
            'items.*.cuota_ieps' => 'nullable|numeric',
            'items.*.apl_man_ieps' => 'nullable|string|size:1',
            'items.*.uuid' => 'nullable|string|max:50',
            'items.*.version_sinc' => 'nullable|date_format:Y-m-d H:i:s',
            'items.*.version_sinc_fecha_img' => 'nullable|date_format:Y-m-d H:i:s',
            'items.*.edo_publ_ml' => 'nullable|string|max:50',
            'items.*.cve_publ_ml' => 'nullable|string|max:50',
            'items.*.condicion_ml' => 'nullable|string|max:50',
            'items.*.tipo_publ_ml' => 'nullable|string|max:50',
            'items.*.categ_ml' => 'nullable|string|max:255',
            'items.*.cve_cate_ml' => 'nullable|string|max:50',
            'items.*.titulo_ml' => 'nullable|string|max:255',
            'items.*.modo_envio_ml' => 'nullable|string|max:50',
            'items.*.envio_ml' => 'nullable|string|size:1',
            'items.*.largo_ml' => 'nullable|numeric',
            'items.*.alto_ml' => 'nullable|numeric',
            'items.*.ancho_ml' => 'nullable|numeric',
            'items.*.campos_categ_ml' => 'nullable|json',
            'items.*.disponible_publ' => 'nullable|string|size:1',
            'items.*.last_update_ml' => 'nullable|date_format:Y-m-d H:i:s',
            'items.*.f_crea_ml' => 'nullable|date_format:Y-m-d H:i:s',
            'items.*.imagen_ml' => 'nullable|string|max:255',
            'items.*.en_catalogo' => 'nullable|string|max:50',
            'items.*.id_catalogo' => 'nullable|string|max:50',
            'items.*.mat_peli' => 'nullable|string|size:1',
        ]);

        $synced = 0;
        $errors = [];

        foreach ($request->items as $index => $item) {
            try {
                AspelSync::updateOrCreate(
                    ['cve_art' => $item['cve_art']],
                    [
                        'cve_art' => $item['cve_art'],
                        'descr' => $item['descr'] ?? null,
                        'lin_prod' => $item['lin_prod'] ?? null,
                        'con_serie' => $item['con_serie'] ?? null,
                        'uni_med' => $item['uni_med'] ?? null,
                        'uni_emp' => $item['uni_emp'] ?? null,
                        'ctrl_alm' => $item['ctrl_alm'] ?? null,
                        'tiem_surt' => $item['tiem_surt'] ?? null,
                        'stock_min' => $item['stock_min'] ?? null,
                        'stock_max' => $item['stock_max'] ?? null,
                        'exist' => $item['exist'] ?? null,
                        'pend_surt' => $item['pend_surt'] ?? null,
                        'apart' => $item['apart'] ?? null,
                        'comp_x_rec' => $item['comp_x_rec'] ?? null,
                        'tip_costeo' => $item['tip_costeo'] ?? null,
                        'num_mon' => $item['num_mon'] ?? null,
                        'costo_prom' => $item['costo_prom'] ?? null,
                        'ult_costo' => $item['ult_costo'] ?? null,
                        'fch_ultcom' => $item['fch_ultcom'] ?? null,
                        'fch_ultvta' => $item['fch_ultvta'] ?? null,
                        'cve_obs' => $item['cve_obs'] ?? null,
                        'tipo_ele' => $item['tipo_ele'] ?? null,
                        'uni_alt' => $item['uni_alt'] ?? null,
                        'fac_conv' => $item['fac_conv'] ?? null,
                        'con_lote' => $item['con_lote'] ?? null,
                        'con_pedimento' => $item['con_pedimento'] ?? null,
                        'peso' => $item['peso'] ?? null,
                        'volumen' => $item['volumen'] ?? null,
                        'cve_esqimpu' => $item['cve_esqimpu'] ?? null,
                        'cve_bita' => $item['cve_bita'] ?? null,
                        'cuent_cont' => $item['cuent_cont'] ?? null,
                        'cve_prodserv' => $item['cve_prodserv'] ?? null,
                        'cve_unidad' => $item['cve_unidad'] ?? null,
                        'vtas_anl_c' => $item['vtas_anl_c'] ?? null,
                        'vtas_anl_m' => $item['vtas_anl_m'] ?? null,
                        'comp_anl_c' => $item['comp_anl_c'] ?? null,
                        'comp_anl_m' => $item['comp_anl_m'] ?? null,
                        'prefijo' => $item['prefijo'] ?? null,
                        'talla' => $item['talla'] ?? null,
                        'color' => $item['color'] ?? null,
                        'cve_imagen' => $item['cve_imagen'] ?? null,
                        'blk_cst_ext' => $item['blk_cst_ext'] ?? 'N',
                        'status' => $item['status'] ?? 'A',
                        'man_ieps' => $item['man_ieps'] ?? 'N',
                        'apl_man_imp' => $item['apl_man_imp'] ?? null,
                        'cuota_ieps' => $item['cuota_ieps'] ?? null,
                        'apl_man_ieps' => $item['apl_man_ieps'] ?? null,
                        'uuid' => $item['uuid'] ?? null,
                        'version_sinc' => $item['version_sinc'] ?? null,
                        'version_sinc_fecha_img' => $item['version_sinc_fecha_img'] ?? null,
                        'edo_publ_ml' => $item['edo_publ_ml'] ?? null,
                        'cve_publ_ml' => $item['cve_publ_ml'] ?? null,
                        'condicion_ml' => $item['condicion_ml'] ?? null,
                        'tipo_publ_ml' => $item['tipo_publ_ml'] ?? null,
                        'categ_ml' => $item['categ_ml'] ?? null,
                        'cve_cate_ml' => $item['cve_cate_ml'] ?? null,
                        'titulo_ml' => $item['titulo_ml'] ?? null,
                        'modo_envio_ml' => $item['modo_envio_ml'] ?? null,
                        'envio_ml' => $item['envio_ml'] ?? null,
                        'largo_ml' => $item['largo_ml'] ?? null,
                        'alto_ml' => $item['alto_ml'] ?? null,
                        'ancho_ml' => $item['ancho_ml'] ?? null,
                        'campos_categ_ml' => $item['campos_categ_ml'] ?? null,
                        'disponible_publ' => $item['disponible_publ'] ?? null,
                        'last_update_ml' => $item['last_update_ml'] ?? null,
                        'f_crea_ml' => $item['f_crea_ml'] ?? null,
                        'imagen_ml' => $item['imagen_ml'] ?? null,
                        'en_catalogo' => $item['en_catalogo'] ?? null,
                        'id_catalogo' => $item['id_catalogo'] ?? null,
                        'mat_peli' => $item['mat_peli'] ?? null,
                        'nombre' => $item['descr'] ?? null,
                        'price' => $item['ult_costo'] ?? null,
                        'stock' => $item['exist'] ?? null,
                        'remote_updated_at' => now(),
                        'sync_hash' => md5($item['cve_art'].$item['ult_costo'].$item['exist'] ?? '')
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                Log::error("Error sincronizando item {$index}: " . $e->getMessage());
                $errors[] = "Item {$index} (SKU: {$item['cve_art']}): " . $e->getMessage();
            }
        }

        return response()->json([
            'status' => $errors ? 'PARTIAL' : 'OK',
            'synced' => $synced,
            'total' => count($request->items),
            'errors' => $errors
        ]);
    }

    public function index(AspelSyncDataTable $dataTable)
    {
        return $dataTable->render('admin.product.sync-aspel.index');
    }
}
?>
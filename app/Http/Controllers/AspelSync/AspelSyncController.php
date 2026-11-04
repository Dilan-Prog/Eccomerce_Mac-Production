<?php

namespace App\Http\Controllers\AspelSync;

use App\Http\Controllers\Controller;
use App\Models\AspelSync;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\AspelSyncTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class AspelSyncController extends Controller
{
    public function __construct()
    {
        // sync() se excluye a propósito: la llama el script externo de Aspel
        // sin sesión de admin, vía routes/api.php. can-access-module:aspel
        // requiere $request->user() (que ahí siempre es null) y truena con
        // 500 en vez de proteger nada — la protección real pendiente es un
        // token compartido, no este middleware. index()/tableData()/export()
        // sí son del panel admin y sí deben quedar protegidos, con permisos
        // por acción (ver/exportar) — este controlador no tiene crear/editar/
        // borrar, es un listado de solo lectura sincronizado desde Aspel.
        $this->middleware('can-access-module:aspel,view')->only(['index', 'tableData']);
        $this->middleware('can-access-module:aspel,export')->only(['export']);
    }

    public function sync(Request $request)
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

        // Solo se valida la ESTRUCTURA del request (items debe ser una lista). Cada producto se valida por separado
        // dentro del ciclo: un producto con un dato invalido se rechaza y se reporta, pero NO tumba el lote completo.
        $request->validate(['items' => 'required|array']);
        $rules = $this->itemRules();

        $synced = 0;
        $errors = [];

        $rejected = 0;

        foreach ($request->items as $index => $item) {
            // Validacion POR REGISTRO: un producto invalido se salta y se reporta; los demas siguen.
            $validator = is_array($item) ? Validator::make($item, $rules) : null;
            if (!$validator || $validator->fails()) {
                $rejected++;
                $sku = is_array($item) ? ($item['cve_art'] ?? '?') : '?';
                $detail = $validator ? implode(' | ', array_slice($validator->errors()->all(), 0, 3)) : 'el registro no es un objeto';
                $errors[] = "Item {$index} (SKU: {$sku}): datos invalidos: " . $detail;
                continue;
            }

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
                        'campo_libre_modelo' => $item['campo_libre_modelo'] ?? null,
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
                        'price' => $item['ult_costo'] ?? null,
                        'stock' => $item['exist'] ?? null,
                        'remote_updated_at' => now(),
                        'sync_hash' => md5(($item['cve_art'] ?? '') . ($item['ult_costo'] ?? '') . ($item['exist'] ?? ''))
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                Log::error("Error sincronizando item {$index}: " . $e->getMessage());
                $errors[] = "Item {$index} (SKU: {$item['cve_art']}): " . $e->getMessage();
            }
        }

        if ($rejected > 0) {
            Log::warning('Aspel sync: registros rechazados por validacion', ['rejected' => $rejected, 'total' => count($request->items), 'muestra' => array_slice($errors, 0, 5)]);
        }

        $payload = [
            'status' => $errors ? 'PARTIAL' : 'OK',
            'synced' => $synced,
            'total' => count($request->items),
            'rejected' => $rejected,
            'errors' => $errors
        ];

        // Si NINGUN registro se pudo guardar porque todos eran invalidos, se mantiene el 422 de siempre (el script
        // de Aspel lo ve como falla). Cualquier otro caso (incluido PARTIAL) responde 200 como antes.
        $status = ($synced === 0 && $rejected > 0 && $rejected === count($request->items)) ? 422 : 200;

        return response()->json($payload, $status);
    }

    /** Reglas de validacion de UN producto del lote (antes eran 'items.*.campo' sobre todo el request). */
    private function itemRules(): array
    {
        return [
            'cve_art' => 'required|string|max:50',
            'descr' => 'nullable|string|max:255',
            'lin_prod' => 'nullable|string|max:50',
            'con_serie' => 'nullable|string|size:1',
            'uni_med' => 'nullable|string|max:50',
            'uni_emp' => 'nullable|numeric',
            'ctrl_alm' => 'nullable|string|max:50',
            'tiem_surt' => 'nullable|numeric',
            'stock_min' => 'nullable|numeric',
            'stock_max' => 'nullable|numeric',
            'exist' => 'nullable|numeric',
            'pend_surt' => 'nullable|numeric',
            'apart' => 'nullable|numeric',
            'comp_x_rec' => 'nullable|numeric',
            'tip_costeo' => 'nullable|string|size:1',
            'num_mon' => 'nullable|string|max:10',
            'costo_prom' => 'nullable|numeric',
            'ult_costo' => 'nullable|numeric',
            'fch_ultcom' => 'nullable|date_format:Y-m-d H:i:s',
            'fch_ultvta' => 'nullable|date_format:Y-m-d H:i:s',
            'cve_obs' => 'nullable|string|max:50',
            'tipo_ele' => 'nullable|string|size:1',
            'uni_alt' => 'nullable|string|max:50',
            'fac_conv' => 'nullable|numeric',
            'con_lote' => 'nullable|string|size:1',
            'con_pedimento' => 'nullable|string|size:1',
            'peso' => 'nullable|numeric',
            'volumen' => 'nullable|numeric',
            'cve_esqimpu' => 'nullable|string|max:50',
            'cve_bita' => 'nullable|string|max:50',
            'cuent_cont' => 'nullable|string|max:50',
            'cve_prodserv' => 'nullable|string|max:50',
            'cve_unidad' => 'nullable|string|max:50',
            'vtas_anl_c' => 'nullable|numeric',
            'vtas_anl_m' => 'nullable|numeric',
            'comp_anl_c' => 'nullable|numeric',
            'comp_anl_m' => 'nullable|numeric',
            'prefijo' => 'nullable|string|max:50',
            'talla' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'cve_imagen' => 'nullable|string|max:255',
            'campo_libre_modelo' => 'nullable|string|max:25',
            'blk_cst_ext' => 'nullable|string|size:1',
            'status' => 'nullable|string|size:1',
            'man_ieps' => 'nullable|string|size:1',
            'apl_man_imp' => 'nullable|integer',
            'cuota_ieps' => 'nullable|numeric',
            'apl_man_ieps' => 'nullable|string|size:1',
            'uuid' => 'nullable|string|max:50',
            'version_sinc' => 'nullable|date_format:Y-m-d H:i:s',
            'version_sinc_fecha_img' => 'nullable|date_format:Y-m-d H:i:s',
            'edo_publ_ml' => 'nullable|string|max:50',
            'cve_publ_ml' => 'nullable|string|max:50',
            'condicion_ml' => 'nullable|string|max:50',
            'tipo_publ_ml' => 'nullable|string|max:50',
            'categ_ml' => 'nullable|string|max:255',
            'cve_cate_ml' => 'nullable|string|max:50',
            'titulo_ml' => 'nullable|string|max:255',
            'modo_envio_ml' => 'nullable|string|max:50',
            'envio_ml' => 'nullable|string|size:1',
            'largo_ml' => 'nullable|numeric',
            'alto_ml' => 'nullable|numeric',
            'ancho_ml' => 'nullable|numeric',
            'campos_categ_ml' => 'nullable|json',
            'disponible_publ' => 'nullable|string|size:1',
            'last_update_ml' => 'nullable|date_format:Y-m-d H:i:s',
            'f_crea_ml' => 'nullable|date_format:Y-m-d H:i:s',
            'imagen_ml' => 'nullable|string|max:255',
            'en_catalogo' => 'nullable|string|max:50',
            'id_catalogo' => 'nullable|string|max:50',
            'mat_peli' => 'nullable|string|size:1',
        ];
    }

    public function index()
    {
        return view('admin-ui.sync-aspel.index');
    }

    /** JSON data source for the custom admin table (replaces AspelSyncDataTable). */
    public function tableData(Request $request, AspelSyncTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every synced product matching the current filter/search. */
    public function export(Request $request, AspelSyncTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Aspel Sincronizacion',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('aspel-sincronizacion.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "aspel-sincronizacion.{$extension}", $writerType);
    }

    // NOTE: No bulkAction() here — this listing is a read-only mirror of the
    // external Aspel SAE inventory sync (see sync() above, the only writer).
    // There is no delete/destroy route for aspel_products rows in this admin
    // module, so a bulk-delete action would have no real destructive
    // counterpart to mirror (unlike OrderController::bulkAction()).
}
?>
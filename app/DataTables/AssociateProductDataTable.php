<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AssociateProductDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('image', function($query){
                return $img = "<img width='70px' src='".asset($query->thumb_image)."' ></img>";
            })
            ->addColumn('price', function($query){
                // IVA desde settings
                $ivaValue = DB::table('general_settings')->value('iva_mexico') ?? 0;

                // Intentar obtener el precio desde la tabla precio_x_product_aspel
                // usando sku (products.sku) que corresponde a cve_art en Aspel
                $aspelPrecio = DB::table('precio_x_product_aspel')
                    ->where('cve_art', $query->sku)
                    ->where('cve_precio', 2)
                    ->value('precio');

                if (!is_null($aspelPrecio)) {
                    // Obtener info de moneda del producto Aspel (num_mon)
                    $aspelProd = DB::table('aspel_products')
                        ->where('cve_art', $query->sku)
                        ->first();

                    $exchange = 1.0;
                    if ($aspelProd && isset($aspelProd->num_mon) && intval($aspelProd->num_mon) === 2) {
                        // Si la moneda es USD (num_mon = 2), obtener tipo de cambio
                        $mon = DB::table('monedas_aspel')->where('num_moneda', $aspelProd->num_mon)->first();
                        if ($mon && !empty($mon->tipo_cambio)) {
                            $exchange = floatval($mon->tipo_cambio) ?: 1.0;
                        }
                    }

                    // Convertir a MXN si aplica
                    $converted = floatval($aspelPrecio) * $exchange;
                    // Aplicar IVA
                    $withIva = $converted * (1 + floatval($ivaValue) / 100);
                    return '$' . number_format(round($withIva, 2), 2, '.', ',') . ' MXN';
                }

                // Fallback al comportamiento actual si no hay precio Aspel
                if ($query->price_personalizated == 1) {
                    $base = floatval($query->price);
                } else {
                    $base = is_null($query->aspel_price) ? floatval($query->price) : floatval($query->aspel_price);
                }
                $withIva = $base * (1 + floatval($ivaValue) / 100);
                return '$' . number_format(round($withIva, 2), 2, '.', ',') . ' MXN';
            })
            ->addColumn('brand', function($query) {
                return $query->brand ? $query->brand->name : 'N/A';  // Retrieve brand name
            })
            ->addColumn('qty', function($query) {
                // Si qty_personalizated == 1 mostrar qty_aspel, sino mostrar qty
                if (isset($query->qty_personalizated) && $query->qty_personalizated == 0) {
                    return $query->qty_aspel;
                }
                return $query->qty;
            })
            ->rawColumns(['image'])
            ->setRowId('id');

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        // Solo productos cuyas marcas tengan status = 1
        return $model->newQuery()
            ->whereHas('brand', function ($q) {
                $q->where('status', 1);
            })
            ->with('brand');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('associateproduct-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(0, 'asc')
                    ->selectStyleSingle()
                    ->pageLength(100)
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ])
                    ->language([
                        'sSearch' => 'Buscar:', // Cambiar el texto de búsqueda
                        'oPaginate' => [
                            'sNext' => 'Siguiente',
                            'sPrevious' => 'Anterior',
                        ],
                        'sLengthMenu' => 'Mostrar _MENU_ Registros',
                        'sInfo' => 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        'sInfoEmpty' => 'Mostrando 0 a 0 de 0 registros',
                        'sInfoFiltered' => '(filtrado de _MAX_ registros totales)',
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            [
                'data' => 'sku',
                'title' => 'Clave', // Título personalizado
            ],
            [
                'data' => 'image',
                'title' => 'Imagen',
                'align' => 'center'
            ],
            [
                'data' => 'name',
                'title' => 'Producto',
                'width' => '25%'
            ],
            [
                'data' => 'productModel',
                'title' => 'Modelo',
                'width' => '20%'
            ],
            [
                'data' => 'brand',
                'title' => 'Marca', // Título personalizado

            ],
            [
                'data' => 'qty',
                'title' => 'Cantidad',
                'width' => '10%',
                'align' => 'start'
            ],
            [
                'data' => 'price',
                'title' => 'Precio',
                'width' => '10%'
            ],
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'AssociateProduct_' . date('YmdHis');
    }
}

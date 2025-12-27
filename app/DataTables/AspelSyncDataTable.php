<?php

namespace App\DataTables;

use App\Models\AspelSync;
use App\Models\PrecioXProductAspel;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use SebastianBergmann\Type\TrueType;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AspelSyncDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
        ->addColumn('num_mon', function($row) {
            $monedas = [
                '1' => 'MXN',
                '2' => 'USD',
            ];
            return $monedas[$row->num_mon] ?? $row->num_mon;
        })
        ->addColumn('precio_publico', function($row) {
            return number_format(round($row->precio_publico, 2),2,'.',',');
        })
        ->addColumn('precio_minimo', function($row) {
            return number_format($row->precio_minimo, 2, '.', ',');
        })
        ->addColumn('precio_liquidacion', function($row) {
            return number_format($row->precio_liquidacion, 2, '.', ',');
        })
        ->addColumn('precio_mayorista', function($row) {
            return number_format($row->precio_mayorista, 2, '.', ',');
        })
        ->addColumn('exist', function($row) {
            return (int)$row->exist;
        })
        ->filter(function ($query) {
            $search = request()->input('search.value');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('aspel_products.cve_art', 'like', "%{$search}%")
                      ->orWhere('aspel_products.descr', 'like', "%{$search}%")
                      ->orWhere('aspel_products.nombre', 'like', "%{$search}%");
                });
            }
        })
        ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AspelSync $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AspelSync $model): QueryBuilder
    {
        $priceSub = PrecioXProductAspel::select('cve_art')
            ->selectRaw('MAX(CASE WHEN cve_precio = 1 THEN precio END) AS precio_publico')
            ->selectRaw('MAX(CASE WHEN cve_precio = 2 THEN precio END) AS precio_minimo')
            ->selectRaw('MAX(CASE WHEN cve_precio = 3 THEN precio END) AS precio_liquidacion')
            ->selectRaw('MAX(CASE WHEN cve_precio = 4 THEN precio END) AS precio_mayorista')
            ->groupBy('cve_art');

        return $model->newQuery()
            ->select(
                'aspel_products.*',
                'pxp.precio_publico',
                'pxp.precio_minimo',
                'pxp.precio_liquidacion',
                'pxp.precio_mayorista'
            )
            ->leftJoinSub($priceSub, 'pxp', 'pxp.cve_art', '=', 'aspel_products.cve_art');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('aspelsync-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('colvis'),
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            // Columnas básicas (visibles por defecto)
            Column::make('cve_art')->title('CVE_ART')->className('text-center'),
            Column::make('descr')->title('Descripción'),
            Column::make('nombre')->title('Nombre Alias')->visible(false),
            // Column::make('ult_costo')->title('Precio (ult_costo)')->className('text-center'),
            // Column::make('costo_prom')->title('Costo Promedio')->visible(true)->className('text-center'),
            Column::make('precio_publico')->title('Precio Público')->visible(true)->className('text-center'),
            Column::make('precio_minimo')->title('Precio Mínimo')->visible(true)->className('text-center'),
            Column::make('precio_liquidacion')->title('Precio Liquidación')->visible(true)->className('text-center'),
            Column::make('precio_mayorista')->title('Precio Mayorista')->visible(true)->className('text-center'),
            // Column::make('tip_costeo')->title('Tipo Costeo')->visible(true)->className('text-center'),
            Column::make('num_mon')->title('Moneda')->visible(true)->className('text-center'),
            Column::make('exist')->title('Stock')->className('text-center'),
            // Column::make('status')->title('Estado')->className('text-center'),

            // // Columnas de inventario
            // Column::make('stock_min')->title('Stock Mín')->visible(false)->className('text-center'),
            // Column::make('stock_max')->title('Stock Máx')->visible(false)->className('text-center'),
            // Column::make('pend_surt')->title('Pendientes')->visible(false)->className('text-center'),
            // Column::make('apart')->title('Apartados')->visible(false)->className('text-center'),
            // Column::make('comp_x_rec')->title('Compras Pend.')->visible(false)->className('text-center'),

            // // Columnas de costos
           

            // // Columnas de control
            // Column::make('con_serie')->title('Con Serie')->visible(false)->className('text-center'),
            // Column::make('con_lote')->title('Con Lote')->visible(false)->className('text-center'),
            // Column::make('con_pedimento')->title('Con Pedimento')->visible(false)->className('text-center'),
            // Column::make('ctrl_alm')->title('Control Almacén')->visible(false),

            // // Columnas de clasificación
            // Column::make('lin_prod')->title('Línea Producto')->visible(false),
            // Column::make('tipo_ele')->title('Tipo Elemento')->visible(false),
            // Column::make('cve_obs')->title('Observación')->visible(false),

            // // Columnas de dimensiones
            // Column::make('peso')->title('Peso (kg)')->visible(false)->className('text-center'),
            // Column::make('volumen')->title('Volumen (m³)')->visible(false)->className('text-center'),
            // Column::make('uni_med')->title('Unidad Medida')->visible(false),
            // Column::make('uni_emp')->title('Unidad Empaque')->visible(false)->className('text-center'),
            // Column::make('uni_alt')->title('Unidad Alternativa')->visible(false),
            // Column::make('fac_conv')->title('Factor Conv.')->visible(false)->className('text-center'),

            // // Columnas de atributos
            // Column::make('prefijo')->title('Modelo/Prefijo')->visible(false),
            // Column::make('talla')->title('Talla')->visible(false),
            // Column::make('color')->title('Color')->visible(false),

            // // Columnas de fechas
            // Column::make('fch_ultcom')->title('Última Compra')->visible(false)->className('text-center'),
            // Column::make('fch_ultvta')->title('Última Venta')->visible(false)->className('text-center'),

            // // IEPS
            // Column::make('man_ieps')->title('Maneja IEPS')->visible(false)->className('text-center'),
            // Column::make('cuota_ieps')->title('Cuota IEPS')->visible(false)->className('text-center'),

            // // Mercado Libre
            // Column::make('edo_publ_ml')->title('Estado ML')->visible(false),
            // Column::make('condicion_ml')->title('Condición ML')->visible(false),
            // Column::make('categ_ml')->title('Categoría ML')->visible(false),
            // Column::make('titulo_ml')->title('Título ML')->visible(false),

            // // Control de cambios
            // Column::make('remote_updated_at')->title('Última Sincronización')->visible(false)->className('text-center'),

            // Acción
            // Column::make('action')->title('Acción')->orderable(false)->searchable(false)->className('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'AspelSync_' . date('YmdHis');
    }
}

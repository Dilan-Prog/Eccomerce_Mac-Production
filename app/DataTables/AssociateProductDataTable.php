<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
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
            ->setRowId('id');
            
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
{
    return Product::with('brand')
    ->where('sku', '!=', '')
        ->whereHas('brand', function ($query) {
            $query->where('name', 'Honeywell'); // Filtrar productos de la marca "Honeywell"
        });
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
            Column::make('id'),
            Column::make('name')->title("Nombre"),
            Column::make('productModel')->title("Modelo"),
            Column::make('sku')->title("Clave"),
            Column::make('qty')->title("Existencias"),
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

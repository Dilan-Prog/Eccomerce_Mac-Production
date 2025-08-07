<?php

namespace App\DataTables;

use App\Models\ProductVariantCombinations;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ProductVariantCombinationsDataTables extends DataTable
{

    protected $productId;

    public function setProductId($productId)
    {
        $this->productId = $productId;
    }
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function($query){
                $editBtn = "<a href='".route('admin.products-variant-combinations.edit', $query->id)."' class='btn btn-primary'>Editar</a>";
                $deleteBtn = "<a href='".route('admin.products-variant-combinations.destroy', $query->id)."' class='btn btn-danger m-2 delete-item'>Eliminar</a>";
                return $editBtn.$deleteBtn;
            })
            ->addColumn('is_default', function($query){
                return $query->is_default ? 'Si' : 'No';
            })
            ->addColumn('status', function($query){
                return $query->status ? 'Activo' : 'Inactivo';
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ProductVariantCombinationsDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProductVariantCombinations $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('productvariantcombinationsdatatables-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
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
            Column::make('id')->title('Id')->width(50),
            Column::make('name')->title('Nombre Combinacion'),
            Column::make('sku')->title('Clave SKU')->width(125),
            Column::make('price')->title('Precio')->width(100),
            Column::make('qty')->title('Stock')->width(100),
            Column::make('is_default')->title('Por Defecto'),
            Column::make('status')->title('Estado'),
            Column::computed('action')->title('Acciones')->width(250),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'ProductVariantCombinations_' . date('YmdHis');
    }
}

<?php

namespace App\DataTables;

use App\Models\ProductMoreEccomerce;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ProductMoreEccomerceDataTable extends DataTable
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
            ->addColumn('action', function($query){
                
                $deleteBtn = "<a href='".route('admin.products-more-eccomerce.destroy',$query->id)."' class='btn btn-danger m-2 delete-item'>Borrar</a>";
                $editBtn = "<a href='".route('admin.products-more-eccomerce.edit', $query->id)."' class='btn btn-primary'>Editar</a>";
                return $deleteBtn.$editBtn;
            })
            ->addColumn('status', function($query){
                if($query->status == 1){
                    
                                    $button = '<label class="custom-switch mt-2">
                                    <input type="checkbox"checked name="custom-switch-checkbox" data-id="'.$query->id.'" class="custom-switch-input change-status">
                                    <span class="custom-switch-indicator"></span>
                                    </label>';

                }else if($query->status == 0){
                    $button = '<label class="custom-switch mt-2">
                                    <input type="checkbox" name="custom-switch-checkbox" data-id="'.$query->id.'" class="custom-switch-input change-status">
                                    <span class="custom-switch-indicator"></span>
                                    </label>';
                }
                return $button;
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ProductMoreEccomerce $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProductMoreEccomerce $model): QueryBuilder
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
                    ->setTableId('productmoreeccomerce-table')
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
            [
                'data' => 'nameEccomerce',
                'title' => 'Nombre del Comercio',
            ],
            [
                'data' => 'linkProduct',
                'title' => 'Link del Producto',
                'width' => '40%',
            ],
            [
                'data' => 'status',
                'title' => 'Estado',
            ],
            Column::computed('action')
                  ->title('Acciones')
                  ->exportable(false)
                  ->printable(false)
                  ->width(150)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'ProductMoreEccomerce_' . date('YmdHis');
    }
}

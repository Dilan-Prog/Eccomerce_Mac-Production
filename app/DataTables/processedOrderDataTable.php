<?php

namespace App\DataTables;

use App\Models\Order;
use App\Models\PendingOrder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class processedOrderDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
        ->addColumn('Acciones', function($query){
            $showBtn = "<a href='".route('admin.order.show', $query->id)."' class='btn btn-primary'>Detalles</a>";
            $deleteBtn = "<a href='".route('admin.order.destroy', $query->id)."' class='btn btn-danger ml-2 mr-2 delete-item'>Borrar</a>";

            return $showBtn.$deleteBtn;
        })
            ->addColumn('Nombre', function($query){
                return $query->user->name;
            })
            ->addColumn('N.Orden', function($query){
                return $query->invocie_id;
            })
            ->addColumn('Fecha', function($query){
                return date('d-M-Y', strtotime($query->created_at));
            })
            ->addColumn('U/Producto', function($query){
                return $query->product_qty;
            })
            ->addColumn('Pago', function($query){
                return $query->currency_icon.number_format($query->amount, 2, '.', ',')." Mxn";
            })
            ->addColumn('Metodo de Pago', function($query){
                return $query->payment_method;
            })
            ->addColumn('Estado de Pago', function($query){
                if($query->payment_status === 1){
                    return "<span class='badge bg-success'>Completada</span>";
                }else {
                    return "<span class='badge bg-warning'>Pendiente</span>";
                }
            })
            ->addColumn('Estado De Orden', function($query){
                switch ($query->order_status) {
                    case 'pending':
                        return "<span class='badge bg-warning'>Pendiente</span>";
                        break;
                    case 'processed_and_ready_to_ship':
                        return "<span class='badge bg-info'>Procesado y listo para enviar</span>";
                        break;
                    case 'dropped_off':
                        return "<span class='badge bg-info'>Entregado al transportista</span>";
                        break;
                    case 'shipped':
                        return "<span class='badge bg-info'>Enviado</span>";
                        break;
                    case 'out_for_delivery':
                        return "<span class='badge bg-primary'>En ruta de entrega</span>";
                        break;
                    case 'delivered':
                        return "<span class='badge bg-success'>Entregado</span>";
                        break;
                    case 'canceled':
                        return "<span class='badge bg-danger'>Cancelado</span>";
                        break;
                    default:
                        # code...
                        break;
                }

            })


            ->rawColumns(['Acciones','Estado De Orden', 'Estado de Pago', 'N.Orden'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Order $model): QueryBuilder
    {
        return $model->where('order_status', 'processed_and_ready_to_ship')->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('pendingorder-table')
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
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('N.Orden'),
            Column::make('Nombre'),
            Column::make('Fecha'),
            Column::make('U/Producto')->width(100),
            Column::make('Pago'),
            Column::make('Estado De Orden'),
            Column::make('Metodo de Pago')->width(100),
            Column::make('Estado de Pago')->width(120),
            Column::computed('Acciones')
                  ->exportable(false)
                  ->printable(false)
                  ->width(180)
                  ->addClass('text-center'),

        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'PendingOrder_' . date('YmdHis');
    }
}

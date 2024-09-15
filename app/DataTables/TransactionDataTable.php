<?php

namespace App\DataTables;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class TransactionDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'transaction.action')
            ->addColumn('Factura Id', function($query){
                return '#'.$query->order->invocie_id;
            })
            ->addColumn('Transaccion Id', function($query){
                return $query->transaction_id;
            })
            ->addColumn('Metodo De Pago', function($query){
                return $query->payment_method;
            })
            ->addColumn('Cantidad con moneda', function($query){
                return number_format($query->amount, 2 , '.', ',').' '.$query->order->currency_name;
            })
            ->addColumn('Cantidad con moneda Real', function($query){
                return '$'.number_format($query->amount, 2 , '.', ',').' '.$query->amount_real_currency_name.'Mxn';
            })
            ->filterColumn('Factura Id', function($query, $keyword){
                $query->whereHas('order', function($query) use ($keyword){
                    $query->where('invocie_id', 'like', "%$keyword%");
                });
            })
            ->rawColumns(['Factura Id', 'Transaccion Id', 'Metodo De Pago'. 'Cantidad con moneda', 'Cantidad con moneda Real'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Transaction $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('transaction-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(0)
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
            Column::make('Factura Id'),
            Column::make('Transaccion Id'),
            Column::make('Metodo De Pago'),
            Column::make('Cantidad con moneda'),
            Column::make('Cantidad con moneda Real'),

        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Transaction_' . date('YmdHis');
    }
}

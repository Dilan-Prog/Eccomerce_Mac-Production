<?php

namespace App\DataTables;

use App\Models\TrackConversion;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class TrackConversionDataTable extends DataTable
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
            
            ->addColumn('gclid', function ($query){
                return $query->gclid;
            })
            ->addColumn('type', function ($query) {
                return $query->type;
            })
            ->addColumn('utm_source', function ($query) {
                return $query->utm_source;
            })
            ->addColumn('utm_medium', function ($query) {
                return $query->utm_medium;
            })
            ->addColumn('utm_campaign', function ($query) {
                return $query->utm_campaign;
            })
            ->addColumn('landing_page', function ($query) {
                return $query->landing_page;
            })
            ->addColumn('created_at', function ($query) {
                return $query->created_at->format('Y-m-d h:i A');
            })

            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\TrackConversion $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(TrackConversion $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('trackconversion-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(6, 'desc')
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
                'data' => 'gclid',
                'title' => 'Google ID (gclid)', // Título personalizado
            ],
            [
                'data' => 'type',
                'title' => 'Tipo de Conversion', // Título personalizado
            ],
            [
                'data' => 'utm_source',
                'title' => 'utm_source', // Título personalizado
            ],
            [
                'data' => 'utm_medium',
                'title' => 'utm_medium', // Título personalizado
            ],
            [
                'data' => 'utm_campaign',
                'title' => 'Campaña', // Título personalizado
            ],
            [
                'data' => 'landing_page',
                'title' => 'Url Conversion', // Título personalizado
            ],
            [
                'data' => 'created_at',
                'title' => 'Fecha Registrada', // Título personalizado
            ],


            // Column::computed('action')
            //       ->title('Acciones')
            //       ->exportable(false)
            //       ->printable(false)
            //       ->width(300)
            //       ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'TrackConversion_' . date('YmdHis');
    }
}

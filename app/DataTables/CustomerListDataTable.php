<?php

namespace App\DataTables;

use App\Models\CustomerList;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CustomerListDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('status', function ($row) {
                $checked = $row->status == 'active' ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                    <input type="checkbox" ' . $checked . ' name="custom-switch-checkbox" data-id="' . $row->id . '" class="custom-switch-input change-status">
                    <span class="custom-switch-indicator"></span>
                </label>';
            })
            ->addColumn('account_type', function ($row) {
                if ($row->account_type === 'b2b') {
                    return '<span class="badge badge-primary">B2B</span>';
                }
                return '<span class="badge badge-secondary">Personal</span>';
            })
            ->addColumn('rfc', function ($row) {
                return $row->rfc ? '<code>' . e($row->rfc) . '</code>' : '<span class="text-muted">—</span>';
            })
            ->addColumn('tipo_cliente', function ($row) {
                $labels = [
                    'revendedor'  => 'Revendedor',
                    'tecnico'     => 'Técnico',
                    'empresa'     => 'Empresa',
                    'contratista' => 'Contratista',
                ];
                return $row->tipo_cliente
                    ? e($labels[$row->tipo_cliente] ?? $row->tipo_cliente)
                    : '<span class="text-muted">—</span>';
            })
            ->addColumn('csf_document', function ($row) {
                $uploadBtn = '<button type="button" class="btn btn-xs btn-outline-secondary btn-csf-upload ml-1"
                    data-id="' . $row->id . '" data-name="' . e($row->name) . '"
                    title="Subir / reemplazar CSF">
                    <i class="fas fa-upload"></i>
                </button>';

                if (!$row->csf_path) {
                    return '<span class="badge badge-light text-muted">Sin CSF</span>' . $uploadBtn;
                }

                $viewUrl = route('admin.customer.csf.view', $row->id);
                $badge   = $row->b2b_status === 'verified'
                    ? '<span class="badge badge-success mr-1">Verificado</span>'
                    : '<span class="badge badge-warning mr-1">Pendiente</span>';

                return $badge .
                    '<a href="' . e($viewUrl) . '" target="_blank" class="btn btn-xs btn-info btn-csf-preview" data-url="' . e($viewUrl) . '">
                        <i class="fas fa-file-pdf"></i> Ver
                    </a>' . $uploadBtn;
            })
            ->rawColumns(['status', 'account_type', 'rfc', 'tipo_cliente', 'csf_document'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->where('role', 'user')->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('customerlist-table')
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
            Column::make('id')->width(60),
            Column::make('name')->title('Nombre'),
            Column::make('email')->title('Correo'),
            Column::make('account_type')->title('Tipo cuenta')->searchable(false),
            Column::make('rfc')->title('RFC')->searchable(false),
            Column::make('tipo_cliente')->title('Perfil')->searchable(false),
            Column::make('csf_document')->title('CSF')->searchable(false)->orderable(false),
            Column::make('status')->title('Estado')->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'CustomerList_' . date('YmdHis');
    }
}

<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Category;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces app/DataTables/CategoryDataTable.php.
 */
class CategoryTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return Category::query();
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'Id',
                'type' => 'mono',
                'sortable' => true,
            ],
            [
                'key' => 'icon',
                'label' => 'Icono',
                'render' => fn (Model $row) => $row->icon,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => $row->status == 1
                    ? ['label' => 'Activo', 'tone' => 'success']
                    : ['label' => 'Inactivo', 'tone' => 'critical'],
            ],
            [
                'key' => 'action',
                'label' => 'Acción',
                'type' => 'actions',
            ],
        ];
    }

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activos', 'label' => 'Activo', 'apply' => fn (Builder $q) => $q->where('status', 1)],
            ['key' => 'inactivos', 'label' => 'Inactivo', 'apply' => fn (Builder $q) => $q->where('status', 0)],
        ];
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar categoría',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-tag',
                    'fragmentUrl' => route('admin.category.edit-fragment', $row->id),
                    'submitUrl' => route('admin.category.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.category.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Estado', 'value' => $row->status == 1 ? 'Activo' : 'Inactivo'],
                ],
            ],
        ];
    }
}

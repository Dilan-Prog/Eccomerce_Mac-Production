<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Subcategory;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces App\DataTables\SubcategoryDataTable.
 */
class SubcategoryTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return Subcategory::query()->with('category');
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'mono',
                'sortable' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'category',
                'label' => 'Categoria',
                'render' => fn (Model $row) => $row->category->name ?? '',
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
                'key' => 'actions',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"));
        });
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar sub categoría',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-tag',
                    'fragmentUrl' => route('admin.sub-category.edit-fragment', $row->id),
                    'submitUrl' => route('admin.sub-category.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.sub-category.destroy', $row->id),
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

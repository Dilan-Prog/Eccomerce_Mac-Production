<?php

namespace App\Support\AdminTable\Queries;

use App\Models\ChildCategory;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces ChildCategoryDataTable (app/DataTables/ChildCategoryDataTable.php).
 *
 * Note: the old view rendered `status` as a live toggle switch (PUT to
 * admin.child-category.change-status on click). The AdminTable column-type
 * set (text/mono/badge/date/currency/person/actions — see
 * public/admin-ui/js/table/column-types.js) has no interactive toggle type,
 * so status is surfaced as a read-only badge here (Activo/Inactivo), same as
 * OrderTableQuery::payment_status. The change-status route/controller method
 * is left untouched and still reachable for any other caller.
 */
class ChildCategoryTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return ChildCategory::query()->with(['category', 'subCategory']);
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'mono',
                'sortable' => true,
                'width' => 90,
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
                'key' => 'sub_category',
                'label' => 'Subcategoria',
                'render' => fn (Model $row) => $row->subCategory->name ?? '',
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->status == 1
                    ? ['label' => 'Activo', 'tone' => 'success']
                    : ['label' => 'Inactivo', 'tone' => 'warning'],
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
                ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"))
                ->orWhereHas('subCategory', fn (Builder $s) => $s->where('name', 'like', "%{$term}%"));
        });
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar categoría secundaria',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-tag',
                    'fragmentUrl' => route('admin.child-category.edit-fragment', $row->id),
                    'submitUrl' => route('admin.child-category.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.child-category.destroy', $row->id),
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

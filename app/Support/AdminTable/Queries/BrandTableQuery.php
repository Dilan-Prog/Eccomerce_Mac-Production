<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Brand;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces app/DataTables/BrandDataTable.php.
 *
 * Note: the old table rendered an actual `<img>` thumbnail for the `logo`
 * column. The AdminTable column-type set (text/mono/badge/date/currency/
 * person/actions — see public/admin-ui/js/table/column-types.js) has no
 * image renderer, so the logo thumbnail is intentionally not ported as a
 * column here; the logo is still viewable/editable from the row's "Editar"
 * action. Nothing else about the old columns/labels was changed.
 */
class BrandTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return Brand::query();
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'mono',
                'sortable' => true,
                'width' => 100,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'is_featured',
                'label' => 'Destacado',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->is_featured == 1
                    ? ['label' => 'Si', 'tone' => 'success']
                    : ['label' => 'No', 'tone' => 'critical'],
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
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

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activas', 'label' => 'Activo', 'apply' => fn (Builder $q) => $q->where('status', 1)],
            ['key' => 'inactivas', 'label' => 'Inactivo', 'apply' => fn (Builder $q) => $q->where('status', 0)],
            ['key' => 'destacadas', 'label' => 'Destacado', 'apply' => fn (Builder $q) => $q->where('is_featured', 1)],
        ];
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar marca',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-copyright',
                    'fragmentUrl' => route('admin.brand.edit-fragment', $row->id),
                    'submitUrl' => route('admin.brand.update', $row->id),
                    'method' => 'PUT',
                    'sidebar' => true,
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.brand.destroy', $row->id),
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

<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Slider;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces app/DataTables/SliderDataTable.php.
 *
 * Note: the old table rendered an actual `<img>` thumbnail for the `banner`
 * column. The AdminTable column-type set (text/mono/badge/date/currency/
 * person/actions — see public/admin-ui/js/table/column-types.js) has no
 * image renderer, so the banner thumbnail is intentionally not ported as a
 * column here (same call made for BrandTableQuery::logo); the banner is
 * still viewable/editable from the row's "Editar" action. Nothing else
 * about the old columns/labels was changed.
 */
class SliderTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return Slider::query();
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
                'key' => 'title',
                'label' => 'Titulo',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'serial',
                'label' => 'Serial',
                'sortable' => true,
                'searchable' => true,
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

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar slider',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-images',
                    'fragmentUrl' => route('admin.slider.edit-fragment', $row->id),
                    'submitUrl' => route('admin.slider.update', $row->id),
                    'method' => 'PUT',
                    'sidebar' => true,
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.slider.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Título', 'value' => $row->title],
                    ['label' => 'Estado', 'value' => $row->status == 1 ? 'Activo' : 'Inactivo'],
                ],
            ],
        ];
    }
}

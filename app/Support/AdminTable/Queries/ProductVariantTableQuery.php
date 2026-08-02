<?php

namespace App\Support\AdminTable\Queries;

use App\Models\ProductVariant;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces ProductVariantDataTable (app/DataTables/ProductVariantDataTable.php).
 *
 * Note: the old query() was `$model->newQuery()` with NO `where('product_id', ...)`
 * filter, even though the index page is opened per-product (?product={id}) and the
 * page header/"Crear Nuevo" link are scoped to that product. That means the legacy
 * listing (and this port, which preserves it as-is per the migration's "don't fix
 * pre-existing business rules" rule) shows ProductVariant rows from EVERY product,
 * not just the one being viewed. Flagging this for the team; not silently fixed here.
 *
 * Note: the old view rendered `status` as a live toggle switch (PUT to
 * admin.products-variant.change-status on click). The AdminTable column-type set
 * (text/mono/badge/date/currency/person/actions — see
 * public/admin-ui/js/table/column-types.js) has no interactive toggle type, so
 * status is surfaced as a read-only badge here (Activo/Inactivo), same as
 * ChildCategoryTableQuery::status. The change-status route/controller method is
 * left untouched and still reachable for any other caller.
 */
class ProductVariantTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return ProductVariant::query();
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

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Variantes',
                'url' => route('admin.products-variant-item.index', [
                    'productId' => request()->product,
                    'variantId' => $row->id,
                ]),
            ],
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar variante',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-layer-group',
                    'fragmentUrl' => route('admin.products-variant.edit-fragment', $row->id),
                    'submitUrl' => route('admin.products-variant.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.products-variant.destroy', $row->id),
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

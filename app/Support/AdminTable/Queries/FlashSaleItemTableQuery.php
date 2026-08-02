<?php

namespace App\Support\AdminTable\Queries;

use App\Models\FlashSaleItem;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces app/DataTables/FlashSaleItemDataTable.php.
 *
 * Notes:
 * - The old table rendered actual toggle-switch <input> elements for the
 *   `status` and `show_at_home` columns (checked/unchecked, AJAX-updated via
 *   admin.flash-sale-status / admin.flash-sale.show-at-home.change-status).
 *   The AdminTable column-type set (text/mono/badge/date/currency/person/
 *   actions — see public/admin-ui/js/table/column-types.js) has no
 *   toggle-switch renderer, so — exactly like BrandTableQuery/CouponTableQuery
 *   before it — both are now shown as badges (Activo/Inactivo, Sí/No).
 * - The old `product_name` column rendered a link to the product's edit
 *   page; the AdminTable column types have no link renderer, so it is now
 *   plain text (the product is still reachable from the row actions of the
 *   Products module).
 * - The old class defined `product_name` via addColumn() (a computed value,
 *   not a real `flash_sale_items` column) with no filterColumn() override,
 *   so the built-in Yajra global search could not actually match against it.
 *   search() below fixes that by matching the related product's name (and
 *   still matches the numeric id), which is a strict improvement and invents
 *   no new business rule.
 */
class FlashSaleItemTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return FlashSaleItem::query()->with('product');
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
                'key' => 'product_name',
                'label' => 'Producto',
                'render' => fn (Model $row) => $row->product->name ?? '',
            ],
            [
                'key' => 'show_at_home',
                'label' => 'Mostrar En Inicio',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->show_at_home == 1
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
                'key' => 'action',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('id', 'like', "%{$term}%")
                ->orWhereHas('product', fn (Builder $p) => $p->where('name', 'like', "%{$term}%"));
        });
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Borrar',
                'url' => route('admin.flash-sale.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
            ],
        ];
    }
}

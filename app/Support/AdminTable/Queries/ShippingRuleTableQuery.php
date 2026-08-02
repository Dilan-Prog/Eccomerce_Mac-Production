<?php

namespace App\Support\AdminTable\Queries;

use App\Models\GeneralSetting;
use App\Models\ShippingRule;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces app/DataTables/ShippingRuleDataTable.php.
 *
 * Note: the old table rendered an actual toggle-switch <input> for the
 * `status` column (checked/unchecked, AJAX-updated via admin.shipping-rule.change-status).
 * The AdminTable column-type set (text/mono/badge/date/currency/person/actions
 * — see public/admin-ui/js/table/column-types.js) has no toggle-switch
 * renderer, so — exactly like BrandTableQuery/CouponTableQuery before it —
 * status is now shown as an Activo/Inactivo badge; the value is still
 * editable from the row's "Editar" action. Nothing else about the old
 * columns/labels/business rules was changed.
 */
class ShippingRuleTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return ShippingRule::query();
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
                'key' => 'type',
                'label' => 'Tipo',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->type == 'min_cost'
                    ? ['label' => 'Cantidad mínima de pedido', 'tone' => 'success']
                    : ['label' => 'Costo fijo', 'tone' => 'info'],
            ],
            [
                'key' => 'min_cost',
                'label' => 'Cantidad Minima',
                'render' => fn (Model $row) => $row->type == 'min_cost'
                    ? GeneralSetting::first()->currency_icon . $row->min_cost
                    : GeneralSetting::first()->currency_icon . '0',
            ],
            [
                'key' => 'cost',
                'label' => 'Costo',
                'render' => fn (Model $row) => GeneralSetting::first()->currency_icon . $row->cost,
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

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activas', 'label' => 'Activo', 'apply' => fn (Builder $q) => $q->where('status', 1)],
            ['key' => 'inactivas', 'label' => 'Inactivo', 'apply' => fn (Builder $q) => $q->where('status', 0)],
        ];
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar regla de envío',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-truck',
                    'fragmentUrl' => route('admin.shipping-rule.edit-fragment', $row->id),
                    'submitUrl' => route('admin.shipping-rule.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.shipping-rule.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Costo', 'value' => (GeneralSetting::first()->currency_icon ?? '') . $row->cost],
                ],
            ],
        ];
    }
}

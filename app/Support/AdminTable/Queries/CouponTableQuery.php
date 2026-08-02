<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Coupon;
use App\Models\GeneralSetting;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces app/DataTables/CouponDataTable.php.
 *
 * Note: the old table rendered an actual toggle-switch <input> for the
 * `status` column (checked/unchecked, AJAX-updated via admin.coupons.change-status).
 * The AdminTable column-type set (text/mono/badge/date/currency/person/actions
 * — see public/admin-ui/js/table/column-types.js) has no toggle-switch
 * renderer, so — exactly like BrandTableQuery/CategoryTableQuery before it —
 * status is now shown as an Activo/Inactivo badge; the value is still
 * editable from the row's "Editar" action. Nothing else about the old
 * columns/labels/business rules was changed.
 */
class CouponTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return Coupon::query();
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
                'key' => 'discount_type',
                'label' => 'Tipo De Descuento',
                'sortable' => true,
            ],
            [
                'key' => 'discount',
                'label' => 'Descuento',
                'render' => fn (Model $row) => GeneralSetting::first()->currency_icon . $row->discount,
            ],
            [
                'key' => 'start_date',
                'label' => 'Fecha Inicio',
                'type' => 'date',
                'sortable' => true,
            ],
            [
                'key' => 'end_date',
                'label' => 'Fecha Fin',
                'type' => 'date',
                'sortable' => true,
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
                'label' => 'Acciones',
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
                    'title' => 'Editar cupón',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-ticket',
                    'fragmentUrl' => route('admin.coupons.edit-fragment', $row->id),
                    'submitUrl' => route('admin.coupons.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.coupons.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Código', 'value' => $row->cod],
                ],
            ],
        ];
    }
}

<?php

namespace App\Support\AdminTable\Queries;

use App\Models\BankAccount;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin table for the "Cuentas Bancarias" list used to configure bank
 * accounts shown in the cotizaciones PDF. Mirrors ShippingRuleTableQuery.
 */
class BankAccountTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return BankAccount::query();
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'mono',
                'sortable' => true,
                'width' => 80,
            ],
            [
                'key' => 'banco',
                'label' => 'Banco',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'titular',
                'label' => 'Titular',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'numero_cuenta',
                'label' => 'No. de Cuenta',
            ],
            [
                'key' => 'clabe',
                'label' => 'CLABE',
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
                    'title' => 'Editar cuenta bancaria',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-university',
                    'fragmentUrl' => route('admin.bank-account.edit-fragment', $row->id),
                    'submitUrl' => route('admin.bank-account.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.bank-account.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Banco', 'value' => $row->banco],
                    ['label' => 'Titular', 'value' => $row->titular],
                ],
            ],
        ];
    }
}

<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Transaction;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces App\DataTables\TransactionDataTable. Ported columns/labels/order
 * exactly as they were (including the pre-existing `amount_real_currency_name`
 * typo/bug from the old class — that attribute does not exist on Transaction,
 * see database/migrations/2024_06_29_022900_create_transactions_table.php,
 * so it always rendered as an empty string there; kept as-is per the
 * incremental migration strategy).
 */
class TransactionTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return Transaction::query()->with('order');
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
                'key' => 'Factura Id',
                'label' => 'Factura Id',
                'type' => 'mono',
                'render' => fn (Model $row) => '#' . ($row->order->invocie_id ?? ''),
            ],
            [
                'key' => 'transaction_id',
                'label' => 'Transaccion Id',
                'type' => 'mono',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'payment_method',
                'label' => 'Metodo De Pago',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'Cantidad con moneda',
                'label' => 'Cantidad con moneda',
                'render' => fn (Model $row) => number_format((float) $row->amount, 2, '.', ',') . ' ' . ($row->order->currency_name ?? ''),
            ],
            [
                'key' => 'Cantidad con moneda Real',
                'label' => 'Cantidad con moneda Real',
                'render' => fn (Model $row) => '$' . number_format((float) $row->amount, 2, '.', ',') . ' ' . ($row->amount_real_currency_name ?? '') . 'Mxn',
            ],
        ];
    }

    /** Replaces the old filterColumn('Factura Id', ...) plus the default per-column search. */
    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('transaction_id', 'like', "%{$term}%")
                ->orWhere('payment_method', 'like', "%{$term}%")
                ->orWhereHas('order', fn (Builder $o) => $o->where('invocie_id', 'like', "%{$term}%"));
        });
    }
}

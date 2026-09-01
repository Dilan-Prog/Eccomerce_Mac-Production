<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Order;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces OrderDataTable + the 7 sibling *OrderDataTable classes
 * (PendingOrderDataTable, processedOrderDataTable, droppedOffOrderDataTable,
 * shippedOrderDataTable, outForDeliveryOrderDataTable, deliveredOrderDataTable,
 * canceledOrderDataTable) — those were the same table filtered by
 * `order_status`; here that becomes one AdminTable with saved-filter tabs.
 */
class OrderTableQuery extends AdminTableQuery
{
    private const STATUS_META = [
        'pending' => ['label' => 'Pendiente', 'tone' => 'warning'],
        'pendiente_de_surtir' => ['label' => 'Pendiente de surtir', 'tone' => 'warning'],
        'processed_and_ready_to_ship' => ['label' => 'Procesado y listo para enviar', 'tone' => 'info'],
        'dropped_off' => ['label' => 'Entregado al transportista', 'tone' => 'info'],
        'shipped' => ['label' => 'Enviado', 'tone' => 'info'],
        'out_for_delivery' => ['label' => 'En ruta de entrega', 'tone' => 'info'],
        'delivered' => ['label' => 'Entregado', 'tone' => 'success'],
        'canceled' => ['label' => 'Cancelado', 'tone' => 'critical'],
    ];

    private const FILTER_STATUS = [
        'pendiente' => 'pending',
        'pendiente_de_surtir' => 'pendiente_de_surtir',
        'procesado' => 'processed_and_ready_to_ship',
        'entregado_transportista' => 'dropped_off',
        'enviado' => 'shipped',
        'en_ruta' => 'out_for_delivery',
        'entregado' => 'delivered',
        'cancelado' => 'canceled',
    ];

    public function baseQuery(): Builder
    {
        return Order::query()->with('user');
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'invocie_id',
                'label' => 'N.Orden',
                'type' => 'mono',
                'sortable' => true,
                'searchable' => true,
                'render' => fn (Model $row) => '#' . $row->invocie_id,
            ],
            [
                'key' => 'user_name',
                'label' => 'Nombre',
                'type' => 'person',
                'render' => fn (Model $row) => [
                    'title' => $row->user->name ?? '',
                    'subtitle' => $row->user->email ?? '',
                ],
            ],
            [
                'key' => 'created_at',
                'label' => 'Fecha',
                'type' => 'date',
                'sortable' => true,
            ],
            [
                'key' => 'product_qty',
                'label' => 'U/Producto',
                'sortable' => true,
            ],
            [
                'key' => 'amount',
                'label' => 'Pago',
                'sortable' => true,
                'render' => fn (Model $row) => $row->currency_icon . number_format((float) $row->amount, 2, '.', ',') . ' Mxn',
            ],
            [
                'key' => 'payment_method',
                'label' => 'Metodo de Pago',
                'sortable' => true,
            ],
            [
                'key' => 'order_status',
                'label' => 'Estado De Orden',
                'type' => 'badge',
                'render' => fn (Model $row) => self::STATUS_META[$row->order_status] ?? ['label' => $row->order_status, 'tone' => 'info'],
            ],
            [
                'key' => 'payment_status',
                'label' => 'Estado de Pago',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->payment_status === 1
                    ? ['label' => 'Completada', 'tone' => 'success']
                    : ['label' => 'Pendiente', 'tone' => 'warning'],
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
        $filters = [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
        ];

        foreach (self::FILTER_STATUS as $key => $status) {
            $filters[] = [
                'key' => $key,
                'label' => self::STATUS_META[$status]['label'],
                'apply' => fn (Builder $q) => $q->where('order_status', $status),
            ];
        }

        return $filters;
    }

    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('invocie_id', 'like', "%{$term}%")
                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$term}%"));
        });
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Detalles',
                'url' => route('admin.order.show', $row->id),
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.order.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
            ],
        ];
    }
}

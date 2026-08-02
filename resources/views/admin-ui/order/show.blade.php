@php
    $address = json_decode($order->order_address);
    $shipping = json_decode($order->shipping_method);
    $coupon = json_decode($order->coupon);

    $statusTone = [
        'pending' => 'warning',
        'processed_and_ready_to_ship' => 'info',
        'dropped_off' => 'info',
        'shipped' => 'info',
        'out_for_delivery' => 'purple',
        'delivered' => 'success',
        'canceled' => 'critical',
    ][$order->order_status] ?? 'info';
@endphp
@extends('admin-ui.layouts.master')

@section('title', 'Orden #' . $order->invocie_id)

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Orden #' . $order->invocie_id,
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Ordenes', 'url' => route('admin.order.index')],
            ['label' => '#' . $order->invocie_id],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-sm" id="au-print-invoice"><i class="fas fa-print"></i> Imprimir</button>',
    ])

    <div id="au-invoice-print">
        <div class="au-card">
            <div class="au-card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                <div>
                    <div class="au-page-subtitle" style="font-weight:600;color:var(--au-text);margin-bottom:8px">Facturado a</div>
                    <div style="font-size:13px;line-height:1.7;color:var(--au-text-subdued)">
                        <div><strong style="color:var(--au-text)">{{ $address->name ?? '' }}</strong></div>
                        <div>{{ $address->email ?? '' }}</div>
                        <div>{{ $address->phone ?? '' }}</div>
                        <div>{{ $address->street ?? '' }} #{{ $address->street_number ?? '' }}</div>
                        <div>{{ $address->col ?? '' }}, {{ $address->city ?? '' }}, {{ $address->zip ?? '' }}</div>
                        <div>{{ $address->state ?? '' }}</div>
                    </div>
                </div>
                <div>
                    <div class="au-page-subtitle" style="font-weight:600;color:var(--au-text);margin-bottom:8px">Información de pago</div>
                    <div style="font-size:13px;line-height:1.7;color:var(--au-text-subdued)">
                        <div><strong style="color:var(--au-text)">Método:</strong> {{ $order->payment_method }}</div>
                        <div><strong style="color:var(--au-text)">Transacción ID:</strong> {{ $order->transaction->transaction_id ?? '—' }}</div>
                        <div><strong style="color:var(--au-text)">Fecha de orden:</strong> {{ date('d M, Y', strtotime($order->created_at)) }}</div>
                        <div style="margin-top:6px">
                            <span class="au-badge au-badge-{{ $order->payment_status === 1 ? 'success' : 'warning' }}">
                                <span class="au-badge-dot"></span>{{ $order->payment_status === 1 ? 'Pago Completada' : 'Pago Pendiente' }}
                            </span>
                            <span class="au-badge au-badge-{{ $statusTone }}">
                                <span class="au-badge-dot"></span>{{ config('order_status.order_status_admin')[$order->order_status]['status'] ?? $order->order_status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">Resumen del pedido</div>
            </div>
            <div class="au-table-wrap">
                <table class="au-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Artículo</th>
                            <th>Clave</th>
                            <th>Modelo</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th class="au-text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderProducts as $product)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $product->product_name }}</td>
                                <td class="au-mono">{{ $product->sku }}</td>
                                <td>{{ $product->productModel }}</td>
                                <td>{{ $settings->currency_icon }}{{ formatCurrency($product->unit_price) }}</td>
                                <td>{{ $product->qty }}</td>
                                <td class="au-text-right au-mono">{{ $settings->currency_icon }}{{ formatCurrency($product->unit_price * $product->qty) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="au-card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;border-top:1px solid var(--au-border)">
                <div class="no-print" style="display:flex;gap:16px">
                    <div class="au-field" style="flex:1">
                        <label class="au-label">Estado de pago</label>
                        <select class="au-select" id="payment_status" data-id="{{ $order->id }}">
                            <option value="0" {{ $order->payment_status === 0 ? 'selected' : '' }}>Pendiente</option>
                            <option value="1" {{ $order->payment_status === 1 ? 'selected' : '' }}>Completado</option>
                        </select>
                    </div>
                    <div class="au-field" style="flex:1">
                        <label class="au-label">Estado de orden</label>
                        <select class="au-select" id="order_status" data-id="{{ $order->id }}">
                            @foreach (config('order_status.order_status_admin') as $key => $status)
                                <option value="{{ $key }}" {{ $order->order_status === $key ? 'selected' : '' }}>{{ $status['status'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <div class="au-flex" style="justify-content:space-between;padding:5px 0;font-size:13px;color:var(--au-text-subdued)">
                        <span>Subtotal</span><span class="au-mono">{{ $settings->currency_icon }}{{ formatCurrency($order->sub_total ?? 0) }}</span>
                    </div>
                    <div class="au-flex" style="justify-content:space-between;padding:5px 0;font-size:13px;color:var(--au-text-subdued)">
                        <span>Descuento</span><span class="au-mono">{{ $settings->currency_icon }}{{ formatCurrency($coupon->discount ?? 0) }}</span>
                    </div>
                    <div class="au-flex" style="justify-content:space-between;padding:5px 0;font-size:13px;color:var(--au-text-subdued)">
                        <span>Envío</span><span class="au-mono">{{ $settings->currency_icon }}{{ formatCurrency($shipping->cost ?? 0) }}</span>
                    </div>
                    <hr style="border:0;border-top:1px solid var(--au-border);margin:6px 0">
                    <div class="au-flex" style="justify-content:space-between;padding:5px 0;font-size:15px;font-weight:700">
                        <span>Total</span><span class="au-mono">{{ $settings->currency_icon }}{{ formatCurrency($order->amount) }} Mxn</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @media print {
            .admin-ui-v2 .au-sidebar,
            .admin-ui-v2 .au-topbar,
            .admin-ui-v2 .au-page-header,
            .admin-ui-v2 .no-print { display: none !important; }
            .admin-ui-v2 .au-main { margin-left: 0 !important; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.getElementById('order_status').addEventListener('change', async (e) => {
            try {
                const data = await AU.request(`{{ route('admin.order.status') }}?status=${e.target.value}&id=${e.target.dataset.id}`);
                AU.toast[data.status === 'success' ? 'success' : 'error'](data.message);
            } catch (err) {
                AU.toast.error('No se pudo actualizar el estado de la orden');
            }
        });

        document.getElementById('payment_status').addEventListener('change', async (e) => {
            try {
                const data = await AU.request(`{{ route('admin.payment.status') }}?status=${e.target.value}&id=${e.target.dataset.id}`);
                AU.toast[data.status === 'success' ? 'success' : 'error'](data.message);
            } catch (err) {
                AU.toast.error('No se pudo actualizar el estado de pago');
            }
        });

        document.getElementById('au-print-invoice').addEventListener('click', () => window.print());
    </script>
@endpush

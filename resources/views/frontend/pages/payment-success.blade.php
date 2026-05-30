@extends('frontend.layouts.master')

@section('title', $settings->site_name . ' || Pedido Confirmado')

@push('styles')
@vite(['resources/css/checkout.css'])
@endpush

@section('content')

{{-- ── HERO VERDE ──────────────────────────────────────────────── --}}
<div class="success-hero">
    <div class="container">
        <div class="inner">
            <div class="success-icon-ring">✓</div>
            <h1>¡Pedido confirmado!</h1>
            <p style="font-size:15px;opacity:0.9;max-width:480px;margin:8px auto 0;">
                Gracias por tu compra. Estamos procesando tu pedido con la mayor prioridad.
            </p>
            <div class="success-order-num" style="margin-top:18px;">
                # {{ $order->invocie_id }}
            </div>
        </div>
    </div>
</div>

{{-- ── MAIN ─────────────────────────────────────────────────────── --}}
<section class="success-page">
    <div class="container">

        @php
            $address   = is_string($order->order_address)
                ? json_decode($order->order_address, true)
                : (array)$order->order_address;
            $shipping  = is_string($order->shipping_method)
                ? json_decode($order->shipping_method, true)
                : (array)($order->shipping_method ?? []);
            $payMethod = match($order->payment_method) {
                'paypal'   => '🅿️ PayPal',
                'stripe'   => '💳 Tarjeta (Stripe)',
                'transfer' => '🏦 Transferencia SPEI',
                default    => ucfirst($order->payment_method ?? 'N/D'),
            };
            $statusMap = [
                'pending'    => ['pending',    '⏳ Pendiente'],
                'processing' => ['processing', '⚙️ En preparación'],
                'delivered'  => ['delivered',  '✅ Entregado'],
                'cancelled'  => ['cancelled',  '❌ Cancelado'],
            ];
            [$statusClass, $statusLabel] = $statusMap[$order->order_status] ?? ['pending', '⏳ Pendiente'];
        @endphp

        {{-- ── BANNER DE EMAIL ──────────────────────────────── --}}
        <div class="email-banner">
            <div class="email-banner-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <div class="email-banner-content">
                <div class="email-banner-title">Confirmación enviada a tu correo</div>
                <div class="email-banner-text">
                    Recibirás los detalles del pedido en
                    <span class="email-banner-email">{{ auth()->user()->email }}</span>.
                    Revisa también tu carpeta de spam.
                </div>
            </div>
        </div>

        <div class="success-grid">

            {{-- ════════════════════════════════════════════════
                 COLUMNA IZQUIERDA — Información del pedido
            ════════════════════════════════════════════════ --}}
            <div>

                {{-- INFO GENERAL --}}
                <div class="success-info-card">
                    <div class="success-card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Información del pedido
                    </div>
                    <div class="success-card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Número de pedido</div>
                                <div class="info-value" style="font-family:monospace;font-size:15px;"># {{ $order->invocie_id }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Estado</div>
                                <div class="info-value">
                                    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Fecha del pedido</div>
                                <div class="info-value">
                                    {{ \Carbon\Carbon::parse($order->created_at)->isoFormat('D [de] MMMM [de] YYYY') }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Método de pago</div>
                                <div class="info-value">{{ $payMethod }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Método de envío</div>
                                <div class="info-value">
                                    {{ $shipping['name'] ?? 'Estándar' }}
                                    @if(isset($shipping['cost']) && $shipping['cost'] > 0)
                                        · {{ $settings->currency_icon }}{{ number_format($shipping['cost'], 2) }}
                                    @else
                                        · Gratis
                                    @endif
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Total</div>
                                <div class="info-value highlight">
                                    {{ $settings->currency_icon }}{{ number_format($order->amount, 2, '.', ',') }} MXN
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DIRECCIÓN DE ENVÍO --}}
                @if($address)
                <div class="success-info-card">
                    <div class="success-card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Dirección de entrega
                    </div>
                    <div class="success-card-body">
                        <div class="info-value" style="font-size:14px;line-height:1.7;">
                            <strong>{{ $address['name'] ?? $address['nombre'] ?? '' }}</strong><br>
                            {{ $address['street'] ?? $address['calle'] ?? '' }}
                            {{ ($address['street_number'] ?? $address['numero'] ?? null) ? ' #'.($address['street_number'] ?? $address['numero']) : '' }}<br>
                            Col. {{ $address['col'] ?? $address['colonia'] ?? '' }},
                            {{ $address['city'] ?? $address['ciudad'] ?? '' }},
                            {{ $address['state'] ?? $address['estado'] ?? '' }}<br>
                            C.P. {{ $address['zip'] ?? $address['cp'] ?? '' }}<br>
                            @if($address['phone'] ?? $address['telefono'] ?? null)
                                📞 {{ $address['phone'] ?? $address['telefono'] }}
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- LÍNEA DE TIEMPO --}}
                <div class="success-info-card">
                    <div class="success-card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Seguimiento de tu pedido
                    </div>
                    <div class="success-card-body">
                        <div class="timeline">
                            <div class="timeline-step">
                                <div class="timeline-dot done">✓</div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Pedido recibido</div>
                                    <div class="timeline-desc">
                                        Tu pedido fue registrado el {{ \Carbon\Carbon::parse($order->created_at)->isoFormat('D [de] MMMM, HH:mm') }}.
                                    </div>
                                </div>
                            </div>
                            <div class="timeline-step">
                                <div class="timeline-dot {{ $order->payment_status ? 'done' : 'pending' }}">
                                    {{ $order->payment_status ? '✓' : '2' }}
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Pago confirmado</div>
                                    <div class="timeline-desc">
                                        {{ $order->payment_status ? 'Tu pago fue procesado exitosamente.' : 'Esperando confirmación del pago.' }}
                                    </div>
                                </div>
                            </div>
                            <div class="timeline-step">
                                <div class="timeline-dot pending">3</div>
                                <div class="timeline-content">
                                    <div class="timeline-title">En preparación</div>
                                    <div class="timeline-desc">Nuestro equipo estará preparando tu pedido para envío.</div>
                                </div>
                            </div>
                            <div class="timeline-step">
                                <div class="timeline-dot pending">4</div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Entregado</div>
                                    <div class="timeline-desc">Tu pedido llegará a tu dirección en 1–5 días hábiles.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PRODUCTOS --}}
                <div class="success-info-card">
                    <div class="success-card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                        Productos pedidos
                    </div>
                    <div class="success-card-body" style="padding:0 20px;">
                        <div class="success-product-list">
                            @foreach($order->orderProducts as $item)
                            @php
                                $product = \App\Models\Product::find($item->product_id);
                            @endphp
                            <div class="success-product-item">
                                <div class="success-product-thumb">
                                    @if($product)
                                        <img src="{{ asset($product->thumb_image) }}" alt="{{ $item->product_name }}" loading="lazy">
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24" style="color:var(--gris-borde)"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h3"/></svg>
                                    @endif
                                </div>
                                <div class="success-product-info">
                                    <div class="success-product-name">{{ $item->product_name }}</div>
                                    @if($item->sku)
                                        <div class="success-product-sku">SKU: {{ $item->sku }}</div>
                                    @endif
                                    <div class="success-product-qty">Cantidad: {{ $item->qty }}</div>
                                </div>
                                <div class="success-product-price">
                                    {{ $settings->currency_icon }}{{ number_format($item->unit_price * $item->qty, 2, '.', ',') }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>{{-- /izquierda --}}

            {{-- ════════════════════════════════════════════════
                 COLUMNA DERECHA — Totales + acciones
            ════════════════════════════════════════════════ --}}
            <div class="success-sidebar">

                <div class="success-total-card">
                    <div class="total-header">Resumen del pago</div>
                    <div class="success-total-row">
                        <span>Subtotal</span>
                        <span>{{ $settings->currency_icon }}{{ number_format($order->sub_total, 2, '.', ',') }}</span>
                    </div>
                    @if($order->amount < $order->sub_total)
                    <div class="success-total-row" style="color:#2F855A;">
                        <span>Descuento</span>
                        <span>− {{ $settings->currency_icon }}{{ number_format($order->sub_total - $order->amount, 2, '.', ',') }}</span>
                    </div>
                    @endif
                    @if(isset($shipping['cost']) && $shipping['cost'] > 0)
                    <div class="success-total-row">
                        <span>Envío</span>
                        <span>{{ $settings->currency_icon }}{{ number_format($shipping['cost'], 2, '.', ',') }}</span>
                    </div>
                    @else
                    <div class="success-total-row">
                        <span>Envío</span>
                        <span style="color:#2F855A;font-weight:700;">Gratis</span>
                    </div>
                    @endif
                    <div class="success-total-row final">
                        <span>TOTAL PAGADO</span>
                        <span class="val">
                            {{ $settings->currency_icon }}{{ number_format($order->amount, 2, '.', ',') }}
                            <small style="font-size:11px;color:var(--gris-claro-texto);display:block;font-weight:600;">{{ $order->currency_name ?? 'MXN' }}</small>
                        </span>
                    </div>
                </div>

                <div class="success-actions">
                    <a href="{{ route('user.orders.index') }}" class="btn-success-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Ver mis pedidos
                    </a>
                    <a href="{{ route('products.index') }}" class="btn-success-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        Seguir comprando
                    </a>
                    <a href="{{ route('contact') }}" class="btn-success-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.29 6.29l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                        Dudas o asesoría
                    </a>
                </div>

                {{-- Sellos de confianza --}}
                <div style="background:var(--blanco);border:1px solid var(--gris-borde);border-radius:12px;padding:16px;margin-top:16px;text-align:center;">
                    <div style="font-size:11px;font-weight:800;color:var(--gris-claro-texto);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Tu compra está protegida</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--gris-texto);">
                            <span style="color:#2F855A;font-size:14px;">🔒</span>
                            Transacción cifrada con SSL
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--gris-texto);">
                            <span style="font-size:14px;">✅</span>
                            Producto 100% original · Garantía de fábrica
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--gris-texto);">
                            <span style="font-size:14px;">📦</span>
                            Envío rastreable a todo México
                        </div>
                    </div>
                </div>

            </div>{{-- /sidebar --}}

        </div>{{-- /success-grid --}}
    </div>{{-- /container --}}
</section>

@endsection

@push('Google-Ads')
<script>
  gtag('event', 'page_view', {
    'send_to': 'AW-16512201966',
    'transaction_id': '{{ $order->invocie_id }}',
    'value': {{ $order->amount }},
    'currency': '{{ $order->currency_name ?? "MXN" }}',
    'items': [
      @foreach($order->orderProducts as $item)
      {
        'id': '{{ $item->product_id }}',
        'name': '{{ addslashes($item->product_name) }}',
        'quantity': {{ $item->qty }},
        'price': {{ $item->unit_price }},
        'google_business_vertical': 'retail'
      }@if(!$loop->last),@endif
      @endforeach
    ]
  });
</script>
<script>
  gtag('event', 'conversion', {
    'send_to': 'AW-16512201966/W1X_CJCy0sgaEO7p0ME9',
    'value': {{ $order->amount }},
    'currency': '{{ $order->currency_name ?? "MXN" }}',
    'transaction_id': '{{ $order->invocie_id }}'
  });
</script>
@endpush

@extends('frontend.layouts.master')
@section('title', $settings->site_name . ' || Pedido Confirmado')

@push('styles')
@vite(['resources/css/checkout.css'])
@endpush

@section('content')

{{-- ── HERO DE ÉXITO ───────────────────────────────────────────── --}}
<div class="success-hero">
    <div class="container">
        <div class="inner">
            <div class="success-icon-ring">✓</div>
            <h1>¡Pedido confirmado!</h1>
            <p style="opacity:.88;font-size:14px;margin-top:6px;">
                Gracias por tu compra. A continuación encontrarás el resumen de tu pedido.
            </p>
            @if(isset($order) && $order->order_no)
            <div class="success-order-num">{{ $order->order_no }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ── CONTENIDO ───────────────────────────────────────────────── --}}
<section class="success-page">
    <div class="container">
        <div class="success-grid">

            {{-- ── COLUMNA IZQUIERDA ─────────────────────── --}}
            <div>

                {{-- Aviso de correo --}}
                <div class="email-banner" style="margin-bottom:24px;">
                    <div class="email-banner-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <div class="email-banner-content">
                        <div class="email-banner-title">Confirma tu correo electrónico</div>
                        <div class="email-banner-text">
                            Te enviamos un resumen del pedido a
                            <span class="email-banner-email">{{ auth()->user()->email ?? '' }}</span>.
                            Revisa tu bandeja de entrada y carpeta de spam.
                        </div>
                    </div>
                </div>

                {{-- Detalles del pedido --}}
                @if(isset($order))
                <div class="success-info-card">
                    <div class="success-card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Detalles del pedido
                    </div>
                    <div class="success-card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Número de pedido</div>
                                <div class="info-value">{{ $order->order_no }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Estado</div>
                                <div class="info-value">
                                    <span class="status-badge pending">Pendiente</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Método de pago</div>
                                <div class="info-value">
                                    @php
                                        $pm = session('payment_method', $order->payment_method ?? '');
                                        $pmNames = ['stripe' => 'Tarjeta (Stripe)', 'paypal' => 'PayPal', 'spei' => 'SPEI / BBVA'];
                                    @endphp
                                    {{ $pmNames[$pm] ?? ucfirst($pm) }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Total</div>
                                <div class="info-value highlight">
                                    {{ $settings->currency_icon }}{{ number_format($order->total_amount ?? 0, 2, '.', ',') }}
                                </div>
                            </div>
                        </div>

                        @if(session('order_notes') || ($order->order_note ?? false))
                        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--gris-borde,#DDE3EA);">
                            <div class="info-label">Notas del pedido</div>
                            <div class="info-value" style="font-size:13px;margin-top:4px;color:var(--gris-texto,#4A5568);">
                                {{ session('order_notes') ?: $order->order_note }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Productos --}}
                <div class="success-info-card">
                    <div class="success-card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        Productos
                    </div>
                    <div class="success-card-body" style="padding:0 20px;">
                        <div class="success-product-list">
                            @foreach($order->orderProducts ?? [] as $item)
                            <div class="success-product-item">
                                <div class="success-product-thumb">
                                    <img src="{{ asset($item->product->thumbnail ?? 'frontend/images/logo/AVIAzul-Celeste.png') }}"
                                         alt="{{ $item->product_name }}" loading="lazy">
                                </div>
                                <div class="success-product-info">
                                    <div class="success-product-name">{{ $item->product_name }}</div>
                                    @if($item->variant_sku ?? false)
                                        <div class="success-product-sku">SKU: {{ $item->variant_sku }}</div>
                                    @endif
                                    <div class="success-product-qty">Cant: {{ $item->qty }}</div>
                                </div>
                                <div class="success-product-price">
                                    {{ $settings->currency_icon }}{{ number_format($item->unit_price * $item->qty, 2, '.', ',') }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- SPEI: instrucciones si aplica --}}
                @if(session('payment_method') === 'spei')
                <div class="success-info-card" style="border-left:4px solid #1E40AF;">
                    <div class="success-card-header" style="background:#EFF6FF;border-bottom-color:#BFDBFE;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#1E40AF" stroke-width="2" width="16" height="16"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <span style="color:#1E40AF;">Instrucciones de pago SPEI</span>
                    </div>
                    <div class="success-card-body" style="background:#EFF6FF;">
                        <p style="font-size:13px;color:#1E40AF;margin-bottom:12px;">
                            Realiza tu transferencia SPEI a los siguientes datos:
                        </p>
                        <div style="background:#fff;border:1.5px solid #BFDBFE;border-radius:8px;padding:14px 16px;font-size:13px;">
                            <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #BFDBFE;">
                                <span style="color:#4A5568;">Banco</span><strong style="color:#1E40AF;">BBVA</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #BFDBFE;">
                                <span style="color:#4A5568;">Beneficiario</span><strong style="color:#1E40AF;">MAC DEL NORTE SA DE CV</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #BFDBFE;">
                                <span style="color:#4A5568;">CLABE</span>
                                <strong style="color:#1E40AF;font-family:monospace;letter-spacing:1.5px;">012345678901234567</strong>
                            </div>
                            @if(isset($order))
                            <div style="display:flex;justify-content:space-between;padding:5px 0;">
                                <span style="color:#4A5568;">Referencia / Concepto</span>
                                <strong style="color:#1E40AF;">{{ $order->order_no }}</strong>
                            </div>
                            @endif
                        </div>
                        <p style="font-size:12px;color:#1E40AF;margin-top:10px;background:rgba(30,64,175,0.08);border-radius:6px;padding:8px 12px;line-height:1.5;">
                            Envía el comprobante a <strong>ventas@macdelnorte.com</strong> con tu número de pedido. Tu pedido se procesará al confirmar el pago.
                        </p>
                    </div>
                </div>
                @endif

                {{-- Seguimiento del pedido --}}
                <div class="success-info-card">
                    <div class="success-card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Seguimiento del pedido
                    </div>
                    <div class="success-card-body">
                        <div class="timeline">
                            <div class="timeline-step">
                                <div class="timeline-dot done">✓</div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Pedido recibido</div>
                                    <div class="timeline-desc">Tu pedido fue registrado correctamente.</div>
                                </div>
                            </div>
                            <div class="timeline-step">
                                <div class="timeline-dot pending">2</div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Confirmación de pago</div>
                                    <div class="timeline-desc">Se verificará tu método de pago.</div>
                                </div>
                            </div>
                            <div class="timeline-step">
                                <div class="timeline-dot pending">3</div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Preparación y envío</div>
                                    <div class="timeline-desc">Tu pedido será preparado y enviado con el transportista.</div>
                                </div>
                            </div>
                            <div class="timeline-step">
                                <div class="timeline-dot pending">4</div>
                                <div class="timeline-content" style="padding-bottom:0;">
                                    <div class="timeline-title">Entrega</div>
                                    <div class="timeline-desc">Recibirás tu pedido en la dirección indicada.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /izquierda --}}

            {{-- ── COLUMNA DERECHA (sidebar) ─────────────── --}}
            <div class="success-sidebar">

                @if(isset($order))
                <div class="success-total-card">
                    <div class="total-header">Resumen de cobro</div>
                    <div class="success-total-row">
                        <span>Subtotal</span>
                        <span>{{ $settings->currency_icon }}{{ number_format($order->sub_total ?? 0, 2, '.', ',') }}</span>
                    </div>
                    @if(($order->coupon_discount ?? 0) > 0)
                    <div class="success-total-row" style="color:#2F855A;">
                        <span>Descuento cupón</span>
                        <span>− {{ $settings->currency_icon }}{{ number_format($order->coupon_discount, 2, '.', ',') }}</span>
                    </div>
                    @endif
                    <div class="success-total-row">
                        <span>Envío</span>
                        <span>{{ $settings->currency_icon }}{{ number_format($order->shipping_cost ?? 0, 2, '.', ',') }}</span>
                    </div>
                    <div class="success-total-row final">
                        <span>Total</span>
                        <span class="val">{{ $settings->currency_icon }}{{ number_format($order->total_amount ?? 0, 2, '.', ',') }}</span>
                    </div>
                </div>
                @endif

                <div class="success-actions">
                    <a href="{{ route('user.order.index') }}" class="btn-success-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        Ver mis pedidos
                    </a>
                    <a href="{{ route('products.index') }}" class="btn-success-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
                        Seguir comprando
                    </a>
                    <a href="{{ route('index') }}" class="btn-success-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        Ir al inicio
                    </a>
                </div>

            </div>{{-- /sidebar --}}

        </div>{{-- /success-grid --}}
    </div>{{-- /container --}}
</section>

@endsection

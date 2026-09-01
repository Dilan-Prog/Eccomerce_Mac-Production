@php
    $address = json_decode($order->order_address);
    $shipping = json_decode($order->shipping_method);
    $coupon = json_decode($order->coupon);
    $activeTab = 'orders';

    // Mismas etiquetas seguras para el cliente que usa la pestaña "Mis
    // pedidos" (resources/views/frontend/dashboard/profile.blade.php) — a
    // propósito no incluye 'pendiente_de_surtir' (estado interno de
    // inventario, solo visible en el panel admin).
    $orderStatusMeta = [
        'pending' => ['label' => 'Pendiente', 'tone' => 'warning'],
        'processed_and_ready_to_ship' => ['label' => 'Procesado y listo para enviar', 'tone' => 'info'],
        'dropped_off' => ['label' => 'Entregado al transportista', 'tone' => 'info'],
        'shipped' => ['label' => 'Enviado', 'tone' => 'info'],
        'out_for_delivery' => ['label' => 'En ruta de entrega', 'tone' => 'primary'],
        'delivered' => ['label' => 'Entregado', 'tone' => 'success'],
        'canceled' => ['label' => 'Cancelado', 'tone' => 'danger'],
    ];
    $statusMeta = $orderStatusMeta[$order->order_status] ?? ['label' => 'Pendiente', 'tone' => 'warning'];
@endphp

@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} | Detalle de pedido
@endsection

@push('styles')
<style>
/* ── VARIABLES ADICIONALES (mismas que profile.blade.php) ──── */
:root {
    --negro-texto:      #1A202C;
    --verde-disponible: #2F855A;
    --verde-claro:      #F0FDF4;
    --amarillo-claro:   #FFFBEB;
    --radius-sm:   4px;
    --radius-md:   6px;
    --radius-lg:   8px;
    --radius-xl:  12px;
    --radius-full: 999px;
    --shadow-sm:  0 2px 4px rgba(0,0,0,0.04);
    --shadow-md:  0 4px 12px rgba(0,0,0,0.08);
}

/* ── BREADCRUMB ─────────────────────────────────────────── */
.mdn-breadcrumb { background:var(--blanco); border-bottom:1px solid var(--gris-borde); padding:12px 0; }
.mdn-breadcrumb-list { display:flex; align-items:center; gap:8px; font-size:13px; flex-wrap:wrap; }
.mdn-breadcrumb-list a { color:var(--gris-claro-texto); text-decoration:none; font-weight:600; }
.mdn-breadcrumb-list a:hover { color:var(--azul-principal); }
.mdn-breadcrumb-sep { color:var(--gris-borde); font-size:11px; }
.mdn-breadcrumb-cur { color:var(--azul-principal); font-weight:700; }

/* ── PAGE HERO ──────────────────────────────────────────── */
.mdn-page-hero {
    background:linear-gradient(135deg,var(--azul-oscuro) 0%,var(--azul-principal) 60%,#0057A8 100%);
    color:var(--blanco); padding:36px 0 30px; position:relative; overflow:hidden;
}
.mdn-page-hero::before {
    content:''; position:absolute; inset:0; pointer-events:none;
    background-image:linear-gradient(rgba(255,255,255,0.04) 1px,transparent 1px),
                     linear-gradient(90deg,rgba(255,255,255,0.04) 1px,transparent 1px);
    background-size:50px 50px;
}
.mdn-page-hero-inner { position:relative; z-index:2; }
.mdn-page-eyebrow {
    display:inline-block; background:rgba(246,173,28,0.18);
    border:1px solid rgba(246,173,28,0.45); color:var(--amarillo-destacado);
    font-size:11px; font-weight:800; padding:5px 12px; border-radius:var(--radius-sm);
    text-transform:uppercase; letter-spacing:1.5px; margin-bottom:10px;
}
.mdn-page-hero h1 { font-size:clamp(20px,2.5vw,28px); font-weight:800; color:var(--blanco); margin:0 0 6px; }
.mdn-page-hero p  { font-size:14px; opacity:0.88; margin:0; }

/* ── PROFILE LAYOUT (igual que profile.blade.php) ──────────── */
.profile-page  { padding:32px 0 64px; background:var(--gris-fondo); }
.profile-grid  { display:grid; grid-template-columns:240px 1fr; gap:24px; align-items:start; }

.profile-sidebar {
    background:var(--blanco); border-radius:var(--radius-xl);
    border:1px solid var(--gris-borde); box-shadow:var(--shadow-sm);
    padding:8px 0; position:sticky; top:24px; overflow:hidden;
}
.profile-sidebar-link {
    display:flex; align-items:center; gap:10px; padding:11px 20px;
    font-size:13px; font-weight:600; color:var(--gris-texto);
    text-decoration:none; background:none; border:none;
    border-left:3px solid transparent; width:100%; text-align:left;
    cursor:pointer; font-family:inherit; transition:all 0.15s; line-height:1.4;
}
.profile-sidebar-link svg { width:16px; height:16px; flex-shrink:0; color:var(--gris-claro-texto); }
.profile-sidebar-link:hover { background:var(--gris-fondo); color:var(--azul-principal); }
.profile-sidebar-link:hover svg { color:var(--azul-principal); }
.profile-sidebar-link.active {
    background:var(--azul-claro); color:var(--azul-principal);
    font-weight:700; border-left-color:var(--accent-cta);
}
.profile-sidebar-link.active svg { color:var(--azul-principal); }
.profile-sidebar-link.danger { color:var(--rojo-error); }
.profile-sidebar-link.danger svg { color:var(--rojo-error); }
.profile-sidebar-link.danger:hover { background:#FEF2F2; }
.profile-sidebar-divider { height:1px; background:var(--gris-borde); margin:6px 0; }

.profile-main {
    background:var(--blanco); border-radius:var(--radius-xl);
    border:1px solid var(--gris-borde); box-shadow:var(--shadow-sm);
    padding:32px; min-height:480px;
}
.profile-section-header { margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid var(--gris-borde); }
.profile-section-header h2 { font-size:19px; font-weight:800; color:var(--azul-principal); margin:0 0 4px; }
.profile-section-header p  { font-size:13px; color:var(--gris-claro-texto); margin:0; }

.mdn-btn { padding:11px 22px; border-radius:var(--radius-md); font-size:13px; font-weight:700; cursor:pointer; border:none; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:7px; transition:all 0.2s; font-family:inherit; white-space:nowrap; }
.mdn-btn svg { width:15px; height:15px; }
.mdn-btn-secondary { background:var(--blanco); color:var(--azul-principal); border:2px solid var(--azul-principal); }
.mdn-btn-secondary:hover { background:var(--azul-claro); color:var(--azul-principal); }

/* ── BADGES DE ESTADO (mismos que la tabla de "Mis pedidos") ── */
.mdn-order-badge { display:inline-block; padding:3px 10px; border-radius:var(--radius-full); font-size:11px; font-weight:700; white-space:nowrap; }
.mdn-order-badge.tone-warning { background:var(--amarillo-claro); color:#92400E; }
.mdn-order-badge.tone-info { background:var(--azul-claro); color:var(--azul-principal); }
.mdn-order-badge.tone-primary { background:var(--azul-claro); color:var(--azul-oscuro); }
.mdn-order-badge.tone-success { background:var(--verde-claro); color:var(--verde-disponible); }
.mdn-order-badge.tone-danger { background:#FEF2F2; color:var(--rojo-error); }

/* ── DETALLE DEL PEDIDO ─────────────────────────────────────── */
.order-summary-row { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:28px; }
.order-summary-box h6 { font-size:11px; text-transform:uppercase; letter-spacing:0.6px; color:var(--gris-claro-texto); font-weight:700; margin:0 0 8px; }
.order-summary-box p { font-size:14px; color:var(--negro-texto); margin:0 0 4px; }
.order-products-table { width:100%; border-collapse:collapse; margin-bottom:24px; }
.order-products-table thead th { background:var(--gris-fondo); color:var(--azul-principal); font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; border-bottom:2px solid var(--gris-borde); padding:10px 12px; text-align:left; white-space:nowrap; }
.order-products-table tbody td { font-size:13px; color:var(--gris-texto); vertical-align:middle; border-top:1px solid var(--gris-borde); padding:10px 12px; }
.order-totals-box { background:var(--gris-fondo); border-radius:var(--radius-lg); padding:18px 20px; max-width:340px; margin-left:auto; }
.order-totals-box p { display:flex; justify-content:space-between; font-size:13px; color:var(--gris-texto); margin:0 0 8px; }
.order-totals-box p:last-child { margin-bottom:0; padding-top:8px; border-top:1px solid var(--gris-borde); font-weight:800; color:var(--azul-principal); font-size:14px; }

@media (max-width:960px) {
    .profile-grid { grid-template-columns:1fr; }
    .profile-sidebar { position:relative; top:0; display:flex; flex-wrap:wrap; padding:8px; gap:4px; }
    .profile-sidebar-link { border-left:none; border-radius:var(--radius-md); border-bottom:2px solid transparent; padding:8px 12px; font-size:12px; flex:1; min-width:max-content; justify-content:center; }
    .profile-sidebar-link.active { border-left-color:transparent; border-bottom-color:var(--accent-cta); }
    .profile-sidebar-divider { display:none; }
    .profile-main { padding:20px 16px; }
    .order-summary-row { grid-template-columns:1fr; gap:16px; }
    .order-products-table { display:block; overflow-x:auto; }
}
@media (max-width:560px) {
    .profile-sidebar-link span { display:none; }
}
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<div class="mdn-breadcrumb">
    <div class="container">
        <div class="mdn-breadcrumb-list">
            <a href="{{ route('index') }}">Inicio</a>
            <span class="mdn-breadcrumb-sep">›</span>
            <a href="{{ route('user.profile', ['tab' => 'orders']) }}">Mi cuenta</a>
            <span class="mdn-breadcrumb-sep">›</span>
            <span class="mdn-breadcrumb-cur">Pedido CP{{ $order->invocie_id }}</span>
        </div>
    </div>
</div>

{{-- Page Hero --}}
<section class="mdn-page-hero">
    <div class="container mdn-page-hero-inner">
        <div class="mdn-page-eyebrow">Tu cuenta</div>
        <h1>Detalle de pedido</h1>
        <p>Rastreo y contenido del pedido CP{{ $order->invocie_id }}</p>
    </div>
</section>

<section class="profile-page">
    <div class="container">
        <div class="profile-grid">

            @include('frontend.dashboard.partials.account-sidebar', ['activeTab' => $activeTab])

            <div class="profile-main">
                <div class="profile-section-header-row profile-section-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h2>Pedido CP{{ $order->invocie_id }}</h2>
                        <p>Realizado el {{ date('d-M-Y', strtotime($order->created_at)) }}</p>
                    </div>
                    <a href="{{ route('user.profile', ['tab' => 'orders']) }}" class="mdn-btn mdn-btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        Volver a Mis pedidos
                    </a>
                </div>

                <div class="order-summary-row">
                    <div class="order-summary-box">
                        <h6>Enviado a</h6>
                        <p>{{ $address->street ?? '' }} @if(!empty($address->street_number))#{{ $address->street_number }}@endif</p>
                        <p>{{ $address->col ?? '' }}, {{ $address->zip ?? '' }}</p>
                        <p>{{ $address->city ?? '' }}, {{ $address->state ?? '' }}, México</p>
                    </div>
                    <div class="order-summary-box">
                        <h6>Envío</h6>
                        <p>{{ $shipping->name ?? 'No especificado' }}</p>
                        <h6 style="margin-top:14px">Estado del pedido</h6>
                        <p><span class="mdn-order-badge tone-{{ $statusMeta['tone'] }}">{{ $statusMeta['label'] }}</span></p>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="order-products-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Clave</th>
                                <th>Modelo</th>
                                <th>Precio x unidad</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderProducts as $product)
                                <tr>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->productModel }}</td>
                                    <td>{{ $settings->currency_icon }}{{ formatCurrency($product->unit_price) }} Mxn</td>
                                    <td>{{ $product->qty }}</td>
                                    <td>{{ $settings->currency_icon }}{{ formatCurrency($product->unit_price * $product->qty) }} Mxn</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="order-totals-box">
                    <p><span>Subtotal</span> <span>{{ $settings->currency_icon }}{{ formatCurrency($order->sub_total) }}</span></p>
                    <p><span>Envío</span> <span>{{ $settings->currency_icon }}{{ formatCurrency($shipping->cost ?? 0) }}</span></p>
                    <p><span>Cupón</span> <span>-{{ $settings->currency_icon }}{{ formatCurrency($coupon->discount ?? 0) }}</span></p>
                    <p><span>Monto total</span> <span>{{ $settings->currency_icon }}{{ formatCurrency($order->amount) }} Mxn</span></p>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

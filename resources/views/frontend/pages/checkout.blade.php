@extends('frontend.layouts.master')
@section('title', $settings->site_name . ' || Checkout')

@push('styles')
@vite(['resources/css/checkout.css'])
<style>
  @keyframes spin { to { transform: rotate(360deg); } }
  .spin-icon { animation: spin 0.8s linear infinite; }

  /* ── PROGRESSIVE SECTION LOCKING ───────────────────── */
  .checkout-section--locked .checkout-section-body {
      max-height: 0; overflow: hidden;
      padding-top: 0 !important; padding-bottom: 0 !important;
  }
  .checkout-section--unlocked .checkout-section-body { max-height: 3000px; }
  .checkout-page.transitions-ready .checkout-section--locked .checkout-section-body {
      transition: max-height 0.38s ease, padding 0.32s ease;
  }
  .checkout-page.transitions-ready .checkout-section--unlocked .checkout-section-body {
      transition: max-height 0.5s ease, padding 0.32s ease;
  }
  .checkout-section--locked   { pointer-events: none; }
  .checkout-section--unlocked { pointer-events: auto; }
  .checkout-section--locked .checkout-section-header   { opacity: 0.5; }
  .checkout-section--completed .checkout-section-num   { background: #2F855A; }
  .checkout-section--completed .checkout-section-header { background: #F0FFF4; border-bottom-color: #9AE6B4; }

  /* ── SECTION BADGE ──────────────────────────────────── */
  .section-badge {
      margin-left: auto; font-size: 11px; font-weight: 700;
      padding: 3px 10px; border-radius: 20px;
      text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap;
  }
  .section-badge.s-pending { background: #FEF3C7; color: #92400E; }
  .section-badge.s-done    { background: #D1FAE5; color: #065F46; }

  /* ── PAYMENT OPTIONS ────────────────────────────────── */
  .payment-method-list { display: flex; flex-direction: column; gap: 10px; }
  .payment-option {
      display: flex; align-items: center; gap: 14px;
      border: 2px solid var(--gris-borde, #DDE3EA); border-radius: 10px;
      padding: 16px; cursor: pointer; transition: all 0.18s;
  }
  .payment-option:hover { border-color: var(--azul-medio, #0057A8); background: var(--azul-claro, #E6EFF8); }
  .payment-option.selected {
      border-color: var(--azul-principal, #003E7E); background: var(--azul-claro, #E6EFF8);
  }
  .payment-option input[type="radio"] { display: none; }
  .payment-radio {
      width: 18px; height: 18px; border: 2px solid var(--gris-borde, #DDE3EA);
      border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
      transition: all 0.18s;
  }
  .payment-option.selected .payment-radio {
      border-color: var(--azul-principal, #003E7E); background: var(--azul-principal, #003E7E);
  }
  .payment-option.selected .payment-radio::after {
      content: ''; width: 6px; height: 6px; background: #fff; border-radius: 50%;
  }
  .payment-icon-box {
      width: 52px; height: 36px; background: #fff;
      border: 1px solid var(--gris-borde, #DDE3EA); border-radius: 6px;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .payment-icon-box img { height: 22px; object-fit: contain; }
  .payment-option-info { flex: 1; }
  .payment-option-name { font-size: 14px; font-weight: 700; color: var(--azul-principal, #003E7E); }
  .payment-option-desc { font-size: 12px; color: var(--gris-claro-texto, #718096); margin-top: 2px; }
  .payment-discount-badge {
      font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px;
      background: #D1FAE5; color: #065F46; white-space: nowrap;
  }

  /* ── PAYMENT DETAIL PANELS ──────────────────────────── */
  .payment-detail-panel {
      display: none; margin-top: 12px;
      border-radius: 10px; overflow: hidden;
  }
  .payment-detail-panel.active { display: block; }

  /* Stripe panel */
  .stripe-panel-inner {
      border: 1.5px solid var(--gris-borde, #DDE3EA); border-radius: 10px;
      padding: 18px; background: #fff;
  }
  .stripe-panel-title { font-size: 13px; font-weight: 800; color: var(--azul-principal, #003E7E); margin-bottom: 12px; }
  #stripe-card-element {
      padding: 12px; border: 1.5px solid var(--gris-borde, #DDE3EA);
      border-radius: 8px; background: #fff;
      transition: border-color 0.18s;
  }
  #stripe-card-element.StripeElement--focus { border-color: var(--azul-principal, #003E7E); box-shadow: 0 0 0 3px rgba(0,62,126,0.1); }

  /* PayPal panel */
  .paypal-panel-inner {
      border: 1.5px solid #E5E7EB; border-radius: 10px;
      padding: 18px; background: #FAFAFA; text-align: center;
  }
  .paypal-panel-title { font-size: 13px; font-weight: 700; color: var(--azul-principal, #003E7E); margin-bottom: 14px; }
  #paypal-button-container { max-width: 340px; margin: 0 auto; }

  /* SPEI panel */
  .spei-panel-inner {
      background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 18px;
  }
  .spei-panel-title { font-size: 13px; font-weight: 800; color: #1E40AF; margin-bottom: 12px; }
  .spei-data-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 6px 0; font-size: 13px; border-bottom: 1px solid rgba(191,219,254,0.6);
  }
  .spei-data-row:last-of-type { border-bottom: none; }
  .spei-data-row .lbl { color: var(--gris-texto, #4A5568); }
  .spei-data-row .val { font-weight: 700; color: #1E40AF; }
  .spei-clabe-row {
      background: #fff; border: 1.5px solid #BFDBFE; border-radius: 7px;
      padding: 10px 14px; display: flex; align-items: center; gap: 10px; margin: 10px 0;
  }
  .spei-clabe-num {
      flex: 1; font-size: 15px; font-weight: 800; color: #1E40AF;
      letter-spacing: 1.5px; font-family: monospace;
  }
  .btn-copy-clabe {
      padding: 6px 12px; background: var(--azul-principal, #003E7E); color: #fff;
      border: none; border-radius: 5px; font-size: 12px; font-weight: 700;
      cursor: pointer; transition: background 0.18s; flex-shrink: 0;
  }
  .btn-copy-clabe:hover { background: var(--azul-oscuro, #002856); }
  .spei-ref-box {
      background: #fff; border: 2px solid #1E40AF; border-radius: 8px;
      padding: 12px 16px; margin: 10px 0; text-align: center;
  }
  .spei-ref-label { font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
  .spei-ref-number { font-size: 22px; font-weight: 900; color: #1E40AF; letter-spacing: 3px; font-family: monospace; }
  .spei-note {
      font-size: 11px; color: #1E40AF; background: rgba(30,64,175,0.08);
      border-radius: 6px; padding: 8px 12px; margin-top: 8px; line-height: 1.55;
  }

  /* ── ORDER NOTES ────────────────────────────────────── */
  .notes-group { margin-top: 16px; }
  .notes-group label {
      font-size: 13px; font-weight: 700; color: var(--azul-principal, #003E7E);
      margin-bottom: 6px; display: block;
  }
  .notes-group textarea {
      width: 100%; padding: 10px 12px; box-sizing: border-box;
      border: 1.5px solid var(--gris-borde, #DDE3EA); border-radius: 8px;
      font-size: 13px; font-family: inherit; resize: vertical; min-height: 72px;
      transition: border-color 0.18s; color: var(--negro-texto, #1A202C);
  }
  .notes-group textarea:focus {
      outline: none; border-color: var(--azul-principal, #003E7E);
      box-shadow: 0 0 0 3px rgba(0,62,126,0.1);
  }

  /* ── SIDEBAR EXTRAS ─────────────────────────────────── */
  .sidebar-payment-row   { display: none; }
  .sidebar-payment-row.visible   { display: flex; }
  .sidebar-spei-discount { display: none; }
  .sidebar-spei-discount.visible { display: flex; color: #2F855A; font-weight: 700; }

  /* ── PAYPAL NOTICE (when PayPal selected, hide main btn) */
  .paypal-btn-notice {
      display: none; margin-top: 8px; padding: 10px 14px;
      background: #FFF3CD; border: 1px solid #FFC107; border-radius: 8px;
      font-size: 13px; color: #856404; text-align: center; line-height: 1.45;
  }
  .paypal-btn-notice.visible { display: block; }
</style>
@endpush

@php $refBank = rand(100000, 999999); @endphp

@section('content')

{{-- ── BREADCRUMB ─────────────────────────────────────────────── --}}
<div class="checkout-breadcrumb">
    <div class="container">
        <nav>
            <a href="{{ route('index') }}">Inicio</a>
            <span class="sep">/</span>
            <a href="{{ route('cart-details') }}">Mi Carrito</a>
            <span class="sep">/</span>
            <span class="cur">Checkout</span>
        </nav>
    </div>
</div>

{{-- ── HEADER ──────────────────────────────────────────────────── --}}
<div class="checkout-header">
    <div class="container">
        <div class="inner">
            <h1>Finaliza tu compra</h1>
            <p>Dirección · Envío · Método de pago · Confirmación</p>
            <div class="checkout-steps">
                <div class="checkout-step">
                    <div class="checkout-step-dot">🛒</div>
                    <span>Carrito</span>
                </div>
                <div class="checkout-step-divider"></div>
                <div class="checkout-step active">
                    <div class="checkout-step-dot">📋</div>
                    <span>Checkout</span>
                </div>
                <div class="checkout-step-divider"></div>
                <div class="checkout-step">
                    <div class="checkout-step-dot">💳</div>
                    <span>Pago</span>
                </div>
                <div class="checkout-step-divider"></div>
                <div class="checkout-step">
                    <div class="checkout-step-dot">✓</div>
                    <span>Confirmación</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── MAIN ─────────────────────────────────────────────────────── --}}
<section class="checkout-page">
    <div class="container">
        <div class="checkout-layout">

            <div>

                {{-- ── SECCIÓN 1: DIRECCIÓN ───────────────── --}}
                <div class="checkout-section checkout-section--unlocked" id="section-1">
                    <div class="checkout-section-header">
                        <div class="checkout-section-num">1</div>
                        <h2 class="checkout-section-title">Dirección de envío</h2>
                        <span class="section-badge s-pending" id="badge-1">Pendiente</span>
                    </div>
                    <div class="checkout-section-body">

                        @if($addresses->isEmpty())
                            <p style="font-size:13px;color:var(--gris-claro-texto);margin-bottom:16px;">
                                No tienes direcciones guardadas. Agrega una nueva a continuación.
                            </p>
                        @else
                            <div class="address-grid">
                                @foreach ($addresses as $address)
                                <label class="address-card {{ $loop->first ? 'selected' : '' }}">
                                    <input class="shipping_address" type="radio" name="address_radio"
                                           value="{{ $address->id }}" data-id="{{ $address->id }}"
                                           {{ $loop->first ? 'checked' : '' }}>
                                    <div class="address-card-check">✓</div>
                                    <div class="address-card-name">{{ $address->name }}</div>
                                    <div class="address-card-line">
                                        {{ $address->street }}{{ $address->street_number ? ' #'.$address->street_number : '' }}<br>
                                        Col. {{ $address->col }}, {{ $address->city }}, {{ $address->state }}<br>
                                        C.P. {{ $address->zip }}
                                    </div>
                                    <div class="address-card-contact">
                                        📞 {{ $address->phone }}
                                        @if($address->email)· ✉️ {{ $address->email }}@endif
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        @endif

                        <button type="button" class="btn-add-address" id="btn-add-address">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Agregar nueva dirección
                        </button>

                        <div class="new-address-panel" id="new-address-panel">
                            <div class="panel-title">Nueva dirección de envío</div>
                            <form id="checkoutFormAddress" action="{{ route('user.checkout.address.create') }}" method="POST">
                                @csrf
                                <div class="form-row-2" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Nombre completo <span>*</span></label>
                                        <input class="form-input @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" placeholder="Ej. Roberto Martínez">
                                        @error('name')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Teléfono <span>*</span></label>
                                        <input class="form-input @error('phone') is-invalid @enderror" type="text" name="phone" value="{{ old('phone') }}" placeholder="10 dígitos">
                                        @error('phone')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label class="form-label">Correo electrónico</label>
                                    <input class="form-input @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com">
                                    @error('email')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-row-3" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Código Postal <span>*</span></label>
                                        <input class="form-input @error('zip') is-invalid @enderror" type="text" name="zip" value="{{ old('zip') }}" placeholder="00000">
                                        @error('zip')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Estado <span>*</span></label>
                                        <select class="form-select-mdn @error('state') is-invalid @enderror" name="state">
                                            <option value="">Seleccionar...</option>
                                            @foreach(config('settings.state_list', []) as $state)
                                                <option value="{{ $state }}" {{ old('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                                            @endforeach
                                        </select>
                                        @error('state')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Ciudad <span>*</span></label>
                                        <input class="form-input @error('city') is-invalid @enderror" type="text" name="city" value="{{ old('city') }}" placeholder="Monterrey">
                                        @error('city')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row-2" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Colonia <span>*</span></label>
                                        <input class="form-input @error('col') is-invalid @enderror" type="text" name="col" value="{{ old('col') }}" placeholder="Colonia / Fraccionamiento">
                                        @error('col')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Calle <span>*</span></label>
                                        <input class="form-input @error('street') is-invalid @enderror" type="text" name="street" value="{{ old('street') }}" placeholder="Nombre de la calle">
                                        @error('street')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row-2" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Número interior</label>
                                        <input class="form-input" type="text" name="street_number" value="{{ old('street_number') }}" placeholder="Opcional">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Entre calles</label>
                                        <input class="form-input" type="text" name="street_1" value="{{ old('street_1') }}" placeholder="Calle 1">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:14px;">
                                    <label class="form-label">Indicaciones adicionales</label>
                                    <textarea class="form-textarea-mdn" name="address" placeholder="Descripción del exterior, referencias, etc.">{{ old('address') }}</textarea>
                                </div>
                                <div class="panel-actions">
                                    <button type="submit" class="btn-save-address" id="saveAddressButton">Guardar dirección</button>
                                    <button type="button" class="btn-cancel-address" id="btn-cancel-address">Cancelar</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                {{-- ── SECCIÓN 2: MÉTODO DE ENVÍO ─────────── --}}
                <div class="checkout-section checkout-section--locked" id="section-2">
                    <div class="checkout-section-header">
                        <div class="checkout-section-num">2</div>
                        <h2 class="checkout-section-title">Método de envío</h2>
                        <span class="section-badge s-pending" id="badge-2">Pendiente</span>
                    </div>
                    <div class="checkout-section-body">

                        @php $hasShipping = false; @endphp
                        <div class="shipping-options">
                            @foreach ($shippingMethod as $method)
                                @php $show = false; @endphp
                                @if ($method->type == 'min_cost' && getCartTotal() >= $method->min_cost)
                                    @php $show = true; @endphp
                                @elseif ($method->type == 'flat_cost')
                                    @php $show = true; @endphp
                                @endif
                                @if($show)
                                @php $hasShipping = true; @endphp
                                <label class="shipping-option">
                                    <input class="shipping_method" type="radio" name="shipping_method_radio"
                                           value="{{ $method->id }}" data-id="{{ $method->cost }}">
                                    <div class="shipping-radio"></div>
                                    <div class="shipping-icon"><i class="fas fa-shipping-fast"></i></div>
                                    <div class="shipping-info">
                                        <div class="shipping-name">{{ $method->name }}</div>
                                        <div class="shipping-desc">Entrega estimada 1–5 días hábiles</div>
                                    </div>
                                    <div class="shipping-cost {{ $method->cost == 0 ? 'free' : '' }}">
                                        {{ $method->cost == 0 ? '🎉 Gratis' : $settings->currency_icon . number_format($method->cost, 2) }}
                                    </div>
                                </label>
                                @endif
                            @endforeach
                            @if(!$hasShipping)
                                <p class="no-shipping-msg">No hay métodos de envío disponibles para tu pedido actual.</p>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- ── SECCIÓN 3: MÉTODO DE PAGO ──────────── --}}
                <div class="checkout-section checkout-section--locked" id="section-3">
                    <div class="checkout-section-header">
                        <div class="checkout-section-num">3</div>
                        <h2 class="checkout-section-title">Método de pago</h2>
                        <span class="section-badge s-pending" id="badge-3">Pendiente</span>
                    </div>
                    <div class="checkout-section-body">

                        <div class="payment-method-list">

                            {{-- ─ Tarjeta / Stripe ─ --}}
                            <label class="payment-option" id="pay-opt-stripe">
                                <input type="radio" name="payment_radio" value="stripe">
                                <div class="payment-radio"></div>
                                <div class="payment-icon-box">
                                    <img src="{{ asset('frontend/images/iconos-empresas/Visa_logo_with_bgc.webp') }}" alt="Tarjeta">
                                </div>
                                <div class="payment-option-info">
                                    <div class="payment-option-name">Tarjeta de crédito / débito</div>
                                    <div class="payment-option-desc">Visa, Mastercard — pago seguro con Stripe</div>
                                </div>
                            </label>

                            {{-- Stripe form (hidden until selected) --}}
                            <div class="payment-detail-panel" id="stripe-detail-panel">
                                <div class="stripe-panel-inner">
                                    <div class="stripe-panel-title">Datos de tu tarjeta</div>
                                    <form action="{{ route('user.stripe.payment') }}" method="POST" id="stripe-checkout-form">
                                        @csrf
                                        <input type="hidden" name="stripe_token" id="stripe-token-id">
                                        <div id="stripe-card-element"></div>
                                        <div id="stripe-card-errors" style="color:#DC2626;font-size:12px;margin-top:6px;"></div>
                                    </form>
                                    <p style="font-size:11px;color:var(--gris-claro-texto);margin-top:10px;">
                                        🔒 Tu información de pago es cifrada con SSL
                                    </p>
                                </div>
                            </div>

                            {{-- ─ PayPal ─ --}}
                            <label class="payment-option" id="pay-opt-paypal">
                                <input type="radio" name="payment_radio" value="paypal">
                                <div class="payment-radio"></div>
                                <div class="payment-icon-box">
                                    <img src="{{ asset('frontend/images/iconos-empresas/Paypal-logo_with_bgc.webp') }}" alt="PayPal">
                                </div>
                                <div class="payment-option-info">
                                    <div class="payment-option-name">PayPal</div>
                                    <div class="payment-option-desc">Paga con tu cuenta PayPal de forma segura</div>
                                </div>
                            </label>

                            {{-- PayPal buttons (hidden until selected) --}}
                            <div class="payment-detail-panel" id="paypal-detail-panel">
                                <div class="paypal-panel-inner">
                                    <div class="paypal-panel-title">Completa tu pago con PayPal</div>
                                    <div id="paypal-button-container"></div>
                                </div>
                            </div>

                            {{-- ─ SPEI / BBVA ─ --}}
                            <label class="payment-option" id="pay-opt-spei">
                                <input type="radio" name="payment_radio" value="spei">
                                <div class="payment-radio"></div>
                                <div class="payment-icon-box">
                                    <img src="{{ asset('frontend/images/iconos-empresas/bank_BBVA-logo_with_bgc.webp') }}" alt="BBVA SPEI">
                                </div>
                                <div class="payment-option-info">
                                    <div class="payment-option-name">Transferencia SPEI · BBVA</div>
                                    <div class="payment-option-desc">Transferencia bancaria electrónica</div>
                                </div>
                                <span class="payment-discount-badge">2% descuento</span>
                            </label>

                            {{-- SPEI details + form (hidden until selected) --}}
                            <div class="payment-detail-panel" id="spei-detail-panel">
                                <div class="spei-panel-inner">
                                    <div class="spei-panel-title">Datos para transferencia SPEI</div>

                                    <div class="spei-data-row">
                                        <span class="lbl">Banco</span>
                                        <span class="val">{{ $transferInfo->nameBank ?? 'BBVA' }}</span>
                                    </div>
                                    <div class="spei-data-row">
                                        <span class="lbl">Beneficiario</span>
                                        <span class="val">{{ $transferInfo->nameTitular ?? 'MAC DEL NORTE SA DE CV' }}</span>
                                    </div>

                                    {{-- CLABE con botón copiar --}}
                                    <div class="spei-clabe-row">
                                        <span class="spei-clabe-num" id="spei-clabe">{{ $transferInfo->accountClabe ?? '000000000000000000' }}</span>
                                        <button type="button" class="btn-copy-clabe" id="btn-copy-clabe">Copiar</button>
                                    </div>

                                    {{-- Número de referencia / pedido --}}
                                    <div class="spei-ref-box">
                                        <div class="spei-ref-label">Referencia / Número de pedido</div>
                                        <div class="spei-ref-number">{{ $refBank }}</div>
                                    </div>

                                    <div class="spei-note">
                                        Recibirás un <strong>2% de descuento</strong> al pagar por transferencia.<br>
                                        Envía el comprobante a <strong>ventas@macdelnorte.com</strong> indicando el número de referencia.
                                    </div>

                                    {{-- Formulario oculto que se submiteará con JS --}}
                                    <form action="{{ route('user.payment.transfer') }}" method="POST" id="spei-form" style="display:none;">
                                        @csrf
                                        <input type="hidden" name="refBank" value="{{ $refBank }}">
                                    </form>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                {{-- ── SECCIÓN 4: CONFIRMACIÓN ─────────────── --}}
                <div class="checkout-section checkout-section--locked" id="section-4">
                    <div class="checkout-section-header">
                        <div class="checkout-section-num">4</div>
                        <h2 class="checkout-section-title">Confirmar pedido</h2>
                        <span class="section-badge s-pending" id="badge-4">Pendiente</span>
                    </div>
                    <div class="checkout-section-body">

                        <div class="terms-section">
                            <label class="terms-check">
                                <input class="agree_terms" type="checkbox" id="check_terms" value="1">
                                He leído y acepto los
                                <a href="{{ route('Terminos-Condiciones') }}" target="_blank">términos y condiciones</a>
                                de Mac Del Norte.
                            </label>
                            <label class="terms-check">
                                <input class="agree_privacy" type="checkbox" id="check_privacy" value="1">
                                He leído y acepto el
                                <a href="{{ route('Aviso-Privacidad') }}" target="_blank">aviso de privacidad</a>
                                de Mac Del Norte.
                            </label>
                            <label class="terms-check">
                                <input type="checkbox" id="check_newsletter" value="1">
                                Deseo recibir noticias y promociones por correo electrónico (opcional).
                            </label>
                        </div>

                        <div class="notes-group">
                            <label for="order_notes_input">Notas adicionales al pedido (opcional)</label>
                            <textarea id="order_notes_input"
                                      placeholder="Indicaciones especiales, horarios de entrega, etc."
                                      rows="3"></textarea>
                        </div>

                    </div>
                </div>

            </div>{{-- /izquierda --}}

            {{-- ════════════════════════════════════════════════
                 SIDEBAR
            ════════════════════════════════════════════════ --}}
            <div class="checkout-sidebar">

                {{-- Formulario oculto para guardar sesión vía AJAX --}}
                <form action="" id="checkOutForm">
                    <input type="hidden" name="shipping_method_id"  value="" id="shipping_method_id">
                    <input type="hidden" name="shipping_address_id"
                           value="{{ $addresses->first()?->id ?? '' }}"
                           id="shipping_address_id">
                    <input type="hidden" name="payment_method" value="" id="payment_method">
                    <input type="hidden" name="order_notes"    value="" id="order_notes">
                </form>

                <div class="checkout-summary-card">
                    <div class="checkout-summary-header">📦 Resumen del pedido</div>

                    <div class="checkout-products-list">
                        @foreach(\Cart::content() as $item)
                        <div class="checkout-product-item">
                            <div class="checkout-product-thumb">
                                <img src="{{ asset($item->options->image ?? 'frontend/images/logo/AVIAzul-Celeste.png') }}"
                                     alt="{{ $item->name }}" loading="lazy">
                            </div>
                            <div class="checkout-product-info">
                                <div class="checkout-product-name">{!! $item->name !!}</div>
                                @if($item->options->sku ?? false)
                                    <div class="checkout-product-sku">SKU: {{ $item->options->sku }}</div>
                                @endif
                                <div class="checkout-product-qty">Cant: {{ $item->qty }}</div>
                            </div>
                            <div class="checkout-product-price">
                                {{ $settings->currency_icon }}{{ number_format($item->price * $item->qty, 2, '.', ',') }}
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="checkout-totals">
                        <div class="checkout-total-row">
                            <span>Subtotal</span>
                            <span>{{ $settings->currency_icon }}{{ formatCurrency(getCartTotal()) }}</span>
                        </div>
                        @if(getCartDiscount() > 0)
                        <div class="checkout-total-row" style="color:#2F855A;font-weight:700;">
                            <span>Descuento cupón</span>
                            <span>− {{ $settings->currency_icon }}{{ formatCurrency(getCartDiscount()) }}</span>
                        </div>
                        @endif
                        <div class="checkout-total-row">
                            <span>Envío</span>
                            <span id="shipping_fee">{{ $settings->currency_icon }}0.00</span>
                        </div>
                        <div class="checkout-total-row sidebar-payment-row" id="sidebar-payment-row">
                            <span>Método de pago</span>
                            <span id="sidebar-payment-value">—</span>
                        </div>
                        <div class="checkout-total-row sidebar-spei-discount" id="sidebar-spei-discount">
                            <span>Descuento SPEI (2%)</span>
                            <span id="spei-discount-amount">−{{ $settings->currency_icon }}0.00</span>
                        </div>
                        <div class="checkout-total-row main">
                            <span>Total</span>
                            <span class="amount" id="total_amount" data-id="{{ getMainCartTotal() }}">
                                {{ $settings->currency_icon }}{{ formatCurrency(getMainCartTotal()) }}
                            </span>
                        </div>
                        <div class="total-iva-note">IVA incluido en el precio</div>

                        {{-- Botón principal (oculto cuando PayPal está seleccionado) --}}
                        <a href="javascript:void(0);" id="submitCheckoutForm" class="checkout-confirm-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                            Realizar pedido
                        </a>

                        {{-- Aviso PayPal --}}
                        <div class="paypal-btn-notice" id="paypal-btn-notice">
                            Usa el botón de PayPal en la sección de pago para completar tu compra.
                        </div>

                        <div class="checkout-security">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13">
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                            </svg>
                            Pago 100% seguro · SSL cifrado
                        </div>

                        <div style="text-align:center;margin-top:14px;padding-top:14px;border-top:1px solid var(--gris-borde);">
                            <div style="font-size:11px;color:var(--gris-claro-texto);margin-bottom:8px;font-weight:600;">FORMAS DE PAGO</div>
                            <div style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap;">
                                <img src="{{ asset('frontend/images/iconos-empresas/Visa_logo_with_bgc.webp') }}" alt="Visa" style="height:22px;border-radius:3px;">
                                <img src="{{ asset('frontend/images/iconos-empresas/mastercard-logo_with_bgc.webp') }}" alt="MC" style="height:22px;border-radius:3px;">
                                <img src="{{ asset('frontend/images/iconos-empresas/Paypal-logo_with_bgc.webp') }}" alt="PayPal" style="height:22px;border-radius:3px;">
                                <img src="{{ asset('frontend/images/iconos-empresas/bank_BBVA-logo_with_bgc.webp') }}" alt="SPEI" style="height:22px;border-radius:3px;">
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('cart-details') }}"
                   style="display:flex;align-items:center;justify-content:center;gap:6px;font-size:13px;color:var(--gris-claro-texto);text-decoration:none;padding:8px 0;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Volver al carrito
                </a>

            </div>{{-- /sidebar --}}

        </div>{{-- /checkout-layout --}}
    </div>{{-- /container --}}
</section>

@endsection

@push('scripts')
{{-- Stripe.js --}}
@if($stripeSetting)
<script src="https://js.stripe.com/v3/"></script>
@endif

{{-- PayPal SDK --}}
@if($paypalInfo)
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalInfo->client_id }}&currency=MXN" defer></script>
@endif

@vite(['resources/js/checkout.js'])

<script>
// ── SECTION UNLOCK (before transitions) ────────────────────────────
(function () {
    function unlockSection(id) {
        var sec = document.getElementById('section-' + id);
        if (!sec) return;
        sec.classList.remove('checkout-section--locked');
        sec.classList.add('checkout-section--unlocked');
    }
    function completeSection(id) {
        var sec = document.getElementById('section-' + id);
        if (!sec) return;
        sec.classList.add('checkout-section--completed');
        var badge = document.getElementById('badge-' + id);
        if (badge) { badge.textContent = 'Completado'; badge.className = 'section-badge s-done'; }
    }

    @if($addresses->isNotEmpty())
        unlockSection(2);
        completeSection(1);
    @endif

    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            var page = document.querySelector('.checkout-page');
            if (page) page.classList.add('transitions-ready');
        });
    });
})();

// ── STRIPE SETUP ────────────────────────────────────────────────────
@if($stripeSetting)
var stripe      = Stripe("{{ $stripeSetting->client_id }}");
var stripeElems = stripe.elements();
var cardElement = stripeElems.create('card', {
    style: {
        base: { fontSize: '14px', color: '#1A202C', '::placeholder': { color: '#A0AEC0' } },
        invalid: { color: '#DC2626' }
    }
});
// Monte se realiza cuando el panel es visible (lo hacemos en click del método)
var stripeCardMounted = false;

cardElement.on('change', function (event) {
    var errDiv = document.getElementById('stripe-card-errors');
    errDiv.textContent = event.error ? event.error.message : '';
});
@endif

// ── PAYPAL BUTTONS SETUP ────────────────────────────────────────────
@if($paypalInfo)
window.initPayPalButtons = function () {
    var container = document.getElementById('paypal-button-container');
    if (!container || container.hasChildNodes()) return;

    paypal.Buttons({
        createOrder: function (data, actions) {
            // Primero guardamos sesión, luego creamos la orden PayPal
            return fetch('{{ route('user.checkout.form-submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: new URLSearchParams(new FormData(document.getElementById('checkOutForm')))
            })
            .then(function (r) { return r.json(); })
            .then(function () {
                return fetch('{{ route('user.paypal.createOrder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(function (r) { return r.json(); })
                  .then(function (d) { return d.id; });
            });
        },
        onApprove: function (data, actions) {
            return fetch('{{ route('user.paypal.captureOrder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ orderId: data.orderID })
            }).then(function (r) { return r.json(); })
              .then(function (details) {
                  if (details.redirect_url) {
                      window.location.href = details.redirect_url;
                  } else {
                      toastr.error('Error al procesar el pago. Inténtalo de nuevo.');
                  }
              });
        },
        onError: function (err) {
            toastr.error('Ocurrió un error con PayPal. Inténtalo de nuevo.');
        }
    }).render('#paypal-button-container');
};
@endif

// ── MAIN JQUERY LOGIC ───────────────────────────────────────────────
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $('input[name="payment_radio"]').prop('checked', false);
    $('input[name="shipping_method_radio"]').prop('checked', false);
    $('#shipping_method_id').val('');

    @if($addresses->isNotEmpty())
    $('#shipping_address_id').val('{{ $addresses->first()->id }}');
    @endif

    var baseTotal    = parseFloat($('#total_amount').attr('data-id')) || 0;
    var currentTotal = baseTotal;
    var speiActive   = false;
    var currIcon     = "{{ $settings->currency_icon }}";

    function fmt(num) { return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'); }

    function unlockSection(id) {
        var sec = document.getElementById('section-' + id);
        if (!sec) return;
        sec.classList.remove('checkout-section--locked');
        sec.classList.add('checkout-section--unlocked');
        sec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function completeSection(id) {
        var sec = document.getElementById('section-' + id);
        if (!sec) return;
        sec.classList.add('checkout-section--completed');
        var badge = document.getElementById('badge-' + id);
        if (badge) { badge.textContent = 'Completado'; badge.className = 'section-badge s-done'; }
    }

    function updateTotalDisplay() {
        if (speiActive) {
            var discount = currentTotal * 0.02;
            var final    = currentTotal - discount;
            $('#spei-discount-amount').text('−' + currIcon + fmt(discount));
            $('#sidebar-spei-discount').addClass('visible');
            $('#total_amount').text(currIcon + fmt(final));
        } else {
            $('#sidebar-spei-discount').removeClass('visible');
            $('#total_amount').text(currIcon + fmt(currentTotal));
        }
    }

    // ── Shipping selection (escucha el label visible, no el radio oculto) ───
    $(document).on('change', 'input.shipping_method', function () {
        var fee = parseFloat($(this).data('id')) || 0;
        currentTotal = baseTotal + fee;
        $('#shipping_method_id').val($(this).val());
        $('#shipping_fee').text(currIcon + fmt(fee));
        $('.shipping-option').removeClass('selected');
        $(this).closest('.shipping-option').addClass('selected');
        updateTotalDisplay();
        completeSection(2);
        unlockSection(3);
        validateSubmitBtn();
    });

    // ── Address selection ──────────────────────────────────────────
    $(document).on('change', 'input.shipping_address', function () {
        $('#shipping_address_id').val($(this).data('id'));
        $('.address-card').removeClass('selected');
        $(this).closest('.address-card').addClass('selected');
        completeSection(1);
        unlockSection(2);
        validateSubmitBtn();
    });

    // ── Payment method selection ───────────────────────────────────
    $(document).on('change', 'input[name="payment_radio"]', function () {
        var val  = $(this).val();
        var $opt = $(this).closest('.payment-option');
        $('.payment-option').removeClass('selected');
        $opt.addClass('selected');
        $('#payment_method').val(val);

        // Close all detail panels
        $('.payment-detail-panel').removeClass('active');

        // Open the corresponding panel
        if (val === 'stripe') {
            $('#stripe-detail-panel').addClass('active');
            @if($stripeSetting)
            // Mount Stripe Elements on first open
            if (!stripeCardMounted) {
                cardElement.mount('#stripe-card-element');
                stripeCardMounted = true;
            }
            @endif
        } else if (val === 'paypal') {
            $('#paypal-detail-panel').addClass('active');
            @if($paypalInfo)
            // Render PayPal buttons on first open
            if (typeof window.initPayPalButtons === 'function') {
                // PayPal SDK might still be loading
                if (typeof paypal !== 'undefined') {
                    window.initPayPalButtons();
                } else {
                    document.querySelector('script[src*="paypal.com"]').addEventListener('load', window.initPayPalButtons);
                }
            }
            @endif
        } else if (val === 'spei') {
            $('#spei-detail-panel').addClass('active');
        }

        speiActive = (val === 'spei');
        updateTotalDisplay();

        // Sidebar payment label
        var names = { stripe: 'Tarjeta (Stripe)', paypal: 'PayPal', spei: 'SPEI / BBVA' };
        $('#sidebar-payment-value').text(names[val] || val);
        $('#sidebar-payment-row').addClass('visible');

        // PayPal: show notice instead of main button
        if (val === 'paypal') {
            $('#submitCheckoutForm').hide();
            $('#paypal-btn-notice').addClass('visible');
        } else {
            $('#submitCheckoutForm').show();
            $('#paypal-btn-notice').removeClass('visible');
        }

        completeSection(3);
        unlockSection(4);
        validateSubmitBtn();
    });

    // ── Copy CLABE ─────────────────────────────────────────────────
    $('#btn-copy-clabe').on('click', function () {
        var clabe = document.getElementById('spei-clabe').textContent.replace(/\s/g, '');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(clabe).then(function () {
                $('#btn-copy-clabe').text('¡Copiado!');
                setTimeout(function () { $('#btn-copy-clabe').text('Copiar'); }, 2200);
            });
        }
    });

    // ── Notes sync ─────────────────────────────────────────────────
    $('#order_notes_input').on('input', function () { $('#order_notes').val($(this).val()); });

    // ── Validate form readiness ────────────────────────────────────
    function validateSubmitBtn() {
        var hasAddress  = !!$('#shipping_address_id').val();
        var hasShipping = !!$('#shipping_method_id').val();
        var hasPayment  = !!$('#payment_method').val();
        var hasTerms    = $('#check_terms').prop('checked');
        var hasPrivacy  = $('#check_privacy').prop('checked');
        var isValid = hasAddress && hasShipping && hasPayment && hasTerms && hasPrivacy;
        $('#submitCheckoutForm').toggleClass('btn-ready', isValid);
    }
    $(document).on('change', 'input[type="checkbox"]', validateSubmitBtn);
    validateSubmitBtn();

    // ── Save session via AJAX (helper) ─────────────────────────────
    function saveSession(callback) {
        $.ajax({
            url: "{{ route('user.checkout.form-submit') }}",
            method: 'POST',
            data: $('#checkOutForm').serialize(),
            success: function (res) {
                if (res.status === 'session_saved') callback(null);
                else callback('Error al guardar la sesión');
            },
            error: function (xhr) {
                var msg = 'Error al procesar. Intenta de nuevo.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var msgs = [];
                    $.each(xhr.responseJSON.errors, function (k, v) { msgs = msgs.concat(v); });
                    msg = msgs.join(', ');
                }
                callback(msg);
            }
        });
    }

    // ── Main submit button ─────────────────────────────────────────
    $('#submitCheckoutForm').on('click', function (e) {
        e.preventDefault();
        var method = $('#payment_method').val();

        if (!$('#shipping_address_id').val()) { toastr.error('Selecciona una dirección de entrega'); return; }
        if (!$('#shipping_method_id').val())  { toastr.error('Selecciona un método de envío'); return; }
        if (!method)                          { toastr.error('Selecciona un método de pago'); return; }
        if (!$('#check_terms').prop('checked'))   { toastr.error('Debes aceptar los términos y condiciones'); return; }
        if (!$('#check_privacy').prop('checked')) { toastr.error('Debes aceptar el aviso de privacidad'); return; }

        var $btn = $(this);
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...').css({'opacity':'0.7','pointer-events':'none'});

        // Save session first, then handle each payment method
        saveSession(function (err) {
            if (err) {
                $btn.html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M5 12h14M12 5l7 7-7 7"/></svg> Realizar pedido')
                    .css({'opacity':'','pointer-events':''});
                toastr.error(err);
                return;
            }

            if (method === 'spei') {
                // Submit SPEI form directly
                document.getElementById('spei-form').submit();

            } else if (method === 'stripe') {
                @if($stripeSetting)
                // Create Stripe token, then submit Stripe form
                stripe.createToken(cardElement).then(function (result) {
                    if (result.error) {
                        document.getElementById('stripe-card-errors').textContent = result.error.message;
                        $btn.html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M5 12h14M12 5l7 7-7 7"/></svg> Realizar pedido')
                            .css({'opacity':'','pointer-events':''});
                    } else {
                        document.getElementById('stripe-token-id').value = result.token.id;
                        document.getElementById('stripe-checkout-form').submit();
                    }
                });
                @else
                toastr.error('Pago con tarjeta no configurado.');
                $btn.html('Realizar pedido').css({'opacity':'','pointer-events':''});
                @endif

            } else if (method === 'paypal') {
                $btn.html('Realizar pedido').css({'opacity':'','pointer-events':''});
                toastr.info('Usa el botón de PayPal en la sección de pago para continuar.');
            }
        });
    });

    // ── Save new address ───────────────────────────────────────────
    $('#saveAddressButton').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.hasClass('saving')) return;
        $btn.addClass('saving').text('Guardando...');
        $(this).closest('form').submit();
    });
});
</script>
@endpush

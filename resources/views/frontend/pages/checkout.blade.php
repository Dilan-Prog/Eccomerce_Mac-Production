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
  .payment-method-list { display: flex; flex-direction: column; gap: 12px; position: relative; }
  .payment-option {
      display: flex; align-items: center; gap: 14px;
      border: 2px solid var(--gris-borde, #DDE3EA); border-radius: 12px;
      padding: 18px 16px; cursor: pointer; transition: all 0.18s;
      background: #fff;
  }
  .payment-option:hover { border-color: var(--azul-medio, #0057A8); background: var(--azul-claro, #E6EFF8); }
  .payment-option.selected {
      border-color: var(--azul-principal, #003E7E); background: var(--azul-claro, #E6EFF8);
      box-shadow: 0 2px 10px rgba(0, 62, 126, 0.10);
  }
  /* Cuando la opcion tiene su panel abierto justo debajo, se unen visualmente:
     se quitan las esquinas y el borde de en medio para que se lean como una
     sola pieza en vez de dos cajas sueltas. */
  .payment-option.has-panel-open {
      border-bottom-left-radius: 0; border-bottom-right-radius: 0;
      border-bottom-color: transparent; margin-bottom: -12px;
  }
  .payment-option input[type="radio"] { display: none; }
  .payment-radio {
      width: 20px; height: 20px; border: 2px solid var(--gris-borde, #DDE3EA);
      border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
      transition: all 0.18s;
  }
  .payment-option.selected .payment-radio {
      border-color: var(--azul-principal, #003E7E); background: var(--azul-principal, #003E7E);
  }
  .payment-option.selected .payment-radio::after {
      content: ''; width: 7px; height: 7px; background: #fff; border-radius: 50%;
  }
  .payment-icon-box {
      width: 52px; height: 36px; background: #fff;
      border: 1px solid var(--gris-borde, #DDE3EA); border-radius: 6px;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .payment-icon-box img { height: 22px; object-fit: contain; }
  /* Fila de logos de tarjeta: comunica de un vistazo que se paga con tarjeta,
     sin depender de leer el texto. */
  .payment-card-logos { display: flex; gap: 5px; flex-shrink: 0; }
  .payment-card-logos img {
      height: 30px; width: 42px; object-fit: contain; background: #fff;
      border: 1px solid var(--gris-borde, #DDE3EA); border-radius: 5px; padding: 2px;
  }
  .payment-option-info { flex: 1; min-width: 0; }
  .payment-option-name { font-size: 15px; font-weight: 700; color: var(--azul-principal, #003E7E); }
  .payment-option-desc { font-size: 12.5px; color: var(--gris-claro-texto, #718096); margin-top: 3px; line-height: 1.4; }
  .payment-option-tag {
      font-size: 10.5px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;
      padding: 4px 9px; border-radius: 999px; white-space: nowrap; flex-shrink: 0;
      background: #D1FAE5; color: #065F46;
  }
  @media (max-width: 560px) {
      .payment-option { flex-wrap: wrap; gap: 10px; padding: 15px 13px; }
      .payment-card-logos img { height: 26px; width: 36px; }
      .payment-option-tag { order: 3; }
  }
  .payment-discount-badge {
      font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px;
      background: #D1FAE5; color: #065F46; white-space: nowrap;
  }

  /* ── PAYMENT DETAIL PANELS ──────────────────────────── */
  .payment-detail-panel {
      display: none; margin-top: 12px;
      border-radius: 10px; overflow: hidden;
      position: relative;
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
  #paypal-detail-panel {
      position: relative;
      isolation: isolate;
      z-index: auto;
      overflow: hidden;
  }
  .paypal-panel-inner {
      /* Se une con la opcion seleccionada de arriba: mismo borde azul, sin
         esquinas superiores, para que se lea como una sola tarjeta. */
      border: 2px solid var(--azul-principal, #003E7E); border-top: 0;
      border-radius: 0 0 12px 12px;
      padding: 20px 18px; background: #fff; text-align: center;
      position: relative;
      isolation: isolate;
  }
  .paypal-panel-title { font-size: 13.5px; font-weight: 700; color: var(--azul-principal, #003E7E); margin-bottom: 14px; }
  .paypal-btn-slot { max-width: 340px; margin: 0 auto; position: relative; overflow: hidden; }
  /* Botones propios (el SDK v6 ya no dibuja los suyos). Se les da el aspecto
     de los de PayPal para que el cliente los reconozca al instante. */
  .pp-btn {
      display: flex; align-items: center; justify-content: center; gap: 9px;
      width: 100%; max-width: 340px; margin: 0 auto;
      padding: 13px 18px; border: 0; border-radius: 8px; cursor: pointer;
      font-size: 15px; font-weight: 600; transition: filter 0.18s;
  }
  .pp-btn:hover { filter: brightness(0.93); }
  .pp-btn:active { transform: translateY(1px); }
  .pp-btn[disabled] { opacity: 0.6; cursor: default; transform: none; }
  .pp-btn-card { background: #2C2E2F; color: #fff; }
  paypal-button { display: block; max-width: 340px; margin: 0 auto; }
  /* El formulario y el boton los dibuja PayPal dentro de sus propios
     elementos. Sin estilos propios ahi adentro: la maqueta es suya. */
  paypal-basic-card-container { display: block; }
  .paypal-panel-note {
      font-size: 11.5px; color: var(--gris-claro-texto, #718096);
      margin: 12px auto 0; max-width: 340px; line-height: 1.5;
  }
  /* Modal de pago rechazado. El motivo del banco es lo unico que le permite
     al cliente corregir y volver a intentar, asi que se muestra centrado y
     bloqueando la pantalla en vez de en un toast que se va solo. */
  .pago-rechazado-fondo {
      position: fixed; inset: 0; z-index: 10050;
      display: none; align-items: center; justify-content: center;
      padding: 20px; background: rgba(11, 22, 40, 0.55);
      -webkit-backdrop-filter: blur(2px); backdrop-filter: blur(2px);
  }
  .pago-rechazado-fondo.abierto { display: flex; }
  .pago-rechazado-caja {
      width: 100%; max-width: 440px; background: #fff; border-radius: 14px;
      padding: 28px 26px 22px; text-align: center;
      box-shadow: 0 18px 48px rgba(11, 22, 40, 0.28);
      animation: pagoRechazadoEntra 0.18s ease-out;
  }
  @keyframes pagoRechazadoEntra {
      from { opacity: 0; transform: translateY(10px) scale(0.98); }
      to   { opacity: 1; transform: none; }
  }
  .pago-rechazado-icono {
      width: 54px; height: 54px; margin: 0 auto 14px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      background: #FEE2E2; color: #DC2626;
  }
  .pago-rechazado-caja h4 {
      margin: 0 0 8px; font-size: 19px; font-weight: 800;
      color: var(--azul-principal, #003E7E);
  }
  .pago-rechazado-msg {
      margin: 0; font-size: 14px; line-height: 1.55;
      color: var(--gris-texto, #4A5568);
  }
  .pago-rechazado-sug {
      margin: 18px 0 0; padding: 14px 16px; border-radius: 10px;
      background: #F4F7FB; text-align: left;
  }
  .pago-rechazado-sug strong {
      display: block; font-size: 12px; font-weight: 800; margin-bottom: 8px;
      color: var(--azul-principal, #003E7E); text-transform: uppercase;
      letter-spacing: 0.4px;
  }
  .pago-rechazado-sug ul { list-style: none; margin: 0; padding: 0; }
  .pago-rechazado-sug li {
      position: relative; padding-left: 18px; margin-bottom: 7px;
      font-size: 13px; line-height: 1.5; color: var(--gris-texto, #4A5568);
  }
  .pago-rechazado-sug li:last-child { margin-bottom: 0; }
  .pago-rechazado-sug li::before {
      content: ''; position: absolute; left: 4px; top: 8px;
      width: 5px; height: 5px; border-radius: 50%;
      background: var(--azul-principal, #003E7E);
  }
  .pago-rechazado-btn {
      width: 100%; margin: 20px 0 0; padding: 14px 18px;
      border: 0; border-radius: 10px; cursor: pointer;
      background: var(--azul-principal, #003E7E); color: #fff;
      font-size: 15px; font-weight: 700; transition: background 0.18s;
  }
  .pago-rechazado-btn:hover { background: var(--azul-medio, #0057A8); }
  .pago-rechazado-cod {
      margin: 12px 0 0; font-size: 11px;
      color: var(--gris-claro-texto, #718096);
  }

  /* Separador entre el camino principal (tarjeta) y la alternativa (PayPal). */
  .paypal-alt-sep {
      display: flex; align-items: center; gap: 12px;
      max-width: 340px; margin: 18px auto 14px;
      color: var(--gris-claro-texto, #718096); font-size: 11.5px;
  }
  .paypal-alt-sep::before, .paypal-alt-sep::after {
      content: ''; flex: 1; height: 1px; background: var(--gris-borde, #DDE3EA);
  }

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
                                        <input class="form-input @error('name') is-invalid @enderror" type="text" id="address-name" name="name" value="{{ old('name') }}" placeholder="Ej. Roberto Martínez">
                                        <div class="field-error" id="address-name-error" style="display:none"></div>
                                        @error('name')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Teléfono <span>*</span></label>
                                        <input class="form-input @error('phone') is-invalid @enderror" type="text" id="address-phone" name="phone" value="{{ old('phone') }}" placeholder="10 dígitos" inputmode="numeric" maxlength="10">
                                        <div class="field-error" id="address-phone-error" style="display:none"></div>
                                        @error('phone')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label class="form-label">Correo electrónico <span>*</span></label>
                                    <input class="form-input @error('email') is-invalid @enderror" type="email" id="address-email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com">
                                    <div class="field-error" id="address-email-error" style="display:none"></div>
                                    @error('email')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-row-3" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Código Postal <span>*</span></label>
                                        <input class="form-input @error('zip') is-invalid @enderror" type="text" id="address-zip" name="zip" value="{{ old('zip') }}" placeholder="00000" inputmode="numeric" maxlength="5">
                                        <div class="field-error" id="address-zip-error" style="display:none"></div>
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
                                        <input class="form-input @error('city') is-invalid @enderror" type="text" id="address-city" name="city" value="{{ old('city') }}" placeholder="Monterrey">
                                        <div class="field-error" id="address-city-error" style="display:none"></div>
                                        @error('city')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row-2" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Colonia <span>*</span></label>
                                        <input class="form-input @error('col') is-invalid @enderror" type="text" id="address-col" name="col" value="{{ old('col') }}" placeholder="Colonia / Fraccionamiento">
                                        <div class="field-error" id="address-col-error" style="display:none"></div>
                                        @error('col')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Calle <span>*</span></label>
                                        <input class="form-input @error('street') is-invalid @enderror" type="text" id="address-street" name="street" value="{{ old('street') }}" placeholder="Nombre de la calle">
                                        <div class="field-error" id="address-street-error" style="display:none"></div>
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

                        @php $hasShipping = false; $autoSelectShippingId = null; @endphp
                        <div class="shipping-options">
                            @foreach ($shippingMethod as $method)
                                @php $show = false; @endphp
                                @if ($method->type == 'min_cost' && getCartTotal() >= $method->min_cost)
                                    @php $show = true; @endphp
                                    {{-- El pedido ya califica para envío gratis: se pre-selecciona
                                         automáticamente más abajo (ver autoSelectShippingId en el
                                         <script>), sin ocultar ni deshabilitar el resto de opciones
                                         (incluido Envío Internacional) por si el cliente prefiere otra. --}}
                                    @php $autoSelectShippingId = $autoSelectShippingId ?? $method->id; @endphp
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
                            @if($stripeSetting)
                            <label class="payment-option" id="pay-opt-stripe">
                                <input type="radio" name="payment_radio" value="stripe">
                                <div class="payment-radio"></div>
                                <div class="payment-icon-box">
                                    <img src="{{ asset('frontend/images/iconos-empresas/Visa_logo_with_bgc.webp') }}" alt="Tarjeta">
                                </div>
                                <div class="payment-option-info">
                                    <div class="payment-option-name">Tarjeta de crédito / débito</div>
                                    <div class="payment-option-desc">
                                        Visa, Mastercard — pago seguro con Stripe
                                        @if($stripeSetting->mode == 0)
                                            <span style="font-size:10px;color:#B45309;font-weight:700;">[SANDBOX]</span>
                                        @endif
                                    </div>
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
                            @endif

                            {{-- ─ Tarjeta (se procesa con PayPal) ─
                                 Se presenta como "Tarjeta de débito / crédito" porque
                                 es lo que el cliente busca: PayPal es solo la pasarela
                                 que lo cobra, y permite pagar con tarjeta sin tener
                                 cuenta de PayPal. El value del radio sigue siendo
                                 'paypal' — es lo que esperan el JS y el backend. --}}
                            @if($paypalInfo)
                            <label class="payment-option" id="pay-opt-paypal">
                                <input type="radio" name="payment_radio" value="paypal">
                                <div class="payment-radio"></div>
                                <div class="payment-card-logos">
                                    <img src="{{ asset('frontend/images/iconos-empresas/Visa_logo_with_bgc.webp') }}" alt="Visa">
                                    <img src="{{ asset('frontend/images/iconos-empresas/mastercard-logo_with_bgc.webp') }}" alt="Mastercard">
                                    <img src="{{ asset('frontend/images/iconos-empresas/American-Express-Color_with_bgc.webp') }}" alt="American Express">
                                </div>
                                <div class="payment-option-info">
                                    <div class="payment-option-name">Tarjeta de Débito / Crédito</div>
                                    <div class="payment-option-desc">
                                        Visa, Mastercard y American Express · pago seguro
                                        @if($paypalInfo->mode == 0)
                                            <span style="font-size:10px;color:#B45309;font-weight:700;">[SANDBOX]</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="payment-option-tag">Recomendado</span>
                            </label>

                            {{-- Modal de pago rechazado. Se llena desde JS con el
                                 motivo que devuelve PaymentController::motivoRechazo(). --}}
                            <div class="pago-rechazado-fondo" id="pago-rechazado">
                                <div class="pago-rechazado-caja" role="alertdialog" aria-modal="true"
                                     aria-labelledby="pago-rechazado-titulo">
                                    <div class="pago-rechazado-icono">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2.2" width="26" height="26" aria-hidden="true">
                                            <circle cx="12" cy="12" r="9"/>
                                            <path d="M15 9l-6 6M9 9l6 6"/>
                                        </svg>
                                    </div>
                                    <h4 id="pago-rechazado-titulo">No pudimos procesar tu pago</h4>
                                    <p class="pago-rechazado-msg" id="pago-rechazado-msg"></p>
                                    <div class="pago-rechazado-sug" id="pago-rechazado-sug">
                                        <strong>Qué puedes hacer</strong>
                                        <ul id="pago-rechazado-lista"></ul>
                                    </div>
                                    <button type="button" class="pago-rechazado-btn" id="pago-rechazado-cerrar">
                                        Intentar de nuevo
                                    </button>
                                    <p class="pago-rechazado-cod" id="pago-rechazado-cod"></p>
                                </div>
                            </div>

                            {{-- Botones de PayPal. Se renderizan por separado (ver
                                 initPayPalButtons): arriba el de TARJETA, que es el
                                 camino principal, y abajo el de PayPal como
                                 alternativa. Los dos cobran por la misma pasarela. --}}
                            <div class="payment-detail-panel" id="paypal-detail-panel">
                                <div class="paypal-panel-inner">
                                    {{-- Los botones son NUESTROS: el SDK v6 ya no los dibuja,
                                         solo expone las sesiones de pago. El rotulo deja de vivir
                                         dentro de un iframe de PayPal, asi que por fin se puede
                                         escribir en espanol. Ambos arrancan ocultos y se muestran
                                         segun findEligibleMethods(). --}}
                                    {{-- Elementos propios del SDK v6: el boton TIENE que ser
                                         hijo directo del contenedor o el SDK se niega a arrancar
                                         ("<paypal-basic-card-button> must be a direct child of
                                         <paypal-basic-card-container>"). PayPal dibuja el boton
                                         dentro y, al pulsarlo, despliega ahi mismo el formulario. --}}
                                    <div id="paypal-card-block" style="display:none">
                                        <paypal-basic-card-container id="paypal-card-container">
                                            <paypal-basic-card-button id="paypal-btn-card"></paypal-basic-card-button>
                                        </paypal-basic-card-container>
                                        <p class="paypal-panel-note" id="paypal-card-note">
                                            Captura tus datos aquí mismo. <strong>No sales del sitio</strong>
                                            y no necesitas cuenta de PayPal.
                                        </p>
                                    </div>

                                    <div class="paypal-alt-sep" id="paypal-alt-sep" style="display:none">
                                        <span>o si prefieres</span>
                                    </div>

                                    <div id="paypal-account-block" style="display:none">
                                        {{-- Elemento del SDK: PayPal exige sus propios botones de
                                             marca. `type="pay"` rotula "Pagar con PayPal". --}}
                                        <paypal-button id="paypal-btn-paypal" type="pay"
                                                       class="paypal-gold"></paypal-button>
                                        <p class="paypal-panel-note">
                                            Se abre la ventana segura de PayPal para que inicies sesión
                                            en tu cuenta.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif

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
                            Completa tu compra en el recuadro de <strong>Tarjeta de Débito / Crédito</strong>, en el paso 3.
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

{{-- PayPal SDK: sandbox usa el mismo endpoint, el client_id determina el entorno --}}
@if($paypalInfo)
{{-- SDK Web v6. Es el unico que sabe dibujar el formulario de tarjeta
     DENTRO de la pagina (presentationMode); el v5 solo abria una ventana
     emergente. Autentica con el client-id, que es seguro en el front, asi que
     ya no hace falta el client token del servidor.

     La promesa se declara ANTES de la etiqueta para no perder el onload: el
     script es async y puede terminar de cargar antes de que corra el resto
     del JS de esta pagina. --}}
<script>
    window.__paypalV6 = new Promise(function (resolver) { window.__paypalV6Listo = resolver; });
</script>
<script async src="{{ $paypalInfo->webSdkBase() }}"
        onload="window.__paypalV6Listo()"></script>
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
var stripe = null, stripeElems = null, cardElement = null, stripeCardMounted = false;
try {
    stripe      = Stripe("{{ $stripeSetting->activeClientId() }}");
    stripeElems = stripe.elements();
    cardElement = stripeElems.create('card', {
        style: {
            base: { fontSize: '14px', color: '#1A202C', '::placeholder': { color: '#A0AEC0' } },
            invalid: { color: '#DC2626' }
        }
    });
    cardElement.on('change', function (event) {
        var errDiv = document.getElementById('stripe-card-errors');
        errDiv.textContent = event.error ? event.error.message : '';
    });
} catch (e) {
    console.warn('Stripe no disponible (requiere HTTPS):', e.message);
}
@endif

// ── PAYPAL BUTTONS SETUP ────────────────────────────────────────────
@if($paypalInfo)
window._paypalRendered = false;
window.initPayPalButtons = function () {
    if (window._paypalRendered) return;
    window._paypalRendered = true;

    // Configuración compartida por los DOS botones (tarjeta y PayPal): ambos
    // cobran por la misma pasarela y contra los mismos endpoints, lo único
    // que cambia es el fundingSource con el que se renderizan.
    // Muestra el motivo del rechazo que devuelve el backend. Se usa un modal
    // y no un toast porque el cliente necesita leer la recomendacion con calma
    // para poder corregir; un aviso que se desvanece solo no sirve aqui.
    function mostrarPagoRechazado(rechazo) {
        var fondo = document.getElementById('pago-rechazado');
        if (!fondo) {
            toastr.error((rechazo && rechazo.mensaje) || 'No pudimos procesar tu pago.');
            return;
        }

        var datos = rechazo || {};
        document.getElementById('pago-rechazado-titulo').textContent =
            datos.titulo || 'No pudimos procesar tu pago';
        document.getElementById('pago-rechazado-msg').textContent =
            datos.mensaje || 'El banco no autorizó el cargo. No se realizó ningún cobro a tu tarjeta.';

        var lista = document.getElementById('pago-rechazado-lista');
        var caja = document.getElementById('pago-rechazado-sug');
        lista.innerHTML = '';
        var sugerencias = datos.sugerencias || [];
        sugerencias.forEach(function (texto) {
            var li = document.createElement('li');
            li.textContent = texto;
            lista.appendChild(li);
        });
        caja.style.display = sugerencias.length ? '' : 'none';

        // El codigo del banco solo sirve si el cliente llama a soporte, por eso
        // va discreto al pie y no en el mensaje principal.
        var cod = document.getElementById('pago-rechazado-cod');
        cod.textContent = datos.codigo ? ('Código de referencia: ' + datos.codigo) : '';

        fondo.classList.add('abierto');
    }

    (function () {
        var fondo = document.getElementById('pago-rechazado');
        if (!fondo) return;
        function cerrar() { fondo.classList.remove('abierto'); }
        document.getElementById('pago-rechazado-cerrar').addEventListener('click', cerrar);
        // Clic fuera de la caja y Escape: salidas esperadas en cualquier modal.
        fondo.addEventListener('click', function (e) { if (e.target === fondo) cerrar(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && fondo.classList.contains('abierto')) cerrar();
        });
    })();

    // ── Crear la orden ───────────────────────────────────────────────
    // Primero guarda la sesion del checkout, luego pide la orden a PayPal.
    // Devuelve una promesa que resuelve al id de la orden, que es lo que
    // espera session.start(). Es la misma cadena de endpoints que usaba el
    // SDK v5: el backend no distingue por donde entro el pago.
    function crearOrden() {
        return fetch('{{ route('user.checkout.form-submit') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: new URLSearchParams(new FormData(document.getElementById('checkOutForm')))
        })
        .then(function (r) {
            // Si el checkout viene incompleto se corta aqui con el motivo
            // real (ej. "Selecciona una direccion de envio") en vez de dejar
            // que aparezca despues como un error generico de PayPal.
            return r.json().then(function (body) {
                if (!r.ok) {
                    var msg = 'No se pudo preparar tu pedido.';
                    if (body && body.errors) {
                        for (var k in body.errors) { msg = body.errors[k][0]; break; }
                    } else if (body && body.message) {
                        msg = body.message;
                    }
                    throw new Error(msg);
                }
                return body;
            });
        })
        .then(function () {
            return fetch('{{ route('user.paypal.createOrder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(function (r) {
                return r.json().then(function (d) {
                    if (!r.ok || !d.id) {
                        throw new Error((d && d.error) || 'No se pudo iniciar el pago.');
                    }
                    return { orderId: d.id };
                });
            });
        });
    }

    // ── Cobrar la orden aprobada ─────────────────────────────────────
    // v6 entrega el id en data.orderId (el v5 lo llamaba orderID).
    function alAprobar(data) {
        var id = data && (data.orderId || data.orderID);
        return fetch('{{ route('user.paypal.captureOrder') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ orderId: id })
        }).then(function (r) { return r.json(); })
          .then(function (details) {
              if (details.redirect_url) {
                  window.location.href = details.redirect_url;
                  return;
              }
              // El backend responde 402 con el motivo cuando el banco rechaza.
              if (details.rechazo) {
                  mostrarPagoRechazado(details.rechazo);
                  return;
              }
              mostrarPagoRechazado(null);
          })
          .catch(function () { mostrarPagoRechazado(null); });
    }

    function alFallar(err) {
        // Si el error trae mensaje propio (ej. el de una validacion del
        // checkout), se muestra ese en vez del generico.
        toastr.error((err && err.message) || 'Ocurrió un error con PayPal. Inténtalo de nuevo.');
    }

    // ── Arranque del SDK v6 ──────────────────────────────────────────
    // A diferencia del v5, aqui NO se renderizan botones: el SDK solo expone
    // sesiones de pago y los botones son nuestros. Lo que se gana con eso es
    // presentationMode, que dibuja el formulario de tarjeta dentro de la
    // pagina en vez de abrir una ventana emergente.
    window.__paypalV6.then(function () {
        return window.paypal.createInstance({
            clientId: '{{ $paypalInfo->activeClientId() }}',
            components: ['paypal-payments', 'paypal-guest-payments'],
            pageType: 'checkout',
            // BCP-47, con GUION. Con 'es_MX' el SDK lanza
            // ERR_DEV_UNSUPPORTED_LOCALE y no arranca.
            locale: 'es-MX'
        });
    }).then(function (sdk) {
        return sdk.findEligibleMethods({ currencyCode: '{{ $paypalInfo->currency_name }}' })
            .then(function (elegibles) { return { sdk: sdk, elegibles: elegibles }; });
    }).then(function (ctx) {
        var sdk = ctx.sdk;
        var puedeTarjeta = ctx.elegibles.isEligible('card');
        var puedePaypal  = ctx.elegibles.isEligible('paypal');

        // Cada bloque se muestra solo si PayPal dice que esa forma de pago
        // esta disponible para esta cuenta y moneda. Sustituye a la
        // comprobacion implicita que hacia paypal.Buttons() en el v5.
        var bloqueTarjeta = document.getElementById('paypal-card-block');
        var bloquePaypal  = document.getElementById('paypal-account-block');
        var separador     = document.getElementById('paypal-alt-sep');

        if (puedeTarjeta) {
            bloqueTarjeta.style.display = '';
            // createPayPal...Session devuelve la sesion YA construida, no una
            // promesa: encadenarle .then() rompia todo el arranque.
            var sesionTarjeta = sdk.createPayPalGuestOneTimePaymentSession({
                onApprove: alAprobar,
                // onComplete es OBLIGATORIO en la sesion de invitado; sin el,
                // el formulario no llega a abrirse. Se dispara al terminar el
                // flujo, despues de onApprove, que es quien cobra.
                onComplete: function () {},
                // Errores RECUPERABLES (tarjeta rechazada, datos mal
                // formados): el formulario sigue abierto para reintentar, asi
                // que basta con avisar sin cerrar nada.
                onWarn: function (aviso) {
                    toastr.warning((aviso && aviso.message) ||
                        'Revisa los datos de tu tarjeta e inténtalo de nuevo.');
                },
                onError: alFallar,
                onCancel: function () {}
            });
            var botonTarjeta = document.getElementById('paypal-btn-card');
            var notaTarjeta = document.getElementById('paypal-card-note');
            var formularioAbierto = false;

            function abrirFormularioTarjeta() {
                // Una sola vez: el boton sigue existiendo despues de abrir el
                // formulario, y un segundo start() crearia otra orden.
                if (formularioAbierto) return;
                formularioAbierto = true;

                if (notaTarjeta) notaTarjeta.textContent = 'Cargando el formulario seguro…';

                // presentationMode 'auto' es lo que hace que el formulario se
                // despliegue AQUI y no en otra ventana. El targetElement es el
                // propio boton: PayPal monta el formulario junto a el.
                Promise.resolve(sesionTarjeta.start(
                    { presentationMode: 'auto', targetElement: botonTarjeta },
                    crearOrden()
                )).then(function () {
                    if (notaTarjeta) {
                        notaTarjeta.innerHTML = 'Tus datos los procesa <strong>PayPal</strong>. ' +
                            'No se guardan en este sitio y no necesitas cuenta.';
                    }
                }).catch(function (e) {
                    // Si no se pudo abrir, el boton vuelve a estar disponible
                    // para que el cliente lo intente a mano.
                    formularioAbierto = false;
                    if (notaTarjeta) {
                        notaTarjeta.innerHTML = 'Captura tus datos aquí mismo. ' +
                            '<strong>No sales del sitio</strong> y no necesitas cuenta de PayPal.';
                    }
                    alFallar(e);
                });
            }

            botonTarjeta.addEventListener('click', abrirFormularioTarjeta);

            // Apertura automatica al llegar al paso de pago: el cliente ve el
            // formulario sin un clic de mas.
            //
            // No hace falta un clic real del usuario. Lo normal es que abrir
            // algo de PayPal exija activacion transitoria, pero aqui el
            // formulario de invitado se dibuja DENTRO de la pagina y no hay
            // ventana emergente que el navegador pueda bloquear. Comprobado:
            // el contenedor pasa de 45px a ~690px sin gesto del usuario.
            //
            // Ojo: tarda unos segundos en montarse, de ahi el texto de carga.
            abrirFormularioTarjeta();
        }

        if (puedePaypal) {
            bloquePaypal.style.display = '';
            var sesionPaypal = sdk.createPayPalOneTimePaymentSession({
                onApprove: alAprobar,
                onError: alFallar,
                onCancel: function () {}
            });
            document.getElementById('paypal-btn-paypal').addEventListener('click', function () {
                // 'auto' es el recomendado: intenta ventana emergente y cae a
                // modal si el navegador la bloquea. El inicio de sesion de
                // PayPal ocurre en su dominio, de ahi la ventana.
                Promise.resolve(
                    sesionPaypal.start({ presentationMode: 'auto' }, crearOrden())
                ).catch(alFallar);
            });
        }

        // El separador solo tiene sentido con las dos opciones a la vista.
        // Arranca oculto en el markup, asi que hay que mostrarlo a proposito.
        if (separador && puedeTarjeta && puedePaypal) separador.style.display = '';
    }).catch(function (e) {
        console.error('PayPal v6 no pudo iniciarse:', e);
    });
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
    // Id del método de envío gratis (type=min_cost) si el pedido ya califica
    // por monto — null si ninguno aplica. Ver uso más abajo.
    var autoSelectShippingId = {{ $autoSelectShippingId ?? 'null' }};
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
        $('#total_amount').text(currIcon + fmt(currentTotal));
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

        // El paso 2 solo cuenta como completado si el 1 lo esta. Es la misma
        // regla que exige el servidor en CheckOutController::checkOutFormSumit,
        // asi que la pantalla no puede prometer un avance que el backend
        // despues rechaza.
        if (!$('#shipping_address_id').val()) {
            validateSubmitBtn();
            return;
        }

        completeSection(2);
        unlockSection(3);
        autoSelectPayment();
        validateSubmitBtn();
    });

    // ── Apertura automática del método de pago ───────────────────────
    // En cuanto se desbloquea el paso 3 se elige solo el método preferido
    // (tarjeta, procesada por PayPal) y se abre su panel, así el cliente ya
    // ve dónde capturar sus datos sin tener que buscar y hacer un clic extra.
    //
    // Solo actúa si el cliente TODAVÍA no eligió nada: si ya escogió, por
    // ejemplo, SPEI y luego cambia el método de envío, no se le pisa su
    // elección.
    function autoSelectPayment() {
        if ($('#payment_method').val()) return;
        // Ultima red de seguridad: sin direccion o sin envio no hay nada que
        // cobrar todavia, y abrir el panel dispararia la creacion de la orden
        // de PayPal contra un checkout incompleto.
        if (!$('#shipping_address_id').val() || !$('#shipping_method_id').val()) return;

        // Orden de preferencia: tarjeta (PayPal) > tarjeta (Stripe) > SPEI.
        var preferidos = ['paypal', 'stripe', 'spei'];
        for (var i = 0; i < preferidos.length; i++) {
            var $radio = $('input[name="payment_radio"][value="' + preferidos[i] + '"]');
            if ($radio.length) {
                // trigger('change') dispara el mismo manejador que un clic
                // real, así que abre el panel, monta los botones de PayPal,
                // actualiza el resumen y desbloquea la sección 4.
                $radio.prop('checked', true).trigger('change');
                return;
            }
        }
    }

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
            if (cardElement && !stripeCardMounted) {
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

        // Une visualmente la opcion elegida con su panel abierto.
        $('.payment-option').removeClass('has-panel-open');
        if ($('.payment-detail-panel.active').length) {
            $opt.addClass('has-panel-open');
        }

        updateTotalDisplay();

        // Sidebar payment label
        var names = { stripe: 'Tarjeta (Stripe)', paypal: 'Tarjeta de débito/crédito', spei: 'SPEI / BBVA' };
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

    // ── Auto-selección de envío gratis ───────────────────────────────
    // Si el pedido ya califica para el envío gratis (ej. "Envio Gratis"
    // desde $2,299), se marca automáticamente para que el cliente no tenga
    // que buscarlo entre las demás opciones. Dispara el mismo 'change' que
    // un click real, así que el total, el badge de sección y el botón de
    // continuar se actualizan igual — el resto de métodos (incluido Envío
    // Internacional, a cargo del comprador) siguen visibles y elegibles
    // por si el cliente prefiere cambiarlo.
    //
    // Solo si YA hay una direccion elegida. Sin esa condicion se disparaba al
    // cargar la pagina aunque el cliente no tuviera ninguna direccion guardada,
    // y como el manejador de 'change' encadena completeSection(2) ->
    // unlockSection(3) -> autoSelectPayment(), el checkout mostraba los pasos
    // de envio y pago como COMPLETADOS con el paso 1 todavia en PENDIENTE.
    if (autoSelectShippingId !== null && $('#shipping_address_id').val()) {
        $('input.shipping_method[value="' + autoSelectShippingId + '"]')
            .prop('checked', true)
            .trigger('change');
    }

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
                if (!stripe || !cardElement) {
                    toastr.error('Pago con tarjeta requiere HTTPS. Usa otro método de pago en local.');
                    $btn.html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M5 12h14M12 5l7 7-7 7"/></svg> Realizar pedido')
                        .css({'opacity':'','pointer-events':''});
                    return;
                }
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
        if (typeof window.validateAddressForm === 'function' && !window.validateAddressForm()) {
            return;
        }
        $btn.addClass('saving').text('Guardando...');
        $(this).closest('form').submit();
    });
});
</script>
@endpush

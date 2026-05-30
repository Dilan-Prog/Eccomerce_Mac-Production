@extends('frontend.layouts.master')
@section('title', $settings->site_name . ' || Checkout')

@push('styles')
@vite(['resources/css/checkout.css'])
<style>
  /* Spinner para el botón de confirmar */
  @keyframes spin { to { transform: rotate(360deg); } }
  .spin-icon { animation: spin 0.8s linear infinite; }
  .checkout-confirm-btn.btn-ready { opacity: 1; }
</style>
@endpush

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
            <p>Elige dirección, método de envío y confirma tu pedido</p>

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

            {{-- ════════════════════════════════════════════════
                 COLUMNA IZQUIERDA — Secciones del formulario
            ════════════════════════════════════════════════ --}}
            <div>

                {{-- ── SECCIÓN 1: DIRECCIÓN ───────────────── --}}
                <div class="checkout-section">
                    <div class="checkout-section-header">
                        <div class="checkout-section-num">1</div>
                        <h2 class="checkout-section-title">Dirección de envío</h2>
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
                                    <input class="shipping_address"
                                           type="radio"
                                           name="address_radio"
                                           value="{{ $address->id }}"
                                           data-id="{{ $address->id }}"
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

                        {{-- Botón agregar nueva dirección --}}
                        <button type="button" class="btn-add-address" id="btn-add-address">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Agregar nueva dirección
                        </button>

                        {{-- Panel nueva dirección (visible con JS) --}}
                        <div class="new-address-panel" id="new-address-panel">
                            <div class="panel-title">Nueva dirección de envío</div>
                            <form id="checkoutFormAddress"
                                  action="{{ route('user.checkout.address.create') }}"
                                  method="POST">
                                @csrf
                                <div class="form-row-2" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Nombre completo <span>*</span></label>
                                        <input class="form-input @error('name') is-invalid @enderror"
                                               type="text" name="name"
                                               value="{{ old('name') }}"
                                               placeholder="Ej. Roberto Martínez">
                                        @error('name')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Teléfono <span>*</span></label>
                                        <input class="form-input @error('phone') is-invalid @enderror"
                                               type="text" name="phone"
                                               value="{{ old('phone') }}"
                                               placeholder="10 dígitos">
                                        @error('phone')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label class="form-label">Correo electrónico</label>
                                    <input class="form-input @error('email') is-invalid @enderror"
                                           type="email" name="email"
                                           value="{{ old('email') }}"
                                           placeholder="correo@ejemplo.com">
                                    @error('email')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-row-3" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Código Postal <span>*</span></label>
                                        <input class="form-input @error('zip') is-invalid @enderror"
                                               type="text" name="zip"
                                               value="{{ old('zip') }}"
                                               placeholder="00000">
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
                                        <input class="form-input @error('city') is-invalid @enderror"
                                               type="text" name="city"
                                               value="{{ old('city') }}"
                                               placeholder="Monterrey">
                                        @error('city')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row-2" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Colonia <span>*</span></label>
                                        <input class="form-input @error('col') is-invalid @enderror"
                                               type="text" name="col"
                                               value="{{ old('col') }}"
                                               placeholder="Colonia / Fraccionamiento">
                                        @error('col')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Calle <span>*</span></label>
                                        <input class="form-input @error('street') is-invalid @enderror"
                                               type="text" name="street"
                                               value="{{ old('street') }}"
                                               placeholder="Nombre de la calle">
                                        @error('street')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row-2" style="margin-bottom:12px;">
                                    <div class="form-group">
                                        <label class="form-label">Número interior</label>
                                        <input class="form-input" type="text" name="street_number"
                                               value="{{ old('street_number') }}"
                                               placeholder="Opcional">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Entre calles</label>
                                        <input class="form-input" type="text" name="street_1"
                                               value="{{ old('street_1') }}"
                                               placeholder="Calle 1">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:14px;">
                                    <label class="form-label">Indicaciones adicionales</label>
                                    <textarea class="form-textarea-mdn" name="address"
                                              placeholder="Descripción del exterior, referencias, etc.">{{ old('address') }}</textarea>
                                </div>
                                <div class="panel-actions">
                                    <button type="submit" class="btn-save-address" id="saveAddressButton">
                                        Guardar dirección
                                    </button>
                                    <button type="button" class="btn-cancel-address" id="btn-cancel-address">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                {{-- ── SECCIÓN 2: MÉTODO DE ENVÍO ─────────── --}}
                <div class="checkout-section">
                    <div class="checkout-section-header">
                        <div class="checkout-section-num">2</div>
                        <h2 class="checkout-section-title">Método de envío</h2>
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
                                    <input class="shipping_method"
                                           type="radio"
                                           name="shipping_method_radio"
                                           value="{{ $method->id }}"
                                           data-id="{{ $method->cost }}">
                                    <div class="shipping-radio"></div>
                                    <div class="shipping-icon">
                                        <i class="fas fa-shipping-fast"></i>
                                    </div>
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
                                <p class="no-shipping-msg">
                                    No hay métodos de envío disponibles para tu pedido actual.
                                </p>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- ── SECCIÓN 3: TÉRMINOS ─────────────────── --}}
                <div class="checkout-section">
                    <div class="checkout-section-header">
                        <div class="checkout-section-num">3</div>
                        <h2 class="checkout-section-title">Términos y condiciones</h2>
                    </div>
                    <div class="checkout-section-body">
                        <div class="terms-section">
                            <label class="terms-check">
                                <input class="agree_term" type="checkbox" id="flexCheckChecked3" value="1">
                                He leído y acepto los
                                <a href="{{ route('Terminos-Condiciones') }}" target="_blank">términos y condiciones</a>
                                y el
                                <a href="{{ route('Aviso-Privacidad') }}" target="_blank">aviso de privacidad</a>
                                de Mac Del Norte.
                            </label>
                            <label class="terms-check">
                                <input type="checkbox" id="check_newsletter" value="1">
                                Deseo recibir noticias y promociones por correo electrónico (opcional).
                            </label>
                        </div>
                    </div>
                </div>

            </div>{{-- /izquierda --}}

            {{-- ════════════════════════════════════════════════
                 COLUMNA DERECHA — Sidebar con resumen + confirmar
            ════════════════════════════════════════════════ --}}
            <div class="checkout-sidebar">

                {{-- Formulario oculto (el jQuery AJAX lo usa) --}}
                <form action="" id="checkOutForm">
                    <input type="hidden" name="shipping_method_id" value="" id="shipping_method_id">
                    <input type="hidden" name="shipping_address_id"
                           value="{{ $addresses->first()?->id ?? '' }}"
                           id="shipping_address_id">
                </form>

                <div class="checkout-summary-card">
                    <div class="checkout-summary-header">
                        📦 Resumen del pedido
                    </div>

                    {{-- Productos del carrito --}}
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

                    {{-- Totales --}}
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
                        <div class="checkout-total-row main">
                            <span>Total</span>
                            <span class="amount" id="total_amount" data-id="{{ getMainCartTotal() }}">
                                {{ $settings->currency_icon }}{{ formatCurrency(getMainCartTotal()) }}
                            </span>
                        </div>
                        <div class="total-iva-note">IVA incluido en el precio</div>

                        {{-- Botón confirmar (habilitado por JS cuando todo está listo) --}}
                        <a href="javascript:void(0);" id="submitCheckoutForm"
                           class="checkout-confirm-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                            Realizar pedido
                        </a>

                        <div class="checkout-security">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13">
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                            </svg>
                            Pago 100% seguro · SSL cifrado
                        </div>

                        {{-- Métodos de pago aceptados --}}
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

                {{-- Link para regresar al carrito --}}
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
@vite(['resources/js/checkout.js'])
<script>
$(document).ready(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('input[type="radio"]').prop('checked', false);
    $('#shipping_method_id').val('');

    // Inicializa dirección con la primera (si existe)
    @if($addresses->isNotEmpty())
    $('#shipping_address_id').val('{{ $addresses->first()->id }}');
    @endif

    // ── Formato de número ──
    function formatNumber(num) {
        return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // ── Selección de método de envío ──
    $(document).on('click', '.shipping_method', function () {
        // Visual (el checkout.js lo maneja con .shipping-option, aquí sincronizamos el hidden)
        let shippingFee       = parseFloat($(this).data('id'));
        let currentCartTotal  = parseFloat($('#total_amount').data('id'));
        let totalWithShipping = currentCartTotal + shippingFee;
        $('#shipping_method_id').val($(this).val());
        $('#shipping_fee').text("{{ $settings->currency_icon }}" + formatNumber(shippingFee));
        $('#total_amount').text("{{ $settings->currency_icon }}" + formatNumber(totalWithShipping));
        // Sincroniza clases visuales
        $('.shipping-option').removeClass('selected');
        $(this).closest('.shipping-option').addClass('selected');
    });

    // ── Selección de dirección (radio) ──
    $(document).on('click', '.shipping_address', function () {
        $('#shipping_address_id').val($(this).data('id'));
        // Sincroniza clases visuales
        $('.address-card').removeClass('selected');
        $(this).closest('.address-card').addClass('selected');
    });

    // ── Submit del checkout ──
    $('#submitCheckoutForm').on('click', function (e) {
        e.preventDefault();
        if ($('#shipping_method_id').val() === '') {
            toastr.error('Selecciona un método de envío');
            return;
        }
        if ($('#shipping_address_id').val() === '') {
            toastr.error('Selecciona una dirección de entrega');
            return;
        }
        if (!$('.agree_term').prop('checked')) {
            toastr.error('Debes aceptar los términos y condiciones');
            return;
        }
        $.ajax({
            url: "{{ route('user.checkout.form-submit') }}",
            method: 'POST',
            data: $('#checkOutForm').serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            beforeSend: function () {
                $('#submitCheckoutForm')
                    .html('<i class="fas fa-spinner fa-spin"></i> Procesando...')
                    .css('opacity', '0.7').css('pointer-events', 'none');
            },
            success: function (data) {
                if (data.status === 'success') {
                    window.location.href = data.redirect_url;
                }
            },
            error: function (xhr) {
                $('#submitCheckoutForm')
                    .html('Realizar pedido')
                    .css('opacity', '').css('pointer-events', '');
                if (xhr.responseJSON?.errors) {
                    let msgs = Object.values(xhr.responseJSON.errors).flat();
                    msgs.forEach(m => toastr.error(m));
                } else {
                    toastr.error('Ocurrió un error. Intenta de nuevo.');
                }
            }
        });
    });

    // ── Guardar nueva dirección ──
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

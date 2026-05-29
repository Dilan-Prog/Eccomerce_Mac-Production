@extends('frontend.layouts.master')

@section('title')
{{ $settings->site_name }} || Mi Carrito
@endsection

@push('styles')
<style>
  /* ===== CART PAGE — from pages.css design system ===== */
  .cart-page { padding: 32px 0 64px; background: var(--gris-fondo); }
  .cart-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; }

  /* ITEMS CARD */
  .cart-items-card {
    background: var(--blanco); border-radius: 12px;
    border: 1px solid var(--gris-borde); overflow: hidden;
  }
  .cart-items-header {
    padding: 20px 24px; border-bottom: 1px solid var(--gris-borde);
    display: flex; align-items: center; justify-content: space-between;
  }
  .cart-items-header h2 { font-size: 18px; margin: 0; color: var(--azul-principal); }
  .cart-items-header .clear-btn {
    background: none; border: none; color: var(--rojo-error);
    font-size: 13px; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; gap: 6px;
  }
  .cart-items-header .clear-btn:hover { text-decoration: underline; }

  /* ITEM ROW */
  .cart-item {
    display: grid; grid-template-columns: 96px 1fr auto;
    gap: 16px; padding: 20px 24px;
    border-bottom: 1px solid var(--gris-borde); align-items: center;
  }
  .cart-item:last-child { border-bottom: none; }
  .cart-item-image {
    width: 96px; height: 96px;
    background: linear-gradient(135deg, var(--azul-claro) 0%, var(--blanco) 100%);
    border-radius: 8px; border: 1px solid var(--gris-borde);
    display: flex; align-items: center; justify-content: center; overflow: hidden;
  }
  .cart-item-image img { width: 100%; height: 100%; object-fit: contain; padding: 6px; }
  .cart-item-details { min-width: 0; }
  .cart-item-brand {
    font-size: 11px; font-weight: 700; color: var(--gris-claro-texto);
    text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;
  }
  .cart-item-name {
    font-size: 14px; font-weight: 700; color: var(--azul-principal);
    margin-bottom: 5px; line-height: 1.3; text-decoration: none;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden; word-break: break-word;
  }
  a.cart-item-name:hover { color: var(--azul-medio); }
  .cart-item-pn {
    font-family: 'Courier New', monospace; font-size: 12px;
    color: var(--gris-texto); background: var(--gris-fondo);
    padding: 3px 8px; border-radius: 4px;
    display: inline-block; margin-bottom: 6px;
  }
  .cart-item-stock {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 12px; color: #2F855A; font-weight: 600;
  }
  .cart-item-actions {
    display: flex; flex-direction: column; align-items: flex-end; gap: 10px;
  }
  .cart-item-price { font-size: 17px; font-weight: 800; color: var(--azul-principal); text-align: right; }
  .cart-item-price small { font-size: 10px; color: var(--gris-claro-texto); font-weight: 600; display: block; }
  .cart-item-subtotal { font-size: 13px; color: var(--gris-claro-texto); text-align: right; }

  /* QTY CONTROL */
  .qty-control {
    display: flex; align-items: center;
    border: 1.5px solid var(--gris-borde); border-radius: 7px;
    overflow: hidden; background: var(--blanco);
  }
  .qty-btn {
    width: 32px; height: 36px; background: var(--gris-fondo); border: none;
    cursor: pointer; color: var(--azul-principal); font-size: 16px; font-weight: 700;
    transition: background 0.2s;
  }
  .qty-btn:hover { background: var(--azul-claro); }
  .qty-input {
    width: 44px; height: 36px; border: none; text-align: center;
    font-size: 14px; font-weight: 700; color: var(--azul-principal);
    border-left: 1px solid var(--gris-borde); border-right: 1px solid var(--gris-borde);
    background: var(--blanco);
  }
  .qty-input:focus { outline: none; }

  /* REMOVE BUTTON */
  .cart-remove-btn {
    background: none; border: none; color: var(--gris-claro-texto);
    cursor: pointer; display: flex; align-items: center; gap: 4px;
    font-size: 12px; font-weight: 600; transition: color 0.2s; text-decoration: none;
  }
  .cart-remove-btn:hover { color: var(--rojo-error); }

  /* EMPTY STATE */
  .cart-empty { padding: 64px 24px; text-align: center; }
  .cart-empty-icon {
    width: 80px; height: 80px; background: var(--gris-fondo); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px; color: var(--gris-claro-texto);
  }
  .cart-empty h3 { font-size: 20px; color: var(--azul-principal); margin-bottom: 8px; }
  .cart-empty p { font-size: 14px; color: var(--gris-claro-texto); margin-bottom: 24px; }

  /* COUPON SECTION */
  .coupon-section { display: flex; gap: 8px; margin: 20px 24px 0; }
  .coupon-section input {
    flex: 1; padding: 10px 12px; border: 1.5px solid var(--gris-borde);
    border-radius: 7px; font-size: 13px; font-family: inherit;
  }
  .coupon-section input:focus { outline: none; border-color: var(--azul-principal); }
  .coupon-section button {
    padding: 10px 16px; background: var(--azul-claro); border: 1.5px solid var(--azul-principal);
    color: var(--azul-principal); border-radius: 7px; font-size: 12px; font-weight: 700;
    cursor: pointer; transition: all 0.2s; white-space: nowrap;
  }
  .coupon-section button:hover { background: var(--azul-principal); color: var(--blanco); }
  .coupon-applied-badge {
    display: flex; align-items: center; gap: 8px; margin: 10px 24px 4px;
    background: #F0FDF4; border: 1px solid #2F855A;
    border-radius: 7px; padding: 8px 12px; font-size: 13px;
  }
  .coupon-applied-badge strong { color: #2F855A; font-weight: 800; }
  .coupon-remove-btn {
    margin-left: auto; background: none; border: none; color: var(--rojo-error);
    font-size: 12px; font-weight: 600; cursor: pointer;
  }
  .coupon-remove-btn:hover { text-decoration: underline; }

  /* SUMMARY */
  .cart-summary { position: sticky; top: 16px; align-self: start; }
  .cart-summary-card {
    background: var(--blanco); border-radius: 12px;
    border: 1px solid var(--gris-borde); padding: 24px; margin-bottom: 16px;
  }
  .cart-summary-card h3 {
    font-size: 16px; color: var(--azul-principal); margin-bottom: 18px;
    padding-bottom: 14px; border-bottom: 1px solid var(--gris-borde);
  }
  .summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0; font-size: 14px; color: var(--gris-texto);
  }
  .summary-row.discount { color: #2F855A; font-weight: 700; }
  .summary-row.total {
    padding: 16px 0 4px; border-top: 1px solid var(--gris-borde); margin-top: 8px;
    font-size: 16px; font-weight: 800; color: var(--azul-principal);
  }
  .summary-row.total .amount { font-size: 22px; color: var(--azul-principal); }
  .summary-iva-note { font-size: 11px; color: var(--gris-claro-texto); text-align: right; margin-bottom: 16px; }

  /* SHIPPING BANNER */
  .shipping-banner {
    background: #F0FDF4; border: 1px solid #2F855A;
    border-radius: 7px; padding: 12px 14px;
    display: flex; align-items: center; gap: 10px;
    font-size: 13px; color: var(--gris-texto); margin: 14px 0;
  }
  .shipping-banner i { color: #2F855A; font-size: 18px; flex-shrink: 0; }
  .shipping-banner strong { color: #2F855A; font-weight: 800; }
  .shipping-progress { margin: 14px 0; font-size: 13px; color: var(--gris-texto); }
  .shipping-progress-bar {
    width: 100%; height: 6px; background: var(--gris-borde);
    border-radius: 999px; margin-top: 8px; overflow: hidden;
  }
  .shipping-progress-fill {
    height: 100%; background: var(--accent-cta); border-radius: 999px; transition: width 0.3s;
  }

  /* CHECKOUT BUTTONS */
  .checkout-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 13px; background: var(--accent-cta); color: var(--blanco);
    border: none; border-radius: 8px; font-size: 14px; font-weight: 800;
    cursor: pointer; text-decoration: none; transition: background 0.2s;
    margin-bottom: 10px;
  }
  .checkout-btn:hover { background: var(--accent-cta-hover); color: var(--blanco); }
  .keep-shopping-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 11px; background: var(--gris-fondo);
    border: 1px solid var(--gris-borde); border-radius: 8px;
    font-size: 13px; font-weight: 700; color: var(--azul-principal);
    text-decoration: none; transition: all 0.2s;
  }
  .keep-shopping-btn:hover { background: var(--azul-claro); border-color: var(--azul-principal); color: var(--azul-principal); }

  /* SECURITY BADGES */
  .security-badges {
    display: flex; justify-content: center; gap: 18px;
    padding: 16px 0; flex-wrap: wrap;
  }
  .security-badge {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    font-size: 11px; color: var(--gris-claro-texto); font-weight: 600; text-align: center;
  }
  .security-badge i { font-size: 20px; color: #2F855A; }

  /* B2B CTA */
  .b2b-cta-card {
    background: linear-gradient(135deg, var(--azul-oscuro) 0%, var(--azul-principal) 100%);
    color: var(--blanco); border-radius: 12px; padding: 20px; text-align: center;
  }
  .b2b-cta-card h4 {
    color: var(--amarillo-destacado); font-size: 13px;
    text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;
  }
  .b2b-cta-card p { font-size: 13px; opacity: 0.92; margin-bottom: 14px; line-height: 1.5; color: rgba(255,255,255,0.9); }
  .b2b-cta-btn {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--amarillo-destacado); color: var(--azul-oscuro);
    font-size: 12px; font-weight: 800; padding: 9px 18px;
    border-radius: 7px; text-decoration: none; transition: opacity 0.2s;
  }
  .b2b-cta-btn:hover { opacity: 0.9; color: var(--azul-oscuro); }

  /* FLASH MESSAGES */
  .cart-flash { padding: 12px 20px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-weight: 600; }
  .cart-flash--success { background: #F0FDF4; border: 1px solid #2F855A; color: #2F855A; }
  .cart-flash--error { background: #FEF2F2; border: 1px solid var(--rojo-error); color: var(--rojo-error); }

  /* RESPONSIVE */
  @media (max-width: 1100px) {
    .cart-grid { grid-template-columns: 1fr; }
    .cart-summary { position: relative; top: 0; }
  }
  @media (max-width: 720px) {
    .cart-item { grid-template-columns: 72px 1fr; gap: 12px; }
    .cart-item-actions {
      grid-column: 1 / -1; flex-direction: row;
      justify-content: space-between; padding-top: 12px;
      border-top: 1px solid var(--gris-borde); align-items: center;
    }
    .cart-items-header h2 { font-size: 16px; }
  }
  @media (max-width: 480px) {
    .cart-item { padding: 14px 16px; }
    .cart-items-header { padding: 14px 16px; }
    .coupon-section { margin: 16px 16px 0; }
    .coupon-applied-badge { margin: 8px 16px 0; }
  }
</style>
@endpush

@section('content')

{{-- BREADCRUMB --}}
<div style="background:var(--blanco);border-bottom:1px solid var(--gris-borde);padding:14px 0;">
    <div class="container">
        <nav style="display:flex;align-items:center;gap:8px;font-size:13px;flex-wrap:wrap;">
            <a href="{{ route('index') }}" style="color:var(--gris-claro-texto);text-decoration:none;font-weight:600;">Inicio</a>
            <span style="color:var(--gris-borde);">/</span>
            <span style="color:var(--azul-principal);font-weight:700;">Mi Carrito</span>
        </nav>
    </div>
</div>

{{-- PAGE HEADER --}}
<section style="background:linear-gradient(135deg,var(--azul-oscuro) 0%,var(--azul-principal) 60%,var(--azul-medio) 100%);color:var(--blanco);padding:40px 0;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.04) 1px,transparent 1px);background-size:50px 50px;"></div>
    <div class="container" style="position:relative;z-index:2;text-align:center;">
        <div style="display:inline-block;background:rgba(246,173,28,0.18);border:1px solid rgba(246,173,28,0.45);color:#F6AD1C;font-size:12px;font-weight:800;padding:7px 14px;border-radius:4px;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px;">
            Tu pedido
        </div>
        <h1 style="font-size:28px;font-weight:800;color:var(--blanco);margin-bottom:10px;letter-spacing:-0.4px;">
            Revisa tu carrito antes de comprar
        </h1>
        <p style="font-size:15px;opacity:0.9;max-width:560px;margin:0 auto;">
            Verifica cantidades, aplica cupones y procede al pago de forma segura.
        </p>
    </div>
</section>

<section class="cart-page">
    <div class="container">

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
            <div class="cart-flash cart-flash--success">
                <i class="fas fa-check-circle" style="margin-right:6px;"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="cart-flash cart-flash--error">
                <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>
                {{ session('error') }}
            </div>
        @endif

        @php
            $subtotal        = getCartTotal();
            $discount        = getCartDiscount();
            $cartTotal       = getMainCartTotal();
            $itemCount       = count($cartItems);
            // TODO: pass $shippingRules from CartController@cartDetails
            $freeShippingMin = isset($shippingRules) ? $shippingRules->min_cost : null;
            $coupon          = session('coupon');
        @endphp

        <div class="cart-grid">

            {{-- ===== CART ITEMS ===== --}}
            <div class="cart-items-card">
                <div class="cart-items-header">
                    <h2>
                        {{ $itemCount }} {{ $itemCount === 1 ? 'producto' : 'productos' }} en tu carrito
                    </h2>
                    @if($itemCount > 0)
                        <button type="button" class="clear-btn" id="clear-cart-btn">
                            <i class="fas fa-trash-alt"></i>
                            Limpiar carrito
                        </button>
                    @endif
                </div>

                @forelse ($cartItems as $item)
                <div class="cart-item">

                    {{-- IMAGE --}}
                    <a href="{{ route('product-detail', $item->options->slug) }}" class="cart-item-image">
                        <img src="{{ asset($item->options->image) }}" alt="{{ $item->name }}" loading="lazy" />
                    </a>

                    {{-- DETAILS --}}
                    <div class="cart-item-details">
                        @if($item->options->brand_name)
                            <div class="cart-item-brand">{{ $item->options->brand_name }}</div>
                        @endif

                        <a class="cart-item-name" href="{{ route('product-detail', $item->options->slug) }}">
                            {!! $item->name !!}
                        </a>

                        @if($item->options->sku)
                            <div class="cart-item-pn">SKU: {{ $item->options->sku }}</div>
                        @endif

                        @if($item->options->productModel)
                            <div class="cart-item-pn" style="background:var(--azul-claro);color:var(--azul-principal);">
                                Modelo: {{ $item->options->productModel }}
                            </div>
                        @endif

                        <div class="cart-item-stock">
                            <i class="fas fa-check-circle" style="font-size:11px;"></i> En existencia
                        </div>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="cart-item-actions">
                        <div class="cart-item-price">
                            {{ $settings->currency_icon }}{{ number_format($item->price, 2, '.', ',') }}
                            <small>c/u · IVA incl.</small>
                        </div>

                        <div class="qty-control product_qty_wrapper">
                            <button type="button" class="qty-btn product-decrement">−</button>
                            <input class="qty-input product-qty"
                                   data-rowid="{{ $item->rowId }}"
                                   type="text" min="1" max="999"
                                   value="{{ $item->qty }}" readonly />
                            <button type="button" class="qty-btn product-increment">+</button>
                        </div>

                        <div class="cart-item-subtotal" id="{{ $item->rowId }}">
                            Total: {{ $settings->currency_icon }}{{ number_format($item->price * $item->qty, 2, '.', ',') }}
                        </div>

                        <a href="{{ route('cart.remove-product', $item->rowId) }}"
                           class="cart-remove-btn"
                           onclick="return confirm('¿Eliminar este producto del carrito?');">
                            <i class="fas fa-times"></i> Eliminar
                        </a>
                    </div>

                </div>
                @empty
                <div class="cart-empty">
                    <div class="cart-empty-icon">
                        <i class="fas fa-shopping-cart" style="font-size:32px;"></i>
                    </div>
                    <h3>Tu carrito está vacío</h3>
                    <p>Explora nuestro catálogo y agrega los productos que necesitas.</p>
                    <a href="{{ route('products.index') }}"
                       style="display:inline-flex;align-items:center;gap:8px;padding:11px 22px;background:var(--azul-principal);color:var(--blanco);border-radius:8px;font-weight:700;text-decoration:none;">
                        <i class="fas fa-search"></i> Explorar productos
                    </a>
                </div>
                @endforelse

            </div>{{-- /.cart-items-card --}}


            {{-- ===== CART SUMMARY ===== --}}
            <div class="cart-summary">
                <div class="cart-summary-card">
                    <h3>Resumen del pedido</h3>

                    <div class="summary-row">
                        <span>Subtotal ({{ $itemCount }} art.)</span>
                        <span id="sub_total">
                            {{ $settings->currency_icon }}{{ formatCurrency($subtotal) }}
                        </span>
                    </div>

                    @if($discount > 0)
                    <div class="summary-row discount">
                        <span><i class="fas fa-tag" style="margin-right:4px;"></i> Descuento</span>
                        <span id="discount">
                            − {{ $settings->currency_icon }}{{ formatCurrency($discount) }}
                        </span>
                    </div>
                    @else
                    <div class="summary-row">
                        <span>Descuento</span>
                        <span id="discount">{{ $settings->currency_icon }}0.00</span>
                    </div>
                    @endif

                    {{-- SHIPPING --}}
                    @if($freeShippingMin)
                        @if($cartTotal >= $freeShippingMin)
                            <div class="shipping-banner">
                                <i class="fas fa-shipping-fast"></i>
                                <span><strong>¡Envío gratis!</strong> Tu pedido califica.</span>
                            </div>
                            <div class="summary-row">
                                <span>Envío</span>
                                <span style="color:#2F855A;font-weight:700;">Gratis</span>
                            </div>
                        @else
                            @php $faltante = $freeShippingMin - $cartTotal; $pct = min(100, round(($cartTotal / $freeShippingMin) * 100)); @endphp
                            <div class="shipping-progress">
                                <span>Te faltan <strong>{{ $settings->currency_icon }}{{ number_format($faltante, 2, '.', ',') }}</strong> para envío gratis</span>
                                <div class="shipping-progress-bar">
                                    <div class="shipping-progress-fill" style="width:{{ $pct }}%;"></div>
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- TODO: pasar $shippingRules desde CartController@cartDetails --}}
                        <div class="summary-row">
                            <span>Envío</span>
                            <span style="color:var(--gris-claro-texto);font-size:12px;">Calculado al pagar</span>
                        </div>
                    @endif

                    {{-- COUPON --}}
                    @if($itemCount > 0)
                        @if($coupon)
                            <div class="coupon-applied-badge" style="margin:10px 0 4px;">
                                <i class="fas fa-tag" style="color:#2F855A;"></i>
                                Cupón activo: <strong>{{ $coupon['coupon_code'] }}</strong>
                                <button type="button" class="coupon-remove-btn" id="remove-coupon-btn">Quitar</button>
                            </div>
                        @endif
                        <div class="coupon-section" style="margin:12px 0 4px;">
                            <input type="text" id="coupon_code_input" name="coupon_code"
                                   placeholder="Código de descuento"
                                   value="{{ $coupon ? $coupon['coupon_code'] : '' }}" />
                            <button type="button" id="apply-coupon-btn">Aplicar</button>
                        </div>
                    @endif

                    <div class="summary-row total">
                        <span>Total</span>
                        <span class="amount" id="cart_total">
                            {{ $settings->currency_icon }}{{ formatCurrency($cartTotal) }}
                        </span>
                    </div>
                    <div class="summary-iva-note">IVA incluido en el precio</div>

                    <a href="{{ route('user.checkout') }}" class="checkout-btn">
                        <i class="fas fa-lock" style="font-size:13px;"></i>
                        Proceder al pago
                    </a>
                    <a href="{{ route('products.index') }}" class="keep-shopping-btn">
                        <i class="fas fa-arrow-left" style="font-size:12px;"></i>
                        Seguir comprando
                    </a>

                </div>

                <div class="b2b-cta-card">
                    <h4>¿Compra empresarial?</h4>
                    <p>Obtén precios especiales para volumen, crédito empresarial y atención personalizada.</p>
                    <a href="{{ route('contact') }}" class="b2b-cta-btn">
                        <i class="fas fa-briefcase" style="font-size:12px;"></i>
                        Cotización B2B
                    </a>
                </div>
            </div>{{-- /.cart-summary --}}

        </div>{{-- /.cart-grid --}}
    </div>{{-- /.container --}}
</section>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ── INCREMENT ──
    $(document).on('click', '.product-increment', function () {
        let input    = $(this).siblings('.product-qty');
        let quantity = parseInt(input.val()) + 1;
        let rowId    = input.data('rowid');
        input.val(quantity);
        updateQty(rowId, quantity);
    });

    // ── DECREMENT ──
    $(document).on('click', '.product-decrement', function () {
        let input    = $(this).siblings('.product-qty');
        let quantity = parseInt(input.val()) - 1;
        let rowId    = input.data('rowid');
        if (quantity < 1) quantity = 1;
        input.val(quantity);
        updateQty(rowId, quantity);
    });

    function updateQty(rowId, quantity) {
        $.ajax({
            url: "{{ route('cart.update-quantity') }}",
            method: 'POST',
            data: { rowId: rowId, quantity: quantity },
            success: function (data) {
                if (data.status === 'success') {
                    let totalAmount = "{{ $settings->currency_icon }}" + formatNumber(data.product_total);
                    $('#' + rowId).text('Total: ' + totalAmount);
                    refreshSubTotal();
                    calculateCouponDiscount();
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            }
        });
    }

    // ── CLEAR CART ──
    $('#clear-cart-btn').on('click', function () {
        Swal.fire({
            title: '¿Limpiar el carrito?',
            text: 'Se eliminarán todos los productos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#003E7E',
            cancelButtonColor: '#DC2626',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('clear.cart') }}",
                    success: function (data) {
                        if (data.status === 'success') window.location.reload();
                    }
                });
            }
        });
    });

    // ── REFRESH SUBTOTAL ──
    function refreshSubTotal() {
        $.ajax({
            method: 'GET',
            url: "{{ route('cart.sidebar-product-total') }}",
            success: function (data) {
                $('#sub_total').text("{{ $settings->currency_icon }}" + formatNumber(parseFloat(data)));
            }
        });
    }

    // ── APPLY COUPON ──
    $('#apply-coupon-btn').on('click', function () {
        let code = $('#coupon_code_input').val().trim();
        if (!code) { toastr.warning('Ingresa un código de cupón'); return; }
        $.ajax({
            method: 'GET',
            url: "{{ route('apply-coupon') }}",
            data: { coupon_code: code },
            success: function (data) {
                if (data.status === 'error') {
                    toastr.error(data.message);
                } else {
                    calculateCouponDiscount();
                    toastr.success(data.message);
                    setTimeout(function () { location.reload(); }, 1000);
                }
            }
        });
    });

    // ── REMOVE COUPON ──
    $('#remove-coupon-btn').on('click', function () {
        $.ajax({
            method: 'GET',
            url: "{{ route('apply-coupon') }}",
            data: { coupon_code: '' },
            complete: function () { location.reload(); }
        });
    });

    // ── COUPON CALCULATION ──
    function calculateCouponDiscount() {
        $.ajax({
            method: 'GET',
            url: "{{ route('coupon-calculation') }}",
            success: function (data) {
                if (data.status === 'success') {
                    $('#discount').text(
                        (data.discount > 0 ? '− ' : '') +
                        "{{ $settings->currency_icon }}" + formatNumber(parseFloat(data.discount))
                    );
                    $('#cart_total').text("{{ $settings->currency_icon }}" + formatNumber(parseFloat(data.cart_total)));
                }
            }
        });
    }

    function formatNumber(num) {
        return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

});
</script>
@endpush

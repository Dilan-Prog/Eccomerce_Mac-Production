@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} || Pagar
@endsection

@section('content')
    <!--============================
            PAYMENT PAGE START
        ==============================-->
    <section id="wsus__cart_view">
        <div class="container">
            <div class="wsus__pay_info_area">
                <div class="row">
                    <div class="col-xl-4 col-lg-4" id="pay-buttons">
                        <div class="how-topay_payment">
                            <h5 style="font-weight: 600"> Elige Como Quieres Pagar</h5>
                        </div>
                        <div class="wsus__payment_menu" id="sticky_sidebar">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <button class="nav-link" id="v-pills-stripe-tab" data-bs-toggle="pill" data-bs-target="#v-pills-stripe" type="button" role="tab" aria-controls="v-pills-stripe" aria-selected="true">
                                    <div class="payment-option">
                                      <div class="image-mask-payment-gift">
                                        <img src="{{asset('frontend/images/iconos-empresas-sin-fondo/GIFT_debit-credit_card.gif')}}" alt="Tarjeta Debito/Credito" class="payment-icon-gift">
                                      </div>
                                      <div class="payment-text">
                                        <h5>Tarjeta de Débito/Crédito</h5>
                                        <p>Realice sus pagos con Mastercard, Visa y American Express.</p>
                                      </div>
                                    </div>
                                  </button>
                                <button class="nav-link" id="v-pills-paypal-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-paypal" type="button" role="tab"
                                    aria-controls="v-pills-paypal" aria-selected="false">
                                    <div class="payment-option">
                                        <div class="image-mask-payment">
                                            <img src="{{asset('frontend/images/iconos-empresas-sin-fondo/Paypal-logo-uniform.png')}}" alt="Pagar con Paypal" class="payment-icon">
                                        </div>
                                        <div class="payment-text">
                                            <h5>Paypal</h5>
                                            <p>Paga sin compartir tu información bancaria directamente.</p>
                                        </div>
                                    </div>

                                </button>
                                <button class="nav-link active" id="v-pills-transfer-tab" data-bs-toggle="pill" data-bs-target="#v-pills-transfer" type="button" role="tab" aria-controls="v-pills-transfer" aria-selected="false">
                                    <div class="discount-banner">
                                        2% desc. adicional
                                    </div>
                                    <div class="payment-option">
                                        <div class="image-mask-payment">
                                            <img src="{{asset('frontend/images/iconos-empresas-sin-fondo/Transferencia-logo.png')}}" alt="Transferencia" class="payment-icon">
                                        </div>
                                        <div class="payment-text">
                                            <h5>Transferencia SPEI</h5>
                                            <p>Tiempo Maximo de Aprobacion de Pedido 24Hrs</p>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-5">
                        <div class="tab-content" id="v-pills-tabContent" id="sticky_sidebar">

                            <div class="tab-pane fade show  " id="v-pills-stripe" role="tabpanel"
                                aria-labelledby="v-pills-home-tab">
                                @include('frontend.pages.payment-gateway.stripe')
                            </div>
                            <div class="tab-pane fade show" id="v-pills-paypal" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                <div class="row">
                                    <div class="col-xl-12 m-auto">
                                        <div class="wsus__payment_area">
                                            <h5 class="text-center">Pagar con PayPal</h5>
                                            {{-- Contenedor para el botón de PayPal --}}
                                            <div id="paypal-button-container"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade show active" id="v-pills-transfer" role="tabpanel"
                                arialabelledby="v-pills-home-tab">


                                <div class="row text-center">

                                    <h5 class="common_btn">Información Bancaria</h5>
                                    <form action="{{ route('user.payment.transfer') }}" method="POST">
                                        @csrf

                                        <div class="container mt-2 text-center">
                                            <h6>Entidad Bancaria:</h6>
                                            <p>{{ $transferInfo->nameBank }}</p>
                                            <h6>Titular:</h6>
                                            <p>{{ $transferInfo->nameTitular }}</p>
                                            <h6>Numero de cuenta:</h6>
                                            <p>{{ $transferInfo->accountNumber }}</p>
                                            <h6>Numero de Tarjeta:</h6>
                                            <p>{{ $transferInfo->accountTarjet }}</p>
                                            <h6>Clabe Interbancaria:</h6>
                                            <p>{{ $transferInfo->accountClabe }}</p>

                                            <h6>Numero de Orden (Referencia Bancaria):</h6>
                                            <input class="form-control m-1 text-center"
                                                style="font-size: 22px;  transition: none;  " name="refBank"
                                                value="{{ rand(100000, 999999) }}" readonly></input>

                                        </div>
                                        <button class="w-100 mt-2"
                                            style="background-color: #00468c ; color: white; font-size: 15px;
                                        text-transform: capitalize; padding: 10px 0px; font-weight: 600; "
                                            type="submit">Hacer Pedido</button>
                                    </form>
                                </div>
                                <div class="container text-center" id="discount-transfer">
                                    <p>Disfruta de un 2% de descuento al hacer tu pago via Transferencia</p>
                                </div>
                                <div class="text-center mt-3">
                                    <small>Al momento de hacer el Pedido se le hara llegar un correo electronico con
                                        indicaciones adicionales de pedido.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3">
                        <div class="wsus__pay_booking_summary" id="sticky_sidebar2">
                            <h5>Resumen De Compra</h5>
                            <p>subtotal: <span>{{ $settings->currency_icon }}{{ formatCurrency(getCartTotal()) }}</span>
                            </p>
                            <p>Env&iacute;o:
                                <span>{{ $settings->currency_icon }}{{ formatCurrency(getShppingFee()) }}</span></p>
                            <p>Descuento de
                                Cupon:<span>{{ $settings->currency_icon }}{{ formatCurrency(getCartDiscount()) }} </span>
                            </p>

                            <h6>total <span id="total">{{ $settings->currency_icon }}{{ formatCurrency(getFinalPayableAmount()) }}
                                    Mxn</span></h6>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--============================
            PAYMENT PAGE END
        ==============================-->
@endsection

@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=MXN"></script>
<script>
    // Configuracion de Paypal

    paypal.Buttons({
    createOrder: function(data, actions) {
        return fetch('{{ route('user.paypal.createOrder') }}', {
            method: 'post',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(function(res) {
            return res.json();
        }).then(function(orderData) {
            return orderData.id;
        });
    },
    onApprove: function(data, actions) {
        return fetch('{{ route('user.paypal.captureOrder') }}', {
            method: 'post',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                orderId: data.orderID
            })
        }).then(function(res) {
            return res.json();
        }).then(function(details) {
            if (details.redirect_url) {
                window.location.href = details.redirect_url;
            } else {
                alert('Ocurrió un error al procesar el pago. Inténtalo de nuevo.');
                window.location.href = "{{ route('user.paypal.cancel') }}";
            }
        });
    },
    onError: function(err) {
        alert('Ocurrió un error al procesar el pago. Inténtalo de nuevo.');
        window.location.href = "{{ route('user.paypal.cancel') }}";
    },
}).render('#paypal-button-container');



    // Variables para descuento y elementos
    const discountRate = 0.02; // Descuento del 2%
    const transferButton = document.getElementById('v-pills-transfer-tab');
    const stripeButton = document.getElementById('v-pills-stripe-tab');
    const paypalButton = document.getElementById('v-pills-paypal-tab');
    const totalElement = document.getElementById('total');

    // Función para calcular el nuevo total con descuento
    function applyDiscount(isTransferSelected) {
        const originalTotal = {{ getFinalPayableAmount() }};
        const newTotal = isTransferSelected ? originalTotal * (1 - discountRate) : originalTotal;
        totalElement.textContent = `{{ $settings->currency_icon }}${newTotal.toFixed(2)} Mxn`;
    }

    // Eventos de cambio de método de pago
    transferButton.addEventListener('click', () => applyDiscount(true));
    stripeButton.addEventListener('click', () => applyDiscount(false));
    paypalButton.addEventListener('click', () => applyDiscount(false));
</script>

@endpush

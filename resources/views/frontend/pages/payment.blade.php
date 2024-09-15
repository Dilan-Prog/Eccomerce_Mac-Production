@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Pagar
@endsection

@section('content')

    <!--============================
        PAYMENT PAGE START
    ==============================-->
    <section id="wsus__cart_view">
        <div class="container">
            <div class="wsus__pay_info_area">
                <div class="row">
                    <div class="col-xl-3 col-lg-3">
                        <div class="wsus__payment_menu" id="sticky_sidebar">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <button class="nav-link common_btn active" id="v-pills-stripe-tab" data-bs-toggle="pill" data-bs-target="#v-pills-stripe" type="button" role="tab" aria-controls="v-pills-stripe" aria-selected="true">Tarjeta de Débito/Crédito</button>
                                <button class="nav-link common_btn" id="v-pills-paypal-tab" data-bs-toggle="pill" data-bs-target="#v-pills-paypal" type="button" role="tab" aria-controls="v-pills-paypal" aria-selected="false">Paypal</button>
                                <button class="nav-link common_btn" id="v-pills-transfer-tab" data-bs-toggle="pill" data-bs-target="#v-pills-transfer" type="button" role="tab" aria-controls="v-pills-transfer" aria-selected="false">Pago Por Transferencia</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-5">
                        <div class="tab-content" id="v-pills-tabContent" id="sticky_sidebar">
                            <div class="tab-pane fade show active " id="v-pills-stripe" role="tabpanel" aria-labelledby="v-pills-home-tab">
                            @include('frontend.pages.payment-gateway.stripe')
                            </div>
                            <div class="tab-pane fade show" id="v-pills-paypal" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                <div class="row">
                                    <div class="col-xl-12 m-auto">
                                        <div class="wsus__payment_area">
                                            <a class="nav-link common_btn text-center" href="{{route('user.paypal.payment')}}">Pagar con Paypal</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade show" id="v-pills-transfer" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                <div class="row text-center">
                                    <h5 class="common_btn" >Información Bancaria</h5>
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
                                            <input class="form-control m-1 text-center" style="font-size: 22px;  transition: none;  " name="refBank" value="{{ rand(100000, 999999) }}" readonly></input>

                                        </div>
                                        <button class="w-100 mt-2" style="background-color: #00468c ; color: white; font-size: 15px;
                                        text-transform: capitalize; padding: 10px 0px; font-weight: 600; " type="submit" >Hacer Pedido</button>
                                    </form>
                                </div>
                                <div class="text-center mt-3">
                                    <small>Al momento de hacer el Pedido se le hara llegar un correo electronico con indicaciones adicionales de pedido.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4">
                        <div class="wsus__pay_booking_summary" id="sticky_sidebar2">
                            <h5>Resumen De Compra</h5>
                            <p>subtotal: <span>{{ $settings->currency_icon }}{{ formatCurrency(getCartTotal()) }}</span></p>
                            <p>Env&iacute;o: <span>{{ $settings->currency_icon }}{{ formatCurrency(getShppingFee()) }}</span></p>
                            <p>Descuento de Cupon:<span>{{ $settings->currency_icon }}{{ formatCurrency(getCartDiscount()) }} </span></p>

                            <h6>total <span>{{ $settings->currency_icon }}{{ formatCurrency(getFinalPayableAmount()) }} Mxn</span></h6>

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


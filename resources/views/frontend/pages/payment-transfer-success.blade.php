@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Pago Exitoso
@endsection

@section('content')

    <!--============================
        PAYMENT PAGE START
    ==============================-->
    <section id="info-payment-sucess">
        <div class="container">
            <div class="row order-confimation-date">
                <div class="col-12 ">
                    <img src="{{asset('frontend/images/iconos/order.webp')}}" alt="">
                    <div>
                        <h2>Pedido Exitoso</h2>
                        <h6>Tu numero de orden es #{{ $order->invocie_id }} </h6>
                    </div>
                </div>
            </div>
            <div class="row order-confimation">
                <div class="col-4">
                    <a href="{{route('index')}}">
                        <div class="redirection-site-order">
                            <div class="redirection-site-order-content">
                                <img src="{{asset('frontend/images/iconos/home.webp')}}" alt="">
                                <p>Ir a Inicio</p>
                                <small>Descubre miles de ofertas y descuentos exlusivos en nuestros banner promocionales.</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-4 ">
                    <a href="{{route('products.index')}}">
                        <div class="redirection-site-order">
                            <div class="redirection-site-order-content">
                                <img src="{{asset('frontend/images/iconos/full-cart.webp')}}" alt="">
                                <p>Quiero Seguir Comprando</p>
                                <small>Envio gratis en miles de productos y con los mejores precios del mercado.</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-4 ">
                    <a href="{{route('contact')}}">
                        <div class="redirection-site-order">
                            <div class="redirection-site-order-content">
                                <img src="{{asset('frontend/images/iconos/Asesorias-payment-sucess.webp')}}" alt="">
                                <p>Dudas o Asesoria</p>
                                <small>Necesitas Asesoria o tienes alguna duda, marcanos a cualquier número de telefono o mandanos un correo.</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="info-payment-sucess-advert">
            <div class="container">
                <small>Tu número de orden tiene que estar en la referencia de la transferencia.</small>
            </div>
        </div>
    </section>
    <!--============================
        PAYMENT PAGE END
    ==============================-->





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
        'name': '{{ $item->product_name }}',
        'quantity': {{ $item->qty }},
        'price': {{ $item->unit_price }},
        'google_business_vertical': 'retail'
      }@if(!$loop->last),@endif
      @endforeach
    ]
  });
</script>
@endpush


@extends('frontend.layouts.master')

@section('title')
    ¿Cómo pagar con PayPal a meses sin intereses?
@endsection

@section('content')
    <section id="paypal-msi">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="paypal-msi-video">
                        <lite-youtube videoid="3w3xq8VJQSc"></lite-youtube>
                        {{-- Dinamico el video Id --}}
                    </div>
                    <h1 class="paypal-msi-title">¿C&oacute;mo pagar con PayPal a meses sin intereses?</h1>
                    <p class="paypal-msi-subtitle"><strong>Promoción : Activa</strong> — Publicado el 1 de Enero de 2025</p>
                    <p class="paypal-msi-description">
                        En <strong>Mac Del Norte</strong> puedes pagar tus compras en <strong>hasta 3 mensualidades sin
                            intereses</strong> al utilizar PayPal como método de pago.
                        Esta promoción es válida del <strong>1 de enero al 31 de diciembre de 2025</strong> en compras
                        mayores a <strong>$3,000.00 MXN</strong>, utilizando tarjetas de crédito participantes.
                    </p>
                    <br>
                    <p class="paypal-msi-description">
                        <strong>Tarjetas de crédito participantes:</strong> CitiBanamex, BBVA, Banorte, HSBC, Santander,
                        Scotiabank, BanBajío, BanRegio, Afirme, Premium Card Liverpool, Invex, Inbursa, Banca Mifel, Banco
                        Falabella, Banjército y Nu.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection

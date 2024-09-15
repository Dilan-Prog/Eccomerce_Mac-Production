@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Nosotros
@endsection

@section('content')

<section>
    <div class="container">
        <div class="container">
            <div class="row mt-5 mb-4">
                <div class="col-md-6 col-12 mt-3 mb-3 text-start ">
                    <p>¿Qui&eacute;nes somos?</p>
                    <p>Somos una empresa mexicana fundada en marzo del 2018. MAC DEL NORTE es una empresa comercializadora de productos y servicios para la industria, con una amplia experiencia en equipos de instrumentación, control y medición de gas. Nuestro equipo de ventas está constantemente capacitado por parte del fabricante para ofrecer la mejor asesoría y soporte técnico a nuestros clientes. MAC DEL NORTE es distribuidor de las mejores marcas a nivel mundial.<br><br><b>"No somos una opción, somos la elección correcta."</b></p>

                </div>
                <div class="col-md-6 col-12 " id="img-logo">
                    <img src="{{asset('frontend/images/logo/logo-negro-azul.png')}}" alt="logo">
                </div>

            </div>
            <div class="row" id="row-about">
                <div class="col-md-4">
                    <img class="" src="{{asset('frontend/images/iconos/mision.png')}}" alt="">
                    <div class="text-center" style="max-width: auto; overflow: auto;">
                        <h1 id="text-big">MISI&Oacute;N</h1>
                        <p id="text-small">Nos comprometemos a liderar el camino en las tecnolog&iacute;as industriales, buscando continuamente desarrollos y crecimiento profesional. Nuestra misi&oacute;n es generar un valor agregado significativo para nuestros clientes, impuls&aacute;ndolos a alcanzar sus metas con &eacute;xito.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <img src="{{asset('frontend/images/iconos/vision.png')}}" alt="">
                    <div class="text-center" style="max-width: auto; overflow: auto;">
                        <h1 id="text-big">VISI&Oacute;N</h1>
                        <p id="text-small">Nos proyectamos como la empresa l&iacute;der en el &aacute;mbito industrial, respaldando a la industria de Automatizaci&oacute;n, Control y Sistemas de Combusti&oacute;n con el talento y experiencia de nuestro equipo. Nos comprometemos a ofrecer productos y servicios de la m&aacute;s alta calidad, consolid&aacute;ndonos como el socio estrat&eacute;gico preferido en el sector industrial.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <img class="" src="{{asset('frontend/images/iconos/valores.png')}}" alt="">
                    <div class="text-center" style="max-width: auto; overflow: auto;">
                        <h1 id="text-big">VALORES</h1>
                        <p id="text-small">Enfocamos nuestra labor en tres pilares fundamentales: calidad, responsabilidad y compromiso. Buscamos trascender y ganarnos la confianza de nuestros clientes, proporcionando productos y soluciones tecnol&oacute;gicas diseñadas a medida para satisfacer de manera integral sus necesidades en el exigente sector industrial.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>







@endsection

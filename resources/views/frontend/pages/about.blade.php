@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} || Nosotros
@endsection

@section('content')
    <section class="about">
        <div class="container">
            
                <div class="row mt-5 mb-4" id="text-about" >
                    <div class="col-md-6 col-12 mt-3 mb-3 text-start " >
                        <div class="container">
                            <h6>¿Qui&eacute;nes somos?</h6>
                            <p>Mac del Norte es una empresa líder en la distribución de soluciones tecnológicas y productos de
                                alta calidad. Con un compromiso firme hacia la innovación y la satisfacción del cliente, nos
                                especializamos en ofrecer una amplia gama de productos que incluyen cables de distintos tipos,
                                equipos industriales y componentes electrónicos. Nuestra misión es proporcionar a nuestros
                                clientes las herramientas necesarias para optimizar sus procesos y alcanzar sus objetivos.
                                Contamos con un equipo altamente calificado que está siempre dispuesto a brindar asesoría y
                                apoyo personalizado, asegurando así que cada cliente encuentre la solución ideal para sus
                                necesidades. En Mac del Norte, valoramos la calidad y la confiabilidad. Trabajamos únicamente
                                con proveedores reconocidos en la industria, garantizando que cada producto cumpla con los
                                estándares más altos de calidad. Además, nuestro enfoque en la atención al cliente nos permite
                                establecer relaciones duraderas y de confianza con nuestros socios comerciales. Si busca un
                                proveedor que combine experiencia, calidad y un excelente servicio al cliente, Mac del Norte es
                                la elección perfecta.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-12 " id="img-logo">
                        <img src="{{ asset('frontend/images/logo/logo-negro-azul.png') }}" alt="logo">
                    </div>

                </div>
                <div class="row" id="row-about">
                    <div class="col-md-4">
                        <img class="" src="{{ asset('frontend/images/iconos/mision.png') }}" alt="">
                        <div class="text-center" style="max-width: auto; overflow: auto;">
                            <h1 id="text-big">MISI&Oacute;N</h1>
                            <p id="text-small">Nos comprometemos a liderar el camino en las tecnolog&iacute;as industriales,
                                buscando continuamente desarrollos y crecimiento profesional. Nuestra misi&oacute;n es
                                generar un valor agregado significativo para nuestros clientes, impuls&aacute;ndolos a
                                alcanzar sus metas con &eacute;xito.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <img src="{{ asset('frontend/images/iconos/vision.png') }}" alt="">
                        <div class="text-center" style="max-width: auto; overflow: auto;">
                            <h1 id="text-big">VISI&Oacute;N</h1>
                            <p id="text-small">Nos proyectamos como la empresa l&iacute;der en el &aacute;mbito industrial,
                                respaldando a la industria de Automatizaci&oacute;n, Control y Sistemas de Combusti&oacute;n
                                con el talento y experiencia de nuestro equipo. Nos comprometemos a ofrecer productos y
                                servicios de la m&aacute;s alta calidad, consolid&aacute;ndonos como el socio
                                estrat&eacute;gico preferido en el sector industrial.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <img class="" src="{{ asset('frontend/images/iconos/valores.png') }}" alt="">
                        <div class="text-center" style="max-width: auto; overflow: auto;">
                            <h1 id="text-big">VALORES</h1>
                            <p id="text-small">Enfocamos nuestra labor en tres pilares fundamentales: calidad,
                                responsabilidad y compromiso. Buscamos trascender y ganarnos la confianza de nuestros
                                clientes, proporcionando productos y soluciones tecnol&oacute;gicas diseñadas a medida para
                                satisfacer de manera integral sus necesidades en el exigente sector industrial.</p>
                        </div>
                    </div>
                </div>
            
        </div>
    </section>
@endsection

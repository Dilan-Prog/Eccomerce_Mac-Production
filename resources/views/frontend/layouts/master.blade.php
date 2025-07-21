<!DOCTYPE html>
<html lang="es-MX">
<head>
    {{-- etiquetas preconnect para seguimiento de Google y analytics --}}
    <link rel="preconnect" href="https://www.google-analytics.com" crossorigin>
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    <link rel="preconnect" href="https://www.google.com" crossorigin>
    <link rel="preconnect" href="https://www.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://stats.g.doubleclick.net" crossorigin>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximal-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" type="image/png" href="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}" sizes="16x16">
    <link rel="icon" type="image/png" href="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}" sizes="32x32">
    
    <meta name="twitter:card" content="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}">
    <meta name="twitter:site" content="@MacdelNorte">
    <meta name="twitter:title" content="Mac Del Norte:Distribuidora y Comercializadora de Productos Industriales y Especialistas en Instrumentacion">
    <meta name="twitter:description" content="Soluciones innovadoras en instrumentacion, automatización, medición y control con el mejor precio de la industria">
    <meta name="twitter:image" content="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}">

    
    <meta property="og:title" content="Mac Del Norte: Comercializadoras de Productos Industriales de Clase Mundial">
    <meta property="og:description" content="Soluciones innovadoras en instrumentacion, automatización, medición y control con el mejor precio de la industria">
    <meta property="og:image" content="{{asset('frontend/images/logo/AVIAzul-Marino.png')}}">
    <meta property="og:url" content="https://www.macdelnorte.com/">
    <meta property="og:site_name" content="Mac Del Norte">
    <meta property="og:type" content="website">
    <meta property="article:author" content="https://www.facebook.com/macdelnorteofficial">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" fetchpriority="high" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"></noscript>

    <title>
        @yield('title')
    </title>
    @yield('meta_tags')
    
    <meta name="author" content="{{$settings->site_name}}">
    <meta name="description" content="Distribuidora y Comercializadora de prouductos Industriales, Expertos en Instrumentacion y Automatizacion Industrial, Distribuidor Autorizado de productos Industriales.Servicios de Intrumentacion de Campo mas de 7 años de experiencia.">
    <meta name="keywords" content="Distribuidor autorizado Honeywell, especialistas en instrumentación, automatización industrial, proveedor Honeywell México, equipos de control industrial, Dominion Electrónica, Electrónica Universal, soluciones industriales, refacciones industriales, sensores industriales">
    <meta name="robots" content="all">
    <meta name="currency" content="MXN">


    @yield('canonical_URL')

    <link rel="preconnect" href="https://adservice.google.com" crossorigin>
    <link rel="preconnect" href="https://www.googletagservices.com" crossorigin>
    <link rel="preconnect" href="https://securepubads.g.doubleclick.net" crossorigin>
    <link rel="preconnect" href="https://tpc.googlesyndication.com" crossorigin>
    <link rel="preconnect" href="https://pagead2.googlesyndication.com" crossorigin>

{{-- 
    <link rel="preload" fetchpriority="high" href="{{asset('frontend/css/bootstrap.min.css')}}" as="style" onload="this.onload=null;this.rel='stylesheet'" >
    <noscript><link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}"></noscript> --}}
    <link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" fetchpriority="high" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></noscript>

    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> --}}
    <link rel="stylesheet" href="{{asset('frontend/css/all.min.css')}}" >
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-sVZ6X2KzNjzDqEtOoyArBPIzY+Z5tc+yjaNQvv3DxRKu+aO+4hBNfRPTPQkn+HkqFhR3LKaU1Y6T1V2kAUp+Zw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
    <link rel="stylesheet" href="{{asset('frontend/css/select2.min.css')}}" >
    <link rel="stylesheet" href="{{ asset('frontend/css/select2.min.css') }}">
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="{{asset('frontend/css/slick.css')}}">
    {{-- <link href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="{{asset('frontend/css/jquery.nice-number.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/jquery.calendar.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/add_row_custon.css')}}">
    <link rel="stylesheet"  href="{{ asset('frontend/css/mobile_menu.css') }}">
    <link rel="stylesheet" href="{{asset('frontend/css/jquery.exzoom.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/multiple-image-video.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/ranger_style.css')}}">
    <link rel="stylesheet"  href="{{ asset('frontend/css/jquery.classycountdown.css') }}">
    <link rel="stylesheet" href="{{asset('frontend/css/venobox.min.css')}}">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script src="https://www.google.com/recaptcha/api.js?render=6LfT84IrAAAAAKVhNXXrFPDAgMFAiCGdj1-tYz2B"></script>

    <link rel="preload" fetchpriority="high" href="{{asset('frontend/css/style.css')}}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}"></noscript>
    <link rel="stylesheet" href="{{ asset('frontend/css/responsive.css') }}">
    <script defer type="module" src="https://cdn.jsdelivr.net/npm/@justinribeiro/lite-youtube@1/lite-youtube.min.js"></script>

    <!-- <link rel="stylesheet" href="css/rtl.css"> -->
    {{-- <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16512201966">
    </script>
     <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'AW-16512201966');
    </script> --}}
    @stack('Google-Ads')

</head>
<body>
        <!--header-->
        @include('frontend.layouts.header')

        <!--menu-->
        @include('frontend.layouts.menu')

        @include('frontend.layouts.whastapp-chat')

        <!--content-->
        @yield('content')


        <!--footer-->
        @include( 'frontend.layouts.footer' )

    <!--============================
        SCROLL BUTTON START
    ==============================-->
    <div class="wsus__scroll_btn">
        <i class="fas fa-chevron-up"></i>
    </div>
    <!--============================
        SCROLL BUTTON  END
    ==============================-->


    <script src="{{asset('frontend/js/jquery-3.6.0.min.js')}}"></script>
    <!--bootstrap js-->
    <script src="{{asset('frontend/js/bootstrap.bundle.min.js')}}"></script>
    <!--font-awesome js-->
    <script src="{{asset('frontend/js/Font-Awesome.js')}}" ></script>
    <!--select2 js-->
    <script src="{{asset('frontend/js/select2.min.js')}}" ></script>
    <!--slick slider js-->
    <script src="{{asset('frontend/js/slick.min.js')}}" ></script>
    <!--simplyCountdown js-->
    <script src="{{asset('frontend/js/simplyCountdown.js')}}" ></script>
    <!--product zoomer js-->
    <script src="{{asset('frontend/js/jquery.exzoom.js')}}" ></script>
    <!--nice-number js-->
    <script src="{{asset('frontend/js/jquery.nice-number.min.js')}}" ></script>
    <!--counter js-->
    <script src="{{asset('frontend/js/jquery.waypoints.min.js')}}"></script>
    <script src="{{asset('frontend/js/jquery.countup.min.js')}}"></script>
    <!--add row js-->
    <script src="{{asset('frontend/js/add_row_custon.js')}}"></script>
    <!--multiple-image-video js-->
    <script src="{{asset('frontend/js/multiple-image-video.js')}}"></script>
    <!--sticky sidebar js-->
    <script src="{{asset('frontend/js/sticky_sidebar.js')}}"></script>
    <!--price ranger js-->
    <script src="{{asset('frontend/js/ranger_jquery-ui.min.js')}}"></script>
    <script src="{{asset('frontend/js/ranger_slider.js')}}"></script>
    <!--isotope js-->
    <script src="{{asset('frontend/js/isotope.pkgd.min.js')}}"></script>
    <!--venobox js-->
    <script src="{{asset('frontend/js/venobox.min.js')}}"></script>
    <!--classycountdown js-->
    <script src="{{asset('frontend/js/jquery.classycountdown.js')}}"></script>
    {{-- sweetalert js --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    


    <!--main/custom js-->
    <script src="{{asset('frontend/js/main.js')}}"></script>
    <!--toast js-->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


    <script>
        const siteKey = '6LfT84IrAAAAAKVhNXXrFPDAgMFAiCGdj1-tYz2B';

        // Función general para ejecutar y validar reCAPTCHA
        function ejecutarRecaptchaYValidar(action, callbackOK) {
            grecaptcha.ready(() => {
                grecaptcha.execute(siteKey, { action: action }).then(token => {
                    fetch('/recaptcha-validar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ token, action })
                    }).then(res => {
                        if (res.ok) {
                            callbackOK(token);
                        } else {
                            alert('No se pudo validar reCAPTCHA.');
                        }
                    }).catch(() => alert('Error al validar reCAPTCHA'));
                });
            });
        }

        // WhatsApp flotante
        document.querySelector('#whastapp-flotante')?.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarRecaptchaYValidar('whatsapp_flotante', function (token) {
                dataLayer.push({
                    event: 'whatsapp_conversion',
                    action: 'click',
                    label: 'whatsapp_flotante',
                    recaptcha_token: token
                });
                window.open('https://wa.link/f28njw', '_blank');
            });
        });

        // WhatsApp en página de producto
        document.querySelector('#whatsappBtn')?.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarRecaptchaYValidar('whatsapp_click', function (token) {
                dataLayer.push({
                    event: 'whatsapp_conversion',
                    action: 'click',
                    label: 'whatsapp_producto',
                    recaptcha_token: token
                });
                window.open('https://wa.link/f28njw', '_blank');
            });
        });

        // Botón de teléfono
        document.querySelector('#telefonoBtn')?.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarRecaptchaYValidar('telefono_click', function (token) {
                dataLayer.push({
                    event: 'Telefono_Conversion',
                    telefono: '8124738768',
                    recaptcha_token: token
                });
                window.location.href = 'tel:8124738768';
            });
        });
        </script>


    {{-- Track Conversion ads --}}


    <script>
        const urlParams = new URLSearchParams(window.location.search);
        [
        'gclid',
        'utm_source',
        'utm_medium',
        'utm_campaign'
        ].forEach(param => {
        const value = urlParams.get(param);
        if (value) localStorage.setItem(param, value);
        });
        localStorage.setItem('landing_page', window.location.pathname);
    </script>
{{-- Envio de datos Track Conversion --}}
    <script>
        const googleSheetsWebhook = 'https://script.google.com/macros/s/TU_ID_SCRIPT/exec';

        document.addEventListener('DOMContentLoaded', function () {
            const conversionLinks = document.querySelectorAll('a.track-conversion');

            conversionLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    const type = this.dataset.type || 'desconocido';
                    const href = this.getAttribute('href');

                    e.preventDefault();

                    const payload = {
                        gclid: localStorage.getItem('gclid') || '',
                        utm_source: localStorage.getItem('utm_source') || '',
                        utm_medium: localStorage.getItem('utm_medium') || '',
                        utm_campaign: localStorage.getItem('utm_campaign') || '',
                        landing_page: localStorage.getItem('landing_page') || window.location.pathname,
                        type: type
                    };

                    fetch(googleSheetsWebhook, {
                        method: 'POST',
                        mode: 'no-cors',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    }).catch(err => {
                        console.warn('No se pudo guardar la conversión:', err);
                    }).finally(() => {
                        setTimeout(() => {
                            window.open(href, '_blank');
                        }, 300);
                    });
                });
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const conversionLinks = document.querySelectorAll('a.track-conversion');

        conversionLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                const type = this.dataset.type || 'desconocido'; 
                const href = this.getAttribute('href');

                e.preventDefault(); 

               
                const payload = {
                    gclid: localStorage.getItem('gclid') || null,
                    utm_source: localStorage.getItem('utm_source') || null,
                    utm_medium: localStorage.getItem('utm_medium') || null,
                    utm_campaign: localStorage.getItem('utm_campaign') || null,
                    landing_page: localStorage.getItem('landing_page') || window.location.pathname,
                    type: type 
                };

                // Envía la info al backend usando fetch POST
                fetch('{{ route('track.conversion') }}', {
                    method: 'post',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                }).catch(err => {
                    console.warn('No se pudo guardar la conversión:', err);
                }).finally(() => {
                    // Después de 300ms redirige al enlace (WhatsApp o Teléfono)
                    setTimeout(() => {
                        window.open(href, '_blank');
                    }, 300);
                });
            });
        });
    });
    </script>

    <script>
        function loadVideo() {
            var container = document.getElementById('video-container');
            var iframe = document.getElementById('video-frame');
            iframe.src = "https://www.youtube.com/embed/3w3xq8VJQSc?si=45jgHxKR5TKx-AN0";
            container.style.display = 'block'; // Muestra el video
            document.querySelector('.video-placeholder').style.display = 'none'; // Oculta el marcador de posición
        }
        </script>
        
   
    <script>
    @if ($errors->any())
                @foreach ($errors->all() as $error )
                // Display an info toast with no title
                    toastr.error("{{$error}}")
                @endforeach
    @endif
        //Reload for problems with cart
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Si la página fue cargada desde el historial (no una recarga completa)
            window.location.reload();
        }
    });
    </script>


    @include('frontend.layouts.scripts')
    @stack('scripts')
</body>

</html>
{{--   
    <script>
        const siteKey = '6LfT84IrAAAAAKVhNXXrFPDAgMFAiCGdj1-tYz2B';

        // Función general para ejecutar y validar reCAPTCHA
        function ejecutarRecaptchaYValidar(action, callbackOK) {
            grecaptcha.ready(() => {
                grecaptcha.execute(siteKey, { action: action }).then(token => {
                    fetch('/recaptcha-validar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ token, action })
                    }).then(res => {
                        if (res.ok) {
                            callbackOK(token);
                        } else {
                            alert('No se pudo validar reCAPTCHA.');
                        }
                    }).catch(() => alert('Error al validar reCAPTCHA'));
                });
            });
        }

        // WhatsApp flotante
        document.querySelector('#whastapp-flotante')?.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarRecaptchaYValidar('whatsapp_flotante', function (token) {
                dataLayer.push({
                    event: 'whatsapp_conversion',
                    action: 'click',
                    label: 'whatsapp_flotante',
                    recaptcha_token: token
                });
                window.open('https://wa.link/f28njw', '_blank');
            });
        });

        // WhatsApp en página de producto
        document.querySelector('#whatsappBtn')?.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarRecaptchaYValidar('whatsapp_click', function (token) {
                dataLayer.push({
                    event: 'whatsapp_conversion',
                    action: 'click',
                    label: 'whatsapp_producto',
                    recaptcha_token: token
                });
                window.open('https://wa.link/f28njw', '_blank');
            });
        });

        // Botón de teléfono
        document.querySelector('#telefonoBtn')?.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarRecaptchaYValidar('telefono_click', function (token) {
                dataLayer.push({
                    event: 'Telefono_Conversion',
                    telefono: '8124738768',
                    recaptcha_token: token
                });
                window.location.href = 'tel:8124738768';
            });
        });
        </script>
    
    {{-- Track Conversion ads --}}
    <script>
        const googleSheetsWebhook = 'https://script.google.com/macros/s/AKfycbwU_alwJ8RczaMMaRWUCcBD2Pc9exMGsG5vWGX-J7-h5BQajHC43VR3Ufk3QiGeQtZF/exec';

        document.addEventListener('DOMContentLoaded', function () {
            const conversionLinks = document.querySelectorAll('a.track-conversion');

            conversionLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    const type = this.dataset.type || 'desconocido';
                    const href = this.getAttribute('href');

                    e.preventDefault();

                    const payload = {
                        gclid: localStorage.getItem('gclid') || '',
                        utm_source: localStorage.getItem('utm_source') || '',
                        utm_medium: localStorage.getItem('utm_medium') || '',
                        utm_campaign: localStorage.getItem('utm_campaign') || '',
                        landing_page: localStorage.getItem('landing_page') || window.location.pathname,
                        type: type
                    };

                    fetch(googleSheetsWebhook, {
                        method: 'POST',
                        mode: 'no-cors',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    }).catch(err => {
                        console.warn('No se pudo guardar la conversión:', err);
                    }).finally(() => {
                        setTimeout(() => {
                            window.open(href, '_blank');
                        }, 300);
                    });
                });
            });
        });
    </script> --}}





    {{-- <script>
            const urlParams = new URLSearchParams(window.location.search);
            [
            'gclid',
            'utm_source',
            'utm_medium',
            'utm_campaign'
            ].forEach(param => {
            const value = urlParams.get(param);
            if (value) localStorage.setItem(param, value);
            });
            localStorage.setItem('landing_page', window.location.pathname);
    </script> --}}


{{-- 
<script>
            document.addEventListener('DOMContentLoaded', function () {
            const conversionLinks = document.querySelectorAll('a.track-conversion');
    
            conversionLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    const type = this.dataset.type || 'desconocido'; 
                    const href = this.getAttribute('href');
    
                    e.preventDefault(); 
    
                   
                    const payload = {
                        gclid: localStorage.getItem('gclid') || null,
                        utm_source: localStorage.getItem('utm_source') || null,
                        utm_medium: localStorage.getItem('utm_medium') || null,
                        utm_campaign: localStorage.getItem('utm_campaign') || null,
                        landing_page: localStorage.getItem('landing_page') || window.location.pathname,
                        type: type 
                    };
    
                    // Envía la info al backend usando fetch POST
                    fetch('{{ route('track.conversion') }}', {
                        method: 'post',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    }).catch(err => {
                        console.warn('No se pudo guardar la conversión:', err);
                    }).finally(() => {
                        // Después de 300ms redirige al enlace (WhatsApp o Teléfono)
                        setTimeout(() => {
                            window.open(href, '_blank');
                        }, 300);
                    });
                });
            });
        });
    </script> --}}

    
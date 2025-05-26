<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximal-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description" content="Comercializadora de Productos de Clase Mundial y Expertos en Instrumentación somos los principales proveedores de marcas de exelencia calidad Garantizandote Seguridad,Calidad y Confianza con nuestros productos de Calidad Alta, Nos destacamos como una empresa con una Gran Capacidad de Proveer Todos Los Productos Industriales al Mejor Precio del Mercado, ademas de que somo Expertos en Instrumentacion de Campo">
    <meta name="author" content="Mac Del Norte">
    <meta name="keywords" content="automatizacion, instrumentación, ingenieria, honeywell, transmisores de presion, transmisores de temperatura, medidores de flujo, altech méxico, distribuidor honeywell, videoregistradores, fotoceldas, amplificadores de flama, control de flama, controladores y programadores, control de temperatura, dc1010, dc2800, modutroles, distribuidor certificado honeywell ">
    <meta name="robots" content="all">
    
    
    <meta name="currency" content="MXN">

    <link rel="icon" type="image/png" href="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}" sizes="16x16">
    <link rel="icon" type="image/png" href="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}" sizes="32x32">
    
    <meta name="twitter:card" content="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}">
    <meta name="twitter:site" content="@MacdelNorte">
    <meta name="twitter:title" content="Mac Del Norte: Comercializadoras de Productos Industriales y Especialistas en Instrumentacion">
    <meta name="twitter:description" content="Soluciones innovadoras en instrumentacion, automatización, medición y control con el mejor precio de la industria">
    <meta name="twitter:image" content="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}">

    
    <meta property="og:title" content="Mac Del Norte: Comercializadoras de Productos Industriales de Clase Mundial">
    <meta property="og:description" content="Soluciones innovadoras en instrumentacion, automatización, medición y control con el mejor precio de la industria">
    <meta property="og:image" content="{{asset('frontend/images/logo/AVIAzul-Marino.png')}}">
    <meta property="og:url" content="https://www.macdelnorte.com/">
    <meta property="og:site_name" content="Mac Del Norte">
    <meta property="og:type" content="website">
    <meta property="article:author" content="https://www.facebook.com/macdelnorteofficial">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
    <title>
        @yield('title')
    </title>
    
    @yield('canonical_URL')
    <link rel="stylesheet" href="{{asset('frontend/css/bootstrap.min.css')}}" >
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> --}}
    <link rel="stylesheet" href="{{asset('frontend/css/all.min.css')}}" >
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-sVZ6X2KzNjzDqEtOoyArBPIzY+Z5tc+yjaNQvv3DxRKu+aO+4hBNfRPTPQkn+HkqFhR3LKaU1Y6T1V2kAUp+Zw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
    <link rel="stylesheet" href="{{asset('frontend/css/select2.min.css')}}" >
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="{{asset('frontend/css/slick.css')}}">
    {{-- <link href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="{{asset('frontend/css/jquery.nice-number.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/jquery.calendar.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/add_row_custon.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/mobile_menu.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/jquery.exzoom.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/multiple-image-video.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/ranger_style.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/jquery.classycountdown.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/venobox.min.css')}}">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <link rel="stylesheet" href="{{asset('frontend/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/css/responsive.css')}}">
    <script type="module" src="https://cdn.jsdelivr.net/npm/@justinribeiro/lite-youtube@1/lite-youtube.min.js"></script>

    <!-- <link rel="stylesheet" href="css/rtl.css"> -->
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

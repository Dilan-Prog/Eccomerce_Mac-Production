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
    <meta name="twitter:title" content="Mac Del Norte:Distribuidora y Comercializadora de Productos Industriales y Especialistas en Instrumentación">
    <meta name="twitter:description" content="Soluciones innovadoras en instrumentación, automatización, medición y control con el mejor precio de la industria">
    <meta name="twitter:image" content="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}">


    <meta property="og:title" content="Mac Del Norte: Comercializadora de Productos Industriales de Clase Mundial">
    <meta property="og:description" content="Soluciones innovadoras en instrumentación, automatización, medición y control con el mejor precio de la industria">
    <meta property="og:image" content="{{asset('frontend/images/logo/AVIAzul-Marino.png')}}">
    <meta property="og:url" content="https://www.macdelnorte.com/">
    <meta property="og:site_name" content="Mac Del Norte">
    <meta property="og:type" content="website">
    <meta property="article:author" content="https://www.facebook.com/macdelnorteofficial">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" fetchpriority="high" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"></noscript>
    <style>
        :root {
        --azul-oscuro: #002856;
        --blanco: #FFFFFF;
        --amarillo-destacado: #F6AD1C;
        }
                /* Contenedor principal */
        .top-bar {
        background: var(--azul-oscuro); /* #002856 */
        color: var(--blanco);           /* #FFFFFF */
        font-size: 13px;
        padding: 8px 0;
        }

        /* Fila interior (usa la clase .container + .top-bar-inner) */
        .top-bar-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        }

        /* Grupo izquierdo (ícono + texto de contacto/ubicación) */
        .top-bar-left {
        display: flex;
        gap: 22px;
        flex-wrap: wrap;
        }

        /* Cada ítem individual (SVG + texto) */
        .top-bar-item {
        display: flex;
        align-items: center;
        gap: 6px;
        opacity: 0.95;
        }
        .top-bar-item a {
        color: var(--blanco)

        }

        /* Tamaño de los SVG dentro de cada ítem */
        .top-bar-item svg {
        width: 14px;
        height: 14px;
        }

        /* Grupo derecho (links de soporte / asociados) */
        .top-bar-right {
        display: flex;
        gap: 18px;
        }

        /* Links del grupo derecho */
        .top-bar-right a {
        color: var(--blanco);      /* #FFFFFF */
        text-decoration: none;
        font-weight: 600;
        }

        /* Hover de los links */
        .top-bar-right a:hover, .top-bar-item a:hover {
        color: var(--amarillo-destacado); /* #F6AD1C */
        }
    </style>
    <title>
        @yield('title')
    </title>
    @yield('meta_tags')

    <meta name="author" content="{{$settings->site_name}}">
    <meta name="description" content="Distribuidora y Comercializadora de productos Industriales, Expertos en Instrumentación y Automatización Industrial, Distribuidor Autorizado de productos Industriales.Servicios de Instrumentación de Campo mas de 7 años de experiencia.">
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
    @vite(['resources/css/app.css','resources/js/app.js'])
    @vite(['resources/css/auth.css', 'resources/js/auth.js'])
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script src="https://www.google.com/recaptcha/api.js?render=6LfT84IrAAAAAKVhNXXrFPDAgMFAiCGdj1-tYz2B"></script>
    <script defer type="module" src="https://cdn.jsdelivr.net/npm/@justinribeiro/lite-youtube@1/lite-youtube.min.js"></script>

            <!-- Captura GCLID + UTM -->
            <script>
            (function() {
                const params = new URLSearchParams(window.location.search);

                if (params.has('gclid')) localStorage.setItem('gclid', params.get('gclid'));
                if (params.has('utm_source')) localStorage.setItem('utm_source', params.get('utm_source'));
                if (params.has('utm_medium')) localStorage.setItem('utm_medium', params.get('utm_medium'));
                if (params.has('utm_campaign')) localStorage.setItem('utm_campaign', params.get('utm_campaign'));

                if (!localStorage.getItem('landing_page')) {
                    localStorage.setItem('landing_page', window.location.pathname);
                }
            })();
            </script>

  <!-- Otros CSS o scripts que carguen después -->
    <style>
        /* ===== VARIABLES ADICIONALES ===== */
        :root {
            --azul-principal: #003E7E;
            --azul-medio: #0057A8;
            --azul-claro: #E6EFF8;
            --gris-fondo: #F5F7FA;
            --gris-borde: #DDE3EA;
            --gris-texto: #4A5568;
            --gris-claro-texto: #718096;
            --accent-cta: #F7941D;
            --accent-cta-hover: #E08416;
            --rojo-error: #DC2626;
            --rojo-claro: #FEE2E2;
        }

        /* ===== ACCOUNT DROPDOWN ===== */
        .account-dropdown-wrapper { position: relative; z-index: 400; }

        .account-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 270px;
            background: var(--blanco);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,62,126,0.15);
            border: 1px solid var(--gris-borde);
            z-index: 500;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
            overflow: hidden;
        }
        .account-dropdown.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .account-dropdown-header {
            padding: 16px 18px;
            background: var(--azul-claro);
            border-bottom: 1px solid var(--gris-borde);
        }
        .account-dropdown-name {
            font-size: 14px; font-weight: 800; color: var(--azul-principal);
            margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .account-dropdown-email {
            font-size: 12px; color: var(--gris-claro-texto);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .account-dropdown-group {
            padding: 6px 0;
            border-bottom: 1px solid var(--gris-borde);
        }
        .account-dropdown-group:last-child { border-bottom: none; }
        .account-dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 18px; font-size: 13px; font-weight: 600;
            color: var(--gris-texto); text-decoration: none;
            transition: all 0.15s; width: 100%;
            background: none; border: none; cursor: pointer;
            font-family: inherit; text-align: left;
        }
        .account-dropdown-item svg {
            width: 16px; height: 16px; flex-shrink: 0; color: var(--gris-claro-texto);
        }
        .account-dropdown-item:hover {
            background: var(--azul-claro); color: var(--azul-principal);
        }
        .account-dropdown-item:hover svg { color: var(--azul-principal); }
        .account-dropdown-logout { color: var(--rojo-error); }
        .account-dropdown-logout svg { color: var(--rojo-error); }
        .account-dropdown-logout:hover {
            background: var(--rojo-claro); color: var(--rojo-error);
        }
        .account-trigger.active { color: var(--azul-principal); }

        @media (max-width: 560px) {
            .account-dropdown {
                width: calc(100vw - 32px);
                right: -16px;
            }
        }

        /* ===== STICKY WRAPPER (top-bar + header + menu) ===== */
        .sticky-wrapper {
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* ===== HEADER ===== */
        header {
            background: var(--blanco);
            border-bottom: 1px solid var(--gris-borde);
        }
        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;
        }
        .logo {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .logo-img {
            height: 44px;
            width: auto;
            display: block;
        }
        .logo-sub {
            font-size: 11px;
            color: var(--azul-medio);
            font-weight: 700;
            font-style: italic;
            letter-spacing: 0.2px;
            margin-top: 4px;
            line-height: 1.1;
            max-width: 220px;
        }
        .search-bar {
            flex: 1;
            max-width: 560px;
            position: relative;
        }
        .search-bar form {
            width: 100%;
        }
        .search-bar input {
            width: 100%;
            padding: 13px 18px 13px 44px;
            border: 1.5px solid var(--gris-borde);
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s;
            background: var(--gris-fondo);
        }
        .search-bar input:focus {
            outline: none;
            border-color: var(--azul-principal);
            background: var(--blanco);
            box-shadow: 0 0 0 3px rgba(0,62,126,0.1);
        }
        .search-bar > svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--gris-claro-texto);
            pointer-events: none;
            z-index: 1;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-shrink: 0;
        }
        .header-icon-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--azul-principal);
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            position: relative;
        }
        .header-icon-btn svg {
            width: 24px;
            height: 24px;
        }
        .cart-badge {
            position: absolute;
            top: -4px;
            right: -6px;
            background: var(--accent-cta);
            color: var(--blanco);
            font-size: 10px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        /* ===== BARRA INSTITUCIONAL ===== */
        .nav-secondary {
            background: var(--blanco);
            border-bottom: 1px solid var(--gris-borde);
        }
        .nav-secondary-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 0;
        }
        .nav-secondary-links {
            display: flex;
            align-items: center;
        }
        .nav-secondary-link {
            padding: 12px 20px;
            text-decoration: none;
            color: var(--gris-texto);
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            border-bottom: 2px solid transparent;
        }
        .nav-secondary-link:hover {
            color: var(--accent-cta);
            border-bottom-color: var(--accent-cta);
        }
            .nav-secondary-link.active {
                color: var(--accent-cta);
                border-bottom-color: var(--accent-cta);
            }
        .nav-secondary-link svg {
            width: 14px;
            height: 14px;
            opacity: 0.7;
        }
        .nav-secondary-divider {
            width: 1px;
            height: 16px;
            background: var(--gris-borde);
            flex-shrink: 0;
        }
        .nav-secondary-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .nav-secondary-quote {
            background: var(--azul-claro);
            color: var(--azul-principal);
            padding: 6px 14px;
            border-radius: 4px;
            font-weight: 700;
            text-decoration: none;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .nav-secondary-quote:hover {
            background: var(--accent-cta);
            color: var(--blanco);
        }
        .nav-secondary-quote svg {
            width: 12px;
            height: 12px;
        }

        /* ===== DROPDOWN PRODUCTOS (barra institucional) ===== */
        .nav-sec-dropdown-wrap {
            position: relative;
        }
        .nav-sec-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--blanco);
            min-width: 240px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
            border-radius: 0 0 8px 8px;
            border-top: 3px solid var(--accent-cta);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.2s;
            z-index: 200;
            padding: 8px 0;
        }
        .nav-sec-dropdown-wrap:hover .nav-sec-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .nav-sec-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            color: var(--gris-texto);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s;
            border-left: 3px solid transparent;
            white-space: nowrap;
        }
        .nav-sec-dropdown a:hover {
            background: var(--gris-fondo);
            color: var(--azul-principal);
            border-left-color: var(--accent-cta);
        }
        .nav-sec-chevron {
            width: 12px !important;
            height: 12px !important;
            opacity: 0.7;
            margin-left: 2px;
            transition: transform 0.2s;
        }
        .nav-sec-dropdown-wrap:hover .nav-sec-chevron {
            transform: rotate(180deg);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 960px) {
            .nav-secondary-right { display: none; }
            .nav-secondary-inner { justify-content: center; }
        }
        @media (max-width: 560px) {
            .header-icon-btn span:not(.cart-badge) { display: none; }
            .header-inner { padding: 12px 0; }
        }

        /* ===== HERO SECTION ===== */
        .hero-home {
            background-color: #002856;
            position: relative;
            min-height: 520px;
            height: 80vh;
            max-height: 700px;
            color: #fff;
            overflow: hidden;
        }
        /* Container ocupa toda la altura del section */
        .hero-container {
            position: relative;
            height: 100%;
        }
        /* Capas de foto dentro del container (ping-pong JS) */
        .hero-bg-layer {
            position: absolute;
            inset: 0;
            z-index: 0;
            background-size: cover;
            background-position: right center;
            background-repeat: no-repeat;
            transition: opacity 0.8s ease-in-out;
        }
        /* Gradiente sobre la foto, debajo del contenido */
        .hero-grad-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            background: linear-gradient(90deg, rgba(0,40,86,0.97) 0%, rgba(0,40,86,0.93) 38%, rgba(0,40,86,0.45) 62%, rgba(0,40,86,0) 100%);
            pointer-events: none;
        }
        /* Contenido sobre el gradiente */
        .hero-content-wrap {
            position: relative;
            z-index: 2;
            padding-top: 72px;
        }
        .hero-badge-distribuidor {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(247,148,29,0.18);
            color: #F7941D;
            border: 1px solid rgba(247,148,29,0.35);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 24px;
        }
        .hero-titulo {
            font-size: clamp(26px, 3.5vw, 48px);
            font-weight: 800;
            line-height: 1.18;
            margin: 0 0 20px;
            color: #fff;
            max-width: 700px;
        }
        .hero-titulo .highlight {
            color: #F7941D;
        }
        .hero-subtitulo {
            font-size: 16px;
            color: rgba(255,255,255,0.78);
            line-height: 1.7;
            margin: 0 0 36px;
            max-width: 580px;
        }
        .hero-cta-grupo {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 56px;
        }
        .hero-home .btn-primary {
            background: #F7941D;
            color: #fff !important;
            border-color: #F7941D;
            box-shadow: 0 4px 18px rgba(247,148,29,0.4);
            font-weight: 700;
            padding: 13px 26px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .hero-home .btn-primary:hover {
            background: #E08416;
            border-color: #E08416;
            color: #fff !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(247,148,29,0.5);
        }
        .hero-home .btn-secondary {
            background: rgba(255,255,255,0.12);
            color: #fff !important;
            border: 1.5px solid rgba(255,255,255,0.35);
            font-weight: 700;
            padding: 13px 26px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .hero-home .btn-secondary:hover {
            background: rgba(255,255,255,0.22);
            color: #fff !important;
            border-color: rgba(255,255,255,0.6);
        }

        /* Stats bar */
        .hero-stats-bar {
            display: inline-flex;
            width: 45%;
            border-top: 1px solid rgba(255,255,255,0.15);
            padding: 24px 0;
            margin-top: 4px;
        }
        .hero-stat-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0 12px;
            gap: 5px;
            border-right: 1px solid rgba(255,255,255,0.12);
        }
        .hero-stat-item:last-child { border-right: none; }
        .hero-stat-item svg { color: #F7941D; }
        .hero-stat-numero {
            font-size: 30px;
            font-weight: 900;
            color: #fff;
            line-height: 1;
        }
        .hero-stat-label {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
            font-weight: 600;
            line-height: 1.3;
        }

        /* ===== HERO RESPONSIVE ===== */
        @media (min-width: 769px) and (max-width: 1024px) {
            .hero-home {
                min-height: 460px;
                height: 70vh;
                background-position: 75% center;
            }
        }
        @media (max-width: 768px) {
            .hero-home {
                min-height: 420px;
                height: auto;
            }
            /* Mobile: oculta foto y gradiente, queda el azul sólido */
            .hero-bg-layer { display: none; }
            .hero-grad-overlay { display: none; }
            .hero-content-wrap { padding-top: 52px; }
            .hero-titulo { font-size: clamp(24px, 5vw, 34px); }
            .hero-subtitulo { font-size: 14px; margin-bottom: 28px; }
            .hero-cta-grupo { margin-bottom: 40px; }
            .hero-stats-bar { width: 100%; flex-wrap: wrap; }
            .hero-stat-item {
                flex: 0 0 50%;
                padding: 16px 12px;
                border-right: none;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            .hero-stat-item:nth-child(odd)  { border-right: 1px solid rgba(255,255,255,0.12); }
            .hero-stat-item:nth-last-child(-n+2) { border-bottom: none; }
        }
        @media (max-width: 480px) {
            .hero-cta-grupo { flex-direction: column; }
            .hero-home .btn-primary,
            .hero-home .btn-secondary { text-align: center; width: 100%; }
            .hero-stat-numero { font-size: 24px; }
        }
    </style>
    @stack('styles')
</head>


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
        <!--header sticky wrapper-->
        <div class="sticky-wrapper">
            @include('frontend.layouts.top-bar')
            @guest
            <div class="guest-info-banner" id="guest-banner">
                <div class="container">
                    <div class="guest-banner-inner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span>
                            <strong>¿Buscas precios?</strong>
                            Regístrate gratis o inicia sesión para ver precios, agregar al carrito y comprar.
                        </span>
                        <div class="guest-banner-actions">
                            <a href="{{ route('register') }}" class="banner-btn-register">Crear cuenta</a>
                            <a href="{{ route('login') }}"    class="banner-btn-login">Iniciar sesión</a>
                        </div>
                        <button class="banner-close" onclick="
                            document.getElementById('guest-banner').style.display='none';
                            sessionStorage.setItem('guest_banner_closed','1');
                        " aria-label="Cerrar">✕</button>
                    </div>
                </div>
            </div>
            <script>
                if (sessionStorage.getItem('guest_banner_closed') === '1') {
                    var b = document.getElementById('guest-banner');
                    if (b) b.style.display = 'none';
                }
            </script>
            @endguest
            @include('frontend.layouts.header')
            @include('frontend.layouts.menu')
        </div>

        @include('frontend.layouts.whastapp-chat')
        {{-- @include('frontend.layouts.chat-personal') --}}

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


    {{-- All jQuery/plugin scripts are now bundled via Vite (resources/js/app.js) --}}
    {{-- sweetalert js --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>





    {{-- jQuery debe cargar antes que toastr y los scripts inline --}}
    <script src="{{ asset('frontend/js/jquery-3.6.0.min.js') }}"></script>
    {{-- classycountdown: cargado aquí como script regular (fuera del bundle Vite)
         porque su wrapper UMD usa `this.jQuery`; en ESM `this` es undefined --}}
    <script src="{{ asset('frontend/js/jquery.classycountdown.js') }}"></script>
    {{-- toastr js (CDN — not bundled) --}}
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
{{-- Envio de datos Track Conversion --}}
    <script>
        const googleSheetsWebhook = 'https://script.google.com/macros/s/AKfycbwp8PQWscSciD7gc5c1DMiFntOr0HuAPraK9pfzwtzqCP_4DF9azi8Fy16HfEeqxK3T/exec';

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
                        type: type,
                        // Fecha en zona horaria de México (formato: YYYY-MM-DD HH:MM:SS)
                        fecha: new Date().toLocaleString('sv-SE', { timeZone: 'America/Mexico_City' })
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
                    type: type,
                    // Fecha en zona horaria de México (formato: YYYY-MM-DD HH:MM:SS)
                    fecha: new Date().toLocaleString('sv-SE', { timeZone: 'America/Mexico_City' })
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


    <script>
    (function () {
        var trigger  = document.getElementById('account-trigger');
        var dropdown = document.getElementById('account-dropdown');
        var wrapper  = document.getElementById('account-dropdown-wrapper');
        if (!trigger || !dropdown || !wrapper) return;

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = dropdown.classList.contains('open');
            dropdown.classList.toggle('open', !isOpen);
            trigger.setAttribute('aria-expanded', String(!isOpen));
            dropdown.setAttribute('aria-hidden', String(isOpen));
            trigger.classList.toggle('active', !isOpen);
        });

        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
                dropdown.setAttribute('aria-hidden', 'true');
                trigger.classList.remove('active');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && dropdown.classList.contains('open')) {
                dropdown.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
                dropdown.setAttribute('aria-hidden', 'true');
                trigger.classList.remove('active');
                trigger.focus();
            }
        });
    })();
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
{{--
ew    <script>
    const googleSheetsWebhook = 'https://script.google.com/macros/s/AKfycbwp8PQWscSciD7gc5c1DMiFntOr0HuAPraK9pfzwtzqCP_4DF9azi8Fy16HfEeqxK3T/exec
';

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

 --}}




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





    {{-- Corregir --}}
    <script src="https://www.google.com/recaptcha/api.js?render=6LfT84IrAAAAAKVhNXXrFPDAgMFAiCGdj1-tYz2B"></script>

<script>
(function () {
const siteKey = '6LfT84IrAAAAAKVhNXXrFPDAgMFAiCGdj1-tYz2B';
const googleSheetsWebhook = 'https://script.google.com/macros/s/AKfycbwU_alwJ8RczaMMaRWUCcBD2Pc9exMGsG5vWGX-J7-h5BQajHC43VR3Ufk3QiGeQtZF/exec';

// Define URLs para cada tipo de botón (según tu data-type)
const urlMap = {
  'whatsapp': 'https://wa.link/f28njw',
  'whatsapp_flotante': 'https://wa.link/f28njw',
  'whatsapp_producto': 'https://wa.link/f28njw',
  'telefono': 'tel:8124738768'
};

// Función general para ejecutar y validar reCAPTCHA v3
function ejecutarRecaptchaYValidar(action, callbackOK) {
  grecaptcha.ready(() => {
    grecaptcha.execute(siteKey, { action: action }).then(token => {
      fetch('/recaptcha-validar', {  // Aquí tu endpoint para validar token
        method: 'POST',
        mode: 'no-cors',
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

document.addEventListener('DOMContentLoaded', () => {
  const conversionLinks = document.querySelectorAll('a.track-conversion');

  conversionLinks.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();

      const type = link.dataset.type || 'desconocido';
      const url = urlMap[type] || '#';

      ejecutarRecaptchaYValidar(type, token => {
        // Envía evento a dataLayer si existe
        if (window.dataLayer) {
          dataLayer.push({
            event: 'conversion_event',
            action: 'click',
            label: type,
            recaptcha_token: token
          });
        }

        // Prepara payload para Google Sheets
        const payload = {
          gclid: localStorage.getItem('gclid') || '',
          utm_source: localStorage.getItem('utm_source') || '',
          utm_medium: localStorage.getItem('utm_medium') || '',
          utm_campaign: localStorage.getItem('utm_campaign') || '',
          landing_page: localStorage.getItem('landing_page') || window.location.pathname,
          type: type,
          // Fecha en zona horaria de México (formato: YYYY-MM-DD HH:MM:SS)
          fecha: new Date().toLocaleString('sv-SE', { timeZone: 'America/Mexico_City' })
        };

        // Envía a Google Sheets via webhook (sin-cors)
        fetch(googleSheetsWebhook, {
          method: 'POST',

          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        }).catch(err => console.warn('No se pudo guardar la conversión:', err));

        // Finalmente abre la URL correcta
        if (type.startsWith('whatsapp')) {
          window.open(url, '_blank');
        } else if (type.startsWith('telefono') || type.startsWith('llamada')) {
          window.location.href = url;
        } else {
          window.open(url, '_blank');
        }
      });
    });
  });
});
})();
</script>

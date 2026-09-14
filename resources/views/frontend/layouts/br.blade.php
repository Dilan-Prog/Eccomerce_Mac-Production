{{-- ============================================================================
     Layout de la sección Brasil (pt-BR).

     Hereda de master TODO lo que no es visible: assets (Vite, Bootstrap,
     Poppins), Google Tag Manager, reCAPTCHA y el tracking de conversiones.
     Sustituye lo visible: la cabecera con datos de México, los menús del
     catálogo, el aviso de "¿Buscas precios?", el footer y el flotante de
     WhatsApp, por versiones propias en portugués.

     Las vistas de esta sección hacen @extends('frontend.layouts.br') y
     rellenan @section('content') como siempre.
     ============================================================================ --}}
@extends('frontend.layouts.master')

@section('html_lang', 'pt-BR')

@section('chrome_top')
    @include('frontend.layouts.br.header')
@endsection

@section('chrome_bottom')
    @include('frontend.layouts.br.footer')
@endsection

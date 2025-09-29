@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}}

@endsection
@section('meta_tags')
    <meta name="author" content="{{$settings->site_name}}">

@endsection

@section('content')
     <!--============================
        BANNER PART 2 END
    ==============================-->
        @include('frontend.home.sections.banner-slider')
     <!--============================
        BANNER PART 2 END
    ==============================-->
    


        @include('frontend.home.sections.section-shop-info')

    <!--============================
        FLASH SELL START
    ==============================-->
    <div class="container">
        <div class="row">
            <!-- Banner Publicitario -->
            <div class="col-lg-3 col-xl-3 d-none d-lg-block">
                <div class="ad-banner_home">
                        <div class="banner-item">
                            <a href="{{ route('paypal-msi-info')}}">
                                <img src="{{ asset('uploads/home-image/1er-banner msi.png') }}" alt="Banner" class="img-fluid">
                            </a>
                        </div>
                        <div class="banner-item">
                            <a href="https://www.macdelnorte.com/products?category=controladores-y-programadores">
                                <img src="{{ asset('uploads/home-image/2dobanner.png') }}" alt="Banner" class="img-fluid">
                            </a>
                        </div>
                        <div class="banner-item">
                            <a href="">
                                <img src="{{ asset('uploads/home-image/3er banner marca.png') }}" alt="Banner" class="img-fluid">
                            </a>
                        </div>
                        <div class="banner-item">
                            <a href="https://www.macdelnorte.com/contact">
                                <img src="{{ asset('uploads/home-image/4tobanner-problemas.png') }}" alt="Banner" class="img-fluid">
                            </a>
                        </div>
                        <div class="banner-item">
                            <a href="https://www.macdelnorte.com/product-detail/control-edge-hc900">
                                <img src="{{ asset('uploads/home-image/5tobanner-promociones.png') }}" alt="Banner" class="img-fluid">
                            </a>
                        </div>
                        <div class="banner-item">
                            <a href="{{route('servicio-instalacion-medidoresdeflujo')}}">
                                <img src="{{ asset('uploads/home-image/6tobanner-promociones.png') }}" alt="Banner" class="img-fluid">
                            </a>
                        </div>
                        {{-- <div class="banner-item">
                            <a href="">
                                <img src="{{ asset('uploads/home-image/5tobanner-promociones.png') }}" alt="Banner" class="img-fluid">
                            </a>
                        </div>
                        <div class="banner-item">
                            <a href="">
                                <img src="{{ asset('uploads/home-image/2dobanner.png') }}" alt="Banner" class="img-fluid">
                            </a>
                        </div>
                        <div class="banner-item">
                            <a href="">
                                <img src="{{ asset('uploads/home-image/2dobanner.png') }}" alt="Banner" class="img-fluid">
                            </a>
                        </div> --}}
                </div>
            </div>
            <div class="col-12 col-lg-9 col-xl-9">
                @include('frontend.home.sections.flash-sale')
            <!--============================
                FLASH SELL END
            ==============================-->


            <!--============================
            MONTHLY TOP PRODUCT START
            ==============================-->
                @include('frontend.home.sections.brands-mark')
            <!--============================
            MONTHLY TOP PRODUCT END
            ==============================-->

            <!--============================
            LIMIT-SWITCH, MICRO-SWITCH,SENSOR CNTD
            ==============================-->
            @include('frontend.home.sections.category-product-slider-two')
            <!--============================
                ELECTRONIC PART END
            ==============================-->

            <!--============================
                Instrument PART START
            ==============================-->
            @include('frontend.home.sections.category-product-slider-one') 
            <!--============================
                Instrument PART END
            ==============================-->

            <!--============================
            ELECTRONIC PART START
            ==============================-->
            @include('frontend.home.sections.category-product-slider-three')
            <!--============================
                ELECTRONIC PART END
            ==============================-->
            </div>
        </div>
    </div>
     <!--============================
       HOW BUY TO MAC DEL NORTE
    ==============================-->
    @include('frontend.home.sections.video-eccomerce')
    <!--============================
       HOW BUY TO MAC DEL NORTE END
    ==============================-->
    
    
    {{-- @include('frontend.home.sections.whastapp-chat') --}}




   


{{--
   

{{--
    <!--============================
        LARGE BANNER  START
    ==============================-->
    @include('frontend.home.sections.larg-banner')
    <!--============================
        LARGE BANNER  END
    ==============================-->


    <!--============================
        WEEKLY BEST ITEM START
    ==============================-->
    @include('frontend.home.sections.weekly-best-')
    <!--============================
        WEEKLY BEST ITEM END
    ==============================-->


    <!--============================
      HOME SERVICES START
    ==============================-->
    @include('frontend.home.sections.services')
    <!--============================
        HOME SERVICES END
    ==============================-->


    <!--============================
        HOME BLOGS START
    ==============================-->
    @include('frontend.home.sections.blog')
    <!--============================
        HOME BLOGS END
    ==============================-->
--}}




@endsection

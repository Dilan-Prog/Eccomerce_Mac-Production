@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}}
@endsection

@section('content')
     <!--============================
        BANNER PART 2 END
    ==============================-->
        @include('frontend.home.sections.banner-slider')
     <!--============================
        BANNER PART 2 END
    ==============================-->


    <!--============================
        FLASH SELL START
    ==============================-->
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


     <!--============================
       HOW BUY TO MAC DEL NORTE
    ==============================-->
    @include('frontend.home.sections.video-eccomerce')
    <!--============================
       HOW BUY TO MAC DEL NORTE END
    ==============================-->
    @include('frontend.home.sections.chat-personal')




   


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

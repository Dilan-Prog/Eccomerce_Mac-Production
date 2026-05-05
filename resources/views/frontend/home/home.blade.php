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

        {{-- Opinion Google --}}

        @include('frontend.home.sections.opinion-google')

        {{-- Categorías principales + Por qué elegirnos + Banda de marcas --}}
        @include('frontend.home.sections.categories-why-us')


   
    {{-- Banner programa B2B --}}
    @include('frontend.home.sections.banner-b2b')

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

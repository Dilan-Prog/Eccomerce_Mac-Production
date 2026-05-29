@extends('frontend.layouts.master')
@section('title')
{{$settings->site_name}}
@endsection
@section('meta_tags')
    <meta name="author" content="{{$settings->site_name}}">
@endsection
@section('content')
        @include('frontend.home.sections.banner-slider')
        @include('frontend.home.sections.section-shop-info')
        @include('frontend.home.sections.opinion-google')
        @include('frontend.home.sections.categories-why-us')
    @include('frontend.home.sections.banner-b2b')
@endsection

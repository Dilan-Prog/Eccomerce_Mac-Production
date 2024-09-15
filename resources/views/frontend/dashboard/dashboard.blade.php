@extends('frontend.dashboard.layouts.master')
@section('title')
{{$settings->site_name}} || Mi Cuenta
@endsection

@section('content')




  <!--=============================
    DASHBOARD START
  ==============================-->
  <section id="wsus__dashboard">
    <div class="container-fluid">
        {{--Sidebar--}}
        @include('frontend.dashboard.layouts.sidebar')

      
    </div>
  </section>
  <!--=============================
    DASHBOARD START
  ==============================-->



  @endsection

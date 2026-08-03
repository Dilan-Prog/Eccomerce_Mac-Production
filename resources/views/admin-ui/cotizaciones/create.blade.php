@extends('admin-ui.layouts.master')

@section('title', 'Nueva Cotización')

@section('content')
    @include('admin-ui.cotizaciones._builder', ['cotizacion' => null])
@endsection

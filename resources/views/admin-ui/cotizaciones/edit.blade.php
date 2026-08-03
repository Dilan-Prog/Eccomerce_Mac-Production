@extends('admin-ui.layouts.master')

@section('title', 'Cotización ' . $cotizacion->folio)

@section('content')
    @include('admin-ui.cotizaciones._builder', ['cotizacion' => $cotizacion])
@endsection

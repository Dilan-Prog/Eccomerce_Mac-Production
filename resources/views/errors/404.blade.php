@extends('frontend.layouts.master')

@section('title')
{{ $settings->site_name ?? 'Mac Del Norte' }} || Página no encontrada
@endsection

@push('styles')
<style>
.mdn-404 {
    max-width: 640px;
    margin: 0 auto;
    padding: 80px 20px 96px;
    text-align: center;
}
.mdn-404 .code {
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 2px;
    color: #F6AD1C;
    margin-bottom: 12px;
}
.mdn-404 h1 {
    font-size: 30px;
    font-weight: 800;
    color: #1A202C;
    margin: 0 0 14px;
}
.mdn-404 p {
    font-size: 15px;
    color: #4A5568;
    line-height: 1.6;
    margin: 0 0 32px;
}
.mdn-404 .actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
.mdn-404 .actions a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 22px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    transition: opacity .15s ease;
}
.mdn-404 .actions a:hover { opacity: .85; }
.mdn-404 .actions .primary {
    background: #F6AD1C;
    color: #1A202C;
}
.mdn-404 .actions .secondary {
    background: #fff;
    color: #003E7E;
    border: 1px solid #cbd5e1;
}
</style>
@endpush

@section('content')
<div class="mdn-404">
    <div class="code">ERROR 404</div>
    <h1>No encontramos esta página</h1>
    <p>El enlace que seguiste puede estar roto o la página pudo haberse movido. Prueba desde el inicio o busca directamente el producto que necesitas.</p>
    <div class="actions">
        <a href="{{ route('index') }}" class="primary"><i class="fas fa-home"></i> Volver al inicio</a>
        <a href="{{ route('products.index') }}" class="secondary"><i class="fas fa-search"></i> Ver catálogo</a>
    </div>
</div>
@endsection

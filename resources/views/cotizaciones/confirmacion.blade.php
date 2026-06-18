@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} || Confirmar Cotización
@endsection

@push('styles')
<style>
.cot-page { padding: 32px 0 64px; background: var(--gris-fondo); }
.cot-card {
    background: var(--blanco); border-radius: 12px;
    border: 1px solid var(--gris-borde); padding: 36px 40px;
    max-width: 640px; margin: 0 auto; text-align: center;
}
.cot-card h2 {
    font-size: 22px; font-weight: 800; color: var(--azul-principal);
    margin-bottom: 8px;
}
.cot-card .cot-subtitle {
    font-size: 14px; color: var(--gris-claro-texto); margin-bottom: 28px;
}
.perfil-summary {
    background: var(--gris-fondo); border-radius: 8px;
    border: 1px solid var(--gris-borde); padding: 20px 24px;
    text-align: left; margin-bottom: 28px;
}
.perfil-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 7px 0; border-bottom: 1px solid var(--gris-borde);
    font-size: 14px;
}
.perfil-row:last-child { border-bottom: none; }
.perfil-row .label { color: var(--gris-claro-texto); font-weight: 600; }
.perfil-row .value { color: var(--azul-principal); font-weight: 700; }
.btn-confirmar {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 13px; background: var(--accent-cta); color: var(--blanco);
    border: none; border-radius: 8px; font-size: 15px; font-weight: 800;
    cursor: pointer; font-family: inherit; transition: background 0.2s;
    margin-bottom: 10px; text-decoration: none;
}
.btn-confirmar:hover { background: var(--accent-cta-hover); color: var(--blanco); }
.btn-otros {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 11px; background: var(--gris-fondo);
    border: 1.5px solid var(--gris-borde); border-radius: 8px;
    font-size: 13px; font-weight: 700; color: var(--azul-principal);
    text-decoration: none; transition: all 0.2s;
}
.btn-otros:hover { background: var(--azul-claro); border-color: var(--azul-principal); color: var(--azul-principal); }
</style>
@endpush

@section('content')

{{-- BREADCRUMB --}}
<div style="background:var(--blanco);border-bottom:1px solid var(--gris-borde);padding:14px 0;">
    <div class="container">
        <nav style="display:flex;align-items:center;gap:8px;font-size:13px;flex-wrap:wrap;">
            <a href="{{ route('index') }}" style="color:var(--gris-claro-texto);text-decoration:none;font-weight:600;">Inicio</a>
            <span style="color:var(--gris-borde);">/</span>
            <a href="{{ route('cart-details') }}" style="color:var(--gris-claro-texto);text-decoration:none;font-weight:600;">Carrito</a>
            <span style="color:var(--gris-borde);">/</span>
            <span style="color:var(--azul-principal);font-weight:700;">Confirmar datos fiscales</span>
        </nav>
    </div>
</div>

{{-- HEADER --}}
<section style="background:linear-gradient(135deg,var(--azul-oscuro) 0%,var(--azul-principal) 60%,var(--azul-medio) 100%);color:var(--blanco);padding:40px 0;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.04) 1px,transparent 1px);background-size:50px 50px;"></div>
    <div class="container" style="position:relative;z-index:2;text-align:center;">
        <div style="display:inline-block;background:rgba(246,173,28,0.18);border:1px solid rgba(246,173,28,0.45);color:#F6AD1C;font-size:12px;font-weight:800;padding:7px 14px;border-radius:4px;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px;">
            <i class="fas fa-check-circle" style="margin-right:6px;"></i> Datos ya registrados
        </div>
        <h1 style="font-size:28px;font-weight:800;color:var(--blanco);margin-bottom:10px;letter-spacing:-0.4px;">
            ¿Usamos tus datos fiscales registrados?
        </h1>
        <p style="font-size:15px;opacity:0.9;max-width:520px;margin:0 auto;">
            Encontramos un perfil fiscal guardado en tu cuenta. Puedes usarlo o ingresar datos diferentes.
        </p>
    </div>
</section>

<section class="cot-page">
    <div class="container">
        <div class="cot-card">
            <h2>
                <i class="fas fa-id-card" style="color:var(--accent-cta);margin-right:8px;"></i>
                Tus datos fiscales registrados
            </h2>
            <p class="cot-subtitle">Verifica que la información sea correcta antes de generar la cotización.</p>

            <div class="perfil-summary">
                <div class="perfil-row">
                    <span class="label">Nombre</span>
                    <span class="value">{{ auth()->user()->name }}</span>
                </div>
                <div class="perfil-row">
                    <span class="label">RFC</span>
                    <span class="value">{{ $perfil->rfc }}</span>
                </div>
                <div class="perfil-row">
                    <span class="label">Tipo de persona</span>
                    <span class="value">{{ $perfil->tipo_persona === 'empresa' ? 'Persona Moral (Empresa)' : 'Persona Física' }}</span>
                </div>
                @if($perfil->tipo_persona === 'empresa' && $perfil->razon_social)
                <div class="perfil-row">
                    <span class="label">Razón Social</span>
                    <span class="value">{{ $perfil->razon_social }}</span>
                </div>
                @endif
                <div class="perfil-row">
                    <span class="label">Dirección fiscal</span>
                    <span class="value" style="text-align:right;max-width:340px;">{{ $perfil->direccion_fiscal }}</span>
                </div>
            </div>

            <form action="{{ route('cotizacion.confirmar') }}" method="POST">
                @csrf
                <button type="submit" class="btn-confirmar">
                    <i class="fas fa-file-pdf"></i>
                    Sí, usar estos datos y generar cotización
                </button>
            </form>

            <a href="{{ route('cotizacion.formulario', ['nuevo' => 1]) }}" class="btn-otros">
                <i class="fas fa-edit"></i>
                Usar otros datos fiscales
            </a>
        </div>
    </div>
</section>

@endsection

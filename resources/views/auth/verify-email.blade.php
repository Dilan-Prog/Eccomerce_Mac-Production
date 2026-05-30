@extends('layouts.guest')

@section('title', 'Verificar Correo')

@section('content')
<div class="auth-centered">
    <div class="auth-card">

        <div class="auth-card__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
        </div>

        <h1 class="auth-card__title">Verifica tu correo</h1>
        <p class="auth-card__desc">
            ¡Gracias por registrarte! Antes de continuar, revisa tu correo y haz clic en el enlace de verificación que te enviamos. Si no lo recibes, podemos enviarte uno nuevo.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="status-banner" role="status">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                Se ha enviado un nuevo enlace de verificación a tu correo.
            </div>
        @endif

        <div style="display:flex; flex-direction:column; gap: 12px; margin-top: 8px;">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    Reenviar correo de verificación
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary" style="width:100%; justify-content:center;">
                    Cerrar sesión
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

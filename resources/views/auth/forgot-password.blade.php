@extends('layouts.guest')

@section('title', 'Recuperar Contraseña')

@section('content')
<div class="auth-centered">
    <div class="auth-card">

        {{-- Icono --}}
        <div class="auth-card__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </div>

        <h1 class="auth-card__title">Recuperar contraseña</h1>

        <p class="auth-card__desc">
            Ingresa tu correo electrónico registrado y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        {{-- Status de éxito --}}
        @if (session('status'))
            <div class="status-banner" role="status">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                {{ session('status') }}
            </div>
        @endif

        {{-- Errores --}}
        @if ($errors->any())
            <div class="form-errors-banner" role="alert">
                <div class="form-errors-banner__title">Por favor corrige lo siguiente:</div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">
                    Correo electrónico <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="form-input-wrap">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="form-input @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="tu@correo.com"
                        required
                        autocomplete="username"
                        autofocus
                    >
                </div>
                @error('email')
                    <span class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                Enviar enlace de recuperación
            </button>
        </form>

        <div style="text-align:center; margin-top: 20px;">
            <a href="{{ route('login') }}" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Volver a iniciar sesión
            </a>
        </div>

    </div>
</div>
@endsection

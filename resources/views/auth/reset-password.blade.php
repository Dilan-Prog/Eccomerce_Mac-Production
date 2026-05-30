@extends('layouts.guest')

@section('title', 'Restablecer Contraseña')

@section('content')
<div class="auth-centered">
    <div class="auth-card">

        <div class="auth-card__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
        </div>

        <h1 class="auth-card__title">Nueva contraseña</h1>
        <p class="auth-card__desc">Elige una contraseña segura para tu cuenta.</p>

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

        <form method="POST" action="{{ route('password.store') }}" novalidate>
            @csrf

            {{-- Token oculto --}}
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                        value="{{ old('email', $request->email) }}"
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

            <div class="form-group">
                <label for="password" class="form-label">
                    Nueva contraseña <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="form-input-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input form-input--icon-right @error('password') is-invalid @enderror"
                        placeholder="Mínimo 8 caracteres"
                        required
                        autocomplete="new-password"
                        data-strength
                    >
                    <button type="button" class="input-icon-btn" data-toggle-password aria-label="Mostrar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="password-strength__bars" aria-hidden="true">
                        <div class="password-strength__bar"></div>
                        <div class="password-strength__bar"></div>
                        <div class="password-strength__bar"></div>
                    </div>
                    <span class="password-strength__label" aria-live="polite"></span>
                </div>
                @error('password')
                    <span class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">
                    Confirmar contraseña <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="form-input-wrap">
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-input form-input--icon-right"
                        placeholder="Repite tu nueva contraseña"
                        required
                        autocomplete="new-password"
                    >
                    <button type="button" class="input-icon-btn" data-toggle-password aria-label="Mostrar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Restablecer contraseña
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </form>

    </div>
</div>
@endsection

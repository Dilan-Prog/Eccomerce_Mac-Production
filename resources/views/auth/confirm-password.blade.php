@extends('layouts.guest')

@section('title', 'Confirmar Contraseña')

@section('content')
<div class="auth-centered">
    <div class="auth-card">

        <div class="auth-card__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        </div>

        <h1 class="auth-card__title">Zona segura</h1>
        <p class="auth-card__desc">
            Esta es un área protegida. Por favor confirma tu contraseña antes de continuar.
        </p>

        @if ($errors->any())
            <div class="form-errors-banner" role="alert">
                <div class="form-errors-banner__title">Contraseña incorrecta:</div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.confirm') }}" novalidate>
            @csrf

            <div class="form-group">
                <label for="password" class="form-label">
                    Contraseña <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="form-input-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input form-input--icon-right @error('password') is-invalid @enderror"
                        placeholder="Tu contraseña actual"
                        required
                        autocomplete="current-password"
                        autofocus
                    >
                    <button type="button" class="input-icon-btn" data-toggle-password aria-label="Mostrar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                @error('password')
                    <span class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-submit">
                Confirmar acceso
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </form>

    </div>
</div>
@endsection

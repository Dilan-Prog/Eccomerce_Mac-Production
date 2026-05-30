@extends('frontend.layouts.master')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="auth-wrapper">
    <div class="auth-grid">

        {{-- ────────────────────────────────────────────
             PANEL IZQUIERDO: Formulario de Login
        ──────────────────────────────────────────── --}}
        <div class="auth-form-panel">
            <div class="auth-form-inner">
                <h1 class="auth-heading">Bienvenido de vuelta</h1>
                <p class="auth-subheading">Inicia sesión en tu cuenta para continuar</p>

                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="form-errors-banner" role="alert">
                        <div class="form-errors-banner__title">Revisa los siguientes campos:</div>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Botones Social Login (solo UI) --}}
                <div class="social-buttons">
                    <button type="button" class="social-btn" aria-label="Continuar con Google">
                        <svg class="social-btn__icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Google
                    </button>
                    <button type="button" class="social-btn" aria-label="Continuar con Facebook">
                        <svg class="social-btn__icon" viewBox="0 0 24 24" fill="#1877F2" aria-hidden="true">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        Facebook
                    </button>
                </div>

                <div class="auth-divider"><span class="auth-divider__text">O continúa con email </span></div>

                {{-- Formulario de Login --}}
                <form id="login-form" method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    {{-- Email --}}
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

                    {{-- Contraseña --}}
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
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            >
                            <button
                                type="button"
                                class="input-icon-btn"
                                data-toggle-password
                                aria-label="Mostrar contraseña"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="form-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Recuérdame + Olvidé mi contraseña --}}
                    <div class="form-extras">
                        <label class="checkbox-wrap" for="remember_me">
                            <input type="checkbox" id="remember_me" name="remember">
                            <span class="checkbox-label">Recuérdame</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn-submit">
                        Iniciar Sesión
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </form>

                <p class="auth-switch-link">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}">Regístrate gratis</a>
                </p>

            </div>
        </div>

        {{-- ────────────────────────────────────────────
             PANEL DERECHO: Sidebar de beneficios
        ──────────────────────────────────────────── --}}
        <aside class="auth-sidebar" aria-label="Beneficios Mac Del Norte">
            <div class="auth-sidebar__content">
                <h2 class="auth-sidebar__headline">Tu proveedor industrial<br>de confianza</h2>
                <p class="auth-sidebar__sub">Más de 20 años llevando tecnología Honeywell a la industria del noroeste de México.</p>

                <ul class="benefits-list">
                    <li class="benefits-list__item">
                        <div class="benefits-list__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                        </div>
                        <div class="benefits-list__text">
                            <span class="benefits-list__title">Productos originales garantizados</span>
                            <span class="benefits-list__desc">Distribuidor oficial Honeywell — 100% producto auténtico</span>
                        </div>
                    </li>
                    <li class="benefits-list__item">
                        <div class="benefits-list__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div class="benefits-list__text">
                            <span class="benefits-list__title">Entrega rápida en el noroeste</span>
                            <span class="benefits-list__desc">Envíos a Sonora, Sinaloa, Chihuahua y Baja California</span>
                        </div>
                    </li>
                    <li class="benefits-list__item">
                        <div class="benefits-list__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div class="benefits-list__text">
                            <span class="benefits-list__title">Soporte técnico especializado</span>
                            <span class="benefits-list__desc">Ingenieros certificados disponibles para tu proyecto</span>
                        </div>
                    </li>
                    <li class="benefits-list__item">
                        <div class="benefits-list__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                        <div class="benefits-list__text">
                            <span class="benefits-list__title">Crédito empresarial B2B</span>
                            <span class="benefits-list__desc">Líneas de crédito y precios especiales para empresas</span>
                        </div>
                    </li>
                </ul>

                <div class="trust-badges">
                    <div class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Compra segura
                    </div>
                    <div class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                        +5,000 productos
                    </div>
                    <div class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        +20 años
                    </div>
                    <div class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        Garantía oficial
                    </div>
                </div>

            </div>
        </aside>

    </div>
</div>
@endsection

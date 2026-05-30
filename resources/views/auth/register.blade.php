@extends('layouts.guest')

@section('title', 'Crear Cuenta')

@section('content')

{{-- ══════════════════════════════════════════════════════
     HERO SECTION
══════════════════════════════════════════════════════ --}}
<section class="page-hero">
    <div class="page-hero-content">
        <div class="page-eyebrow">Únete a Mac Del Norte</div>
        <h1>Crea tu cuenta y compra <span class="accent">5x más rápido</span></h1>
        <p class="page-hero-sub">Regístrate gratis en menos de 1 minuto. Si eres revendedor o técnico, accede a precios B2B especiales.</p>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════
     SELECTOR TIPO DE CUENTA
══════════════════════════════════════════════════════ --}}
<section class="account-type-section">
    <div class="container">
        <div class="account-type-grid">

            <div class="account-type-card active" id="card-personal" onclick="switchAccount('personal')" role="button" tabindex="0" aria-pressed="true">
                <div class="account-type-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <h3>Cuenta personal</h3>
                <p>Ideal para clientes finales y técnicos que compran ocasionalmente.</p>
                <ul class="account-type-perks">
                    <li>Registro en 30 segundos</li>
                    <li>Precios públicos y ofertas regulares</li>
                    <li>Historial de pedidos guardado</li>
                    <li>Activación inmediata</li>
                </ul>
            </div>

            <div class="account-type-card" id="card-b2b" onclick="switchAccount('b2b')" role="button" tabindex="0" aria-pressed="false">
                <span class="badge-best">⭐ Más popular</span>
                <div class="account-type-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10z"/></svg>
                </div>
                <h3>Cuenta B2B / Revendedor</h3>
                <p>Para revendedores, distribuidores y técnicos que compran volumen o revenden.</p>
                <ul class="account-type-perks">
                    <li><strong>+15% descuento promedio</strong></li>
                    <li>Crédito a 30 días disponible</li>
                    <li>Ingeniero de aplicación asignado</li>
                    <li>Validación en 24–48 h hábiles</li>
                </ul>
            </div>

        </div>

        {{-- Radio inputs semánticos (ocultos) --}}
        <input type="radio" id="type_personal" name="_account_type_select" value="personal" style="display:none" checked>
        <input type="radio" id="type_b2b"      name="_account_type_select" value="b2b"      style="display:none">
    </div>
</section>

{{-- ══════════════════════════════════════════════════════
     FORMULARIO
══════════════════════════════════════════════════════ --}}
<section class="form-section">
    <div class="container">
        <div class="form-grid">

            {{-- ── FORM CARD ── --}}
            <div class="form-card" id="form-container">

                <div class="form-card-header">
                    <h2 id="form-title">Datos de tu cuenta</h2>
                    <p id="form-subtitle">Llena los siguientes campos para crear tu cuenta</p>
                    <div class="progress-indicator">
                        <span id="progress-text">Paso 1 de 1 · 30 segundos</span>
                        <div class="progress-bar-wrap">
                            <div class="progress-fill" id="progress-fill"></div>
                        </div>
                    </div>
                </div>

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

                {{-- Social login (solo UI) --}}
                <div class="social-buttons">
                    <button type="button" class="social-btn" aria-label="Continuar con Google">
                        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Google
                    </button>
                    <button type="button" class="social-btn" aria-label="Continuar con Facebook">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="#1877F2" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </button>
                </div>

                <div class="auth-divider"><span>O regístrate con tu email</span></div>

                {{-- ── FORM ── --}}
                <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    {{-- Campo oculto: tipo de cuenta --}}
                    <input type="hidden" name="account_type" id="account_type_value" value="personal">

                    {{-- Nombre y Email --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">
                                Nombre completo <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                class="form-input @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="Ej. Roberto Martínez"
                                required
                                autocomplete="name"
                                autofocus
                            >
                            @error('name')
                                <span class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">
                                Teléfono <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="phone"
                                type="tel"
                                name="phone"
                                class="form-input @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}"
                                placeholder="Ej. 81 1234 5678"
                                autocomplete="tel"
                            >
                            <div class="form-help">A 10 dígitos · Solo para temas de tu pedido</div>
                            @error('phone')
                                <span class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            Correo electrónico <span class="required" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="form-input @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="tu@correo.com"
                            required
                            autocomplete="username"
                        >
                        <div class="form-help">Lo usaremos para enviarte tu confirmación de pedido y facturas</div>
                        @error('email')
                            <span class="form-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Contraseñas --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">
                                Contraseña <span class="required" aria-hidden="true">*</span>
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
                            <div class="password-strength" aria-hidden="true">
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                            </div>
                            <div class="form-help">Usa al menos 8 caracteres con números y símbolos</div>
                            @error('password')
                                <span class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">
                                Confirmar contraseña <span class="required" aria-hidden="true">*</span>
                            </label>
                            <div class="form-input-wrap">
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    class="form-input form-input--icon-right"
                                    placeholder="Repite tu contraseña"
                                    required
                                    autocomplete="new-password"
                                >
                                <button type="button" class="input-icon-btn" data-toggle-password aria-label="Mostrar contraseña">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ══ CAMPOS B2B (ocultos por defecto, visibles con .b2b-active en form-container) ══ --}}
                    <div class="b2b-fields">

                        <div class="form-divider"><span>Datos de tu empresa</span></div>

                        <div class="form-group">
                            <label for="empresa">
                                Nombre de empresa o razón social <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="empresa"
                                type="text"
                                name="empresa"
                                class="form-input @error('empresa') is-invalid @enderror"
                                value="{{ old('empresa') }}"
                                placeholder="Ej. Industrias del Norte S.A. de C.V."
                                autocomplete="organization"
                            >
                            @error('empresa')
                                <span class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rfc">
                                    RFC <span class="required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    id="rfc"
                                    type="text"
                                    name="rfc"
                                    class="form-input @error('rfc') is-invalid @enderror"
                                    value="{{ old('rfc') }}"
                                    placeholder="ABC123456XYZ"
                                    maxlength="13"
                                    style="text-transform:uppercase"
                                >
                                <div class="form-help">13 caracteres · Validamos con SAT</div>
                                @error('rfc')
                                    <span class="form-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="tipo_cliente">
                                    Tipo de cliente <span class="required" aria-hidden="true">*</span>
                                </label>
                                <select id="tipo_cliente" name="tipo_cliente" class="form-select">
                                    <option value="">Selecciona una opción</option>
                                    <option value="revendedor"   {{ old('tipo_cliente') === 'revendedor'   ? 'selected' : '' }}>Revendedor / Distribuidor</option>
                                    <option value="tecnico"      {{ old('tipo_cliente') === 'tecnico'      ? 'selected' : '' }}>Técnico independiente</option>
                                    <option value="empresa"      {{ old('tipo_cliente') === 'empresa'      ? 'selected' : '' }}>Empresa / Planta industrial</option>
                                    <option value="contratista"  {{ old('tipo_cliente') === 'contratista'  ? 'selected' : '' }}>Contratista / Integrador</option>
                                </select>
                                @error('tipo_cliente')
                                    <span class="form-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Upload CSF --}}
                        <div class="form-group">
                            <label>
                                Constancia de Situación Fiscal
                                <span class="optional">(recomendado)</span>
                            </label>

                            <div class="file-upload-wrapper">
                                <div class="file-upload-area" id="csf-upload">
                                    <input
                                        type="file"
                                        name="constancia_fiscal"
                                        id="csf-input"
                                        accept="application/pdf,.pdf"
                                        class="file-upload-input"
                                        aria-label="Subir Constancia de Situación Fiscal en PDF"
                                    >

                                    {{-- Estado: vacío --}}
                                    <div class="file-upload-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    </div>
                                    <div class="file-upload-title">
                                        Arrastra tu CSF aquí o <strong>selecciona un archivo</strong>
                                    </div>
                                    <div class="file-upload-subtitle">
                                        Sube tu Constancia de Situación Fiscal actualizada
                                    </div>
                                    <div class="file-upload-formats">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>
                                        Solo PDF · Máx. 5 MB
                                    </div>

                                    {{-- Estado: con archivo --}}
                                    <div class="file-preview">
                                        <div class="file-preview-icon" aria-hidden="true">PDF</div>
                                        <div class="file-preview-info">
                                            <div class="file-preview-name" id="file-name">archivo.pdf</div>
                                            <div class="file-preview-size">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                                <span id="file-size">0 KB</span> · Listo para enviar
                                            </div>
                                        </div>
                                        <div class="file-preview-actions">
                                            <button type="button" class="file-preview-btn" title="Eliminar archivo" onclick="removeFile()" aria-label="Eliminar archivo">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="file-help-secure">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                                <span>
                                    <strong>Subir tu CSF acelera tu validación de 48 h a 24 h.</strong>
                                    Tu documento se almacena cifrado. Obtenla en
                                    <a href="https://www.sat.gob.mx" target="_blank" rel="noopener" style="color:var(--azul-medio);font-weight:700;">sat.gob.mx</a>.
                                </span>
                            </div>

                            @error('constancia_fiscal')
                                <span class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="giro_industrial">
                                    Giro industrial <span class="optional">(opcional)</span>
                                </label>
                                <select id="giro_industrial" name="giro_industrial" class="form-select">
                                    <option value="">Selecciona</option>
                                    <option value="alimentaria"   {{ old('giro_industrial') === 'alimentaria'   ? 'selected' : '' }}>Alimentaria</option>
                                    <option value="petroquimica"  {{ old('giro_industrial') === 'petroquimica'  ? 'selected' : '' }}>Petroquímica</option>
                                    <option value="farmaceutica"  {{ old('giro_industrial') === 'farmaceutica'  ? 'selected' : '' }}>Farmacéutica</option>
                                    <option value="automotriz"    {{ old('giro_industrial') === 'automotriz'    ? 'selected' : '' }}>Automotriz</option>
                                    <option value="energia"       {{ old('giro_industrial') === 'energia'       ? 'selected' : '' }}>Energía / Eléctrica</option>
                                    <option value="calderas"      {{ old('giro_industrial') === 'calderas'      ? 'selected' : '' }}>Calderas / Vapor</option>
                                    <option value="otro"          {{ old('giro_industrial') === 'otro'          ? 'selected' : '' }}>Otro</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="volumen_mensual">
                                    Volumen estimado mensual <span class="optional">(opcional)</span>
                                </label>
                                <select id="volumen_mensual" name="volumen_mensual" class="form-select">
                                    <option value="">Selecciona</option>
                                    <option value="menos_20k"    {{ old('volumen_mensual') === 'menos_20k'    ? 'selected' : '' }}>Menos de $20,000 MXN</option>
                                    <option value="20k_100k"     {{ old('volumen_mensual') === '20k_100k'     ? 'selected' : '' }}>$20,000 – $100,000 MXN</option>
                                    <option value="100k_500k"    {{ old('volumen_mensual') === '100k_500k'    ? 'selected' : '' }}>$100,000 – $500,000 MXN</option>
                                    <option value="mas_500k"     {{ old('volumen_mensual') === 'mas_500k'     ? 'selected' : '' }}>Más de $500,000 MXN</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ciudad">
                                Ciudad <span class="required" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="ciudad"
                                type="text"
                                name="ciudad"
                                class="form-input @error('ciudad') is-invalid @enderror"
                                value="{{ old('ciudad') }}"
                                placeholder="Ej. Monterrey, N.L."
                            >
                            @error('ciudad')
                                <span class="form-error" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                    {{-- ══ FIN CAMPOS B2B ══ --}}

                    {{-- Checkboxes --}}
                    <div class="form-checkbox">
                        <input type="checkbox" id="terms" name="terms" required {{ old('terms') ? 'checked' : '' }}>
                        <label for="terms">
                            Acepto los <a href="#" target="_blank" rel="noopener">términos y condiciones</a>
                            y el <a href="#" target="_blank" rel="noopener">aviso de privacidad</a>
                            de Mac Del Norte. <span class="required" aria-hidden="true">*</span>
                        </label>
                    </div>
                    @error('terms')
                        <span class="form-error" role="alert">{{ $message }}</span>
                    @enderror

                    <div class="form-checkbox">
                        <input type="checkbox" id="newsletter" name="newsletter" value="1" {{ old('newsletter', '1') ? 'checked' : '' }}>
                        <label for="newsletter">
                            Quiero recibir ofertas y novedades sobre productos Honeywell, Resideo y McDonnell &amp; Miller por correo.
                        </label>
                    </div>

                    <button type="submit" class="btn-submit">
                        <span id="submit-text">Crear mi cuenta gratis</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                </form>

                <div class="login-link">
                    ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a>
                </div>

            </div>{{-- /form-card --}}

            {{-- ── BENEFITS SIDEBAR ── --}}
            <aside class="benefits-sidebar" aria-label="Beneficios de registrarse">

                <div class="benefits-card">
                    <div class="benefits-card-inner">
                        <div class="benefits-eyebrow">¿Qué obtienes al registrarte?</div>
                        <h3>5 beneficios concretos para ti</h3>
                        <ul class="benefits-list">
                            <li>
                                <div class="benefit-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                                </div>
                                <div class="benefit-text">
                                    <strong>Compra 5× más rápido</strong>
                                    <span>Datos fiscales y direcciones guardadas</span>
                                </div>
                            </li>
                            <li>
                                <div class="benefit-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 8C8 10 5.9 16.17 3.82 19.34L5.71 21l1-1.06C8.28 21.13 10.16 22 12 22c5.52 0 10-4.48 10-10 0-1.81-.49-3.5-1.34-4.96L17 8z"/></svg>
                                </div>
                                <div class="benefit-text">
                                    <strong>Reorden en 1 clic</strong>
                                    <span>Repite pedidos de refacciones recurrentes</span>
                                </div>
                            </li>
                            <li>
                                <div class="benefit-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6z"/></svg>
                                </div>
                                <div class="benefit-text">
                                    <strong>Cotizaciones formales en PDF</strong>
                                    <span>Listas para tu orden de compra interna</span>
                                </div>
                            </li>
                            <li>
                                <div class="benefit-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z"/></svg>
                                </div>
                                <div class="benefit-text">
                                    <strong>Precios B2B exclusivos</strong>
                                    <span>Si eres revendedor, +15% de descuento</span>
                                </div>
                            </li>
                            <li>
                                <div class="benefit-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 4.5h-17C2.67 4.5 2 5.17 2 6v12c0 .83.67 1.5 1.5 1.5h17c.83 0 1.5-.67 1.5-1.5V6c0-.83-.67-1.5-1.5-1.5z"/></svg>
                                </div>
                                <div class="benefit-text">
                                    <strong>Soporte de ingenieros</strong>
                                    <span>Atención técnica cuando la necesites</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="trust-card">
                    <div class="trust-title">100% Seguro</div>
                    <div class="trust-items">
                        <div class="trust-item-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                            <span>SSL Seguro</span>
                        </div>
                        <div class="trust-item-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                            <span>Datos cifrados</span>
                        </div>
                        <div class="trust-item-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                            <span>SAT validado</span>
                        </div>
                        <div class="trust-item-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            <span>Sin spam</span>
                        </div>
                    </div>
                </div>

            </aside>

        </div>{{-- /form-grid --}}
    </div>{{-- /container --}}
</section>

{{-- Sincroniza el valor del radio oculto con el hidden input del form --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var radios = document.querySelectorAll('[name="_account_type_select"]');
    var hidden = document.getElementById('account_type_value');
    radios.forEach(function (r) {
        r.addEventListener('change', function () {
            if (hidden) hidden.value = r.value;
        });
    });
    // Default
    var checkedRadio = document.querySelector('[name="_account_type_select"]:checked');
    if (hidden && checkedRadio) hidden.value = checkedRadio.value;
});
</script>

@endsection

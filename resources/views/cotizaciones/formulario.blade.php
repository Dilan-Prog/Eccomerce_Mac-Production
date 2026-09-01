@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} || Cotización Formal
@endsection

@push('styles')
<style>
.cot-page { padding: 32px 0 64px; background: var(--gris-fondo); }
.cot-card {
    background: var(--blanco); border-radius: 12px;
    border: 1px solid var(--gris-borde); padding: 36px 40px;
    max-width: 720px; margin: 0 auto;
}
.cot-card h2 {
    font-size: 22px; font-weight: 800; color: var(--azul-principal);
    margin-bottom: 6px;
}
.cot-card .cot-subtitle {
    font-size: 14px; color: var(--gris-claro-texto); margin-bottom: 28px;
}
.form-group { margin-bottom: 18px; }
.form-group label {
    display: block; font-size: 13px; font-weight: 700;
    color: var(--azul-principal); margin-bottom: 6px;
}
.form-group label span.req { color: var(--accent-cta); margin-left: 2px; }
.form-input, .form-select, .form-textarea {
    width: 100%; padding: 11px 14px;
    border: 1.5px solid var(--gris-borde); border-radius: 7px;
    font-size: 14px; font-family: inherit; background: var(--blanco);
    transition: border-color 0.2s, box-shadow 0.2s; color: var(--negro-texto);
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none; border-color: var(--azul-principal);
    box-shadow: 0 0 0 3px rgba(0,62,126,0.1);
}
.form-input:disabled, .form-input[readonly] {
    background: var(--gris-fondo); color: var(--gris-claro-texto); cursor: not-allowed;
}
.form-textarea { resize: vertical; min-height: 88px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-error { font-size: 12px; color: #E53E3E; margin-top: 4px; display: block; }
.form-section-divider {
    border: none; border-top: 1px solid var(--gris-borde);
    margin: 28px 0 22px;
}
.cot-tipo-toggle {
    display: flex; gap: 0; border: 1.5px solid var(--gris-borde);
    border-radius: 7px; overflow: hidden; margin-bottom: 20px;
}
.cot-tipo-btn {
    flex: 1; padding: 10px; border: none; background: var(--gris-fondo);
    font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s;
    color: var(--gris-texto); font-family: inherit;
}
.cot-tipo-btn.active {
    background: var(--azul-principal); color: var(--blanco);
}
.hidden { display: none !important; }
.file-label {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px; border: 1.5px dashed var(--gris-borde);
    border-radius: 7px; cursor: pointer; transition: border-color 0.2s;
    font-size: 14px; color: var(--gris-claro-texto);
}
.file-label:hover { border-color: var(--azul-principal); color: var(--azul-principal); }
.file-label input[type=file] { display: none; }
.file-name { font-size: 13px; font-weight: 600; color: var(--azul-principal); }
.btn-cot-submit {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 14px; background: var(--accent-cta); color: var(--blanco);
    border: none; border-radius: 8px; font-size: 15px; font-weight: 800;
    cursor: pointer; font-family: inherit; transition: background 0.2s; margin-top: 8px;
}
.btn-cot-submit:hover { background: var(--accent-cta-hover); }
@media (max-width: 600px) {
    .cot-card { padding: 24px 18px; }
    .form-row { grid-template-columns: 1fr; }
}

.prefilled-banner {
    display: flex; align-items: flex-start; gap: 12px;
    background: var(--azul-claro); border: 1px solid var(--gris-borde);
    border-left: 4px solid var(--azul-principal); border-radius: 8px;
    padding: 14px 16px; margin-bottom: 24px; font-size: 13px; color: var(--azul-principal);
}
.prefilled-banner i { margin-top: 2px; }
.prefilled-banner a { color: var(--accent-cta); font-weight: 700; text-decoration: none; }
.label-from-profile {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; color: var(--azul-principal);
    background: var(--azul-claro); border: 1px solid var(--gris-borde);
    border-radius: 4px; padding: 2px 6px; margin-left: 8px; text-transform: none;
}
.cot-tipo-toggle--readonly .cot-tipo-btn { cursor: not-allowed; opacity: 0.85; }
.form-help { font-size: 12px; color: var(--gris-claro-texto); margin-top: 6px; }
.csf-preview-card {
    display: flex; align-items: center; gap: 12px;
    background: var(--gris-fondo); border: 1px solid var(--gris-borde);
    border-radius: 8px; padding: 12px 16px;
}
.csf-preview-icon {
    width: 40px; height: 40px; background: var(--accent-cta); color: var(--blanco);
    border-radius: 6px; display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 800; flex-shrink: 0;
}
.csf-preview-info { flex: 1; min-width: 0; }
.csf-preview-name {
    font-size: 13px; font-weight: 700; color: var(--negro-texto);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.csf-preview-status {
    font-size: 12px; color: var(--gris-claro-texto);
    display: flex; align-items: center; gap: 4px; margin-top: 2px;
}
.csf-preview-status i { color: #16A34A; }
.csf-preview-change-btn {
    font-size: 12px; color: var(--azul-principal); background: var(--blanco);
    border: 1.5px solid var(--gris-borde); border-radius: 6px; padding: 6px 12px;
    cursor: pointer; white-space: nowrap; font-family: inherit; flex-shrink: 0;
}
.csf-preview-change-btn:hover { border-color: var(--azul-principal); }
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
            <span style="color:var(--azul-principal);font-weight:700;">Cotización Formal</span>
        </nav>
    </div>
</div>

{{-- HEADER --}}
<section style="background:linear-gradient(135deg,var(--azul-oscuro) 0%,var(--azul-principal) 60%,var(--azul-medio) 100%);color:var(--blanco);padding:40px 0;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.04) 1px,transparent 1px);background-size:50px 50px;"></div>
    <div class="container" style="position:relative;z-index:2;text-align:center;">
        <div style="display:inline-block;background:rgba(246,173,28,0.18);border:1px solid rgba(246,173,28,0.45);color:#F6AD1C;font-size:12px;font-weight:800;padding:7px 14px;border-radius:4px;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px;">
            <i class="fas fa-file-invoice" style="margin-right:6px;"></i> Documentación fiscal
        </div>
        <h1 style="font-size:28px;font-weight:800;color:var(--blanco);margin-bottom:10px;letter-spacing:-0.4px;">
            Genera tu Cotización Formal
        </h1>
        <p style="font-size:15px;opacity:0.9;max-width:520px;margin:0 auto;">
            Completa tus datos fiscales para recibir una cotización oficial con validez comercial.
        </p>
    </div>
</section>

<section class="cot-page">
    <div class="container">
        <div class="cot-card">
            <h2><i class="fas fa-id-card" style="color:var(--accent-cta);margin-right:8px;"></i>Datos fiscales</h2>
            <p class="cot-subtitle">Estos datos aparecerán en el PDF de cotización. Los campos con <span style="color:var(--accent-cta);">*</span> son obligatorios.</p>

            @if(session('error'))
                <div style="background:#FEF2F2;border:1px solid #E53E3E;border-radius:8px;padding:14px 18px;margin-bottom:20px;color:#E53E3E;font-size:13px;font-weight:600;">
                    <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>{{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:#FEF2F2;border:1px solid #E53E3E;border-radius:8px;padding:14px 18px;margin-bottom:20px;">
                    <ul style="margin:0;padding-left:18px;color:#E53E3E;font-size:13px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $tipoPersonaFijo = $user->rfc ? (strlen($user->rfc) === 12 ? 'empresa' : 'fisica') : null;
                $tipoPersonaActual = $tipoPersonaFijo ?? old('tipo_persona', 'empresa');
                $tieneCIF = (bool) $user->csf_path;
            @endphp

            <div class="prefilled-banner">
                <i class="fas fa-user-check"></i>
                <div>
                    <strong>Tus datos fiscales fueron pre-llenados desde tu perfil.</strong>
                    Solo completa la dirección fiscal para generar tu cotización.
                    <a href="{{ route('profile.edit') }}">¿Datos incorrectos? Edítalos aquí →</a>
                </div>
            </div>

            <form action="{{ route('cotizacion.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- DATOS DE CUENTA (no editables) --}}
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre completo <span class="label-from-profile"><i class="fas fa-lock"></i> Del perfil</span></label>
                        <input type="text" class="form-input" value="{{ $user->name }} {{ $user->last_name }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Correo electrónico <span class="label-from-profile"><i class="fas fa-lock"></i> Del perfil</span></label>
                        <input type="email" class="form-input" value="{{ $user->email }}" readonly>
                    </div>
                </div>

                {{-- TELÉFONO --}}
                @if($user->phone)
                    <div class="form-group">
                        <label>Teléfono <span class="label-from-profile"><i class="fas fa-lock"></i> Del perfil</span></label>
                        <input type="tel" class="form-input" value="{{ $user->phone }}" readonly>
                        <input type="hidden" name="telefono" value="{{ $user->phone }}">
                    </div>
                @else
                    <div class="form-group">
                        <label>Teléfono <span class="req">*</span></label>
                        <input type="tel" name="telefono" class="form-input"
                               value="{{ old('telefono') }}"
                               placeholder="81-3582-5559" required>
                        @error('telefono') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                @endif

                <hr class="form-section-divider">

                {{-- TIPO DE PERSONA --}}
                <div class="form-group">
                    <label>
                        Tipo de persona <span class="req">*</span>
                        @if($tipoPersonaFijo)
                            <span class="label-from-profile"><i class="fas fa-lock"></i> Auto por RFC</span>
                        @endif
                    </label>
                    <input type="hidden" name="tipo_persona" id="tipo_persona_input" value="{{ $tipoPersonaActual }}">

                    @if($tipoPersonaFijo)
                        <div class="cot-tipo-toggle cot-tipo-toggle--readonly">
                            <button type="button" class="cot-tipo-btn {{ $tipoPersonaFijo === 'empresa' ? 'active' : '' }}" disabled>
                                <i class="fas fa-building" style="margin-right:6px;"></i> Persona Moral (Empresa)
                            </button>
                            <button type="button" class="cot-tipo-btn {{ $tipoPersonaFijo === 'fisica' ? 'active' : '' }}" disabled>
                                <i class="fas fa-user" style="margin-right:6px;"></i> Persona Física
                            </button>
                        </div>
                        <div class="form-help">Determinado automáticamente por tu RFC registrado.</div>
                    @else
                        <div class="cot-tipo-toggle">
                            <button type="button" class="cot-tipo-btn {{ $tipoPersonaActual === 'empresa' ? 'active' : '' }}"
                                    data-tipo="empresa">
                                <i class="fas fa-building" style="margin-right:6px;"></i> Persona Moral (Empresa)
                            </button>
                            <button type="button" class="cot-tipo-btn {{ $tipoPersonaActual === 'fisica' ? 'active' : '' }}"
                                    data-tipo="fisica">
                                <i class="fas fa-user" style="margin-right:6px;"></i> Persona Física
                            </button>
                        </div>
                    @endif
                </div>

                {{-- RAZÓN SOCIAL --}}
                <div id="campos-empresa" class="{{ $tipoPersonaActual === 'fisica' ? 'hidden' : '' }}">
                    @if($user->company)
                        <div class="form-group">
                            <label>Razón Social <span class="label-from-profile"><i class="fas fa-lock"></i> Del perfil</span></label>
                            <input type="text" class="form-input" value="{{ $user->company }}" readonly>
                            <input type="hidden" name="razon_social" value="{{ $user->company }}">
                        </div>
                    @else
                        <div class="form-group">
                            <label>Razón Social <span class="req">*</span></label>
                            <input type="text" name="razon_social" class="form-input"
                                   value="{{ old('razon_social') }}"
                                   placeholder="Mac Del Norte S.A. de C.V.">
                            @error('razon_social') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                {{-- RFC --}}
                @if($user->rfc)
                    <div class="form-group">
                        <label>RFC <span class="label-from-profile"><i class="fas fa-lock"></i> Del perfil</span></label>
                        <input type="text" class="form-input" value="{{ $user->rfc }}" readonly>
                        <input type="hidden" name="rfc" value="{{ $user->rfc }}">
                    </div>
                @else
                    <div id="campos-empresa-rfc" class="{{ $tipoPersonaActual === 'fisica' ? 'hidden' : '' }}">
                        <div class="form-group">
                            <label>RFC (12 caracteres) <span class="req">*</span></label>
                            <input type="text" name="rfc" id="rfc_empresa" class="form-input"
                                   value="{{ $tipoPersonaActual !== 'fisica' ? old('rfc') : '' }}"
                                   placeholder="MDN200101ABC" maxlength="12"
                                   style="text-transform:uppercase;">
                            @error('rfc') <span class="form-error" id="rfc_error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div id="campos-fisica" class="{{ $tipoPersonaActual !== 'fisica' ? 'hidden' : '' }}">
                        <div class="form-group">
                            <label>RFC (13 caracteres) <span class="req">*</span></label>
                            <input type="text" name="rfc" id="rfc_fisica" class="form-input"
                                   value="{{ $tipoPersonaActual === 'fisica' ? old('rfc') : '' }}"
                                   placeholder="GOML850102ABC" maxlength="13"
                                   style="text-transform:uppercase;">
                        </div>
                    </div>
                @endif

                <hr class="form-section-divider">

                <div class="form-group">
                    <label for="direccion_fiscal">Dirección fiscal completa <span class="req">*</span></label>
                    @if($user->ciudad)
                        <div class="form-help" style="margin-bottom:8px;">
                            <i class="fas fa-map-marker-alt"></i> Ciudad de referencia registrada: <strong>{{ $user->ciudad }}</strong>
                        </div>
                    @endif
                    <textarea name="direccion_fiscal" id="direccion_fiscal" class="form-textarea"
                              placeholder="Calle, Número, Colonia, CP, Ciudad, Estado"
                              required>{{ old('direccion_fiscal') }}</textarea>
                    @error('direccion_fiscal') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                {{-- CIF --}}
                <div class="form-group">
                    <label>
                        CIF — Constancia de Situación Fiscal
                        @if($tieneCIF)
                            <span class="label-from-profile"><i class="fas fa-check"></i> Ya subida</span>
                        @else
                            <span class="req">*</span>
                        @endif
                        <span style="font-size:11px;font-weight:400;color:var(--gris-claro-texto);">(PDF, JPG o PNG · máx. 4 MB)</span>
                    </label>

                    @if($tieneCIF)
                        <div class="csf-preview-card">
                            <div class="csf-preview-icon">PDF</div>
                            <div class="csf-preview-info">
                                <div class="csf-preview-name">{{ basename($user->csf_path) }}</div>
                                <div class="csf-preview-status"><i class="fas fa-check-circle"></i> Archivo subido durante el registro</div>
                            </div>
                            <button type="button" class="csf-preview-change-btn" onclick="toggleCSFUpload()">Cambiar archivo</button>
                        </div>

                        <div id="csf-new-upload" class="hidden" style="margin-top:10px;">
                            <label class="file-label" for="cif_input">
                                <i class="fas fa-cloud-upload-alt" style="font-size:18px;flex-shrink:0;"></i>
                                <span id="cif_filename">Seleccionar nuevo archivo…</span>
                                <input type="file" id="cif_input" name="cif" accept=".pdf,.jpg,.jpeg,.png">
                            </label>
                            <div class="form-help">Si subes un nuevo archivo reemplazará al de tu perfil.</div>
                        </div>
                    @else
                        <label class="file-label" for="cif_input">
                            <i class="fas fa-cloud-upload-alt" style="font-size:18px;flex-shrink:0;"></i>
                            <span id="cif_filename">Seleccionar archivo…</span>
                            <input type="file" id="cif_input" name="cif" accept=".pdf,.jpg,.jpeg,.png" required>
                        </label>
                    @endif
                    @error('cif') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn-cot-submit">
                    <i class="fas fa-file-pdf"></i>
                    Generar Cotización y PDF
                </button>
            </form>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
function toggleCSFUpload() {
    var newUpload = document.getElementById('csf-new-upload');
    if (!newUpload) return;

    if (newUpload.classList.contains('hidden')) {
        newUpload.classList.remove('hidden');
    } else {
        newUpload.classList.add('hidden');
        var fileInput = document.getElementById('cif_input');
        if (fileInput) fileInput.value = '';
        var filenameSpan = document.getElementById('cif_filename');
        if (filenameSpan) filenameSpan.textContent = 'Seleccionar nuevo archivo…';
    }
}

(function () {
    var btns       = document.querySelectorAll('.cot-tipo-btn');
    var tipoInput  = document.getElementById('tipo_persona_input');
    var tipoLocked = {{ $tipoPersonaFijo ? 'true' : 'false' }};
    var emp        = document.getElementById('campos-empresa');
    var empRfc     = document.getElementById('campos-empresa-rfc');
    var fis        = document.getElementById('campos-fisica');
    var rfcEmp     = document.getElementById('rfc_empresa');
    var rfcFis     = document.getElementById('rfc_fisica');

    function activarTipo(tipo) {
        btns.forEach(function (b) { b.classList.remove('active'); });
        var activeBtn = document.querySelector('.cot-tipo-btn[data-tipo="' + tipo + '"]');
        if (activeBtn) activeBtn.classList.add('active');
        tipoInput.value = tipo;

        if (tipo === 'empresa') {
            if (emp) emp.classList.remove('hidden');
            if (empRfc) empRfc.classList.remove('hidden');
            if (fis) fis.classList.add('hidden');
            if (rfcEmp) rfcEmp.setAttribute('name', 'rfc');
            if (rfcFis) rfcFis.removeAttribute('name');
        } else {
            if (fis) fis.classList.remove('hidden');
            if (emp) emp.classList.add('hidden');
            if (empRfc) empRfc.classList.add('hidden');
            if (rfcFis) rfcFis.setAttribute('name', 'rfc');
            if (rfcEmp) rfcEmp.removeAttribute('name');
        }
    }

    if (!tipoLocked) {
        // Estado inicial según old() del servidor
        activarTipo(tipoInput.value || 'empresa');

        btns.forEach(function (btn) {
            btn.addEventListener('click', function () { activarTipo(btn.dataset.tipo); });
        });
    }

    // Sanitización: mayúsculas y sin espacios en RFC
    [rfcEmp, rfcFis].forEach(function (el) {
        if (!el) return;
        el.addEventListener('input', function () {
            this.value = this.value.replace(/\s/g, '').toUpperCase();
        });
        el.addEventListener('keydown', function (e) {
            if (e.key === ' ') e.preventDefault();
        });
    });

    // Nombre del archivo CIF
    var cifInput = document.getElementById('cif_input');
    if (cifInput) {
        cifInput.addEventListener('change', function () {
            var name = this.files[0] ? this.files[0].name : 'Seleccionar archivo…';
            document.getElementById('cif_filename').textContent = name;
        });
    }
})();
</script>
@endpush

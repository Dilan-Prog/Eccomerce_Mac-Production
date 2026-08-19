@extends('admin-ui.layouts.master')

@section('title', 'Configuración General')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Configuración General',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Configuración General'],
        ],
    ])

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Datos del Sitio</div>
        </div>
        <div class="au-card-body">
            <form action="{{ route('admin.general-setting-update') }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Nombre del sitio<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="site_name" value="{{ old('site_name', $generalSettings->site_name ?? '') }}" required>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Disposición<span class="au-required-mark">*</span></label>
                        <select class="au-select" name="layout" required>
                            <option value="LTR" {{ old('layout', $generalSettings->layout ?? '') == 'LTR' ? 'selected' : '' }}>LTR</option>
                            <option value="RTL" {{ old('layout', $generalSettings->layout ?? '') == 'RTL' ? 'selected' : '' }}>RTL</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Correo de contacto<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="contact_email" value="{{ old('contact_email', $generalSettings->contact_email ?? '') }}" required>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Zona horaria<span class="au-required-mark">*</span></label>
                        <select class="au-select" name="time_zone" required>
                            <option value="">Seleccionar</option>
                            @foreach (config('settings.time_zone') as $key => $timeZone)
                                <option value="{{ $key }}" {{ old('time_zone', $generalSettings->time_zone ?? '') == $key ? 'selected' : '' }}>{{ $key }} {{ $timeZone }}</option>
                            @endforeach
                        </select>
                        <span class="au-help-text">Usada en toda la aplicación en cada solicitud. Cambiar con precaución.</span>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Moneda predeterminada<span class="au-required-mark">*</span></label>
                        <select class="au-select" name="currency_name" required>
                            <option value="">Seleccionar</option>
                            @foreach (config('settings.currency_list') as $currency)
                                <option value="{{ $currency }}" {{ old('currency_name', $generalSettings->currency_name ?? '') == $currency ? 'selected' : '' }}>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Ícono de moneda<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="currency_icon" value="{{ old('currency_icon', $generalSettings->currency_icon ?? '') }}" required>
                    </div>
                </div>

                <div class="au-card-header" style="padding-left:0;padding-right:0">
                    <div class="au-card-title">Tipos de cambio de Cotizaciones</div>
                </div>
                <div class="au-help-text" style="display:block;margin:-4px 0 16px;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
                    @if ($cotizacionAspelRate && $cotizacionAspelRate->tipo_cambio)
                        Tipo de cambio recibido de Aspel hoy: <strong>${{ number_format($cotizacionAspelRate->tipo_cambio, 4) }} MXN por USD</strong>
                        (actualizado: {{ \Illuminate\Support\Carbon::parse($cotizacionAspelRate->updated_at)->format('d/m/Y H:i') }}).
                        Mientras este valor exista, Cotizaciones lo usa en vez de los campos manuales de abajo.
                    @else
                        Aún no se ha recibido ningún tipo de cambio de Aspel para Cotizaciones. Mientras tanto, se están usando los valores manuales de abajo.
                    @endif
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Tipo de cambio USD &rarr; MXN (pesos por dólar)<span class="au-required-mark">*</span></label>
                        <input type="number" step="0.0001" min="0.0001" class="au-input" name="tipo_cambio_usd_mxn" value="{{ old('tipo_cambio_usd_mxn', $generalSettings->tipo_cambio_usd_mxn ?? '') }}" required>
                        <span class="au-help-text">Respaldo: se usa solo si Aspel aún no ha sincronizado un tipo de cambio para Cotizaciones (ver arriba). Nunca afecta el catálogo de Productos.</span>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Tipo de cambio MXN &rarr; USD (dólares por peso)<span class="au-required-mark">*</span></label>
                        <input type="number" step="0.0001" min="0.0001" class="au-input" name="tipo_cambio_mxn_usd" value="{{ old('tipo_cambio_mxn_usd', $generalSettings->tipo_cambio_mxn_usd ?? '') }}" required>
                        <span class="au-help-text">Respaldo: se usa solo si Aspel aún no ha sincronizado un tipo de cambio para Cotizaciones (ver arriba). Nunca afecta el catálogo de Productos.</span>
                    </div>
                </div>
                <span class="au-help-text" style="display:block;margin:-8px 0 16px">Estos dos tipos de cambio son exclusivos del módulo de Cotizaciones — no afectan los precios del catálogo de Productos ni la sincronización con Aspel.</span>

                <button type="submit" class="au-btn au-btn-primary">Guardar</button>
            </form>
        </div>
    </div>
@endsection

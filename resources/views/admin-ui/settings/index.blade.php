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

                <button type="submit" class="au-btn au-btn-primary">Guardar</button>
            </form>
        </div>
    </div>
@endsection

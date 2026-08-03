@extends('admin-ui.layouts.master')

@section('title', 'Ajustes De Pago')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Ajustes De Pago',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Ajustes De Pago'],
        ],
    ])

    {{-- PayPal --}}
    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">PayPal</div>
        </div>
        <div class="au-card-body">
            <form action="{{ route('admin.paypal-setting.update', 1) }}" method="POST">
                @csrf
                @method('PUT')

                @include('admin-ui.partials._toggle-field', [
                    'name' => 'status',
                    'label' => 'PayPal Estado',
                    'checked' => isset($paypalSetting) && (int) $paypalSetting->status === 1,
                ])

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Modo De Cuenta<span class="au-required-mark">*</span></label>
                        <select class="au-select" name="mode" required>
                            <option value="0" {{ old('mode', $paypalSetting->mode ?? '') == 0 ? 'selected' : '' }}>Sandbox</option>
                            <option value="1" {{ old('mode', $paypalSetting->mode ?? '') == 1 ? 'selected' : '' }}>Live</option>
                        </select>
                        <span class="au-help-text">Modo sandbox es para pruebas de manera local.</span>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Nombre Del Pais<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="country_name" value="{{ old('country_name', $paypalSetting->country_name ?? '') }}" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Divisa<span class="au-required-mark">*</span></label>
                        <select class="au-select" name="currency_name" required>
                            <option value="">Seleccionar</option>
                            @foreach (config('settings.currency_list') as $key => $currency)
                                <option value="{{ $key }}" {{ old('currency_name', $paypalSetting->currency_name ?? '') == $key ? 'selected' : '' }}>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Taza De Cambio (Por USD)<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="currency_rate" value="{{ old('currency_rate', $paypalSetting->currency_rate ?? '') }}" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Cliente De Paypal<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="client_id" value="{{ old('client_id', $paypalSetting->client_id ?? '') }}" required>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Paypal key<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="secret_key" value="{{ old('secret_key', $paypalSetting->secret_key ?? '') }}" required>
                    </div>
                </div>

                <button type="submit" class="au-btn au-btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    {{-- Stripe --}}
    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Stripe</div>
        </div>
        <div class="au-card-body">
            <form action="{{ route('admin.stripe-setting.update', 1) }}" method="POST">
                @csrf
                @method('PUT')

                @include('admin-ui.partials._toggle-field', [
                    'name' => 'status',
                    'label' => 'Stripe Estado',
                    'checked' => isset($stripeSetting) && (int) $stripeSetting->status === 1,
                ])

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Modo De Cuenta<span class="au-required-mark">*</span></label>
                        <select class="au-select" name="mode" required>
                            <option value="0" {{ old('mode', $stripeSetting->mode ?? '') == 0 ? 'selected' : '' }}>Sandbox</option>
                            <option value="1" {{ old('mode', $stripeSetting->mode ?? '') == 1 ? 'selected' : '' }}>Live</option>
                        </select>
                        <span class="au-help-text">Modo sandbox es para pruebas de manera local.</span>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Nombre Del Pais<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="country_name" value="{{ old('country_name', $stripeSetting->country_name ?? '') }}" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Divisa<span class="au-required-mark">*</span></label>
                        <select class="au-select" name="currency_name" required>
                            <option value="">Seleccionar</option>
                            @foreach (config('settings.currency_list') as $key => $currency)
                                <option value="{{ $key }}" {{ old('currency_name', $stripeSetting->currency_name ?? '') == $key ? 'selected' : '' }}>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Taza De Cambio (Por USD)<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="currency_rate" value="{{ old('currency_rate', $stripeSetting->currency_rate ?? '') }}" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Cliente De Stripe<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="client_id" value="{{ old('client_id', $stripeSetting->client_id ?? '') }}" required>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Stripe key<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="secret_key" value="{{ old('secret_key', $stripeSetting->secret_key ?? '') }}" required>
                    </div>
                </div>

                <button type="submit" class="au-btn au-btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    {{-- Transferencia --}}
    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Transferencia</div>
        </div>
        <div class="au-card-body">
            <form action="{{ route('admin.transfer.update', 1) }}" method="POST">
                @csrf
                @method('PUT')

                @include('admin-ui.partials._toggle-field', [
                    'name' => 'status',
                    'label' => 'Transferencia Estado',
                    'checked' => isset($transferSetting) && (int) $transferSetting->status === 1,
                ])

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Nombre del Banco<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="nameBank" value="{{ old('nameBank', $transferSetting->nameBank ?? '') }}" required>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Titular<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="nameTitular" value="{{ old('nameTitular', $transferSetting->nameTitular ?? '') }}" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Numero De Cuenta<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="accountNumber" value="{{ old('accountNumber', $transferSetting->accountNumber ?? '') }}" required>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Numero De Tarjeta<span class="au-required-mark">*</span></label>
                        <input type="text" class="au-input" name="accountTarjet" value="{{ old('accountTarjet', $transferSetting->accountTarjet ?? '') }}" required>
                    </div>
                </div>

                <div class="au-field">
                    <label class="au-label">Clabe Interbancaria<span class="au-required-mark">*</span></label>
                    <input type="text" class="au-input" name="accountClabe" value="{{ old('accountClabe', $transferSetting->accountClabe ?? '') }}" required>
                </div>

                <button type="submit" class="au-btn au-btn-primary">Guardar</button>
            </form>
        </div>
    </div>
@endsection

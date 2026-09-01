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
                        <label class="au-label">Modo Activo<span class="au-required-mark">*</span></label>
                        <select class="au-select" name="mode" required>
                            <option value="0" {{ old('mode', $paypalSetting->mode ?? '') == 0 ? 'selected' : '' }}>Sandbox</option>
                            <option value="1" {{ old('mode', $paypalSetting->mode ?? '') == 1 ? 'selected' : '' }}>Live</option>
                        </select>
                        <span class="au-help-text">Cambia entre las credenciales de Sandbox y Live guardadas abajo — no borra ni pisa la otra, solo elige cuál se usa para cobrar.</span>
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

                <div class="au-card" style="margin:16px 0;border-color:#f0ad4e">
                    <div class="au-card-header">
                        <div class="au-card-title">Credenciales Sandbox {{ (old('mode', $paypalSetting->mode ?? 0) == 0) ? '(Activo)' : '' }}</div>
                    </div>
                    <div class="au-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                            <div class="au-field">
                                <label class="au-label">Cliente De Paypal (Sandbox)</label>
                                <input type="text" class="au-input" name="sandbox_client_id" value="{{ old('sandbox_client_id', $paypalSetting->sandbox_client_id ?? '') }}">
                            </div>
                            <div class="au-field">
                                <label class="au-label">Paypal key (Sandbox)</label>
                                <input type="text" class="au-input" name="sandbox_secret_key" value="{{ old('sandbox_secret_key', $paypalSetting->sandbox_secret_key ?? '') }}">
                            </div>
                        </div>
                        <div class="au-field">
                            <label class="au-label">Webhook ID (Sandbox)</label>
                            <input type="text" class="au-input au-mono" name="sandbox_webhook_id" value="{{ old('sandbox_webhook_id', $paypalSetting->sandbox_webhook_id ?? '') }}">
                            <span class="au-help-text">Del webhook de sandbox creado en el Dashboard de PayPal (evento PAYMENT.CAPTURE.COMPLETED) apuntando a {{ url('/api/webhooks/paypal') }}.</span>
                        </div>
                    </div>
                </div>

                <div class="au-card" style="margin:16px 0;border-color:#5cb85c">
                    <div class="au-card-header">
                        <div class="au-card-title">Credenciales Live {{ (old('mode', $paypalSetting->mode ?? 0) == 1) ? '(Activo)' : '' }}</div>
                    </div>
                    <div class="au-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                            <div class="au-field">
                                <label class="au-label">Cliente De Paypal (Live)</label>
                                <input type="text" class="au-input" name="client_id" value="{{ old('client_id', $paypalSetting->client_id ?? '') }}">
                            </div>
                            <div class="au-field">
                                <label class="au-label">Paypal key (Live)</label>
                                <input type="text" class="au-input" name="secret_key" value="{{ old('secret_key', $paypalSetting->secret_key ?? '') }}">
                            </div>
                        </div>
                        <div class="au-field">
                            <label class="au-label">Webhook ID (Live)</label>
                            <input type="text" class="au-input au-mono" name="webhook_id" value="{{ old('webhook_id', $paypalSetting->webhook_id ?? '') }}">
                            <span class="au-help-text">Del webhook de live creado en el Dashboard de PayPal (evento PAYMENT.CAPTURE.COMPLETED) apuntando a {{ url('/api/webhooks/paypal') }} — sin esto, no se manda alerta si un cobro se completa pero la orden no se crea.</span>
                        </div>
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
                        <label class="au-label">Modo Activo<span class="au-required-mark">*</span></label>
                        <select class="au-select" name="mode" required>
                            <option value="0" {{ old('mode', $stripeSetting->mode ?? '') == 0 ? 'selected' : '' }}>Sandbox (Test mode)</option>
                            <option value="1" {{ old('mode', $stripeSetting->mode ?? '') == 1 ? 'selected' : '' }}>Live</option>
                        </select>
                        <span class="au-help-text">Cambia entre las credenciales de Sandbox y Live guardadas abajo — no borra ni pisa la otra, solo elige cuál se usa para cobrar.</span>
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

                <div class="au-card" style="margin:16px 0;border-color:#f0ad4e">
                    <div class="au-card-header">
                        <div class="au-card-title">Credenciales Sandbox (Test mode) {{ (old('mode', $stripeSetting->mode ?? 0) == 0) ? '(Activo)' : '' }}</div>
                    </div>
                    <div class="au-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                            <div class="au-field">
                                <label class="au-label">Cliente De Stripe (Sandbox)</label>
                                <input type="text" class="au-input" name="sandbox_client_id" value="{{ old('sandbox_client_id', $stripeSetting->sandbox_client_id ?? '') }}">
                            </div>
                            <div class="au-field">
                                <label class="au-label">Stripe key (Sandbox)</label>
                                <input type="text" class="au-input" name="sandbox_secret_key" value="{{ old('sandbox_secret_key', $stripeSetting->sandbox_secret_key ?? '') }}">
                            </div>
                        </div>
                        <div class="au-field">
                            <label class="au-label">Webhook Signing Secret (Sandbox)</label>
                            <input type="text" class="au-input au-mono" name="sandbox_webhook_secret" value="{{ old('sandbox_webhook_secret', $stripeSetting->sandbox_webhook_secret ?? '') }}">
                            <span class="au-help-text">Del webhook de test mode creado en el Dashboard de Stripe (evento charge.succeeded) apuntando a {{ url('/api/webhooks/stripe') }}.</span>
                        </div>
                    </div>
                </div>

                <div class="au-card" style="margin:16px 0;border-color:#5cb85c">
                    <div class="au-card-header">
                        <div class="au-card-title">Credenciales Live {{ (old('mode', $stripeSetting->mode ?? 0) == 1) ? '(Activo)' : '' }}</div>
                    </div>
                    <div class="au-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                            <div class="au-field">
                                <label class="au-label">Cliente De Stripe (Live)</label>
                                <input type="text" class="au-input" name="client_id" value="{{ old('client_id', $stripeSetting->client_id ?? '') }}">
                            </div>
                            <div class="au-field">
                                <label class="au-label">Stripe key (Live)</label>
                                <input type="text" class="au-input" name="secret_key" value="{{ old('secret_key', $stripeSetting->secret_key ?? '') }}">
                            </div>
                        </div>
                        <div class="au-field">
                            <label class="au-label">Webhook Signing Secret (Live)</label>
                            <input type="text" class="au-input au-mono" name="webhook_secret" value="{{ old('webhook_secret', $stripeSetting->webhook_secret ?? '') }}">
                            <span class="au-help-text">Del webhook de live creado en el Dashboard de Stripe (evento charge.succeeded) apuntando a {{ url('/api/webhooks/stripe') }} — sin esto, no se manda alerta si un cobro se completa pero la orden no se crea.</span>
                        </div>
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

{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div style="display:flex;flex-direction:column;gap:16px;min-width:0">
        <div class="au-form-grid-2">
            <div class="au-field">
                <label class="au-label">Banco<span class="au-required-mark">*</span></label>
                <input type="text" class="au-input" name="banco" value="{{ $bankAccount->banco ?? '' }}" required>
            </div>
            <div class="au-field">
                <label class="au-label">Titular<span class="au-required-mark">*</span></label>
                <input type="text" class="au-input" name="titular" value="{{ $bankAccount->titular ?? '' }}" required>
            </div>
        </div>
        <div class="au-form-grid-2">
            <div class="au-field">
                <label class="au-label">Tipo de moneda<span class="au-required-mark">*</span></label>
                <select class="au-input" name="moneda" required>
                    <option value="MXN" {{ (($bankAccount->moneda ?? 'MXN') === 'MXN') ? 'selected' : '' }}>Pesos (MXN)</option>
                    <option value="USD" {{ (($bankAccount->moneda ?? 'MXN') === 'USD') ? 'selected' : '' }}>Dólares (USD)</option>
                </select>
            </div>
            <div class="au-field">
                <label class="au-label">No. de Cuenta</label>
                <input type="text" class="au-input" name="numero_cuenta" value="{{ $bankAccount->numero_cuenta ?? '' }}">
            </div>
        </div>
        <div class="au-form-grid-2">
            <div class="au-field">
                <label class="au-label">No. de Tarjeta</label>
                <input type="text" class="au-input" name="numero_tarjeta" value="{{ $bankAccount->numero_tarjeta ?? '' }}">
            </div>
            <div class="au-field">
                <label class="au-label">CLABE</label>
                <input type="text" class="au-input" name="clabe" value="{{ $bankAccount->clabe ?? '' }}">
            </div>
        </div>
        @include('admin-ui.partials._toggle-field', [
            'name' => 'status',
            'label' => 'Estado',
            'checked' => isset($bankAccount) ? (int) $bankAccount->status === 1 : true,
        ])
    </div>
    <div class="au-form-sidebar">
        <button type="button" class="au-image-slot {{ isset($bankAccount) && $bankAccount->logo ? 'has-image' : '' }}" data-au-image-slot data-au-image-slot-folder="bank-account" data-au-image-slot-target="[name=logo_from_library]">
            @if (isset($bankAccount) && $bankAccount->logo)
                <span class="au-image-slot-preview" style="background-image:url('{{ $bankAccount->logo }}')"></span>
            @else
                <div class="au-image-slot-empty">
                    <div class="au-image-slot-empty-icon"><i class="fas fa-image"></i></div>
                    <span style="font-size:12px;font-weight:600;color:#2b3648">Seleccionar logo del banco</span>
                </div>
            @endif
        </button>
        <label class="au-label" style="margin-top:4px">O sube un archivo nuevo</label>
        <input type="file" class="au-input" name="logo">
        <input type="hidden" name="logo_from_library" value="">
        <span class="au-help-text">Recomendado: 200x200px, fondo transparente.</span>
        @if (isset($bankAccount) && $bankAccount->logo)
            <span class="au-help-text">Deja ambos vacíos para mantener el logo actual.</span>
        @endif
    </div>
</form>

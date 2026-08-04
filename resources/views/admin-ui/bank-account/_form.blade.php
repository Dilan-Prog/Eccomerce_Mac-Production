{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
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
            <label class="au-label">No. de Cuenta</label>
            <input type="text" class="au-input" name="numero_cuenta" value="{{ $bankAccount->numero_cuenta ?? '' }}">
        </div>
        <div class="au-field">
            <label class="au-label">No. de Tarjeta</label>
            <input type="text" class="au-input" name="numero_tarjeta" value="{{ $bankAccount->numero_tarjeta ?? '' }}">
        </div>
    </div>
    <div class="au-field">
        <label class="au-label">CLABE</label>
        <input type="text" class="au-input" name="clabe" value="{{ $bankAccount->clabe ?? '' }}">
    </div>
    @include('admin-ui.partials._toggle-field', [
        'name' => 'status',
        'label' => 'Estado',
        'checked' => isset($bankAccount) ? (int) $bankAccount->status === 1 : true,
    ])
</form>

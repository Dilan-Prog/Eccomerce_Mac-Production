{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $coupon->name ?? '' }}" required>
    </div>
    <div class="au-field">
        <label class="au-label">Codigo<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="cod" value="{{ $coupon->cod ?? '' }}" required>
    </div>
    <div class="au-form-grid-2">
        <div class="au-field">
            <label class="au-label">Cantidad<span class="au-required-mark">*</span></label>
            <input type="number" class="au-input" name="quantity" value="{{ $coupon->quantity ?? '' }}" required>
        </div>
        <div class="au-field">
            <label class="au-label">Max Usos por Persona (un mismo usuario)<span class="au-required-mark">*</span></label>
            <input type="number" class="au-input" name="max_use" value="{{ $coupon->max_use ?? '' }}" required>
        </div>
    </div>
    <div class="au-form-grid-2">
        <div class="au-field">
            <label class="au-label">Fecha de inicio<span class="au-required-mark">*</span></label>
            <input type="date" class="au-input" name="start_date" value="{{ $coupon->start_date ?? '' }}" required>
        </div>
        <div class="au-field">
            <label class="au-label">Fecha de Fin<span class="au-required-mark">*</span></label>
            <input type="date" class="au-input" name="end_date" value="{{ $coupon->end_date ?? '' }}" required>
        </div>
    </div>
    <div class="au-form-grid-2">
        <div class="au-field">
            <label class="au-label">Tipo de descuento<span class="au-required-mark">*</span></label>
            <select class="au-select" name="discount_type">
                <option value="percent" {{ (isset($coupon) ? $coupon->discount_type : '') == 'percent' ? 'selected' : '' }}>Porcentaje (%)</option>
                <option value="amount" {{ (isset($coupon) ? $coupon->discount_type : '') == 'amount' ? 'selected' : '' }}>Monto o Precio ({{ $settings->currency_icon ?? '' }})</option>
            </select>
        </div>
        <div class="au-field">
            <label class="au-label">Valor De Descuento<span class="au-required-mark">*</span></label>
            <input type="text" class="au-input" name="discount" value="{{ $coupon->discount ?? '' }}" required>
        </div>
    </div>
    @include('admin-ui.partials._toggle-field', [
        'name' => 'status',
        'label' => 'Estado',
        'checked' => isset($coupon) ? (int) $coupon->status === 1 : true,
    ])
</form>

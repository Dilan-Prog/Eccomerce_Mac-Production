{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $shippingRule->name ?? '' }}" required>
    </div>
    <div class="au-form-grid-2">
        <div class="au-field">
            <label class="au-label">Tipo<span class="au-required-mark">*</span></label>
            <select class="au-select" name="type"
                    onchange="document.getElementById('au-min-cost-field').style.display = (this.value === 'min_cost') ? '' : 'none'">
                <option value="" {{ !isset($shippingRule) ? 'selected' : '' }}>Seleccionar...</option>
                <option value="flat_cost" {{ isset($shippingRule) && $shippingRule->type == 'flat_cost' ? 'selected' : '' }}>Costo fijo</option>
                <option value="min_cost" {{ isset($shippingRule) && $shippingRule->type == 'min_cost' ? 'selected' : '' }}>Cantidad mínima de pedido</option>
            </select>
        </div>
        <div class="au-field">
            <label class="au-label">Costo<span class="au-required-mark">*</span></label>
            <input type="number" step="any" class="au-input" name="cost" value="{{ $shippingRule->cost ?? '' }}" required>
        </div>
    </div>
    <div class="au-field" id="au-min-cost-field" style="{{ isset($shippingRule) && $shippingRule->type == 'min_cost' ? '' : 'display:none' }}">
        <label class="au-label">Cantidad Mínima</label>
        <input type="number" step="any" class="au-input" name="min_cost" value="{{ $shippingRule->min_cost ?? '' }}">
    </div>
    @include('admin-ui.partials._toggle-field', [
        'name' => 'status',
        'label' => 'Estado',
        'checked' => isset($shippingRule) ? (int) $shippingRule->status === 1 : true,
    ])
</form>

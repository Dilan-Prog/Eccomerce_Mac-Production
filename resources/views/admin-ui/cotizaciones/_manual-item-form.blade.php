{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre del producto<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="nombre" required>
    </div>
    <div class="au-field">
        <label class="au-label">Marca</label>
        <input type="text" class="au-input" name="marca">
    </div>
    <div class="au-field">
        <label class="au-label">Modelo</label>
        <input type="text" class="au-input" name="modelo">
    </div>
    <div class="au-field">
        <label class="au-label">SKU</label>
        <input type="text" class="au-input" name="sku">
    </div>
    <div class="au-field">
        <label class="au-label">Cantidad<span class="au-required-mark">*</span></label>
        <input type="number" class="au-input" name="cantidad" min="1" step="1" value="1" required>
    </div>
    <div class="au-field">
        <label class="au-label">Precio unitario ({{ $cotizacion->currency }})<span class="au-required-mark">*</span></label>
        <input type="number" class="au-input" name="precio_unitario" min="0" step="0.01" required>
        <span class="au-help-text">Captura el precio directamente en {{ $cotizacion->currency }} — no se convierte automáticamente.</span>
    </div>
</form>

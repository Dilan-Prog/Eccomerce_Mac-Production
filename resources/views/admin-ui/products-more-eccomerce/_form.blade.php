{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <input type="hidden" name="product" value="{{ $productMoreEccomerce->product_id ?? request()->product }}">
    <div class="au-field">
        <label class="au-label">Nombre del Comercio<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="nameEccomerce" value="{{ $productMoreEccomerce->nameEccomerce ?? '' }}" required>
    </div>
    <div class="au-field">
        <label class="au-label">Link del Producto<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="linkProduct" value="{{ $productMoreEccomerce->linkProduct ?? '' }}" required>
    </div>
    @include('admin-ui.partials._toggle-field', [
        'name' => 'status',
        'label' => 'Estado',
        'checked' => isset($productMoreEccomerce) ? (int) $productMoreEccomerce->status === 1 : true,
    ])
</form>

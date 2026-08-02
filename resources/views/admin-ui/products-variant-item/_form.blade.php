{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
@php
    // Create: $product/$variant are passed in. Edit: $variantItem is passed in.
    $variant = $variant ?? optional($variantItem ?? null)->productVariant;
    $product = $product ?? optional($variant)->product;
@endphp
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre de la variante</label>
        <input type="text" class="au-input" name="variant_name" value="{{ $variant->name ?? '' }}" readonly>
    </div>
    <input type="hidden" name="variant_id" value="{{ $variant->id ?? '' }}">
    <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
    <div class="au-field">
        <label class="au-label">Nombre del Item<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $variantItem->name ?? '' }}" required>
    </div>
    @include('admin-ui.partials._toggle-field', [
        'name' => 'status',
        'label' => 'Estado',
        'checked' => isset($variantItem) ? (int) $variantItem->status === 1 : true,
    ])
</form>

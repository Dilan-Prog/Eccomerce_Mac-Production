{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
@php
    // ProductVariantCombinations::variants_item_ids has an 'array' cast, but
    // store()/update() assign it a pre-json_encode()'d string, so Eloquent's
    // array-cast mutator encodes it a second time on save. On read this
    // yields the *string* "[7,12]" rather than the array [7, 12]. Normalize
    // defensively here (view layer only) so the edit form still pre-checks
    // the right items regardless of which shape ends up in the DB.
    $selectedVariantItemIds = [];
    if (isset($productVariantCombination)) {
        $rawVariantItemIds = $productVariantCombination->variants_item_ids;
        if (is_string($rawVariantItemIds)) {
            $decodedVariantItemIds = json_decode($rawVariantItemIds, true);
            $selectedVariantItemIds = is_array($decodedVariantItemIds) ? $decodedVariantItemIds : [];
        } elseif (is_array($rawVariantItemIds)) {
            $selectedVariantItemIds = $rawVariantItemIds;
        }
    }
@endphp
<form>
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">

    <div class="au-field">
        <label class="au-label">Nombre de la Combinación<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $productVariantCombination->name ?? '' }}" required>
    </div>

    <div class="au-field">
        <label class="au-label">Seleccionar Items de Variante<span class="au-required-mark">*</span></label>
        <div class="au-form-grid-3">
            @foreach ($variantItems as $item)
                <label class="au-flex au-gap-2" style="align-items:center;font-weight:400;">
                    <input class="au-checkbox" type="checkbox" name="variants_item_ids[]" value="{{ $item->id }}"
                        {{ in_array($item->id, $selectedVariantItemIds) ? 'checked' : '' }}>
                    {{ $item->name }}
                </label>
            @endforeach
        </div>
    </div>

    <div class="au-field">
        <label class="au-label">SKU<span class="au-required-mark">*</span> <span class="au-help-text">(Clave del Producto)</span></label>
        <input type="text" class="au-input" name="sku" value="{{ $productVariantCombination->sku ?? '' }}" required>
    </div>

    <div class="au-form-grid-2">
        <div class="au-field">
            <label class="au-label">Precio<span class="au-required-mark">*</span> <span class="au-help-text">(0 para hacerlo gratis)</span></label>
            <input type="text" class="au-input" name="price" value="{{ $productVariantCombination->price ?? '' }}" required>
        </div>
        <div class="au-field">
            <label class="au-label">Cantidad<span class="au-required-mark">*</span> <span class="au-help-text">(Stock de la Variante)</span></label>
            <input type="number" class="au-input" name="qty" value="{{ $productVariantCombination->qty ?? '' }}" required>
        </div>
    </div>

    @include('admin-ui.partials._toggle-field', [
        'name' => 'is_default',
        'label' => 'Seleccionar por Defecto',
        'checked' => isset($productVariantCombination) ? (int) $productVariantCombination->is_default === 1 : false,
    ])
    @include('admin-ui.partials._toggle-field', [
        'name' => 'status',
        'label' => 'Estado',
        'checked' => isset($productVariantCombination) ? (int) $productVariantCombination->status === 1 : true,
    ])
</form>

{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $variant->name ?? '' }}" required>
    </div>
    @include('admin-ui.partials._toggle-field', [
        'name' => 'status',
        'label' => 'Estado',
        'checked' => isset($variant) ? (int) $variant->status === 1 : true,
    ])
    @if(!isset($variant))
        <input type="hidden" name="product" value="{{ $product->id ?? request('product') }}">
    @endif
</form>

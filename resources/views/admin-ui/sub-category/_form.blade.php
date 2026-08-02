{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Categoria<span class="au-required-mark">*</span></label>
        <select class="au-select" name="category">
            <option value="" {{ !isset($subCategory) ? 'selected' : '' }}>Seleccionar</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ isset($subCategory) && (int) $subCategory->category_id === (int) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $subCategory->name ?? '' }}" required>
    </div>
    @include('admin-ui.partials._toggle-field', [
        'name' => 'status',
        'label' => 'Estado',
        'checked' => isset($subCategory) ? (int) $subCategory->status === 1 : true,
    ])
</form>

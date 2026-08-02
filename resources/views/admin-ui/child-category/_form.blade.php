{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form.
     Category -> Subcategory cascading is done with plain JS (inline onchange)
     instead of the old jQuery AJAX call, since <script> tags injected via
     innerHTML by form-modal.js do not execute. --}}
<form>
    @csrf
    <div class="au-form-grid-2">
        <div class="au-field">
            <label class="au-label">Categoria<span class="au-required-mark">*</span></label>
            <select class="au-select" name="category" id="au-cc-category"
                onchange="
                    var sub = document.getElementById('au-cc-subcategory');
                    Array.prototype.forEach.call(sub.options, function (o) {
                        if (!o.value) { o.hidden = false; return; }
                        o.hidden = o.dataset.category !== this.value;
                    }, this);
                    if (sub.selectedOptions[0] && sub.selectedOptions[0].hidden) { sub.value = ''; }
                ">
                <option value="">Seleccionar...</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ isset($childCategory) && (int) $childCategory->category_id === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="au-field">
            <label class="au-label">Sub Categoria<span class="au-required-mark">*</span></label>
            <select class="au-select" name="sub_category" id="au-cc-subcategory">
                <option value="">Seleccionar...</option>
                @foreach ($subCategories as $subCategory)
                    <option value="{{ $subCategory->id }}" data-category="{{ $subCategory->category_id }}"
                        {{ isset($childCategory) && (int) $subCategory->category_id !== (int) $childCategory->category_id ? 'hidden' : '' }}
                        {{ isset($childCategory) && (int) $childCategory->sub_category_id === $subCategory->id ? 'selected' : '' }}>
                        {{ $subCategory->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $childCategory->name ?? '' }}" required>
    </div>
    @include('admin-ui.partials._toggle-field', [
        'name' => 'status',
        'label' => 'Estado',
        'checked' => isset($childCategory) ? (int) $childCategory->status === 1 : true,
    ])
</form>

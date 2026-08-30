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
    {{-- Cascada categoria -> subcategoria -> categoria hija con JS plano
         (inline onchange), mismo patron que admin-ui/products/_form.blade.php
         -- necesario porque los <script> inyectados via innerHTML por
         form-modal.js no se ejecutan. Los 3 son opcionales y cada nivel
         restringe mas: dejar subcategoria/hija en "Ninguna" aplica el cupon
         a toda la categoria (o a todo el sitio si tambien category_id queda vacio). --}}
    <div class="au-form-grid-3">
        <div class="au-field">
            <label class="au-label">Categoria (opcional)</label>
            <select class="au-select" name="category_id" onchange="
                var sub = this.form.sub_category_id;
                var val = this.value;
                Array.prototype.forEach.call(sub.options, function (o) {
                    if (!o.value) { o.hidden = false; return; }
                    o.hidden = o.dataset.category !== val;
                });
                if (sub.selectedOptions[0] && sub.selectedOptions[0].hidden) { sub.value = ''; }
                sub.dispatchEvent(new Event('change'));
            ">
                <option value="" {{ !isset($coupon) || !$coupon->category_id ? 'selected' : '' }}>Ninguna / Global</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ isset($coupon) && (int) $coupon->category_id === (int) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="au-field">
            <label class="au-label">Subcategoria (opcional)</label>
            <select class="au-select" name="sub_category_id" onchange="
                var child = this.form.child_category_id;
                var val = this.value;
                Array.prototype.forEach.call(child.options, function (o) {
                    if (!o.value) { o.hidden = false; return; }
                    o.hidden = o.dataset.subcategory !== val;
                });
                if (child.selectedOptions[0] && child.selectedOptions[0].hidden) { child.value = ''; }
            ">
                <option value="" {{ !isset($coupon) || !$coupon->sub_category_id ? 'selected' : '' }}>Ninguna</option>
                @foreach ($subCategories as $subCategory)
                    <option value="{{ $subCategory->id }}" data-category="{{ $subCategory->category_id }}"
                        {{ isset($coupon) && $coupon->category_id && (int) $subCategory->category_id !== (int) $coupon->category_id ? 'hidden' : '' }}
                        {{ isset($coupon) && (int) $coupon->sub_category_id === $subCategory->id ? 'selected' : '' }}>
                        {{ $subCategory->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="au-field">
            <label class="au-label">Categoria hija (opcional)</label>
            <select class="au-select" name="child_category_id">
                <option value="" {{ !isset($coupon) || !$coupon->child_category_id ? 'selected' : '' }}>Ninguna</option>
                @foreach ($childCategories as $childCategory)
                    <option value="{{ $childCategory->id }}" data-subcategory="{{ $childCategory->sub_category_id }}"
                        {{ isset($coupon) && $coupon->sub_category_id && (int) $childCategory->sub_category_id !== (int) $coupon->sub_category_id ? 'hidden' : '' }}
                        {{ isset($coupon) && (int) $coupon->child_category_id === $childCategory->id ? 'selected' : '' }}>
                        {{ $childCategory->name }}
                    </option>
                @endforeach
            </select>
        </div>
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

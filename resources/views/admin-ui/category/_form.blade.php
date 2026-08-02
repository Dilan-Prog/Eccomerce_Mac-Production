{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div class="au-field">
        <label class="au-label">Icono (clase FontAwesome)</label>
        <div class="au-flex au-gap-2">
            <input type="text" class="au-input" name="icon" value="{{ $category->icon ?? '' }}"
                   placeholder="fas fa-tag"
                   oninput="document.getElementById('au-icon-preview').className = this.value || 'fas fa-tag'">
            <i id="au-icon-preview" class="{{ $category->icon ?? 'fas fa-tag' }}"
               style="width:38px;height:38px;flex:none;border-radius:9px;background:var(--au-surface-subdued);border:1px solid var(--au-border);display:flex;align-items:center;justify-content:center;color:var(--au-text-subdued)"></i>
        </div>
    </div>
    <div class="au-field">
        <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $category->name ?? '' }}" required>
    </div>
    <div class="au-form-grid-2">
        @include('admin-ui.partials._toggle-field', [
            'name' => 'status',
            'label' => 'Estado',
            'checked' => isset($category) ? (int) $category->status === 1 : true,
        ])
        <div class="au-field">
            <label class="au-label">Orden en el menú<span class="au-required-mark">*</span></label>
            <input type="number" class="au-input" name="sort_order" value="{{ $category->sort_order ?? 0 }}" min="0">
            <span class="au-help-text">Número más bajo aparece primero. Máximo 6 categorías visibles.</span>
        </div>
    </div>
</form>

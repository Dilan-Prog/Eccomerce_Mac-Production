{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div style="display:flex;flex-direction:column;gap:16px;min-width:0">
        <div class="au-field">
            <label class="au-label">Nombre<span class="au-required-mark">*</span></label>
            <input type="text" class="au-input" name="name" value="{{ $brand->name ?? '' }}" required>
        </div>
        @include('admin-ui.partials._toggle-field', ['name' => 'is_featured', 'label' => 'Favoritos', 'checked' => isset($brand) && (int) $brand->is_featured === 1])
        @include('admin-ui.partials._toggle-field', ['name' => 'status', 'label' => 'Estado', 'checked' => isset($brand) ? (int) $brand->status === 1 : true])
    </div>
    <div class="au-form-sidebar">
        <button type="button" class="au-image-slot {{ isset($brand) && $brand->logo ? 'has-image' : '' }}" data-au-image-slot data-au-image-slot-folder="logo" data-au-image-slot-target="[name=logo_from_library]">
            @if (isset($brand) && $brand->logo)
                <span class="au-image-slot-preview" style="background-image:url('{{ $brand->logo }}')"></span>
            @else
                <div class="au-image-slot-empty">
                    <div class="au-image-slot-empty-icon"><i class="fas fa-image"></i></div>
                    <span style="font-size:12px;font-weight:600;color:#2b3648">Seleccionar logo</span>
                </div>
            @endif
        </button>
        <label class="au-label" style="margin-top:4px">O sube un archivo nuevo</label>
        <input type="file" class="au-input" name="logo">
        <input type="hidden" name="logo_from_library" value="">
        @if (isset($brand) && $brand->logo)
            <span class="au-help-text">Deja ambos vacíos para mantener el logo actual.</span>
        @endif
    </div>
</form>

{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form. --}}
<form>
    @csrf
    <div style="display:flex;flex-direction:column;gap:16px;min-width:0">
        <div class="au-form-grid-2">
            <div class="au-field">
                <label class="au-label">Tipo</label>
                <input type="text" class="au-input" name="type" value="{{ $slider->type ?? '' }}">
            </div>
            <div class="au-field">
                <label class="au-label">Título</label>
                <input type="text" class="au-input" name="title" value="{{ $slider->title ?? '' }}">
            </div>
        </div>
        <div class="au-form-grid-2">
            <div class="au-field">
                <label class="au-label">Precio inicial</label>
                <input type="text" class="au-input" name="starting_price" value="{{ $slider->starting_price ?? '' }}">
            </div>
            <div class="au-field">
                <label class="au-label">URL del Botón</label>
                <input type="text" class="au-input" name="btn_url" value="{{ $slider->btn_url ?? '' }}">
            </div>
        </div>
        <div class="au-field">
            <label class="au-label">Serial<span class="au-required-mark">*</span></label>
            <input type="number" class="au-input" name="serial" value="{{ $slider->serial ?? '' }}" required>
        </div>
        @include('admin-ui.partials._toggle-field', [
            'name' => 'status',
            'label' => 'Estado',
            'checked' => isset($slider) ? (int) $slider->status === 1 : true,
        ])
    </div>
    <div class="au-form-sidebar">
        <button type="button" class="au-image-slot {{ isset($slider) && $slider->banner ? 'has-image' : '' }}" data-au-image-slot data-au-image-slot-folder="slider" data-au-image-slot-target="[name=banner_from_library]">
            @if (isset($slider) && $slider->banner)
                <span class="au-image-slot-preview" style="background-image:url('{{ $slider->banner }}')"></span>
            @else
                <div class="au-image-slot-empty">
                    <div class="au-image-slot-empty-icon"><i class="fas fa-image"></i></div>
                    <span style="font-size:12px;font-weight:600;color:#2b3648">Seleccionar banner</span>
                </div>
            @endif
        </button>
        <label class="au-label" style="margin-top:4px">O sube un archivo nuevo</label>
        <input type="file" class="au-input" name="banner">
        <input type="hidden" name="banner_from_library" value="">
        @if (isset($slider) && $slider->banner)
            <span class="au-help-text">Deja ambos vacíos para mantener el banner actual.</span>
        @else
            <span class="au-help-text">Imagen JPEG/PNG/JPG/GIF/SVG/WEBP, máx 2MB.</span>
        @endif
    </div>
</form>

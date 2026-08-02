{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form.
     This module has no old create/edit Blade view: its "create" form IS the
     existing multi-image upload (store() validates image.* and calls
     uploadMultiImage). Scoped to the current product via a hidden input,
     mirroring ProductImageGalleryTableQuery::forProduct()'s request()->product.

     Images can come from either source (or both): a fresh multi-file upload
     (unchanged) or one-or-more picks from the Media Library gallery. The
     "+ Agregar desde galería" button is wired by a small page-level script
     in index.blade.php (AU.ImagePicker with multiple:true) rather than the
     generic single-target [data-au-image-slot] handler in admin.js, since
     that handler only supports one hidden input per slot and this module
     needs to accumulate any number of picks into image_from_library[]. --}}
<form>
    @csrf
    <input type="hidden" name="product" value="{{ $product->id ?? request('product') }}">
    <div class="au-field">
        <label class="au-label">Imágenes<span class="au-required-mark">*</span></label>
        <input type="file" class="au-input" name="image[]" multiple accept="image/*">
        <span class="au-help-text">Puedes seleccionar varias imágenes. Máx. 2MB cada una.</span>
    </div>
    <div class="au-field">
        <button type="button" class="au-btn" data-au-gallery-picker-btn="product">+ Agregar desde galería</button>
        <span class="au-help-text">O elige una o varias imágenes ya subidas desde la Biblioteca de Medios.</span>
        <div class="au-image-slot-extra-grid" data-au-gallery-picker-preview style="margin-top:10px"></div>
    </div>
</form>

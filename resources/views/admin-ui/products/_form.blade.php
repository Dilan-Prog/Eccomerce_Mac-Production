{{-- Fragment injected into AU.FormModal — no @extends/layout, just the form.
     Category -> Subcategory -> Child Category cascading is done with plain JS
     (inline onchange, form-scoped element access) instead of the old jQuery
     AJAX calls to admin.product.get-subcategories/get-child-categories,
     since <script> tags injected via innerHTML by form-modal.js do not
     execute (see resources/views/admin-ui/child-category/_form.blade.php
     for the precedent this mirrors). All sub/child categories are preloaded
     and filtered client-side via data-category/data-subcategory + hidden.

     The live SKU-typeahead dropdown (search against admin.product.search-sku,
     auto-filling qty_aspel/aspel_price on click) is wired up again via
     public/admin-ui/js/products/sku-search.js, using a delegated listener on
     document (AU.on) instead of a script tag in this fragment, since anything
     injected here via innerHTML never executes. See that file for the full
     flow. The edit view still additionally pre-renders real Aspel price
     options server-side on load (zero JS needed just to open the modal);
     the JS only kicks in when the SKU field changes.

     Sidebar (opts.sidebar: true, see products/index.blade.php +
     ProductTableQuery::rowActions()): main "image" field lives here as an
     AU.ImagePicker slot (folder "product"), same resolveOrCopyImage pattern
     as brand/slider. Below it, 3 read-only preview slots surface this
     product's own products-image-gallery rows (there is no dedicated DB
     column for them — they're just the first 3 gallery rows for this
     product, queried via Product::productImageGalleries()) plus a
     "Ver galería completa" link to that module's own index/modal; this is
     a read/link surface only, not a new upload path. --}}
@php
    $priceOff = isset($product) && (int) $product->price_personalizated === 0;
    $offertOff = isset($product) && (int) $product->price_offert_personalizated === 0;
    $qtyOff = isset($product) && (int) $product->qty_personalizated === 0;
    // New product defaults: both selects default to their first option ("No" / value 0),
    // matching the old form's un-set <select> behavior (no old() -> first option wins).
    $priceDisabled = isset($product) ? $priceOff : true;
    $aspelPriceDisabled = isset($product) ? !$priceOff : false;
    $offertDisabled = isset($product) ? $offertOff : true;
    $aspelOffertDisabled = isset($product) ? !$offertOff : false;
    $qtyDisabled = isset($product) ? $qtyOff : true;
    $qtyAspelDisabled = isset($product) ? !$qtyOff : false;
@endphp
<form>
    @csrf
    <div style="display:flex;flex-direction:column;gap:16px;min-width:0">
    <div class="au-field">
        <label class="au-label">Nombre del Producto<span class="au-required-mark">*</span></label>
        <input type="text" class="au-input" name="name" value="{{ $product->name ?? '' }}" required>
    </div>

    <div class="au-form-grid-3">
        <div class="au-field">
            <label class="au-label">Categoria<span class="au-required-mark">*</span></label>
            <select class="au-select" name="category" onchange="
                var sub = this.form.sub_category;
                var val = this.value;
                Array.prototype.forEach.call(sub.options, function (o) {
                    if (!o.value) { o.hidden = false; return; }
                    o.hidden = o.dataset.category !== val;
                });
                if (sub.selectedOptions[0] && sub.selectedOptions[0].hidden) { sub.value = ''; }
                sub.dispatchEvent(new Event('change'));
            ">
                <option value="">Seleccionar</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ isset($product) && (int) $product->category_id === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="au-field">
            <label class="au-label">Sub Categoria</label>
            <select class="au-select" name="sub_category" onchange="
                var child = this.form.child_category;
                var val = this.value;
                Array.prototype.forEach.call(child.options, function (o) {
                    if (!o.value) { o.hidden = false; return; }
                    o.hidden = o.dataset.subcategory !== val;
                });
                if (child.selectedOptions[0] && child.selectedOptions[0].hidden) { child.value = ''; }
            ">
                <option value="">Seleccionar</option>
                @foreach ($subCategories as $subCategory)
                    <option value="{{ $subCategory->id }}" data-category="{{ $subCategory->category_id }}"
                        {{ isset($product) && (int) $subCategory->category_id !== (int) $product->category_id ? 'hidden' : '' }}
                        {{ isset($product) && (int) $product->sub_category_id === $subCategory->id ? 'selected' : '' }}>
                        {{ $subCategory->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="au-field">
            <label class="au-label">Categoria Secundaria</label>
            <select class="au-select" name="child_category">
                <option value="">Seleccionar</option>
                @foreach ($childCategories as $childCategory)
                    <option value="{{ $childCategory->id }}" data-subcategory="{{ $childCategory->sub_category_id }}"
                        {{ isset($product) && (int) $childCategory->sub_category_id !== (int) $product->sub_category_id ? 'hidden' : '' }}
                        {{ isset($product) && (int) $product->child_category_id === $childCategory->id ? 'selected' : '' }}>
                        {{ $childCategory->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="au-field">
        <label class="au-label">Marca<span class="au-required-mark">*</span></label>
        <select class="au-select" name="brand">
            <option value="">Seleccionar</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" {{ isset($product) && (int) $product->brand_id === $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="au-form-grid-2">
        <div class="au-field">
            <label class="au-label">Sku</label>
            <input type="text" class="au-input" name="sku" value="{{ $product->sku ?? '' }}" autocomplete="off">
            <div class="au-sku-search-results" data-sku-results></div>
        </div>
        <div class="au-field">
            <label class="au-label">Modelo</label>
            <input type="text" class="au-input" name="productModel" value="{{ $product->productModel ?? '' }}">
        </div>
    </div>

    <div class="au-form-grid-2">
        @include('admin-ui.partials._toggle-field', [
            'name' => 'price_personalizated',
            'label' => 'Precio Personalizado',
            'checked' => isset($product) ? (int) $product->price_personalizated === 1 : false,
            'onchange' => "
                var f = this.form;
                var personal = this.value === '1';
                f.price.disabled = !personal;
                if (f.aspel_price) { f.aspel_price.disabled = personal; }
            ",
        ])
        <div class="au-field">
            <label class="au-label">Precio</label>
            <input type="text" class="au-input" name="price" value="{{ $product->price ?? '' }}" {{ $priceDisabled ? 'disabled' : '' }}>
        </div>
    </div>

    <div class="au-field">
        <label class="au-label">Precio Aspel</label>
        @if(isset($product) && isset($aspelPriceOptions) && count($aspelPriceOptions))
            <select class="au-select" name="aspel_price" {{ $aspelPriceDisabled ? 'disabled' : '' }}>
                <option value="">Seleccionar...</option>
                @foreach($aspelPriceOptions as $opt)
                    <option value="{{ $opt['cve_precio'] }}" {{ (int) $product->aspel_price_tier === $opt['cve_precio'] ? 'selected' : '' }}>
                        {{ $opt['descripcion'] }} — ${{ number_format($opt['with_iva'], 2) }} MXN (IVA incl.)
                    </option>
                @endforeach
            </select>
        @else
            <input type="text" class="au-input" name="aspel_price" value="{{ $product->aspel_price ?? '' }}" placeholder="Sin precios SAE para este SKU" {{ $aspelPriceDisabled ? 'disabled' : '' }}>
        @endif
    </div>

    <div class="au-form-grid-2">
        @include('admin-ui.partials._toggle-field', [
            'name' => 'price_offert_personalizated',
            'label' => 'Precio Oferta Personalizado',
            'checked' => isset($product) ? (int) $product->price_offert_personalizated === 1 : false,
            'onchange' => "
                var f = this.form;
                var personal = this.value === '1';
                f.offert_price.disabled = !personal;
                if (f.aspel_offert_price) { f.aspel_offert_price.disabled = personal; }
            ",
        ])
        <div class="au-field">
            <label class="au-label">Precio De Oferta</label>
            <input type="text" class="au-input" name="offert_price" value="{{ $product->offert_price ?? '' }}" {{ $offertDisabled ? 'disabled' : '' }}>
        </div>
    </div>

    <div class="au-field">
        <label class="au-label">Precio De Oferta Aspel</label>
        @if(isset($product) && isset($aspelPriceOptions) && count($aspelPriceOptions))
            <select class="au-select" name="aspel_offert_price" {{ $aspelOffertDisabled ? 'disabled' : '' }}>
                <option value="">Seleccionar...</option>
                @foreach($aspelPriceOptions as $opt)
                    <option value="{{ $opt['cve_precio'] }}" {{ (int) $product->aspel_offert_price_tier === $opt['cve_precio'] ? 'selected' : '' }}>
                        {{ $opt['descripcion'] }} — ${{ number_format($opt['with_iva'], 2) }} MXN (IVA incl.)
                    </option>
                @endforeach
            </select>
        @else
            <input type="text" class="au-input" name="aspel_offert_price" value="{{ $product->aspel_offert_price ?? '' }}" placeholder="Sin precios SAE para este SKU" {{ $aspelOffertDisabled ? 'disabled' : '' }}>
        @endif
    </div>

    <div class="au-form-grid-2">
        <div class="au-field">
            <label class="au-label">Fecha De Inicio De Oferta</label>
            <input type="date" class="au-input" name="offer_start_date" value="{{ $product->offer_start_date ?? '' }}">
        </div>
        <div class="au-field">
            <label class="au-label">Fecha De Final De Oferta</label>
            <input type="date" class="au-input" name="offer_end_date" value="{{ $product->offer_end_date ?? '' }}">
        </div>
    </div>

    <div class="au-form-grid-3">
        <div class="au-field">
            <label class="au-label">Cantidad Personalizada</label>
            <select class="au-select" name="qty_personalizated" onchange="
                var f = this.form;
                var personal = this.value === '1';
                f.qty.disabled = !personal;
                if (f.qty_aspel) { f.qty_aspel.disabled = personal; }
            ">
                <option value="0" {{ isset($product) && (int) $product->qty_personalizated === 0 ? 'selected' : '' }}>No</option>
                <option value="1" {{ isset($product) && (int) $product->qty_personalizated === 1 ? 'selected' : '' }}>Si</option>
            </select>
        </div>
        <div class="au-field">
            <label class="au-label">Cantidad En Stock</label>
            <input type="number" class="au-input" min="0" name="qty" value="{{ $product->qty ?? '' }}" {{ $qtyDisabled ? 'disabled' : '' }}>
        </div>
        <div class="au-field">
            <label class="au-label">Cantidad Aspel</label>
            <input type="number" class="au-input" min="0" name="qty_aspel" value="{{ $product->qty_aspel ?? '' }}" placeholder="Ingrese cantidad manual" {{ $qtyAspelDisabled ? 'disabled' : '' }}>
        </div>
    </div>

    <div class="au-form-grid-2">
        <div class="au-field">
            <label class="au-label">Enlace de Video (insertar https://www.youtube.com/embed/ID)</label>
            <input type="text" class="au-input" name="video_link" value="{{ $product->video_link ?? '' }}">
        </div>
        <div class="au-field">
            <label class="au-label">Url de drive para el PDF</label>
            <input type="text" class="au-input" name="url_PDF" value="{{ $product->url_PDF ?? '' }}">
        </div>
    </div>

    <div class="au-field">
        <label class="au-label">Garantia<span class="au-required-mark">*</span></label>
        <textarea class="au-textarea" name="short_description" maxlength="600" required>{{ $product->short_description ?? '' }}</textarea>
    </div>
    <div class="au-field">
        <label class="au-label">Descripción Larga<span class="au-required-mark">*</span></label>
        <textarea class="au-textarea" name="long_description" required>{{ $product->long_description ?? '' }}</textarea>
    </div>

    <div class="au-field">
        <label class="au-label">Tipo De Producto</label>
        <select class="au-select" name="product_type">
            <option value="0" {{ isset($product) && $product->product_type == '0' ? 'selected' : '' }}>Seleccionar...</option>
            <option value="new_arrival" {{ isset($product) && $product->product_type == 'new_arrival' ? 'selected' : '' }}>Nuevo</option>
            <option value="featured_product" disabled {{ isset($product) && $product->product_type == 'featured_product' ? 'selected' : '' }}>Producto Destacado</option>
            <option value="top_product" {{ isset($product) && $product->product_type == 'top_product' ? 'selected' : '' }}>Más Buscado</option>
            <option value="best_product" {{ isset($product) && $product->product_type == 'best_product' ? 'selected' : '' }}>Más Vendido</option>
        </select>
    </div>

    <div class="au-field">
        <label class="au-label">Seo Titulo</label>
        <input type="text" class="au-input" name="seo_title" maxlength="200" value="{{ $product->seo_title ?? '' }}">
    </div>
    <div class="au-field">
        <label class="au-label">Seo Descripción</label>
        <textarea class="au-textarea" name="seo_description">{{ $product->seo_description ?? '' }}</textarea>
    </div>

    <div class="au-form-grid-2">
        @include('admin-ui.partials._toggle-field', [
            'name' => 'status',
            'label' => 'Estado',
            'checked' => isset($product) ? (int) $product->status === 1 : true,
        ])
        @include('admin-ui.partials._toggle-field', [
            'name' => 'is_canonical',
            'label' => 'Es Url Canonica (Solo Marketing)',
            'checked' => isset($product) && (int) $product->is_canonical === 1,
        ])
    </div>

    <div class="au-field">
        <label class="au-label">Url Canonica Personalizada (Solo Marketing)</label>
        <input type="text" class="au-input" name="canonical_url" value="{{ $product->canonical_url ?? '' }}" placeholder="Dejar vacío si no aplica">
    </div>
    </div>
    <div class="au-form-sidebar">
        <button type="button" class="au-image-slot {{ isset($product) && $product->thumb_image ? 'has-image' : '' }}" data-au-image-slot data-au-image-slot-folder="product" data-au-image-slot-target="[name=image_from_library]">
            @if (isset($product) && $product->thumb_image)
                <span class="au-image-slot-preview" style="background-image:url('{{ asset($product->thumb_image) }}')"></span>
            @else
                <div class="au-image-slot-empty">
                    <div class="au-image-slot-empty-icon"><i class="fas fa-image"></i></div>
                    <span style="font-size:12px;font-weight:600;color:#2b3648">Seleccionar imagen</span>
                </div>
            @endif
        </button>
        <label class="au-label" style="margin-top:4px">O sube un archivo nuevo</label>
        <input type="file" class="au-input" name="image">
        <input type="hidden" name="image_from_library" value="">
        <span class="au-help-text">Se genera automáticamente en los tamaños computers/laptop/tablet/phone/carrusel.{{ isset($product) ? ' Deja ambos vacíos para conservar la imagen actual.' : '' }}</span>

        <label class="au-label" style="margin-top:8px">Galería del producto</label>
        @if (isset($product))
            <div class="au-image-slot-extra-grid">
                @for ($i = 0; $i < 3; $i++)
                    @php($galleryImage = $galleryImages[$i] ?? null)
                    <a href="{{ route('admin.products-image-gallery.index', ['product' => $product->id]) }}" target="_blank" rel="noopener" class="au-image-slot" title="Ver galería completa">
                        @if ($galleryImage)
                            <span class="au-image-slot-preview" style="background-image:url('{{ asset($galleryImage->image) }}')"></span>
                        @else
                            <div class="au-image-slot-empty">
                                <div class="au-image-slot-empty-icon"><i class="fas fa-images"></i></div>
                            </div>
                        @endif
                    </a>
                @endfor
            </div>
            <a href="{{ route('admin.products-image-gallery.index', ['product' => $product->id]) }}" target="_blank" rel="noopener" class="au-btn" style="width:100%;justify-content:center;margin-top:4px">
                <i class="fas fa-images"></i> Ver galería completa
            </a>
        @else
            <div class="au-image-slot-extra-grid">
                @for ($i = 0; $i < 3; $i++)
                    <div class="au-image-slot" style="cursor:default">
                        <div class="au-image-slot-empty">
                            <div class="au-image-slot-empty-icon"><i class="fas fa-images"></i></div>
                        </div>
                    </div>
                @endfor
            </div>
            <span class="au-help-text">La galería estará disponible después de guardar el producto.</span>
        @endif

        <div class="au-sidebar-meta">
            <span class="au-sidebar-meta-label">ID interno</span>
            <span class="au-sidebar-meta-value">{{ $product->id ?? 'Nuevo' }}</span>
        </div>
    </div>
</form>

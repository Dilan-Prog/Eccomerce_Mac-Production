@extends('admin-ui.layouts.master')

@section('title', 'Galeria de Imagenes de Producto')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Galeria de Imagenes de Producto',
        'subtitle' => 'Producto: ' . $product->name,
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Productos', 'url' => route('admin.products.index')],
            ['label' => 'Galeria de Imagenes'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Subir imágenes',
            'subtitle' => 'Producto: ' . $product->name,
            'icon' => 'fas fa-upload',
            'fragmentUrl' => route('admin.products-image-gallery.create-fragment', ['product' => $product->id]),
            'submitUrl' => route('admin.products-image-gallery.store'),
            'method' => 'POST',
        ])) . '">+ Subir Imágenes</button>',
    ])

    <div id="products-image-gallery-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#products-image-gallery-table',
            endpoint: '{{ route('admin.products-image-gallery.table-data', ['product' => $product->id]) }}',
            bulkEndpoint: '{{ route('admin.products-image-gallery.bulk') }}',
            exportEndpoint: '{{ route('admin.products-image-gallery.export', ['product' => $product->id]) }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
            initialFilter: @json(request('filter')),
        });

        /*
         * The Crear modal's fragment (_form.blade.php) is injected via
         * innerHTML by AU.FormModal, so any <script> inside it would never
         * run. This module also needs to pick MULTIPLE library images into
         * one form (image_from_library[]), which the generic single-target
         * [data-au-image-slot] handler in admin.js doesn't support (one
         * hidden input per slot). So this page-level, delegated listener
         * drives AU.ImagePicker directly with {multiple: true} instead —
         * the one exception to the shared image-slot wiring, exactly as
         * called out in _form.blade.php's comment.
         */
        function addGalleryPick(container, file) {
            // Skip a path that's already been picked into this form.
            var already = AU.qsa('[data-au-gallery-pick-path]', container).some(function (el) {
                return el.getAttribute('data-au-gallery-pick-path') === file.path;
            });
            if (already) return;

            var item = document.createElement('div');
            item.className = 'au-image-slot has-image';
            item.setAttribute('data-au-gallery-pick-path', file.path);

            var preview = document.createElement('span');
            preview.className = 'au-image-slot-preview';
            preview.style.backgroundImage = "url('" + file.url + "')";
            item.appendChild(preview);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'au-image-slot-remove';
            removeBtn.setAttribute('aria-label', 'Quitar');
            removeBtn.innerHTML = '&times;';
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                item.remove();
            });
            item.appendChild(removeBtn);

            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'image_from_library[]';
            hidden.value = file.path;
            item.appendChild(hidden);

            container.appendChild(item);
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-au-gallery-picker-btn]');
            if (!btn) return;
            e.preventDefault();

            var form = btn.closest('form');
            var preview = form ? form.querySelector('[data-au-gallery-picker-preview]') : null;
            if (!preview || !window.AU_MEDIA_LIBRARY) return;

            AU.ImagePicker.open({
                dataUrl: window.AU_MEDIA_LIBRARY.dataUrl,
                uploadUrl: window.AU_MEDIA_LIBRARY.uploadUrl,
                defaultFolder: btn.getAttribute('data-au-gallery-picker-btn') || 'product',
                multiple: true,
                onSelect: function (files) {
                    (files || []).forEach(function (file) {
                        addGalleryPick(preview, file);
                    });
                },
            });
        });
    </script>
@endpush

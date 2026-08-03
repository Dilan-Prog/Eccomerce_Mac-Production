@extends('admin-ui.layouts.master')

@section('title', 'Galería de Medios')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Galería de Medios',
        'subtitle' => 'Todas las imágenes subidas al sitio (logo, slider, productos, etc.)',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Administrar Sitio Web'],
            ['label' => 'Galería de Medios'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" id="au-upload-btn">+ Subir Imágenes</button>',
    ])

    <div class="au-tabs" id="ml-top-tabs">
        <div class="au-tab is-active" data-ml-panel="gallery">Galería</div>
        <div class="au-tab" data-ml-panel="duplicates">Duplicados</div>
    </div>

    <div id="ml-panel-gallery">
        <div id="media-library-table"></div>
    </div>

    <div id="ml-panel-duplicates" hidden>
        @include('admin-ui.media-library._duplicates-panel')
    </div>

    <div class="au-modal-overlay" id="au-upload-modal">
        <div class="au-modal">
            <div class="au-modal-head">
                <div class="au-modal-icon"><i class="fas fa-upload"></i></div>
                <div style="display:flex;flex-direction:column;gap:6px">
                    <div class="au-modal-title">Subir imágenes</div>
                    <div class="au-modal-text">Formatos aceptados: JPG, PNG, WEBP, GIF (máx. 5MB cada una).</div>
                </div>
            </div>
            <form id="au-upload-form" style="padding:0 22px;display:flex;flex-direction:column;gap:14px">
                <div class="au-field">
                    <label class="au-label">Carpeta destino</label>
                    <select class="au-select" name="folder">
                        <option value="media">Media (general)</option>
                        <option value="about">Sobre Nosotros</option>
                        <option value="logo">Logo</option>
                        <option value="slider">Slider</option>
                    </select>
                </div>
                <div class="au-field">
                    <label class="au-label">Archivos</label>
                    <input class="au-input" type="file" name="image[]" multiple accept="image/*" required style="padding:8px 11px">
                </div>
            </form>
            <div class="au-modal-actions">
                <button type="button" class="au-btn" id="au-upload-cancel">Cancelar</button>
                <button type="button" class="au-btn au-btn-primary" id="au-upload-submit">Subir</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script src="{{ asset('admin-ui/js/duplicate-images/duplicate-images.js') }}"></script>
    <script>
        const mediaTable = new AU.AdminTable({
            el: '#media-library-table',
            endpoint: '{{ route('admin.media-library.data') }}',
            bulkEndpoint: '{{ route('admin.media-library.bulk') }}',
            bulkActions: [
                { key: 'watermark', label: 'Aplicar marca de agua' },
                { key: 'download', label: 'Descargar (ZIP)', download: true },
            ],
            rowSelectable: true,
        });

        const uploadModal = document.getElementById('au-upload-modal');
        const uploadForm = document.getElementById('au-upload-form');

        document.getElementById('au-upload-btn').addEventListener('click', () => {
            uploadModal.classList.add('is-open');
        });
        document.getElementById('au-upload-cancel').addEventListener('click', () => {
            uploadModal.classList.remove('is-open');
        });
        uploadModal.addEventListener('click', (e) => {
            if (e.target === uploadModal) uploadModal.classList.remove('is-open');
        });

        document.getElementById('au-upload-submit').addEventListener('click', async () => {
            const formData = new FormData(uploadForm);
            try {
                const data = await AU.request('{{ route('admin.media-library.store') }}', {
                    method: 'POST',
                    body: formData,
                });
                AU.toast[data.status === 'success' ? 'success' : 'error'](data.message || 'Listo');
                uploadModal.classList.remove('is-open');
                uploadForm.reset();
                mediaTable.fetchData();
            } catch (err) {
                AU.toast.error((err.data && err.data.message) || 'Ocurrió un error al subir');
            }
        });

        // --- Galería / Duplicados top-level tabs -----------------------------
        // The Duplicados panel never fetches or scans until the user actually
        // opens the tab for the first time (no eager work on page load).
        const mlPanelGallery = document.getElementById('ml-panel-gallery');
        const mlPanelDuplicates = document.getElementById('ml-panel-duplicates');
        let dupInitialized = false;

        const dupConfig = {
            dataUrl: '{{ route('admin.duplicate-images.data') }}',
            scanUrl: '{{ route('admin.duplicate-images.scan') }}',
            searchUrl: '{{ route('admin.duplicate-images.search') }}',
            // Blade can't pre-generate a URL for a group id that doesn't exist
            // yet, so these are templates with a placeholder token the JS
            // module swaps out per-card at click time.
            discardUrlTemplate: '{{ route('admin.duplicate-images.discard', ['group' => '__GROUP_ID__']) }}',
            restoreUrlTemplate: '{{ route('admin.duplicate-images.restore', ['group' => '__GROUP_ID__']) }}',
            replaceUrlTemplate: '{{ route('admin.duplicate-images.replace', ['group' => '__GROUP_ID__']) }}',
        };

        AU.on(document.getElementById('ml-top-tabs'), 'click', '.au-tab', (e, tabEl) => {
            const panel = tabEl.getAttribute('data-ml-panel');

            AU.qsa('#ml-top-tabs .au-tab').forEach((t) => t.classList.remove('is-active'));
            tabEl.classList.add('is-active');

            mlPanelGallery.hidden = panel !== 'gallery';
            mlPanelDuplicates.hidden = panel !== 'duplicates';

            if (panel === 'duplicates' && !dupInitialized) {
                dupInitialized = true;
                AU.DuplicateImages.init(dupConfig);
            }
        });
    </script>
@endpush

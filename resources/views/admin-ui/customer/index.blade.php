@extends('admin-ui.layouts.master')

@section('title', 'Usuarios/Clientes')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Usuarios/Clientes',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Usuarios/Clientes'],
        ],
    ])

    <div id="customer-table"></div>

    {{-- CSF preview modal (reuses the au-modal-* component styles) --}}
    <div class="au-modal-overlay" id="customer-csf-preview">
        <div class="au-modal" style="width:720px;">
            <div class="au-modal-head">
                <div class="au-modal-icon"><i class="fas fa-file-pdf"></i></div>
                <div style="display:flex;flex-direction:column;gap:6px">
                    <div class="au-modal-title">Constancia de Situación Fiscal</div>
                    <div class="au-modal-text">Vista previa del documento CSF del cliente.</div>
                </div>
            </div>
            <iframe id="customer-csf-preview-iframe" src="" style="width:100%;height:60vh;border:1px solid #e2e7f0;border-radius:8px;" title="CSF"></iframe>
            <div class="au-modal-actions">
                <a href="#" target="_blank" id="customer-csf-preview-download" class="au-btn au-btn-sm">
                    <i class="fas fa-download"></i> Abrir en nueva pestaña
                </a>
                <button type="button" class="au-btn au-btn-sm" data-au-modal-close>Cerrar</button>
            </div>
        </div>
    </div>

    {{-- CSF upload modal --}}
    <div class="au-modal-overlay" id="customer-csf-upload">
        <div class="au-modal">
            <div class="au-modal-head">
                <div class="au-modal-icon"><i class="fas fa-upload"></i></div>
                <div style="display:flex;flex-direction:column;gap:6px">
                    <div class="au-modal-title">Subir CSF del cliente</div>
                    <div class="au-modal-text" id="customer-csf-upload-name"></div>
                </div>
            </div>
            <form id="customer-csf-upload-form">
                <div class="au-field">
                    <label class="au-label">Archivo PDF</label>
                    <input type="file" class="au-input" id="customer-csf-upload-file" name="csf_file" accept="application/pdf,.pdf" required>
                    <div class="au-help-text">Solo PDF · Máx. 5 MB</div>
                </div>
                <div class="au-modal-actions">
                    <button type="button" class="au-btn au-btn-sm" data-au-modal-close>Cancelar</button>
                    <button type="submit" class="au-btn au-btn-sm au-btn-primary" id="customer-csf-upload-submit">
                        <i class="fas fa-upload"></i> Subir CSF
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        const customerTable = new AU.AdminTable({
            el: '#customer-table',
            endpoint: '{{ route('admin.customer.table-data') }}',
            exportEndpoint: '{{ route('admin.customer.export') }}',
            rowSelectable: false,
        });

        (function () {
            const csfBaseUrl = '{{ url('admin/customer') }}';
            const statusChangeUrl = '{{ route('admin.customer.status-change') }}';
            const tableRoot = document.getElementById('customer-table');

            const previewOverlay = document.getElementById('customer-csf-preview');
            const previewIframe = document.getElementById('customer-csf-preview-iframe');
            const previewDownload = document.getElementById('customer-csf-preview-download');

            function openPreview(url) {
                previewIframe.src = url;
                previewDownload.href = url;
                previewOverlay.classList.add('is-open');
            }

            function closePreview() {
                previewOverlay.classList.remove('is-open');
                previewIframe.src = '';
            }

            const uploadOverlay = document.getElementById('customer-csf-upload');
            const uploadForm = document.getElementById('customer-csf-upload-form');
            const uploadNameEl = document.getElementById('customer-csf-upload-name');
            const uploadFileInput = document.getElementById('customer-csf-upload-file');
            const uploadSubmitBtn = document.getElementById('customer-csf-upload-submit');
            let uploadUserId = null;

            function openUpload(id, name) {
                uploadUserId = id;
                uploadNameEl.textContent = name ? ('Cliente: ' + name) : '';
                uploadForm.reset();
                uploadOverlay.classList.add('is-open');
            }

            function closeUpload() {
                uploadOverlay.classList.remove('is-open');
                uploadUserId = null;
            }

            tableRoot.addEventListener('click', function (e) {
                const toggleTrigger = e.target.closest('a[href="#toggle-status"]');
                if (toggleTrigger) {
                    e.preventDefault();
                    const id = toggleTrigger.getAttribute('data-row-id');
                    const activate = toggleTrigger.textContent.trim() === 'Activar';
                    AU.request(statusChangeUrl, { method: 'PUT', body: { id: id, status: activate } })
                        .then(function (data) {
                            AU.toast.success(data.message || 'Estado actualizado');
                            customerTable.fetchData();
                        })
                        .catch(function (err) {
                            AU.toast.error((err.data && err.data.message) || 'Ocurrió un error al actualizar el estado.');
                        });
                    return;
                }

                const uploadTrigger = e.target.closest('a[href="#upload-csf"]');
                if (uploadTrigger) {
                    e.preventDefault();
                    const id = uploadTrigger.getAttribute('data-row-id');
                    const row = uploadTrigger.closest('tr');
                    const nameEl = row ? row.querySelector('.au-flex span') : null;
                    openUpload(id, nameEl ? nameEl.textContent.trim() : '');
                    return;
                }

                const viewTrigger = e.target.closest('a[href*="/csf/view"]');
                if (viewTrigger) {
                    e.preventDefault();
                    openPreview(viewTrigger.getAttribute('href'));
                }
            });

            previewOverlay.addEventListener('click', function (e) {
                if (e.target === previewOverlay || e.target.closest('[data-au-modal-close]')) closePreview();
            });

            uploadOverlay.addEventListener('click', function (e) {
                if (e.target === uploadOverlay || e.target.closest('[data-au-modal-close]')) closeUpload();
            });

            uploadForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!uploadUserId || !uploadFileInput.files.length) return;

                const formData = new FormData(uploadForm);
                uploadSubmitBtn.disabled = true;

                AU.request(csfBaseUrl + '/' + uploadUserId + '/csf', { method: 'POST', body: formData })
                    .then(function (data) {
                        AU.toast.success(data.message || 'CSF subido correctamente.');
                        closeUpload();
                        customerTable.fetchData();
                    })
                    .catch(function (err) {
                        AU.toast.error((err.data && err.data.message) || 'Error al subir el archivo.');
                    })
                    .finally(function () {
                        uploadSubmitBtn.disabled = false;
                    });
            });
        })();
    </script>
@endpush

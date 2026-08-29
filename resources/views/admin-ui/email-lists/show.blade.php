@extends('admin-ui.layouts.master')

@php
    // Mismo criterio que la pantalla de pestañas: sin permiso de edición no
    // se muestran los botones que agregan contactos.
    $auCanEdit = auth()->user()?->canPerform('marketing-integracion', 'edit') ?? false;
@endphp

@section('title', 'Contactos — ' . $emailContactList->name)

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => $emailContactList->name,
        'subtitle' => $emailContactList->members_count . ' contacto(s) suscrito(s) · ' . $emailContactList->unsubscribed_count . ' dado(s) de baja',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
            ['label' => 'Email Marketing', 'url' => route('admin.email-marketing.index', ['tab' => 'listas'])],
            ['label' => $emailContactList->name],
        ],
        'actions' => '<a href="' . route('admin.email-marketing.index', ['tab' => 'listas']) . '" class="au-btn"><i class="fas fa-arrow-left"></i> Volver</a>',
    ])

    <div class="au-flex au-gap-2" style="justify-content:space-between;align-items:flex-start;margin:0 0 14px">
        <div class="au-help-text" style="display:block;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
            Importar la misma fuente varias veces no duplica contactos: los correos que ya están en la lista se omiten (incluidos los dados de baja, para no volver a suscribir a quien pidió no recibir correos).
        </div>
        @if ($auCanEdit)
            <div class="au-flex au-gap-2" style="white-space:nowrap">
                <button type="button" class="au-btn" id="ecl-import-customers-btn">Importar clientes del sitio</button>
                <button type="button" class="au-btn" id="ecl-import-aspel-btn">Importar clientes de Aspel</button>
                <button type="button" class="au-btn au-btn-primary" id="ecl-add-manual-btn">+ Agregar contacto</button>
            </div>
        @endif
    </div>

    <div id="ecl-members-table"></div>

    {{-- Modal: agregar contacto manual ---------------------------------- --}}
    <div class="au-modal-overlay" id="ecl-manual-modal">
        <div class="au-modal">
            <div class="au-modal-head">
                <div class="au-modal-icon"><i class="fas fa-user-plus"></i></div>
                <div style="display:flex;flex-direction:column;gap:6px">
                    <div class="au-modal-title">Agregar contacto</div>
                    <div class="au-modal-text">Para alguien que no tiene cuenta en el sitio ni existe en Aspel.</div>
                </div>
            </div>
            <form id="ecl-manual-form" style="padding:0 22px;display:flex;flex-direction:column;gap:14px">
                <div class="au-field">
                    <label class="au-label">Correo<span class="au-required-mark">*</span></label>
                    <input type="email" class="au-input" name="email" required placeholder="cliente@ejemplo.com">
                </div>
                <div class="au-field">
                    <label class="au-label">Nombre</label>
                    <input type="text" class="au-input" name="name" placeholder="Opcional — se usa en @{{contact.name}}">
                </div>
                <div class="au-field">
                    <label class="au-label">Empresa</label>
                    <input type="text" class="au-input" name="company" placeholder="Opcional — se usa en @{{contact.company}}">
                </div>
            </form>
            <div class="au-modal-actions">
                <button type="button" class="au-btn" data-ecl-close>Cancelar</button>
                <button type="button" class="au-btn au-btn-primary" id="ecl-manual-submit">Agregar</button>
            </div>
        </div>
    </div>

    {{-- Modal: importar clientes del sitio ------------------------------- --}}
    <div class="au-modal-overlay" id="ecl-customers-modal">
        <div class="au-modal">
            <div class="au-modal-head">
                <div class="au-modal-icon"><i class="fas fa-users"></i></div>
                <div style="display:flex;flex-direction:column;gap:6px">
                    <div class="au-modal-title">Importar clientes del sitio</div>
                    <div class="au-modal-text">Toma los clientes registrados en la tienda y los agrega a esta lista.</div>
                </div>
            </div>
            <form id="ecl-customers-form" style="padding:0 22px;display:flex;flex-direction:column;gap:14px">
                <div class="au-field">
                    <label class="au-label">Qué clientes</label>
                    <select class="au-select" name="scope">
                        <option value="compradores">Solo los que ya compraron</option>
                        <option value="todos">Todos los clientes registrados</option>
                    </select>
                    <span class="au-help-text">"Ya compraron" usa el mismo criterio del resto del sistema: al menos una orden pagada y no cancelada.</span>
                </div>
            </form>
            <div class="au-modal-actions">
                <button type="button" class="au-btn" data-ecl-close>Cancelar</button>
                <button type="button" class="au-btn au-btn-primary" id="ecl-customers-submit">Importar</button>
            </div>
        </div>
    </div>

    {{-- Modal: importar clientes de Aspel -------------------------------- --}}
    <div class="au-modal-overlay" id="ecl-aspel-modal">
        <div class="au-modal">
            <div class="au-modal-head">
                <div class="au-modal-icon"><i class="fas fa-database"></i></div>
                <div style="display:flex;flex-direction:column;gap:6px">
                    <div class="au-modal-title">Importar clientes de Aspel</div>
                    <div class="au-modal-text">Toma los clientes sincronizados desde Aspel que tengan correo registrado.</div>
                </div>
            </div>
            <div style="padding:0 22px">
                <div class="au-help-text" style="display:block;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
                    Los clientes de Aspel sin correo no se pueden importar. Si un cliente de Aspel ya está ligado a una cuenta del sitio, el contacto queda identificado por los dos lados.
                </div>
            </div>
            <div class="au-modal-actions">
                <button type="button" class="au-btn" data-ecl-close>Cancelar</button>
                <button type="button" class="au-btn au-btn-primary" id="ecl-aspel-submit">Importar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        const eclTable = new AU.AdminTable({
            el: '#ecl-members-table',
            endpoint: '{{ route('admin.email-lists.members.table-data', $emailContactList->id) }}',
            rowSelectable: false,
        });

        const eclModals = {
            manual: document.getElementById('ecl-manual-modal'),
            customers: document.getElementById('ecl-customers-modal'),
            aspel: document.getElementById('ecl-aspel-modal'),
        };

        function eclOpen(key) { eclModals[key].classList.add('is-open'); }
        function eclCloseAll() { Object.values(eclModals).forEach((m) => m.classList.remove('is-open')); }

        document.querySelectorAll('[data-ecl-close]').forEach((btn) => btn.addEventListener('click', eclCloseAll));
        Object.values(eclModals).forEach((modal) => {
            modal.addEventListener('click', (e) => { if (e.target === modal) eclCloseAll(); });
        });

        // Los botones no existen para un rol con solo permiso de Ver, de ahí
        // el optional chaining — la pantalla sigue sirviendo como consulta.
        document.getElementById('ecl-add-manual-btn')?.addEventListener('click', () => eclOpen('manual'));
        document.getElementById('ecl-import-customers-btn')?.addEventListener('click', () => eclOpen('customers'));
        document.getElementById('ecl-import-aspel-btn')?.addEventListener('click', () => eclOpen('aspel'));

        // Una sola rutina para las tres acciones: mandar, avisar y refrescar
        // la tabla. El backend siempre responde {status, message} — un
        // duplicado o una lista vacía llegan como status 'error' con su
        // mensaje, no como excepción.
        async function eclSubmit(button, url, body) {
            const originalLabel = button.textContent;
            button.disabled = true;
            button.textContent = 'Trabajando...';
            try {
                const data = await AU.request(url, { method: 'POST', body });
                AU.toast[data.status === 'success' ? 'success' : 'error'](data.message || 'Listo');
                if (data.status === 'success') {
                    eclCloseAll();
                    eclTable.fetchData();
                }
            } catch (err) {
                AU.toast.error((err.data && err.data.message) || 'Ocurrió un error');
            } finally {
                button.disabled = false;
                button.textContent = originalLabel;
            }
        }

        document.getElementById('ecl-manual-submit').addEventListener('click', (e) => {
            const form = document.getElementById('ecl-manual-form');
            if (!form.reportValidity()) return;
            eclSubmit(e.currentTarget, '{{ route('admin.email-lists.members.manual', $emailContactList->id) }}', new FormData(form))
                .then(() => form.reset());
        });

        document.getElementById('ecl-customers-submit').addEventListener('click', (e) => {
            eclSubmit(e.currentTarget, '{{ route('admin.email-lists.members.import-customers', $emailContactList->id) }}', new FormData(document.getElementById('ecl-customers-form')));
        });

        document.getElementById('ecl-aspel-submit').addEventListener('click', (e) => {
            eclSubmit(e.currentTarget, '{{ route('admin.email-lists.members.import-aspel', $emailContactList->id) }}', {});
        });
    </script>
@endpush

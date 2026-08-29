@extends('admin-ui.layouts.master')

@php
    // La pestaña activa viaja en la query string (?tab=...) — así, cuando un
    // modal de Crear/Editar termina y AU.FormModal recarga la página (ver
    // public/admin-ui/js/admin.js), se vuelve a la misma pestaña en vez de
    // regresar siempre a Plantillas.
    $auTabs = [
        'plantillas' => 'Plantillas',
        'listas' => 'Listas',
        'campanas' => 'Campañas',
        'secuencias' => 'Secuencias',
    ];
    $activeTab = array_key_exists(request('tab'), $auTabs) ? request('tab') : 'plantillas';

    // Los botones de acción se ocultan según el permiso granular del rol,
    // además del bloqueo que ya hace el middleware can-access-module en cada
    // controlador: un rol con solo "Ver" no debe siquiera ver el botón.
    $auCanCreate = auth()->user()?->canPerform('marketing-integracion', 'create') ?? false;
@endphp

@section('title', 'Email Marketing')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Email Marketing',
        'subtitle' => 'Plantillas, listas de contactos, campañas y secuencias de seguimiento',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
            ['label' => 'Email Marketing'],
        ],
    ])

    <div class="au-tabs" id="em-tabs">
        @foreach ($auTabs as $key => $label)
            <div class="au-tab {{ $activeTab === $key ? 'is-active' : '' }}" data-em-panel="{{ $key }}">{{ $label }}</div>
        @endforeach
    </div>

    {{-- Plantillas ------------------------------------------------------ --}}
    <div id="em-panel-plantillas" {{ $activeTab === 'plantillas' ? '' : 'hidden' }}>
        <div class="au-flex au-gap-2" style="justify-content:space-between;align-items:flex-start;margin:0 0 14px">
            <div class="au-help-text" style="display:block;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
                El contenido de los correos. Una plantilla sirve igual para los tres flujos — el "Tipo" solo la clasifica para encontrarla más rápido. Las de tipo Individual son las que consume <code>GET /api/marketing/email/{userId}</code>.
            </div>
            @if ($auCanCreate)
                {{-- Sigue siendo un enlace real a la página completa: si el JS
                     no cargara, el botón lleva al editor de toda la vida en vez
                     de quedarse muerto. El script de abajo lo intercepta para
                     abrirlo aquí mismo. --}}
                <a href="{{ route('admin.email-templates.create') }}" class="au-btn au-btn-primary" style="white-space:nowrap"
                   data-em-editor-open data-em-fragment="{{ route('admin.email-templates.create-fragment') }}">+ Nueva plantilla</a>
            @endif
        </div>
        <div id="em-templates-table"></div>
    </div>

    {{-- Listas ---------------------------------------------------------- --}}
    <div id="em-panel-listas" {{ $activeTab === 'listas' ? '' : 'hidden' }}>
        <div class="au-flex au-gap-2" style="justify-content:space-between;align-items:flex-start;margin:0 0 14px">
            <div class="au-help-text" style="display:block;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
                A quién se le envía. Una lista se llena importando clientes del sitio, clientes de Aspel o agregando contactos a mano. Entra a "Ver contactos" para administrarlos.
            </div>
            @if ($auCanCreate)
            <button type="button" class="au-btn au-btn-primary" style="white-space:nowrap" data-au-open-modal="{{ json_encode([
                'title' => 'Crear lista de contactos',
                'subtitle' => 'Nueva lista vacía',
                'icon' => 'fas fa-address-book',
                'narrow' => true,
                'fragmentUrl' => route('admin.email-lists.create-fragment'),
                'submitUrl' => route('admin.email-lists.store'),
                'method' => 'POST',
            ]) }}">+ Nueva lista</button>
            @endif
        </div>
        <div id="em-lists-table"></div>
    </div>

    {{-- Campañas -------------------------------------------------------- --}}
    <div id="em-panel-campanas" {{ $activeTab === 'campanas' ? '' : 'hidden' }}>
        <div class="au-flex au-gap-2" style="justify-content:space-between;align-items:flex-start;margin:0 0 14px">
            <div class="au-help-text" style="display:block;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
                Un envío masivo = una plantilla + una lista. Se crea como borrador; al "Programar" se congela la lista de destinatarios y n8n la recoge la próxima vez que pregunte. El envío lo hace n8n, no este panel.
            </div>
            @if ($auCanCreate)
            <button type="button" class="au-btn au-btn-primary" style="white-space:nowrap" data-au-open-modal="{{ json_encode([
                'title' => 'Crear campaña',
                'subtitle' => 'Se guarda como borrador',
                'icon' => 'fas fa-paper-plane',
                'narrow' => true,
                'fragmentUrl' => route('admin.email-campaigns.create-fragment'),
                'submitUrl' => route('admin.email-campaigns.store'),
                'method' => 'POST',
            ]) }}">+ Nueva campaña</button>
            @endif
        </div>
        <div id="em-campaigns-table"></div>
    </div>

    {{-- Secuencias ------------------------------------------------------ --}}
    <div id="em-panel-secuencias" {{ $activeTab === 'secuencias' ? '' : 'hidden' }}>
        <div class="au-flex au-gap-2" style="justify-content:space-between;align-items:flex-start;margin:0 0 14px">
            <div class="au-help-text" style="display:block;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
                Seguimiento automático de cotizaciones: cada cotización se inscribe en las secuencias activas y va recibiendo los pasos según los días de espera. Si el cliente compra, sale del seguimiento y sus pasos pendientes ya no se envían.
            </div>
            @if ($auCanCreate)
                <a href="{{ route('admin.email-sequences.create') }}" class="au-btn au-btn-primary" style="white-space:nowrap">+ Nueva secuencia</a>
            @endif
        </div>
        <div id="em-sequences-table"></div>
    </div>

    {{-- Panel del editor de plantillas. Vive al lado de los paneles de las
         pestañas: al abrirlo se ocultan todos ellos y la barra de pestañas se
         queda visible, tal como en el diseño de referencia. El contenido lo
         trae por AJAX admin.email-templates.{create,edit}-fragment. --}}
    <div id="em-editor-host" hidden>
        <div id="em-editor-slot"></div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <link href="{{ asset('admin-ui/css/components/email-builder.css') }}?v={{ filemtime(public_path('admin-ui/css/components/email-builder.css')) }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="{{ asset('admin-ui/js/email-builder/email-builder.js') }}?v={{ filemtime(public_path('admin-ui/js/email-builder/email-builder.js')) }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        // Cada pestaña monta su propia AU.AdminTable, y solo la primera vez
        // que se abre: entrar a Email Marketing no dispara las cuatro
        // consultas de golpe (mismo criterio que el panel de Duplicados de
        // la Galería de Medios).
        const emEditorHost = document.getElementById('em-editor-host');
        const emEditorSlot = document.getElementById('em-editor-slot');

        const emTables = {
            plantillas: { el: '#em-templates-table', endpoint: '{{ route('admin.email-templates.table-data') }}' },
            listas: { el: '#em-lists-table', endpoint: '{{ route('admin.email-lists.table-data') }}' },
            campanas: { el: '#em-campaigns-table', endpoint: '{{ route('admin.email-campaigns.table-data') }}' },
            secuencias: { el: '#em-sequences-table', endpoint: '{{ route('admin.email-sequences.table-data') }}' },
        };
        const emMounted = {};

        function emMount(key) {
            if (emMounted[key] || !emTables[key]) return;
            emMounted[key] = new AU.AdminTable({
                el: emTables[key].el,
                endpoint: emTables[key].endpoint,
                rowSelectable: false,
            });
        }

        function emShow(key) {
            // Cambiar de pestaña sale del editor: si no, el panel del editor
            // se quedaría abierto detrás de la tabla de otra pestaña.
            emEditorHide();
            Object.keys(emTables).forEach((k) => {
                const panel = document.getElementById('em-panel-' + k);
                if (panel) panel.hidden = k !== key;
            });
            document.querySelectorAll('#em-tabs .au-tab').forEach((tab) => {
                tab.classList.toggle('is-active', tab.getAttribute('data-em-panel') === key);
            });
            emMount(key);

            // La pestaña activa se refleja en la URL para que sobreviva a la
            // recarga que hace AU.FormModal al guardar.
            const url = new URL(window.location.href);
            url.searchParams.set('tab', key);
            window.history.replaceState({}, '', url);
        }

        document.querySelectorAll('#em-tabs .au-tab').forEach((tab) => {
            tab.addEventListener('click', () => emShow(tab.getAttribute('data-em-panel')));
        });

        emMount(@json($activeTab));

        // ============================================================
        // Editor de plantillas dentro de la pestaña
        // ============================================================
        // Abre el editor sin cambiar de página: se piden por AJAX los
        // fragmentos admin.email-templates.{create,edit}-fragment (el mismo
        // parcial que usa la página completa), se inyectan aquí y se monta
        // AU.EmailBuilder sobre ellos. La barra de pestañas se queda visible;
        // lo que se oculta son los paneles de las cuatro tablas.
        //
        // Todo esto es una mejora encima de enlaces que ya funcionan solos:
        // tanto "+ Nueva plantilla" como el "Editar" de cada fila siguen
        // apuntando a la página completa, así que si el JS fallara el módulo
        // no se queda inservible.
        function emEditorHide() {
            if (emEditorHost.hidden) return;
            emEditorHost.hidden = true;
            // Se vacía el contenedor a propósito: el editor usa ids fijos
            // (#eb-editor, #eb-canvas-blocks, ...) y dejar el anterior en el
            // DOM haría que un segundo montaje encontrara ids duplicados.
            emEditorSlot.innerHTML = '';
        }

        function emEditorClose() {
            emShow('plantillas');
        }

        async function emEditorOpen(fragmentUrl) {
            Object.keys(emTables).forEach((k) => {
                const panel = document.getElementById('em-panel-' + k);
                if (panel) panel.hidden = true;
            });
            emEditorHost.hidden = false;
            emEditorSlot.innerHTML = '<div class="au-table-empty">Cargando editor…</div>';

            let html;
            try {
                const res = await fetch(fragmentUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                html = await res.text();
            } catch (err) {
                emEditorSlot.innerHTML = '<div class="au-table-empty">No se pudo cargar el editor.</div>';
                AU.toast.error('No se pudo cargar el editor');
                return;
            }

            emEditorSlot.innerHTML = html;
            AU.EmailBuilder.mount(AU.EmailBuilder.readConfig(emEditorSlot));

            emEditorSlot.querySelector('[data-eb-cancel]').addEventListener('click', emEditorClose);
            emEditorSlot.querySelector('#eb-form').addEventListener('submit', emEditorSubmit);
        }

        async function emEditorSubmit(e) {
            e.preventDefault();
            const form = e.currentTarget;
            const saveBtn = form.querySelector('button[type="submit"]');
            const originalLabel = saveBtn.textContent;
            saveBtn.disabled = true;
            saveBtn.textContent = 'Guardando…';

            try {
                // FormData sobre el mismo <form> que usaría el envío normal —
                // incluye _token y, al editar, el _method=PUT que ya trae el
                // formulario.
                const data = await AU.request(form.getAttribute('action'), {
                    method: 'POST',
                    body: new FormData(form),
                });
                AU.toast.success((data && data.message) || 'Guardado exitosamente');
                emEditorClose();
                if (emMounted.plantillas) emMounted.plantillas.fetchData();
            } catch (err) {
                if (err.status === 422 && err.data && err.data.errors) {
                    // El editor no tiene el mismo armado de errores en línea
                    // que AU.FormModal, así que los mensajes se muestran como
                    // avisos — es lo que hay que ver de todos modos, ya que
                    // los campos viven en la barra superior, a la vista.
                    Object.values(err.data.errors).flat().forEach((msg) => AU.toast.error(msg));
                } else {
                    AU.toast.error((err.data && err.data.message) || 'Ocurrió un error al guardar');
                }
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = originalLabel;
            }
        }

        // "+ Nueva plantilla"
        document.querySelectorAll('[data-em-editor-open]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                emEditorOpen(btn.getAttribute('data-em-fragment'));
            });
        });

        // "Editar" de cada fila de la tabla de plantillas. La tabla se
        // redibuja sola en cada consulta, así que el click se escucha en el
        // contenedor (delegación) en vez de en cada enlace.
        const emEditUrlPattern = /\/email-templates\/(\d+)\/edit$/;
        document.getElementById('em-templates-table').addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (!link) return;
            const match = emEditUrlPattern.exec(link.getAttribute('href') || '');
            if (!match) return;
            e.preventDefault();
            emEditorOpen('{{ url('admin/email-templates') }}/' + match[1] + '/edit-fragment');
        });
    </script>
@endpush

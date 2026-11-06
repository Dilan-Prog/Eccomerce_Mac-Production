@extends('admin-ui.layouts.master')

@section('title', 'Solicitudes web')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Solicitudes web',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Administrar Sitio Web'],
            ['label' => 'Solicitudes web'],
        ],
    ])

    <div class="au-help-text" style="display:block;margin:-4px 0 16px;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
        Aquí llegan las solicitudes de los formularios públicos (Asociados, Calibración y puesta en marcha, Medición y Cotización).
        Haz clic en una fila para ver todos sus datos, el origen del clic y el estado de los avisos por correo y webhook.
        Si un aviso quedó pendiente o con error, usa «Reenviar aviso».
    </div>

    <section class="au-card" style="margin-bottom:16px" aria-labelledby="wl-notify-title">
        <div class="au-card-header">
            <h2 class="au-card-title" id="wl-notify-title">Correos que reciben el aviso</h2>
        </div>
        <div class="au-card-body">
            <div class="au-field">
                <label for="wl-notify-to">Enviar el aviso de cada solicitud a</label>
                <div class="au-flex" style="gap:8px;flex-wrap:wrap;align-items:flex-start">
                    <input type="text" class="au-input" id="wl-notify-to" style="flex:1 1 320px;min-width:0" autocomplete="off" spellcheck="false"
                           value="{{ $notifyTo }}" placeholder="contacto@macdelnorte.com" aria-describedby="wl-notify-help wl-notify-status">
                    <button type="button" class="au-btn au-btn-primary" id="wl-notify-save"><i class="fas fa-save"></i> Guardar</button>
                </div>
                <div class="au-help-text" id="wl-notify-help" style="margin-top:6px">
                    Puedes poner varios correos separados por comas (máximo {{ \App\Support\WebLeads\WebLeadNotifier::MAX_RECIPIENTS }}).
                    Si lo dejas vacío se usa <code>WEB_LEADS_NOTIFY_TO</code> del <code>.env</code> del servidor.
                </div>
                <div class="au-help-text" id="wl-notify-status" style="margin-top:6px" role="status">
                    @if ($recipientsSource === 'none')
                        Ahora mismo <strong>no hay ningún correo configurado</strong>: las solicitudes se guardan aquí, pero no se manda aviso por correo.
                    @else
                        Los avisos se envían ahora a: <strong>{{ implode(', ', $recipients) }}</strong>
                        ({{ $recipientsSource === 'panel' ? 'configurado en este panel' : 'tomado del .env del servidor' }}).
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="au-flex" style="gap:8px;align-items:center;margin-bottom:12px;flex-wrap:wrap">
        <label for="wl-form-type" class="au-help-text" style="margin:0">Formulario</label>
        <select id="wl-form-type" class="au-select" style="width:auto">
            <option value="">Todos los formularios</option>
            @foreach ($formTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div id="web-leads-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        const webLeadsTable = new AU.AdminTable({
            el: '#web-leads-table',
            endpoint: '{{ route('admin.web-leads.table-data') }}',
            rowSelectable: false,
            rowDetailsUrl: (id) => '{{ url('admin/web-leads') }}/' + id,
            rowDetailsTitle: (row) => 'Solicitud: ' + ((row && row.cells && row.cells.form_type && row.cells.form_type.label) || 'web'),
            initialFilter: @json(request('filter')),
        });

        // Correos que reciben el aviso: se guardan en BD desde aqui (tienen prioridad sobre el .env).
        (function () {
            const input = document.getElementById('wl-notify-to');
            const button = document.getElementById('wl-notify-save');
            const status = document.getElementById('wl-notify-status');
            const SOURCES = { panel: 'configurado en este panel', env: 'tomado del .env del servidor' };

            button.addEventListener('click', async () => {
                button.disabled = true;
                try {
                    const data = await AU.request('{{ route('admin.web-leads.settings') }}', {
                        method: 'POST',
                        body: { notify_to: input.value },
                    });
                    AU.toast.success(data.message || 'Guardado');
                    const list = (data.recipients || []);
                    status.textContent = list.length
                        ? 'Los avisos se envían ahora a: ' + list.join(', ') + ' (' + (SOURCES[data.source] || '') + ').'
                        : 'Ahora mismo no hay ningún correo configurado: las solicitudes se guardan aquí, pero no se manda aviso por correo.';
                    input.value = data.source === 'panel' ? list.join(', ') : '';
                } catch (error) {
                    AU.toast.error(error.message || 'No se pudo guardar');
                } finally {
                    button.disabled = false;
                }
            });
        })();

        // Filtro por formulario: se agrega al query string de la tabla sin tocar el motor compartido.
        (function () {
            const select = document.getElementById('wl-form-type');
            const baseBuildQuery = webLeadsTable.buildQuery.bind(webLeadsTable);
            webLeadsTable.buildQuery = function () {
                const q = baseBuildQuery();
                return select.value ? q + '&form_type=' + encodeURIComponent(select.value) : q;
            };
            select.addEventListener('change', () => {
                webLeadsTable.state.page = 1;
                webLeadsTable.fetchData();
            });
        })();
    </script>
@endpush

@extends('admin-ui.layouts.master')

@section('title', 'Seguimiento De Conversiones')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Publicidad / Seguimiento de Conversión',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Seguimiento De Conversiones'],
        ],
    ])

    {{-- Resumen del rango elegido (tarjetas + desgloses). Se llena por JS desde admin.track-conversion.summary. --}}
    <div class="au-card" style="margin-bottom:16px">
        <div class="au-card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div class="au-card-title">Resumen</div>
            <div class="au-flex" style="gap:8px;align-items:center">
                <label for="tc-range" class="au-help-text" style="margin:0">Periodo</label>
                <select id="tc-range" class="au-select" style="width:auto">
                    <option value="today">Hoy</option>
                    <option value="7">Últimos 7 días</option>
                    <option value="30" selected>Últimos 30 días</option>
                    <option value="90">Últimos 90 días</option>
                    <option value="all">Todo el historial</option>
                </select>
            </div>
        </div>
        <div class="au-card-body">
            <div id="tc-summary-error" class="au-help-text" style="display:none;color:var(--au-critical)"></div>

            <div class="au-stat-grid" id="tc-stats">
                <div class="au-stat-card"><div class="au-stat-icon is-info"><i class="fas fa-eye"></i></div>
                    <div><div class="au-stat-label">Visitas registradas</div><div class="au-stat-value" data-stat="visits">—</div></div></div>
                <div class="au-stat-card"><div class="au-stat-icon is-success"><i class="fas fa-bullseye"></i></div>
                    <div><div class="au-stat-label">Conversiones</div><div class="au-stat-value" data-stat="conversions">—</div></div></div>
                <div class="au-stat-card"><div class="au-stat-icon is-warning"><i class="fas fa-shopping-cart"></i></div>
                    <div><div class="au-stat-label">Carrito y cotización (interacciones)</div><div class="au-stat-value" data-stat="interactions">—</div></div></div>
                <div class="au-stat-card"><div class="au-stat-icon is-info"><i class="fas fa-file-invoice"></i></div>
                    <div><div class="au-stat-label">Cotizaciones iniciadas</div><div class="au-stat-value" data-stat="quote_started">—</div></div></div>
                <div class="au-stat-card"><div class="au-stat-icon is-success"><i class="fas fa-file-pdf"></i></div>
                    <div><div class="au-stat-label">Cotizaciones generadas</div><div class="au-stat-value" data-stat="quote_submitted">—</div></div></div>
                <div class="au-stat-card"><div class="au-stat-icon is-critical"><i class="fas fa-file-excel"></i></div>
                    <div><div class="au-stat-label">Cotizaciones abandonadas</div><div class="au-stat-value" data-stat="quote_abandoned">—</div></div></div>
                <div class="au-stat-card"><div class="au-stat-icon"><i class="fas fa-percent"></i></div>
                    <div><div class="au-stat-label">Conversiones por visita</div><div class="au-stat-value" data-stat="conversion_rate">—</div></div></div>
                <div class="au-stat-card"><div class="au-stat-icon is-warning"><i class="fas fa-user-clock"></i></div>
                    <div><div class="au-stat-label">Sesiones únicas</div><div class="au-stat-value" data-stat="sessions">—</div></div></div>
                <div class="au-stat-card"><div class="au-stat-icon is-info"><i class="fab fa-google"></i></div>
                    <div><div class="au-stat-label">Visitas de Google Ads</div><div class="au-stat-value" data-stat="google_visits">—</div></div></div>
                <div class="au-stat-card"><div class="au-stat-icon is-success"><i class="fab fa-google"></i></div>
                    <div><div class="au-stat-label">Conversiones de Google Ads</div><div class="au-stat-value" data-stat="google_conversions">—</div></div></div>
            </div>

            <div style="margin-top:20px">
                <div class="au-card-title" style="margin-bottom:8px">Por día</div>
                <div id="tc-daily" style="display:flex;align-items:flex-end;gap:3px;height:120px;overflow-x:auto;padding-bottom:2px"></div>
                <div class="au-help-text" style="margin-top:6px">
                    <span style="display:inline-block;width:10px;height:10px;background:var(--au-blue-500);border-radius:2px"></span> Visitas
                    &nbsp;<span style="display:inline-block;width:10px;height:10px;background:var(--au-success);border-radius:2px"></span> Conversiones
                </div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px;margin-bottom:16px">
        <div class="au-card">
            <div class="au-card-header"><div class="au-card-title">Por tipo de evento</div></div>
            <div class="au-table-wrap"><table class="au-table"><thead><tr><th>Tipo</th><th class="au-text-right">Total</th><th class="au-text-right">Con Google</th></tr></thead>
                <tbody id="tc-by-type"></tbody></table></div>
        </div>
        <div class="au-card">
            <div class="au-card-header"><div class="au-card-title">Por fuente / medio (top 10)</div></div>
            <div class="au-table-wrap"><table class="au-table"><thead><tr><th>Fuente</th><th>Medio</th><th class="au-text-right">Visitas</th><th class="au-text-right">Conv.</th></tr></thead>
                <tbody id="tc-by-source"></tbody></table></div>
        </div>
        <div class="au-card">
            <div class="au-card-header"><div class="au-card-title">Por campaña (top 10)</div></div>
            <div class="au-table-wrap"><table class="au-table"><thead><tr><th>Campaña</th><th class="au-text-right">Visitas</th><th class="au-text-right">Conv.</th></tr></thead>
                <tbody id="tc-by-campaign"></tbody></table></div>
        </div>
        <div class="au-card">
            <div class="au-card-header"><div class="au-card-title">Por página de entrada (top 10)</div></div>
            <div class="au-table-wrap"><table class="au-table"><thead><tr><th>Landing</th><th class="au-text-right">Visitas</th><th class="au-text-right">Conv.</th></tr></thead>
                <tbody id="tc-by-landing"></tbody></table></div>
        </div>
    </div>

    <div class="au-help-text" style="margin-bottom:8px">
        Clic en una fila para ver todos sus datos y el recorrido de su sesión. Usa las pestañas para separar visitas de conversiones
        o por origen del clic; el botón de columnas muestra u oculta campos (gbraid, wbraid, fbclid, ID de evento, URL completa…).
    </div>
    <div id="track-conversion-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#track-conversion-table',
            endpoint: '{{ route('admin.track-conversion.table-data') }}',
            exportEndpoint: '{{ route('admin.track-conversion.export') }}',
            rowSelectable: false,
            rowDetailsUrl: (id) => '{{ url('admin/track-conversion') }}/' + id + '/details-fragment',
            rowDetailsTitle: (row) => 'Detalle: ' + ((row && row.cells && row.cells.type) || 'registro'),
        });

        (function () {
            // Los valores vienen de la URL del visitante (utm, landing…): se escapan SIEMPRE antes de pintarse.
            const esc = (v) => String(v == null ? '' : v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const num = (n) => Number(n || 0).toLocaleString('es-MX');
            const summaryUrl = '{{ route('admin.track-conversion.summary') }}';
            const select = document.getElementById('tc-range');
            const errorBox = document.getElementById('tc-summary-error');

            function fillRows(id, rows, cols, emptyCols) {
                const tbody = document.getElementById(id);
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="' + emptyCols + '" class="au-help-text">Sin datos en este periodo.</td></tr>';
                    return;
                }
                tbody.innerHTML = rows.map((r) => '<tr>' + cols.map((c) => {
                    const cls = c.num ? ' class="au-text-right au-mono"' : (c.mono ? ' class="au-mono"' : ' style="word-break:break-all"');
                    return '<td' + cls + '>' + (c.num ? num(r[c.key]) : esc(r[c.key] || '—')) + '</td>';
                }).join('') + '</tr>').join('');
            }

            function drawDaily(days) {
                const box = document.getElementById('tc-daily');
                if (!days.length) {
                    box.innerHTML = '<span class="au-help-text">Sin datos en este periodo.</span>';
                    return;
                }
                const max = Math.max(1, ...days.map((d) => Math.max(d.visits, d.conversions)));
                box.innerHTML = days.map((d) => {
                    const hv = Math.max(2, Math.round(d.visits / max * 110));
                    const hc = Math.max(d.conversions ? 2 : 0, Math.round(d.conversions / max * 110));
                    return '<div title="' + esc(d.day) + ' — ' + d.visits + ' visitas, ' + d.conversions + ' conversiones" style="display:flex;align-items:flex-end;gap:1px;flex:none">'
                        + '<span style="width:7px;height:' + hv + 'px;background:var(--au-blue-500);border-radius:2px 2px 0 0"></span>'
                        + '<span style="width:7px;height:' + hc + 'px;background:var(--au-success);border-radius:2px 2px 0 0"></span></div>';
                }).join('');
            }

            async function load() {
                errorBox.style.display = 'none';
                try {
                    const data = await AU.request(summaryUrl + '?range=' + encodeURIComponent(select.value));
                    const t = data.totals;
                    const set = (k, v) => { const el = document.querySelector('[data-stat="' + k + '"]'); if (el) el.textContent = v; };
                    set('visits', num(t.visits));
                    set('conversions', num(t.conversions));
                    set('interactions', num(t.interactions));
                    set('quote_started', num(t.quote_started));
                    set('quote_submitted', num(t.quote_submitted));
                    set('quote_abandoned', num(t.quote_abandoned));
                    set('conversion_rate', t.conversion_rate === null ? '—' : t.conversion_rate + '%');
                    set('sessions', num(t.sessions));
                    set('google_visits', num(t.google_visits));
                    set('google_conversions', num(t.google_conversions));
                    fillRows('tc-by-type', data.by_type, [{ key: 'type', mono: true }, { key: 'total', num: true }, { key: 'with_google', num: true }], 3);
                    fillRows('tc-by-source', data.by_source, [{ key: 'source' }, { key: 'medium' }, { key: 'visits', num: true }, { key: 'conversions', num: true }], 4);
                    fillRows('tc-by-campaign', data.by_campaign, [{ key: 'campaign' }, { key: 'visits', num: true }, { key: 'conversions', num: true }], 3);
                    fillRows('tc-by-landing', data.by_landing, [{ key: 'landing_page' }, { key: 'visits', num: true }, { key: 'conversions', num: true }], 3);
                    drawDaily(data.daily);
                } catch (e) {
                    errorBox.textContent = 'No se pudo cargar el resumen: ' + (e && e.message ? e.message : 'error desconocido');
                    errorBox.style.display = 'block';
                }
            }

            select.addEventListener('change', load);
            load();
        })();
    </script>
@endpush

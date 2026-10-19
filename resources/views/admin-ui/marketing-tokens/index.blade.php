@extends('admin-ui.layouts.master')

@section('title', 'Marketing')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Marketing — Tokens API',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
            ['label' => 'Integración'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear token',
            'subtitle' => 'Nuevo acceso para n8n',
            'icon' => 'fas fa-key',
            'fragmentUrl' => route('admin.marketing-tokens.create-fragment'),
            'submitUrl' => route('admin.marketing-tokens.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div class="au-help-text" style="display:block;margin:-4px 0 16px;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
        Los tokens de abajo autentican las rutas <code>GET /api/marketing/*</code>. n8n debe mandar el header <code>Authorization: Bearer {key_id}.{secret}</code> con un token en estado Activo. Sistema aislado de los tokens de Aspel — ninguno funciona en las rutas del otro.
    </div>

    @isset($attribution)
        @php
            $attrUrl = $attribution['lookup_url'];
            $attrByFolio = $attrUrl . '?folio={{ $json.folio }}';
            $attrByWindow = $attrUrl . '?type=phone_click&from={{ $json.from }}&to={{ $json.to }}';
        @endphp
        <section class="au-card" style="margin:0 0 16px" aria-labelledby="attribution-api-title">
            <div class="au-card-header">
                <h2 class="au-card-title" id="attribution-api-title">API de atribución (Google Ads)</h2>
                @if(!$attribution['token_configured'])
                    <span class="au-badge au-badge-critical">Token no configurado</span>
                @elseif(!$attribution['token_length_ok'])
                    <span class="au-badge au-badge-warning">Token demasiado corto</span>
                @else
                    <span class="au-badge au-badge-success">Token configurado</span>
                @endif
            </div>
            <div class="au-card-body">
                <p class="au-help-text" style="margin:0 0 12px">
                    Para que n8n recupere de qué anuncio vino un contacto, pega esta URL en el nodo <strong>HTTP Request</strong> (método <code>GET</code>) que consulta el sitio.
                    Esta API <strong>no usa los tokens de esta tabla</strong>: usa su propio token, <code>ATTRIBUTION_API_TOKEN</code> del <code>.env</code> del servidor (mínimo {{ $attribution['min_length'] }} caracteres).
                </p>

                @foreach([
                    ['URL base', $attrUrl],
                    ['Por folio (WhatsApp / correo / cotización)', $attrByFolio],
                    ['Por ventana de tiempo (llamadas, máx. 15 min)', $attrByWindow],
                ] as [$label, $value])
                    <div class="au-field" style="margin-bottom:10px">
                        <label class="au-label">{{ $label }}</label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <input type="text" class="au-input" readonly value="{{ $value }}" style="font-family:monospace;font-size:12px" onclick="this.select()">
                            <button type="button" class="au-btn au-btn-sm" data-au-copy="{{ $value }}"><i class="fas fa-copy"></i> Copiar</button>
                        </div>
                    </div>
                @endforeach

                <div class="au-field" style="margin-bottom:10px">
                    <label class="au-label">Encabezado de autenticación</label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="text" class="au-input" readonly value="Authorization: Bearer &lt;ATTRIBUTION_API_TOKEN&gt;" style="font-family:monospace;font-size:12px" onclick="this.select()">
                    </div>
                </div>

                <p class="au-help-text" style="margin:0">
                    La respuesta es JSON y trae <code>gclid</code>, <code>utm_*</code>, <code>attribution_source</code> e <code>is_test</code>: n8n debe <strong>ignorar</strong> los eventos con <code>is_test = true</code> antes de subir cualquier conversión a Google Ads.
                    El valor <code>@{{ $json.folio }}</code> es una expresión de n8n con el folio <code>MN…</code> que extrajiste del mensaje. Para probar el flujo completo usa el
                    <a href="{{ route('admin.attribution-lab.index') }}">Laboratorio de atribución</a>.
                </p>
            </div>
        </section>
    @endisset

    <div id="marketing-tokens-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#marketing-tokens-table',
            endpoint: '{{ route('admin.marketing-tokens.table-data') }}',
            rowSelectable: false,
            initialFilter: @json(request('filter')),
        });
    </script>
@endpush

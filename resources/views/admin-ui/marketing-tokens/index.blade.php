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

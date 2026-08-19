@extends('admin-ui.layouts.master')

@section('title', 'Integración')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Integración — Tokens API',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Aspel Integration'],
            ['label' => 'Integración'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear token',
            'subtitle' => 'Nuevo acceso para el script de Aspel',
            'icon' => 'fas fa-key',
            'fragmentUrl' => route('admin.aspel-tokens.create-fragment'),
            'submitUrl' => route('admin.aspel-tokens.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div class="au-help-text" style="display:block;margin:-4px 0 16px;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
        Los tokens de abajo autentican las rutas <code>POST /api/aspel/*</code>. El script externo de Aspel debe mandar el header <code>Authorization: Bearer {token}</code> con un token en estado Activo.
    </div>

    <div id="aspel-tokens-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#aspel-tokens-table',
            endpoint: '{{ route('admin.aspel-tokens.table-data') }}',
            rowSelectable: false,
            initialFilter: @json(request('filter')),
        });
    </script>
@endpush

@extends('admin-ui.layouts.master')

@section('title', 'Roles y Permisos')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Roles y Permisos',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Roles y Permisos'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear rol',
            'subtitle' => 'Alta manual de rol personalizado',
            'icon' => 'fas fa-user-shield',
            'fragmentUrl' => route('admin.roles.create-fragment'),
            'submitUrl' => route('admin.roles.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="role-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#role-table',
            endpoint: '{{ route('admin.roles.table-data') }}',
            bulkEndpoint: '{{ route('admin.roles.bulk') }}',
            exportEndpoint: '{{ route('admin.roles.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

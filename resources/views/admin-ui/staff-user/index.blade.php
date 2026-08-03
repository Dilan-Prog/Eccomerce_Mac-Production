@extends('admin-ui.layouts.master')

@section('title', 'Personal / Staff')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Personal / Staff',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Personal / Staff'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear personal',
            'subtitle' => 'Alta manual de administrador, vendedor, asociado o técnico',
            'icon' => 'fas fa-user-tie',
            'fragmentUrl' => route('admin.staff-users.create-fragment'),
            'submitUrl' => route('admin.staff-users.store'),
            'method' => 'POST',
            'sidebar' => false,
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="staff-user-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#staff-user-table',
            endpoint: '{{ route('admin.staff-users.table-data') }}',
            bulkEndpoint: '{{ route('admin.staff-users.bulk') }}',
            exportEndpoint: '{{ route('admin.staff-users.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

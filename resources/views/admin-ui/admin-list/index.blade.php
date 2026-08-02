@extends('admin-ui.layouts.master')

@section('title', 'Administradores')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Administradores',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Administradores'],
        ],
    ])

    <div id="admin-list-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#admin-list-table',
            endpoint: '{{ route('admin.admin-list.table-data') }}',
            bulkEndpoint: '{{ route('admin.admin-list.bulk') }}',
            exportEndpoint: '{{ route('admin.admin-list.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
            initialFilter: @json(request('filter')),
        });
    </script>
@endpush

@extends('admin-ui.layouts.master')

@section('title', 'Ordenes')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Ordenes',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Ordenes'],
        ],
    ])

    <div id="orders-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#orders-table',
            endpoint: '{{ route('admin.order.table-data') }}',
            bulkEndpoint: '{{ route('admin.order.bulk') }}',
            exportEndpoint: '{{ route('admin.order.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
            initialFilter: @json(request('filter')),
        });
    </script>
@endpush

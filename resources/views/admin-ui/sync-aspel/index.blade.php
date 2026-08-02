@extends('admin-ui.layouts.master')

@section('title', 'Aspel Sincronizacion')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Productos Sincronizados',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Aspel Sincronizacion'],
            ['label' => 'Productos'],
        ],
    ])

    <div id="sync-aspel-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#sync-aspel-table',
            endpoint: '{{ route('admin.sync-aspel.table-data') }}',
            exportEndpoint: '{{ route('admin.sync-aspel.export') }}',
            rowSelectable: false,
        });
    </script>
@endpush

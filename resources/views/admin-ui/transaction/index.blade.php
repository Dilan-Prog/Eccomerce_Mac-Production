@extends('admin-ui.layouts.master')

@section('title', 'Transacciones')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Transacciones',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Transacciones'],
        ],
    ])

    <div id="transaction-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#transaction-table',
            endpoint: '{{ route('admin.transaction.table-data') }}',
            exportEndpoint: '{{ route('admin.transaction.export') }}',
        });
    </script>
@endpush

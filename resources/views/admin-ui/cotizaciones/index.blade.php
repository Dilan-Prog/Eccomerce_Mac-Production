@extends('admin-ui.layouts.master')

@section('title', 'Cotizaciones')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Cotizaciones',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Cotizaciones'],
        ],
    ])

    <div id="cotizaciones-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#cotizaciones-table',
            endpoint: '{{ route('admin.cotizaciones.table-data') }}',
            exportEndpoint: '{{ route('admin.cotizaciones.export') }}',
            rowSelectable: false,
        });
    </script>
@endpush

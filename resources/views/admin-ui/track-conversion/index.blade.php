@extends('admin-ui.layouts.master')

@section('title', 'Seguimiento De Conversiones')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Publicidad / Seguimiento de Conversión',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Seguimiento De Conversiones'],
        ],
    ])

    <div id="track-conversion-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#track-conversion-table',
            endpoint: '{{ route('admin.track-conversion.table-data') }}',
            exportEndpoint: '{{ route('admin.track-conversion.export') }}',
            rowSelectable: false,
        });
    </script>
@endpush

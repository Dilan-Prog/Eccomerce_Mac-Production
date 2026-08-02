@extends('admin-ui.layouts.master')

@section('title', 'Cupones')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Cupones',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Cupones'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear cupón',
            'subtitle' => 'Alta manual de cupón',
            'icon' => 'fas fa-ticket',
            'fragmentUrl' => route('admin.coupons.create-fragment'),
            'submitUrl' => route('admin.coupons.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="coupons-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#coupons-table',
            endpoint: '{{ route('admin.coupons.table-data') }}',
            bulkEndpoint: '{{ route('admin.coupons.bulk') }}',
            exportEndpoint: '{{ route('admin.coupons.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

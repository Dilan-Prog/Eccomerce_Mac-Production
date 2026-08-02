@extends('admin-ui.layouts.master')

@section('title', 'Producto Más Eccomerce')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Producto Más Eccomerce',
        'subtitle' => 'Producto: ' . $product->name,
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Productos', 'url' => route('admin.products.index')],
            ['label' => 'Producto Más Eccomerce'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear opción de comercio',
            'subtitle' => 'Producto: ' . $product->name,
            'icon' => 'fas fa-store',
            'fragmentUrl' => route('admin.products-more-eccomerce.create-fragment', ['product' => $product->id]),
            'submitUrl' => route('admin.products-more-eccomerce.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="products-more-eccomerce-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#products-more-eccomerce-table',
            endpoint: '{{ route('admin.products-more-eccomerce.table-data', ['product' => $product->id]) }}',
            bulkEndpoint: '{{ route('admin.products-more-eccomerce.bulk', ['product' => $product->id]) }}',
            exportEndpoint: '{{ route('admin.products-more-eccomerce.export', ['product' => $product->id]) }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
            initialFilter: @json(request('filter')),
        });
    </script>
@endpush

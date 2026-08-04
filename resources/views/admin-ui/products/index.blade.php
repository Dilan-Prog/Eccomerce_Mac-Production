@extends('admin-ui.layouts.master')

@section('title', 'Productos')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Productos',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Productos'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear producto',
            'subtitle' => 'Alta manual de producto',
            'icon' => 'fas fa-box',
            'fragmentUrl' => route('admin.products.create-fragment'),
            'submitUrl' => route('admin.products.store'),
            'method' => 'POST',
            'sidebar' => true,
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="products-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script src="{{ asset('admin-ui/js/core/detail-modal.js') }}"></script>
    <script>
        window.AU_PRODUCT_SKU_SEARCH = {
            routes: {
                searchSku: '{{ route('admin.product.search-sku') }}',
                aspelPrices: '{{ route('admin.product.aspel-prices') }}',
            },
        };
    </script>
    <script src="{{ asset('admin-ui/js/products/sku-search.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#products-table',
            endpoint: '{{ route('admin.products.table-data') }}',
            bulkEndpoint: '{{ route('admin.products.bulk') }}',
            exportEndpoint: '{{ route('admin.products.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
            initialFilter: @json(request('filter')),
            // Opt-in read-only detail modal (AU.DetailModal): clicking a row
            // (outside the checkbox / Acciones column) fetches the product's
            // details fragment and shows it in an overlay.
            rowDetailsUrl: (id) => '{{ url('admin/products') }}/' + id + '/details-fragment',
            rowDetailsTitle: (row) => (row && row.cells && row.cells.name) || 'Detalle de producto',
        });
    </script>
@endpush

@extends('admin-ui.layouts.master')

@section('title', 'Variantes de Producto')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Variantes de Producto',
        'subtitle' => 'Producto: ' . $product->name,
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Productos', 'url' => route('admin.products.index')],
            ['label' => 'Variantes de Producto'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear variante',
            'subtitle' => 'Producto: ' . $product->name,
            'icon' => 'fas fa-layer-group',
            'fragmentUrl' => route('admin.products-variant.create-fragment', ['product' => $product->id]),
            'submitUrl' => route('admin.products-variant.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="products-variant-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#products-variant-table',
            endpoint: '{{ route('admin.products-variant.table-data', ['product' => $product->id]) }}',
            bulkEndpoint: '{{ route('admin.products-variant.bulk') }}',
            exportEndpoint: '{{ route('admin.products-variant.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

@extends('admin-ui.layouts.master')

@section('title', 'Items de Variante')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Items de Variante',
        'subtitle' => 'Variante: ' . $variant->name,
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Productos', 'url' => route('admin.products.index')],
            ['label' => 'Variantes', 'url' => route('admin.products-variant.index', ['product' => $product->id])],
            ['label' => 'Items de Variante'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear item de variante',
            'subtitle' => 'Alta manual de item para la variante: ' . $variant->name,
            'icon' => 'fas fa-layer-group',
            'fragmentUrl' => route('admin.products-variant-item.create-fragment', ['productId' => $product->id, 'variantId' => $variant->id]),
            'submitUrl' => route('admin.products-variant-item.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="products-variant-item-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#products-variant-item-table',
            endpoint: '{{ route('admin.products-variant-item.table-data', ['productId' => $product->id, 'variantId' => $variant->id]) }}',
            bulkEndpoint: '{{ route('admin.products-variant-item.bulk') }}',
            exportEndpoint: '{{ route('admin.products-variant-item.export', ['productId' => $product->id, 'variantId' => $variant->id]) }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
            initialFilter: @json(request('filter')),
        });
    </script>
@endpush

@extends('admin-ui.layouts.master')

@section('title', 'Combinaciones de Variante')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Combinaciones de Variante',
        'subtitle' => 'Producto: ' . $product->name,
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Productos', 'url' => route('admin.products.index')],
            ['label' => 'Combinaciones de Variante'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear combinación',
            'subtitle' => 'Producto: ' . $product->name,
            'icon' => 'fas fa-layer-group',
            'fragmentUrl' => route('admin.products-variant-combinations.create-fragment', ['product' => $product->id]),
            'submitUrl' => route('admin.products-variant-combinations.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="products-variant-combinations-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#products-variant-combinations-table',
            endpoint: '{{ route('admin.products-variant-combinations.table-data') }}',
            bulkEndpoint: '{{ route('admin.products-variant-combinations.bulk') }}',
            exportEndpoint: '{{ route('admin.products-variant-combinations.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

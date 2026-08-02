@extends('admin-ui.layouts.master')

@section('title', 'Sub Categorias')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Sub Categorias',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Sub Categorias'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear sub categoría',
            'subtitle' => 'Alta manual de sub categoría',
            'icon' => 'fas fa-tag',
            'fragmentUrl' => route('admin.sub-category.create-fragment'),
            'submitUrl' => route('admin.sub-category.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="sub-category-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#sub-category-table',
            endpoint: '{{ route('admin.sub-category.table-data') }}',
            bulkEndpoint: '{{ route('admin.sub-category.bulk') }}',
            exportEndpoint: '{{ route('admin.sub-category.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

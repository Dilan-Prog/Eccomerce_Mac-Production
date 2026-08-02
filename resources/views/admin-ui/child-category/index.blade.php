@extends('admin-ui.layouts.master')

@section('title', 'Categorias Secundarias')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Categorias Secundarias',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Categorias Secundarias'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear categoría secundaria',
            'subtitle' => 'Alta manual de categoría secundaria',
            'icon' => 'fas fa-tag',
            'fragmentUrl' => route('admin.child-category.create-fragment'),
            'submitUrl' => route('admin.child-category.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="child-category-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#child-category-table',
            endpoint: '{{ route('admin.child-category.table-data') }}',
            bulkEndpoint: '{{ route('admin.child-category.bulk') }}',
            exportEndpoint: '{{ route('admin.child-category.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

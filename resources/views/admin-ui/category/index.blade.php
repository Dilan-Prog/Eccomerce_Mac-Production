@extends('admin-ui.layouts.master')

@section('title', 'Categorias')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Categorias',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Categorias'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear categoría',
            'subtitle' => 'Alta manual de categoría',
            'icon' => 'fas fa-tag',
            'fragmentUrl' => route('admin.category.create-fragment'),
            'submitUrl' => route('admin.category.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="category-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#category-table',
            endpoint: '{{ route('admin.category.table-data') }}',
            bulkEndpoint: '{{ route('admin.category.bulk') }}',
            exportEndpoint: '{{ route('admin.category.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
            initialFilter: @json(request('filter')),
        });
    </script>
@endpush

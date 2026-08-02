@extends('admin-ui.layouts.master')

@section('title', 'Marcas')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Marcas',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marcas'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear marca',
            'subtitle' => 'Alta manual de marca',
            'icon' => 'fas fa-copyright',
            'fragmentUrl' => route('admin.brand.create-fragment'),
            'submitUrl' => route('admin.brand.store'),
            'method' => 'POST',
            'sidebar' => true,
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="brand-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#brand-table',
            endpoint: '{{ route('admin.brand.table-data') }}',
            bulkEndpoint: '{{ route('admin.brand.bulk') }}',
            exportEndpoint: '{{ route('admin.brand.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

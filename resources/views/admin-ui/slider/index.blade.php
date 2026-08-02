@extends('admin-ui.layouts.master')

@section('title', 'Slider')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Slider',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Slider'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear slider',
            'subtitle' => 'Alta manual de slider',
            'icon' => 'fas fa-images',
            'fragmentUrl' => route('admin.slider.create-fragment'),
            'submitUrl' => route('admin.slider.store'),
            'method' => 'POST',
            'sidebar' => true,
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="slider-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#slider-table',
            endpoint: '{{ route('admin.slider.table-data') }}',
            bulkEndpoint: '{{ route('admin.slider.bulk') }}',
            exportEndpoint: '{{ route('admin.slider.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

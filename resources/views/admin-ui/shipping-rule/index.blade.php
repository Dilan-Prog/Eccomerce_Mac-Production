@extends('admin-ui.layouts.master')

@section('title', 'Reglas de Envio')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Reglas de Envio',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Reglas de Envio'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear regla de envío',
            'subtitle' => 'Alta manual de regla de envío',
            'icon' => 'fas fa-truck',
            'fragmentUrl' => route('admin.shipping-rule.create-fragment'),
            'submitUrl' => route('admin.shipping-rule.store'),
            'method' => 'POST',
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="shipping-rule-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#shipping-rule-table',
            endpoint: '{{ route('admin.shipping-rule.table-data') }}',
            bulkEndpoint: '{{ route('admin.shipping-rule.bulk') }}',
            exportEndpoint: '{{ route('admin.shipping-rule.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
            initialFilter: @json(request('filter')),
        });
    </script>
@endpush

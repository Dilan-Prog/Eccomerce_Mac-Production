@extends('admin-ui.layouts.master')

@section('title', 'Cuentas Bancarias')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Cuentas Bancarias',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Cuentas Bancarias'],
        ],
        'actions' => '<button type="button" class="au-btn au-btn-primary" data-au-open-modal="' . e(json_encode([
            'title' => 'Crear cuenta bancaria',
            'subtitle' => 'Alta manual de cuenta bancaria',
            'icon' => 'fas fa-university',
            'fragmentUrl' => route('admin.bank-account.create-fragment'),
            'submitUrl' => route('admin.bank-account.store'),
            'method' => 'POST',
            'sidebar' => true,
        ])) . '">+ Crear Nuevo</button>',
    ])

    <div id="bank-account-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#bank-account-table',
            endpoint: '{{ route('admin.bank-account.table-data') }}',
            bulkEndpoint: '{{ route('admin.bank-account.bulk') }}',
            exportEndpoint: '{{ route('admin.bank-account.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
            initialFilter: @json(request('filter')),
        });
    </script>
@endpush

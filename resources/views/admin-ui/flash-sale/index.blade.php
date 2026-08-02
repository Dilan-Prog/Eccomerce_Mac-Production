@extends('admin-ui.layouts.master')

@section('title', 'Venta Flash')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Venta Flash',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Venta Flash'],
        ],
    ])

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Fecha Final de la Venta Flash</div>
        </div>
        <div class="au-card-body">
            <form action="{{ route('admin.flash-sale.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="au-field" style="max-width:280px">
                    <label class="au-label">Fecha Final de Venta</label>
                    <input type="date" class="au-input" name="end_date" value="{{ $flashSaleDate->end_date ?? '' }}">
                </div>
                <button type="submit" class="au-btn au-btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Agregar Productos a la Venta Flash</div>
        </div>
        <div class="au-card-body">
            <form action="{{ route('admin.flash-sale.add-product') }}" method="POST">
                @csrf
                <div class="au-field">
                    <label class="au-label">Agregar Producto</label>
                    <select name="product" class="au-select">
                        <option value="">Seleccionar</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="au-field">
                        <label class="au-label">Mostrar En Inicio? (Productos Más Vendidos)</label>
                        <select name="show_at_home" class="au-select">
                            <option value="">Seleccionar</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="au-field">
                        <label class="au-label">Estado</label>
                        <select name="status" class="au-select">
                            <option value="">Seleccionar</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="au-btn au-btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    <div class="au-page-subtitle" style="font-weight:600;color:var(--au-text);font-size:var(--au-fs-heading);margin:var(--au-space-5) 0 var(--au-space-3)">Todos los Productos de Venta Flash</div>
    <div id="flash-sale-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#flash-sale-table',
            endpoint: '{{ route('admin.flash-sale.table-data') }}',
            bulkEndpoint: '{{ route('admin.flash-sale.bulk') }}',
            exportEndpoint: '{{ route('admin.flash-sale.export') }}',
            bulkActions: [
                { key: 'delete', label: 'Eliminar', tone: 'critical' },
            ],
            rowSelectable: true,
        });
    </script>
@endpush

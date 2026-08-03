{{--
    Shared markup for the ERP-style admin quote builder, included by both
    create.blade.php (no $cotizacion — client picker + "crear cliente nuevo")
    and edit.blade.php (real $cotizacion with items/user/perfil preloaded —
    full product search + line items + summary).

    Passed in: 'cotizacion' => Cotizacion|null
--}}
@php
    /** @var \App\Models\Cotizacion|null $cotizacion */
    $isEdit = isset($cotizacion) && $cotizacion;

    $builderConfig = [
        'cotizacionId' => $isEdit ? $cotizacion->id : null,
        'items' => $isEdit
            ? $cotizacion->items->sortBy('sort_order')->values()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nombre' => $item->nombre,
                    'sku' => $item->sku,
                    'modelo' => $item->modelo,
                    'marca' => $item->marca,
                    'precio_unitario' => (float) $item->precio_unitario,
                    'cantidad' => $item->cantidad,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->all()
            : [],
        'total' => $isEdit ? (float) $cotizacion->total : 0,
        'routes' => [
            'clientsSearch' => route('admin.cotizaciones.clients-search'),
            'productsSearch' => route('admin.cotizaciones.products-search'),
            'store' => route('admin.cotizaciones.store'),
            'editUrlBase' => url('admin/cotizaciones'),
            'itemsStore' => $isEdit ? route('admin.cotizaciones.items.store', $cotizacion->id) : null,
            'itemBase' => $isEdit ? url('admin/cotizaciones/' . $cotizacion->id . '/items') : null,
            'finalize' => $isEdit ? route('admin.cotizaciones.finalize', $cotizacion->id) : null,
            'showUrlBase' => url('admin/cotizaciones'),
            'customerCreateFragment' => route('admin.customer.create-fragment'),
            'customerStore' => route('admin.customer.store'),
        ],
    ];
@endphp

@include('admin-ui.layouts.page-header', [
    'title' => $isEdit ? 'Cotización ' . $cotizacion->folio : 'Nueva Cotización',
    'breadcrumbs' => [
        ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
        ['label' => 'Cotizaciones', 'url' => route('admin.cotizaciones.index')],
        ['label' => $isEdit ? $cotizacion->folio : 'Nueva'],
    ],
    'actions' => '<a href="' . route('admin.cotizaciones.index') . '" class="au-btn"><i class="fas fa-arrow-left"></i> Volver a Cotizaciones</a>',
])

<div class="au-quote-builder" id="au-quote-builder">
    <div class="au-quote-builder-main">
        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">Cliente</div>
            </div>
            <div class="au-card-body">
                <div id="au-quote-client-selected" class="au-quote-client-selected" style="{{ $isEdit ? '' : 'display:none' }}">
                    <div>
                        <div class="au-quote-client-name" data-au-quote-client-name>{{ $isEdit ? $cotizacion->user->name : '' }}</div>
                        <div class="au-quote-client-email" data-au-quote-client-email>{{ $isEdit ? $cotizacion->user->email : '' }}</div>
                    </div>
                    <button type="button" class="au-btn au-btn-sm" disabled title="Para asignar otro cliente, crea una nueva cotización.">
                        Cambiar cliente
                    </button>
                </div>
                <div id="au-quote-client-picker" style="{{ $isEdit ? 'display:none' : '' }}">
                    <div class="au-table-search" style="width:100%;margin-bottom:12px">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar cliente por nombre o correo..." data-au-quote-client-search>
                    </div>
                    <div class="au-quote-client-results" data-au-quote-client-results></div>
                    <button type="button" class="au-btn" data-au-quote-client-new>+ Crear cliente nuevo</button>
                </div>
            </div>
        </div>

        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">Agregar productos</div>
            </div>
            <div class="au-card-body">
                @if ($isEdit)
                    <div class="au-table-search" style="width:100%;margin-bottom:12px">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar producto por nombre, SKU o modelo..." data-au-quote-product-search>
                    </div>
                    <div class="au-quote-product-results" data-au-quote-product-results></div>
                @else
                    <div class="au-table-empty">Selecciona o crea un cliente para poder agregar productos.</div>
                @endif
            </div>
        </div>

        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">Partidas</div>
            </div>
            <div class="au-table-wrap">
                <table class="au-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Descripción</th>
                            <th>SKU / Modelo</th>
                            <th>Cantidad</th>
                            <th class="au-text-right">Precio Unitario</th>
                            <th class="au-text-right">Subtotal</th>
                            <th class="au-col-actions"></th>
                        </tr>
                    </thead>
                    <tbody data-au-quote-items-body>
                        @if (!$isEdit)
                            <tr><td colspan="7" class="au-table-empty">Selecciona o crea un cliente para comenzar.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="au-quote-builder-side">
        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">Resumen</div>
            </div>
            <div class="au-card-body">
                <div class="au-quote-summary-count" data-au-quote-summary-count>0 artículos</div>
                <div class="au-quote-summary-total" data-au-quote-summary-total>{{ "$0.00" }}</div>
                @if ($isEdit)
                    <button type="button" id="au-quote-finalize" class="au-btn au-btn-primary"
                            style="width:100%;justify-content:center;margin-top:16px"
                            {{ $cotizacion->items->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-check-circle"></i> Finalizar cotización
                    </button>
                @endif
                <a href="{{ route('admin.cotizaciones.index') }}" class="au-btn" style="width:100%;justify-content:center;margin-top:8px">
                    <i class="fas fa-arrow-left"></i> Volver a Cotizaciones
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.AU_COTIZACION_BUILDER = @json($builderConfig);
    </script>
    <script src="{{ asset('admin-ui/js/cotizaciones/builder.js') }}"></script>
@endpush

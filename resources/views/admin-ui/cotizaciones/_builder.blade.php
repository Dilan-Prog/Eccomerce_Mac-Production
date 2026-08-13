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

    // Defaults de Configuración General — se muestran siempre (aun antes de
    // crear la cotización) para que el vendedor vea "a cuánto lo está
    // poniendo" el sistema; ver App\Support\CotizacionExchange.
    $defaultRateUsdMxn = \App\Support\CotizacionExchange::defaultUsdToMxn();
    $defaultRateMxnUsd = \App\Support\CotizacionExchange::defaultMxnToUsd();

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
                    'precio_tier_label' => $item->precio_tier_label,
                    'moneda_origen' => $item->moneda_origen,
                    'precio_unitario_origen' => $item->precio_unitario_origen !== null ? (float) $item->precio_unitario_origen : null,
                    'cantidad' => $item->cantidad,
                    'es_pendiente' => (bool) $item->es_pendiente,
                    'tiempo_entrega' => $item->tiempo_entrega,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->all()
            : [],
        'total' => $isEdit ? (float) $cotizacion->total : 0,
        'currency' => $isEdit ? $cotizacion->currency : 'MXN',
        'exchangeRate' => $isEdit && $cotizacion->exchange_rate ? (float) $cotizacion->exchange_rate : null,
        'exchangeRateMxnUsd' => $isEdit && $cotizacion->exchange_rate_mxn_usd ? (float) $cotizacion->exchange_rate_mxn_usd : null,
        'defaultExchangeRateUsdMxn' => $defaultRateUsdMxn,
        'defaultExchangeRateMxnUsd' => $defaultRateMxnUsd,
        'routes' => [
            'clientsSearch' => route('admin.cotizaciones.clients-search'),
            'productsSearch' => route('admin.cotizaciones.products-search'),
            'store' => route('admin.cotizaciones.store'),
            'editUrlBase' => url('admin/cotizaciones'),
            'itemsStore' => $isEdit ? route('admin.cotizaciones.items.store', $cotizacion->id) : null,
            'manualItemFragment' => $isEdit ? route('admin.cotizaciones.items.manual-fragment', $cotizacion->id) : null,
            'itemBase' => $isEdit ? url('admin/cotizaciones/' . $cotizacion->id . '/items') : null,
            'currency' => $isEdit ? route('admin.cotizaciones.currency', $cotizacion->id) : null,
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
                @if ($isEdit)
                    <button type="button" class="au-btn au-btn-sm" data-au-quote-add-manual>+ Agregar personalizado</button>
                @endif
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
                @if ($isEdit)
                    <div class="au-field" style="margin-bottom:10px">
                        <label class="au-label">Moneda</label>
                        <select class="au-select" data-au-quote-currency>
                            <option value="MXN" {{ $cotizacion->currency === 'MXN' ? 'selected' : '' }}>Pesos (MXN)</option>
                            <option value="USD" {{ $cotizacion->currency === 'USD' ? 'selected' : '' }}>Dólares (USD)</option>
                        </select>
                    </div>
                @endif

                {{-- Los dos tipos de cambio siempre son visibles (no solo cuando
                     currency=USD): el USD->MXN también normaliza a MXN los
                     productos nativos-USD al agregarlos, sin importar en qué
                     moneda se esté mostrando la cotización — ver
                     App\Support\CotizacionExchange y
                     AdminCotizacionController::storeItem().

                     Los campos de referencia (Configuración General) son
                     siempre de solo-lectura y nunca se envían al servidor.
                     Los campos personalizados empiezan vacíos (no prellenados
                     con el default) para que el vendedor deba tocarlos
                     explícitamente si quiere fijar un override — de lo
                     contrario el placeholder ya deja claro que se usará el
                     de Configuración General. --}}
                <div class="au-field" style="margin-bottom:6px">
                    <label class="au-label">Tipo de cambio USD &rarr; MXN (Configuración General)</label>
                    <input type="text" class="au-input" value="{{ number_format($defaultRateUsdMxn, 4) }}" disabled>
                </div>
                <div class="au-field" style="margin-bottom:10px">
                    <label class="au-label">Tipo de cambio personalizado USD &rarr; MXN</label>
                    @if ($isEdit)
                        <input type="number" step="0.0001" min="0" class="au-input" data-au-quote-rate-usd-mxn
                               value="{{ $cotizacion->exchange_rate ?? '' }}" placeholder="Usar el de Configuración General">
                    @else
                        <input type="number" step="0.0001" min="0" class="au-input" placeholder="Podrás personalizarlo al crear la cotización" disabled>
                    @endif
                    <span class="au-help-text">Se aplicará solo a los productos cuya moneda en Aspel sea USD (no afecta a los que ya están en MXN).</span>
                </div>

                <div class="au-field" style="margin-bottom:6px">
                    <label class="au-label">Tipo de cambio MXN &rarr; USD (Configuración General)</label>
                    <input type="text" class="au-input" value="{{ number_format($defaultRateMxnUsd, 4) }}" disabled>
                </div>
                <div class="au-field" style="margin-bottom:10px">
                    <label class="au-label">Tipo de cambio personalizado MXN &rarr; USD</label>
                    @if ($isEdit)
                        <input type="number" step="0.0001" min="0" class="au-input" data-au-quote-rate-mxn-usd
                               value="{{ $cotizacion->exchange_rate_mxn_usd ?? '' }}" placeholder="Usar el de Configuración General">
                    @else
                        <input type="number" step="0.0001" min="0" class="au-input" placeholder="Podrás personalizarlo al crear la cotización" disabled>
                    @endif
                    <span class="au-help-text">Se aplicará solo a los productos cuya moneda en Aspel sea MXN (no afecta a los que ya están en USD).</span>
                </div>
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

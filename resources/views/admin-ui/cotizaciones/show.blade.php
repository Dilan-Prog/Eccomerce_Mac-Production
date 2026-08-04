@php
    $statusTone = match ($cotizacion->status) {
        'generada' => 'info',
        'borrador' => 'warning',
        'finalizada' => 'success',
        default => 'info',
    };
    $statusLabel = match ($cotizacion->status) {
        'generada' => 'Generada',
        'borrador' => 'Borrador',
        'finalizada' => 'Finalizada',
        default => ucfirst($cotizacion->status),
    };
    $sourceTone = $cotizacion->source === 'cliente' ? 'info' : 'warning';
    $sourceLabel = $cotizacion->source === 'cliente' ? 'Cliente' : 'Admin';

    // Forward IVA calc (subtotal + IVA = total), matching the fix applied to
    // resources/views/cotizaciones/pdf.blade.php — cotizacion->total is
    // always raw MXN with no IVA baked in (see App\Support\CotizacionPricing),
    // so IVA is added once here for display, then converted to the quote's
    // chosen currency via Cotizacion::displayAmount().
    $subtotalSinIva = round($cotizacion->total, 2);
    $ivaPercent = (float) (\Illuminate\Support\Facades\DB::table('general_settings')->value('iva_mexico') ?? 16.00);
    $iva = round($subtotalSinIva * $ivaPercent / 100, 2);
    $totalConIva = $subtotalSinIva + $iva;

    // Finalized/generated quotes carry their final line items as a frozen
    // snapshot in productos_json. A draft has no snapshot yet — its items
    // still live in the relational cotizacion_items rows.
    $useDraftItems = $cotizacion->status === 'borrador';

    $actions = '<a href="' . route('admin.cotizaciones.index') . '" class="au-btn au-btn-sm">Volver</a>';
    if ($cotizacion->source === 'admin' && $cotizacion->status === 'borrador') {
        $actions .= ' <a href="' . route('admin.cotizaciones.edit', $cotizacion->id) . '" class="au-btn au-btn-sm au-btn-primary">Continuar edición</a>';
    }
    if ($pdfUrl) {
        // AU.PdfPreview (public/admin-ui/js/core/pdf-preview.js) opens an
        // iframe+download modal instead of a bare target="_blank" link, via
        // the generic [data-au-pdf-preview] delegated handler in admin.js.
        $actions .= ' <a href="' . $pdfUrl . '" class="au-btn au-btn-sm" data-au-pdf-preview'
            . ' data-title="' . e('Cotización ' . $cotizacion->folio) . '"'
            . ' data-url="' . e($pdfUrl) . '"'
            . ' data-download-name="' . e($cotizacion->folio . '.pdf') . '">Ver PDF</a>';
    }
    if ($cotizacion->status !== 'borrador' && $cotizacion->pdf_path) {
        // Re-renders the same PDF file from this quote's frozen data but
        // with whatever is CURRENT in the template/settings (logo, IVA %,
        // bank accounts) — see AdminCotizacionController::regeneratePdf().
        // .au-action-item is the generic POST+toast+reload handler in
        // admin.js — no bespoke JS needed for this button.
        $actions .= ' <a href="' . route('admin.cotizaciones.regenerate-pdf', $cotizacion->id) . '" class="au-btn au-btn-sm au-action-item" data-method="POST">Regenerar PDF</a>';
    }
@endphp
@extends('admin-ui.layouts.master')

@section('title', 'Cotización ' . $cotizacion->folio)

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => $cotizacion->folio,
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Cotizaciones', 'url' => route('admin.cotizaciones.index')],
            ['label' => $cotizacion->folio],
        ],
        'actions' => $actions,
    ])

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start">
        <div>
            {{-- LINE ITEMS --}}
            <div class="au-card">
                <div class="au-card-header">
                    <div class="au-card-title">Productos cotizados</div>
                </div>
                <div class="au-table-wrap">
                    <table class="au-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>SKU / Modelo</th>
                                <th>Marca</th>
                                <th class="au-text-right">Cantidad</th>
                                <th class="au-text-right">P. Unitario</th>
                                <th class="au-text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($useDraftItems)
                                @forelse ($cotizacion->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><strong>{{ $item->nombre }}</strong></td>
                                        <td>
                                            @if (!empty($item->sku))<span class="au-mono">{{ $item->sku }}</span>@endif
                                            @if (!empty($item->modelo)) <span style="color:var(--au-text-subdued)">/ {{ $item->modelo }}</span>@endif
                                        </td>
                                        <td>{{ $item->marca ?? '—' }}</td>
                                        <td class="au-text-right">{{ $item->cantidad }}</td>
                                        <td class="au-text-right au-mono">${{ number_format($item->precio_unitario, 2, '.', ',') }}</td>
                                        <td class="au-text-right au-mono"><strong>${{ number_format($item->subtotal, 2, '.', ',') }}</strong></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align:center;color:var(--au-text-subdued)">Este borrador aún no tiene productos.</td>
                                    </tr>
                                @endforelse
                            @else
                                @forelse ($cotizacion->productos_json ?? [] as $i => $p)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <strong>{{ $p['nombre'] }}</strong>
                                            @if (!empty($p['precio_tier_label']))
                                                <div style="font-size:11px;color:var(--au-text-subdued)">Precio: {{ $p['precio_tier_label'] }}</div>
                                            @endif
                                            @if (!empty($p['es_pendiente']))
                                                <div style="margin-top:4px">
                                                    <span class="au-badge au-badge-warning"><span class="au-badge-dot"></span>Pendiente — {{ $p['tiempo_entrega'] ?? 'sin especificar' }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if (!empty($p['sku']))<span class="au-mono">{{ $p['sku'] }}</span>@endif
                                            @if (!empty($p['modelo'])) <span style="color:var(--au-text-subdued)">/ {{ $p['modelo'] }}</span>@endif
                                        </td>
                                        <td>{{ $p['marca'] ?? '—' }}</td>
                                        <td class="au-text-right">{{ $p['cantidad'] }}</td>
                                        <td class="au-text-right au-mono">${{ number_format($p['precio'], 2, '.', ',') }}</td>
                                        <td class="au-text-right au-mono"><strong>${{ number_format($p['subtotal'], 2, '.', ',') }}</strong></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align:center;color:var(--au-text-subdued)">Sin productos registrados.</td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TOTALS --}}
            <div class="au-card">
                <div class="au-card-header">
                    <div class="au-card-title">Totales</div>
                </div>
                <div class="au-card-body">
                    <div class="au-flex" style="justify-content:space-between;padding:5px 0;font-size:13px;color:var(--au-text-subdued)">
                        <span>Subtotal (s/IVA)</span><span class="au-mono">${{ number_format($cotizacion->displayAmount($subtotalSinIva), 2, '.', ',') }} {{ $cotizacion->currency }}</span>
                    </div>
                    <div class="au-flex" style="justify-content:space-between;padding:5px 0;font-size:13px;color:var(--au-text-subdued)">
                        <span>IVA ({{ number_format($ivaPercent, 0) }}%)</span><span class="au-mono">${{ number_format($cotizacion->displayAmount($iva), 2, '.', ',') }} {{ $cotizacion->currency }}</span>
                    </div>
                    <hr style="border:0;border-top:1px solid var(--au-border);margin:6px 0">
                    <div class="au-flex" style="justify-content:space-between;padding:5px 0;font-size:15px;font-weight:700">
                        <span>Total</span><span class="au-mono">${{ number_format($cotizacion->displayAmount($totalConIva), 2, '.', ',') }} {{ $cotizacion->currency }}</span>
                    </div>
                    @if ($cotizacion->currency === 'USD' && $cotizacion->exchange_rate)
                    <div style="font-size:11px;color:var(--au-text-subdued);margin-top:4px">Tipo de cambio: {{ $cotizacion->exchange_rate }} MXN por USD</div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            {{-- CLIENT INFO --}}
            <div class="au-card">
                <div class="au-card-header">
                    <div class="au-card-title">Datos del cliente</div>
                </div>
                <div class="au-card-body" style="font-size:13px;line-height:1.9">
                    <div><span style="color:var(--au-text-subdued)">Nombre:</span> <strong>{{ $cotizacion->user->name ?? '—' }}</strong></div>
                    <div><span style="color:var(--au-text-subdued)">Correo:</span> {{ $cotizacion->user->email ?? '—' }}</div>
                    <div><span style="color:var(--au-text-subdued)">Fecha:</span> {{ $cotizacion->created_at->format('d/m/Y H:i') }}</div>
                    <div style="margin-top:8px">
                        <span class="au-badge au-badge-{{ $statusTone }}"><span class="au-badge-dot"></span>{{ $statusLabel }}</span>
                        <span class="au-badge au-badge-{{ $sourceTone }}"><span class="au-badge-dot"></span>{{ $sourceLabel }}</span>
                    </div>
                    @if ($cotizacion->source === 'admin' && $cotizacion->createdByAdmin)
                        <div style="margin-top:8px"><span style="color:var(--au-text-subdued)">Creada por:</span> {{ $cotizacion->createdByAdmin->name }}</div>
                    @endif
                </div>
            </div>

            {{-- FISCAL PROFILE --}}
            <div class="au-card">
                <div class="au-card-header">
                    <div class="au-card-title">Perfil fiscal</div>
                </div>
                <div class="au-card-body" style="font-size:13px;line-height:1.9">
                    @if ($cotizacion->perfil)
                        <div>
                            <span class="au-badge au-badge-{{ $cotizacion->perfil->tipo_persona === 'empresa' ? 'info' : 'success' }}">
                                <span class="au-badge-dot"></span>{{ $cotizacion->perfil->tipo_persona === 'empresa' ? 'Persona Moral' : 'Persona Física' }}
                            </span>
                        </div>
                        <div style="margin-top:8px"><span style="color:var(--au-text-subdued)">RFC:</span> <span class="au-mono">{{ $cotizacion->perfil->rfc }}</span></div>
                        @if ($cotizacion->perfil->razon_social)
                            <div><span style="color:var(--au-text-subdued)">Razón Social:</span> {{ $cotizacion->perfil->razon_social }}</div>
                        @endif
                        @if ($cotizacion->perfil->curp)
                            <div><span style="color:var(--au-text-subdued)">CURP:</span> <span class="au-mono">{{ $cotizacion->perfil->curp }}</span></div>
                        @endif
                        <div><span style="color:var(--au-text-subdued)">Dirección fiscal:</span> {{ $cotizacion->perfil->direccion_fiscal }}</div>
                        @if ($cotizacion->perfil->cif_path)
                            <div style="margin-top:8px">
                                <a href="{{ Storage::url($cotizacion->perfil->cif_path) }}" target="_blank" class="au-btn au-btn-sm">
                                    <i class="fas fa-file"></i> Ver CIF
                                </a>
                            </div>
                        @endif
                    @else
                        <span style="color:var(--au-text-subdued)">Sin datos fiscales.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

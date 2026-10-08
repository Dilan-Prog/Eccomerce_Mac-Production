{{-- Fragmento de solo lectura que se inyecta por innerHTML en el modal de detalle de la tabla de
     Seguimiento de Conversiones (sin layout y sin <script>: nada de lo que se inyecte aquí se ejecuta).
     Solo usa clases ya definidas en public/admin-ui/css/** --}}
@php
    $isVisit = $record->type === \App\Models\TrackConversion::VISIT_TYPE;
    $isEngagement = in_array($record->type, \App\Models\TrackConversion::ENGAGEMENT_TYPES, true);
    $kindLabel = $isVisit ? 'Visita' : ($isEngagement ? 'Interacción' : 'Conversión');
    $kindTone = $isVisit ? 'info' : ($isEngagement ? 'warning' : 'success');
    $metaLabels = [
        'product_id' => 'Producto (id)', 'sku' => 'SKU', 'product_name' => 'Producto', 'brand' => 'Marca',
        'qty' => 'Cantidad', 'value' => 'Valor', 'currency' => 'Moneda', 'placement' => 'Ubicación del botón',
        'link_text' => 'Texto del botón', 'href' => 'Destino', 'folio' => 'Folio de cotización',
        'intent' => 'Intención', 'form_variant' => 'Variante de formulario', 'cart_error' => 'Motivo (carrito)',
    ];
    $meta = (array) $record->meta;
    $fmt = fn ($d) => $d ? $d->timezone('America/Mexico_City')->format('d/m/Y H:i:s') : '—';
    $rows = [
        'Tipo' => $record->type,
        'ID de evento' => $record->event_id,
        'ID de sesión' => $record->session_id,
        'Capturado en el navegador' => $fmt($record->captured_at),
        'Registrado en el servidor' => $fmt($record->created_at),
        'gclid' => $record->gclid,
        'gbraid' => $record->gbraid,
        'wbraid' => $record->wbraid,
        'fbclid' => $record->fbclid,
        'utm_source' => $record->utm_source,
        'utm_medium' => $record->utm_medium,
        'utm_campaign' => $record->utm_campaign,
        'utm_term' => $record->utm_term,
        'utm_content' => $record->utm_content,
        'Landing (primera entrada)' => $record->landing_page,
        'URL completa del evento' => $record->page_url,
        'Referrer' => $record->referrer,
    ];
    $monoKeys = ['ID de evento', 'ID de sesión', 'gclid', 'gbraid', 'wbraid', 'fbclid'];
@endphp
<div style="display:flex;flex-direction:column;gap:16px;min-width:0">

    <div class="au-card">
        <div class="au-card-body">
            <div class="au-flex" style="gap:8px;flex-wrap:wrap;align-items:center">
                <span class="au-badge au-badge-{{ $kindTone }}">
                    <span class="au-badge-dot"></span>{{ $kindLabel }}
                </span>
                <span class="au-mono">{{ $record->type }}</span>
                <span class="au-help-text" style="margin:0">{{ $fmt($record->created_at) }}</span>
            </div>
        </div>
    </div>

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Datos del registro</div>
        </div>
        <div class="au-table-wrap">
            <table class="au-table">
                <tbody>
                    @foreach ($rows as $label => $value)
                        <tr>
                            <td style="width:34%;white-space:nowrap"><strong>{{ $label }}</strong></td>
                            <td style="word-break:break-all" class="{{ in_array($label, $monoKeys, true) ? 'au-mono' : '' }}">
                                @if ($value === null || $value === '')
                                    —
                                @elseif (in_array($label, ['URL completa del evento', 'Referrer'], true) && preg_match('#^https?://#i', $value))
                                    <a href="{{ $value }}" target="_blank" rel="noopener noreferrer">{{ $value }}</a>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($meta)
        <div class="au-card">
            <div class="au-card-header">
                <div class="au-card-title">Datos extra del evento</div>
            </div>
            <div class="au-table-wrap">
                <table class="au-table">
                    <tbody>
                        @foreach ($meta as $key => $value)
                            <tr>
                                <td style="width:34%;white-space:nowrap"><strong>{{ $metaLabels[$key] ?? $key }}</strong></td>
                                <td style="word-break:break-all">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="au-card">
        <div class="au-card-header">
            <div class="au-card-title">Recorrido de la sesión ({{ $sessionEvents->count() }})</div>
        </div>
        @if ($sessionEvents->isEmpty())
            <div class="au-card-body"><span class="au-help-text">Este registro no trae id de sesión.</span></div>
        @else
            <div class="au-table-wrap">
                <table class="au-table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Evento</th>
                            <th>Página</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessionEvents as $event)
                            <tr @if ($event->id === $record->id) style="font-weight:700" @endif>
                                <td class="au-mono" style="white-space:nowrap">{{ $event->created_at?->timezone('America/Mexico_City')->format('d/m H:i:s') }}</td>
                                <td>
                                    <span class="au-badge au-badge-{{ $event->type === \App\Models\TrackConversion::VISIT_TYPE ? 'info' : (in_array($event->type, \App\Models\TrackConversion::ENGAGEMENT_TYPES, true) ? 'warning' : 'success') }}">
                                        <span class="au-badge-dot"></span>{{ $event->type }}
                                    </span>
                                </td>
                                <td style="word-break:break-all">{{ $event->page_url ?: ($event->landing_page ?: '—') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

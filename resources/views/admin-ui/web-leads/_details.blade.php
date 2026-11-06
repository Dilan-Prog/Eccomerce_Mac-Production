{{-- Fragmento de solo lectura que se inyecta por innerHTML en el modal de detalle de Solicitudes web
     (sin layout y sin <script>: nada de lo que se inyecte aquí se ejecuta). Los datos vienen de visitantes
     anónimos: SIEMPRE con {{ }} (escapado), nunca {!! !!}. Los botones de acción usan los manejadores
     globales de admin-ui/js/admin.js (.au-action-item = POST/PATCH sin confirmar, .delete-item = DELETE con confirmación). --}}
@php
    $fmt = fn ($d) => $d ? $d->copy()->timezone('America/Mexico_City')->format('d/m/Y H:i:s') : '—';
    $statusTone = ['nuevo' => 'warning', 'contactado' => 'success', 'descartado' => 'info'][$lead->status] ?? 'info';
    $isHttp = fn ($v) => is_string($v) && preg_match('#^https?://#i', $v);
    $phoneDigits = preg_replace('/[^0-9+]/', '', (string) $lead->phone);
    $validEmail = filter_var($lead->email, FILTER_VALIDATE_EMAIL);

    $contact = [
        'Nombre' => $lead->full_name,
        'Correo' => $lead->email,
        'Teléfono' => $lead->phone,
        'Empresa' => $lead->company,
        'Ciudad' => $lead->city,
        'Estado' => $lead->state,
        'Operación / giro' => $lead->operation,
    ];
    $origin = [
        'Página del formulario' => $lead->source_page,
        'Página de entrada (landing)' => $lead->landing_page,
        'Referrer' => $lead->referrer,
        'ID de sesión' => $lead->session_id,
        'gclid' => $lead->gclid,
        'gbraid' => $lead->gbraid,
        'wbraid' => $lead->wbraid,
        'fbclid' => $lead->fbclid,
        'utm_source' => $lead->utm_source,
        'utm_medium' => $lead->utm_medium,
        'utm_campaign' => $lead->utm_campaign,
        'utm_term' => $lead->utm_term,
        'utm_content' => $lead->utm_content,
    ];
    $monoKeys = ['ID de sesión', 'gclid', 'gbraid', 'wbraid', 'fbclid'];
    $linkKeys = ['Página del formulario', 'Página de entrada (landing)', 'Referrer'];
    $webhookConfigured = filled(config('webleads.webhook_url'));
@endphp
<div style="display:flex;flex-direction:column;gap:16px;min-width:0" data-web-lead-id="{{ $lead->id }}">

    <div class="au-card">
        <div class="au-card-body">
            <div class="au-flex" style="gap:8px;flex-wrap:wrap;align-items:center">
                <span class="au-badge au-badge-info"><span class="au-badge-dot"></span>{{ $lead->form_label }}</span>
                <span class="au-badge au-badge-{{ $statusTone }}"><span class="au-badge-dot"></span>{{ \App\Models\WebLead::STATUSES[$lead->status] ?? $lead->status }}</span>
                <span class="au-help-text" style="margin:0">Recibida el {{ $fmt($lead->created_at) }} (hora de CDMX) · #{{ $lead->id }}</span>
            </div>
            <div class="au-flex" style="gap:8px;flex-wrap:wrap;margin-top:12px">
                @if ($lead->status !== 'contactado')
                    <a href="{{ route('admin.web-leads.update-status', $lead->id) }}?status=contactado" data-method="PATCH" class="au-btn au-btn-sm au-action-item">Marcar contactada</a>
                @endif
                @if ($lead->status !== 'descartado')
                    <a href="{{ route('admin.web-leads.update-status', $lead->id) }}?status=descartado" data-method="PATCH" class="au-btn au-btn-sm au-action-item">Descartar</a>
                @endif
                @if ($lead->status !== 'nuevo')
                    <a href="{{ route('admin.web-leads.update-status', $lead->id) }}?status=nuevo" data-method="PATCH" class="au-btn au-btn-sm au-action-item">Marcar nueva</a>
                @endif
                <a href="{{ route('admin.web-leads.resend', $lead->id) }}" data-method="POST" class="au-btn au-btn-sm au-action-item">Reenviar aviso</a>
                <a href="{{ route('admin.web-leads.destroy', $lead->id) }}" class="au-btn au-btn-sm au-btn-critical delete-item"
                   data-au-summary="{{ json_encode([['label' => 'Nombre', 'value' => $lead->full_name], ['label' => 'Formulario', 'value' => $lead->form_label]], JSON_UNESCAPED_UNICODE) }}">Borrar</a>
            </div>
        </div>
    </div>

    <div class="au-card">
        <div class="au-card-header"><div class="au-card-title">Contacto</div></div>
        <div class="au-table-wrap">
            <table class="au-table">
                <tbody>
                    @foreach ($contact as $label => $value)
                        <tr>
                            <td style="width:34%;white-space:nowrap"><strong>{{ $label }}</strong></td>
                            <td style="word-break:break-all">
                                @if ($value === null || $value === '')
                                    —
                                @elseif ($label === 'Correo' && $validEmail)
                                    <a href="mailto:{{ $value }}">{{ $value }}</a>
                                @elseif ($label === 'Teléfono' && $phoneDigits !== '')
                                    <a href="tel:{{ $phoneDigits }}">{{ $value }}</a>
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

    <div class="au-card">
        <div class="au-card-header"><div class="au-card-title">Mensaje</div></div>
        <div class="au-card-body">
            @if (filled($lead->message))
                <div style="white-space:pre-wrap;word-break:break-word">{{ $lead->message }}</div>
            @else
                <span class="au-help-text">El visitante no escribió ningún mensaje.</span>
            @endif
        </div>
    </div>

    <div class="au-card">
        <div class="au-card-header"><div class="au-card-title">Origen y atribución</div></div>
        <div class="au-table-wrap">
            <table class="au-table">
                <tbody>
                    @foreach ($origin as $label => $value)
                        <tr>
                            <td style="width:34%;white-space:nowrap"><strong>{{ $label }}</strong></td>
                            <td style="word-break:break-all" class="{{ in_array($label, $monoKeys, true) ? 'au-mono' : '' }}">
                                @if ($value === null || $value === '')
                                    —
                                @elseif (in_array($label, $linkKeys, true) && $isHttp($value))
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

    <div class="au-card">
        <div class="au-card-header"><div class="au-card-title">Avisos</div></div>
        <div class="au-table-wrap">
            <table class="au-table">
                <tbody>
                    <tr>
                        <td style="width:34%;white-space:nowrap"><strong>Correo</strong></td>
                        <td>
                            @if ($lead->notified_at)
                                <span class="au-badge au-badge-success"><span class="au-badge-dot"></span>Enviado</span>
                                <span class="au-help-text">{{ $fmt($lead->notified_at) }}</span>
                            @elseif (filled($lead->notify_error))
                                <span class="au-badge au-badge-critical"><span class="au-badge-dot"></span>Error</span>
                            @else
                                <span class="au-badge au-badge-warning"><span class="au-badge-dot"></span>Pendiente</span>
                            @endif
                            @if (filled($lead->notify_error))
                                <div style="margin-top:6px;white-space:pre-wrap;word-break:break-word;color:var(--au-critical)">{{ $lead->notify_error }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="white-space:nowrap"><strong>Webhook (n8n)</strong></td>
                        <td>
                            @if ($lead->webhook_sent_at)
                                <span class="au-badge au-badge-success"><span class="au-badge-dot"></span>Enviado</span>
                                <span class="au-help-text">{{ $fmt($lead->webhook_sent_at) }}</span>
                            @elseif (filled($lead->webhook_error))
                                <span class="au-badge au-badge-critical"><span class="au-badge-dot"></span>Error</span>
                            @elseif ($webhookConfigured)
                                <span class="au-badge au-badge-warning"><span class="au-badge-dot"></span>Pendiente</span>
                            @else
                                <span class="au-badge au-badge-info"><span class="au-badge-dot"></span>No configurado</span>
                            @endif
                            @if (filled($lead->webhook_error))
                                <div style="margin-top:6px;white-space:pre-wrap;word-break:break-word;color:var(--au-critical)">{{ $lead->webhook_error }}</div>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

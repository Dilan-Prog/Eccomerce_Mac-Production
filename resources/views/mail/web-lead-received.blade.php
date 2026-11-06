<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva solicitud web</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #222; font-size: 14px;">
    <h2 style="margin: 0 0 4px;">Nueva solicitud web: {{ $lead->form_label }}</h2>
    <p style="margin: 0 0 16px; color: #666;">
        Folio #{{ $lead->id }} &middot; {{ $lead->created_at?->format('d/m/Y H:i') }}
    </p>

    @php
        $rows = [
            'Nombre' => $lead->full_name,
            'Correo' => $lead->email,
            'Teléfono' => $lead->phone,
            'Empresa' => $lead->company,
            'Ciudad' => $lead->city,
            'Estado' => $lead->state,
            'Operación' => $lead->operation,
        ];
        $origin = [
            'Página del formulario' => $lead->source_page,
            'Página de entrada' => $lead->landing_page,
            'Referido de' => $lead->referrer,
            'Sesión' => $lead->session_id,
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
    @endphp

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 640px;">
        @foreach ($rows as $label => $value)
            @if ($value !== null && $value !== '')
                <tr>
                    <td style="border-bottom: 1px solid #eee; width: 160px; color: #666;">{{ $label }}</td>
                    <td style="border-bottom: 1px solid #eee;">{{ $value }}</td>
                </tr>
            @endif
        @endforeach
    </table>

    @if ($lead->message)
        <h3 style="margin: 20px 0 6px;">Mensaje</h3>
        <div style="white-space: pre-wrap; max-width: 640px; padding: 10px; background: #f6f6f6; border-radius: 4px;">{{ $lead->message }}</div>
    @endif

    <h3 style="margin: 20px 0 6px;">Origen de la solicitud</h3>
    <table cellpadding="4" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 640px; font-size: 12px;">
        @php($hasOrigin = false)
        @foreach ($origin as $label => $value)
            @if ($value !== null && $value !== '')
                @php($hasOrigin = true)
                <tr>
                    <td style="border-bottom: 1px solid #eee; width: 160px; color: #666;">{{ $label }}</td>
                    <td style="border-bottom: 1px solid #eee; word-break: break-all;">{{ $value }}</td>
                </tr>
            @endif
        @endforeach
        @unless ($hasOrigin)
            <tr><td style="color: #888;">Sin datos de origen (entrada directa).</td></tr>
        @endunless
    </table>

    <p style="margin-top: 24px; color: #888; font-size: 12px;">
        Responde a este correo para contestar directamente al cliente.
    </p>
</body>
</html>

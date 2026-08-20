<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #000; }
  table { border-collapse: collapse; width: 100%; }
  .wrap { padding: 20px 26px; }

  /* ── ENCABEZADO ── */
  .head-table td { vertical-align: top; padding: 0; }
  .head-logo-cell { width: 350px; }
  .head-logo-cell img { width: 350px; display: block; }
  .head-company-cell { padding-top: 8px; }
  .company-name { font-size: 12px; font-weight: 700; }
  .company-meta-table { margin-top: 3px; }
  .company-meta-table td { padding: 1px 6px 1px 0; font-size: 9px; vertical-align: top; }
  .company-meta-table .lbl { font-weight: 700; white-space: nowrap; width: 90px; }
  .head-folio-cell { width: 150px; text-align: right; font-size: 9px; }
  .head-folio-cell .lbl { font-weight: 700; }
  .head-folio-cell .folio-val { font-weight: 700; font-size: 11px; }
  .head-folio-cell .block { margin-top: 6px; }

  .divider { height: 1px; background: #000; margin: 6px 0; }

  /* ── DATOS CLIENTE / VENDEDOR ── */
  .client-table td { padding: 1px 0; font-size: 9px; vertical-align: top; }
  .client-table .lbl { font-weight: 700; width: 70px; }

  /* ── TABLA DE PRODUCTOS ── */
  .prod-table { margin-top: 10px; }
  .prod-table th {
      border-bottom: 1.5px solid #000; border-top: 1.5px solid #000;
      font-size: 9px; font-weight: 700; text-align: left; padding: 4px 4px;
  }
  .prod-table th.right, .prod-table td.right { text-align: right; }
  .prod-table td { padding: 4px 4px; font-size: 9px; vertical-align: top; border-bottom: 1px solid #ddd; }
  .prod-table .item-desc-sub { font-size: 8px; color: #444; }

  /* ── TOTALES ── */
  .totals-table { width: 220px; margin-left: auto; margin-top: 6px; }
  .totals-table td { padding: 2px 6px; font-size: 10px; }
  .totals-table .lbl { font-weight: 700; }
  .totals-table .val { text-align: right; font-weight: 700; }

  .note-line { margin-top: 10px; font-size: 9px; font-weight: 700; }
  .vigencia-line { margin-top: 20px; font-size: 10px; }
  .vigencia-line .lbl { font-weight: 700; }
  .vigencia-line .val { font-weight: 400; }
  .en-letras { margin-top: 10px; font-size: 10px; font-weight: 700; }

  /* ── CUENTAS BANCARIAS ── */
  .bank-accounts-row { margin-top: 20px; }
  .bank-accounts-title { font-size: 10px; font-weight: 700; }
  .bank-accounts-table { margin-top: 4px; }
  .bank-accounts-table th {
      border-bottom: 1px solid #000; font-size: 9px; font-weight: 700;
      text-align: left; padding: 4px 4px;
  }
  .bank-accounts-table td { padding: 4px 4px; font-size: 9px; vertical-align: top; border-bottom: 1px solid #ddd; }

  /* ── MARCAS ── */
  .brands-row { margin-top: 40px; text-align: center; }
  .brands-row img { height: 24px; margin: 0 10px; vertical-align: middle; }

  /* ── CONDICIONES ── */
  .conditions-title { margin-top: 14px; font-size: 10px; font-weight: 700; }
  .conditions-list { margin-top: 4px; font-size: 8px; line-height: 1.5; }
  .cond-highlight { color: #C0392B; }

  .env-note { margin-top: 10px; text-align: center; font-size: 8px; color: #2E7D32; }
</style>
</head>
<body>
<div class="wrap">

{{-- ENCABEZADO: logo arriba (35% de ancho, estirado), Nombre/R.F.C./Domicilio
     fiscal apilados debajo — folio/fecha/condiciones se mantienen a la
     derecha, alineados con ambas filas vía rowspan. --}}
<table class="head-table">
    <tr>
        <td class="head-logo-cell">
            @php($logoDataUri = uploadedImageToBase64(asset('uploads/logo/webp-horizontal.webp')))
            @if ($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="Mac Del Norte">
            @endif
        </td>
        <td class="head-folio-cell" rowspan="2">
            <div><span class="lbl">COTIZACIÓN No.:</span></div>
            <div class="folio-val">{{ $cotizacion->folio }}</div>
            <div class="block"><span class="lbl">Fecha</span><br>{{ $cotizacion->created_at->format('d/m/Y') }}</div>
            @if (!empty($cotizacion->tiempo_entrega_general))
                {{-- Fecha capturada por el vendedor (AdminCotizacionController::updateCurrency()) —
                     sin ningún valor por defecto; antes esto duplicaba por error la fecha de creación. --}}
                <div class="block"><span class="lbl">Tiempo de entrega</span><br>{{ $cotizacion->tiempo_entrega_general->format('d/m/Y') }}</div>
            @endif
            <div class="block"><span class="lbl">Condiciones de pago</span><br>CONTADO</div>
        </td>
    </tr>
    <tr>
        <td class="head-company-cell">
            <div class="company-name">MAC DEL NORTE - MONITOREO, AUTOMATIZACION Y CONTROLES DEL NORTE</div>
            <table class="company-meta-table">
                <tr>
                    <td class="lbl">R.F.C.:</td>
                    <td>NMA180313M46</td>
                </tr>
                <tr>
                    <td class="lbl">Domicilio fiscal:</td>
                    <td>
                        Calle: CASTAÑO No. 718, Col. EBANOS NORTE, CP: 66612, APODACA, NUEVO LEON, MEXICO
                        &nbsp;&nbsp;TEL: 8124738768 o 8124738744&nbsp;&nbsp;www.macdelnorte.com
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="divider"></div>

{{-- DATOS DEL CLIENTE / VENDEDOR --}}
<table class="client-table">
    <tr>
        <td class="lbl">Cliente:</td>
        <td>{{ $user->id }} &nbsp; {{ mb_strtoupper($user->name) }}</td>
    </tr>
    <tr>
        <td class="lbl">Calle:</td>
        <td>
            @if ($perfil)
                {{ mb_strtoupper($perfil->direccion_fiscal) }} &nbsp; RFC: {{ mb_strtoupper($perfil->rfc) }}
            @else
                Cotización interna — sin datos fiscales
            @endif
        </td>
    </tr>
    <tr>
        <td class="lbl">Vendedor:</td>
        <td>{{ $cotizacion->createdByAdmin->name ?? 'Cotizador Web (autoservicio)' }}</td>
    </tr>
    <tr>
        <td class="lbl">Enviar a:</td>
        <td>&nbsp;</td>
    </tr>
</table>

<?php
    $displayRows = [];
    $displaySubtotal = 0;
    foreach ($cotizacion->productos_json as $p) {
        $montoOrigenSubtotal = ($p['precio_origen'] ?? null) !== null ? $p['precio_origen'] * $p['cantidad'] : null;
        $puDisplay = $cotizacion->displayItemAmount($p['precio'], $p['moneda_origen'] ?? null, $p['precio_origen'] ?? null);
        $subtotalDisplay = $cotizacion->displayItemAmount($p['subtotal'], $p['moneda_origen'] ?? null, $montoOrigenSubtotal);
        $displayRows[] = ['p' => $p, 'pu' => $puDisplay, 'subtotal' => $subtotalDisplay];
        $displaySubtotal += $subtotalDisplay;
    }
    $displaySubtotal = round($displaySubtotal, 2);
    $displayIva = round($displaySubtotal * $ivaValue / 100, 2);
    $displayTotal = $displaySubtotal + $displayIva;
?>

{{-- TABLA DE PRODUCTOS --}}
<table class="prod-table">
    <thead>
        <tr>
            <th style="width:50px;">Cantidad</th>
            <th style="width:80px;">Clave</th>
            <th>Descripción</th>
            <th class="right" style="width:70px;">P/U</th>
            <th class="right" style="width:80px;">Importe</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($displayRows as $row)
        @php($p = $row['p'])
        <tr>
            <td>{{ number_format($p['cantidad'], 2) }}</td>
            <td>{{ $p['sku'] ?? '' }}</td>
            <td>
                {{ $p['nombre'] }}
                @if (!empty($p['marca']) || !empty($p['modelo']))
                    <div class="item-desc-sub">
                        {{ mb_strtoupper(trim(($p['marca'] ?? '') . ' ' . ($p['modelo'] ?? ''))) }}
                    </div>
                @endif
                @if (!empty($p['es_pendiente']))
                    <div class="item-desc-sub cond-highlight">
                        PENDIENTE DE SURTIR — TIEMPO DE ENTREGA: {{ $p['tiempo_entrega'] ?? 'Por confirmar' }}
                    </div>
                @elseif (!empty($p['tiempo_entrega']))
                    {{-- Nota de tiempo de entrega en un producto CON stock (o personalizado) —
                         sin el prefijo "PENDIENTE DE SURTIR" ni el color de advertencia, para no
                         implicar que falta inventario. Mismo criterio que en el builder admin. --}}
                    <div class="item-desc-sub">
                        TIEMPO DE ENTREGA: {{ $p['tiempo_entrega'] }}
                    </div>
                @endif
            </td>
            <td class="right">{{ number_format($row['pu'], 2, '.', ',') }}</td>
            <td class="right">{{ number_format($row['subtotal'], 2, '.', ',') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- TOTALES --}}
<table class="totals-table">
    <tr><td class="lbl">Subtotal</td><td class="val">{{ number_format($displaySubtotal, 2, '.', ',') }}</td></tr>
    <tr><td class="lbl">I.V.A.</td><td class="val">{{ number_format($displayIva, 2, '.', ',') }}</td></tr>
    <tr><td class="lbl">Total ({{ $cotizacion->currency }})</td><td class="val">{{ number_format($displayTotal, 2, '.', ',') }}</td></tr>
</table>

{{-- El costo de envío (si aplica) ya no es una leyenda fija en el pie —
     antes decía "TIEMPO DE ENTREGA INMEDIATA, ENVIO GRATIS" si el total
     >= $2,299 MXN, lo cual podía contradecir el tiempo de entrega real de
     cada producto (ver arriba). Ahora, si el vendedor eligió "Envío con
     costo" en el builder, ese monto ya viene como una partida más en la
     tabla de productos de arriba — no necesita tratamiento aparte aquí. --}}

@unless (collect($cotizacion->productos_json)->contains(fn ($p) => ($p['nombre'] ?? null) === 'Envío'))
    {{-- Sin partida "Envío" en la cotización = el vendedor dejó/eligió
         "Envío gratis" en el builder (ver storeItem() y Envío#4 arriba).
         Si SÍ hay una partida "Envío" (envío con costo), no se imprime nada
         aquí — el monto ya se ve en la tabla de productos, no hace falta
         repetirlo como leyenda. --}}
    <div class="note-line">ENVÍO GRATIS</div>
@endunless

<div class="vigencia-line">
    <span class="lbl">La cotización será vigente hasta el día</span>
    <span class="val">{{ $cotizacion->created_at->copy()->addDays(15)->format('d/m/Y') }}</span>
</div>

<div class="en-letras">
    {{ $cotizacion->currency === 'USD'
        ? numeroALetras($displayTotal, 'dólares', 'usd')
        : numeroALetras($displayTotal) }}
</div>

{{-- CUENTAS BANCARIAS --}}
@php($bankAccounts = \App\Models\BankAccount::where('status', 1)->get())
@if ($bankAccounts->isNotEmpty())
<div class="bank-accounts-row">
    <div class="bank-accounts-title">Cuentas Bancarias</div>
    <table class="bank-accounts-table">
        <thead>
            <tr>
                <th style="width:36px"></th>
                <th>Banco</th>
                <th>Titular</th>
                <th>Moneda</th>
                <th>No. de Cuenta</th>
                <th>No. de Tarjeta</th>
                <th>CLABE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bankAccounts as $account)
            <tr>
                <td>
                    @php($accountLogo = $account->logo ? uploadedImageToBase64($account->logo) : null)
                    @if ($accountLogo)
                        <img src="{{ $accountLogo }}" alt="{{ $account->banco }}" style="width:28px;height:28px;object-fit:contain;display:block">
                    @endif
                </td>
                <td>{{ $account->banco }}</td>
                <td>{{ $account->titular }}</td>
                <td>{{ $account->moneda ?? 'MXN' }}</td>
                <td>{{ $account->numero_cuenta ?? '—' }}</td>
                <td>{{ $account->numero_tarjeta ?? '—' }}</td>
                <td>{{ $account->clabe ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- MARCAS --}}
@php($brands = \App\Models\Brand::where('status', 1)->get())
@if ($brands->isNotEmpty())
<div class="brands-row">
    @foreach ($brands as $brand)
        @php($brandLogo = uploadedImageToBase64($brand->logo))
        @if ($brandLogo)
            <img src="{{ $brandLogo }}" alt="{{ $brand->name }}">
        @endif
    @endforeach
</div>
@endif

{{-- CONDICIONES COMERCIALES --}}
<div class="conditions-title">Condiciones Comerciales</div>
<div class="conditions-list">
    1. Esta Cotizacion tiene una vigencia de 20 Dias<br>
    2. Lugar de entrega LAB SU PLANTA<br>
    3. Cualquier Cambio que no este descrito en la cotizacion se tendra que Recotizar<br>
    4. Tiempo de entrega inicia despues de haber recibido su orden de compra o su anticipo según corresponda la negociacion con su ejecutivo de ventas<br>
    5. No se aceptan Cambios o Cancelaciones por parte del cliente despues de haber recibido su orden de Compra<br>
    <span class="cond-highlight">6. SI su Cotizacion es en DOLARES y desea hacer el pago en Moneda Nacional se debera Pagar A precio venta ventanillas BANCOMER.</span>
</div>

<div class="env-note">Antes de imprimir, piense en su responsabilidad y compromiso con el MEDIO AMBIENTE</div>

</div>
</body>
</html>

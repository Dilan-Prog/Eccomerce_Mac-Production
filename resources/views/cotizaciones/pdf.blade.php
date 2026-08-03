<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 11px; color: #1A202C; line-height: 1.5;
    background: #fff;
  }
  /* ── HEADER ── */
  .pdf-header {
    background: #003E7E; color: #fff;
    padding: 18px 30px; display: table; width: 100%;
  }
  .pdf-header-left { display: table-cell; vertical-align: middle; }
  .pdf-header-right { display: table-cell; text-align: right; vertical-align: middle; }
  .logo-box {
    display: inline-block; background: #F7941D;
    width: 40px; height: 40px; line-height: 40px;
    text-align: center; font-size: 22px; font-weight: 900;
    color: #fff; border-radius: 6px; vertical-align: middle;
  }
  .logo-text {
    display: inline-block; vertical-align: middle; margin-left: 10px;
  }
  .logo-text .name { font-size: 16px; font-weight: 900; letter-spacing: -0.3px; }
  .logo-text .slogan { font-size: 9px; opacity: 0.75; font-style: italic; }
  .folio-box { font-size: 13px; font-weight: 700; }
  .folio-box .folio-num {
    font-size: 18px; font-weight: 900; color: #F6AD1C; display: block;
  }
  .fecha { font-size: 10px; opacity: 0.8; margin-top: 4px; }

  /* ── SECCIONES ── */
  .section { padding: 16px 30px; }
  .section-title {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.5px; color: #003E7E; margin-bottom: 10px;
    padding-bottom: 5px; border-bottom: 2px solid #F7941D;
  }
  .info-table { width: 100%; border-collapse: collapse; }
  .info-table td { padding: 4px 6px; font-size: 11px; vertical-align: top; }
  .info-table .lbl { color: #718096; font-weight: 700; width: 140px; }
  .info-table .val { color: #1A202C; }

  /* ── TABLA DE PRODUCTOS ── */
  .prod-table {
    width: 100%; border-collapse: collapse; margin-top: 4px;
  }
  .prod-table th {
    background: #003E7E; color: #fff; font-size: 10px; font-weight: 700;
    padding: 8px 10px; text-align: left; text-transform: uppercase; letter-spacing: 0.5px;
  }
  .prod-table th.right { text-align: right; }
  .prod-table td { padding: 7px 10px; font-size: 11px; border-bottom: 1px solid #DDE3EA; }
  .prod-table td.right { text-align: right; }
  .prod-table tr:nth-child(even) td { background: #F5F7FA; }
  .prod-table .item-name { font-weight: 600; color: #003E7E; }
  .prod-table .item-sku {
    font-size: 9px; color: #718096; font-family: monospace; margin-top: 2px;
  }

  /* ── TOTALES ── */
  .totals-table {
    width: 260px; margin-left: auto; margin-top: 10px;
    border-collapse: collapse;
  }
  .totals-table td { padding: 5px 10px; font-size: 11px; }
  .totals-table .lbl { color: #718096; }
  .totals-table .val { text-align: right; font-weight: 700; color: #1A202C; }
  .totals-table .total-row td {
    background: #003E7E; color: #fff; font-size: 13px;
    font-weight: 900; border-top: 2px solid #F7941D;
    padding: 8px 10px;
  }
  .totals-table .total-row .val { color: #F6AD1C; }
  .iva-note { font-size: 9px; color: #718096; margin-top: 4px; }

  /* ── VIGENCIA ── */
  .vigencia {
    background: #E6EFF8; border-left: 4px solid #003E7E;
    padding: 10px 16px; margin: 0 30px 16px; border-radius: 0 6px 6px 0;
    font-size: 10px; color: #003E7E;
  }

  /* ── FOOTER ── */
  .pdf-footer {
    background: #002856; color: rgba(255,255,255,0.75);
    padding: 12px 30px; text-align: center; font-size: 9px; margin-top: 10px;
  }
  .pdf-footer strong { color: #F6AD1C; }

  .divider { height: 1px; background: #DDE3EA; margin: 0 30px; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="pdf-header">
    <div class="pdf-header-left">
        <span class="logo-box">M</span>
        <div class="logo-text">
            <div class="name">Mac Del Norte</div>
            <div class="slogan">No somos una opción, somos la elección correcta</div>
        </div>
    </div>
    <div class="pdf-header-right">
        <div class="folio-box">
            Cotización Formal
            <span class="folio-num">{{ $cotizacion->folio }}</span>
        </div>
        <div class="fecha">Fecha: {{ $cotizacion->created_at->format('d/m/Y') }}</div>
    </div>
</div>

{{-- DATOS DEL CLIENTE --}}
<div class="section">
    <div class="section-title">Datos del cliente</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Nombre completo:</td>
            <td class="val">{{ $user->name }}</td>
            <td class="lbl">Correo:</td>
            <td class="val">{{ $user->email }}</td>
        </tr>
        @if($perfil)
        <tr>
            <td class="lbl">Teléfono:</td>
            <td class="val">{{ $telefono ?: '—' }}</td>
            <td class="lbl">Tipo de persona:</td>
            <td class="val">{{ $perfil->tipo_persona === 'empresa' ? 'Persona Moral (Empresa)' : 'Persona Física' }}</td>
        </tr>
        <tr>
            <td class="lbl">RFC:</td>
            <td class="val">{{ $perfil->rfc }}</td>
            @if($perfil->tipo_persona === 'empresa')
                <td class="lbl">Razón Social:</td>
                <td class="val">{{ $perfil->razon_social ?? '—' }}</td>
            @else
                <td class="lbl"></td>
                <td class="val"></td>
            @endif
        </tr>
        <tr>
            <td class="lbl">Dirección fiscal:</td>
            <td class="val" colspan="3">{{ $perfil->direccion_fiscal }}</td>
        </tr>
        @else
        <tr>
            <td class="val" colspan="4">Cotización interna — sin datos fiscales</td>
        </tr>
        @endif
    </table>
</div>

<div class="divider"></div>

{{-- TABLA DE PRODUCTOS --}}
<div class="section">
    <div class="section-title">Productos cotizados</div>
    <table class="prod-table">
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th>Descripción / Nombre</th>
                <th style="width:120px;">SKU / Modelo</th>
                <th class="right" style="width:55px;">Cant.</th>
                <th class="right" style="width:90px;">P. Unitario</th>
                <th class="right" style="width:90px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->productos_json as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <div class="item-name">{{ $p['nombre'] }}</div>
                    @if(!empty($p['marca']))
                        <div class="item-sku">Marca: {{ $p['marca'] }}</div>
                    @endif
                </td>
                <td>
                    @if(!empty($p['sku']))
                        <div class="item-sku">SKU: {{ $p['sku'] }}</div>
                    @endif
                    @if(!empty($p['modelo']))
                        <div class="item-sku">Mod: {{ $p['modelo'] }}</div>
                    @endif
                </td>
                <td class="right">{{ $p['cantidad'] }}</td>
                <td class="right">${{ number_format($p['precio'], 2, '.', ',') }}</td>
                <td class="right">${{ number_format($p['subtotal'], 2, '.', ',') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTALES --}}
    <table class="totals-table">
        <tr>
            <td class="lbl">Subtotal (s/IVA):</td>
            <td class="val">${{ number_format($subtotalSinIva, 2, '.', ',') }} MXN</td>
        </tr>
        <tr>
            <td class="lbl">IVA (16%):</td>
            <td class="val">${{ number_format($iva, 2, '.', ',') }} MXN</td>
        </tr>
        <tr class="total-row">
            <td class="lbl">TOTAL:</td>
            <td class="val">${{ number_format($cotizacion->total, 2, '.', ',') }} MXN</td>
        </tr>
    </table>
    <div class="iva-note" style="text-align:right;margin-top:4px;">* Precios con IVA incluido.</div>
</div>

{{-- VIGENCIA --}}
<div class="vigencia">
    <strong>Nota:</strong> Esta cotización tiene vigencia de <strong>15 días hábiles</strong>
    a partir de la fecha de emisión ({{ $cotizacion->created_at->format('d/m/Y') }}).
    Sujeta a disponibilidad de inventario.
</div>

{{-- FOOTER --}}
<div class="pdf-footer">
    <strong>Mac Del Norte</strong> &nbsp;·&nbsp; Distribuidor autorizado Honeywell &nbsp;·&nbsp;
    contacto@macdelnorte.com &nbsp;·&nbsp; 81-3582-5559 &nbsp;·&nbsp;
    Monterrey, N.L. · Envíos a todo México
</div>

</body>
</html>

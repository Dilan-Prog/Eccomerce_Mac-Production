{{-- resources/views/technician/reports/index.blade.php --}}
@extends('technician.layouts.master')

@push('styles')
<link rel="stylesheet" href="//cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<style>
  :root {
    --Primary-Blue-Dark:#00468c;
    --Secondaryy-Blue-Dark:#7fbae7;
    --Tercer-Blue-Dark:#53bdfe;
    --Secondary-Blue-Low:#bfdfff;
    --Primary-Blue-Dark-Hover:#005ab5;
    --Backgroud-pure-white:#ffffff;
    --Background-black-2:#212121;
    --Backgroud-black:#000000;
    --Backgroud-Product:#ececec;
    --Background:#f4f4f4;
    --White-Box:#ffffff;
    --Text-Primary-white:#ffffff;
    --Text-Primary-black:#000000;
    --Text-Secondary:#313131;
    /* Aliases semánticos */
    --navy:     var(--Primary-Blue-Dark);
    --navy-mid: var(--Primary-Blue-Dark-Hover);
    --orange:   var(--Tercer-Blue-Dark);
    --green:#10B981;--red:#EF4444;--amber:#F59E0B;
  }
  .dt-search input { border:1px solid #ddd;border-radius:6px;padding:4px 10px; }
  #pdfModal .modal-header { background:var(--navy);color:#fff; }
  .badge-completed { background:var(--green);color:#fff; }
  .badge-draft     { background:var(--amber);color:#fff; }
</style>
@endpush

@section('content')
<section class="section">
  <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1>Mis Reportes de Servicio</h1>
    <a href="{{ route('technician.reports.create') }}" class="btn text-white" style="background:var(--orange)">
      <i class="fas fa-plus"></i> Nuevo Reporte
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="reportsTable" class="table table-striped table-hover w-100">
          <thead >
            <tr>
              <th>Folio</th>
              <th>Fecha Servicio</th>
              <th>Tipo</th>
              <th>Cliente</th>
              <th>Equipo</th>
              <th>Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($reports as $r)
            <tr>
              <td><strong>{{ $r->folio }}</strong></td>
              <td>{{ $r->fecha_servicio ? $r->fecha_servicio->format('d/m/Y') : '—' }}</td>
              <td>{{ $r->tipo_servicio ?? '—' }}</td>
              <td>{{ $r->cliente_nombre ?? '—' }}</td>
              <td>{{ $r->equipo_descripcion ?? '—' }}</td>
              <td>
                @if($r->status === 'completed')
                  <span class="badge badge-completed">Completado</span>
                @else
                  <span class="badge badge-draft">Borrador</span>
                @endif
              </td>
              <td class="text-center">
                @if($r->status === 'completed')
                  <button onclick="verPDF({{ $r->id }})"
                          class="btn btn-sm text-white me-1" style="background:var(--navy-mid)"
                          title="Generar PDF">
                    <i class="fas fa-file-pdf"></i> PDF
                  </button>
                @else
                  <a href="{{ route('technician.reports.create') }}?draft={{ $r->id }}"
                     class="btn btn-sm btn-warning me-1" title="Continuar borrador">
                    <i class="fas fa-edit"></i> Continuar
                  </a>
                @endif
                <button onclick="eliminarReporte({{ $r->id }})"
                        class="btn btn-sm btn-danger" title="Eliminar">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

{{-- Modal loading PDF --}}
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Generando PDF…</h5>
      </div>
      <div class="modal-body text-center py-4">
        <div class="spinner-border" style="color:var(--orange)" role="status"></div>
        <p class="mt-3 mb-0">Por favor espera…</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="//cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
$('#reportsTable').DataTable({
  language: {
    url: '//cdn.datatables.net/plug-ins/2.0.5/i18n/es-ES.json'
  },
  order: [[0, 'desc']],
  pageLength: 15,
});

const BASE = '{{ url("/technician/reports") }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

let logoBase64 = null;
async function loadLogo() {
  try {
    const res = await fetch('{{ asset("frontend/images/logo/Aviblnco-largo.png") }}');
    const blob = await res.blob();
    logoBase64 = await new Promise(r => {
      const reader = new FileReader();
      reader.onload = e => r(e.target.result);
      reader.readAsDataURL(blob);
    });
  } catch(e) { /* logo no disponible, se usará texto */ }
}
loadLogo();

/* Convierte una URL de imagen a base64 vía fetch (maneja espacios y guiones en nombre) */
async function urlToBase64(url) {
  // Encode espacios y caracteres especiales sin romper slashes
  const safeUrl = url.split('/').map((seg, i) =>
    i === 0 ? seg : encodeURIComponent(decodeURIComponent(seg))
  ).join('/');

  // Intento 1: fetch (funciona con "foto 1.jpg" y "foto-1.jpg")
  try {
    const res = await fetch(safeUrl);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const blob = await res.blob();
    return await new Promise(r => {
      const reader = new FileReader();
      reader.onload = e => r(e.target.result);
      reader.readAsDataURL(blob);
    });
  } catch(fetchErr) {
    // Intento 2: fallback canvas con crossOrigin
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.onload = () => {
        try {
          const canvas = document.createElement('canvas');
          canvas.width  = img.naturalWidth  || img.width;
          canvas.height = img.naturalHeight || img.height;
          canvas.getContext('2d').drawImage(img, 0, 0);
          resolve(canvas.toDataURL('image/jpeg', 0.85));
        } catch(e) { reject(e); }
      };
      img.onerror = () => reject(new Error('No se pudo cargar: ' + safeUrl));
      img.src = safeUrl;
    });
  }
}

/* Enriquece report.fotos (URLs del servidor) → {data: base64} para el PDF */
async function enrichFotos(fotos) {
  if (!fotos) return { antes: [], despues: [] };
  const result = { antes: [], despues: [] };
  for (const tipo of ['antes', 'despues']) {
    for (const foto of (fotos[tipo] || [])) {
      try {
        console.log('[PDF foto]', tipo, foto.url);
        const data = await urlToBase64(foto.url);
        result[tipo].push({ data });
      } catch(e) {
        console.warn('[PDF foto ERROR]', foto.url, e.message);
        result[tipo].push({ data: null });
      }
    }
  }
  return result;
}

async function verPDF(id) {
  if (typeof window.jspdf === 'undefined') {
    toastr.error('La librería PDF no está disponible. Recarga la página.');
    return;
  }
  $('#pdfModal .modal-title').text('Obteniendo reporte…');
  $('#pdfModal').modal('show');
  let report;
  try {
    const r = await fetch(`${BASE}/${id}`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    report = await r.json();
  } catch(e) {
    toastr.error('Error al obtener el reporte: ' + e.message);
    $('#pdfModal').modal('hide');
    return;
  }

  /* Convertir fotos URL → base64 antes de generar el PDF */
  if (report.fotos) {
    $('#pdfModal .modal-title').text('Cargando fotografías…');
    report.fotos = await enrichFotos(report.fotos);
  }

  $('#pdfModal .modal-title').text('Generando PDF…');
  try {
    generatePDF(report);
  } catch(e) {
    console.error('[PDF Error]', e);
    toastr.error('Error al generar PDF: ' + e.message);
  } finally {
    $('#pdfModal').modal('hide');
  }
}

async function eliminarReporte(id) {
  const res = await Swal.fire({
    title: '¿Eliminar reporte?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#EF4444',
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Sí, eliminar',
  });
  if (!res.isConfirmed) return;
  try {
    const r = await fetch(`${BASE}/${id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const d = await r.json();
    if (d.status === 'success') {
      toastr.success(d.message);
      setTimeout(() => location.reload(), 1200);
    } else {
      toastr.error('No se pudo eliminar.');
    }
  } catch(e) {
    toastr.error('Error de red.');
  }
}

/* ─── PDF GENERATION ─────────────────────────────────────────── */
function imgFormat(dataUrl) {
  const mime = (dataUrl || '').split(';')[0].split(':')[1] || '';
  if (mime === 'image/png')  return 'PNG';
  if (mime === 'image/webp') return 'WEBP';
  return 'JPEG';
}

function generatePDF(report) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ unit: 'mm', format: 'a4' });
  const PW  = 210;
  const M   = 14;
  const BOT = 281;
  let y = 0;

  /* ── Header ── */
  const HDR = 40; // altura total del header en mm
  function addHeader() {
    const bW = 54, bH = 20, bX = PW - 12 - bW, bY = 4;

    doc.setFillColor(0, 70, 140);
    doc.rect(0, 0, PW, HDR, 'F');

    if (logoBase64) {
      /* Logo arriba al 25% del ancho disponible, proporcional */
      const fullW = bX - M - 4;          // ancho "100%"
      const lgW   = Math.round(fullW * 0.55); // 25%
      const lgH   = Math.round(lgW * 22 / fullW); // proporcional
      doc.addImage(logoBase64, 'PNG', M, 5, lgW, lgH);
    } else {
      doc.setFont('helvetica', 'bold'); doc.setFontSize(15); doc.setTextColor(255, 255, 255);
      doc.text('MAC DEL NORTE', M, 14);
    }

    /* Texto de contacto debajo del logo, blanco */
    doc.setFont('helvetica', 'normal'); doc.setFontSize(6.5); doc.setTextColor(255, 255, 255);
    doc.text('MONITOREO, AUTOMATIZACIÓN Y CONTROLES DEL NORTE, S.A.P.I. de C.V.  ·  RFC: NMA180313M46', M, 28);
    doc.text('C. Castaño 718, Ebanos Norte 2do Sector, Apodaca N.L. CP.66612  ·  +81-3582-5559  ·  contacto@macdelnorte.com', M, 35);

    /* Línea de acento azul claro */
    doc.setFillColor(83, 189, 254);
    doc.rect(0, HDR, PW, 2, 'F');

    /* Badge: upper-right — fondo naranja, letras blancas */
    doc.setFillColor(247, 148, 29);
    doc.rect(bX, bY, bW, bH, 'F');
    doc.setFont('helvetica', 'bold'); doc.setFontSize(5.5); doc.setTextColor(255, 255, 255);
    doc.text('REPORTE DE SERVICIO', bX + bW / 2, bY + 6, { align: 'center' });
    doc.setDrawColor(255, 255, 255); doc.setLineWidth(0.3);
    doc.line(bX + 4, bY + 8, bX + bW - 4, bY + 8);
    doc.setFont('helvetica', 'bold'); doc.setFontSize(9); doc.setTextColor(255, 255, 255);
    doc.text(String(report.folio || '—'), bX + bW / 2, bY + 16, { align: 'center' });
  }

  /* ── Footer (added at the end for all pages) ── */
  function addFooters() {
    const total = doc.getNumberOfPages();
    for (let p = 1; p <= total; p++) {
      doc.setPage(p);
      doc.setFillColor(0, 70, 140);
      doc.rect(0, 285, PW, 12, 'F');
      doc.setFont('helvetica', 'normal'); doc.setFontSize(7); doc.setTextColor(255, 255, 255);
      doc.text('MAC DEL NORTE  |  contacto@macdelnorte.com  |  www.macdelnorte.com', PW / 2, 290, { align: 'center' });
      doc.text('Página ' + p + ' de ' + total, PW / 2, 294.5, { align: 'center' });
    }
  }

  function newPage() { doc.addPage(); addHeader(); y = HDR + 7; }
  function chk(h)    { if (y + h > BOT) newPage(); }

  /* ── Section title bar ── */
  function secTitle(num, label) {
    chk(14);
    doc.setFillColor(0, 70, 140);
    doc.rect(M, y, PW - M * 2, 8, 'F');
    doc.setFont('helvetica', 'bold'); doc.setFontSize(8.5); doc.setTextColor(83, 189, 254);
    doc.text(num + '.', M + 3, y + 5.5);
    doc.setTextColor(255, 255, 255);
    doc.text(label, M + 11, y + 5.5);
    y += 11;
  }

  /* ── Two-column field row (card style: label small/grey above, value dark below) ── */
  function row2(l1, v1, l2, v2) {
    chk(16);
    const half = (PW - M * 2) / 2;
    const cardW = half - 2;
    [[l1, v1, M], [l2, v2, M + half]].forEach(([label, val, x]) => {
      if (!label) return;
      doc.setFillColor(248, 250, 252);
      doc.rect(x, y, cardW, 14, 'F');
      doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(100, 100, 100);
      doc.text(label + ':', x + 3, y + 4.5);
      doc.setFont('helvetica', 'normal'); doc.setTextColor(20, 20, 20);
      doc.text(String(val || '—').substring(0, 38), x + 3, y + 10.5);
    });
    y += 17;
  }

  /* ── Full-width text block ── */
  function textBlock(label, val) {
    const lines = doc.splitTextToSize(String(val || 'Sin información.'), PW - M * 2);
    chk(lines.length * 4.5 + 10);
    doc.setFont('helvetica', 'bold'); doc.setFontSize(8); doc.setTextColor(80, 80, 80);
    doc.text(label + ':', M, y); y += 5;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(8); doc.setTextColor(20, 20, 20);
    doc.text(lines, M, y);
    y += lines.length * 4.5 + 5;
  }

  /* ══ PAGE 1 ══ */
  addHeader(); y = HDR + 7;

  /* ── Metadata bar: Fecha | Tipo de Servicio | Técnico ── */
  const fecha = (report.fecha_servicio || '').toString().substring(0, 10);
  const metaH = 14;
  doc.setFillColor(241, 245, 249);
  doc.rect(M, y, PW - M * 2, metaH, 'F');
  const metaCW = (PW - M * 2) / 3;
  [
    { label: 'FECHA DE SERVICIO', value: fecha || '—' },
    { label: 'TIPO DE SERVICIO',  value: report.tipo_servicio || '—' },
    { label: 'TÉCNICO',           value: report.tecnico_nombre || '—' },
  ].forEach((item, i) => {
    const mx = M + i * metaCW;
    if (i > 0) {
      doc.setDrawColor(200, 210, 220); doc.setLineWidth(0.3);
      doc.line(mx, y + 2, mx, y + metaH - 2);
    }
    doc.setFont('helvetica', 'bold'); doc.setFontSize(6); doc.setTextColor(100, 120, 140);
    doc.text(item.label, mx + metaCW / 2, y + 5, { align: 'center' });
    doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(20, 20, 20);
    doc.text(String(item.value).substring(0, 26), mx + metaCW / 2, y + 11, { align: 'center' });
  });
  y += metaH + 5;

  /* 1. Datos Generales */
  secTitle('1', 'DATOS GENERALES');
  row2('Folio', report.folio, 'Fecha de Servicio', fecha);
  row2('Tipo de Servicio', report.tipo_servicio, 'Técnico Responsable', report.tecnico_nombre);

  /* 2. Cliente */
  secTitle('2', 'DATOS DEL CLIENTE');
  row2('Nombre', report.cliente_nombre, 'Empresa', report.cliente_empresa);
  row2('RFC', report.cliente_rfc, 'Teléfono', report.cliente_telefono);
  row2('Email', report.cliente_email, null, null);
  textBlock('Dirección', report.cliente_direccion);

  /* 3. Equipo */
  secTitle('3', 'DATOS DEL EQUIPO / INSTRUMENTO');
  textBlock('Descripción del Equipo', report.equipo_descripcion);
  row2('Marca', report.equipo_marca, 'Modelo', report.equipo_modelo);
  row2('No. de Serie', report.equipo_serie, 'Ubicación / TAG', report.equipo_ubicacion_tag);

  /* 4. Mediciones */
  secTitle('4', 'TABLA DE MEDICIONES');
  const meds = Array.isArray(report.mediciones) ? report.mediciones : [];

  if (meds.length === 0) {
    chk(8);
    doc.setFont('helvetica', 'italic'); doc.setFontSize(8); doc.setTextColor(150, 150, 150);
    doc.text('Sin mediciones registradas.', M, y); y += 8;
  } else {
    const cols = ['Punto', 'Val. Referencia', 'Val. Medido', 'Error', 'Tolerancia', 'Resultado'];
    const cw   = [36, 28, 28, 22, 22, 26];
    const rH   = 7, hH = 9;
    let drawHead = true;

    function medHead() {
      chk(hH + rH);
      let cx = M;
      cols.forEach((c, i) => {
        doc.setFillColor(0, 90, 181); doc.rect(cx, y, cw[i], hH, 'F');
        doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(255, 255, 255);
        doc.text(c, cx + 3, y + 6); cx += cw[i];
      });
      y += hH; drawHead = false;
    }
    medHead();

    meds.forEach((m, idx) => {
      if (y + rH > BOT) { newPage(); drawHead = true; }
      if (drawHead) medHead();
      const cells = [m.punto, m.valor_referencia, m.valor_medido, m.error, m.tolerancia, m.resultado];
      let cx = M;
      cells.forEach((cell, i) => {
        let bg = idx % 2 === 0 ? [248, 250, 252] : [237, 241, 245];
        let tc = [20, 20, 20], fw = 'normal';
        if (i === 5) {
          if (cell === 'OK')    { bg = [16, 185, 129]; tc = [255, 255, 255]; fw = 'bold'; }
          if (cell === 'NO OK') { bg = [239, 68, 68];  tc = [255, 255, 255]; fw = 'bold'; }
          if (cell === 'N/A')   { bg = [200, 200, 200]; tc = [60, 60, 60]; }
        }
        doc.setFillColor(...bg); doc.setDrawColor(210, 210, 210); doc.setLineWidth(0.2);
        doc.rect(cx, y, cw[i], rH, 'FD');
        doc.setFont('helvetica', fw); doc.setFontSize(7.5); doc.setTextColor(...tc);
        doc.text(String(cell || '—'), cx + 3, y + 5); cx += cw[i];
      });
      y += rH;
    });

    /* Result banner */
    const hasNoOk = meds.some(m => m.resultado === 'NO OK');
    const allOk   = meds.length > 0 && meds.every(m => m.resultado === 'OK' || m.resultado === 'N/A');
    chk(12);
    if (hasNoOk) {
      doc.setFillColor(239, 68, 68);
      doc.rect(M, y + 2, PW - M * 2, 8, 'F');
      doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(255, 255, 255);
      doc.text('SE DETECTARON PUNTOS FUERA DE TOLERANCIA', PW / 2, y + 7.5, { align: 'center' });
    } else if (allOk) {
      doc.setFillColor(16, 185, 129);
      doc.rect(M, y + 2, PW - M * 2, 8, 'F');
      doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(255, 255, 255);
      doc.text('TODOS LOS PUNTOS DENTRO DE TOLERANCIA — EQUIPO CONFORME', PW / 2, y + 7.5, { align: 'center' });
    }
    y += 14;
  }

  /* 5. Fotografías */
  const fotosA = (report.fotos && Array.isArray(report.fotos.antes))   ? report.fotos.antes   : [];
  const fotosD = (report.fotos && Array.isArray(report.fotos.despues)) ? report.fotos.despues : [];

  secTitle('5', 'FOTOGRAFÍAS');

  if (fotosA.length === 0 && fotosD.length === 0) {
    chk(8);
    doc.setFont('helvetica', 'italic'); doc.setFontSize(8); doc.setTextColor(150, 150, 150);
    doc.text('Sin fotografías adjuntas.', M, y); y += 8;
  } else {
    const perRow = 3;
    const gap    = 3;
    const iW     = (PW - M * 2 - gap * (perRow - 1)) / perRow;
    const iH     = Math.round(iW * 0.72);
    const lblH   = 6;

    function photoGrid(groupLabel, photos) {
      if (photos.length === 0) return;
      chk(10);
      doc.setFont('helvetica', 'bold'); doc.setFontSize(8.5); doc.setTextColor(50, 50, 50);
      doc.text(groupLabel, M, y); y += 5;

      for (let i = 0; i < photos.length; i++) {
        const col = i % perRow;
        if (col === 0 && i > 0) y += iH + lblH + gap;
        if (col === 0) chk(iH + lblH + gap + 4);
        const cx = M + col * (iW + gap);
        try {
          const fmt = imgFormat(photos[i].data);
          doc.addImage(photos[i].data, fmt, cx, y, iW, iH);
          doc.setDrawColor(200, 200, 200); doc.setLineWidth(0.3);
          doc.rect(cx, y, iW, iH);
        } catch(e) {
          doc.setFillColor(240, 240, 240);
          doc.rect(cx, y, iW, iH, 'F');
          doc.setFont('helvetica', 'italic'); doc.setFontSize(7); doc.setTextColor(160, 160, 160);
          doc.text('Imagen no disponible', cx + 2, y + iH / 2);
        }
        doc.setFont('helvetica', 'normal'); doc.setFontSize(7); doc.setTextColor(100, 100, 100);
        doc.text('Foto ' + (i + 1), cx + iW / 2, y + iH + 4.5, { align: 'center' });
      }
      y += iH + lblH + gap + 6;
    }

    photoGrid('Antes del Servicio:', fotosA);
    photoGrid('Después del Servicio:', fotosD);
  }

  /* 6. Observaciones */
  secTitle('6', 'OBSERVACIONES Y RECOMENDACIONES');
  textBlock('Observaciones', report.observaciones);
  textBlock('Recomendaciones', report.recomendaciones);

  /* 7. Firmas */
  chk(52);
  secTitle('7', 'FIRMAS');
  const sigW = 90, sigH = 30;
  const sigX = (PW - sigW) / 2;
  if (report.firma_tecnico) {
    try { doc.addImage(report.firma_tecnico, 'PNG', sigX, y, sigW, sigH); } catch(e) {}
  }
  doc.setDrawColor(80, 80, 80); doc.setLineWidth(0.5);
  doc.line(sigX, y + sigH, sigX + sigW, y + sigH);
  doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(20, 20, 20);
  doc.text(report.tecnico_nombre || 'Técnico Responsable', PW / 2, y + sigH + 6, { align: 'center' });
  doc.setFont('helvetica', 'normal'); doc.setFontSize(7.5); doc.setTextColor(80, 80, 80);
  doc.text('Firma del Técnico Responsable', PW / 2, y + sigH + 11, { align: 'center' });
  y += sigH + 20;

  /* 8. Garantía */
  const gar = 'MAC DEL NORTE garantiza los servicios prestados en el presente reporte por un período de 30 (treinta) días naturales contados a partir de la fecha de servicio indicada. Esta garantía cubre exclusivamente los trabajos de mano de obra y los materiales suministrados por MAC DEL NORTE. No cubre daños causados por mal uso, negligencia, accidentes, modificaciones no autorizadas, desgaste natural o causas ajenas al servicio prestado. Contacto: contacto@macdelnorte.com  ·  +81-3582-5559.';
  const garL = doc.splitTextToSize(gar, PW - M * 2 - 10);
  const garH = garL.length * 4.5 + 16;
  chk(11 + garH + 4);

  doc.setFillColor(255, 251, 235);
  doc.setDrawColor(245, 158, 11);
  doc.setLineWidth(0.8);
  doc.roundedRect(M, y, PW - M * 2, garH, 2, 2, 'FD');

  doc.setFont('helvetica', 'bold');
  doc.setFontSize(8.5);
  doc.setTextColor(245, 158, 11);
  doc.text('GARANTÍA DE SERVICIO — 30 DÍAS NATURALES', M + 5, y + 7);

  doc.setDrawColor(245, 158, 11);
  doc.setLineWidth(0.3);
  doc.line(M + 5, y + 9.5, M + PW - M * 2 - 5, y + 9.5);

  doc.setFont('helvetica', 'normal');
  doc.setFontSize(7.5);
  doc.setTextColor(60, 60, 60);
  doc.text(garL, M + 5, y + 14);

  y += garH + 4;

  addFooters();
  doc.save('Reporte_' + (report.folio || 'MAC') + '.pdf');
}
</script>
@endpush

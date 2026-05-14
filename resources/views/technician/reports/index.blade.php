{{-- resources/views/technician/reports/index.blade.php --}}
@extends('technician.layouts.master')

@push('styles')
<link rel="stylesheet" href="//cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<style>
  :root {
    --navy:#0A1628;--navy-mid:#1E3355;--orange:#F47920;--green:#10B981;--red:#EF4444;--amber:#F59E0B;
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
          <thead style="background:var(--navy);color:#fff">
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

/* Convierte una URL de imagen a base64 para jsPDF */
function urlToBase64(url) {
  return fetch(url)
    .then(res => res.blob())
    .then(blob => new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload  = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    }));
}

/* Enriquece report.fotos (URLs del servidor) → {data: base64} para el PDF */
async function enrichFotos(fotos) {
  if (!fotos) return { antes: [], despues: [] };
  const result = { antes: [], despues: [] };
  for (const tipo of ['antes', 'despues']) {
    for (const foto of (fotos[tipo] || [])) {
      try {
        const data = await urlToBase64(foto.url);
        result[tipo].push({ data });
      } catch(e) {
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

  function addHeader() {
    doc.setFillColor(10, 22, 40);
    doc.rect(0, 0, PW, 28, 'F');
    doc.setFont('helvetica', 'bold'); doc.setFontSize(15); doc.setTextColor(255, 255, 255);
    doc.text('MAC DEL NORTE', M, 11);
    doc.setFontSize(6.5); doc.setFont('helvetica', 'normal');
    doc.text('MONITOREO, AUTOMATIZACIÓN Y CONTROLES DEL NORTE, S.A.P.I. de C.V.  ·  RFC: NMA180313M46', M, 18);
    doc.text('C. Castaño 718, Ebanos Norte 2do Sector, Apodaca N.L. CP.66612  ·  +81-3582-5559  ·  contacto@macdelnorte.com', M, 23);
    doc.setFillColor(244, 121, 32);
    doc.rect(0, 28, PW, 2, 'F');
  }

  function addFooters() {
    const total = doc.getNumberOfPages();
    for (let p = 1; p <= total; p++) {
      doc.setPage(p);
      doc.setFillColor(10, 22, 40);
      doc.rect(0, 285, PW, 12, 'F');
      doc.setFont('helvetica', 'normal'); doc.setFontSize(7); doc.setTextColor(255, 255, 255);
      doc.text('MAC DEL NORTE  |  contacto@macdelnorte.com  |  www.macdelnorte.com', PW / 2, 290, { align: 'center' });
      doc.text('Página ' + p + ' de ' + total, PW / 2, 294.5, { align: 'center' });
    }
  }

  function newPage() { doc.addPage(); addHeader(); y = 35; }
  function chk(h)    { if (y + h > BOT) newPage(); }

  function secTitle(num, label) {
    chk(14);
    doc.setFillColor(10, 22, 40);
    doc.rect(M, y, PW - M * 2, 8, 'F');
    doc.setFont('helvetica', 'bold'); doc.setFontSize(8.5); doc.setTextColor(244, 121, 32);
    doc.text(num + '.', M + 3, y + 5.5);
    doc.setTextColor(255, 255, 255);
    doc.text(label, M + 11, y + 5.5);
    y += 11;
  }

  function row2(l1, v1, l2, v2) {
    chk(8);
    const half = (PW - M * 2) / 2;
    [[l1, v1, M], [l2, v2, M + half]].forEach(([label, val, x]) => {
      if (!label) return;
      doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(100, 100, 100);
      doc.text(label + ':', x, y);
      doc.setFont('helvetica', 'normal'); doc.setTextColor(20, 20, 20);
      doc.text(String(val || '—').substring(0, 42), x, y + 4.5);
    });
    y += 10;
  }

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
  addHeader(); y = 35;

  doc.setFont('helvetica', 'bold'); doc.setFontSize(14); doc.setTextColor(10, 22, 40);
  doc.text('REPORTE DE SERVICIO TÉCNICO', PW / 2, y, { align: 'center' }); y += 7;
  doc.setFont('helvetica', 'bold'); doc.setFontSize(10); doc.setTextColor(244, 121, 32);
  doc.text('Folio: ' + (report.folio || '—'), PW / 2, y, { align: 'center' }); y += 8;

  /* 1. Datos Generales */
  secTitle('1', 'DATOS GENERALES');
  const fecha = (report.fecha_servicio || '').toString().substring(0, 10);
  row2('Folio', report.folio, 'Fecha de Servicio', fecha);
  row2('Tipo de Servicio', report.tipo_servicio, 'Técnico Responsable', report.tecnico_nombre);

  /* 2. Cliente */
  secTitle('2', 'DATOS DEL CLIENTE');
  row2('Nombre', report.cliente_nombre, 'Empresa', report.cliente_empresa);
  row2('RFC', report.cliente_rfc, 'Teléfono', report.cliente_telefono);
  row2('Email', report.cliente_email, null, null);
  textBlock('Dirección', report.cliente_direccion);

  /* 3. Equipo */
  secTitle('3', 'DATOS DEL EQUIPO');
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
    const rH   = 6.5, hH = 8;
    let drawHead = true;

    function medHead() {
      chk(hH + rH);
      let cx = M;
      cols.forEach((c, i) => {
        doc.setFillColor(30, 51, 85); doc.rect(cx, y, cw[i], hH, 'F');
        doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(255, 255, 255);
        doc.text(c, cx + 2, y + 5.5); cx += cw[i];
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
        doc.text(String(cell || '—'), cx + 2, y + 4.5); cx += cw[i];
      });
      y += rH;
    });
    y += 5;
  }

  /* 5. Fotografías */
  const fotosA = (report.fotos && Array.isArray(report.fotos.antes))   ? report.fotos.antes   : [];
  const fotosD = (report.fotos && Array.isArray(report.fotos.despues)) ? report.fotos.despues : [];

  secTitle('5', 'FOTOGRAFÍAS — ANTES Y DESPUÉS DEL SERVICIO');

  if (fotosA.length === 0 && fotosD.length === 0) {
    chk(8);
    doc.setFont('helvetica', 'italic'); doc.setFontSize(8); doc.setTextColor(150, 150, 150);
    doc.text('Sin fotografías adjuntas.', M, y); y += 8;
  } else {
    const perRow = 3;
    const gap    = 3;
    const iW     = (PW - M * 2 - gap * (perRow - 1)) / perRow;
    const iH     = Math.round(iW * 0.72);

    function photoGrid(groupLabel, photos) {
      if (photos.length === 0) return;
      chk(10);
      doc.setFont('helvetica', 'bold'); doc.setFontSize(8.5); doc.setTextColor(50, 50, 50);
      doc.text(groupLabel, M, y); y += 5;

      for (let i = 0; i < photos.length; i++) {
        const col = i % perRow;
        if (col === 0 && i > 0) y += iH + gap;
        if (col === 0) chk(iH + gap + 4);
        const cx = M + col * (iW + gap);
        if (photos[i].data) {
          try {
            doc.addImage(photos[i].data, imgFormat(photos[i].data), cx, y, iW, iH);
            doc.setDrawColor(200, 200, 200); doc.setLineWidth(0.3);
            doc.rect(cx, y, iW, iH);
          } catch(e) {
            doc.setFillColor(240, 240, 240); doc.rect(cx, y, iW, iH, 'F');
          }
        } else {
          doc.setFillColor(240, 240, 240); doc.rect(cx, y, iW, iH, 'F');
          doc.setFont('helvetica', 'italic'); doc.setFontSize(7); doc.setTextColor(160, 160, 160);
          doc.text('Imagen no disponible', cx + 2, y + iH / 2);
        }
      }
      y += iH + gap + 6;
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
  if (report.firma_tecnico) {
    try { doc.addImage(report.firma_tecnico, 'PNG', M, y, 82, 30); } catch(e) {}
  }
  doc.setDrawColor(80, 80, 80); doc.setLineWidth(0.5);
  doc.line(M,   y + 30, M + 82, y + 30);
  doc.line(112, y + 30, PW - M, y + 30);
  doc.setFont('helvetica', 'normal'); doc.setFontSize(7.5); doc.setTextColor(80, 80, 80);
  doc.text('Firma y Sello del Técnico Responsable', M,   y + 35);
  doc.text('Firma y Sello del Cliente',             112, y + 35);
  y += 44;

  /* 8. Garantía */
  chk(28);
  secTitle('8', 'CLÁUSULA DE GARANTÍA (30 DÍAS)');
  const gar = 'MAC DEL NORTE garantiza los servicios prestados en el presente reporte por un período de 30 (treinta) días naturales contados a partir de la fecha de servicio indicada. Esta garantía cubre exclusivamente los trabajos de mano de obra y los materiales suministrados por MAC DEL NORTE. No cubre daños causados por mal uso, negligencia, accidentes, modificaciones no autorizadas, desgaste natural o causas ajenas al servicio prestado. Contacto: contacto@macdelnorte.com  ·  +81-3582-5559.';
  const garL = doc.splitTextToSize(gar, PW - M * 2);
  chk(garL.length * 4);
  doc.setFont('helvetica', 'normal'); doc.setFontSize(7.5); doc.setTextColor(60, 60, 60);
  doc.text(garL, M, y);

  addFooters();
  doc.save('Reporte_' + (report.folio || 'MAC') + '.pdf');
}
</script>
@endpush

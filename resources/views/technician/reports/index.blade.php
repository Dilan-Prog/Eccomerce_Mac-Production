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

async function verPDF(id) {
  if (typeof window.jspdf === 'undefined') {
    toastr.error('La libreria PDF no esta disponible. Recarga la pagina.');
    return;
  }
  $('#pdfModal').modal('show');
  let report;
  try {
    const r = await fetch(`${BASE}/${id}`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    report = await r.json();
  } catch(e) {
    console.error('[Fetch Error]', e);
    toastr.error('Error al obtener el reporte: ' + e.message);
    $('#pdfModal').modal('hide');
    return;
  }
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
function generatePDF(report) {
  const { jsPDF } = window.jspdf;
  const doc    = new jsPDF({ unit: 'mm', format: 'a4' });
  const pageW  = 210;
  const margin = 14;
  const contentBottom = 283;
  let y = 0, pageNum = 1;

  function addHeader() {
    doc.setFillColor(10, 22, 40);
    doc.rect(0, 0, pageW, 27, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(16);
    doc.setTextColor(255, 255, 255);
    doc.text('MAC DEL NORTE', margin, 12);
    doc.setFontSize(7);
    doc.setFont('helvetica', 'normal');
    doc.text('MONITOREO, AUTOMATIZACIÓN Y CONTROLES DEL NORTE, S.A.P.I. de C.V.  ·  RFC: NMA180313M46', margin, 19);
    doc.text('C. Castaño 718, Ebanos Norte 2do Sector, Apodaca N.L. CP.66612  ·  +81-3582-5559  ·  contacto@macdelnorte.com', margin, 24);
    doc.setFillColor(244, 121, 32);
    doc.rect(0, 27, pageW, 2.5, 'F');
  }

  function newPage() {
    doc.addPage(); pageNum++; addHeader(); y = 34;
  }

  function check(h) { if (y + h > contentBottom) newPage(); }

  function secTitle(title) {
    check(12);
    doc.setFillColor(10, 22, 40);
    doc.rect(margin, y, pageW - margin * 2, 7, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(255, 255, 255);
    doc.text(title, margin + 3, y + 5);
    y += 9;
  }

  function row2(l1, v1, l2, v2) {
    check(6);
    doc.setFont('helvetica', 'bold'); doc.setFontSize(8); doc.setTextColor(80, 80, 80);
    doc.text(l1 + ':', margin, y);
    doc.setFont('helvetica', 'normal'); doc.setTextColor(0);
    doc.text(String(v1 || '-'), margin + 32, y);
    if (l2) {
      doc.setFont('helvetica', 'bold'); doc.setTextColor(80, 80, 80);
      doc.text(l2 + ':', 112, y);
      doc.setFont('helvetica', 'normal'); doc.setTextColor(0);
      doc.text(String(v2 || '-'), 145, y);
    }
    y += 6;
  }

  function row1(label, val) {
    check(6);
    doc.setFont('helvetica', 'bold'); doc.setFontSize(8); doc.setTextColor(80, 80, 80);
    doc.text(label + ':', margin, y);
    doc.setFont('helvetica', 'normal'); doc.setTextColor(0);
    const lines = doc.splitTextToSize(String(val || '-'), pageW - margin - 50);
    doc.text(lines, margin + 32, y);
    y += lines.length * 4.5 + 1.5;
  }

  /* Start */
  addHeader();
  y = 34;

  doc.setFont('helvetica', 'bold'); doc.setFontSize(13); doc.setTextColor(10, 22, 40);
  doc.text('REPORTE DE SERVICIO TÉCNICO', 105, y, { align: 'center' });
  y += 9;

  /* Sections 1-3 */
  secTitle('1. DATOS GENERALES');
  const fecha = (report.fecha_servicio || '').toString().substring(0, 10);
  row2('Folio', report.folio, 'Fecha de Servicio', fecha);
  row2('Tipo de Servicio', report.tipo_servicio, 'Técnico', report.tecnico_nombre);
  y += 2;

  secTitle('2. DATOS DEL CLIENTE');
  row2('Nombre', report.cliente_nombre, 'Empresa', report.cliente_empresa);
  row2('RFC', report.cliente_rfc, 'Teléfono', report.cliente_telefono);
  row1('Dirección', report.cliente_direccion);
  row1('Email', report.cliente_email);
  y += 2;

  secTitle('3. DATOS DEL EQUIPO');
  row1('Descripción', report.equipo_descripcion);
  row2('Marca', report.equipo_marca, 'Modelo', report.equipo_modelo);
  row2('No. de Serie', report.equipo_serie, 'Ubicación/TAG', report.equipo_ubicacion_tag);
  y += 2;

  /* Section 4: Mediciones */
  secTitle('4. TABLA DE MEDICIONES');
  const mediciones = Array.isArray(report.mediciones) ? report.mediciones : [];
  const colH = ['Punto', 'Val. Referencia', 'Val. Medido', 'Error', 'Tolerancia', 'Resultado'];
  const cw   = [33, 27, 27, 22, 22, 25];
  const rH   = 6.5, hH = 7;

  let needHeader = true;
  function drawMedHeader() {
    check(hH + rH);
    let cx = margin;
    colH.forEach((c, i) => {
      doc.setFillColor(30, 51, 85); doc.rect(cx, y, cw[i], hH, 'F');
      doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(255, 255, 255);
      doc.text(c, cx + 2, y + 5); cx += cw[i];
    });
    y += hH; needHeader = false;
  }
  drawMedHeader();

  mediciones.forEach((m, idx) => {
    if (y + rH > contentBottom) { newPage(); needHeader = true; }
    if (needHeader) drawMedHeader();
    const cells = [m.punto, m.valor_referencia, m.valor_medido, m.error, m.tolerancia, m.resultado];
    let cx = margin;
    cells.forEach((cell, i) => {
      let bg = idx % 2 === 0 ? [250, 250, 250] : [240, 242, 245];
      let tc = [20, 20, 20]; let fw = 'normal';
      if (i === 5) {
        if (cell === 'OK')    { bg = [16, 185, 129]; tc = [255, 255, 255]; fw = 'bold'; }
        if (cell === 'NO OK') { bg = [239, 68, 68];  tc = [255, 255, 255]; fw = 'bold'; }
        if (cell === 'N/A')   { bg = [180, 180, 180]; tc = [60, 60, 60]; }
      }
      doc.setFillColor(...bg); doc.setDrawColor(210, 210, 210);
      doc.rect(cx, y, cw[i], rH, 'FD');
      doc.setFont('helvetica', fw); doc.setFontSize(7.5); doc.setTextColor(...tc);
      doc.text(String(cell || '-'), cx + 2, y + 4.5); cx += cw[i];
    });
    y += rH;
  });
  y += 4;

  /* Section 5 */
  secTitle('5. OBSERVACIONES Y RECOMENDACIONES');
  doc.setFont('helvetica', 'bold'); doc.setFontSize(8); doc.setTextColor(60, 60, 60);
  doc.text('Observaciones:', margin, y); y += 5;
  doc.setFont('helvetica', 'normal'); doc.setTextColor(0);
  const obsL = doc.splitTextToSize(report.observaciones || 'Sin observaciones.', pageW - margin * 2);
  check(obsL.length * 4.5); doc.text(obsL, margin, y); y += obsL.length * 4.5 + 4;
  doc.setFont('helvetica', 'bold'); doc.setFontSize(8); doc.setTextColor(60, 60, 60);
  doc.text('Recomendaciones:', margin, y); y += 5;
  doc.setFont('helvetica', 'normal'); doc.setTextColor(0);
  const recL = doc.splitTextToSize(report.recomendaciones || 'Sin recomendaciones.', pageW - margin * 2);
  check(recL.length * 4.5); doc.text(recL, margin, y); y += recL.length * 4.5 + 6;

  /* Section 6: Firmas */
  check(55);
  secTitle('6. FIRMAS');
  if (report.firma_tecnico) {
    try { doc.addImage(report.firma_tecnico, 'PNG', margin, y, 82, 32); } catch(e) {}
  }
  doc.setDrawColor(0);
  doc.line(margin, y + 32, margin + 82, y + 32);
  doc.line(112, y + 32, 196, y + 32);
  doc.setFont('helvetica', 'normal'); doc.setFontSize(8); doc.setTextColor(60, 60, 60);
  doc.text('Firma y Sello del Técnico Responsable', margin, y + 37);
  doc.text('Firma y Sello del Cliente', 112, y + 37);
  y += 45;

  /* Section 7: Garantía */
  check(30);
  secTitle('7. CLÁUSULA DE GARANTÍA (30 DÍAS)');
  doc.setFont('helvetica', 'normal'); doc.setFontSize(7.5); doc.setTextColor(60, 60, 60);
  const gar = 'MAC DEL NORTE garantiza los servicios prestados en el presente reporte por un período de 30 (treinta) días naturales contados a partir de la fecha de servicio indicada. Esta garantía cubre exclusivamente los trabajos de mano de obra y los materiales suministrados por MAC DEL NORTE. No cubre daños causados por mal uso, negligencia, accidentes, modificaciones no autorizadas, desgaste natural o causas ajenas al servicio prestado. Para hacer válida esta garantía, comuníquese a contacto@macdelnorte.com o llame al +81-3582-5559.';
  const garL = doc.splitTextToSize(gar, pageW - margin * 2);
  check(garL.length * 4); doc.text(garL, margin, y); y += garL.length * 4 + 4;

  /* Footers en todas las páginas */
  const total = doc.getNumberOfPages();
  for (let p = 1; p <= total; p++) {
    doc.setPage(p);
    doc.setFillColor(10, 22, 40);
    doc.rect(0, 286, pageW, 11, 'F');
    doc.setFont('helvetica', 'normal'); doc.setFontSize(7); doc.setTextColor(255, 255, 255);
    doc.text('MAC DEL NORTE  |  contacto@macdelnorte.com  |  www.macdelnorte.com', 105, 291, { align: 'center' });
    doc.text('Pagina ' + p + ' de ' + total, 105, 295, { align: 'center' });
  }

  doc.save('Reporte_' + (report.folio || 'MAC') + '.pdf');
}
</script>
@endpush

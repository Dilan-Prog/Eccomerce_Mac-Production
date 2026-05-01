{{-- resources/views/technician/reports/create.blade.php --}}
@extends('technician.layouts.master')

@push('styles')
<style>
  :root {
    --navy:#0A1628;--navy-light:#162240;--navy-mid:#1E3355;
    --orange:#F47920;--orange-hover:#E06810;
    --green:#10B981;--red:#EF4444;--amber:#F59E0B;
  }

  /* ── Wizard container ───────────────────────────────── */
  .wizard-wrap { max-width: 900px; margin: 0 auto; }

  /* ── Progress bar ───────────────────────────────────── */
  .progress-steps {
    display: flex; align-items: center; margin-bottom: 32px;
    background: #fff; border-radius: 12px; padding: 20px 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
  }
  .ps-item {
    flex: 1; display: flex; flex-direction: column; align-items: center;
    position: relative; gap: 6px;
  }
  .ps-item:not(:last-child)::after {
    content: ''; position: absolute; top: 18px; left: 60%; width: 80%; height: 2px;
    background: #e2e8f0; z-index: 0;
  }
  .ps-item.done:not(:last-child)::after  { background: var(--orange); }
  .ps-item.active:not(:last-child)::after { background: #e2e8f0; }

  .ps-dot {
    width: 36px; height: 36px; border-radius: 50%; border: 2px solid #e2e8f0;
    background: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 14px; color: #aaa; position: relative; z-index: 1;
    transition: all .25s;
  }
  .ps-item.active .ps-dot { border-color: var(--orange); color: var(--orange); background: #fff8f3; }
  .ps-item.done .ps-dot   { background: var(--orange); border-color: var(--orange); color: #fff; }
  .ps-label { font-size: 11px; color: #888; font-weight: 500; text-align: center; }
  .ps-item.active .ps-label { color: var(--orange); font-weight: 700; }
  .ps-item.done .ps-label   { color: var(--navy); }

  /* ── Step panel ─────────────────────────────────────── */
  .step-panel { display: none; animation: fadeIn .25s ease; }
  .step-panel.active { display: block; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(8px) } to { opacity:1; transform:none } }

  .step-card {
    background: #fff; border-radius: 12px; padding: 32px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 16px;
  }
  .step-title {
    font-size: 18px; font-weight: 700; color: var(--navy);
    margin-bottom: 24px; padding-bottom: 12px;
    border-bottom: 2px solid var(--orange); display: flex; align-items: center; gap: 10px;
  }
  .step-title i { color: var(--orange); }

  .form-label { font-weight: 600; color: #374151; font-size: 13px; }
  .form-control, .form-select {
    border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 14px;
    font-size: 14px; transition: border-color .2s;
  }
  .form-control:focus, .form-select:focus {
    border-color: var(--orange); box-shadow: 0 0 0 3px rgba(244,121,32,.15); outline: none;
  }

  /* ── Wizard navigation ──────────────────────────────── */
  .wizard-nav {
    display: flex; justify-content: space-between; align-items: center;
    background: #fff; border-radius: 12px; padding: 16px 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
  }
  .btn-nav {
    padding: 10px 28px; border-radius: 8px; font-weight: 600;
    font-size: 14px; border: none; cursor: pointer; transition: all .2s;
  }
  .btn-prev { background: #f3f4f6; color: #374151; }
  .btn-prev:hover { background: #e5e7eb; }
  .btn-next { background: var(--orange); color: #fff; }
  .btn-next:hover { background: var(--orange-hover); }
  .btn-next:disabled { background: #fdba74; cursor: not-allowed; }
  .btn-finish { background: var(--navy); color: #fff; }
  .btn-finish:hover { background: var(--navy-mid); }

  /* ── Mediciones table ───────────────────────────────── */
  #medicionesTable { width: 100%; border-collapse: collapse; font-size: 13px; }
  #medicionesTable thead { background: var(--navy); color: #fff; }
  #medicionesTable thead th { padding: 10px 8px; text-align: left; font-weight: 600; }
  #medicionesTable tbody tr { border-bottom: 1px solid #f3f4f6; }
  #medicionesTable tbody tr:hover { background: #fafafa; }
  #medicionesTable td { padding: 6px 4px; vertical-align: middle; }
  #medicionesTable input, #medicionesTable select {
    width: 100%; border: 1px solid #e5e7eb; border-radius: 6px; padding: 6px 8px; font-size: 12px;
  }
  #medicionesTable input[readonly] { background: #f9fafb; color: #6b7280; }
  .sel-ok    { background: #d1fae5 !important; color: #065f46 !important; font-weight:700; }
  .sel-nook  { background: #fee2e2 !important; color: #991b1b !important; font-weight:700; }
  .sel-na    { background: #f3f4f6 !important; color: #6b7280 !important; }
  .btn-add-row {
    background: var(--navy); color: #fff; border: none; border-radius: 8px;
    padding: 8px 18px; font-size: 13px; cursor: pointer; transition: background .2s;
  }
  .btn-add-row:hover { background: var(--navy-mid); }
  .btn-del-row {
    background: var(--red); color: #fff; border: none; border-radius: 6px;
    padding: 4px 10px; font-size: 12px; cursor: pointer;
  }

  /* ── Summary (Step 6) ───────────────────────────────── */
  .summary-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 8px 20px;
    font-size: 13px; margin-bottom: 20px;
  }
  .summary-grid dt { font-weight: 700; color: #6b7280; }
  .summary-grid dd { color: var(--navy); margin: 0; }
  .summary-section { background: #f8fafc; border-radius: 8px; padding: 14px 18px; margin-bottom: 12px; }
  .summary-section-title { font-weight: 700; color: var(--navy); font-size: 13px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .5px; }

  /* ── Signature canvas ───────────────────────────────── */
  #signatureCanvas {
    border: 2px dashed #d1d5db; border-radius: 10px;
    background: #f9fafb; cursor: crosshair; display: block;
    width: 100%; height: 180px; touch-action: none;
  }
  .canvas-controls { display: flex; gap: 10px; margin-top: 8px; }
  .btn-clear-sig {
    background: #f3f4f6; border: none; border-radius: 8px;
    padding: 8px 18px; font-size: 13px; cursor: pointer; color: #374151;
  }
  .btn-clear-sig:hover { background: #e5e7eb; }

  /* ── Alerts ─────────────────────────────────────────── */
  .wiz-alert {
    display: none; border-radius: 8px; padding: 10px 16px;
    font-size: 13px; margin-bottom: 16px;
  }
  .wiz-alert.error { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--red); }
  .wiz-alert.info  { background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6; }
  .wiz-alert.show  { display: flex; align-items: center; gap: 10px; }

  /* ── Spinner overlay ────────────────────────────────── */
  .saving-overlay {
    display: none; position: fixed; inset: 0; background: rgba(10,22,40,.45);
    z-index: 9999; align-items: center; justify-content: center; flex-direction: column; gap: 16px;
  }
  .saving-overlay.show { display: flex; }
  .saving-overlay p { color: #fff; font-weight: 600; font-size: 16px; }
</style>
@endpush

@section('content')
<section class="section">
  <div class="section-header">
    <h1>Nuevo Reporte de Servicio</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="{{ route('technician.reports.index') }}">Reportes</a></div>
      <div class="breadcrumb-item active">Nuevo</div>
    </div>
  </div>

  <div class="wizard-wrap">

    {{-- Alerta global --}}
    <div class="wiz-alert error" id="wizAlert">
      <i class="fas fa-exclamation-circle"></i>
      <span id="wizAlertMsg"></span>
    </div>

    {{-- ── PROGRESS BAR ── --}}
    <div class="progress-steps" id="progressSteps">
      <div class="ps-item active" data-step="1">
        <div class="ps-dot">1</div>
        <div class="ps-label">Datos Generales</div>
      </div>
      <div class="ps-item" data-step="2">
        <div class="ps-dot">2</div>
        <div class="ps-label">Cliente</div>
      </div>
      <div class="ps-item" data-step="3">
        <div class="ps-dot">3</div>
        <div class="ps-label">Equipo</div>
      </div>
      <div class="ps-item" data-step="4">
        <div class="ps-dot">4</div>
        <div class="ps-label">Mediciones</div>
      </div>
      <div class="ps-item" data-step="5">
        <div class="ps-dot">5</div>
        <div class="ps-label">Observaciones</div>
      </div>
      <div class="ps-item" data-step="6">
        <div class="ps-dot">6</div>
        <div class="ps-label">Firma y PDF</div>
      </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PASO 1 — DATOS GENERALES
    ══════════════════════════════════════════════════ --}}
    <div class="step-panel active" id="panel-1">
      <div class="step-card">
        <div class="step-title"><i class="fas fa-info-circle"></i> Paso 1 — Datos Generales</div>
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Folio <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" id="folio" class="form-control" placeholder="MAC-YYYYMMDD-XXXX" required>
              <button type="button" class="btn btn-secondary" onclick="autoFolio()" title="Auto-generar folio">
                <i class="fas fa-magic"></i> Auto-generar
              </button>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Fecha de Servicio <span class="text-danger">*</span></label>
            <input type="date" id="fecha_servicio" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tipo de Servicio <span class="text-danger">*</span></label>
            <select id="tipo_servicio" class="form-select" required>
              <option value="">— Seleccionar —</option>
              <option>Calibración</option>
              <option>Reparación</option>
              <option>Mantenimiento Preventivo</option>
              <option>Mantenimiento Correctivo</option>
              <option>Instalación</option>
              <option>Diagnóstico</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Técnico Responsable <span class="text-danger">*</span></label>
            <input type="text" id="tecnico_nombre" class="form-control"
                   value="{{ auth()->user()->name }}" placeholder="Nombre del técnico" required>
          </div>
        </div>
      </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PASO 2 — DATOS DEL CLIENTE
    ══════════════════════════════════════════════════ --}}
    <div class="step-panel" id="panel-2">
      <div class="step-card">
        <div class="step-title"><i class="fas fa-building"></i> Paso 2 — Datos del Cliente</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre del Contacto</label>
            <input type="text" id="cliente_nombre" class="form-control" placeholder="Nombre completo">
          </div>
          <div class="col-md-6">
            <label class="form-label">Empresa</label>
            <input type="text" id="cliente_empresa" class="form-control" placeholder="Razón social">
          </div>
          <div class="col-md-4">
            <label class="form-label">RFC</label>
            <input type="text" id="cliente_rfc" class="form-control" placeholder="RFC de la empresa">
          </div>
          <div class="col-md-4">
            <label class="form-label">Teléfono</label>
            <input type="text" id="cliente_telefono" class="form-control" placeholder="81-0000-0000">
          </div>
          <div class="col-md-4">
            <label class="form-label">Correo Electrónico</label>
            <input type="email" id="cliente_email" class="form-control" placeholder="correo@empresa.com">
          </div>
          <div class="col-12">
            <label class="form-label">Dirección</label>
            <textarea id="cliente_direccion" class="form-control" rows="2" placeholder="Calle, Número, Colonia, Ciudad, Estado, CP"></textarea>
          </div>
        </div>
      </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PASO 3 — DATOS DEL EQUIPO
    ══════════════════════════════════════════════════ --}}
    <div class="step-panel" id="panel-3">
      <div class="step-card">
        <div class="step-title"><i class="fas fa-microchip"></i> Paso 3 — Datos del Equipo</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Descripción del Equipo</label>
            <input type="text" id="equipo_descripcion" class="form-control" placeholder="Ej. Controlador de temperatura, transmisor de presión…">
          </div>
          <div class="col-md-4">
            <label class="form-label">Marca</label>
            <input type="text" id="equipo_marca" class="form-control" placeholder="Honeywell, Siemens…">
          </div>
          <div class="col-md-4">
            <label class="form-label">Modelo</label>
            <input type="text" id="equipo_modelo" class="form-control" placeholder="UDC2800, S7-1200…">
          </div>
          <div class="col-md-4">
            <label class="form-label">No. de Serie</label>
            <input type="text" id="equipo_serie" class="form-control" placeholder="SN-XXXXXXXX">
          </div>
          <div class="col-md-6">
            <label class="form-label">Ubicación / TAG</label>
            <input type="text" id="equipo_ubicacion_tag" class="form-control" placeholder="Área de producción / TAG-001">
          </div>
        </div>
      </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PASO 4 — MEDICIONES
    ══════════════════════════════════════════════════ --}}
    <div class="step-panel" id="panel-4">
      <div class="step-card">
        <div class="step-title"><i class="fas fa-ruler-combined"></i> Paso 4 — Tabla de Mediciones</div>
        <p class="text-muted mb-3" style="font-size:13px">
          Agrega las mediciones del servicio. El campo <strong>Error</strong> se calcula automáticamente.
        </p>
        <div class="table-responsive mb-3">
          <table id="medicionesTable">
            <thead>
              <tr>
                <th style="min-width:120px">Punto</th>
                <th style="min-width:110px">Val. Referencia</th>
                <th style="min-width:110px">Val. Medido</th>
                <th style="min-width:90px">Error</th>
                <th style="min-width:100px">Tolerancia</th>
                <th style="min-width:110px">Resultado</th>
                <th style="width:50px"></th>
              </tr>
            </thead>
            <tbody id="medTbody">
              {{-- rows added by JS --}}
            </tbody>
          </table>
        </div>
        <button type="button" class="btn-add-row" onclick="addMedRow()">
          <i class="fas fa-plus"></i> Agregar fila
        </button>
      </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PASO 5 — OBSERVACIONES
    ══════════════════════════════════════════════════ --}}
    <div class="step-panel" id="panel-5">
      <div class="step-card">
        <div class="step-title"><i class="fas fa-comment-alt"></i> Paso 5 — Observaciones</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Observaciones</label>
            <textarea id="observaciones" class="form-control" rows="5"
                      placeholder="Describe el estado del equipo, anomalías encontradas, trabajos realizados…"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Recomendaciones</label>
            <textarea id="recomendaciones" class="form-control" rows="4"
                      placeholder="Acciones preventivas, próximas revisiones, repuestos sugeridos…"></textarea>
          </div>
        </div>
      </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PASO 6 — FIRMA Y PDF
    ══════════════════════════════════════════════════ --}}
    <div class="step-panel" id="panel-6">
      {{-- Summary --}}
      <div class="step-card">
        <div class="step-title"><i class="fas fa-list-check"></i> Resumen del Reporte</div>
        <div id="summaryContent">
          {{-- Populated by JS --}}
        </div>
      </div>

      {{-- Signature --}}
      <div class="step-card">
        <div class="step-title"><i class="fas fa-signature"></i> Firma del Técnico <span class="text-danger">*</span></div>
        <p class="text-muted mb-2" style="font-size:13px">Firma en el área de abajo usando el mouse o pantalla táctil.</p>
        <canvas id="signatureCanvas" width="850" height="180"></canvas>
        <div class="canvas-controls">
          <button type="button" class="btn-clear-sig" onclick="clearCanvas()">
            <i class="fas fa-eraser"></i> Limpiar firma
          </button>
          <small class="text-muted my-auto">La firma queda registrada en el PDF.</small>
        </div>
      </div>
    </div>

    {{-- ── NAVIGATION ── --}}
    <div class="wizard-nav">
      <button type="button" class="btn-nav btn-prev" id="btnPrev" onclick="prevStep()" style="display:none">
        <i class="fas fa-arrow-left"></i> Anterior
      </button>
      <div style="flex:1"></div>
      <button type="button" class="btn-nav btn-next" id="btnNext" onclick="nextStep()">
        Siguiente <i class="fas fa-arrow-right"></i>
      </button>
      <button type="button" class="btn-nav btn-finish" id="btnFinish" onclick="finishReport()" style="display:none">
        <i class="fas fa-file-pdf"></i> Generar PDF y Guardar
      </button>
    </div>

  </div>{{-- .wizard-wrap --}}
</section>

{{-- Saving overlay --}}
<div class="saving-overlay" id="savingOverlay">
  <div class="spinner-border text-warning" style="width:48px;height:48px" role="status"></div>
  <p id="savingMsg">Guardando…</p>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
/* ══════════════════════════════════════════════════════════════
   WIZARD STATE
══════════════════════════════════════════════════════════════ */
const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
const BASE    = '{{ url("/technician/reports") }}';
const FOLIO_URL = '{{ route("technician.reports.folio") }}';

let currentStep = 1;
let reportId    = null;

/* Collected data per step (for summary & PDF) */
let s1 = {}, s2 = {}, s3 = {}, s4 = [], s5 = {};

/* ══════════════════════════════════════════════════════════════
   UI HELPERS
══════════════════════════════════════════════════════════════ */
function showAlert(msg) {
  const el = document.getElementById('wizAlert');
  document.getElementById('wizAlertMsg').textContent = msg;
  el.classList.add('show');
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  setTimeout(() => el.classList.remove('show'), 6000);
}

function showOverlay(msg) {
  document.getElementById('savingMsg').textContent = msg || 'Guardando…';
  document.getElementById('savingOverlay').classList.add('show');
}

function hideOverlay() {
  document.getElementById('savingOverlay').classList.remove('show');
}

/* ══════════════════════════════════════════════════════════════
   STEP NAVIGATION
══════════════════════════════════════════════════════════════ */
function showStep(n) {
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
  document.getElementById(`panel-${n}`).classList.add('active');

  document.querySelectorAll('.ps-item').forEach(item => {
    const s = parseInt(item.dataset.step);
    item.classList.remove('active', 'done');
    if (s < n)      item.classList.add('done');
    else if (s === n) item.classList.add('active');
  });

  document.getElementById('btnPrev').style.display   = n > 1 ? '' : 'none';
  document.getElementById('btnNext').style.display   = n < 6 ? '' : 'none';
  document.getElementById('btnFinish').style.display = n === 6 ? '' : 'none';

  currentStep = n;

  if (n === 6) buildSummary();
  if (n === 4 && document.getElementById('medTbody').rows.length === 0) addMedRow();
}

async function nextStep() {
  const ok = await saveCurrentStep();
  if (!ok) return;
  showStep(currentStep + 1);
}

function prevStep() {
  showStep(currentStep - 1);
}

/* ══════════════════════════════════════════════════════════════
   SAVE LOGIC PER STEP
══════════════════════════════════════════════════════════════ */
async function saveCurrentStep() {
  switch (currentStep) {
    case 1: return await saveStep1();
    case 2: return await saveStep2();
    case 3: return await saveStep3();
    case 4: return await saveStep4();
    case 5: return await saveStep5();
    default: return true;
  }
}

/* ── Step 1 ────────────────────────────────────────────────── */
async function saveStep1() {
  const folio   = document.getElementById('folio').value.trim();
  const fecha   = document.getElementById('fecha_servicio').value;
  const tipo    = document.getElementById('tipo_servicio').value;
  const tecnico = document.getElementById('tecnico_nombre').value.trim();

  if (!folio)   { showAlert('El folio es obligatorio.'); return false; }
  if (!fecha)   { showAlert('La fecha de servicio es obligatoria.'); return false; }
  if (!tipo)    { showAlert('Selecciona el tipo de servicio.'); return false; }
  if (!tecnico) { showAlert('El nombre del técnico es obligatorio.'); return false; }

  s1 = { folio, fecha_servicio: fecha, tipo_servicio: tipo, tecnico_nombre: tecnico };
  showOverlay('Guardando datos generales…');

  try {
    if (!reportId) {
      /* Primera vez → POST */
      const res = await fetch(BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(s1),
      });
      if (!res.ok) {
        const err = await res.json();
        showAlert(err.message || (err.errors ? Object.values(err.errors).flat().join(' ') : 'Error al crear el reporte.'));
        return false;
      }
      const data = await res.json();
      reportId = data.id;
      /* Bloquear folio después de creado */
      document.getElementById('folio').setAttribute('readonly', true);
      document.getElementById('folio').style.background = '#f9fafb';
    } else {
      /* Regresó al paso 1 → PUT sin folio */
      const updateData = { fecha_servicio: fecha, tipo_servicio: tipo, tecnico_nombre: tecnico };
      await putReport(updateData);
    }
    return true;
  } catch(e) {
    showAlert('Error de conexión al guardar el paso 1.');
    return false;
  } finally {
    hideOverlay();
  }
}

/* ── Step 2 ────────────────────────────────────────────────── */
async function saveStep2() {
  s2 = {
    cliente_nombre:    document.getElementById('cliente_nombre').value.trim(),
    cliente_empresa:   document.getElementById('cliente_empresa').value.trim(),
    cliente_rfc:       document.getElementById('cliente_rfc').value.trim(),
    cliente_telefono:  document.getElementById('cliente_telefono').value.trim(),
    cliente_email:     document.getElementById('cliente_email').value.trim(),
    cliente_direccion: document.getElementById('cliente_direccion').value.trim(),
  };
  showOverlay('Guardando datos del cliente…');
  try { await putReport(s2); return true; }
  catch(e) { showAlert('Error al guardar el paso 2.'); return false; }
  finally   { hideOverlay(); }
}

/* ── Step 3 ────────────────────────────────────────────────── */
async function saveStep3() {
  s3 = {
    equipo_descripcion:  document.getElementById('equipo_descripcion').value.trim(),
    equipo_marca:        document.getElementById('equipo_marca').value.trim(),
    equipo_modelo:       document.getElementById('equipo_modelo').value.trim(),
    equipo_serie:        document.getElementById('equipo_serie').value.trim(),
    equipo_ubicacion_tag:document.getElementById('equipo_ubicacion_tag').value.trim(),
  };
  showOverlay('Guardando datos del equipo…');
  try { await putReport(s3); return true; }
  catch(e) { showAlert('Error al guardar el paso 3.'); return false; }
  finally   { hideOverlay(); }
}

/* ── Step 4 ────────────────────────────────────────────────── */
async function saveStep4() {
  s4 = getMediciones();
  showOverlay('Guardando mediciones…');
  try { await putReport({ mediciones: s4 }); return true; }
  catch(e) { showAlert('Error al guardar las mediciones.'); return false; }
  finally   { hideOverlay(); }
}

/* ── Step 5 ────────────────────────────────────────────────── */
async function saveStep5() {
  s5 = {
    observaciones:   document.getElementById('observaciones').value.trim(),
    recomendaciones: document.getElementById('recomendaciones').value.trim(),
  };
  showOverlay('Guardando observaciones…');
  try { await putReport(s5); return true; }
  catch(e) { showAlert('Error al guardar el paso 5.'); return false; }
  finally   { hideOverlay(); }
}

/* ── PUT helper ──────────────────────────────────────────────*/
async function putReport(data) {
  const res = await fetch(`${BASE}/${reportId}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    body: JSON.stringify(data),
  });
  if (!res.ok) throw new Error('put failed');
}

/* ══════════════════════════════════════════════════════════════
   FOLIO AUTO-GENERATE
══════════════════════════════════════════════════════════════ */
async function autoFolio() {
  try {
    const r = await fetch(FOLIO_URL, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
    const d = await r.json();
    document.getElementById('folio').value = d.folio;
  } catch(e) {
    showAlert('No se pudo generar el folio automáticamente.');
  }
}

/* ══════════════════════════════════════════════════════════════
   MEDICIONES TABLE
══════════════════════════════════════════════════════════════ */
function addMedRow() {
  const tbody = document.getElementById('medTbody');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input type="text" class="m-punto" placeholder="SP-01"></td>
    <td><input type="number" class="m-ref" step="any" placeholder="0.00" oninput="calcError(this)"></td>
    <td><input type="number" class="m-med" step="any" placeholder="0.00" oninput="calcError(this)"></td>
    <td><input type="number" class="m-err" step="any" readonly tabindex="-1"></td>
    <td><input type="text"   class="m-tol" placeholder="±1%"></td>
    <td>
      <select class="m-res" onchange="colorRes(this)">
        <option value="">—</option>
        <option value="OK">OK</option>
        <option value="NO OK">NO OK</option>
        <option value="N/A">N/A</option>
      </select>
    </td>
    <td><button type="button" class="btn-del-row" onclick="delMedRow(this)"><i class="fas fa-trash"></i></button></td>
  `;
  tbody.appendChild(tr);
}

function delMedRow(btn) {
  btn.closest('tr').remove();
}

function calcError(input) {
  const row = input.closest('tr');
  const ref = parseFloat(row.querySelector('.m-ref').value) || 0;
  const med = parseFloat(row.querySelector('.m-med').value) || 0;
  const err = row.querySelector('.m-err');
  err.value = (med - ref).toFixed(4).replace(/\.?0+$/, '');
}

function colorRes(sel) {
  sel.className = 'm-res';
  if (sel.value === 'OK')    sel.classList.add('sel-ok');
  if (sel.value === 'NO OK') sel.classList.add('sel-nook');
  if (sel.value === 'N/A')   sel.classList.add('sel-na');
}

function getMediciones() {
  return Array.from(document.getElementById('medTbody').rows).map(row => ({
    punto:            row.querySelector('.m-punto').value.trim(),
    valor_referencia: row.querySelector('.m-ref').value,
    valor_medido:     row.querySelector('.m-med').value,
    error:            row.querySelector('.m-err').value,
    tolerancia:       row.querySelector('.m-tol').value.trim(),
    resultado:        row.querySelector('.m-res').value,
  }));
}

/* ══════════════════════════════════════════════════════════════
   STEP 6 — SUMMARY
══════════════════════════════════════════════════════════════ */
function buildSummary() {
  const medRows = getMediciones(); // snapshot before user edits canvas
  s4 = medRows;

  const v = (x) => x || '<span class="text-muted">—</span>';

  const resTag = (r) => {
    if (r === 'OK')    return `<span style="background:var(--green);color:#fff;padding:1px 8px;border-radius:4px;font-size:11px;font-weight:700">OK</span>`;
    if (r === 'NO OK') return `<span style="background:var(--red);color:#fff;padding:1px 8px;border-radius:4px;font-size:11px;font-weight:700">NO OK</span>`;
    if (r === 'N/A')   return `<span style="background:#e5e7eb;color:#6b7280;padding:1px 8px;border-radius:4px;font-size:11px">N/A</span>`;
    return '—';
  };

  let medHTML = '';
  if (medRows.length) {
    medHTML = `<table style="width:100%;border-collapse:collapse;font-size:12px;margin-top:8px">
      <thead><tr style="background:var(--navy);color:#fff">
        <th style="padding:6px 8px">Punto</th><th style="padding:6px 8px">Val. Ref.</th>
        <th style="padding:6px 8px">Val. Med.</th><th style="padding:6px 8px">Error</th>
        <th style="padding:6px 8px">Tolerancia</th><th style="padding:6px 8px">Resultado</th>
      </tr></thead><tbody>`;
    medRows.forEach((m, i) => {
      medHTML += `<tr style="background:${i%2===0?'#f8fafc':'#fff'}">
        <td style="padding:5px 8px">${v(m.punto)}</td>
        <td style="padding:5px 8px">${v(m.valor_referencia)}</td>
        <td style="padding:5px 8px">${v(m.valor_medido)}</td>
        <td style="padding:5px 8px">${v(m.error)}</td>
        <td style="padding:5px 8px">${v(m.tolerancia)}</td>
        <td style="padding:5px 8px">${resTag(m.resultado)}</td>
      </tr>`;
    });
    medHTML += '</tbody></table>';
  } else {
    medHTML = '<p class="text-muted" style="font-size:13px">Sin mediciones registradas.</p>';
  }

  document.getElementById('summaryContent').innerHTML = `
    <div class="summary-section">
      <div class="summary-section-title">1. Datos Generales</div>
      <dl class="summary-grid">
        <dt>Folio</dt><dd>${v(s1.folio)}</dd>
        <dt>Fecha de Servicio</dt><dd>${v(s1.fecha_servicio)}</dd>
        <dt>Tipo de Servicio</dt><dd>${v(s1.tipo_servicio)}</dd>
        <dt>Técnico</dt><dd>${v(s1.tecnico_nombre)}</dd>
      </dl>
    </div>
    <div class="summary-section">
      <div class="summary-section-title">2. Cliente</div>
      <dl class="summary-grid">
        <dt>Nombre</dt><dd>${v(s2.cliente_nombre)}</dd>
        <dt>Empresa</dt><dd>${v(s2.cliente_empresa)}</dd>
        <dt>RFC</dt><dd>${v(s2.cliente_rfc)}</dd>
        <dt>Teléfono</dt><dd>${v(s2.cliente_telefono)}</dd>
        <dt>Email</dt><dd>${v(s2.cliente_email)}</dd>
        <dt>Dirección</dt><dd>${v(s2.cliente_direccion)}</dd>
      </dl>
    </div>
    <div class="summary-section">
      <div class="summary-section-title">3. Equipo</div>
      <dl class="summary-grid">
        <dt>Descripción</dt><dd>${v(s3.equipo_descripcion)}</dd>
        <dt>Marca</dt><dd>${v(s3.equipo_marca)}</dd>
        <dt>Modelo</dt><dd>${v(s3.equipo_modelo)}</dd>
        <dt>No. de Serie</dt><dd>${v(s3.equipo_serie)}</dd>
        <dt>Ubicación/TAG</dt><dd>${v(s3.equipo_ubicacion_tag)}</dd>
      </dl>
    </div>
    <div class="summary-section">
      <div class="summary-section-title">4. Mediciones</div>
      ${medHTML}
    </div>
    <div class="summary-section">
      <div class="summary-section-title">5. Observaciones y Recomendaciones</div>
      <p style="font-size:13px"><strong>Observaciones:</strong><br>${v(s5.observaciones)}</p>
      <p style="font-size:13px;margin:0"><strong>Recomendaciones:</strong><br>${v(s5.recomendaciones)}</p>
    </div>
  `;
}

/* ══════════════════════════════════════════════════════════════
   SIGNATURE CANVAS
══════════════════════════════════════════════════════════════ */
(function initCanvas() {
  const canvas = document.getElementById('signatureCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let drawing = false, lx = 0, ly = 0;

  function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const scaleX = canvas.width  / r.width;
    const scaleY = canvas.height / r.height;
    if (e.touches) {
      return { x: (e.touches[0].clientX - r.left) * scaleX, y: (e.touches[0].clientY - r.top) * scaleY };
    }
    return { x: (e.clientX - r.left) * scaleX, y: (e.clientY - r.top) * scaleY };
  }

  canvas.addEventListener('mousedown',  e => { drawing = true; const p = getPos(e); lx = p.x; ly = p.y; });
  canvas.addEventListener('mousemove',  e => {
    if (!drawing) return;
    const p = getPos(e);
    ctx.beginPath(); ctx.moveTo(lx, ly); ctx.lineTo(p.x, p.y);
    ctx.strokeStyle = '#0A1628'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke();
    lx = p.x; ly = p.y;
  });
  canvas.addEventListener('mouseup',    () => drawing = false);
  canvas.addEventListener('mouseleave', () => drawing = false);
  canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; const p = getPos(e); lx = p.x; ly = p.y; }, { passive: false });
  canvas.addEventListener('touchmove',  e => {
    if (!drawing) return; e.preventDefault();
    const p = getPos(e);
    ctx.beginPath(); ctx.moveTo(lx, ly); ctx.lineTo(p.x, p.y);
    ctx.strokeStyle = '#0A1628'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke();
    lx = p.x; ly = p.y;
  }, { passive: false });
  canvas.addEventListener('touchend', () => drawing = false);
})();

function clearCanvas() {
  const c = document.getElementById('signatureCanvas');
  c.getContext('2d').clearRect(0, 0, c.width, c.height);
}

function isCanvasBlank() {
  const c = document.getElementById('signatureCanvas');
  return !c.getContext('2d').getImageData(0, 0, c.width, c.height).data.some(x => x !== 0);
}

/* ══════════════════════════════════════════════════════════════
   FINISH — POST COMPLETE + GENERATE PDF
══════════════════════════════════════════════════════════════ */
async function finishReport() {
  if (!reportId) { showAlert('Error: el reporte no fue creado correctamente.'); return; }
  if (isCanvasBlank()) { showAlert('Por favor, firma en el area de firma antes de continuar.'); return; }

  if (typeof window.jspdf === 'undefined') {
    showAlert('La libreria PDF no esta disponible. Recarga la pagina e intenta de nuevo.');
    return;
  }

  const canvas    = document.getElementById('signatureCanvas');
  const firmaData = canvas.toDataURL('image/png');

  /* ── 1. Guardar en BD ── */
  showOverlay('Guardando firma y completando reporte...');
  let reportFull = null;

  try {
    const res = await fetch(`${BASE}/${reportId}/complete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({ firma_tecnico: firmaData }),
    });
    if (!res.ok) {
      const errJson = await res.json().catch(() => ({}));
      throw new Error(errJson.message || 'HTTP ' + res.status);
    }
    const data = await res.json();
    reportFull = Object.assign({}, data.report, s1, s2, s3, s5, {
      mediciones:    Array.isArray(s4) ? s4 : [],
      firma_tecnico: firmaData,
    });
  } catch(e) {
    hideOverlay();
    showAlert('Error al guardar el reporte: ' + e.message);
    return;
  }

  /* ── 2. Generar PDF ── */
  hideOverlay();
  showOverlay('Generando PDF...');

  try {
    generatePDF(reportFull);
    toastr.success('Reporte completado y PDF generado correctamente.');
  } catch(e) {
    console.error('[PDF Error]', e);
    toastr.warning('Reporte guardado. Error al generar PDF: ' + e.message);
  } finally {
    hideOverlay();
  }

  setTimeout(() => window.location.href = '{{ route("technician.reports.index") }}', 2500);
}

/* ══════════════════════════════════════════════════════════════
   PDF GENERATION (jsPDF 2.5.2)
══════════════════════════════════════════════════════════════ */
function generatePDF(report) {
  const { jsPDF } = window.jspdf;
  const doc    = new jsPDF({ unit: 'mm', format: 'a4' });
  const pageW  = 210;
  const margin = 14;
  const bottom = 283;
  let y = 0, pageNum = 1;

  function addHeader() {
    doc.setFillColor(10, 22, 40);
    doc.rect(0, 0, pageW, 27, 'F');
    doc.setFont('helvetica', 'bold'); doc.setFontSize(16); doc.setTextColor(255, 255, 255);
    doc.text('MAC DEL NORTE', margin, 12);
    doc.setFontSize(7); doc.setFont('helvetica', 'normal');
    doc.text('MONITOREO, AUTOMATIZACIÓN Y CONTROLES DEL NORTE, S.A.P.I. de C.V.  ·  RFC: NMA180313M46', margin, 19);
    doc.text('C. Castaño 718, Ebanos Norte 2do Sector, Apodaca N.L. CP.66612  ·  +81-3582-5559  ·  contacto@macdelnorte.com', margin, 24);
    doc.setFillColor(244, 121, 32);
    doc.rect(0, 27, pageW, 2.5, 'F');
  }

  function newPage() {
    doc.addPage(); pageNum++; addHeader(); y = 34;
  }

  function chk(h) { if (y + h > bottom) newPage(); }

  function secTitle(title) {
    chk(12);
    doc.setFillColor(10, 22, 40); doc.rect(margin, y, pageW - margin * 2, 7, 'F');
    doc.setFont('helvetica', 'bold'); doc.setFontSize(9); doc.setTextColor(255, 255, 255);
    doc.text(title, margin + 3, y + 5); y += 9;
  }

  function row2(l1, v1, l2, v2) {
    chk(6);
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
    chk(6);
    doc.setFont('helvetica', 'bold'); doc.setFontSize(8); doc.setTextColor(80, 80, 80);
    doc.text(label + ':', margin, y);
    doc.setFont('helvetica', 'normal'); doc.setTextColor(0);
    const lines = doc.splitTextToSize(String(val || '-'), pageW - margin - 50);
    doc.text(lines, margin + 32, y);
    y += lines.length * 4.5 + 1.5;
  }

  /* ── Page 1 ── */
  addHeader(); y = 34;
  doc.setFont('helvetica', 'bold'); doc.setFontSize(13); doc.setTextColor(10, 22, 40);
  doc.text('REPORTE DE SERVICIO TÉCNICO', 105, y, { align: 'center' }); y += 9;

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

  /* ── Mediciones table (manual) ── */
  secTitle('4. TABLA DE MEDICIONES');
  const meds = Array.isArray(report.mediciones) ? report.mediciones : [];
  const colH = ['Punto', 'Val. Referencia', 'Val. Medido', 'Error', 'Tolerancia', 'Resultado'];
  const cw   = [33, 27, 27, 22, 22, 25];
  const rH   = 6.5, hH = 7;
  let needMedHead = true;

  function drawMedHead() {
    chk(hH + rH);
    let cx = margin;
    colH.forEach((c, i) => {
      doc.setFillColor(30, 51, 85); doc.rect(cx, y, cw[i], hH, 'F');
      doc.setFont('helvetica', 'bold'); doc.setFontSize(7.5); doc.setTextColor(255, 255, 255);
      doc.text(c, cx + 2, y + 5); cx += cw[i];
    });
    y += hH; needMedHead = false;
  }
  drawMedHead();

  meds.forEach((m, idx) => {
    if (y + rH > bottom) { newPage(); needMedHead = true; }
    if (needMedHead) drawMedHead();
    const cells = [m.punto, m.valor_referencia, m.valor_medido, m.error, m.tolerancia, m.resultado];
    let cx = margin;
    cells.forEach((cell, i) => {
      let bg = idx % 2 === 0 ? [250, 250, 250] : [240, 242, 245];
      let tc = [20, 20, 20], fw = 'normal';
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

  /* ── Section 5 ── */
  secTitle('5. OBSERVACIONES Y RECOMENDACIONES');
  doc.setFont('helvetica', 'bold'); doc.setFontSize(8); doc.setTextColor(60, 60, 60);
  doc.text('Observaciones:', margin, y); y += 5;
  doc.setFont('helvetica', 'normal'); doc.setTextColor(0);
  const obsL = doc.splitTextToSize(report.observaciones || 'Sin observaciones.', pageW - margin * 2);
  chk(obsL.length * 4.5); doc.text(obsL, margin, y); y += obsL.length * 4.5 + 4;
  doc.setFont('helvetica', 'bold'); doc.setFontSize(8); doc.setTextColor(60, 60, 60);
  doc.text('Recomendaciones:', margin, y); y += 5;
  doc.setFont('helvetica', 'normal'); doc.setTextColor(0);
  const recL = doc.splitTextToSize(report.recomendaciones || 'Sin recomendaciones.', pageW - margin * 2);
  chk(recL.length * 4.5); doc.text(recL, margin, y); y += recL.length * 4.5 + 6;

  /* ── Section 6: Firmas ── */
  chk(55); secTitle('6. FIRMAS');
  if (report.firma_tecnico) {
    try { doc.addImage(report.firma_tecnico, 'PNG', margin, y, 82, 32); } catch(e) {}
  }
  doc.setDrawColor(0);
  doc.line(margin,     y + 32, margin + 82, y + 32);
  doc.line(112,        y + 32, 196,         y + 32);
  doc.setFont('helvetica', 'normal'); doc.setFontSize(8); doc.setTextColor(60, 60, 60);
  doc.text('Firma y Sello del Técnico Responsable', margin,     y + 37);
  doc.text('Firma y Sello del Cliente',             112,        y + 37);
  y += 45;

  /* ── Section 7: Garantía ── */
  chk(30); secTitle('7. CLÁUSULA DE GARANTÍA (30 DÍAS)');
  doc.setFont('helvetica', 'normal'); doc.setFontSize(7.5); doc.setTextColor(60, 60, 60);
  const gar = 'MAC DEL NORTE garantiza los servicios prestados en el presente reporte por un período de 30 (treinta) días naturales contados a partir de la fecha de servicio indicada. Esta garantía cubre exclusivamente los trabajos de mano de obra y los materiales suministrados por MAC DEL NORTE. No cubre daños causados por mal uso, negligencia, accidentes, modificaciones no autorizadas, desgaste natural o causas ajenas al servicio prestado. Para hacer válida esta garantía, comuníquese a contacto@macdelnorte.com o llame al +81-3582-5559.';
  const garL = doc.splitTextToSize(gar, pageW - margin * 2);
  chk(garL.length * 4); doc.text(garL, margin, y);

  /* ── Footers en todas las páginas ── */
  const total = doc.getNumberOfPages();
  for (let p = 1; p <= total; p++) {
    doc.setPage(p);
    doc.setFillColor(10, 22, 40); doc.rect(0, 286, pageW, 11, 'F');
    doc.setFont('helvetica', 'normal'); doc.setFontSize(7); doc.setTextColor(255, 255, 255);
    doc.text('MAC DEL NORTE  |  contacto@macdelnorte.com  |  www.macdelnorte.com', 105, 291, { align: 'center' });
    doc.text('Pagina ' + p + ' de ' + total, 105, 295, { align: 'center' });
  }

  doc.save('Reporte_' + (report.folio || 'MAC') + '.pdf');
}
</script>
@endpush

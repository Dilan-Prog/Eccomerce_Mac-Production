<div id="step-general" class="step-panel">
    <div class="section-title">📋 Datos Generales</div>

    <div class="field-row col-2">
        <div class="field-group">
            <label for="folio">Folio *</label>
            <input type="text" id="folio" name="folio" placeholder="Ej. SRV-2024-001">
        </div>
        <div class="field-group">
            <label for="fecha">Fecha *</label>
            <input type="date" id="fecha" name="fecha">
        </div>
    </div>

    <div class="field-row col-2">
        <div class="field-group">
            <label for="tecnico">Técnico Responsable *</label>
            <input type="text" id="tecnico" name="tecnico" placeholder="Nombre completo del técnico">
        </div>
        <div class="field-group">
            <label for="tipoServicio">Tipo de Servicio</label>
            <select id="tipoServicio" name="tipoServicio">
                <option value="">— Seleccionar —</option>
                <option value="Calibración">Calibración</option>
                <option value="Reparación">Reparación</option>
                <option value="Mantenimiento Preventivo">Mantenimiento Preventivo</option>
                <option value="Mantenimiento Correctivo">Mantenimiento Correctivo</option>
                <option value="Instalación">Instalación</option>
                <option value="Diagnóstico">Diagnóstico</option>
            </select>
        </div>
    </div>
</div>

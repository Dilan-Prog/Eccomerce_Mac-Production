<div id="step-firma" class="step-panel">
    <div class="section-title">✍️ Firma y Generación de PDF</div>

    <!-- Resumen del reporte -->
    <div style="
        background: var(--g50);
        border: 1px solid var(--g200);
        border-radius: 10px;
        padding: 18px 20px;
        margin-bottom: 28px;
    ">
        <div style="
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--navy);
            margin-bottom: 14px;
        ">Resumen del Reporte</div>

        <div class="summary-grid">
            <div class="summary-item">
                <span class="s-label">Folio</span>
                <span class="s-value" id="sum-folio">—</span>
            </div>
            <div class="summary-item">
                <span class="s-label">Fecha</span>
                <span class="s-value" id="sum-fecha">—</span>
            </div>
            <div class="summary-item">
                <span class="s-label">Técnico</span>
                <span class="s-value" id="sum-tecnico">—</span>
            </div>
            <div class="summary-item">
                <span class="s-label">Cliente</span>
                <span class="s-value" id="sum-cliente">—</span>
            </div>
            <div class="summary-item">
                <span class="s-label">Equipo</span>
                <span class="s-value" id="sum-equipo">—</span>
            </div>
            <div class="summary-item">
                <span class="s-label">Tipo de Servicio</span>
                <span class="s-value" id="sum-tipo">—</span>
            </div>
        </div>
    </div>

    <!-- Canvas de firma -->
    <div style="margin-bottom: 28px;">
        <label style="margin-bottom: 10px;">Firma del Técnico Responsable</label>

        <div id="sig-canvas-wrap">
            <canvas id="sigCanvas" width="600" height="180"></canvas>
            <div id="sig-baseline"></div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; margin-top: 10px;">
            <button id="btn-clear-sig" class="btn-secondary" style="font-size: 12px; padding: 6px 12px;">
                🗑 Limpiar firma
            </button>
            <span id="sig-status" style="font-size: 13px; color: var(--g400);">Sin firma</span>
        </div>
    </div>

    <!-- Generar PDF -->
    <div style="
        text-align: center;
        padding: 10px 0 4px;
        border-top: 1px solid var(--g100);
        margin-top: 8px;
    ">
        <button id="btn-generate-pdf" class="btn-primary" style="
            padding: 14px 48px;
            font-size: 16px;
            border-radius: 10px;
            background: var(--navy);
        ">
            📄 Generar PDF
        </button>
        <div style="font-size: 11px; color: var(--g400); margin-top: 10px;">
            Se descargará automáticamente · Requiere folio, técnico y al menos un punto de medición
        </div>
    </div>
</div>

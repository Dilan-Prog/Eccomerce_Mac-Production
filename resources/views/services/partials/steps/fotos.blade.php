<div id="step-fotos" class="step-panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="section-title" style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;">
            📷 Fotografías del Servicio
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <span id="foto-count-label" style="font-size: 12px; color: var(--g500); font-weight: 600;">
                0 / 8 fotos
            </span>
            <button id="btn-add-foto" class="btn-primary" style="padding: 8px 16px; font-size: 13px;">
                + Agregar
            </button>
        </div>
    </div>

    <input type="file" id="fotoInput" accept="image/*" multiple style="display: none;">

    <div id="foto-empty" style="
        text-align: center;
        padding: 52px 20px;
        color: var(--g400);
        border: 2px dashed var(--g200);
        border-radius: 10px;
    ">
        <div style="font-size: 40px; margin-bottom: 10px;">📷</div>
        <div style="font-size: 14px; font-weight: 600; color: var(--g500);">Sin fotografías agregadas</div>
        <div style="font-size: 12px; margin-top: 5px;">Haz clic en "+ Agregar" para subir imágenes (máx. 8)</div>
    </div>

    <div id="foto-grid"></div>
</div>

<div id="step-mediciones" class="step-panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="section-title" style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;">
            📊 Tabla de Mediciones
        </div>
        <button id="btn-add-medicion" class="btn-primary" style="padding: 8px 16px; font-size: 13px;">
            + Agregar fila
        </button>
    </div>

    <div style="overflow-x: auto; border-radius: 8px; border: 1px solid var(--g200);">
        <table id="med-table">
            <thead>
                <tr>
                    <th>Punto</th>
                    <th>Valor Ref.</th>
                    <th>Valor Medido</th>
                    <th>Error</th>
                    <th>Tolerancia</th>
                    <th>Resultado</th>
                    <th style="text-align: center; width: 44px;">×</th>
                </tr>
            </thead>
            <tbody id="med-tbody">
                {{-- Filas generadas por form-wizard.js --}}
            </tbody>
        </table>
    </div>

    <p style="font-size: 11px; color: var(--g400); margin-top: 10px;">
        * Al menos un punto de medición es requerido para generar el PDF.
    </p>
</div>

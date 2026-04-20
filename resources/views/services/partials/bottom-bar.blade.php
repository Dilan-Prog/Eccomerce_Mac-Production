<div id="bottom-bar" style="
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: var(--white);
    border-top: 1px solid var(--g200);
    box-shadow: 0 -4px 16px rgba(10,22,40,0.07);
    z-index: 100;
    padding: 12px 16px;
">
    <div style="
        max-width: 900px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    ">
        <button id="btn-prev" class="btn-secondary" style="min-width: 110px;">
            ← Anterior
        </button>

        <div id="step-dots" style="display: flex; gap: 8px; align-items: center;">
            <!-- Dots rendered by form-wizard.js -->
        </div>

        <button id="btn-next" class="btn-primary" style="min-width: 130px;">
            Siguiente →
        </button>
    </div>
</div>

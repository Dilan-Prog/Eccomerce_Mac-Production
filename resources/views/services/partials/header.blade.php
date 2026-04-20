<header id="app-header" style="
    background: var(--navy);
    color: var(--white);
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
">
    <div style="
        max-width: 900px;
        margin: 0 auto;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
    ">
        <!-- Logo -->
        <div style="flex-shrink: 0;">
            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="44" height="44" rx="7" fill="#0A1628"/>
                <rect x="0" y="36" width="44" height="8" fill="#F47920"/>
                <text x="22" y="18" text-anchor="middle" fill="#F47920" font-size="11"
                      font-weight="800" font-family="sans-serif">MAC</text>
                <text x="22" y="28" text-anchor="middle" fill="#FFFFFF" font-size="5.5"
                      font-weight="600" font-family="sans-serif" letter-spacing="0.5">DEL NORTE</text>
            </svg>
        </div>

        <!-- Title -->
        <div style="flex: 1; min-width: 0;">
            <div style="font-size: 15px; font-weight: 800; letter-spacing: 0.2px; line-height: 1.2;">
                Reporte de Servicio Técnico
            </div>
            <div style="font-size: 11px; color: var(--g400); margin-top: 2px;">
                MONITOREO, AUTOMATIZACIÓN Y CONTROLES DEL NORTE
            </div>
        </div>

        <!-- Progress bar -->
        <div style="flex: 0 0 170px; display: flex; flex-direction: column; gap: 5px;">
            <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--g400);">
                <span>Completado</span>
                <span id="progress-pct" style="font-weight: 700; color: var(--orange);">0%</span>
            </div>
            <div style="background: var(--navy-mid); border-radius: 4px; height: 5px; overflow: hidden;">
                <div id="progress-bar" style="
                    height: 100%;
                    background: var(--orange);
                    border-radius: 4px;
                    width: 0%;
                    transition: width 0.4s ease;
                "></div>
            </div>
        </div>

        <!-- Nuevo button -->
        <button id="btn-nuevo" class="btn-secondary" style="
            border-color: rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.75);
            flex-shrink: 0;
        ">
            + Nuevo
        </button>
    </div>
</header>

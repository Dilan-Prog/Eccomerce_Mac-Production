{{-- Duplicados panel: scan trigger, summary stats, status tabs and the group grid.
     Included from index.blade.php inside #ml-panel-duplicates. JS lives in
     public/admin-ui/js/duplicate-images/duplicate-images.js (AU.DuplicateImages). --}}
<div class="au-stat-grid au-mt-4">
    <div class="au-stat-card">
        <div class="au-stat-icon is-info"><i class="fas fa-layer-group"></i></div>
        <div>
            <div class="au-stat-label">Grupos encontrados</div>
            <div class="au-stat-value" id="dup-stat-groups">—</div>
        </div>
    </div>
    <div class="au-stat-card">
        <div class="au-stat-icon is-warning"><i class="fas fa-clone"></i></div>
        <div>
            <div class="au-stat-label">Archivos duplicados</div>
            <div class="au-stat-value" id="dup-stat-files">—</div>
        </div>
    </div>
    <div class="au-stat-card">
        <div class="au-stat-icon is-success"><i class="fas fa-hdd"></i></div>
        <div>
            <div class="au-stat-label">MB recuperables (estimado)</div>
            <div class="au-stat-value" id="dup-stat-mb">—</div>
        </div>
    </div>
    <div class="au-stat-card">
        <div class="au-stat-icon"><i class="fas fa-clock"></i></div>
        <div>
            <div class="au-stat-label">Último escaneo</div>
            <div class="au-stat-value" id="dup-stat-last-scan" style="font-size:14px">—</div>
        </div>
    </div>
</div>

<div class="au-card au-mt-4">
    <div class="au-card-header">
        <div class="au-card-title">Imágenes duplicadas</div>
        <button type="button" class="au-btn au-btn-primary" id="dup-scan-btn">
            <i class="fas fa-magnifying-glass"></i> Escanear
        </button>
    </div>
    <div class="au-tabs">
        <div class="au-tab is-active" data-dup-tab="pending">Pendientes <span class="au-tab-count" id="dup-tab-count-pending">0</span></div>
        <div class="au-tab" data-dup-tab="discarded">Descartados <span class="au-tab-count" id="dup-tab-count-discarded">0</span></div>
        <div class="au-tab" data-dup-tab="resolved">Resueltos <span class="au-tab-count" id="dup-tab-count-resolved">0</span></div>
        <div class="au-tab" data-dup-tab="all">Todos <span class="au-tab-count" id="dup-tab-count-all">0</span></div>
    </div>
    <div class="au-card-body">
        <div id="dup-groups-grid">
            <div class="au-table-loading">Cargando...</div>
        </div>
        <div class="au-table-footer" id="dup-pagination-footer" style="display:none">
            <span class="au-table-count" id="dup-pagination-count"></span>
            <div class="au-pagination" id="dup-pagination"></div>
        </div>
    </div>
</div>

{{-- "Reemplazar con..." dialog --}}
<div class="au-modal-overlay" id="dup-replace-modal">
    <div class="au-modal" style="width:640px" role="dialog" aria-modal="true">
        <div class="au-modal-head">
            <div class="au-modal-icon"><i class="fas fa-arrows-rotate"></i></div>
            <div style="display:flex;flex-direction:column;gap:6px">
                <div class="au-modal-title">Reemplazar con...</div>
                <div class="au-modal-text">Elige la imagen que se conservará. Las demás marcadas se descartarán y sus referencias se actualizarán automáticamente.</div>
            </div>
        </div>

        <div class="dup-replace-mode-tabs" style="padding:0 22px">
            <button type="button" class="au-btn au-btn-sm dup-replace-mode-btn is-active" data-dup-replace-mode="group_member">Del mismo grupo</button>
            <button type="button" class="au-btn au-btn-sm dup-replace-mode-btn" data-dup-replace-mode="library_search">Buscar en biblioteca</button>
            <button type="button" class="au-btn au-btn-sm dup-replace-mode-btn" data-dup-replace-mode="upload">Subir nueva</button>
        </div>

        <div style="padding:0 22px;display:flex;flex-direction:column;gap:14px">
            <div class="dup-replace-panel" id="dup-replace-panel-group_member">
                <div class="dup-thumb-grid" id="dup-replace-group-members"></div>
            </div>

            <div class="dup-replace-panel" id="dup-replace-panel-library_search" hidden>
                <div class="au-table-search" style="width:100%">
                    <i class="fas fa-search"></i>
                    <input type="text" id="dup-replace-search-input" placeholder="Buscar por nombre de archivo...">
                </div>
                <div class="dup-thumb-grid" id="dup-replace-search-results" style="margin-top:12px"></div>
            </div>

            <div class="dup-replace-panel" id="dup-replace-panel-upload" hidden>
                <div class="au-field" style="margin-bottom:0">
                    <label class="au-label">Nueva imagen</label>
                    <input class="au-input" type="file" id="dup-replace-upload" accept="image/*" style="padding:8px 11px">
                </div>
            </div>
        </div>

        <div class="au-modal-actions">
            <button type="button" class="au-btn" id="dup-replace-cancel">Cancelar</button>
            <button type="button" class="au-btn au-btn-primary" id="dup-replace-confirm">Confirmar</button>
        </div>
    </div>
</div>

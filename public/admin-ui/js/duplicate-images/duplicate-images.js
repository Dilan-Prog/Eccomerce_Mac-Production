/*
 * Duplicate image detection panel (Media Library > Duplicados).
 * Plain ES2017+, no build step — hangs off window.AU like every other
 * admin-ui script. Talks to the AspelSync-style duplicate-images endpoints:
 *   POST admin.duplicate-images.scan
 *   GET  admin.duplicate-images.data?status=&page=&per_page=
 *   GET  admin.duplicate-images.search?q=
 *   POST admin.duplicate-images.{group}.discard / .restore / .replace
 *
 * Usage (wired from media-library/index.blade.php):
 *   AU.DuplicateImages.init({
 *     dataUrl, scanUrl, searchUrl,
 *     discardUrlTemplate, restoreUrlTemplate, replaceUrlTemplate, // contain __GROUP_ID__
 *   });
 */
window.AU = window.AU || {};

AU.DuplicateImages = (function () {
  const GROUP_ID_TOKEN = "__GROUP_ID__";
  const PER_PAGE = 24;

  function formatSize(bytes) {
    if (bytes === undefined || bytes === null) return "";
    const n = Number(bytes);
    if (Number.isNaN(n)) return "";
    return n >= 1048576 ? `${(n / 1048576).toFixed(1)} MB` : `${(n / 1024).toFixed(1)} KB`;
  }

  // The data endpoint returns raw `file_size` bytes on group members (unlike
  // the search endpoint, which returns a pre-formatted `size_label`) — fall
  // back to formatting it client-side either way.
  function sizeLabel(entity) {
    return entity.size_label || formatSize(entity.file_size);
  }

  let config = null;
  let activeTab = "pending";
  let currentPage = 1;
  let currentGroups = [];
  let selections = {}; // groupId -> Set<path_hash> marked for discard on replace
  let replaceCtx = null; // { groupId, mode, keeperPathHash, keeperFile }

  function groupUrl(template, groupId) {
    return template.replace(GROUP_ID_TOKEN, encodeURIComponent(groupId));
  }

  function init(cfg) {
    config = cfg;
    bindEvents();
    fetchData();
  }

  function bindEvents() {
    const statusTabs = AU.qs("#ml-panel-duplicates .au-tabs");
    AU.on(statusTabs, "click", "[data-dup-tab]", (e, el) => {
      const tab = el.getAttribute("data-dup-tab");
      if (tab === activeTab) return;
      activeTab = tab;
      currentPage = 1;
      AU.qsa("#ml-panel-duplicates .au-tabs .au-tab").forEach((t) => t.classList.remove("is-active"));
      el.classList.add("is-active");
      fetchData();
    });

    document.getElementById("dup-scan-btn").addEventListener("click", runScan);

    const grid = document.getElementById("dup-groups-grid");
    AU.on(grid, "click", "[data-dup-discard]", (e, el) => onDiscard(el.getAttribute("data-dup-discard")));
    AU.on(grid, "click", "[data-dup-restore]", (e, el) => onRestore(el.getAttribute("data-dup-restore")));
    AU.on(grid, "click", "[data-dup-replace]", (e, el) => openReplaceModal(el.getAttribute("data-dup-replace")));
    AU.on(grid, "change", "[data-dup-thumb-check]", (e, el) => {
      const groupId = el.getAttribute("data-group-id");
      const pathHash = el.getAttribute("data-path-hash");
      if (!selections[groupId]) selections[groupId] = new Set();
      if (el.checked) {
        selections[groupId].add(pathHash);
      } else {
        selections[groupId].delete(pathHash);
      }
    });

    AU.on(document.getElementById("dup-pagination-footer"), "click", "[data-dup-page]", (e, el) => {
      const page = parseInt(el.getAttribute("data-dup-page"), 10);
      if (page && page !== currentPage) {
        currentPage = page;
        fetchData();
      }
    });

    // Replace modal
    const modal = document.getElementById("dup-replace-modal");
    document.getElementById("dup-replace-cancel").addEventListener("click", closeReplaceModal);
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeReplaceModal();
    });

    AU.on(modal, "click", "[data-dup-replace-mode]", (e, el) => {
      switchReplaceMode(el.getAttribute("data-dup-replace-mode"));
    });

    AU.on(modal, "change", "[data-dup-keeper-radio]", (e, el) => {
      if (replaceCtx) replaceCtx.keeperPathHash = el.value;
    });

    const searchInput = document.getElementById("dup-replace-search-input");
    searchInput.addEventListener(
      "input",
      AU.debounce((e) => runLibrarySearch(e.target.value), 350)
    );

    document.getElementById("dup-replace-upload").addEventListener("change", (e) => {
      if (replaceCtx) replaceCtx.keeperFile = e.target.files[0] || null;
    });

    document.getElementById("dup-replace-confirm").addEventListener("click", submitReplace);
  }

  async function fetchData() {
    const grid = document.getElementById("dup-groups-grid");
    grid.innerHTML = '<div class="au-table-loading">Cargando...</div>';
    try {
      const params = new URLSearchParams({
        status: activeTab,
        page: String(currentPage),
        per_page: String(PER_PAGE),
      });
      const data = await AU.request(`${config.dataUrl}?${params.toString()}`);
      currentGroups = data.groups || [];
      render(data);
    } catch (err) {
      grid.innerHTML = '<div class="au-table-empty">No se pudieron cargar los duplicados.</div>';
      AU.toast.error((err.data && err.data.message) || "Ocurrió un error al cargar los duplicados");
    }
  }

  function render(data) {
    renderStats(data.summary || {});
    renderTabCounts(data.tabs || []);
    renderGroups();
    renderPagination(data);
  }

  function renderStats(summary) {
    document.getElementById("dup-stat-groups").textContent =
      summary.groups_found !== undefined ? summary.groups_found : "—";
    document.getElementById("dup-stat-files").textContent =
      summary.duplicate_files !== undefined ? summary.duplicate_files : "—";
    document.getElementById("dup-stat-mb").textContent =
      summary.recoverable_mb_estimate !== undefined ? `${summary.recoverable_mb_estimate} MB` : "—";

    const lastScanEl = document.getElementById("dup-stat-last-scan");
    // Backend returns a flat `last_scan_at` ISO string; tolerate a nested
    // `last_scan: {at, duration_ms}` shape too in case that ever lands.
    const at = (summary.last_scan && summary.last_scan.at) || summary.last_scan_at;
    const durationMs = summary.last_scan && summary.last_scan.duration_ms;
    if (at) {
      const d = new Date(at);
      const label = isNaN(d.getTime())
        ? at
        : d.toLocaleString("es-MX", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });
      lastScanEl.textContent = durationMs ? `${label} · ${durationMs} ms` : label;
    } else {
      lastScanEl.textContent = "Nunca";
    }
  }

  function renderTabCounts(tabs) {
    tabs.forEach((t) => {
      const el = document.getElementById(`dup-tab-count-${t.key}`);
      if (el) el.textContent = t.count;
    });
  }

  function renderGroups() {
    const grid = document.getElementById("dup-groups-grid");

    if (!currentGroups.length) {
      grid.innerHTML = '<div class="au-table-empty">No hay grupos de duplicados en esta vista.</div>';
      return;
    }

    // Default: every non-keeper member is pre-marked for discard the first
    // time a group is seen; the checkboxes let the user opt individual files
    // back out before opening "Reemplazar con...".
    currentGroups.forEach((g) => {
      if (!selections[g.id]) {
        selections[g.id] = new Set((g.members || []).map((m) => m.path_hash));
      }
    });

    grid.innerHTML = currentGroups.map(renderGroupCard).join("");
  }

  function renderGroupCard(group) {
    const statusBadge =
      group.status === "resolved"
        ? '<span class="au-badge au-badge-success"><span class="au-badge-dot"></span>Resuelto</span>'
        : group.status === "discarded"
        ? '<span class="au-badge au-badge-warning"><span class="au-badge-dot"></span>Descartado</span>'
        : '<span class="au-badge au-badge-info"><span class="au-badge-dot"></span>Pendiente</span>';

    const actionBtn =
      group.status === "discarded"
        ? `<button type="button" class="au-btn au-btn-sm" data-dup-restore="${AU.escapeHtml(group.id)}">Restaurar</button>`
        : `<button type="button" class="au-btn au-btn-sm au-btn-critical" data-dup-discard="${AU.escapeHtml(group.id)}">Descartar</button>`;

    const replaceBtn = `<button type="button" class="au-btn au-btn-sm au-btn-primary" data-dup-replace="${AU.escapeHtml(group.id)}">Reemplazar con...</button>`;

    const members = group.members || [];
    const thumbs = members.map((m) => renderThumb(group.id, m)).join("");

    return `
      <div class="au-card dup-group-card" data-dup-group="${AU.escapeHtml(group.id)}">
        <div class="au-card-header">
          <div class="au-flex au-gap-2">
            ${statusBadge}
            <span class="au-text-subdued" style="font-size:12.5px">${members.length} archivo${members.length === 1 ? "" : "s"}</span>
          </div>
          <div class="au-flex au-gap-2">
            ${replaceBtn}
            ${actionBtn}
          </div>
        </div>
        <div class="au-card-body">
          <div class="dup-thumb-grid">${thumbs}</div>
        </div>
      </div>`;
  }

  function renderThumb(groupId, member) {
    const usedIn = member.used_in || [];
    const badge = usedIn.length
      ? `<span class="au-badge au-badge-info" title="${AU.escapeHtml(usedIn.map((u) => u.label).join(", "))}"><span class="au-badge-dot"></span>Usada en ${usedIn.length} lugar${usedIn.length === 1 ? "" : "es"}</span>`
      : '<span class="au-badge au-badge-warning"><span class="au-badge-dot"></span>Archivo huérfano</span>';

    const checked = selections[groupId] && selections[groupId].has(member.path_hash) ? "checked" : "";

    return `
      <div class="dup-thumb" data-path-hash="${AU.escapeHtml(member.path_hash)}">
        <label class="dup-thumb-checkbox-overlay">
          <input type="checkbox" class="au-checkbox" data-dup-thumb-check
                 data-group-id="${AU.escapeHtml(groupId)}" data-path-hash="${AU.escapeHtml(member.path_hash)}" ${checked}>
        </label>
        <img class="dup-thumb-img" src="${AU.escapeHtml(member.url)}" alt="">
        <div class="dup-thumb-meta">
          <span class="au-text-subdued" style="font-size:11.5px">${AU.escapeHtml(sizeLabel(member))}</span>
          ${badge}
        </div>
      </div>`;
  }

  function renderPagination(data) {
    const footer = document.getElementById("dup-pagination-footer");
    const total = data.total || 0;
    const perPage = data.per_page || PER_PAGE;
    const page = data.page || currentPage;
    const totalPages = Math.max(1, Math.ceil(total / perPage));

    if (total <= perPage) {
      footer.style.display = "none";
      return;
    }

    footer.style.display = "flex";
    const from = total === 0 ? 0 : (page - 1) * perPage + 1;
    const to = Math.min(total, page * perPage);
    document.getElementById("dup-pagination-count").textContent = `Mostrando ${from}-${to} de ${total}`;

    const pager = document.getElementById("dup-pagination");
    pager.innerHTML = `
      <button type="button" class="au-page-btn" data-dup-page="${page - 1}" ${page <= 1 ? "disabled" : ""}><i class="fas fa-chevron-left"></i></button>
      <span class="au-text-subdued" style="font-size:12px">Página ${page} de ${totalPages}</span>
      <button type="button" class="au-page-btn" data-dup-page="${page + 1}" ${page >= totalPages ? "disabled" : ""}><i class="fas fa-chevron-right"></i></button>`;
  }

  async function runScan() {
    const btn = document.getElementById("dup-scan-btn");
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Escaneando...';
    try {
      const data = await AU.request(config.scanUrl, { method: "POST" });
      AU.toast[data.status === "success" ? "success" : "error"](data.message || "Escaneo completado");
      fetchData();
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "Ocurrió un error al escanear");
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  }

  async function onDiscard(groupId) {
    const confirmed = await AU.confirm({
      title: "¿Descartar este grupo?",
      text: "El grupo pasará a la pestaña de Descartados. Podrás restaurarlo cuando quieras.",
      tone: "warning",
      confirmText: "Sí, descartar",
    });
    if (!confirmed) return;

    try {
      const data = await AU.request(groupUrl(config.discardUrlTemplate, groupId), { method: "POST" });
      AU.toast[data.status === "success" ? "success" : "error"](data.message || "Listo");
      fetchData();
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "Ocurrió un error al descartar");
    }
  }

  async function onRestore(groupId) {
    try {
      const data = await AU.request(groupUrl(config.restoreUrlTemplate, groupId), { method: "POST" });
      AU.toast[data.status === "success" ? "success" : "error"](data.message || "Listo");
      fetchData();
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "Ocurrió un error al restaurar");
    }
  }

  function findGroup(groupId) {
    return currentGroups.find((g) => String(g.id) === String(groupId));
  }

  function openReplaceModal(groupId) {
    const group = findGroup(groupId);
    if (!group) return;

    replaceCtx = { groupId, mode: "group_member", keeperPathHash: null, keeperFile: null };

    document.getElementById("dup-replace-search-input").value = "";
    document.getElementById("dup-replace-search-results").innerHTML = "";
    document.getElementById("dup-replace-upload").value = "";

    renderGroupMemberPicker(group);
    switchReplaceMode("group_member");

    document.getElementById("dup-replace-modal").classList.add("is-open");
  }

  function renderGroupMemberPicker(group) {
    const container = document.getElementById("dup-replace-group-members");
    const members = group.members || [];
    container.innerHTML = members
      .map(
        (m, i) => `
      <label class="dup-thumb dup-thumb-selectable">
        <input type="radio" name="dup-keeper" data-dup-keeper-radio value="${AU.escapeHtml(m.path_hash)}" ${i === 0 ? "checked" : ""}>
        <img class="dup-thumb-img" src="${AU.escapeHtml(m.url)}" alt="">
        <div class="dup-thumb-meta">
          <span class="au-text-subdued" style="font-size:11.5px">${AU.escapeHtml(sizeLabel(m))}</span>
        </div>
      </label>`
      )
      .join("");

    if (replaceCtx && members[0]) {
      replaceCtx.keeperPathHash = members[0].path_hash;
    }
  }

  function switchReplaceMode(mode) {
    if (replaceCtx) replaceCtx.mode = mode;

    AU.qsa("#dup-replace-modal [data-dup-replace-mode]").forEach((btn) => {
      btn.classList.toggle("is-active", btn.getAttribute("data-dup-replace-mode") === mode);
    });

    ["group_member", "library_search", "upload"].forEach((m) => {
      const panel = document.getElementById(`dup-replace-panel-${m}`);
      if (panel) panel.hidden = m !== mode;
    });
  }

  async function runLibrarySearch(query) {
    const resultsEl = document.getElementById("dup-replace-search-results");
    const q = (query || "").trim();
    if (q.length < 2) {
      resultsEl.innerHTML = "";
      return;
    }

    resultsEl.innerHTML = '<div class="au-table-loading">Buscando...</div>';
    try {
      const data = await AU.request(`${config.searchUrl}?q=${encodeURIComponent(q)}`);
      const results = data.results || [];
      resultsEl.innerHTML = results.length
        ? results
            .map(
              (r) => `
          <label class="dup-thumb dup-thumb-selectable">
            <input type="radio" name="dup-keeper" data-dup-keeper-radio value="${AU.escapeHtml(r.path_hash)}">
            <img class="dup-thumb-img" src="${AU.escapeHtml(r.url)}" alt="">
            <div class="dup-thumb-meta">
              <span class="au-text-subdued" style="font-size:11px">${AU.escapeHtml(r.name)}</span>
              <span class="au-text-subdued" style="font-size:11px">${AU.escapeHtml(r.size_label)}</span>
            </div>
          </label>`
            )
            .join("")
        : '<div class="au-table-empty">Sin resultados.</div>';
    } catch (err) {
      resultsEl.innerHTML = '<div class="au-table-empty">Ocurrió un error al buscar.</div>';
    }
  }

  async function submitReplace() {
    if (!replaceCtx) return;
    const { groupId, mode } = replaceCtx;
    const group = findGroup(groupId);
    if (!group) return;

    if (mode === "upload" && !replaceCtx.keeperFile) {
      AU.toast.error("Selecciona un archivo para subir.");
      return;
    }
    if ((mode === "group_member" || mode === "library_search") && !replaceCtx.keeperPathHash) {
      AU.toast.error("Selecciona una imagen para conservar.");
      return;
    }

    const formData = new FormData();
    formData.append("keeper_mode", mode);
    if (mode === "upload") {
      formData.append("keeper_file", replaceCtx.keeperFile);
    } else {
      formData.append("keeper_path_hash", replaceCtx.keeperPathHash);
    }

    const discardSet = selections[groupId] || new Set();
    (group.members || []).forEach((m) => {
      const isKeeper = mode !== "upload" && m.path_hash === replaceCtx.keeperPathHash;
      if (!isKeeper && discardSet.has(m.path_hash)) {
        formData.append("discard_path_hashes[]", m.path_hash);
      }
    });

    const confirmBtn = document.getElementById("dup-replace-confirm");
    confirmBtn.disabled = true;
    try {
      const data = await AU.request(groupUrl(config.replaceUrlTemplate, groupId), {
        method: "POST",
        body: formData,
      });
      AU.toast[data.status === "success" ? "success" : "error"](data.message || "Listo");
      closeReplaceModal();
      fetchData();
    } catch (err) {
      if (err.status === 422 && err.data && err.data.errors) {
        const firstError = Object.values(err.data.errors)[0];
        AU.toast.error((firstError && firstError[0]) || "Revisa los datos ingresados.");
      } else {
        AU.toast.error((err.data && err.data.message) || "Ocurrió un error al reemplazar");
      }
    } finally {
      confirmBtn.disabled = false;
    }
  }

  function closeReplaceModal() {
    document.getElementById("dup-replace-modal").classList.remove("is-open");
    replaceCtx = null;
  }

  return {
    init,
    fetchData,
    runScan,
    onDiscard,
    onRestore,
    openReplaceModal,
    submitReplace,
  };
})();

/*
 * Image gallery picker sub-modal, matching the "Modal Producto" reference's
 * image-selection overlay. Reuses the existing Media Library JSON endpoints
 * (media-library.data / media-library.store) as-is — no new backend surface,
 * this is purely a thinner, thumbnail-grid-only consumer of the same
 * contract admin-table.js already renders as a full data table.
 *
 * Independent overlay (own singleton, stacks above an already-open
 * AU.FormModal) so it can be opened mid-form without fighting for the same
 * DOM node.
 *
 * Usage:
 *   AU.ImagePicker.open({
 *     dataUrl: '{{ route("admin.media-library.data") }}',
 *     uploadUrl: '{{ route("admin.media-library.store") }}',
 *     defaultFolder: 'product',        // used as the upload destination folder
 *     multiple: false,                  // true => onSelect receives an array
 *     onSelect: (file) => { ... },      // file = { path, url, name }
 *   });
 */
window.AU = window.AU || {};

(function () {
  let overlay = null;
  let state = null;

  function ensureShell() {
    if (overlay) return overlay;
    overlay = document.createElement("div");
    overlay.className = "au-picker-overlay";
    overlay.innerHTML = `
      <div class="au-picker" role="dialog" aria-modal="true">
        <div class="au-picker-head">
          <div class="au-picker-titles">
            <h3 class="au-picker-title">Seleccionar imagen</h3>
            <span class="au-picker-summary" data-au-picker-summary></span>
          </div>
          <button type="button" class="au-btn au-btn-plain au-btn-icon" data-au-picker-close aria-label="Cerrar">&times;</button>
        </div>
        <div class="au-picker-toolbar">
          <div class="au-picker-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar en la galería..." data-au-picker-search>
          </div>
          <select class="au-select" data-au-picker-folder style="width:auto"></select>
          <button type="button" class="au-btn au-btn-primary" data-au-picker-upload>
            <i class="fas fa-arrow-up"></i> Subir nueva
          </button>
          <input type="file" accept="image/*" multiple hidden data-au-picker-file>
        </div>
        <div class="au-picker-grid" data-au-picker-grid></div>
        <div class="au-picker-foot">
          <span class="au-picker-note" data-au-picker-note></span>
          <button type="button" class="au-btn" data-au-picker-cancel>Cancelar</button>
          <button type="button" class="au-btn au-btn-primary" data-au-picker-confirm disabled>Usar imagen</button>
        </div>
      </div>`;
    // Every au-picker-* rule is scoped under .admin-ui-v2 — appending to
    // document.body directly would leave the overlay completely unstyled.
    (document.querySelector(".admin-ui-v2") || document.body).appendChild(overlay);

    overlay.querySelector("[data-au-picker-close]").addEventListener("click", close);
    overlay.querySelector("[data-au-picker-cancel]").addEventListener("click", close);
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) close();
    });

    overlay.querySelector("[data-au-picker-search]").addEventListener(
      "input",
      AU.debounce((e) => {
        state.search = e.target.value;
        fetchGrid();
      }, 300)
    );
    overlay.querySelector("[data-au-picker-folder]").addEventListener("change", (e) => {
      state.filter = e.target.value;
      fetchGrid();
    });

    const fileInput = overlay.querySelector("[data-au-picker-file]");
    overlay.querySelector("[data-au-picker-upload]").addEventListener("click", () => fileInput.click());
    fileInput.addEventListener("change", async (e) => {
      const files = Array.from(e.target.files || []);
      e.target.value = "";
      if (!files.length) return;
      await upload(files);
    });

    overlay.querySelector("[data-au-picker-confirm]").addEventListener("click", () => {
      if (!state.selected.size) return;
      const files = Array.from(state.selected.values());
      close();
      if (state.opts.onSelect) state.opts.onSelect(state.opts.multiple ? files : files[0]);
    });

    return overlay;
  }

  function close() {
    if (!overlay) return;
    overlay.classList.remove("is-open");
  }

  function relativePathFromUrl(url) {
    try {
      const u = new URL(url, window.location.origin);
      return u.pathname.replace(/^\//, "");
    } catch (err) {
      return url;
    }
  }

  function renderFolderOptions(filters) {
    const select = overlay.querySelector("[data-au-picker-folder]");
    select.innerHTML = filters
      .map((f) => `<option value="${AU.escapeHtml(f.key)}">${AU.escapeHtml(f.label)} (${f.count})</option>`)
      .join("");
    select.value = state.filter;
  }

  function renderGrid(rows) {
    const grid = overlay.querySelector("[data-au-picker-grid]");
    if (!rows.length) {
      grid.innerHTML = `<div class="au-table-empty">Sin imágenes en esta carpeta.</div>`;
      return;
    }
    grid.innerHTML = rows
      .map((row) => {
        const file = {
          path: relativePathFromUrl(row.cells.thumbnail.url),
          url: row.cells.thumbnail.url,
          name: row.cells.name,
        };
        const isSelected = state.selected.has(row.row_id);
        return `
        <button type="button" class="au-picker-item ${isSelected ? "is-selected" : ""}" data-au-picker-pick="${AU.escapeHtml(row.row_id)}">
          <span class="au-picker-item-thumb" style="background-image:url('${AU.escapeHtml(file.url)}')"></span>
          <span class="au-picker-item-meta">
            <span class="au-picker-item-name">${AU.escapeHtml(file.name)}</span>
            <span class="au-picker-item-sub">${AU.escapeHtml(row.cells.folder ? row.cells.folder.label : "")} · ${AU.escapeHtml(row.cells.size || "")}</span>
          </span>
          <span class="au-picker-item-check">&check;</span>
        </button>`;
      })
      .join("");

    AU.qsa("[data-au-picker-pick]", grid).forEach((btn) => {
      btn.addEventListener("click", () => {
        const rowId = btn.getAttribute("data-au-picker-pick");
        const row = state.rowsById.get(rowId);
        if (!row) return;
        const file = { path: relativePathFromUrl(row.cells.thumbnail.url), url: row.cells.thumbnail.url, name: row.cells.name };

        if (!state.opts.multiple) state.selected.clear();
        if (state.selected.has(rowId)) {
          state.selected.delete(rowId);
        } else {
          state.selected.set(rowId, file);
        }
        renderGrid(Array.from(state.rowsById.values()));
        updateFooter();
      });
    });
  }

  function updateFooter() {
    const note = overlay.querySelector("[data-au-picker-note]");
    const confirmBtn = overlay.querySelector("[data-au-picker-confirm]");
    const count = state.selected.size;
    note.textContent = count
      ? count === 1
        ? "Seleccionado: " + Array.from(state.selected.values())[0].name
        : count + " imágenes seleccionadas"
      : "Selecciona un archivo o sube uno nuevo";
    confirmBtn.disabled = count === 0;
  }

  async function fetchGrid() {
    const grid = overlay.querySelector("[data-au-picker-grid]");
    grid.innerHTML = '<div class="au-table-loading">Cargando...</div>';
    try {
      const params = new URLSearchParams({ filter: state.filter || "todas", search: state.search || "", per_page: "60" });
      const data = await AU.request(`${state.opts.dataUrl}?${params.toString()}`);
      state.rowsById = new Map((data.rows || []).map((r) => [r.row_id, r]));
      overlay.querySelector("[data-au-picker-summary]").textContent = `${data.total} archivo(s) en la galería`;
      if (data.filters && data.filters.length) renderFolderOptions(data.filters);
      renderGrid(Array.from(state.rowsById.values()));
    } catch (err) {
      grid.innerHTML = '<div class="au-table-empty">No se pudo cargar la galería.</div>';
    }
  }

  async function upload(files) {
    const formData = new FormData();
    files.forEach((f) => formData.append("image[]", f));
    formData.append("folder", state.opts.defaultFolder || "media");
    try {
      const data = await AU.request(state.opts.uploadUrl, { method: "POST", body: formData });
      AU.toast.success(data.message || "Imagen(es) subida(s)");
      await fetchGrid();
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "No se pudo subir la imagen");
    }
  }

  AU.ImagePicker = {
    open(opts) {
      const el = ensureShell();
      state = { opts, filter: opts.defaultFolder || "todas", search: "", selected: new Map(), rowsById: new Map() };
      overlay.querySelector("[data-au-picker-search]").value = "";
      overlay.querySelector("[data-au-picker-confirm]").disabled = true;
      el.classList.add("is-open");
      fetchGrid();
    },
    close,
  };
})();

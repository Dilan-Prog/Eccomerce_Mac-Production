/* Lightweight confirm/alert modal. Replaces SweetAlert2 on admin-ui pages. */
window.AU = window.AU || {};

(function () {
  function build({
    title,
    text,
    tone = "warning",
    confirmText = "Confirmar",
    cancelText = "Cancelar",
    showCancel = true,
    summaryRows = [],
    showNotify = false,
    notifyLabel = "Notificar por correo",
    notifyChecked = true,
  }) {
    const overlay = document.createElement("div");
    overlay.className = "au-modal-overlay";
    const summaryHtml = summaryRows.length
      ? `<div class="au-modal-summary">
          ${summaryRows
            .map(
              (row) => `
            <div class="au-modal-summary-row">
              <span class="au-modal-summary-label">${AU.escapeHtml(row.label)}</span>
              <span class="au-modal-summary-value${row.mono ? " au-mono" : ""}">${AU.escapeHtml(row.value)}</span>
            </div>`
            )
            .join("")}
        </div>`
      : "";
    const notifyHtml = showNotify
      ? `<label class="au-modal-notify">
          <input type="checkbox" class="au-checkbox" data-au-modal-notify ${notifyChecked ? "checked" : ""}>
          <span>${AU.escapeHtml(notifyLabel)}</span>
        </label>`
      : "";
    overlay.innerHTML = `
      <div class="au-modal" role="dialog" aria-modal="true">
        <div class="au-modal-head">
          <div class="au-modal-icon is-${tone}">${tone === "critical" ? "&#9888;" : tone === "success" ? "&#10003;" : "?"}</div>
          <div style="display:flex;flex-direction:column;gap:6px">
            <div class="au-modal-title"></div>
            <div class="au-modal-text"></div>
          </div>
        </div>
        ${summaryHtml}
        ${notifyHtml}
        <div class="au-modal-actions">
          ${showCancel ? `<button type="button" class="au-btn au-modal-cancel">${AU.escapeHtml(cancelText)}</button>` : ""}
          <button type="button" class="au-btn au-btn-primary ${tone === "critical" ? "au-btn-critical" : ""} au-modal-confirm">${AU.escapeHtml(confirmText)}</button>
        </div>
      </div>`;
    overlay.querySelector(".au-modal-title").textContent = title || "";
    overlay.querySelector(".au-modal-text").textContent = text || "";
    // Every au-modal-* rule is scoped under .admin-ui-v2 — appending to
    // document.body directly would leave the overlay completely unstyled.
    (document.querySelector(".admin-ui-v2") || document.body).appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add("is-open"));
    return overlay;
  }

  function destroy(overlay) {
    overlay.classList.remove("is-open");
    setTimeout(() => overlay.remove(), 150);
  }

  /**
   * Returns a Promise<{confirmed: boolean, notify: boolean|undefined}>.
   * `notify` is only meaningful when `opts.showNotify` is used (see modal.js
   * header); otherwise it's undefined and callers can ignore it.
   */
  AU.confirm = (opts) =>
    new Promise((resolve) => {
      const overlay = build(opts);
      const cancelBtn = overlay.querySelector(".au-modal-cancel");
      const confirmBtn = overlay.querySelector(".au-modal-confirm");
      const notifyBox = overlay.querySelector("[data-au-modal-notify]");

      const finish = (confirmed) => {
        const notify = notifyBox ? notifyBox.checked : undefined;
        destroy(overlay);
        resolve({ confirmed, notify });
      };

      if (cancelBtn) cancelBtn.addEventListener("click", () => finish(false));
      confirmBtn.addEventListener("click", () => finish(true));
      overlay.addEventListener("click", (e) => {
        if (e.target === overlay) finish(false);
      });
    });

  AU.alert = (opts) =>
    new Promise((resolve) => {
      const overlay = build(Object.assign({ showCancel: false, confirmText: "Aceptar" }, opts));
      const confirmBtn = overlay.querySelector(".au-modal-confirm");
      confirmBtn.addEventListener("click", () => {
        destroy(overlay);
        resolve(true);
      });
    });
})();

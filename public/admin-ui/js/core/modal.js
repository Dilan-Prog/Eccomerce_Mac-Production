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

  /**
   * Modal para mostrar un valor sensible (ej. el secreto de un token API)
   * exactamente una vez, con un campo de solo lectura + botón "Copiar" —
   * reemplaza window.prompt() para dar una mejor experiencia. El botón usa
   * el mismo atributo data-au-copy que ya maneja admin.js en cualquier
   * otro lugar del panel, así que no duplica lógica de portapapeles.
   */
  AU.revealSecret = ({ title, text, value }) =>
    new Promise((resolve) => {
      const overlay = document.createElement("div");
      overlay.className = "au-modal-overlay";
      overlay.innerHTML = `
        <div class="au-modal" role="dialog" aria-modal="true">
          <div class="au-modal-head">
            <div class="au-modal-icon is-success">&#10003;</div>
            <div style="display:flex;flex-direction:column;gap:6px">
              <div class="au-modal-title"></div>
              <div class="au-modal-text"></div>
            </div>
          </div>
          <div class="au-field" style="margin:2px 0 4px">
            <div style="display:flex;gap:8px">
              <input type="text" class="au-input au-mono" readonly style="flex:1">
              <button type="button" class="au-btn au-btn-primary" data-au-copy="${AU.escapeHtml(value || "")}">Copiar</button>
            </div>
          </div>
          <div class="au-modal-actions">
            <button type="button" class="au-btn au-btn-primary au-modal-confirm">Cerrar</button>
          </div>
        </div>`;
      overlay.querySelector(".au-modal-title").textContent = title || "";
      overlay.querySelector(".au-modal-text").textContent = text || "";
      const input = overlay.querySelector("input");
      input.value = value || "";
      input.addEventListener("click", () => input.select());

      (document.querySelector(".admin-ui-v2") || document.body).appendChild(overlay);
      requestAnimationFrame(() => overlay.classList.add("is-open"));

      const finish = () => {
        destroy(overlay);
        resolve(true);
      };
      overlay.querySelector(".au-modal-confirm").addEventListener("click", finish);
      overlay.addEventListener("click", (e) => {
        if (e.target === overlay) finish();
      });
    });

  /**
   * Modal para capturar/editar el "tiempo de entrega" de una partida de
   * cotización. Primero se elige Inmediato/Personalizado con un select; solo
   * "Personalizado" revela los campos de número + unidad (días/semanas/
   * meses/años). Usado tanto para partidas pendientes de surtir como para
   * cualquier otra partida (con stock completo o personalizada) — ver
   * public/admin-ui/js/cotizaciones/builder.js.
   *
   * opts.allowInmediato (default true): en false oculta el select de modo y
   * va directo a los campos de número/unidad — usado cuando storeItem()
   * exige un tiempo de entrega para la parte de una partida que quedó
   * pendiente de surtir (ahí "Inmediato" no aplica: por definición esa
   * cantidad no está disponible ahora). opts.text: línea informativa opcional
   * bajo el título (ej. cuántas piezas hay disponibles vs. pendientes).
   *
   * Devuelve "Inmediato", la cadena compuesta (ej. "5 días"), o undefined si
   * se cancela.
   */
  AU.promptTiempoEntrega = ({ title, text, currentValue, allowInmediato = true }) =>
    new Promise((resolve) => {
      const match = (currentValue || "").match(/^(\d+)\s*(d[ií]as?|semanas?|mes(?:es)?|a[ñn]os?)/i);
      const unitMap = { d: "dias", s: "semanas", m: "meses", a: "anios" };
      const prefillNumber = match ? match[1] : "";
      const prefillUnit = match ? unitMap[match[2][0].toLowerCase()] : "dias";
      // Sin match numérico: si ya había un valor que no es "Inmediato" (o no
      // aplica Inmediato), arranca directo en modo personalizado.
      const initialMode = allowInmediato && (!currentValue || currentValue === "Inmediato") ? "inmediato" : "personalizado";

      const overlay = document.createElement("div");
      overlay.className = "au-modal-overlay";
      overlay.innerHTML = `
        <div class="au-modal" role="dialog" aria-modal="true">
          <div class="au-modal-head">
            <div class="au-modal-icon">&#128337;</div>
            <div style="display:flex;flex-direction:column;gap:6px">
              <div class="au-modal-title"></div>
              <div class="au-modal-text"></div>
            </div>
          </div>
          ${
            allowInmediato
              ? `<div class="au-field" style="margin-bottom:${initialMode === "personalizado" ? "10px" : "2px"}">
                  <label class="au-label">Tiempo de entrega</label>
                  <select class="au-select" data-eta-mode>
                    <option value="inmediato">Inmediato</option>
                    <option value="personalizado">Personalizado</option>
                  </select>
                </div>`
              : ""
          }
          <div class="au-field" data-eta-custom-wrap style="display:${initialMode === "personalizado" ? "block" : "none"}">
            ${allowInmediato ? "" : '<label class="au-label">Tiempo de entrega</label>'}
            <div style="display:flex;gap:8px">
              <input type="number" min="1" step="1" class="au-input" style="flex:1" data-eta-number>
              <select class="au-select" style="flex:1" data-eta-unit>
                <option value="dias">Días</option>
                <option value="semanas">Semanas</option>
                <option value="meses">Meses</option>
                <option value="anios">Años</option>
              </select>
            </div>
          </div>
          <div class="au-modal-actions">
            <button type="button" class="au-btn au-modal-cancel">Cancelar</button>
            <button type="button" class="au-btn au-btn-primary au-modal-confirm">Guardar</button>
          </div>
        </div>`;
      overlay.querySelector(".au-modal-title").textContent = title || "Tiempo de entrega";
      overlay.querySelector(".au-modal-text").textContent = text || "";
      overlay.querySelector("[data-eta-number]").value = prefillNumber;
      overlay.querySelector("[data-eta-unit]").value = prefillUnit;

      const modeSelect = overlay.querySelector("[data-eta-mode]");
      const customWrap = overlay.querySelector("[data-eta-custom-wrap]");
      if (modeSelect) {
        modeSelect.value = initialMode;
        modeSelect.addEventListener("change", () => {
          customWrap.style.display = modeSelect.value === "personalizado" ? "block" : "none";
        });
      }

      (document.querySelector(".admin-ui-v2") || document.body).appendChild(overlay);
      requestAnimationFrame(() => overlay.classList.add("is-open"));

      const finish = (value) => {
        destroy(overlay);
        resolve(value);
      };

      overlay.querySelector(".au-modal-cancel").addEventListener("click", () => finish(undefined));
      overlay.querySelector(".au-modal-confirm").addEventListener("click", () => {
        if (modeSelect && modeSelect.value === "inmediato") {
          finish("Inmediato");
          return;
        }
        const num = parseInt(overlay.querySelector("[data-eta-number]").value, 10);
        if (!num || num < 1) {
          AU.toast.error("Captura un número válido.");
          return;
        }
        const unit = overlay.querySelector("[data-eta-unit]").value;
        const labels = {
          dias: num === 1 ? "día" : "días",
          semanas: num === 1 ? "semana" : "semanas",
          meses: num === 1 ? "mes" : "meses",
          anios: num === 1 ? "año" : "años",
        };
        finish(`${num} ${labels[unit] || labels.dias}`);
      });
      overlay.addEventListener("click", (e) => {
        if (e.target === overlay) finish(undefined);
      });
    });

  /**
   * Modal simple para capturar/editar un monto en dinero (ej. el precio
   * unitario de una partida manual de cotización — ver
   * public/admin-ui/js/cotizaciones/builder.js::editPrecio()). Devuelve el
   * número (float) ya capturado, o undefined si se cancela.
   */
  AU.promptMonto = ({ title, currentValue }) =>
    new Promise((resolve) => {
      const overlay = document.createElement("div");
      overlay.className = "au-modal-overlay";
      overlay.innerHTML = `
        <div class="au-modal" role="dialog" aria-modal="true">
          <div class="au-modal-head">
            <div class="au-modal-icon">&#128176;</div>
            <div style="display:flex;flex-direction:column;gap:6px">
              <div class="au-modal-title"></div>
            </div>
          </div>
          <div class="au-field">
            <label class="au-label">Monto</label>
            <input type="number" min="0" step="0.01" class="au-input" data-monto-value>
          </div>
          <div class="au-modal-actions">
            <button type="button" class="au-btn au-modal-cancel">Cancelar</button>
            <button type="button" class="au-btn au-btn-primary au-modal-confirm">Guardar</button>
          </div>
        </div>`;
      overlay.querySelector(".au-modal-title").textContent = title || "Monto";
      overlay.querySelector("[data-monto-value]").value = currentValue != null ? currentValue : "";

      (document.querySelector(".admin-ui-v2") || document.body).appendChild(overlay);
      requestAnimationFrame(() => overlay.classList.add("is-open"));

      const finish = (value) => {
        destroy(overlay);
        resolve(value);
      };

      overlay.querySelector(".au-modal-cancel").addEventListener("click", () => finish(undefined));
      overlay.querySelector(".au-modal-confirm").addEventListener("click", () => {
        const value = parseFloat(overlay.querySelector("[data-monto-value]").value);
        if (isNaN(value) || value < 0) {
          AU.toast.error("Captura un monto válido.");
          return;
        }
        finish(value);
      });
      overlay.addEventListener("click", (e) => {
        if (e.target === overlay) finish(undefined);
      });
    });
})();

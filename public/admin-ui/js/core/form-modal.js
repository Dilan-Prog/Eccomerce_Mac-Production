/*
 * Reusable Crear/Editar modal engine, matching the "Modal Producto"/"Modal
 * Orden" reference components: large 2-column overlay, header (icon+title+
 * subtitle+close), body (loaded from a server-rendered fragment), footer
 * (footnote + Cancelar/Guardar).
 *
 * A module only supplies: a fragment URL (GET, returns the bare <form> HTML
 * for create or edit — no page layout) and a submit URL/method. The engine
 * handles opening/closing, loading, submitting via fetch (FormData, so file
 * inputs work), inline 422 validation errors, and success toast + callback
 * (typically refetching the module's AdminTable).
 *
 * Usage:
 *   AU.FormModal.open({
 *     title: 'Crear categoría', subtitle: 'Alta manual',
 *     icon: 'fas fa-tag',
 *     fragmentUrl: '/admin/category/create-fragment',
 *     submitUrl: '/admin/category', method: 'POST',
 *     onSuccess: () => table.fetchData(),
 *   });
 */
window.AU = window.AU || {};

(function () {
  let overlay = null;

  function ensureShell() {
    if (overlay) return overlay;
    overlay = document.createElement("div");
    overlay.className = "au-form-modal-overlay";
    overlay.innerHTML = `
      <div class="au-form-modal" role="dialog" aria-modal="true">
        <div class="au-form-modal-head">
          <div class="au-form-modal-icon"><i></i></div>
          <div class="au-form-modal-titles">
            <div class="au-form-modal-title"></div>
            <div class="au-form-modal-subtitle"></div>
          </div>
          <button type="button" class="au-btn au-btn-plain au-btn-icon" data-au-form-modal-close aria-label="Cerrar">&times;</button>
        </div>
        <div class="au-form-modal-body"></div>
        <div class="au-form-modal-foot">
          <span class="au-form-modal-footnote"></span>
          <button type="button" class="au-btn" data-au-form-modal-cancel>Cancelar</button>
          <button type="button" class="au-btn au-btn-primary" data-au-form-modal-save>Guardar</button>
        </div>
      </div>`;
    // Every au-form-modal-* rule is scoped under .admin-ui-v2 — appending to
    // document.body directly would leave the overlay completely unstyled.
    (document.querySelector(".admin-ui-v2") || document.body).appendChild(overlay);

    overlay.querySelector("[data-au-form-modal-close]").addEventListener("click", close);
    overlay.querySelector("[data-au-form-modal-cancel]").addEventListener("click", close);
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) close();
    });
    return overlay;
  }

  function close() {
    if (!overlay) return;
    overlay.classList.remove("is-open");
  }

  function clearFieldErrors(form) {
    AU.qsa(".au-field.has-error", form).forEach((f) => f.classList.remove("has-error"));
    AU.qsa(".au-field-error", form).forEach((e) => e.remove());
  }

  function showFieldErrors(form, errors) {
    Object.keys(errors).forEach((key) => {
      const input = form.querySelector(`[name="${key}"], [name="${key}[]"]`);
      const field = input ? input.closest(".au-field") : null;
      if (!field) return;
      field.classList.add("has-error");
      const msg = document.createElement("span");
      msg.className = "au-field-error";
      msg.textContent = errors[key][0];
      field.appendChild(msg);
    });
  }

  /**
   * opts: { title, subtitle, icon, footnote, narrow, sidebar, fragmentUrl,
   *         submitUrl, method ('POST'|'PUT'), onSuccess }
   *
   * `sidebar: true` switches .au-form-modal-body to a 1fr/232px grid — the
   * fragment itself still supplies both columns (the engine doesn't inject
   * any image-picker markup), typically a plain field stack plus a sibling
   * <div class="au-form-sidebar">...</div> built with AU.ImagePicker.
   */
  AU.FormModal = {
    open(opts) {
      const el = ensureShell();
      const modal = el.querySelector(".au-form-modal");
      modal.classList.toggle("is-narrow", !!opts.narrow);
      modal.classList.toggle("has-sidebar", !!opts.sidebar);

      el.querySelector(".au-form-modal-icon i").className = opts.icon || "fas fa-pen";
      el.querySelector(".au-form-modal-title").textContent = opts.title || "";
      el.querySelector(".au-form-modal-subtitle").textContent = opts.subtitle || "";
      el.querySelector(".au-form-modal-footnote").textContent = opts.footnote || "";

      const body = el.querySelector(".au-form-modal-body");
      body.className = "au-form-modal-body is-loading";
      body.textContent = "Cargando...";

      el.classList.add("is-open");

      fetch(opts.fragmentUrl, {
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "text/html" },
      })
        .then((res) => res.text())
        .then((html) => {
          body.className = "au-form-modal-body";
          body.innerHTML = html;
        })
        .catch(() => {
          body.className = "au-form-modal-body";
          body.innerHTML = '<div class="au-table-empty">No se pudo cargar el formulario.</div>';
        });

      const saveBtn = el.querySelector("[data-au-form-modal-save]");
      const newSaveBtn = saveBtn.cloneNode(true);
      saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);

      newSaveBtn.addEventListener("click", async () => {
        const form = body.querySelector("form");
        if (!form) return;
        clearFieldErrors(form);

        const formData = new FormData(form);
        if (opts.method === "PUT") formData.set("_method", "PUT");

        newSaveBtn.disabled = true;
        const originalLabel = newSaveBtn.textContent;
        newSaveBtn.textContent = "Guardando...";

        try {
          const data = await AU.request(opts.submitUrl, { method: "POST", body: formData });
          AU.toast.success(data.message || "Guardado exitosamente");
          close();
          if (opts.onSuccess) opts.onSuccess(data);
        } catch (err) {
          if (err.status === 422 && err.data && err.data.errors) {
            showFieldErrors(form, err.data.errors);
            AU.toast.error("Revisa los campos marcados en rojo");
          } else {
            AU.toast.error((err.data && err.data.message) || "Ocurrió un error al guardar");
          }
        } finally {
          newSaveBtn.disabled = false;
          newSaveBtn.textContent = originalLabel;
        }
      });
    },

    close,
  };
})();

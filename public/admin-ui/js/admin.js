/*
 * admin-ui entry point.
 * Reproduces the legacy global `.delete-item` confirm -> DELETE -> reload flow
 * (see resources/views/admin/layouts/master.blade.php) using the custom
 * modal/toast components instead of SweetAlert2/Toastr, so existing delete
 * endpoints (which return {status, message}) keep working unchanged.
 */
(function () {
  document.addEventListener("click", async (e) => {
    const trigger = e.target.closest(".delete-item");
    if (!trigger) return;
    e.preventDefault();

    const url = trigger.getAttribute("href") || trigger.getAttribute("data-url");
    if (!url) return;

    let summaryRows = [];
    const rawSummary = trigger.getAttribute("data-au-summary");
    if (rawSummary) {
      try {
        summaryRows = JSON.parse(rawSummary) || [];
      } catch (err) {
        summaryRows = [];
      }
    }

    const { confirmed } = await AU.confirm({
      title: "¿Estás seguro?",
      text: "Esta acción no se puede deshacer.",
      tone: "critical",
      confirmText: "Sí, borrar",
      cancelText: "Cancelar",
      summaryRows,
    });
    if (!confirmed) return;

    try {
      const data = await AU.request(url, { method: "DELETE" });
      if (data.status === "success") {
        AU.toast.success(data.message || "Borrado exitosamente");
        setTimeout(() => window.location.reload(), 600);
      } else {
        AU.toast.error(data.message || "No se pudo borrar");
      }
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "Ocurrió un error al borrar");
    }
  });

  /*
   * Generic non-destructive row action trigger, e.g. "apply watermark" or
   * any other POST-style action that doesn't warrant a confirm() step
   * (unlike .delete-item above). Reusable by any admin table screen.
   */
  document.addEventListener("click", async (e) => {
    const trigger = e.target.closest(".au-action-item");
    if (!trigger) return;
    e.preventDefault();

    const method = trigger.getAttribute("data-method") || "POST";
    const href = trigger.getAttribute("href");
    if (!href) return;

    try {
      const data = await AU.request(href, { method });
      if (data.status === "success") {
        AU.toast.success(data.message || "Listo");
        setTimeout(() => window.location.reload(), 600);
      } else {
        AU.toast.error(data.message || "No se pudo completar la acción");
      }
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "Ocurrió un error");
    }
  });

  document.addEventListener("click", async (e) => {
    const trigger = e.target.closest("[data-au-copy]");
    if (!trigger) return;
    const value = trigger.getAttribute("data-au-copy");
    if (!value) return;
    try {
      await navigator.clipboard.writeText(value);
      AU.toast.success("URL copiada");
    } catch (err) {
      AU.toast.error("No se pudo copiar la URL");
    }
  });

  /*
   * Generic PDF-preview trigger (AU.PdfPreview, core/pdf-preview.js) — any
   * "Ver PDF" link anywhere in the admin panel opts in with 3 data-*
   * attributes instead of writing its own modal wiring:
   *   data-au-pdf-preview                marks the trigger
   *   data-title="..."                   modal header text
   *   data-url="..."                     PDF-serving URL (iframe src + download href)
   *   data-download-name="..."           filename for the download button
   */
  document.addEventListener("click", (e) => {
    const trigger = e.target.closest("[data-au-pdf-preview]");
    if (!trigger) return;
    e.preventDefault();
    AU.PdfPreview.open({
      title: trigger.getAttribute("data-title") || "Vista previa",
      url: trigger.getAttribute("data-url"),
      downloadName: trigger.getAttribute("data-download-name") || "documento.pdf",
    });
  });

  /*
   * Generic image-slot -> gallery-picker wiring, shared by every module's
   * form fragment (brand/slider/products/products-image-gallery). A slot
   * only needs 3 data-* attributes — no per-module JS required:
   *   data-au-image-slot                       marks the clickable slot
   *   data-au-image-slot-folder="logo"          upload destination folder
   *   data-au-image-slot-target="[name=logo_from_library]"  selector for the
   *                                             hidden input that receives
   *                                             the picked file's relative path
   */
  document.addEventListener("click", (e) => {
    const slot = e.target.closest("[data-au-image-slot]");
    if (!slot) return;
    e.preventDefault();
    if (!window.AU_MEDIA_LIBRARY) return;

    const targetSelector = slot.getAttribute("data-au-image-slot-target");
    const targetInput = targetSelector ? document.querySelector(targetSelector) : null;
    if (!targetInput) return;

    AU.ImagePicker.open({
      dataUrl: window.AU_MEDIA_LIBRARY.dataUrl,
      uploadUrl: window.AU_MEDIA_LIBRARY.uploadUrl,
      defaultFolder: slot.getAttribute("data-au-image-slot-folder") || "media",
      onSelect: (file) => {
        targetInput.value = file.path;
        slot.classList.add("has-image");
        slot.innerHTML = '<span class="au-image-slot-preview"></span>';
        slot.querySelector(".au-image-slot-preview").style.backgroundImage = `url('${file.url}')`;
      },
    });
  });

  document.addEventListener("click", (e) => {
    const trigger = e.target.closest("[data-au-open-modal]");
    if (!trigger) return;
    e.preventDefault();
    try {
      const config = JSON.parse(trigger.getAttribute("data-au-open-modal"));
      AU.FormModal.open(Object.assign({}, config, { onSuccess: () => window.location.reload() }));
    } catch (err) {
      AU.toast.error("No se pudo abrir el formulario");
    }
  });

  document.addEventListener("DOMContentLoaded", () => {
    if (window.__auFlash) {
      (window.__auFlash.errors || []).forEach((msg) => AU.toast.error(msg));
      if (window.__auFlash.success) AU.toast.success(window.__auFlash.success);
    }
  });
})();

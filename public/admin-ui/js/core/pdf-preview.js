/*
 * Reusable PDF preview modal: an <iframe> pointed at a PDF-serving URL, plus
 * a "Descargar" button (same URL, `download` attribute forces the save —
 * no separate attachment-disposition endpoint needed) and a close button.
 * Mirrors the shell-built-once/reused pattern in form-modal.js, and the
 * overlay+iframe+download-button layout already proven in the customer-
 * facing resources/views/cotizaciones/generada.blade.php.
 *
 * Usage:
 *   AU.PdfPreview.open({
 *     title: 'Cotización COT-2026-00017',
 *     url: '/admin/cotizaciones/17/pdf',
 *     downloadName: 'COT-2026-00017.pdf',
 *   });
 */
window.AU = window.AU || {};

(function () {
  let overlay = null;

  function ensureShell() {
    if (overlay) return overlay;
    overlay = document.createElement("div");
    overlay.className = "au-pdf-preview-overlay";
    overlay.innerHTML = `
      <div class="au-pdf-preview" role="dialog" aria-modal="true">
        <div class="au-pdf-preview-head">
          <div class="au-pdf-preview-title"></div>
          <button type="button" class="au-btn au-btn-plain au-btn-icon" data-au-pdf-preview-close aria-label="Cerrar">&times;</button>
        </div>
        <div class="au-pdf-preview-body">
          <iframe></iframe>
        </div>
        <div class="au-pdf-preview-foot">
          <a class="au-btn" data-au-pdf-preview-close>Cerrar</a>
          <a class="au-btn au-btn-primary" data-au-pdf-preview-download><i class="fas fa-download"></i> Descargar PDF</a>
        </div>
      </div>`;
    // Every au-pdf-preview-* rule is scoped under .admin-ui-v2 — appending to
    // document.body directly would leave the overlay completely unstyled.
    (document.querySelector(".admin-ui-v2") || document.body).appendChild(overlay);

    AU.qsa("[data-au-pdf-preview-close]", overlay).forEach((el) => el.addEventListener("click", close));
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) close();
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && overlay.classList.contains("is-open")) close();
    });

    return overlay;
  }

  function close() {
    if (!overlay) return;
    overlay.classList.remove("is-open");
    overlay.querySelector("iframe").src = "about:blank";
  }

  /**
   * opts: { title, url, downloadName }
   */
  AU.PdfPreview = {
    open(opts) {
      const el = ensureShell();
      el.querySelector(".au-pdf-preview-title").textContent = opts.title || "Vista previa";
      el.querySelector("iframe").src = opts.url;

      const downloadLink = el.querySelector("[data-au-pdf-preview-download]");
      downloadLink.href = opts.url;
      downloadLink.setAttribute("download", opts.downloadName || "documento.pdf");

      el.classList.add("is-open");
    },
    close,
  };
})();

/*
 * Reusable read-only "detail" modal: fetches a bare HTML fragment (same
 * contract as FormModal's fragmentUrl — server returns display-only markup,
 * no page layout) and injects it via innerHTML into an overlay. No form, no
 * Save button — just a header (title + close) and a Close button, mirroring
 * the simpler shell built for pdf-preview.js but with raw injected HTML
 * instead of an <iframe>, since the backend returns HTML, not a PDF.
 *
 * Note: fragments injected via innerHTML do NOT execute embedded <script>
 * tags — fine here since the fragment is pure display markup.
 *
 * Usage:
 *   AU.DetailModal.open({
 *     title: 'Detalle de producto',
 *     fragmentUrl: '/admin/products/17/details-fragment',
 *   });
 */
window.AU = window.AU || {};

(function () {
  let overlay = null;

  function ensureShell() {
    if (overlay) return overlay;
    overlay = document.createElement("div");
    overlay.className = "au-detail-modal-overlay";
    overlay.innerHTML = `
      <div class="au-detail-modal" role="dialog" aria-modal="true">
        <div class="au-detail-modal-head">
          <div class="au-detail-modal-title"></div>
          <button type="button" class="au-btn au-btn-plain au-btn-icon" data-au-detail-modal-close aria-label="Cerrar">&times;</button>
        </div>
        <div class="au-detail-modal-body"></div>
        <div class="au-detail-modal-foot">
          <button type="button" class="au-btn" data-au-detail-modal-close>Cerrar</button>
        </div>
      </div>`;
    // Every au-detail-modal-* rule is scoped under .admin-ui-v2 — appending to
    // document.body directly would leave the overlay completely unstyled.
    (document.querySelector(".admin-ui-v2") || document.body).appendChild(overlay);

    AU.qsa("[data-au-detail-modal-close]", overlay).forEach((el) => el.addEventListener("click", close));
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
  }

  /**
   * opts: { title, fragmentUrl }
   */
  AU.DetailModal = {
    open(opts) {
      const el = ensureShell();
      el.querySelector(".au-detail-modal-title").textContent = opts.title || "Detalle";

      const body = el.querySelector(".au-detail-modal-body");
      body.className = "au-detail-modal-body is-loading";
      body.textContent = "Cargando...";

      el.classList.add("is-open");

      fetch(opts.fragmentUrl, {
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "text/html" },
      })
        .then((res) => res.text())
        .then((html) => {
          body.className = "au-detail-modal-body";
          body.innerHTML = html;
        })
        .catch(() => {
          body.className = "au-detail-modal-body";
          body.innerHTML = '<div class="au-table-empty">No se pudo cargar el detalle.</div>';
        });
    },

    close,
  };
})();

/*
 * Live SKU search against Aspel SAE (aspel_products), for the product
 * Crear/Editar modal (admin-ui.products._form). Reads its config from
 * window.AU_PRODUCT_SKU_SEARCH (set inline by products/index.blade.php
 * right before this script tag):
 *   { routes: { searchSku, aspelPrices } }
 *
 * The modal body is injected via innerHTML by core/form-modal.js, so a
 * listener bound directly to the sku input at page-load time would never
 * see it — AU.on's delegated listener on document works regardless of when
 * the field is inserted, same idea as admin.js's .delete-item handler.
 */
window.AU = window.AU || {};

(function () {
  const config = window.AU_PRODUCT_SKU_SEARCH;
  if (!config) return;
  const routes = config.routes;

  function resultsContainer(skuInput) {
    const field = skuInput.closest(".au-field");
    return field ? field.querySelector("[data-sku-results]") : null;
  }

  function renderResults(skuInput, results) {
    const container = resultsContainer(skuInput);
    if (!container) return;

    if (!results.length) {
      container.innerHTML = '<div class="au-sku-search-status">No encontrado en Aspel.</div>';
      return;
    }

    container.innerHTML = results
      .map(
        (r) => `
      <button type="button" class="au-sku-search-result" data-au-sku-pick="${AU.escapeHtml(r.cve_art)}" data-exist="${Number(r.exist) || 0}">
        <span class="au-sku-search-result-code">${AU.escapeHtml(r.cve_art)}</span>
        <span class="au-sku-search-result-desc">${AU.escapeHtml(r.descr || "")}</span>
        <span class="au-sku-search-result-stock">Existencia: ${Number(r.exist) || 0}</span>
        ${
          r.exists_in_products
            ? '<span class="au-badge au-badge-warning"><span class="au-badge-dot"></span>Ya existe en Productos</span>'
            : '<span class="au-badge au-badge-success"><span class="au-badge-dot"></span>Disponible</span>'
        }
      </button>`
      )
      .join("");

    AU.qsa("[data-au-sku-pick]", container).forEach((btn) => {
      btn.addEventListener("click", () => {
        pickSku(skuInput, btn.getAttribute("data-au-sku-pick"), Number(btn.getAttribute("data-exist")) || 0);
      });
    });
  }

  async function search(skuInput, term) {
    const container = resultsContainer(skuInput);
    if (!container) return;
    if (!term) {
      container.innerHTML = "";
      return;
    }
    try {
      const results = await AU.request(`${routes.searchSku}?sku=${encodeURIComponent(term)}`);
      renderResults(skuInput, Array.isArray(results) ? results : []);
    } catch (err) {
      container.innerHTML = '<div class="au-sku-search-status">No se pudo buscar en Aspel.</div>';
    }
  }

  /*
   * A field that never had Aspel price options at render time (create mode,
   * SKU unknown) is a plain <input>, not a <select> — swap it in place so we
   * can offer the tier dropdown once we know the SKU. Already-a-select stays
   * a select, its options just get rebuilt for the newly picked SKU.
   */
  function ensureSelect(input) {
    if (input.tagName === "SELECT") return input;
    const select = document.createElement("select");
    select.name = input.name;
    select.className = "au-select";
    select.disabled = input.disabled;
    input.replaceWith(select);
    return select;
  }

  function buildPriceOptions(select, prices) {
    const options = ['<option value="">Seleccionar...</option>'].concat(
      prices.map((p) => {
        const amount = typeof p.priceWithIva === "number" ? p.priceWithIva.toFixed(2) : p.priceWithIva;
        return `<option value="${p.cve_precio}">${AU.escapeHtml(p.desc)} — $${amount} MXN (IVA incl.)</option>`;
      })
    );
    select.innerHTML = options.join("");
  }

  async function pickSku(skuInput, cveArt, exist) {
    const form = skuInput.closest("form");
    if (!form) return;

    skuInput.value = cveArt;
    const container = resultsContainer(skuInput);
    if (container) container.innerHTML = "";
    if (form.qty_aspel) form.qty_aspel.value = exist;

    try {
      const data = await AU.request(`${routes.aspelPrices}?sku=${encodeURIComponent(cveArt)}`);
      const prices = data.prices || [];

      if (form.aspel_price) buildPriceOptions(ensureSelect(form.aspel_price), prices);
      if (form.aspel_offert_price) buildPriceOptions(ensureSelect(form.aspel_offert_price), prices);
    } catch (err) {
      AU.toast.error("No se pudieron cargar los precios de Aspel para este SKU");
    }
  }

  AU.on(
    document,
    "input",
    '[name="sku"]',
    AU.debounce((e) => search(e.target, e.target.value.trim()), 300)
  );
})();

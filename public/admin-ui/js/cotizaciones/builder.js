/*
 * ERP-style admin quote builder (admin-ui.cotizaciones.create/edit).
 *
 * Reads its config from window.AU_COTIZACION_BUILDER (set inline by
 * _builder.blade.php right before this script tag):
 *   {
 *     cotizacionId, items: [...], total,
 *     routes: { clientsSearch, productsSearch, store, editUrlBase,
 *               itemsStore, itemBase, finalize, showUrlBase,
 *               customerCreateFragment, customerStore }
 *   }
 *
 * On the create page (cotizacionId === null) only the client picker is wired
 * up: picking/creating a client immediately POSTs to `store` and redirects
 * to the edit page for the new draft — there is no in-page "add products"
 * state on create.blade.php, so those DOM nodes simply don't exist there and
 * every AU.qs() lookup below no-ops safely.
 */
window.AU = window.AU || {};

(function () {
  const config = window.AU_COTIZACION_BUILDER;
  if (!config) return;

  let items = Array.isArray(config.items) ? config.items.slice() : [];

  function formatMoney(value) {
    const num = typeof value === "number" ? value : parseFloat(value);
    if (isNaN(num)) return "$0.00";
    return num.toLocaleString("es-MX", { style: "currency", currency: "MXN" });
  }

  /* ---------------------------------------------------------------- *
   * Summary sidebar
   * ---------------------------------------------------------------- */

  function renderSummary(count, total) {
    const countEl = AU.qs("[data-au-quote-summary-count]");
    const totalEl = AU.qs("[data-au-quote-summary-total]");
    if (countEl) countEl.textContent = count === 1 ? "1 artículo" : `${count} artículos`;
    if (totalEl) totalEl.textContent = formatMoney(total);
  }

  function applyServerTotals(totals) {
    if (!totals) return;
    renderSummary(totals.item_count, totals.total);
  }

  /* ---------------------------------------------------------------- *
   * Finalize action — assigns the real folio, snapshots productos_json,
   * generates the PDF, and locks the quote. Disabled while there are no
   * line items; the button only exists on the edit page (create.blade.php
   * has no items yet, so config.routes.finalize is null there).
   * ---------------------------------------------------------------- */

  function syncFinalizeButton() {
    const btn = AU.qs("#au-quote-finalize");
    if (!btn) return;
    btn.disabled = items.length === 0;
  }

  function wireFinalizeButton() {
    const btn = AU.qs("#au-quote-finalize");
    if (!btn || !config.routes.finalize) return;

    btn.addEventListener("click", async () => {
      const { confirmed } = await AU.confirm({
        title: "¿Finalizar cotización?",
        text: "Se asignará el folio definitivo y se generará el PDF. Esta acción no se puede deshacer.",
        tone: "warning",
        confirmText: "Sí, finalizar",
        cancelText: "Cancelar",
      });
      if (!confirmed) return;

      btn.disabled = true;
      try {
        const data = await AU.request(config.routes.finalize, { method: "POST" });
        AU.toast.success(data.message || "Cotización finalizada correctamente");
        window.location.href = data.redirect || `${config.routes.showUrlBase}/${config.cotizacionId}`;
      } catch (err) {
        AU.toast.error((err.data && err.data.message) || "No se pudo finalizar la cotización");
        syncFinalizeButton();
      }
    });
  }

  /* ---------------------------------------------------------------- *
   * Line items table
   * ---------------------------------------------------------------- */

  function renderItems() {
    const tbody = AU.qs("[data-au-quote-items-body]");
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = `<tr><td colspan="7" class="au-table-empty">Aún no hay partidas. Busca un producto arriba para agregarlo.</td></tr>`;
      syncFinalizeButton();
      return;
    }

    tbody.innerHTML = items
      .map(
        (item, idx) => `
      <tr data-item-row="${item.id}">
        <td>${idx + 1}</td>
        <td>
          ${AU.escapeHtml(item.nombre)}
          ${item.marca ? `<div class="au-quote-item-sub">${AU.escapeHtml(item.marca)}</div>` : ""}
        </td>
        <td class="au-mono">${AU.escapeHtml(item.sku || "")}${item.modelo ? " / " + AU.escapeHtml(item.modelo) : ""}</td>
        <td>
          <input type="number" min="1" step="1" class="au-input au-quote-qty-input"
                 value="${item.cantidad}" data-au-quote-qty="${item.id}">
        </td>
        <td class="au-text-right au-mono">${formatMoney(item.precio_unitario)}</td>
        <td class="au-text-right au-mono" data-au-quote-item-subtotal="${item.id}">${formatMoney(item.subtotal)}</td>
        <td class="au-col-actions">
          <button type="button" class="au-btn au-btn-sm au-btn-critical" data-au-quote-remove="${item.id}" aria-label="Quitar">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>`
      )
      .join("");

    syncFinalizeButton();
  }

  async function addItem(payload) {
    if (!config.routes.itemsStore) return;
    try {
      const data = await AU.request(config.routes.itemsStore, { method: "POST", body: payload });
      items.push(data.item);
      renderItems();
      applyServerTotals(data.total);
      AU.toast.success("Producto agregado");
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "No se pudo agregar el producto");
    }
  }

  async function updateItemQty(itemId, cantidad) {
    if (!config.routes.itemBase) return;
    try {
      const data = await AU.request(`${config.routes.itemBase}/${itemId}`, { method: "PUT", body: { cantidad } });
      const idx = items.findIndex((i) => String(i.id) === String(itemId));
      if (idx !== -1) {
        items[idx].cantidad = data.item.cantidad;
        items[idx].subtotal = data.item.subtotal;
      }
      const subtotalCell = AU.qs(`[data-au-quote-item-subtotal="${itemId}"]`);
      if (subtotalCell) subtotalCell.textContent = formatMoney(data.item.subtotal);
      applyServerTotals(data.total);
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "No se pudo actualizar la cantidad");
      renderItems(); // revert any stray client-side edit back to last known-good state
    }
  }

  async function removeItem(itemId) {
    if (!config.routes.itemBase) return;
    try {
      const data = await AU.request(`${config.routes.itemBase}/${itemId}`, { method: "DELETE" });
      items = items.filter((i) => String(i.id) !== String(itemId));
      renderItems();
      applyServerTotals(data.total);
      AU.toast.success("Partida eliminada");
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "No se pudo eliminar la partida");
    }
  }

  function wireItemsTable() {
    const tbody = AU.qs("[data-au-quote-items-body]");
    if (!tbody) return;

    AU.on(
      tbody,
      "input",
      "[data-au-quote-qty]",
      AU.debounce((e, target) => {
        let qty = parseInt(target.value, 10);
        if (!qty || qty < 1) qty = 1;
        target.value = qty;
        updateItemQty(target.getAttribute("data-au-quote-qty"), qty);
      }, 400)
    );

    AU.on(tbody, "click", "[data-au-quote-remove]", (e, target) => {
      removeItem(target.getAttribute("data-au-quote-remove"));
    });
  }

  /* ---------------------------------------------------------------- *
   * Product search + add
   * ---------------------------------------------------------------- */

  function productRowHtml(p) {
    const meta = [p.sku ? `SKU: ${AU.escapeHtml(p.sku)}` : null, p.modelo ? `Modelo: ${AU.escapeHtml(p.modelo)}` : null, p.marca ? AU.escapeHtml(p.marca) : null]
      .filter(Boolean)
      .join(" · ");

    if (!p.has_variants) {
      return `
        <div class="au-quote-product-row" data-product-row="${p.id}">
          <div class="au-quote-product-info">
            <div class="au-quote-product-name">${AU.escapeHtml(p.name)}</div>
            <div class="au-quote-product-meta">${meta}${meta ? " · " : ""}Stock: ${p.stock}</div>
          </div>
          <div class="au-quote-product-price au-mono">${formatMoney(p.price)}</div>
          <div class="au-quote-product-add">
            <input type="number" min="1" step="1" value="1" class="au-input au-quote-qty-input">
            <button type="button" class="au-btn au-btn-sm au-btn-primary" data-au-quote-add-product="${p.id}">Agregar</button>
          </div>
        </div>`;
    }

    const combos = (p.combinations || [])
      .map(
        (c) => `
      <div class="au-quote-combo-row" data-combo-row="${c.id}">
        <div class="au-quote-combo-info">
          <div>${AU.escapeHtml(c.label)}</div>
          <div class="au-quote-product-meta">SKU: ${AU.escapeHtml(c.sku)} · Stock: ${c.qty}</div>
        </div>
        <div class="au-quote-product-price au-mono">${formatMoney(c.price)}</div>
        <div class="au-quote-product-add">
          <input type="number" min="1" step="1" value="1" class="au-input au-quote-qty-input">
          <button type="button" class="au-btn au-btn-sm au-btn-primary" data-au-quote-add-combo="${c.id}">Agregar</button>
        </div>
      </div>`
      )
      .join("");

    return `
      <div class="au-quote-product-row au-quote-product-row-variant" data-product-row="${p.id}">
        <div class="au-quote-product-info">
          <div class="au-quote-product-name">${AU.escapeHtml(p.name)}</div>
          <div class="au-quote-product-meta">${meta}${meta ? " · " : ""}${(p.combinations || []).length} variante(s), elige una:</div>
        </div>
      </div>
      <div class="au-quote-combo-list">${combos}</div>`;
  }

  function renderProductResults(results) {
    const container = AU.qs("[data-au-quote-product-results]");
    if (!container) return;

    if (!results.length) {
      container.innerHTML = `<div class="au-table-empty">Sin resultados.</div>`;
      return;
    }

    container.innerHTML = results.map(productRowHtml).join("");

    AU.qsa("[data-au-quote-add-product]", container).forEach((btn) => {
      btn.addEventListener("click", () => {
        const row = btn.closest(".au-quote-product-row");
        const qtyInput = row.querySelector("input[type=\"number\"]");
        const qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);
        addItem({ product_id: btn.getAttribute("data-au-quote-add-product"), cantidad: qty });
      });
    });

    AU.qsa("[data-au-quote-add-combo]", container).forEach((btn) => {
      btn.addEventListener("click", () => {
        const row = btn.closest(".au-quote-combo-row");
        const qtyInput = row.querySelector("input[type=\"number\"]");
        const qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);
        addItem({ product_variant_combination_id: btn.getAttribute("data-au-quote-add-combo"), cantidad: qty });
      });
    });
  }

  async function fetchProducts(term) {
    try {
      const data = await AU.request(`${config.routes.productsSearch}?q=${encodeURIComponent(term)}`);
      renderProductResults(data.results || []);
    } catch (err) {
      AU.toast.error("No se pudo buscar productos");
    }
  }

  function wireProductSearch() {
    const input = AU.qs("[data-au-quote-product-search]");
    if (!input) return;
    fetchProducts("");
    input.addEventListener(
      "input",
      AU.debounce((e) => fetchProducts(e.target.value), 300)
    );
  }

  /* ---------------------------------------------------------------- *
   * Client search / create / select -> creates the draft and redirects
   * ---------------------------------------------------------------- */

  function renderClientResults(results) {
    const container = AU.qs("[data-au-quote-client-results]");
    if (!container) return;

    if (!results.length) {
      container.innerHTML = `<div class="au-table-empty">Sin resultados.</div>`;
      return;
    }

    container.innerHTML = results
      .map(
        (c) => `
      <button type="button" class="au-quote-client-result" data-au-quote-client-pick="${c.id}"
              data-name="${AU.escapeHtml(c.name)}" data-email="${AU.escapeHtml(c.email)}">
        <span class="au-quote-client-result-name">${AU.escapeHtml(c.name)}</span>
        <span class="au-quote-client-result-email">${AU.escapeHtml(c.email)}</span>
        ${c.has_profile ? '<span class="au-badge au-badge-info"><span class="au-badge-dot"></span>Perfil fiscal</span>' : ""}
      </button>`
      )
      .join("");

    AU.qsa("[data-au-quote-client-pick]", container).forEach((btn) => {
      btn.addEventListener("click", () => {
        selectClient({
          id: btn.getAttribute("data-au-quote-client-pick"),
          name: btn.getAttribute("data-name"),
          email: btn.getAttribute("data-email"),
        });
      });
    });
  }

  async function fetchClients(term) {
    try {
      const data = await AU.request(`${config.routes.clientsSearch}?q=${encodeURIComponent(term)}`);
      renderClientResults(data.results || []);
    } catch (err) {
      AU.toast.error("No se pudo buscar clientes");
    }
  }

  async function selectClient(client) {
    const searchInput = AU.qs("[data-au-quote-client-search]");
    const newClientBtn = AU.qs("[data-au-quote-client-new]");
    if (searchInput) searchInput.disabled = true;
    if (newClientBtn) newClientBtn.disabled = true;

    try {
      const data = await AU.request(config.routes.store, { method: "POST", body: { user_id: client.id } });
      window.location.href = `${config.routes.editUrlBase}/${data.cotizacion.id}/edit`;
    } catch (err) {
      AU.toast.error((err.data && err.data.message) || "No se pudo crear la cotización");
      if (searchInput) searchInput.disabled = false;
      if (newClientBtn) newClientBtn.disabled = false;
    }
  }

  function wireClientPicker() {
    const searchInput = AU.qs("[data-au-quote-client-search]");
    const newClientBtn = AU.qs("[data-au-quote-client-new]");
    if (!searchInput && !newClientBtn) return; // already in "edit" mode with a client picked

    if (searchInput) {
      fetchClients("");
      searchInput.addEventListener(
        "input",
        AU.debounce((e) => fetchClients(e.target.value), 300)
      );
    }

    if (newClientBtn) {
      newClientBtn.addEventListener("click", () => {
        AU.FormModal.open({
          title: "Crear cliente",
          subtitle: "Alta manual de cliente",
          icon: "fas fa-user-plus",
          fragmentUrl: config.routes.customerCreateFragment,
          submitUrl: config.routes.customerStore,
          method: "POST",
          onSuccess: (data) => {
            if (data && data.client) selectClient(data.client);
          },
        });
      });
    }
  }

  /* ---------------------------------------------------------------- *
   * Init
   * ---------------------------------------------------------------- */

  renderItems();
  renderSummary(items.length, config.total || 0);
  wireItemsTable();
  wireProductSearch();
  wireClientPicker();
  wireFinalizeButton();
})();

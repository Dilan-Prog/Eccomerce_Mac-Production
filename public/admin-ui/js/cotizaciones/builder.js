/*
 * ERP-style admin quote builder (admin-ui.cotizaciones.create/edit).
 *
 * Reads its config from window.AU_COTIZACION_BUILDER (set inline by
 * _builder.blade.php right before this script tag):
 *   {
 *     cotizacionId, items: [...], total, currency, exchangeRate,
 *     routes: { clientsSearch, productsSearch, store, editUrlBase,
 *               itemsStore, itemBase, currency, finalize, showUrlBase,
 *               customerCreateFragment, customerStore }
 *   }
 *
 * currency/exchangeRate: every price from the server is always raw MXN
 * (cotizacion_items.precio_unitario never changes) — these two only control
 * how amounts are *displayed* here (see toDisplayAmount()/formatMoney()) and
 * in the final PDF (Cotizacion::displayAmount()). Manual, per-quote, entered
 * by the vendor — deliberately not sourced from Aspel's monedas_aspel rates.
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
  let currency = config.currency || "MXN";
  let exchangeRate = config.exchangeRate || null;
  let lastTotals = { item_count: items.length, total: config.total || 0 };

  /*
   * Every price the server sends is always raw MXN (see
   * App\Support\CotizacionPricing / Cotizacion::displayAmount() — the same
   * conversion, mirrored here so the builder doesn't need a round-trip just
   * to preview totals as the vendor changes currency/tipo de cambio).
   */
  function toDisplayAmount(mxnValue) {
    const num = typeof mxnValue === "number" ? mxnValue : parseFloat(mxnValue);
    if (isNaN(num)) return 0;
    return currency === "USD" && exchangeRate ? num / exchangeRate : num;
  }

  function formatMoney(mxnValue) {
    return toDisplayAmount(mxnValue).toLocaleString("es-MX", { style: "currency", currency });
  }

  /* ---------------------------------------------------------------- *
   * Moneda / tipo de cambio
   * ---------------------------------------------------------------- */

  function wireCurrencyControls() {
    const currencySelect = AU.qs("[data-au-quote-currency]");
    const rateField = AU.qs("[data-au-quote-exchange-rate-field]");
    const rateInput = AU.qs("[data-au-quote-exchange-rate]");
    if (!currencySelect || !config.routes.currency) return;

    async function save() {
      const payload = { currency: currencySelect.value };
      if (currencySelect.value === "USD") payload.exchange_rate = rateInput.value;

      try {
        const data = await AU.request(config.routes.currency, { method: "PUT", body: payload });
        currency = data.cotizacion.currency;
        exchangeRate = data.cotizacion.exchange_rate;
        renderItems();
        renderSummary(lastTotals.item_count, lastTotals.total);
      } catch (err) {
        AU.toast.error((err.data && err.data.message) || "No se pudo guardar la moneda/tipo de cambio");
      }
    }

    currencySelect.addEventListener("change", () => {
      if (rateField) rateField.style.display = currencySelect.value === "USD" ? "" : "none";
      if (currencySelect.value === "MXN" || (rateInput && rateInput.value)) save();
    });

    if (rateInput) {
      rateInput.addEventListener("input", AU.debounce(save, 500));
    }
  }

  /* ---------------------------------------------------------------- *
   * Summary sidebar
   * ---------------------------------------------------------------- */

  function renderSummary(count, total) {
    lastTotals = { item_count: count, total };
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
          ${item.precio_tier_label ? `<div class="au-quote-item-sub">Precio: ${AU.escapeHtml(item.precio_tier_label)}</div>` : ""}
          ${
            item.es_pendiente
              ? `<div class="au-badge au-badge-warning" style="margin-top:4px"><span class="au-badge-dot"></span>Pendiente de surtir — ${AU.escapeHtml(item.tiempo_entrega || "sin especificar")}</div>`
              : ""
          }
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
      items.push(...data.items);
      renderItems();
      applyServerTotals(data.total);
      const splitNote = data.items.some((i) => i.es_pendiente) ? " (parte quedó pendiente de surtir)" : "";
      AU.toast.success(`Producto agregado${splitNote}`);
    } catch (err) {
      // El servidor detectó que la cantidad pedida supera el stock disponible
      // y necesita que el vendedor capture manualmente el tiempo de entrega
      // de la parte pendiente (no hay fuente automática de ETA hoy) — se
      // pide con un prompt nativo y se reintenta la misma petición con ese
      // dato ya incluido.
      if (err.data && err.data.code === "needs_tiempo_entrega") {
        const nota = window.prompt(err.data.message + "\n\nTiempo de entrega para lo pendiente:");
        if (nota && nota.trim()) {
          await addItem({ ...payload, tiempo_entrega: nota.trim() });
          return;
        }
        AU.toast.error("Cancelado: se necesita el tiempo de entrega para agregar el producto.");
        return;
      }
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
      const tiers = p.price_tiers || [];
      const minimo = p.precio_minimo || 1;
      const publico = p.precio_publico || null;
      const priceControl = tiers.length
        ? `<div class="au-quote-price-controls">
            <select class="au-select au-quote-tier-select" data-tier-select>
              ${tiers.map((t) => `<option value="${t.cve_precio}">${AU.escapeHtml(t.descripcion)} — ${formatMoney(t.precio)}</option>`).join("")}
            </select>
            <input type="number" min="0" step="0.01" class="au-input au-quote-custom-price" data-custom-price
                   data-minimo="${minimo}" data-publico="${publico || ""}" placeholder="Precio personalizado (opcional)">
            <div class="au-quote-price-notice" data-price-notice></div>
          </div>`
        : `<div class="au-quote-product-price au-mono">${formatMoney(p.price)}</div>`;

      return `
        <div class="au-quote-product-row" data-product-row="${p.id}">
          <div class="au-quote-product-info">
            <div class="au-quote-product-name">${AU.escapeHtml(p.name)}</div>
            <div class="au-quote-product-meta">${meta}${meta ? " · " : ""}Stock: ${p.stock}</div>
          </div>
          ${priceControl}
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

    /*
     * Live custom-price feedback: a value below "mínimo" is a hard error
     * (blocks "Agregar", same rule storeItem() enforces server-side — never
     * trust this client check alone). A value above "público" is allowed,
     * just shown as an informational %/amount-over notice.
     */
    AU.qsa("[data-custom-price]", container).forEach((input) => {
      input.addEventListener("input", () => {
        const notice = input.closest(".au-quote-price-controls").querySelector("[data-price-notice]");
        const value = parseFloat(input.value);
        const minimo = parseFloat(input.getAttribute("data-minimo"));
        const publico = parseFloat(input.getAttribute("data-publico"));

        if (!input.value || isNaN(value)) {
          notice.textContent = "";
          notice.className = "au-quote-price-notice";
          return;
        }

        if (value < minimo) {
          notice.textContent = "No puedes poner un precio por debajo del precio mínimo.";
          notice.className = "au-quote-price-notice is-error";
        } else if (!isNaN(publico) && value > publico) {
          const diff = value - publico;
          const pct = (diff / publico) * 100;
          notice.textContent = `+${pct.toFixed(1)}% (${formatMoney(diff)} arriba del precio público)`;
          notice.className = "au-quote-price-notice is-info";
        } else {
          notice.textContent = "";
          notice.className = "au-quote-price-notice";
        }
      });
    });

    AU.qsa("[data-au-quote-add-product]", container).forEach((btn) => {
      btn.addEventListener("click", () => {
        const row = btn.closest(".au-quote-product-row");
        const qtyInput = row.querySelector("input[type=\"number\"]");
        const qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);
        const tierSelect = row.querySelector("[data-tier-select]");
        const customPriceInput = row.querySelector("[data-custom-price]");
        const payload = { product_id: btn.getAttribute("data-au-quote-add-product"), cantidad: qty };

        if (customPriceInput && customPriceInput.value) {
          const value = parseFloat(customPriceInput.value);
          const minimo = parseFloat(customPriceInput.getAttribute("data-minimo"));
          if (value < minimo) {
            AU.toast.error("No puedes poner un precio por debajo del precio mínimo.");
            return;
          }
          payload.precio_personalizado = value;
        } else if (tierSelect) {
          payload.precio_tier = tierSelect.value;
        }

        addItem(payload);
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
  wireCurrencyControls();
})();

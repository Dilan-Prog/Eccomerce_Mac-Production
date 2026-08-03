/*
 * Reusable admin data table. Replaces DataTables.net/jQuery: search, sort,
 * pagination, saved-filter tabs, multi-select + bulk actions, and
 * show/hide-columns — all driven by ONE JSON endpoint per module
 * (see app/Support/AdminTable/AdminTableQuery.php for the server contract).
 *
 * Usage:
 *   new AU.AdminTable({
 *     el: '#orders-table',
 *     endpoint: '{{ route("admin.order.table-data") }}',
 *     bulkEndpoint: '{{ route("admin.order.bulk") }}',   // optional
 *     bulkActions: [{ key: 'delete', label: 'Eliminar', tone: 'critical' }],
 *     rowSelectable: true,
 *   });
 */
window.AU = window.AU || {};

(function () {
  class AdminTable {
    constructor(opts) {
      this.root = typeof opts.el === "string" ? document.querySelector(opts.el) : opts.el;
      if (!this.root) return;

      this.endpoint = opts.endpoint;
      this.bulkEndpoint = opts.bulkEndpoint || null;
      this.bulkActionsConfig = opts.bulkActions || [];
      this.rowSelectable = !!opts.rowSelectable && this.bulkActionsConfig.length > 0;
      this.tableKey = opts.tableKey || opts.endpoint;
      this.exportEndpoint = opts.exportEndpoint || null;
      this.exportFormats = opts.exportFormats || [
        { key: "xlsx", label: "Excel" },
        { key: "csv", label: "CSV" },
        { key: "pdf", label: "PDF" },
      ];

      this.state = {
        page: 1,
        perPage: opts.perPage || 25,
        search: "",
        sort: null,
        dir: "asc",
        filter: opts.initialFilter || null,
      };
      this.selected = new Set();
      this.data = null;
      this.hiddenCols = AU.columnVisibility.getHidden(this.tableKey);

      this.renderShell();
      this.bindEvents();
      this.fetchData();
    }

    renderShell() {
      this.root.classList.add("au-card");
      this.root.innerHTML = `
        <div class="au-table-toolbar">
          <div class="au-table-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar..." data-au-search>
          </div>
          <div class="au-table-toolbar-right">
            ${
              this.exportEndpoint
                ? `<div class="au-dropdown" data-au-export-dropdown>
                     <button type="button" class="au-btn au-btn-sm" data-au-dropdown-trigger>
                       <i class="fas fa-download"></i> Exportar
                     </button>
                     <div class="au-dropdown-panel">
                       ${this.exportFormats
                         .map((f) => `<button type="button" class="au-dropdown-item" data-au-export="${AU.escapeHtml(f.key)}">${AU.escapeHtml(f.label)}</button>`)
                         .join("")}
                     </div>
                   </div>`
                : ""
            }
            <div class="au-dropdown" data-au-col-dropdown>
              <button type="button" class="au-btn au-btn-sm" data-au-dropdown-trigger>
                <i class="fas fa-columns"></i> Columnas
              </button>
              <div class="au-dropdown-panel" data-au-col-panel></div>
            </div>
          </div>
        </div>
        <div class="au-tabs" data-au-tabs style="display:none"></div>
        <div class="au-bulk-bar" data-au-bulk-bar></div>
        <div class="au-table-wrap">
          <table class="au-table">
            <thead data-au-thead></thead>
            <tbody data-au-tbody></tbody>
          </table>
        </div>
        <div class="au-table-footer">
          <span class="au-table-count" data-au-count></span>
          <div class="au-flex au-gap-2">
            <select class="au-page-size-select" data-au-page-size>
              <option value="25">25 / página</option>
              <option value="50">50 / página</option>
              <option value="100">100 / página</option>
            </select>
            <div class="au-pagination" data-au-pagination></div>
          </div>
        </div>
      `;
      this.root.querySelector('[data-au-page-size]').value = String(this.state.perPage);
    }

    bindEvents() {
      const searchInput = this.root.querySelector("[data-au-search]");
      searchInput.addEventListener(
        "input",
        AU.debounce((e) => {
          this.state.search = e.target.value;
          this.state.page = 1;
          this.fetchData();
        }, 350)
      );

      AU.on(this.root, "click", "[data-au-tab]", (e, el) => {
        this.state.filter = el.getAttribute("data-au-tab");
        this.state.page = 1;
        this.fetchData();
      });

      AU.on(this.root, "click", "thead th.is-sortable", (e, th) => {
        const key = th.getAttribute("data-col-key");
        if (this.state.sort === key) {
          this.state.dir = this.state.dir === "asc" ? "desc" : "asc";
        } else {
          this.state.sort = key;
          this.state.dir = "asc";
        }
        this.fetchData();
      });

      this.root.querySelector("[data-au-page-size]").addEventListener("change", (e) => {
        this.state.perPage = parseInt(e.target.value, 10);
        this.state.page = 1;
        this.fetchData();
      });

      AU.on(this.root, "click", "[data-au-page]", (e, el) => {
        const page = parseInt(el.getAttribute("data-au-page"), 10);
        if (page && page !== this.state.page) {
          this.state.page = page;
          this.fetchData();
        }
      });

      if (this.exportEndpoint) {
        AU.on(this.root, "click", "[data-au-export]", (e, el) => {
          const format = el.getAttribute("data-au-export");
          window.location.href = `${this.exportEndpoint}?${this.buildQuery()}&format=${format}`;
        });
      }

      AU.on(this.root, "change", "[data-col-toggle]", (e, el) => {
        const key = el.getAttribute("data-col-toggle");
        if (el.checked) {
          this.hiddenCols.delete(key);
        } else {
          this.hiddenCols.add(key);
        }
        AU.columnVisibility.setHidden(this.tableKey, this.hiddenCols);
        this.renderTable();
      });

      if (this.rowSelectable) {
        AU.on(this.root, "change", "[data-au-select-all]", (e, el) => {
          const ids = (this.data.rows || []).map((r) => r.row_id);
          if (el.checked) {
            ids.forEach((id) => this.selected.add(id));
          } else {
            ids.forEach((id) => this.selected.delete(id));
          }
          this.renderTable();
          this.renderBulkBar();
        });

        AU.on(this.root, "change", "[data-au-select-row]", (e, el) => {
          const id = el.getAttribute("data-au-select-row");
          if (el.checked) {
            this.selected.add(id);
          } else {
            this.selected.delete(id);
          }
          this.renderBulkBar();
          const row = el.closest("tr");
          if (row) row.classList.toggle("is-selected", el.checked);
        });

        AU.on(this.root, "click", "[data-au-clear-selection]", () => {
          this.selected.clear();
          this.renderTable();
          this.renderBulkBar();
        });

        AU.on(this.root, "click", "[data-bulk-action]", async (e, el) => {
          const action = el.getAttribute("data-bulk-action");
          const config = this.bulkActionsConfig.find((a) => a.key === action);
          if (!config || !this.bulkEndpoint) return;

          if (config.download) {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = this.bulkEndpoint;
            form.target = "_blank";
            form.style.display = "none";

            const addField = (name, value) => {
              const input = document.createElement("input");
              input.type = "hidden";
              input.name = name;
              input.value = value;
              form.appendChild(input);
            };

            addField("_token", AU.csrfToken());
            addField("action", action);
            Array.from(this.selected).forEach((id, i) => addField(`ids[${i}]`, id));

            document.body.appendChild(form);
            form.submit();
            form.remove();
            return;
          }

          if (config.tone === "critical") {
            const { confirmed } = await AU.confirm({
              title: "¿Estás seguro?",
              text: `Se aplicará "${config.label}" a ${this.selected.size} elemento(s). Esta acción no se puede deshacer.`,
              tone: "critical",
              confirmText: "Sí, continuar",
            });
            if (!confirmed) return;
          }

          try {
            const data = await AU.request(this.bulkEndpoint, {
              method: "POST",
              body: { action, ids: Array.from(this.selected) },
            });
            AU.toast[data.status === "success" ? "success" : "error"](data.message || "Listo");
            this.selected.clear();
            this.fetchData();
          } catch (err) {
            AU.toast.error((err.data && err.data.message) || "Ocurrió un error");
          }
        });
      }
    }

    buildQuery() {
      const params = new URLSearchParams();
      params.set("page", this.state.page);
      params.set("per_page", this.state.perPage);
      if (this.state.search) params.set("search", this.state.search);
      if (this.state.sort) {
        params.set("sort", this.state.sort);
        params.set("dir", this.state.dir);
      }
      if (this.state.filter) params.set("filter", this.state.filter);
      return params.toString();
    }

    async fetchData() {
      this.root.querySelector("[data-au-tbody]").innerHTML =
        '<tr><td class="au-table-loading" colspan="99">Cargando...</td></tr>';
      try {
        const data = await AU.request(`${this.endpoint}?${this.buildQuery()}`);
        this.data = data;
        this.state.filter = data.active_filter;
        this.renderTabs();
        this.renderColumnToggle();
        this.renderTable();
        this.renderFooter();
        this.renderBulkBar();
      } catch (err) {
        this.root.querySelector("[data-au-tbody]").innerHTML =
          `<tr><td class="au-table-empty" colspan="99">No se pudieron cargar los datos.</td></tr>`;
      }
    }

    renderTabs() {
      const el = this.root.querySelector("[data-au-tabs]");
      if (!this.data.filters || !this.data.filters.length) {
        el.style.display = "none";
        return;
      }
      el.style.display = "flex";
      el.innerHTML = this.data.filters
        .map(
          (f) => `
        <div class="au-tab ${f.key === this.state.filter ? "is-active" : ""}" data-au-tab="${AU.escapeHtml(f.key)}">
          ${AU.escapeHtml(f.label)} <span class="au-tab-count">${f.count}</span>
        </div>`
        )
        .join("");
    }

    renderColumnToggle() {
      this.root.querySelector("[data-au-col-panel]").innerHTML = AU.columnVisibility.renderToggleList(
        this.data.columns,
        this.hiddenCols
      );
    }

    visibleColumns() {
      return this.data.columns.filter((c) => !this.hiddenCols.has(c.key));
    }

    renderTable() {
      const cols = this.visibleColumns();
      const thead = this.root.querySelector("[data-au-thead]");
      const tbody = this.root.querySelector("[data-au-tbody]");

      const pageRows = (this.data && this.data.rows) || [];
      let headHtml = "<tr>";
      if (this.rowSelectable) {
        const allOnPageSelected = pageRows.length > 0 && pageRows.every((r) => this.selected.has(String(r.row_id)));
        headHtml += `<th class="au-col-checkbox"><input type="checkbox" class="au-checkbox" data-au-select-all ${allOnPageSelected ? "checked" : ""}></th>`;
      }
      cols.forEach((col) => {
        const isActions = col.type === "actions";
        const sortable = col.sortable && !isActions;
        const sorted = this.state.sort === col.key;
        headHtml += `<th class="${sortable ? "is-sortable" : ""} ${sorted ? "is-sorted" : ""} ${isActions ? "au-col-actions" : ""}" data-col-key="${AU.escapeHtml(col.key)}">
          ${AU.escapeHtml(col.label)}
          ${sortable ? `<i class="fas fa-sort${sorted ? (this.state.dir === "asc" ? "-up" : "-down") : ""} au-sort-icon"></i>` : ""}
        </th>`;
      });
      headHtml += "</tr>";
      thead.innerHTML = headHtml;

      const rows = pageRows;
      if (!rows.length) {
        tbody.innerHTML = `<tr><td class="au-table-empty" colspan="99">Sin resultados.</td></tr>`;
        return;
      }

      tbody.innerHTML = rows
        .map((row) => {
          let rowHtml = `<tr data-row-id="${AU.escapeHtml(row.row_id)}" class="${this.selected.has(String(row.row_id)) ? "is-selected" : ""}">`;
          if (this.rowSelectable) {
            const checked = this.selected.has(String(row.row_id)) ? "checked" : "";
            rowHtml += `<td class="au-col-checkbox"><input type="checkbox" class="au-checkbox" data-au-select-row="${AU.escapeHtml(row.row_id)}" ${checked}></td>`;
          }
          cols.forEach((col) => {
            const value = row.cells ? row.cells[col.key] : undefined;
            const renderer = AU.columnTypes[col.type] || AU.columnTypes.text;
            const cellHtml = col.type === "actions" ? renderer(value, row.row_id) : renderer(value);
            rowHtml += `<td class="${col.type === "actions" ? "au-col-actions" : ""}">${cellHtml}</td>`;
          });
          rowHtml += "</tr>";
          return rowHtml;
        })
        .join("");
    }

    renderFooter() {
      const total = this.data.total || 0;
      const perPage = this.data.per_page || this.state.perPage;
      const page = this.data.page || this.state.page;
      const totalPages = Math.max(1, Math.ceil(total / perPage));
      const from = total === 0 ? 0 : (page - 1) * perPage + 1;
      const to = Math.min(total, page * perPage);

      this.root.querySelector("[data-au-count]").textContent = `Mostrando ${from}-${to} de ${total}`;

      const pager = this.root.querySelector("[data-au-pagination]");
      let html = `<button type="button" class="au-page-btn" data-au-page="${page - 1}" ${page <= 1 ? "disabled" : ""}><i class="fas fa-chevron-left"></i></button>`;
      html += `<span class="au-text-subdued" style="font-size:12px">Página ${page} de ${totalPages}</span>`;
      html += `<button type="button" class="au-page-btn" data-au-page="${page + 1}" ${page >= totalPages ? "disabled" : ""}><i class="fas fa-chevron-right"></i></button>`;
      pager.innerHTML = html;
    }

    renderBulkBar() {
      const bar = this.root.querySelector("[data-au-bulk-bar]");
      if (!this.rowSelectable) return;
      const count = this.selected.size;
      bar.classList.toggle("is-visible", count > 0);
      bar.innerHTML = count > 0 ? AU.renderBulkBar(count, this.bulkActionsConfig) : "";
    }
  }

  AU.AdminTable = AdminTable;
})();

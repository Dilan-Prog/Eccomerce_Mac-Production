/* Per-column-type cell renderers used by admin-table.js. Each returns an HTML string. */
window.AU = window.AU || {};

const AU_AVATAR_COLORS = ["#2f7ff0", "#7a5af5", "#20a35c", "#e0a12b", "#e5484d", "#0f8f9c"];
AU.avatarColorFor = (seed) => {
  let hash = 0;
  const str = String(seed ?? "");
  for (let i = 0; i < str.length; i++) hash = (hash * 31 + str.charCodeAt(i)) | 0;
  return AU_AVATAR_COLORS[Math.abs(hash) % AU_AVATAR_COLORS.length];
};

AU.columnTypes = {
  text(value) {
    return `<span>${AU.escapeHtml(value ?? "")}</span>`;
  },

  mono(value) {
    return `<span class="au-mono">${AU.escapeHtml(value ?? "")}</span>`;
  },

  badge(value) {
    if (!value) return "";
    const tone = value.tone || "info";
    return `<span class="au-badge au-badge-${AU.escapeHtml(tone)}"><span class="au-badge-dot"></span>${AU.escapeHtml(value.label ?? "")}</span>`;
  },

  date(value) {
    if (!value) return "";
    const d = new Date(value);
    if (isNaN(d.getTime())) return AU.escapeHtml(value);
    return `<span>${d.toLocaleDateString("es-MX", { day: "2-digit", month: "short", year: "numeric" })}</span>`;
  },

  currency(value) {
    if (value === null || value === undefined || value === "") return "";
    const num = typeof value === "number" ? value : parseFloat(value);
    if (isNaN(num)) return `<span class="au-mono">${AU.escapeHtml(value)}</span>`;
    return `<span class="au-mono" style="font-weight:600">${num.toLocaleString("es-MX", { style: "currency", currency: "MXN" })}</span>`;
  },

  /** Small square thumbnail preview, e.g. Media Library files. */
  image(value) {
    if (!value || !value.url) {
      return `<div style="width:40px;height:40px;border-radius:7px;background:var(--au-surface-subdued);border:1px solid var(--au-border)"></div>`;
    }
    return `<img src="${AU.escapeHtml(value.url)}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:7px;border:1px solid var(--au-border)">`;
  },

  /** Avatar + title + subtitle stack, e.g. customer name + email. */
  person(value) {
    if (!value) return "";
    const initials = AU.escapeHtml(value.initials || (value.title || "?").slice(0, 2).toUpperCase());
    const color = AU.avatarColorFor(value.title);
    return `
      <div class="au-flex au-gap-2">
        <div style="width:30px;height:30px;flex:none;border-radius:9px;background:${color}1a;color:${color};display:flex;align-items:center;justify-content:center;font-size:11.5px;font-weight:700">${initials}</div>
        <div style="display:flex;flex-direction:column;gap:1px;min-width:0">
          <span style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:190px">${AU.escapeHtml(value.title || "")}</span>
          ${value.subtitle ? `<span style="font-size:11px;color:var(--au-text-muted)">${AU.escapeHtml(value.subtitle)}</span>` : ""}
        </div>
      </div>`;
  },

  actions(actions, rowId) {
    if (!Array.isArray(actions) || !actions.length) return "";
    const primary = actions[0];
    const rest = actions.slice(1);

    const renderLink = (a) => {
      if (a.copy) {
        return `<button type="button" class="au-dropdown-item" data-au-copy="${AU.escapeHtml(a.value || a.url || "")}">${AU.escapeHtml(a.label)}</button>`;
      }
      if (a.modal) {
        return `<button type="button" class="au-dropdown-item" data-au-open-modal="${AU.escapeHtml(JSON.stringify(a.modal))}">${AU.escapeHtml(a.label)}</button>`;
      }
      const attrs = [
        `href="${AU.escapeHtml(a.url || "#")}"`,
        `class="au-dropdown-item${a.tone === "critical" ? " is-critical" : ""}${a.tone === "critical" && a.method === "DELETE" ? " delete-item" : ""}"`,
        `data-row-id="${AU.escapeHtml(rowId)}"`,
      ];
      if (a.method && a.method.toUpperCase() !== "GET") attrs.push(`data-method="${AU.escapeHtml(a.method)}"`);
      if (a.target) attrs.push(`target="${AU.escapeHtml(a.target)}"`);
      if (a.summary) attrs.push(`data-au-summary="${AU.escapeHtml(JSON.stringify(a.summary))}"`);
      return `<a ${attrs.join(" ")}>${AU.escapeHtml(a.label)}</a>`;
    };

    let html = `<div class="au-flex au-gap-2 au-row-actions" style="justify-content:flex-end">`;
    if (primary && primary.modal) {
      html += `<button type="button" class="au-btn au-btn-sm" data-au-open-modal="${AU.escapeHtml(JSON.stringify(primary.modal))}">${AU.escapeHtml(primary.label)}</button>`;
    } else if (primary) {
      const summaryAttr = primary.summary ? `data-au-summary="${AU.escapeHtml(JSON.stringify(primary.summary))}"` : "";
      html += `<a href="${AU.escapeHtml(primary.url || "#")}" ${primary.target ? `target="${AU.escapeHtml(primary.target)}"` : ""} class="au-btn au-btn-sm ${primary.tone === "critical" ? "au-btn-critical delete-item" : ""}" data-row-id="${AU.escapeHtml(rowId)}" ${summaryAttr}>${AU.escapeHtml(primary.label)}</a>`;
    }
    if (rest.length) {
      html += `
        <div class="au-dropdown">
          <button type="button" class="au-btn au-btn-sm au-btn-icon" data-au-dropdown-trigger aria-label="Más acciones">&hellip;</button>
          <div class="au-dropdown-panel">${rest.map(renderLink).join("")}</div>
        </div>`;
    }
    html += `</div>`;
    return html;
  },
};

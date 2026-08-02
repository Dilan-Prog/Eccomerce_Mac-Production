/* Bulk-action bar rendering. Selection state itself lives in AdminTable (admin-table.js). */
window.AU = window.AU || {};

AU.renderBulkBar = function (count, actionsConfig) {
  const buttons = (actionsConfig || [])
    .map(
      (a) =>
        `<button type="button" class="au-btn au-btn-sm ${a.tone === "critical" ? "au-btn-critical" : ""}" data-bulk-action="${AU.escapeHtml(a.key)}">${AU.escapeHtml(a.label)}</button>`
    )
    .join("");

  return `
    <span class="au-bulk-bar-count">${count} seleccionado${count === 1 ? "" : "s"}</span>
    <div class="au-bulk-bar-actions">${buttons}</div>
    <div style="flex:1"></div>
    <button type="button" class="au-btn au-btn-plain au-btn-sm" data-au-clear-selection>Limpiar</button>
  `;
};

/* Column show/hide, persisted to localStorage keyed by the table's endpoint. */
window.AU = window.AU || {};

AU.columnVisibility = {
  storageKey(tableKey) {
    return `au-table-hidden-cols:${tableKey}`;
  },

  getHidden(tableKey) {
    try {
      return new Set(JSON.parse(localStorage.getItem(this.storageKey(tableKey))) || []);
    } catch (e) {
      return new Set();
    }
  },

  setHidden(tableKey, hiddenSet) {
    localStorage.setItem(this.storageKey(tableKey), JSON.stringify(Array.from(hiddenSet)));
  },

  renderToggleList(columns, hiddenSet) {
    return `
      <div class="au-col-toggle-list">
        ${columns
          .map(
            (col) => `
          <label>
            <input type="checkbox" class="au-checkbox" data-col-toggle="${AU.escapeHtml(col.key)}" ${hiddenSet.has(col.key) ? "" : "checked"}>
            ${AU.escapeHtml(col.label)}
          </label>`
          )
          .join("")}
      </div>`;
  },
};

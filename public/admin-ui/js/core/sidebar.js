/* Sidebar submenu collapse state, persisted per-browser in localStorage. */
window.AU = window.AU || {};

(function () {
  const STORAGE_KEY = "au-sidebar-open-groups";

  function loadOpenGroups() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    } catch (e) {
      return [];
    }
  }

  function saveOpenGroups(groups) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(groups));
  }

  function init() {
    const sidebar = AU.qs(".au-sidebar");
    if (!sidebar) return;

    const openGroups = new Set(loadOpenGroups());
    AU.qsa(".au-nav-item.has-submenu", sidebar).forEach((item) => {
      const key = item.getAttribute("data-nav-key");
      if (item.classList.contains("is-active") || openGroups.has(key)) {
        item.classList.add("is-open");
      }
    });

    AU.on(sidebar, "click", ".au-nav-item.has-submenu > .au-nav-link", (e, link) => {
      e.preventDefault();
      const item = link.closest(".au-nav-item");
      const key = item.getAttribute("data-nav-key");
      item.classList.toggle("is-open");

      const current = new Set(loadOpenGroups());
      if (item.classList.contains("is-open")) {
        current.add(key);
      } else {
        current.delete(key);
      }
      saveOpenGroups(Array.from(current));
    });

    const toggleBtn = AU.qs("[data-au-sidebar-toggle]");
    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => sidebar.classList.toggle("is-open"));
    }
  }

  document.addEventListener("DOMContentLoaded", init);
})();

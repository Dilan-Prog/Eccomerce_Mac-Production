/* Generic popover/dropdown: any [data-au-dropdown] toggles its .au-dropdown-panel. */
window.AU = window.AU || {};

(function () {
  document.addEventListener("click", (e) => {
    const trigger = e.target.closest("[data-au-dropdown-trigger]");
    const openDropdown = document.querySelector(".au-dropdown.is-open");

    if (trigger) {
      const dropdown = trigger.closest(".au-dropdown");
      const wasOpen = dropdown.classList.contains("is-open");
      AU.qsa(".au-dropdown.is-open").forEach((d) => d.classList.remove("is-open"));
      if (!wasOpen) dropdown.classList.add("is-open");
      return;
    }

    if (openDropdown && !openDropdown.contains(e.target)) {
      openDropdown.classList.remove("is-open");
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      AU.qsa(".au-dropdown.is-open").forEach((d) => d.classList.remove("is-open"));
    }
  });
})();

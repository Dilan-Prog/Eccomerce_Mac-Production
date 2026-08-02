/*
 * iOS-style toggle switch. Markup-driven (no constructor), consistent with
 * dropdown.js/sidebar.js: a single delegated listener flips the visual state
 * and keeps a sibling hidden <input> in sync with the exact name/value
 * (1/0) the backend already validates — so swapping a <select> for a toggle
 * requires zero backend changes.
 *
 * Markup convention (see resources/views/admin-ui/partials/_toggle-field.blade.php):
 *   <label class="au-toggle" data-au-toggle>
 *     <input type="checkbox" class="au-toggle-input" hidden>
 *     <span class="au-toggle-track"><span class="au-toggle-knob"></span></span>
 *   </label>
 *   <input type="hidden" name="status" value="1" data-au-toggle-value>
 */
window.AU = window.AU || {};

(function () {
  document.addEventListener("click", (e) => {
    const toggle = e.target.closest("[data-au-toggle]");
    if (!toggle) return;
    e.preventDefault();
    if (toggle.classList.contains("is-disabled")) return;

    const isOn = toggle.classList.toggle("is-on");
    const checkbox = toggle.querySelector(".au-toggle-input");
    if (checkbox) checkbox.checked = isOn;

    const valueInput = toggle.parentElement ? toggle.parentElement.querySelector("[data-au-toggle-value]") : null;
    if (valueInput) {
      valueInput.value = isOn ? "1" : "0";
      valueInput.dispatchEvent(new Event("change", { bubbles: true }));
    }
  });
})();

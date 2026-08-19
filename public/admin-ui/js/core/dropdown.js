/* Generic popover/dropdown: any [data-au-dropdown-trigger] toggles its .au-dropdown-panel. */
window.AU = window.AU || {};

(function () {
  /*
   * .au-dropdown-panel usa position:fixed (ver dropdown-menu.css) para no
   * quedar atrapado por un ancestro con overflow — ej. .au-table-wrap, que
   * necesita overflow-x:auto para el scroll horizontal de la tabla, y por
   * regla del spec de CSS eso hace que overflow-y también se vuelva
   * scrolleable (nunca "visible" de verdad), atrapando cualquier panel
   * absoluto que se abra cerca del borde de una tabla con pocas filas.
   *
   * Pero position:fixed por sí solo no basta: <header class="au-topbar">
   * usa position:sticky, y un descendiente position:fixed dentro de un
   * ancestro sticky termina posicionado mal en este motor (confirmado:
   * mismas coordenadas calculadas, geometría pintada distinta — el panel
   * termina fuera de la pantalla). La solución robusta es sacar el panel
   * del DOM al wrapper .admin-ui-v2 mientras está abierto (patrón
   * "portal") — queda fuera de CUALQUIER ancestro problemático (sticky,
   * overflow, transform, etc), sin importar dónde viva el trigger. Se
   * regresa a su .au-dropdown original (como último hijo, su posición de
   * siempre) al cerrar.
   *
   * OJO: el portal NO puede ser document.body directamente — todo el CSS
   * de este componente está scoped bajo ".admin-ui-v2 .au-dropdown-panel"
   * (ver dropdown-menu.css), y .admin-ui-v2 es un <div> DENTRO de <body>,
   * no <body> mismo. Mover el panel a document.body lo saca de ese scope
   * y pierde TODO su CSS (position:fixed, display:none, fondo, borde,
   * sombra, z-index) — queda como un <div> sin estilo, de ancho completo,
   * en el flujo normal al final de la página (justo el bug reportado:
   * "no pasa nada" — en realidad sí se abre, pero invisible/deforme).
   */
  let openPanel = null;
  let openHomeParent = null;

  function portalTarget() {
    return document.querySelector(".admin-ui-v2") || document.body;
  }

  function positionPanel(trigger, panel) {
    panel.style.top = "0px";
    panel.style.left = "0px";
    panel.style.visibility = "hidden";
    panel.style.display = "block";

    const triggerRect = trigger.getBoundingClientRect();
    const panelRect = panel.getBoundingClientRect();

    let top = triggerRect.bottom + 4;
    if (top + panelRect.height > window.innerHeight - 8) {
      top = triggerRect.top - panelRect.height - 4;
    }

    let left = triggerRect.right - panelRect.width;
    if (left < 8) left = 8;
    if (left + panelRect.width > window.innerWidth - 8) {
      left = window.innerWidth - 8 - panelRect.width;
    }

    panel.style.top = `${Math.max(8, top)}px`;
    panel.style.left = `${left}px`;
    panel.style.visibility = "visible";
  }

  function close() {
    if (!openPanel) return;
    openPanel.style.display = "none";
    if (openHomeParent) openHomeParent.appendChild(openPanel);
    openPanel.closest(".au-dropdown")?.classList.remove("is-open");
    openPanel = null;
    openHomeParent = null;
  }

  document.addEventListener("click", (e) => {
    const trigger = e.target.closest("[data-au-dropdown-trigger]");

    if (trigger) {
      const dropdown = trigger.closest(".au-dropdown");
      const panel = dropdown.querySelector(".au-dropdown-panel");
      const reopening = openPanel !== panel;
      close();
      if (reopening && panel) {
        openPanel = panel;
        openHomeParent = dropdown;
        dropdown.classList.add("is-open");
        portalTarget().appendChild(panel);
        positionPanel(trigger, panel);
      }
      return;
    }

    if (openPanel && !openPanel.contains(e.target)) {
      close();
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") close();
  });

  // Un dropdown fixed no se mueve solo con el trigger al hacer scroll (ej.
  // dentro de .au-table-wrap) — más simple y seguro cerrarlo que dejarlo
  // desalineado. capture:true para detectar scroll en cualquier ancestro
  // scrolleable, no solo en window.
  window.addEventListener("scroll", close, true);
  window.addEventListener("resize", close);
})();

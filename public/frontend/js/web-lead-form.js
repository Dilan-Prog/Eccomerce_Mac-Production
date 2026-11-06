/*!
 * Formularios publicos de solicitud (web leads).
 * Rellena los campos hidden de origen/atribucion antes de enviar (y al cargar) y evita el doble envio.
 * Si window.MDNAttribution no existe o falla, el formulario se envia igual con esos campos vacios.
 * No agrega tracking nuevo ni dispara eventos de conversion. Sin dependencias.
 */
(function () {
  'use strict';

  var ATTR_KEYS = [
    'session_id', 'gclid', 'gbraid', 'wbraid', 'fbclid',
    'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
    'landing_page', 'referrer'
  ];

  function setField(form, name, value) {
    try {
      var el = form.elements ? form.elements[name] : null;
      if (!el || el.length) { return; } // inexistente o NodeList (nombre repetido)
      el.value = (value === null || value === undefined) ? '' : String(value);
    } catch (e) { /* ignorar */ }
  }

  function currentPage() {
    try {
      return String(window.location.href).split('#')[0];
    } catch (e) { return ''; }
  }

  function fill(form) {
    setField(form, 'source_page', currentPage());

    var attr = null;
    try {
      if (window.MDNAttribution && typeof window.MDNAttribution.get === 'function') {
        attr = window.MDNAttribution.get();
      }
    } catch (e) { attr = null; }
    if (!attr || typeof attr !== 'object') { return; }

    for (var i = 0; i < ATTR_KEYS.length; i++) {
      var key = ATTR_KEYS[i];
      try {
        if (attr[key]) { setField(form, key, attr[key]); }
      } catch (e) { /* ignorar */ }
    }
  }

  function setBusy(form, busy) {
    try {
      var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
      for (var i = 0; i < buttons.length; i++) { buttons[i].disabled = !!busy; }
    } catch (e) { /* ignorar */ }
  }

  function init(form) {
    if (form.getAttribute('data-web-lead-init') === '1') { return; }
    form.setAttribute('data-web-lead-init', '1');

    fill(form);

    form.addEventListener('submit', function (ev) {
      if (form.getAttribute('data-web-lead-sent') === '1') {
        ev.preventDefault(); // ya se envio: evita el doble envio
        return;
      }
      fill(form);
      form.setAttribute('data-web-lead-sent', '1');
      // Se deshabilita el boton despues de que el navegador arme el envio, para no perder el valor del formulario.
      setTimeout(function () { setBusy(form, true); }, 0);
    });
  }

  function restore(form) {
    form.removeAttribute('data-web-lead-sent');
    setBusy(form, false);
    fill(form);
  }

  function boot() {
    var forms = document.querySelectorAll('form input[name="form_type"]');
    var seen = [];
    for (var i = 0; i < forms.length; i++) {
      var form = forms[i].form;
      if (!form || seen.indexOf(form) !== -1) { continue; }
      seen.push(form);
      init(form);
    }
    window.addEventListener('pageshow', function (ev) {
      // Pagina restaurada desde bfcache (boton "atras"): vuelve a habilitar el envio.
      if (ev && ev.persisted) {
        for (var j = 0; j < seen.length; j++) { restore(seen[j]); }
      }
    });
  }

  try {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', boot);
    } else {
      boot();
    }
  } catch (e) { /* el formulario funciona igual sin este script */ }
})();

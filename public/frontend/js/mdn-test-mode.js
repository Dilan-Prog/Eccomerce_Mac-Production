/*!
 * MDN Test Mode (cliente)
 * Utilidades del modo prueba de atribucion del Laboratorio (admin). Sin dependencias, nunca lanzan.
 *
 *   MDNTestMode.bootClear(nonceOrNull, env?)
 *     Evita que la atribucion de una sesion de prueba contamine visitas reales (y al reves).
 *     - Modo prueba (nonce = string del servidor): si localStorage.mdn_test_boot !== nonce, borra la
 *       atribucion guardada (cookie mdn_attr con el mismo dominio que attribution.js + localStorage), las
 *       claves planas heredadas (gclid, utm_*, landing_page, mdn_visit_sent, mdn_quote_*, ...) y guarda el marcador.
 *       Con el mismo nonce no toca nada (la atribucion de la prueba se conserva entre paginas).
 *     - Fuera de modo prueba (nonce null/''): si existe el marcador, hace la misma limpieza y lo borra, para que
 *       la atribucion de prueba no la herede una visita real. Sin marcador no hace nada.
 *     env opcional (pruebas): { document, localStorage, location }.
 *
 *   MDNTestMode.banner(document?)  Inyecta la pill fija "Modo prueba de atribucion activo" (idempotente).
 *
 * Cargado desde partials/attribution-capture.blade.php, ANTES de attribution.js.
 */
(function (root) {
  'use strict';

  var MARKER_KEY = 'mdn_test_boot';
  var ATTR_KEY = 'mdn_attr';
  var BANNER_ID = 'mdn-test-mode-banner';
  var BANNER_TEXT = 'Modo prueba de atribución activo';
  // Claves planas del sistema anterior y estado auxiliar de mdn-events / attribution-capture.
  var LEGACY_KEYS = [
    'gclid', 'gbraid', 'wbraid', 'fbclid',
    'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
    'landing_page', 'mdn_visit_sent', 'mdn_quote_pending', 'mdn_queue'
  ];
  var PREFIX_KEYS = ['mdn_quote_']; // mdn_quote_<id> (folio de cotizacion generado en la prueba)

  // Mismo criterio que cookieDomainFor() de attribution.js.
  function cookieDomainFor(hostname) {
    var h = String(hostname || '').toLowerCase();
    if (h === 'macdelnorte.com' || /\.macdelnorte\.com$/.test(h)) return '.macdelnorte.com';
    return '';
  }

  function expireCookie(doc, name, domain, secure) {
    var base = name + '=; path=/; max-age=0; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax' + (secure ? '; Secure' : '');
    if (domain) {
      try { doc.cookie = base + '; domain=' + domain; } catch (e) { /* ignore */ }
    }
    try { doc.cookie = base; } catch (e2) { /* ignore */ } // por si quedo una cookie sin dominio
  }

  function resolveEnv(env) {
    env = env || {};
    return {
      document: env.document || (root && root.document) || null,
      localStorage: env.localStorage || (function () { try { return root.localStorage; } catch (e) { return null; } })(),
      location: env.location || (root && root.location) || {}
    };
  }

  function clearAttribution(e) {
    var ls = e.localStorage;
    try {
      if (e.document) {
        var host = (e.location && e.location.hostname) || '';
        expireCookie(e.document, ATTR_KEY, cookieDomainFor(host), e.location && e.location.protocol === 'https:');
      }
    } catch (err1) { /* ignore */ }
    if (!ls) return;
    try { ls.removeItem(ATTR_KEY); } catch (err2) { /* ignore */ }
    for (var i = 0; i < LEGACY_KEYS.length; i++) {
      try { ls.removeItem(LEGACY_KEYS[i]); } catch (err3) { /* ignore */ }
    }
    try {
      if (typeof ls.length === 'number' && typeof ls.key === 'function') {
        var doomed = [];
        for (var k = 0; k < ls.length; k++) {
          var name = ls.key(k);
          for (var p = 0; p < PREFIX_KEYS.length; p++) {
            if (typeof name === 'string' && name.indexOf(PREFIX_KEYS[p]) === 0) doomed.push(name);
          }
        }
        for (var d = 0; d < doomed.length; d++) ls.removeItem(doomed[d]);
      }
    } catch (err4) { /* ignore */ }
  }

  function bootClear(nonceOrNull, env) {
    var result = { cleared: false, mode: 'normal' };
    try {
      var e = resolveEnv(env);
      var ls = e.localStorage;
      if (!ls) return result;
      var nonce = (typeof nonceOrNull === 'string' && nonceOrNull !== '') ? nonceOrNull : null;
      var marker = ls.getItem(MARKER_KEY); // si el storage falla, salta al catch: no se limpia nada

      if (nonce !== null) {
        result.mode = 'test';
        if (marker !== nonce) {
          clearAttribution(e);
          ls.setItem(MARKER_KEY, nonce);
          result.cleared = true;
        }
      } else if (marker !== null && marker !== undefined) {
        clearAttribution(e);
        ls.removeItem(MARKER_KEY);
        result.cleared = true;
      }
    } catch (err) { /* ignore */ }
    return result;
  }

  function banner(doc) {
    try {
      doc = doc || (root && root.document);
      if (!doc || typeof doc.createElement !== 'function') return null;
      if (typeof doc.getElementById === 'function' && doc.getElementById(BANNER_ID)) return null;
      var host = doc.body || doc.documentElement;
      if (!host || typeof host.appendChild !== 'function') return null;
      var el = doc.createElement('div');
      el.setAttribute('id', BANNER_ID);
      el.setAttribute('role', 'status');
      el.setAttribute('aria-live', 'polite');
      el.setAttribute('style', [
        'position:fixed', 'left:12px', 'bottom:12px', 'z-index:2147483000', 'pointer-events:none',
        'margin:0', 'padding:5px 12px', 'border-radius:999px', 'background:#b45309', 'color:#fff',
        'font:600 12px/1.3 system-ui,-apple-system,Segoe UI,Roboto,sans-serif', 'letter-spacing:.2px',
        'box-shadow:0 2px 8px rgba(0,0,0,.35)', 'opacity:.92'
      ].join(';'));
      el.textContent = BANNER_TEXT;
      host.appendChild(el);
      return el;
    } catch (e) { return null; }
  }

  var api = {
    bootClear: bootClear,
    banner: banner,
    cookieDomainFor: cookieDomainFor,
    MARKER_KEY: MARKER_KEY,
    LEGACY_KEYS: LEGACY_KEYS,
    BANNER_ID: BANNER_ID
  };

  if (root) root.MDNTestMode = api;
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
})(typeof window !== 'undefined' ? window : this);

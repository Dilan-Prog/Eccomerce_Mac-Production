/*
 * Medicion uniforme de eventos del sitio (WhatsApp, llamadas, carrito, cotizacion).
 * Una sola delegacion por document: no hace falta editar cada boton.
 *
 * Todo se guarda con POST a /track-conversion (misma tabla track_conversions) junto con la
 * atribucion completa de window.MDNAttribution (gclid, UTM, sesion…). Los datos propios del
 * evento van en `meta` (sku, qty, ubicacion del boton, folio…).
 *
 * Requiere (lo imprime resources/views/frontend/partials/mdn-events.blade.php):
 *   window.MDNEventsConfig = { url: <ruta track.conversion>, csrf: <token> }
 *
 * Tipos que emite:
 *   whatsapp_click   clic a cualquier enlace de WhatsApp SIN la clase .track-conversion
 *   phone_click      clic a cualquier tel: SIN .track-conversion
 *   add_to_cart / add_to_cart_blocked   respuesta de POST /add-to-cart
 *   quote_start      clic para cotizar (formulario formal, boton "Cotizar", solicitar-cotizacion)
 *   quote_submit     se genero la cotizacion (pagina /cotizacion/generada/{id})
 *   quote_dismiss    se cerro el modal de la cotizacion sin descargar el PDF
 * Los enlaces con .track-conversion los sigue registrando el listener de master.blade.php
 * (conservan su data-type historico), asi nada se cuenta doble.
 */
(function (root) {
  'use strict';

  var WA_PATTERNS = ['wa.link/', 'wa.me/', 'api.whatsapp.com', 'web.whatsapp.com', 'whatsapp://'];
  var LANDMARKS = 'header, footer, nav, aside, main, section, article';

  function isWhatsAppHref(href) {
    var h = String(href || '').toLowerCase();
    for (var i = 0; i < WA_PATTERNS.length; i++) {
      if (h.indexOf(WA_PATTERNS[i]) !== -1) return true;
    }
    return false;
  }

  function isPhoneHref(href) {
    return /^tel:/i.test(String(href || '').trim());
  }

  function isQuoteFormHref(href) {
    return /\/cotizacion\/formulario/i.test(String(href || ''));
  }

  function quotePagePath(pathname) {
    var m = /^\/cotizacion\/generada\/(\d+)/.exec(String(pathname || ''));
    return m ? m[1] : null;
  }

  /** "name=value&…" serializado por jQuery -> meta del carrito. */
  function parseCartData(raw) {
    var out = {};
    try {
      var p = new URLSearchParams(String(raw || ''));
      var map = { product_id: 'product_id', sku: 'sku', qty: 'qty', brand_name: 'brand', productModel: 'product_name' };
      Object.keys(map).forEach(function (k) {
        var v = p.get(k);
        if (v !== null && v !== '') out[map[k]] = v;
      });
    } catch (e) { /* ignore */ }
    return out;
  }

  /** Ubicacion legible del elemento: data-placement o "<zona>#id.clase". */
  function placementOf(el) {
    try {
      var tagged = el.closest('[data-placement]');
      if (tagged) return String(tagged.getAttribute('data-placement')).slice(0, 80);
      var zone = el.closest(LANDMARKS);
      if (!zone) return (el.id ? '#' + el.id : (el.className && typeof el.className === 'string' ? '.' + el.className.trim().split(/\s+/)[0] : 'pagina')).slice(0, 80);
      var name = zone.tagName.toLowerCase();
      if (zone.id) name += '#' + zone.id;
      else if (typeof zone.className === 'string' && zone.className.trim()) name += '.' + zone.className.trim().split(/\s+/)[0];
      return name.slice(0, 80);
    } catch (e) { return ''; }
  }

  function clean(meta) {
    var out = {};
    Object.keys(meta || {}).forEach(function (k) {
      var v = meta[k];
      if (v === null || v === undefined || v === '') return;
      if (typeof v === 'object') return;
      out[k] = String(v).slice(0, 300);
    });
    return out;
  }

  // ---- envio -------------------------------------------------------------------------------
  var recent = {};

  function newId() {
    if (root.MDNAttribution && typeof root.MDNAttribution.newEventId === 'function') return root.MDNAttribution.newEventId();
    return 'e-' + Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
  }

  function send(type, meta, eventId) {
    try {
      var cfg = root.MDNEventsConfig;
      if (!cfg || !cfg.url) return;

      // Antidoble clic: mismo tipo+destino en menos de 1.5 s cuenta una vez.
      var key = type + '|' + ((meta && (meta.href || meta.sku || meta.folio)) || '');
      var now = Date.now();
      if (!eventId && recent[key] && now - recent[key] < 1500) return;
      recent[key] = now;

      var attr = (root.MDNAttribution && typeof root.MDNAttribution.get === 'function') ? root.MDNAttribution.get() : {};
      var payload = Object.assign({}, attr, {
        type: type,
        page_url: root.location.href,
        event_id: eventId || newId(),
        meta: clean(meta)
      });

      root.fetch(cfg.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf || '' },
        body: JSON.stringify(payload),
        keepalive: true
      }).catch(function () { /* medir nunca debe romper la pagina */ });

      // Mismo evento para GTM/GA4/Ads (nombres propios con prefijo para no chocar con los existentes).
      root.dataLayer = root.dataLayer || [];
      root.dataLayer.push(Object.assign({ event: 'mdn_' + type }, clean(meta)));
    } catch (e) { /* ignore */ }
  }

  // ---- clics: WhatsApp, telefono, cotizar ----------------------------------------------------
  function onClick(e) {
    try {
      var target = e.target;
      if (!target || !target.closest) return;

      var btn = target.closest('[data-action="solicitar-cotizacion"]');
      if (btn) {
        send('quote_start', { placement: placementOf(btn), intent: 'catalogo', link_text: (btn.textContent || '').trim().slice(0, 80) });
        return;
      }

      var a = target.closest('a[href]');
      if (!a) return;
      // Ya lo registra master.blade.php con su data-type historico.
      if (a.closest('a.track-conversion') || a.classList.contains('track-conversion')) return;

      var href = a.getAttribute('href') || '';
      var meta = { placement: placementOf(a), href: href.slice(0, 200), link_text: (a.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 80) };

      // Enlaces con evento propio (p. ej. data-mdn-event="job_apply" en /empleos): se registran con ese
      // tipo y NO como whatsapp_click, para que no cuenten como conversion de ventas.
      var customEvent = a.getAttribute('data-mdn-event');
      if (customEvent) {
        if (a.getAttribute('data-mdn-ref')) meta.ref = a.getAttribute('data-mdn-ref');
        if (a.getAttribute('data-mdn-puesto')) meta.puesto = a.getAttribute('data-mdn-puesto');
        if (a.getAttribute('data-mdn-area')) meta.area = a.getAttribute('data-mdn-area');
        send(customEvent, meta);
        return;
      }

      if (isWhatsAppHref(href)) { send('whatsapp_click', meta); return; }
      if (isPhoneHref(href)) { send('phone_click', meta); return; }
      if (isQuoteFormHref(href)) { meta.intent = 'formal'; send('quote_start', meta); return; }
      if (a.classList.contains('btn-cotizar')) { meta.intent = 'invitado'; send('quote_start', meta); }
    } catch (err) { /* ignore */ }
  }

  // ---- carrito: respuesta de POST /add-to-cart (jQuery se carga al final del body) ------------
  function hookCart() {
    var $ = root.jQuery;
    if (!$ || !$.fn) return false;
    var isCartUrl = function (u) { return /\/add-to-cart(\?|$)/.test(String(u || '')); };

    $(root.document).ajaxSuccess(function (ev, xhr, settings, data) {
      if (!settings || !isCartUrl(settings.url)) return;
      var meta = parseCartData(settings.data);
      if (data && data.status === 'success') {
        send('add_to_cart', meta);
      } else {
        meta.cart_error = (data && data.message) || 'error';
        send('add_to_cart_blocked', meta);
      }
    });
    $(root.document).ajaxError(function (ev, xhr, settings) {
      if (!settings || !isCartUrl(settings.url)) return;
      var meta = parseCartData(settings.data);
      meta.cart_error = xhr && xhr.status === 401 ? 'login_required' : ('http_' + (xhr && xhr.status));
      send('add_to_cart_blocked', meta);
    });
    return true;
  }

  function waitForJquery() {
    if (hookCart()) return;
    var tries = 0;
    var timer = root.setInterval(function () {
      tries += 1;
      if (hookCart() || tries > 50) root.clearInterval(timer);
    }, 200);
  }

  // ---- cotizacion generada: exito y descarte del modal ------------------------------------------
  function hookQuotePage() {
    var id = quotePagePath(root.location.pathname);
    if (!id) return;
    var doc = root.document;
    var folioEl = doc.querySelector('.cot-modal-folio');
    var folio = folioEl ? (folioEl.textContent || '').trim() : '';

    // Exito real: el servidor ya genero el folio. event_id fijo => recargar la pagina no duplica.
    send('quote_submit', { folio: folio, intent: 'formal' }, 'quote_submit-' + id);

    var downloaded = false;
    var dismissed = false;
    var dismiss = function () {
      if (downloaded || dismissed) return;
      dismissed = true;
      send('quote_dismiss', { folio: folio }, 'quote_dismiss-' + id);
    };

    doc.addEventListener('click', function (e) {
      var t = e.target;
      if (!t || !t.closest) return;
      if (t.closest('.btn-download')) { downloaded = true; return; }
      if (t.closest('#closeModalBtn, #closeModalLink') || t.id === 'cotModal') dismiss();
    }, true);
    doc.addEventListener('keydown', function (e) { if (e.key === 'Escape') dismiss(); });
  }

  function init() {
    root.document.addEventListener('click', onClick, true);
    waitForJquery();
    if (root.document.readyState === 'loading') root.document.addEventListener('DOMContentLoaded', hookQuotePage);
    else hookQuotePage();
  }

  if (root && root.document) init();

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
      isWhatsAppHref: isWhatsAppHref,
      isPhoneHref: isPhoneHref,
      isQuoteFormHref: isQuoteFormHref,
      quotePagePath: quotePagePath,
      parseCartData: parseCartData,
      clean: clean
    };
  }
})(typeof window !== 'undefined' ? window : this);

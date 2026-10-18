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

  // extra = { contact_folio } (folio de contacto del clic, solo si ATTRIBUTION_FOLIO_ENABLED lo generó).
  function send(type, meta, eventId, extra) {
    try {
      var cfg = root.MDNEventsConfig;
      if (!cfg || !cfg.url) return;

      // Antidoble clic: mismo tipo+destino en menos de 1.5 s cuenta una vez.
      var key = type + '|' + ((meta && (meta.href || meta.sku || meta.folio)) || '');
      var now = Date.now();
      if (!eventId && recent[key] && now - recent[key] < 1500) return;
      recent[key] = now;

      var attr = (root.MDNAttribution && typeof root.MDNAttribution.get === 'function') ? root.MDNAttribution.get() : {};
      // Datos de la captura (edad/canal del click id) y hora REAL del evento (captured_at sigue siendo la del toque).
      var attrMeta = {};
      try { if (root.MDNAttribution && typeof root.MDNAttribution.getMeta === 'function') attrMeta = root.MDNAttribution.getMeta() || {}; } catch (e) { /* ignore */ }
      var eventAt = (root.MDNAttribution && typeof root.MDNAttribution.eventAt === 'function')
        ? root.MDNAttribution.eventAt() : new Date().toISOString();
      var payload = Object.assign({}, attr, {
        type: type,
        page_url: root.location.href,
        event_id: eventId || newId(),
        event_at: eventAt,
        meta: clean(Object.assign({}, attrMeta, meta))
      });
      if (extra && extra.contact_folio) payload.contact_folio = extra.contact_folio;
      // Modo prueba de atribucion (window.MDN_TEST_MODE lo imprime el servidor con la cookie de prueba valida):
      // el backend guarda el evento como is_test. Sin modo prueba el payload es el de siempre.
      if (root.MDN_TEST_MODE) payload.test = 1;

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

  // ---- folio de contacto (mdn-contact.js; INERTE si ATTRIBUTION_FOLIO_ENABLED=false) ----------------
  function contact() { return root.MDNContact || null; }

  function folioEnabled() {
    try { var C = contact(); return !!(C && C.getConfig && C.getConfig().enabled); } catch (e) { return false; }
  }

  var ctxCache = null;
  function contactCtx() {
    if (ctxCache) return ctxCache;
    var C = contact();
    var product = { name: '', sku: '' };
    // El producto del mensaje SOLO sale de la pagina de detalle (/product-detail/...): en home, listados y
    // servicios el JSON-LD puede traer otros productos y el mensaje diria "me interesa: <producto que nadie eligio>".
    var onProductPage = /^\/product-detail\//.test((root.location && root.location.pathname) || '');
    try { if (onProductPage && C && C.productContext) product = C.productContext(root.document) || product; } catch (e) { /* ignore */ }
    ctxCache = { lang: (root.document.documentElement && root.document.documentElement.lang) || '', product: product };
    return ctxCache;
  }

  /**
   * Clasifica un enlace de contacto y, SOLO si el folio esta activo, reescribe su href con el folio
   * (el listener de master lee ese href y data-mdn-folio). `reuse=false` en pointerdown (guarda el
   * resultado 5 s); `reuse=true` en click (consume el de pointerdown o genera uno nuevo, p. ej. con teclado).
   */
  function prepareContact(a, reuse) {
    var C = contact();
    if (!C || !C.classify) return null;
    var cls = C.classify(a);
    if (!cls || !cls.kind) return null;
    if (!folioEnabled()) return { cls: cls, prep: null };
    var rec = a.__mdnPrep;
    if (reuse && rec && Date.now() - rec.ts < 5000) { a.__mdnPrep = null; return rec; }
    var prep = C.prepare(a, contactCtx());
    rec = { ts: Date.now(), cls: cls, prep: prep };
    a.__mdnPrep = reuse ? null : rec;
    return rec;
  }

  // Evento de un enlace de contacto SIN clase track-conversion (los .track-conversion los envia master).
  function sendContact(a, rec) {
    var C = contact();
    var cls = rec.cls;
    var prep = rec.prep;
    var on = folioEnabled();
    var type = cls.canonicalType;                       // whatsapp_click | phone_click | email_click
    if (cls.kind === 'mailto' && !on) return;          // sin flag, el correo sin clase no se registra (igual que hoy)
    if (on && C.shouldDedupe && C.dedupeKey && C.shouldDedupe(C.dedupeKey(cls))) return;
    var ctx = contactCtx();
    var orig = cls.origHref || a.getAttribute('href') || '';
    var meta = {
      placement: cls.placement || placementOf(a),
      href: orig.slice(0, 200),
      link_text: (a.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 80)
    };
    if (ctx.product && ctx.product.name) meta.product_name = ctx.product.name;
    if (ctx.product && ctx.product.sku) meta.sku = ctx.product.sku;
    if (cls.legacyType && cls.legacyType !== type) meta.legacy_type = cls.legacyType;
    send(type, meta, null, { contact_folio: prep && prep.folio ? prep.folio : '' });
  }

  // Botones "Solicitar cotizacion" de las paginas autonomas (catalogo/categorias): abren WhatsApp por JS
  // con CATALOGO_DATA.whatsappUrl. Con folio activo se reescribe esa URL y el clic cuenta como whatsapp_click.
  // Devuelve true si lo manejo; false => se registra como quote_start igual que antes.
  function handleCatalogWhatsApp(btn) {
    try {
      var C = contact();
      var data = root.CATALOGO_DATA;
      if (!C || !C.prepare || !folioEnabled() || !data || !data.whatsappUrl) return false;
      if (!data.__mdnOrig) data.__mdnOrig = data.whatsappUrl;
      var modelo = btn.getAttribute('data-modelo') || '';
      var path = (root.location && root.location.pathname) || '';
      var placement = modelo ? 'card_producto_catalogo' : (/categorias/.test(path) && !/categoria-productos/.test(path) ? 'categorias_movil' : 'categoria_movil');
      var a = root.document.createElement('a');
      a.setAttribute('href', data.__mdnOrig);
      a.setAttribute('data-placement', placement);
      var ctx = contactCtx();
      var prep = C.prepare(a, { lang: ctx.lang, product: modelo ? { name: modelo, sku: '' } : { name: '', sku: '' } });
      if (!prep || !prep.folio) { data.whatsappUrl = data.__mdnOrig; return false; }
      data.whatsappUrl = a.getAttribute('href');       // lo lee categorias.js / categoria-productos.js al abrir
      var meta = { placement: placement, href: String(data.__mdnOrig).slice(0, 200), link_text: (btn.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 80) };
      if (modelo) meta.product_name = modelo;
      send('whatsapp_click', meta, null, { contact_folio: prep.folio });
      return true;
    } catch (e) { return false; }
  }

  // Reescribe el href al pulsar (cubre clic central y pulsacion larga); no envia nada todavia.
  function onPointerDown(e) {
    try {
      if (e.button === 2) return;
      var t = e.target;
      if (!t || !t.closest) return;
      var a = t.closest('a[href]');
      if (a) prepareContact(a, false);
    } catch (err) { /* ignore */ }
  }

  // Devuelve el href original (data-mdn-orig-href). Se usa cuando el gesto NO termina en un clic (scroll tactil,
  // pulsacion larga, menu contextual) y justo despues de un clic: el navegador y master leen el href de forma
  // sincrona, asi que restaurarlo en el siguiente turno es seguro y evita folios sin evento ("copiar enlace").
  function restoreHref(a) {
    try {
      var orig = a.getAttribute('data-mdn-orig-href');
      if (orig !== null && orig !== '' && a.getAttribute('href') !== orig) a.setAttribute('href', orig);
    } catch (e) { /* ignore */ }
  }

  function onPointerAbort(e) {
    try {
      var t = e.target;
      if (!t || !t.closest) return;
      var a = t.closest('a[href]');
      if (a) { restoreHref(a); a.__mdnPrep = null; }
    } catch (err) { /* ignore */ }
  }

  function restoreSoon(a) {
    try { root.setTimeout(function () { restoreHref(a); }, 0); } catch (e) { /* ignore */ }
  }

  // ---- clics: WhatsApp, telefono, correo, cotizar ----------------------------------------------
  function onClick(e, isAux) {
    try {
      var target = e.target;
      if (!target || !target.closest) return;

      var btn = target.closest('[data-action="solicitar-cotizacion"]');
      if (btn) {
        // El clic central no abre WhatsApp (los handlers de categorias.js escuchan solo `click`): no registrar nada.
        if (isAux) return;
        if (handleCatalogWhatsApp(btn)) return;
        send('quote_start', { placement: placementOf(btn), intent: 'catalogo', link_text: (btn.textContent || '').trim().slice(0, 80) });
        return;
      }

      var a = target.closest('a[href]');
      if (!a) return;
      var isTC = !!(a.closest('a.track-conversion') || a.classList.contains('track-conversion'));

      // Reescribe el href con folio ANTES de que corra el listener de master (burbuja).
      var rec = prepareContact(a, true);
      if (rec && rec.prep && rec.prep.folio) restoreSoon(a);

      if (isTC) {
        // Ya lo registra master.blade.php con su data-type historico. El clic central no pasa por el listener
        // de master, asi que se registra aqui para que ese folio no quede huerfano (con antidoble clic y con el
        // mismo conversion_click de dataLayer que un clic normal).
        if (isAux && rec && rec.prep && rec.prep.folio && typeof root.registrarConversion === 'function') {
          var Cc = contact();
          var dup = false;
          try { dup = !!(Cc && Cc.shouldDedupe && Cc.dedupeKey && Cc.shouldDedupe(Cc.dedupeKey(rec.cls))); } catch (e) { /* ignore */ }
          if (!dup) {
            var dtype = a.getAttribute('data-type') || 'desconocido';
            if (!/^(correo|telefono)/.test(dtype)) {
              root.dataLayer = root.dataLayer || [];
              root.dataLayer.push({ event: 'conversion_click', action: 'click', label: dtype });
            }
            root.registrarConversion(dtype, a);
          }
        }
        return;
      }
      if (isAux && !(rec && rec.prep && rec.prep.folio)) return;   // el clic central solo cuenta si genero folio

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

      // Enlace de contacto clasificado por mdn-contact.js (folio, tipo canonico, placement, producto).
      if (rec && rec.cls && !rec.cls.skip) { sendContact(a, rec); return; }

      // Respaldo si mdn-contact.js no cargo o el enlace es un destino ajeno (skip): comportamiento de siempre.
      if (isWhatsAppHref(href)) { send('whatsapp_click', meta); return; }
      if (isPhoneHref(href)) { send('phone_click', meta); return; }
      if (isQuoteFormHref(href)) {
        meta.intent = 'formal';
        // Con el folio activo, la cotizacion formal estrena su folio MN al INICIAR: se guarda como "pendiente"
        // hasta que /cotizacion/generada/{id} lo reciba en quote_submit (junto al COT-).
        var mn = startQuoteFolio();
        send('quote_start', meta, null, mn ? { contact_folio: mn } : null);
        return;
      }
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

  // ---- folio MN de la cotizacion formal ----------------------------------------------------------
  // quote_start lo genera y lo deja "pendiente" en localStorage; la pagina de la cotizacion generada lo toma,
  // lo fija por id (estable al recargar) y lo manda en quote_submit con meta.folio = COT-…. Solo con el folio
  // activo y el placement "cotizacion" permitido (piloto): con el flag apagado no se genera ni se guarda nada.
  var QUOTE_PLACEMENT = 'cotizacion';
  var QUOTE_PENDING_KEY = 'mdn_quote_pending';
  var QUOTE_PENDING_TTL = 6 * 60 * 60 * 1000;

  function lsGet(k) { try { return root.localStorage.getItem(k); } catch (e) { return null; } }
  function lsSet(k, v) { try { root.localStorage.setItem(k, v); } catch (e) { /* ignore */ } }
  function lsDel(k) { try { root.localStorage.removeItem(k); } catch (e) { /* ignore */ } }

  function quoteFolioEnabled() {
    try {
      if (!folioEnabled()) return false;
      var p = contact().getConfig().placements;
      return p === '*' || (Array.isArray(p) && p.indexOf(QUOTE_PLACEMENT) !== -1);
    } catch (e) { return false; }
  }

  function readPendingQuoteFolio() {
    try {
      var p = JSON.parse(lsGet(QUOTE_PENDING_KEY) || 'null');
      if (p && typeof p.folio === 'string' && typeof p.ts === 'number' && Date.now() - p.ts < QUOTE_PENDING_TTL) return p;
    } catch (e) { /* ignore */ }
    return null;
  }

  /** Folio MN al iniciar la cotizacion ('' si el folio no esta activo). Un doble clic reutiliza el mismo. */
  function startQuoteFolio() {
    try {
      if (!quoteFolioEnabled()) return '';
      var C = contact();
      var pend = readPendingQuoteFolio();
      if (pend && C.isValidFolio(pend.folio) && Date.now() - pend.ts < 1500) return pend.folio;
      var mn = C.newFolio();
      if (!C.isValidFolio(mn)) return '';
      lsSet(QUOTE_PENDING_KEY, JSON.stringify({ folio: mn, ts: Date.now() }));
      return mn;
    } catch (e) { return ''; }
  }

  /** Folio MN de la cotizacion {id}: el guardado para ese id, o el pendiente de quote_start, o uno nuevo. */
  function quoteFolioFor(id) {
    try {
      if (!quoteFolioEnabled()) return '';
      var C = contact();
      var key = 'mdn_quote_' + id;
      var mn = lsGet(key);
      if (mn && C.isValidFolio(mn)) return mn;
      var pend = readPendingQuoteFolio();
      mn = (pend && C.isValidFolio(pend.folio)) ? pend.folio : C.newFolio();
      if (!C.isValidFolio(mn)) return '';
      lsSet(key, mn);
      lsDel(QUOTE_PENDING_KEY);
      return mn;
    } catch (e) { return ''; }
  }

  // ---- cotizacion generada: exito y descarte del modal ------------------------------------------
  function hookQuotePage() {
    var id = quotePagePath(root.location.pathname);
    if (!id) return;
    var doc = root.document;
    var folioEl = doc.querySelector('.cot-modal-folio');
    var folio = folioEl ? (folioEl.textContent || '').trim() : '';

    // Folio MN de seguimiento (solo con el folio activo): se muestra junto al COT- para que el cliente lo mencione.
    var mn = quoteFolioFor(id);
    if (mn && folioEl && folioEl.parentNode && doc.createElement) {
      try {
        var tag = doc.createElement('span');
        tag.className = 'mdn-quote-folio';
        tag.setAttribute('style', 'display:block;font-size:12px;color:#4A5568;margin-top:2px');
        tag.textContent = 'Folio de seguimiento: ' + mn;
        folioEl.parentNode.appendChild(tag);
      } catch (e) { /* ignore */ }
    }

    // Exito real: el servidor ya genero el folio. event_id fijo => recargar la pagina no duplica.
    send('quote_submit', { folio: folio, intent: 'formal' }, 'quote_submit-' + id, mn ? { contact_folio: mn } : null);

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
    // Configura el folio con lo que imprime partials/mdn-events.blade.php (sin config => inerte).
    try {
      var C = contact();
      var cfg = root.MDNEventsConfig;
      if (C && typeof C.configure === 'function' && cfg && cfg.folio) C.configure(cfg.folio);
    } catch (e) { /* ignore */ }

    root.document.addEventListener('pointerdown', onPointerDown, true);
    root.document.addEventListener('pointercancel', onPointerAbort, true);
    root.document.addEventListener('contextmenu', onPointerAbort, true);
    root.document.addEventListener('click', onClick, true);
    root.document.addEventListener('auxclick', function (e) { if (e.button === 1) onClick(e, true); }, true);
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

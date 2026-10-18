/*!
 * MDN Contact
 * Folio de contacto para atribucion: prepara (reescribe) el href de los enlaces de
 * WhatsApp y correo para que el mensaje lleve un folio "MN" + 5 caracteres.
 * Solo PREPARA el enlace: nunca envia nada al backend. Sin dependencias.
 *
 * Inerte por defecto: sin configure({ enabled: true, ... }) no cambia ningun href.
 * Todo es sincrono (se usa dentro del gesto del usuario) y nunca lanza hacia fuera:
 * ante cualquier error el href original se deja intacto.
 *
 * API publica: window.MDNContact = {
 *   configure, getConfig, newFolio, isValidFolio, canonicalType, placementOf, classify,
 *   prepare, productContext, shouldDedupe, dedupeKey, resetDedupe,
 *   encodeInvisible, decodeInvisible, languageOf, ALPHABET
 * }
 */
(function (root) {
  'use strict';

  var ALPHABET = '23456789BCDFGHJKMNPQRSTVWXYZ'; // 28 simbolos, sin vocales ni ambiguos
  var FOLIO_PREFIX = 'MN';
  var FOLIO_LEN = 5;
  var FOLIO_RE = /^MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}$/;
  // Mayor multiplo de 28 que cabe en 256: los bytes >= 224 se descartan (muestreo por rechazo).
  var REJECT_FROM = 224;
  var DEDUPE_MS = 1500;

  var ZWJ_START_END = '\u2060'; // WORD JOINER
  var BIT0 = '\u200b';          // ZERO WIDTH SPACE
  var BIT1 = '\u200c';          // ZERO WIDTH NON-JOINER

  function defaultConfig() {
    return {
      enabled: false,
      placements: '*',
      invisible: false,
      whatsapp: { url: 'https://wa.link/f28njw', number: '528135825559' },
      templates: {
        es: {
          product: 'Hola, me interesa: {producto}. Folio: {folio}',
          generic: 'Hola, quiero m\u00e1s informaci\u00f3n. Folio: {folio}'
        },
        pt: {
          product: 'Ol\u00e1, tenho interesse em: {produto}. C\u00f3digo: {folio}',
          generic: 'Ol\u00e1, quero mais informa\u00e7\u00f5es. C\u00f3digo: {folio}'
        }
      },
      mail: {
        es: 'Cotizaci\u00f3n web - Folio {folio}',
        pt: 'Cota\u00e7\u00e3o web - C\u00f3digo {folio}'
      }
    };
  }

  var cfg = defaultConfig();
  var seen = {}; // dedupe: key -> timestamp
  var recent = {}; // folios recientes por dedupeKey: key -> { folio, ts }

  /* ------------------------------------------------------------------ */
  /* Utilidades                                                          */
  /* ------------------------------------------------------------------ */

  function str(v) { return (v === null || v === undefined) ? '' : String(v); }

  function toBool(v) {
    return v === true || v === 1 || v === '1' || (typeof v === 'string' && v.toLowerCase() === 'true');
  }

  function isObj(v) { return !!v && typeof v === 'object'; }

  function normalizePlacements(p) {
    if (p === undefined || p === null || p === '') return '*';
    var list = Array.isArray(p) ? p : String(p).split(',');
    var out = [];
    for (var i = 0; i < list.length; i++) {
      var t = str(list[i]).trim().toLowerCase();
      if (!t) continue;
      if (t === '*') return '*';
      if (out.indexOf(t) === -1) out.push(t);
    }
    return out;
  }

  function mergeStrings(target, src, keys) {
    if (!isObj(src)) return;
    for (var i = 0; i < keys.length; i++) {
      if (typeof src[keys[i]] === 'string' && src[keys[i]]) target[keys[i]] = src[keys[i]];
    }
  }

  function configure(c) {
    try {
      var next = defaultConfig();
      if (isObj(c)) {
        next.enabled = toBool(c.enabled);
        next.invisible = toBool(c.invisible);
        next.placements = normalizePlacements(c.placements);
        if (isObj(c.whatsapp)) {
          mergeStrings(next.whatsapp, c.whatsapp, ['url', 'number']);
          // Modo prueba de atribucion (lo imprime el servidor): un numero vacio es VALIDO y significa
          // "sin numero" (wa.me/?text=...); si no, mergeStrings lo ignoraria y quedaria el numero real por defecto.
          if (toBool(c.testMode) && typeof c.whatsapp.number === 'string') next.whatsapp.number = c.whatsapp.number;
        }
        if (isObj(c.templates)) {
          var langs = ['es', 'pt'];
          for (var i = 0; i < langs.length; i++) {
            mergeStrings(next.templates[langs[i]], c.templates[langs[i]], ['product', 'generic']);
          }
        }
        if (isObj(c.mail)) mergeStrings(next.mail, c.mail, ['es', 'pt']);
      }
      cfg = next;
    } catch (e) { /* conserva la configuracion previa */ }
    return getConfig();
  }

  function getConfig() {
    try { return JSON.parse(JSON.stringify(cfg)); } catch (e) { return defaultConfig(); }
  }

  /* ------------------------------------------------------------------ */
  /* Folio                                                               */
  /* ------------------------------------------------------------------ */

  function randomBytes(n) {
    try {
      var c = root && root.crypto;
      if (c && typeof c.getRandomValues === 'function') {
        var buf = new Uint8Array(n);
        c.getRandomValues(buf);
        return buf;
      }
    } catch (e) { /* fallback */ }
    return null;
  }

  function newFolio() {
    var out = '';
    var guard = 0;
    while (out.length < FOLIO_LEN && guard < 1000) {
      guard++;
      var bytes = randomBytes(16);
      if (bytes) {
        for (var i = 0; i < bytes.length && out.length < FOLIO_LEN; i++) {
          if (bytes[i] >= REJECT_FROM) continue; // rechazo: evita sesgo de modulo
          out += ALPHABET.charAt(bytes[i] % ALPHABET.length);
        }
      } else {
        out += ALPHABET.charAt(Math.floor(Math.random() * ALPHABET.length));
      }
    }
    while (out.length < FOLIO_LEN) { // salvaguarda (no deberia ocurrir)
      out += ALPHABET.charAt(Math.floor(Math.random() * ALPHABET.length));
    }
    return FOLIO_PREFIX + out;
  }

  function isValidFolio(s) {
    return typeof s === 'string' && FOLIO_RE.test(s);
  }

  /* ------------------------------------------------------------------ */
  /* Folio invisible (apagado por defecto: solo si cfg.invisible)        */
  /* ------------------------------------------------------------------ */

  function encodeInvisible(folio) {
    var s = str(folio);
    var out = ZWJ_START_END;
    for (var i = 0; i < s.length; i++) {
      var code = s.charCodeAt(i) & 0xFF;
      for (var b = 7; b >= 0; b--) out += ((code >> b) & 1) ? BIT1 : BIT0;
    }
    return out + ZWJ_START_END;
  }

  function decodeInvisible(text) {
    try {
      var m = /\u2060([\u200b\u200c]*)\u2060/.exec(str(text));
      if (!m) return '';
      var bits = m[1];
      var out = '';
      for (var i = 0; i + 8 <= bits.length; i += 8) {
        var code = 0;
        for (var b = 0; b < 8; b++) code = (code << 1) | (bits.charAt(i + b) === BIT1 ? 1 : 0);
        out += String.fromCharCode(code);
      }
      return out;
    } catch (e) { return ''; }
  }

  /* ------------------------------------------------------------------ */
  /* Tipo canonico y placement                                           */
  /* ------------------------------------------------------------------ */

  // Espejo del canonicalType() de PHP (TrackConversion).
  function canonicalType(legacy) {
    var t = str(legacy).trim().toLowerCase();
    if (!t) return '';
    if (t.indexOf('whatsapp') === 0) return 'whatsapp_click';
    if (t.indexOf('telefono') === 0 || t === 'phone_click') return 'phone_click';
    if (t.indexOf('correo') === 0 || t === 'email_click') return 'email_click';
    return str(legacy).trim();
  }

  var KIND_CANON = { whatsapp: 'whatsapp_click', tel: 'phone_click', mailto: 'email_click' };

  function readAttr(el, name, camel) {
    try {
      if (el && typeof el.getAttribute === 'function') {
        var v = el.getAttribute(name);
        if (v !== null && v !== undefined) return String(v);
        return '';
      }
      if (el && el.dataset && camel && el.dataset[camel] !== undefined) return String(el.dataset[camel]);
    } catch (e) { /* ignore */ }
    return '';
  }

  function hasAttr(el, name, camel) {
    try {
      if (el && typeof el.getAttribute === 'function') return el.getAttribute(name) !== null && el.getAttribute(name) !== undefined;
      return !!(el && el.dataset && camel && el.dataset[camel] !== undefined);
    } catch (e) { return false; }
  }

  function collapse(s) {
    return str(s).replace(/\s+/g, ' ').trim();
  }

  function readText(el) {
    try { return str(el && el.textContent); } catch (e) { return ''; }
  }

  function writeData(el, name, camel, value) {
    try {
      if (el.dataset) { el.dataset[camel] = value; return true; }
    } catch (e) { /* cae a setAttribute */ }
    try { el.setAttribute(name, value); return true; } catch (e2) { return false; }
  }

  function currentHref(el) {
    var h = readAttr(el, 'href');
    if (h) return h;
    try { return el && typeof el.href === 'string' ? el.href : ''; } catch (e) { return ''; }
  }

  function placementOf(anchor) {
    try {
      // 1) data-placement del propio enlace o de un ancestro.
      var holder = null;
      try {
        if (anchor && typeof anchor.closest === 'function') holder = anchor.closest('[data-placement]');
      } catch (e) { holder = null; }
      if (!holder) {
        var node = anchor, hops = 0;
        while (node && hops < 50) {
          if (readAttr(node, 'data-placement', 'placement')) { holder = node; break; }
          node = node.parentNode || node.parentElement || null;
          hops++;
        }
      }
      if (holder) {
        var dp = readAttr(holder, 'data-placement', 'placement').trim();
        if (dp) return dp;
      }

      // 2) A partir del data-type legacy sin prefijo de canal.
      var dt = readAttr(anchor, 'data-type', 'type').trim().toLowerCase();
      if (!dt) return '';
      if (dt === 'whatsapp' || dt === 'telefono') return 'producto';
      var m = /^(?:whatsapp|telefono|correo)[_-](.+)$/.exec(dt);
      if (!m) return '';
      var rest = m[1];
      if (rest === 'click') return '';
      return rest;
    } catch (e) {
      return '';
    }
  }

  /* ------------------------------------------------------------------ */
  /* Clasificacion                                                       */
  /* ------------------------------------------------------------------ */

  var WA_HOST_RE = /^(?:https?:)?\/\/(?:www\.)?(wa\.link|wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)(?=[\/?#]|$)/i;

  function digits(s) { return str(s).replace(/\D+/g, ''); }

  function queryParam(href, name) {
    var q = str(href).replace(/#.*$/, '');
    var i = q.indexOf('?');
    if (i === -1) return null;
    var pairs = q.slice(i + 1).split('&');
    for (var p = 0; p < pairs.length; p++) {
      var eq = pairs[p].indexOf('=');
      var k = eq === -1 ? pairs[p] : pairs[p].slice(0, eq);
      var key;
      try { key = decodeURIComponent(k.replace(/\+/g, ' ')); } catch (e) { key = k; }
      if (key !== name) continue;
      var raw = eq === -1 ? '' : pairs[p].slice(eq + 1);
      try { return decodeURIComponent(raw.replace(/\+/g, ' ')); } catch (e2) { return raw; }
    }
    return null;
  }

  // 'whatsapp' | 'mailto' | 'tel' | ''
  function kindOf(href) {
    var h = str(href).trim();
    if (/^mailto:/i.test(h)) return 'mailto';
    if (/^tel:/i.test(h)) return 'tel';
    if (WA_HOST_RE.test(h) || /^whatsapp:\/\//i.test(h)) return 'whatsapp';
    return '';
  }

  function configuredTarget() {
    var u = str(cfg.whatsapp && cfg.whatsapp.url).trim();
    var m = /^(?:https?:)?\/\/(?:www\.)?wa\.link\/([^\/?#]+)/i.exec(u);
    return { slug: m ? m[1] : '', number: digits(cfg.whatsapp && cfg.whatsapp.number) };
  }

  // true si el enlace de WhatsApp apunta al destino configurado.
  function isConfiguredWhatsApp(href) {
    var h = str(href).trim();
    var t = configuredTarget();
    var host = WA_HOST_RE.exec(h);
    host = host ? host[1].toLowerCase() : '';

    if (host === 'wa.link') {
      var s = /^(?:https?:)?\/\/(?:www\.)?wa\.link\/([^\/?#]+)/i.exec(h);
      return !!(s && t.slug && s[1] === t.slug);
    }
    if (host === 'wa.me') {
      var n = /^(?:https?:)?\/\/(?:www\.)?wa\.me\/\+?([0-9]+)(?=[\/?#]|$)/i.exec(h);
      return !!(n && t.number && n[1] === t.number);
    }
    // api.whatsapp.com / web.whatsapp.com / whatsapp://  -> por el parametro phone
    var phone = digits(queryParam(h, 'phone'));
    return !!(phone && t.number && phone === t.number);
  }

  function classify(anchor) {
    try {
      if (!anchor) return null;
      var orig = readAttr(anchor, 'data-mdn-orig-href', 'mdnOrigHref');
      var origHref = orig || currentHref(anchor);
      var kind = kindOf(origHref);
      if (!kind) return null;

      var legacyType = readAttr(anchor, 'data-type', 'type').trim();
      var canon = canonicalType(legacyType);
      var kindCanon = KIND_CANON[kind];
      var canonical = (canon === 'whatsapp_click' || canon === 'phone_click' || canon === 'email_click')
        ? canon : kindCanon;

      var skip = false;
      if (hasAttr(anchor, 'data-mdn-event', 'mdnEvent')) skip = true;
      if (kind === 'whatsapp' && !isConfiguredWhatsApp(origHref)) skip = true;

      var placement = placementOf(anchor);
      var out = {
        kind: kind,
        origHref: origHref,
        legacyType: legacyType,
        canonicalType: canonical,
        placement: placement,
        skip: skip
      };
      // Sin placement, el texto del enlace distingue tarjetas en dedupeKey (solo en ese caso).
      if (!placement) out.text = collapse(readText(anchor)).slice(0, 30);
      return out;
    } catch (e) {
      return null;
    }
  }

  /* ------------------------------------------------------------------ */
  /* Prepare                                                             */
  /* ------------------------------------------------------------------ */

  function placementAllowed(placement) {
    var p = cfg.placements;
    if (p === '*') return true;
    if (!Array.isArray(p)) return false;
    return p.indexOf(str(placement).trim().toLowerCase()) !== -1;
  }

  function languageOf(ctx) {
    try {
      if (ctx && typeof ctx.lang === 'string' && ctx.lang) return /^pt/i.test(ctx.lang.trim()) ? 'pt' : 'es';
      var doc = (ctx && ctx.doc) || (root && root.document) || null;
      var dl = '';
      try { dl = str(doc && doc.documentElement && doc.documentElement.lang); } catch (e) { dl = ''; }
      if (/^pt/i.test(dl.trim())) return 'pt';
      var path = '';
      try { path = str(root && root.location && root.location.pathname); } catch (e2) { path = ''; }
      if (path.indexOf('/br/') === 0 || path === '/br') return 'pt';
    } catch (e3) { /* ignore */ }
    return 'es';
  }

  function normalizeProduct(p) {
    if (!isObj(p)) return null;
    var name = collapse(p.name);
    var sku = collapse(p.sku);
    if (!name && !sku) return null;
    return { name: name, sku: sku };
  }

  function fill(tpl, vars) {
    return str(tpl).replace(/\{(folio|producto|produto|product|sku)\}/g, function (all, k) {
      return str(vars[k]);
    });
  }

  function buildWhatsApp(origHref, folio, lang, product) {
    var existing = queryParam(origHref, 'text');
    var msg;
    if (existing !== null && existing.trim() !== '') {
      msg = existing.replace(/\s+$/, '') + (lang === 'pt' ? ' C\u00f3digo: ' : ' Folio: ') + folio;
    } else {
      var t = cfg.templates[lang] || cfg.templates.es;
      var vars = {
        folio: folio,
        producto: product ? product.name : '',
        produto: product ? product.name : '',
        product: product ? product.name : '',
        sku: product ? product.sku : ''
      };
      msg = fill(product && product.name ? t.product : t.generic, vars);
    }
    if (cfg.invisible) msg += encodeInvisible(folio);
    var number = digits(cfg.whatsapp.number);
    return 'https://wa.me/' + number + '?text=' + encodeURIComponent(msg);
  }

  function buildMailto(origHref, folio, lang) {
    var subject = fill(cfg.mail[lang] || cfg.mail.es, { folio: folio });
    var encoded = 'subject=' + encodeURIComponent(subject);
    var hash = '';
    var h = str(origHref);
    var hi = h.indexOf('#');
    if (hi !== -1) { hash = h.slice(hi); h = h.slice(0, hi); }
    var qi = h.indexOf('?');
    var base = qi === -1 ? h : h.slice(0, qi);
    var query = qi === -1 ? '' : h.slice(qi + 1);
    var parts = query ? query.split('&') : [];
    var out = [];
    var replaced = false;
    for (var i = 0; i < parts.length; i++) {
      if (!parts[i]) continue;
      var eq = parts[i].indexOf('=');
      var key = (eq === -1 ? parts[i] : parts[i].slice(0, eq)).toLowerCase();
      if (key === 'subject') {
        if (!replaced) { out.push(encoded); replaced = true; }
        continue;
      }
      out.push(parts[i]);
    }
    if (!replaced) out.push(encoded);
    return base + '?' + out.join('&') + hash;
  }

  function prepare(anchor, ctx) {
    var c = null;
    try {
      c = classify(anchor);
      if (!c) return null;
      if (!cfg.enabled || c.skip || !placementAllowed(c.placement)) return c;

      if (c.kind === 'tel') {
        c.folio = null;
        return c;
      }

      var lang = languageOf(ctx);
      var product = normalizeProduct(ctx && ctx.product);
      var folio = folioFor(dedupeKey(c));
      var href = c.kind === 'whatsapp'
        ? buildWhatsApp(c.origHref, folio, lang, product)
        : buildMailto(c.origHref, folio, lang);

      // setAttribute (no anchor.href): en <a> de SVG la propiedad href es de solo lectura.
      // Si lanza, no se ha cambiado nada y el catch deja el href original.
      var hadOrig = !!readAttr(anchor, 'data-mdn-orig-href', 'mdnOrigHref');
      anchor.setAttribute('href', href);
      // Original solo la primera vez (y solo despues de reescribir); luego se reconstruye siempre desde el.
      if (!hadOrig && !writeData(anchor, 'data-mdn-orig-href', 'mdnOrigHref', c.origHref)) {
        try { anchor.setAttribute('href', c.origHref); } catch (e0) { /* ignore */ }
        return c;
      }
      writeData(anchor, 'data-mdn-folio', 'mdnFolio', folio);
      writeData(anchor, 'data-mdn-placement', 'mdnPlacement', c.placement);

      var res = {
        kind: c.kind,
        folio: folio,
        origHref: c.origHref,
        href: href,
        legacyType: c.legacyType,
        canonicalType: c.canonicalType,
        placement: c.placement,
        product: product
      };
      if (c.text !== undefined) res.text = c.text;
      return res;
    } catch (e) {
      return c || null;
    }
  }

  /* ------------------------------------------------------------------ */
  /* Producto (JSON-LD / microdatos)                                     */
  /* ------------------------------------------------------------------ */

  function isProductType(t) {
    if (Array.isArray(t)) {
      for (var i = 0; i < t.length; i++) if (isProductType(t[i])) return true;
      return false;
    }
    return typeof t === 'string' && /(^|\/)Product$/.test(t);
  }

  function findProduct(node, depth) {
    if (!node || typeof node !== 'object' || depth > 6) return null;
    if (Array.isArray(node)) {
      for (var i = 0; i < node.length; i++) {
        var f = findProduct(node[i], depth + 1);
        if (f) return f;
      }
      return null;
    }
    if (isProductType(node['@type'])) return node;
    if (node['@graph']) return findProduct(node['@graph'], depth + 1);
    return null;
  }

  var ENTITIES = { amp: '&', quot: '"', apos: "'", lt: '<', gt: '>', nbsp: ' ' };

  function decodeEntities(s) {
    return str(s).replace(/&(#x[0-9a-f]+|#[0-9]+|[a-z]+);/gi, function (all, body) {
      try {
        if (body.charAt(0) === '#') {
          var code = body.charAt(1).toLowerCase() === 'x' ? parseInt(body.slice(2), 16) : parseInt(body.slice(1), 10);
          return (code > 0 && code <= 0x10FFFF) ? String.fromCodePoint(code) : all;
        }
      } catch (e) { return all; }
      var v = ENTITIES[body.toLowerCase()];
      return v === undefined ? all : v;
    });
  }

  // Entidades HTML -> texto, quita barras invertidas que preceden comillas
  // (addslashes), y colapsa espacios y saltos de linea.
  function cleanName(s) {
    var t = decodeEntities(str(s));
    t = t.replace(/\\(["'])/g, '$1');
    return collapse(t);
  }

  function textOf(el) {
    if (!el) return '';
    var v = '';
    try {
      if (typeof el.getAttribute === 'function') v = str(el.getAttribute('content')).trim();
      if (!v) v = str(el.textContent).trim();
    } catch (e) { v = ''; }
    return v;
  }

  function jsonUnescape(raw) {
    try { return JSON.parse('"' + raw.replace(/\r?\n/g, '\\n') + '"'); } catch (e) { /* manual */ }
    return raw.replace(/\\(["\\\/])/g, '$1').replace(/\\[nrt]/g, ' ');
  }

  // JSON-LD invalido (p. ej. addslashes + entidades, o saltos de linea crudos): se recorre el
  // texto, a profundidad 1 del objeto Product, y se extraen "name" y "sku".
  function extractProductFromRaw(raw) {
    var out = { name: '', sku: '' };
    var m = /"@type"\s*:\s*"Product"/.exec(raw);
    if (!m) return null;
    var start = raw.lastIndexOf('{', m.index);
    if (start === -1) start = 0;
    var depth = 0, i = start, n = raw.length;
    while (i < n) {
      var ch = raw.charAt(i);
      if (ch === '{' || ch === '[') { depth++; i++; continue; }
      if (ch === '}' || ch === ']') { depth--; i++; if (depth <= 0) break; continue; }
      if (ch !== '"') { i++; continue; }
      // cadena: leer hasta la comilla de cierre (tolerante a barras invertidas)
      var j = i + 1;
      while (j < n && raw.charAt(j) !== '"') { if (raw.charAt(j) === '\\') j++; j++; }
      var token = raw.slice(i + 1, j);
      i = j + 1;
      if (depth !== 1) continue;
      var rest = /^\s*:\s*/.exec(raw.slice(i, i + 20));
      if (!rest) continue;
      if (token !== 'name' && token !== 'sku') continue;
      var k = i + rest[0].length;
      if (raw.charAt(k) !== '"') continue;
      var e = k + 1;
      while (e < n && raw.charAt(e) !== '"') { if (raw.charAt(e) === '\\') e++; e++; }
      var val = jsonUnescape(raw.slice(k + 1, e));
      if (!out[token]) out[token] = val;
      i = e + 1;
    }
    return out;
  }

  function productFromJsonLd(doc) {
    var scripts = doc.querySelectorAll('script[type="application/ld+json"]');
    for (var i = 0; i < scripts.length; i++) {
      var raw = str(scripts[i].textContent);
      var data = null, ok = false;
      try { data = JSON.parse(raw); ok = true; } catch (e) { ok = false; }
      if (ok) {
        var p = findProduct(data, 0);
        if (p) return { name: str(p.name), sku: str(p.sku) };
      } else {
        var x = extractProductFromRaw(raw);
        if (x && (x.name || x.sku)) return x;
      }
    }
    return null;
  }

  function productContext(doc) {
    var out = { name: '', sku: '' };
    try {
      doc = doc || (root && root.document);
      if (!doc || typeof doc.querySelectorAll !== 'function') return out;

      try {
        var ld = productFromJsonLd(doc);
        if (ld) { out.name = cleanName(ld.name); out.sku = cleanName(ld.sku); }
      } catch (e1) { /* sigue con el respaldo */ }

      if (!out.sku) out.sku = cleanName(textOf(doc.querySelector('[itemprop="sku"]')));
      if (!out.name) {
        // Nunca el primer [itemprop="name"] del documento: suele ser la marca.
        var h = doc.querySelector('h1[itemprop="name"]') || doc.querySelector('h1.product-title');
        if (!h) {
          var h1s = doc.querySelectorAll('h1');
          if (h1s && h1s.length === 1) h = h1s[0];
        }
        out.name = cleanName(textOf(h));
      }
    } catch (e2) { /* devuelve lo reunido */ }
    return out;
  }

  /* ------------------------------------------------------------------ */
  /* Dedupe compartido (master + mdn-events)                             */
  /* ------------------------------------------------------------------ */

  // canonicalType|origHref|placement. Sin placement se usa el texto del enlace
  // (primeros 30 caracteres) para que dos tarjetas distintas no colisionen.
  function dedupeKey(c) {
    c = c || {};
    var third = str(c.placement);
    if (!third) third = 'txt:' + collapse(c.text).slice(0, 30);
    return str(c.canonicalType) + '|' + str(c.origHref) + '|' + third;
  }

  // Misma key en menos de DEDUPE_MS -> reutiliza el folio (el evento duplicado se descarta
  // en shouldDedupe, asi el href final siempre lleva el folio del evento que si se registro).
  function folioFor(key) {
    var now = Date.now();
    var r = recent[key];
    if (r && now - r.ts >= 0 && now - r.ts < DEDUPE_MS) return r.folio;
    var folio = newFolio();
    recent[key] = { folio: folio, ts: now };
    var n = 0, k;
    for (k in recent) { if (Object.prototype.hasOwnProperty.call(recent, k)) n++; }
    if (n > 100) {
      for (k in recent) {
        if (Object.prototype.hasOwnProperty.call(recent, k) && now - recent[k].ts >= DEDUPE_MS) delete recent[k];
      }
    }
    return folio;
  }

  function shouldDedupe(key, now) {
    try {
      var t = typeof now === 'number' ? now : Date.now();
      var k = str(key);
      var prev = seen[k];
      if (typeof prev === 'number' && t - prev >= 0 && t - prev < DEDUPE_MS) return true;
      seen[k] = t;
      // Limpieza ocasional para que el mapa no crezca.
      var n = 0, name;
      for (name in seen) { if (Object.prototype.hasOwnProperty.call(seen, name)) n++; }
      if (n > 100) {
        for (name in seen) {
          if (Object.prototype.hasOwnProperty.call(seen, name) && t - seen[name] >= DEDUPE_MS) delete seen[name];
        }
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  function resetDedupe() { seen = {}; recent = {}; }

  /* ------------------------------------------------------------------ */
  /* Exportes                                                            */
  /* ------------------------------------------------------------------ */

  var api = {
    configure: configure,
    getConfig: getConfig,
    newFolio: newFolio,
    isValidFolio: isValidFolio,
    canonicalType: canonicalType,
    placementOf: placementOf,
    classify: classify,
    prepare: prepare,
    productContext: productContext,
    shouldDedupe: shouldDedupe,
    dedupeKey: dedupeKey,
    resetDedupe: resetDedupe,
    encodeInvisible: encodeInvisible,
    decodeInvisible: decodeInvisible,
    languageOf: languageOf,
    ALPHABET: ALPHABET,
    DEDUPE_MS: DEDUPE_MS
  };

  if (root && typeof root === 'object' && root.document) {
    root.MDNContact = api;
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = api;
  }
})(typeof window !== 'undefined' ? window : (typeof globalThis !== 'undefined' ? globalThis : this));

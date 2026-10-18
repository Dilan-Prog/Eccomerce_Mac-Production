/*!
 * MDN Attribution
 * Captura first-touch / last-touch (gclid, gbraid, wbraid, fbclid, utm_*),
 * referrer externo, landing page y session id. Persiste en cookie propia
 * (primera parte) + localStorage. Sin dependencias.
 *
 * API publica: window.MDNAttribution = { capture, get, getMeta, eventAt, newEventId }
 */
(function (root) {
  'use strict';

  var STORAGE_KEY = 'mdn_attr';
  var STATE_VERSION = 1;
  var DAY_MS = 24 * 60 * 60 * 1000;
  var LIFETIME_MS = 90 * DAY_MS;
  var SESSION_TIMEOUT_MS = 30 * 60 * 1000;
  var MAX_VALUE_LEN = 255;
  var MAX_COOKIE_LEN = 3500;

  var CLICK_IDS = ['gclid', 'gbraid', 'wbraid', 'fbclid'];
  var GOOGLE_IDS = ['gclid', 'gbraid', 'wbraid'];
  var UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
  var PARAM_KEYS = CLICK_IDS.concat(UTM_KEYS);
  // Claves planas de localStorage del sistema anterior (se usan para sembrar el estado la primera vez).
  var LEGACY_KEYS = ['gclid', 'utm_source', 'utm_medium', 'utm_campaign'];

  // Estado en memoria: session_id generado cuando aun no hay estado guardado
  // (page_visit y el primer clic comparten sesion en vez de uno nuevo por llamada).
  var memoSessionId = '';

  var FLAT_KEYS = [
    'gclid', 'gbraid', 'wbraid', 'fbclid',
    'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
    'landing_page', 'referrer', 'session_id', 'captured_at'
  ];

  /* ------------------------------------------------------------------ */
  /* Helpers puros (sin DOM)                                             */
  /* ------------------------------------------------------------------ */

  function newUuid() {
    try {
      if (root && root.crypto && typeof root.crypto.randomUUID === 'function') {
        return root.crypto.randomUUID();
      }
    } catch (e) { /* fallback */ }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = (Math.random() * 16) | 0;
      var v = c === 'x' ? r : ((r & 0x3) | 0x8);
      return v.toString(16);
    });
  }

  function cleanValue(v) {
    if (v === null || v === undefined) return '';
    var s = String(v).trim();
    if (!s) return '';
    return s.length > MAX_VALUE_LEN ? s.slice(0, MAX_VALUE_LEN) : s;
  }

  /**
   * Valida el formato de un identificador de clic.
   *  - gclid: [A-Za-z0-9_-]{20,255}, sin prefijo "gtm" (cualquier caso) y no solo digitos.
   *  - gbraid / wbraid / fbclid: [A-Za-z0-9_-]{10,255}.
   * No recorta espacios: un valor con espacios es invalido.
   */
  function isValidClickId(value, kind) {
    if (typeof value !== 'string') return false;
    if (kind === 'gclid') {
      if (!/^[A-Za-z0-9_-]{20,255}$/.test(value)) return false;
      if (/^gtm/i.test(value)) return false;
      if (/^[0-9]+$/.test(value)) return false;
      return true;
    }
    if (kind === 'gbraid' || kind === 'wbraid' || kind === 'fbclid') {
      return /^[A-Za-z0-9_-]{10,255}$/.test(value);
    }
    return false;
  }

  function isClickIdKey(key) {
    return CLICK_IDS.indexOf(key) !== -1;
  }

  // Devuelve solo los parametros presentes y no vacios.
  function parseParams(search) {
    var out = {};
    if (!search) return out;
    var qs = String(search);
    if (qs.charAt(0) === '?') qs = qs.slice(1);
    if (!qs) return out;
    var pairs = qs.split('&');
    for (var i = 0; i < pairs.length; i++) {
      if (!pairs[i]) continue;
      var idx = pairs[i].indexOf('=');
      var rawKey = idx === -1 ? pairs[i] : pairs[i].slice(0, idx);
      var rawVal = idx === -1 ? '' : pairs[i].slice(idx + 1);
      var key, val;
      try { key = decodeURIComponent(rawKey.replace(/\+/g, ' ')); } catch (e) { key = rawKey; }
      if (PARAM_KEYS.indexOf(key) === -1) continue;
      if (Object.prototype.hasOwnProperty.call(out, key)) continue; // primera aparicion gana
      try { val = decodeURIComponent(rawVal.replace(/\+/g, ' ')); } catch (e) { val = rawVal; }
      val = cleanValue(val);
      if (!val) continue;
      if (isClickIdKey(key) && !isValidClickId(val, key)) continue; // click id invalido: se descarta
      out[key] = val;
    }
    return out;
  }

  function stripWww(host) {
    return String(host || '').toLowerCase().replace(/^www\./, '');
  }

  // Referrer solo si es de un host distinto al del sitio.
  function externalReferrer(referrer, hostname) {
    if (!referrer) return '';
    var m = /^[a-z][a-z0-9+.\-]*:\/\/([^\/?#:]+)/i.exec(String(referrer));
    if (!m) return '';
    var refHost = stripWww(m[1]);
    if (!refHost || refHost === stripWww(hostname)) return '';
    return cleanValue(referrer);
  }

  function emptyFirst() {
    return {
      landing_page: '', referrer: '',
      utm_source: '', utm_medium: '', utm_campaign: '', utm_term: '', utm_content: '',
      ts: 0
    };
  }

  function emptyLast() {
    var o = { gclid: '', gbraid: '', wbraid: '', fbclid: '' };
    for (var i = 0; i < UTM_KEYS.length; i++) o[UTM_KEYS[i]] = '';
    o.ts = 0;
    // Aditivo: marca de tiempo por click id y de la ultima llegada de utm_source.
    o.cts = { gclid: 0, gbraid: 0, wbraid: 0, fbclid: 0 };
    o.utm_ts = 0;
    return o;
  }

  function isValidState(s) {
    return !!s && typeof s === 'object' && s.v === STATE_VERSION &&
      s.first && typeof s.first === 'object' && s.last && typeof s.last === 'object';
  }

  function hasAny(params, keys) {
    for (var i = 0; i < keys.length; i++) {
      if (params[keys[i]]) return true;
    }
    return false;
  }

  /**
   * Fusiona los parametros de la URL actual en el estado.
   * ctx = { now, pathname, referrer, hostname }  (opcional ctx.uuid para tests)
   * No muta `state`; devuelve un estado nuevo.
   */
  function mergeAttribution(state, params, ctx) {
    ctx = ctx || {};
    params = params || {};
    var now = typeof ctx.now === 'number' ? ctx.now : Date.now();

    var s = isValidState(state) ? JSON.parse(JSON.stringify(state)) : null;

    // Atribucion vencida: empezar de cero.
    if (s && typeof s.expires === 'number' && now > s.expires) s = null;

    var created = false;
    if (!s) {
      created = true;
      s = {
        v: STATE_VERSION,
        first: emptyFirst(),
        last: emptyLast(),
        sid: '',
        sid_ts: 0,
        expires: now + LIFETIME_MS
      };
      s.first.landing_page = cleanValue(ctx.pathname || '');
      s.first.referrer = externalReferrer(ctx.referrer, ctx.hostname);
      for (var u = 0; u < UTM_KEYS.length; u++) {
        if (params[UTM_KEYS[u]]) s.first[UTM_KEYS[u]] = params[UTM_KEYS[u]];
      }
      s.first.ts = now;

      // Visitante que ya traia datos guardados por el sistema anterior (claves planas):
      // se conservan en lugar de perder su gclid/UTM y su primera pagina de entrada.
      var legacy = ctx.legacy || null;
      if (legacy) {
        if (legacy.landing_page) s.first.landing_page = cleanValue(legacy.landing_page);
        var seeded = false;
        for (var lk = 0; lk < LEGACY_KEYS.length; lk++) {
          var lv = cleanValue(legacy[LEGACY_KEYS[lk]] || '');
          if (!lv) continue;
          if (isClickIdKey(LEGACY_KEYS[lk]) && !isValidClickId(lv, LEGACY_KEYS[lk])) continue;
          s.last[LEGACY_KEYS[lk]] = lv;
          seeded = true;
          if (isClickIdKey(LEGACY_KEYS[lk])) s.last.cts[LEGACY_KEYS[lk]] = now;
          if (LEGACY_KEYS[lk] === 'utm_source') s.last.utm_ts = now;
        }
        if (seeded) s.last.ts = now;
      }
    }

    // Completar campos faltantes por si el estado viene incompleto.
    var base = emptyLast();
    for (var k in base) { if (s.last[k] === undefined) s.last[k] = base[k]; }
    if (!s.last.cts || typeof s.last.cts !== 'object') s.last.cts = base.cts;
    for (var ci = 0; ci < CLICK_IDS.length; ci++) {
      if (typeof s.last.cts[CLICK_IDS[ci]] !== 'number') s.last.cts[CLICK_IDS[ci]] = 0;
    }
    if (typeof s.last.utm_ts !== 'number') s.last.utm_ts = 0;

    // Last touch.
    var hasNew = hasAny(params, PARAM_KEYS);
    if (hasNew) {
      // Los ids de Google son mutuamente excluyentes: uno nuevo reemplaza a los demas.
      if (hasAny(params, GOOGLE_IDS)) {
        for (var g = 0; g < GOOGLE_IDS.length; g++) {
          s.last[GOOGLE_IDS[g]] = '';
          s.last.cts[GOOGLE_IDS[g]] = 0;
        }
      }
      for (var p = 0; p < PARAM_KEYS.length; p++) {
        var key = PARAM_KEYS[p];
        if (params[key]) {
          s.last[key] = params[key];
          if (isClickIdKey(key)) s.last.cts[key] = now;
        }
      }
      if (params.utm_source) s.last.utm_ts = now;
      s.last.ts = now;
      s.expires = now + LIFETIME_MS; // refrescar vigencia
    }

    // Sesion.
    if (!s.sid || typeof s.sid_ts !== 'number' || (now - s.sid_ts) > SESSION_TIMEOUT_MS) {
      s.sid = ctx.uuid || newUuid();
    }
    s.sid_ts = now;

    return s;
  }

  // Estado -> objeto plano de strings con las 13 llaves fijas.
  function flatten(state) {
    var out = {};
    for (var i = 0; i < FLAT_KEYS.length; i++) out[FLAT_KEYS[i]] = '';
    if (!isValidState(state)) return out;

    var first = state.first || {};
    var last = state.last || {};
    var str = function (v) { return (v === null || v === undefined) ? '' : String(v); };

    for (var c = 0; c < CLICK_IDS.length; c++) out[CLICK_IDS[c]] = str(last[CLICK_IDS[c]]);
    for (var u = 0; u < UTM_KEYS.length; u++) {
      out[UTM_KEYS[u]] = str(last[UTM_KEYS[u]]) || str(first[UTM_KEYS[u]]);
    }
    out.landing_page = str(first.landing_page);
    out.referrer = str(first.referrer);
    out.session_id = str(state.sid);

    var ts = last.ts || first.ts;
    if (ts) {
      try { out.captured_at = new Date(ts).toISOString(); } catch (e) { out.captured_at = ''; }
    }
    return out;
  }

  function isExpired(state, now) {
    return !!state && typeof state.expires === 'number' &&
      (typeof now === 'number' ? now : Date.now()) > state.expires;
  }

  // utm_source "no-google": valor no vacio que no contiene 'google' (sin distinguir mayusculas).
  function isNonGoogleSource(v) {
    var s = (v === null || v === undefined) ? '' : String(v).trim();
    return s !== '' && s.toLowerCase().indexOf('google') === -1;
  }

  function isoOrEmpty(ts) {
    if (!ts || typeof ts !== 'number') return '';
    try { return new Date(ts).toISOString(); } catch (e) { return ''; }
  }

  /**
   * Metadatos de atribucion derivados del estado (todo strings):
   *  click_id_at     ISO UTC del click id mas reciente ('' si no hay / sin marca de tiempo)
   *  click_channel   'google' | 'meta' | 'none'
   *  touch_conflict  '1' si hay click id de Google y un toque posterior de otro canal
   *  attribution_at  captured_at actual (ISO)
   * Los click ids con formato invalido se ignoran. Un estado vencido o ausente no tiene click ids.
   */
  function computeMeta(state, now) {
    var out = { click_id_at: '', click_channel: 'none', touch_conflict: '0', attribution_at: '' };
    if (!isValidState(state) || isExpired(state, now)) return out;
    out.attribution_at = flatten(state).captured_at;

    var last = state.last || {};
    var cts = (last.cts && typeof last.cts === 'object') ? last.cts : {};
    var tsOf = function (k) { return typeof cts[k] === 'number' ? cts[k] : 0; };

    var best = null, bestTs = -1;
    var googleTs = -1;
    for (var i = 0; i < CLICK_IDS.length; i++) {
      var k = CLICK_IDS[i];
      if (!isValidClickId(last[k], k)) continue;
      var t = tsOf(k);
      if (t > bestTs) { best = k; bestTs = t; } // empate: gana el primero (Google antes que Meta)
      if (GOOGLE_IDS.indexOf(k) !== -1 && t > googleTs) googleTs = t;
    }
    if (!best) return out;

    out.click_id_at = bestTs > 0 ? isoOrEmpty(bestTs) : '';
    out.click_channel = GOOGLE_IDS.indexOf(best) !== -1 ? 'google' : 'meta';

    if (googleTs >= 0) {
      var fbOk = isValidClickId(last.fbclid, 'fbclid') && tsOf('fbclid') > googleTs;
      var utmTs = typeof last.utm_ts === 'number' ? last.utm_ts : 0;
      var utmOk = isNonGoogleSource(last.utm_source) && utmTs > googleTs;
      if (fbOk || utmOk) out.touch_conflict = '1';
    }
    return out;
  }

  function cookieDomainFor(hostname) {
    var h = String(hostname || '').toLowerCase();
    if (h === 'macdelnorte.com' || /\.macdelnorte\.com$/.test(h)) return '.macdelnorte.com';
    return '';
  }

  // Serializa para cookie manteniendola pequena (quita referrer, luego landing_page).
  function serializeForCookie(state) {
    var json = JSON.stringify(state);
    if (encodeURIComponent(json).length <= MAX_COOKIE_LEN) return json;
    var slim = JSON.parse(json);
    slim.first.referrer = '';
    json = JSON.stringify(slim);
    if (encodeURIComponent(json).length <= MAX_COOKIE_LEN) return json;
    slim.first.landing_page = '';
    return JSON.stringify(slim);
  }

  /* ------------------------------------------------------------------ */
  /* Capa de navegador (DOM / storage)                                   */
  /* ------------------------------------------------------------------ */

  function readCookie() {
    try {
      var all = document.cookie ? document.cookie.split('; ') : [];
      for (var i = 0; i < all.length; i++) {
        var eq = all[i].indexOf('=');
        if (eq === -1) continue;
        if (all[i].slice(0, eq) === STORAGE_KEY) {
          return JSON.parse(decodeURIComponent(all[i].slice(eq + 1)));
        }
      }
    } catch (e) { /* ignore */ }
    return null;
  }

  function readLocal() {
    try {
      var raw = root.localStorage.getItem(STORAGE_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (e) { return null; }
  }

  function readState() {
    var s = readCookie();
    if (isValidState(s)) return s;
    s = readLocal();
    return isValidState(s) ? s : null;
  }

  function writeState(state) {
    try {
      var cookie = STORAGE_KEY + '=' + encodeURIComponent(serializeForCookie(state)) +
        '; path=/; max-age=' + Math.floor(LIFETIME_MS / 1000) + '; SameSite=Lax';
      var domain = cookieDomainFor(root.location.hostname);
      if (domain) cookie += '; domain=' + domain;
      if (root.location.protocol === 'https:') cookie += '; Secure';
      document.cookie = cookie;
    } catch (e) { /* ignore */ }
    try {
      root.localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) { /* ignore */ }
  }

  // Claves planas heredadas (GTM / codigo anterior).
  function writeLegacy(params, pathname) {
    try {
      var ls = root.localStorage;
      var legacy = ['gclid', 'utm_source', 'utm_medium', 'utm_campaign'];
      for (var i = 0; i < legacy.length; i++) {
        var v = params[legacy[i]];
        if (!v) continue;
        if (isClickIdKey(legacy[i]) && !isValidClickId(v, legacy[i])) continue;
        ls.setItem(legacy[i], v);
      }
      if (!ls.getItem('landing_page')) ls.setItem('landing_page', pathname);
    } catch (e) { /* ignore */ }
  }

  function sessionFallback() {
    if (!memoSessionId) {
      try { memoSessionId = newUuid(); } catch (e) { memoSessionId = ''; }
    }
    return memoSessionId;
  }

  function emptyWithSession() {
    var out = flatten(null);
    out.session_id = sessionFallback();
    return out;
  }

  function get() {
    try {
      var s = readState();
      if (!s || isExpired(s)) return emptyWithSession();
      return flatten(s);
    } catch (e) {
      try { return emptyWithSession(); } catch (e2) { return flatten(null); }
    }
  }

  function getMeta() {
    try {
      return computeMeta(readState());
    } catch (e) {
      return { click_id_at: '', click_channel: 'none', touch_conflict: '0', attribution_at: '' };
    }
  }

  function eventAt() {
    return new Date().toISOString();
  }

  function capture() {
    try {
      var loc = root.location;
      var params = parseParams(loc.search);
      var existing = readState();
      var legacy = null;
      if (!existing) {
        try {
          var ls = root.localStorage;
          legacy = { landing_page: ls.getItem('landing_page') || '' };
          for (var i = 0; i < LEGACY_KEYS.length; i++) legacy[LEGACY_KEYS[i]] = ls.getItem(LEGACY_KEYS[i]) || '';
        } catch (e) { legacy = null; }
      }
      var next = mergeAttribution(existing, params, {
        now: Date.now(),
        pathname: loc.pathname,
        referrer: document.referrer || '',
        hostname: loc.hostname,
        legacy: legacy,
        uuid: (!existing && memoSessionId) ? memoSessionId : undefined
      });
      memoSessionId = next.sid || memoSessionId;
      writeState(next);
      writeLegacy(params, loc.pathname);
      return flatten(next);
    } catch (e) {
      return get();
    }
  }

  function newEventId() {
    return newUuid();
  }

  if (root && typeof root === 'object' && root.document) {
    root.MDNAttribution = {
      capture: capture, get: get, getMeta: getMeta, eventAt: eventAt, newEventId: newEventId
    };
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
      parseParams: parseParams,
      mergeAttribution: mergeAttribution,
      flatten: flatten,
      cookieDomainFor: cookieDomainFor,
      newUuid: newUuid,
      externalReferrer: externalReferrer,
      serializeForCookie: serializeForCookie,
      capture: capture,
      get: get,
      getMeta: getMeta,
      eventAt: eventAt,
      newEventId: newEventId,
      isValidClickId: isValidClickId,
      isNonGoogleSource: isNonGoogleSource,
      computeMeta: computeMeta,
      STORAGE_KEY: STORAGE_KEY,
      FLAT_KEYS: FLAT_KEYS,
      LIFETIME_MS: LIFETIME_MS,
      SESSION_TIMEOUT_MS: SESSION_TIMEOUT_MS
    };
  }
})(typeof window !== 'undefined' ? window : (typeof globalThis !== 'undefined' ? globalThis : this));

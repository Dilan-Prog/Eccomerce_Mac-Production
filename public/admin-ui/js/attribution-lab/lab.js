/*!
 * Laboratorio de atribucion (admin) - logica del navegador.
 *
 * UMD: en el navegador expone window.AttributionLab; en Node (require / vm) exporta las
 * funciones PURAS para probarlas. La parte de DOM solo corre si hay `document` y `window.AU_LAB`
 * (lo imprime la vista admin-ui/attribution-lab/index.blade.php).
 *
 * Reglas de este archivo:
 *  - Todo dato que viene del visitante (utm, gclid, producto, folio, JSON de eventos) se pinta con
 *    textContent / createTextNode; nunca con innerHTML.
 *  - El token de la API NUNCA llega al navegador: el curl que se genera lleva el literal $TOKEN.
 *  - La peticion al backend pasa por AU.request (CSRF + JSON), definido en core/dom.js.
 */
(function (root) {
  'use strict';

  var DEFAULT_TZ = 'America/Mexico_City';
  var ALNUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  var TEST_GCLID_PREFIX = 'TEST_GCLID_';
  var TEST_GCLID_RANDOM_LEN = 16;

  // Misma regex que el endpoint de n8n (texto crudo, sin normalizar).
  var N8N_RE = /\bMN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}\b/g;
  // Regex tolerante (paridad con FolioExtractor.php): separador opcional, lookarounds en vez de \b.
  // El limite izquierdo (que no haya letra/digito antes de MN) se comprueba a mano en extractFolio():
  // el lookbehind no existe en Safari < 16.4 y rompia todo el archivo.
  var TOLERANT_RE = /MN[-\t\n\v\f\r ]?([2-9BCDFGHJKMNPQRSTVWXYZ]{5})(?![A-Za-z0-9])/g;
  var INVISIBLES_RE = /[\u200B\u200C\u200D\u2060\uFEFF]/g;

  /* ------------------------------------------------------------------ *
   * Funciones puras
   * ------------------------------------------------------------------ */

  /** Mayusculas, quita U+200B/200C/200D/2060/FEFF y convierte NBSP en espacio (no colapsa espacios). */
  function normalizeText(text) {
    return String(text == null ? '' : text)
      .toUpperCase()
      .replace(INVISIBLES_RE, '')
      .replace(/\u00A0/g, ' ');
  }

  /** Extraccion tolerante: ultimo folio gana. Devuelve "MN" + 5 caracteres o null. */
  function extractFolio(text) {
    var clean = normalizeText(text);
    var re = new RegExp(TOLERANT_RE.source, 'g');
    var last = null;
    var m;
    while ((m = re.exec(clean)) !== null) {
      if (m.index > 0 && /[A-Za-z0-9]/.test(clean.charAt(m.index - 1))) {
        // MN pegado a una letra/digito previo (p. ej. XMN7K4Q2): no es un folio.
        re.lastIndex = m.index + 1;
        continue;
      }
      last = 'MN' + m[1];
    }
    return last;
  }

  /**
   * Regex exacta del nodo de n8n sobre el texto CRUDO (sin mayusculas ni limpieza). Como
   * `texto.match(re)`, toma el PRIMER folio (la extraccion tolerante toma el ultimo).
   */
  function extractFolioExact(text) {
    var m = new RegExp(N8N_RE.source).exec(String(text == null ? '' : text));
    return m ? m[0] : null;
  }

  /** Un gclid valido: [A-Za-z0-9_-]{20,255}, sin prefijo "gtm" ni solo digitos (igual que el servidor). */
  function isValidGclid(value) {
    if (typeof value !== 'string') return false;
    if (!/^[A-Za-z0-9_-]{20,255}$/.test(value)) return false;
    if (/^gtm/i.test(value)) return false;
    if (/^[0-9]+$/.test(value)) return false;
    return true;
  }

  /** gbraid / wbraid: [A-Za-z0-9_-]{10,255}. */
  function isValidBraid(value) {
    return typeof value === 'string' && /^[A-Za-z0-9_-]{10,255}$/.test(value);
  }

  function defaultRand(n) {
    var bytes = new Uint8Array(n);
    var c = (root && root.crypto) || (typeof globalThis !== 'undefined' && globalThis.crypto);
    if (!c || typeof c.getRandomValues !== 'function') {
      throw new Error('crypto.getRandomValues no esta disponible');
    }
    c.getRandomValues(bytes);
    return bytes;
  }

  /**
   * TEST_GCLID_ + 16 caracteres alfanumericos (muestreo por rechazo: sin sesgo de modulo).
   * `rand(n)` devuelve n bytes (Uint8Array o arreglo); se inyecta para poder probarlo.
   */
  function randomTestGclid(rand) {
    var gen = typeof rand === 'function' ? rand : defaultRand;
    var limit = 248; // mayor multiplo de 62 que cabe en 256 (62 * 4)
    var out = '';
    var rounds = 0;
    while (out.length < TEST_GCLID_RANDOM_LEN) {
      if (++rounds > 200) throw new Error('El generador aleatorio no produjo bytes validos');
      var bytes = gen(TEST_GCLID_RANDOM_LEN * 2);
      for (var i = 0; i < bytes.length && out.length < TEST_GCLID_RANDOM_LEN; i++) {
        var b = bytes[i] & 255;
        if (b < limit) out += ALNUM.charAt(b % 62);
      }
    }
    return TEST_GCLID_PREFIX + out;
  }

  /** Arma la URL de una visita simulada. Omite parametros vacios; todo va url-encoded. */
  function buildAdUrl(opts) {
    opts = opts || {};
    var base = String(opts.siteUrl || '').replace(/\/+$/, '');
    var path = String(opts.path || '/');
    if (path.charAt(0) !== '/') path = '/' + path;
    var utm = opts.utm || {};
    var pairs = [
      ['gclid', opts.gclid],
      ['gbraid', opts.gbraid],
      ['wbraid', opts.wbraid]
    ];
    ['source', 'medium', 'campaign', 'term', 'content'].forEach(function (k) {
      var v = utm[k] != null ? utm[k] : utm['utm_' + k];
      pairs.push(['utm_' + k, v]);
    });
    var qs = pairs
      .filter(function (p) { return p[1] != null && String(p[1]).trim() !== ''; })
      .map(function (p) { return p[0] + '=' + encodeURIComponent(String(p[1]).trim()); })
      .join('&');
    return base + path + (qs ? '?' + qs : '');
  }

  /** encodeURIComponent + los caracteres que deja pasar y que el shell puede interpretar (! ' ( ) *). */
  function encodeStrict(v) {
    return encodeURIComponent(String(v)).replace(/[!'()*]/g, function (c) {
      return '%' + c.charCodeAt(0).toString(16).toUpperCase();
    });
  }

  /** Escapa lo que sigue siendo especial dentro de comillas dobles de bash. */
  function escapeDoubleQuoted(s) {
    return String(s).replace(/(["\\$`])/g, '\\$1');
  }

  /**
   * curl listo para pegar. Lleva el literal $TOKEN (la variable de tu shell): el token real nunca
   * pasa por esta pagina.
   */
  function buildCurl(opts) {
    opts = opts || {};
    var params = opts.params || {};
    var qs = Object.keys(params)
      .filter(function (k) { return params[k] != null && String(params[k]) !== ''; })
      .map(function (k) { return encodeStrict(k) + '=' + encodeStrict(params[k]); })
      .join('&');
    var api = String(opts.apiUrl || '');
    var url = api + (qs ? (api.indexOf('?') === -1 ? '?' : '&') + qs : '');
    return 'curl -s -H "Authorization: Bearer $TOKEN" "' + escapeDoubleQuoted(url) + '"';
  }

  function zoneParts(date, tz) {
    var fmt = new Intl.DateTimeFormat('en-CA', {
      timeZone: tz || DEFAULT_TZ,
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit',
      hourCycle: 'h23'
    });
    var out = {};
    fmt.formatToParts(date).forEach(function (p) { out[p.type] = p.value; });
    return out;
  }

  /** 'YYYY-MM-DD HH:mm:ss' en la zona dada. Devuelve '' si la fecha no es valida. */
  function formatInZone(isoOrDate, tz) {
    if (isoOrDate == null || isoOrDate === '') return '';
    var d = isoOrDate instanceof Date ? isoOrDate : new Date(isoOrDate);
    if (isNaN(d.getTime())) return '';
    try {
      var p = zoneParts(d, tz);
      return p.year + '-' + p.month + '-' + p.day + ' ' + p.hour + ':' + p.minute + ':' + p.second;
    } catch (e) {
      return '';
    }
  }

  function formatMexico(isoOrDate) {
    return formatInZone(isoOrDate, DEFAULT_TZ);
  }

  /** Valor para <input type="datetime-local"> ('YYYY-MM-DDTHH:mm') de una fecha en la zona dada. */
  function datetimeLocalFromDate(date, tz) {
    var s = formatInZone(date, tz);
    return s ? s.slice(0, 16).replace(' ', 'T') : '';
  }

  /** Desfase (ms) de la zona respecto a UTC en el instante utcMs: hora de pared tratada como UTC - utcMs. */
  function zoneOffsetMs(utcMs, tz) {
    var p = zoneParts(new Date(utcMs), tz);
    var asUtc = Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second);
    return asUtc - Math.floor(utcMs / 1000) * 1000;
  }

  /**
   * Convierte un datetime-local ingenuo ('YYYY-MM-DDTHH:mm[:ss]') de la zona IANA `tz` a ISO UTC con
   * Z (sin milisegundos). Correcto para cualquier zona y en los cambios de horario (en un hueco de
   * primavera cae en la hora siguiente; en un traslape de otono toma la primera ocurrencia).
   * Devuelve null si la entrada o la zona no son validas.
   */
  function naiveLocalToUtcIso(local, tz) {
    var m = /^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?(?:\.\d+)?$/.exec(String(local == null ? '' : local).trim());
    if (!m) return null;
    var y = +m[1], mo = +m[2], d = +m[3], h = +m[4], mi = +m[5], s = m[6] ? +m[6] : 0;
    if (mo < 1 || mo > 12 || d < 1 || d > 31 || h > 23 || mi > 59 || s > 59) return null;
    var naive = Date.UTC(y, mo - 1, d, h, mi, s);
    var check = new Date(naive);
    if (check.getUTCMonth() !== mo - 1 || check.getUTCDate() !== d) return null; // 31 de febrero, etc.
    try {
      var zone = tz || DEFAULT_TZ;
      var DAY = 24 * 3600 * 1000;
      var offBefore = zoneOffsetMs(naive - DAY, zone);
      var offAfter = zoneOffsetMs(naive + DAY, zone);
      var candidates = [];
      [offBefore, offAfter].forEach(function (o) {
        var utc = naive - o;
        if (zoneOffsetMs(utc, zone) === o && candidates.indexOf(utc) === -1) candidates.push(utc);
      });
      var result;
      if (candidates.length === 0) result = naive - offBefore;      // hueco de primavera: cae en la hora siguiente
      else result = Math.min.apply(null, candidates);                // traslape de otono: primera ocurrencia
      return new Date(result).toISOString().replace(/\.\d{3}Z$/, 'Z');
    } catch (e) {
      return null;
    }
  }

  /**
   * Valida el formulario del probador y arma los parametros que entiende GET /api/attribution/lookup.
   * Devuelve {params} o {error}. from/to/message_at salen en ISO UTC con Z.
   */
  function buildLookupParams(form) {
    form = form || {};
    var tz = form.tz || DEFAULT_TZ;
    if (form.mode === 'window') {
      if (!form.type) return { error: 'Elige el tipo de evento.' };
      if (!form.from || !form.to) return { error: 'Indica la hora inicial y la final de la ventana.' };
      var from = naiveLocalToUtcIso(form.from, tz);
      var to = naiveLocalToUtcIso(form.to, tz);
      if (!from || !to) return { error: 'Las fechas de la ventana no son validas.' };
      if (new Date(to).getTime() <= new Date(from).getTime()) {
        return { error: 'La hora final debe ser posterior a la inicial.' };
      }
      return { params: { type: String(form.type), from: from, to: to } };
    }
    var folio = String(form.folio == null ? '' : form.folio).trim().toUpperCase();
    if (!folio) return { error: 'Escribe un folio (por ejemplo MN7K4Q2 o COT-...).' };
    var params = { folio: folio };
    if (form.message_at) {
      var at = naiveLocalToUtcIso(form.message_at, tz);
      if (!at) return { error: 'La hora del mensaje no es valida.' };
      params.message_at = at;
    }
    return { params: params };
  }

  /** Clase de badge para un codigo HTTP. */
  function badgeForHttp(code) {
    var n = Number(code);
    if (n >= 200 && n < 300) return 'success';
    if (n >= 400 && n < 500) return 'warning';
    return 'critical';
  }

  /** Clase de badge para el nivel de una advertencia del estado. */
  function badgeForLevel(level) {
    var l = String(level || '').toLowerCase();
    if (l === 'error' || l === 'critical' || l === 'danger') return 'critical';
    if (l === 'warning' || l === 'warn') return 'warning';
    if (l === 'success' || l === 'ok') return 'success';
    return 'info';
  }

  var pure = {
    normalizeText: normalizeText,
    extractFolio: extractFolio,
    extractFolioExact: extractFolioExact,
    isValidGclid: isValidGclid,
    isValidBraid: isValidBraid,
    randomTestGclid: randomTestGclid,
    buildAdUrl: buildAdUrl,
    buildCurl: buildCurl,
    formatInZone: formatInZone,
    formatMexico: formatMexico,
    datetimeLocalFromDate: datetimeLocalFromDate,
    naiveLocalToUtcIso: naiveLocalToUtcIso,
    buildLookupParams: buildLookupParams,
    badgeForHttp: badgeForHttp,
    badgeForLevel: badgeForLevel,
    DEFAULT_TZ: DEFAULT_TZ,
    TEST_GCLID_PREFIX: TEST_GCLID_PREFIX
  };

  /* ------------------------------------------------------------------ *
   * DOM (solo navegador, con window.AU_LAB)
   * ------------------------------------------------------------------ */

  function initDom(win, doc, cfg) {
    var AU = win.AU;
    var routes = cfg.routes || {};
    var POLL_MS = 7000;
    var MAX_ROWS = 50;

    var state = {
      testActive: !!cfg.test_mode_active,
      rows: [],            // eventos (maximo 50), el mas reciente primero
      afterId: 0,
      selectedId: null,
      polling: null,
      inflight: false,
      product: null,
      productSeq: 0,
      mode: 'folio'
    };

    /* ---------- helpers de DOM ---------- */

    function $(id) { return doc.getElementById(id); }

    function h(tag, props, children) {
      var el = doc.createElement(tag);
      props = props || {};
      Object.keys(props).forEach(function (k) {
        if (k === 'class') el.className = props[k];
        else if (k === 'text') el.textContent = props[k];
        else el.setAttribute(k, props[k]);
      });
      (children || []).forEach(function (c) {
        if (c == null) return;
        el.appendChild(typeof c === 'string' ? doc.createTextNode(c) : c);
      });
      return el;
    }

    function clear(el) { while (el && el.firstChild) el.removeChild(el.firstChild); }

    function badge(kind, text) {
      return h('span', { class: 'au-badge au-badge-' + kind }, [h('span', { class: 'au-badge-dot' }), text]);
    }

    function toastErr(e, fallback) {
      var msg = (e && e.data && e.data.message) || (e && e.message) || fallback || 'Ocurrio un error';
      if (AU && AU.toast) AU.toast.error(msg);
    }

    function setBusy(btn, busy) { if (btn) btn.disabled = !!busy; }

    function show(el, on) { if (el) { el.style.display = ''; el.classList.toggle('lab-hidden', !on); } }

    function text(v, dash) {
      return v === null || v === undefined || v === '' ? (dash === undefined ? '—' : dash) : String(v);
    }

    /* ---------- 1. Estado ---------- */

    function statCard(icon, tone, label, value, lines) {
      var body = h('div', { class: 'lab-stat-body' }, [
        h('div', { class: 'au-stat-label', text: label }),
        h('div', { class: 'au-stat-value', text: value })
      ]);
      (lines || []).forEach(function (ln) { body.appendChild(h('div', { class: 'lab-stat-line', text: ln })); });
      return h('div', { class: 'au-stat-card' }, [
        h('div', { class: 'au-stat-icon' + (tone ? ' is-' + tone : '') }, [h('i', { class: icon })]),
        body
      ]);
    }

    function typeCounts(obj) {
      var keys = Object.keys(obj || {});
      if (!keys.length) return 'sin eventos';
      return keys.map(function (k) { return k + ' ' + obj[k]; }).join(' · ');
    }

    function renderStatus(s) {
      var grid = $('lab-status-grid');
      clear(grid);
      var tm = s.test_mode || {};
      var fo = s.folio || {};
      var tk = s.token || {};
      var wa = s.whatsapp || {};
      var as = s.assets || {};
      var today = s.today || {};

      var placements = fo.placements;
      if (Array.isArray(placements)) placements = placements.join(', ');

      var tmLines = [];
      if (tm.active) {
        if (tm.expires_at_mx) tmLines.push('Vence: ' + tm.expires_at_mx + ' (hora MX)');
        if (tm.minutes_left != null) tmLines.push('Quedan ' + tm.minutes_left + ' min');
      }
      grid.appendChild(statCard('fas fa-flask', tm.active ? 'success' : '', 'Modo prueba', tm.active ? 'Activo' : 'Apagado', tmLines));
      grid.appendChild(statCard('fas fa-link', fo.enabled ? 'success' : 'warning', 'Folio de contacto', fo.enabled ? 'Encendido' : 'Apagado',
        ['Placements: ' + text(placements, 'ninguno')]));
      grid.appendChild(statCard('fas fa-eye-slash', fo.invisible ? 'warning' : '', 'Folio invisible', fo.invisible ? 'Encendido' : 'Apagado', []));
      grid.appendChild(statCard('fas fa-key', tk.configured ? 'success' : 'critical', 'Token de la API', tk.configured ? 'Configurado' : 'No configurado',
        tk.configured ? [tk.length_ok ? 'Longitud adecuada' : 'Demasiado corto'] : ['El endpoint responde 503']));
      var effective = wa.effective_in_test;
      grid.appendChild(statCard('fab fa-whatsapp', wa.production_number ? 'info' : 'warning', 'WhatsApp', tm.active ? 'Usa número de prueba' : 'Usa producción', [
        'Producción: ' + text(wa.production_number, 'sin definir'),
        'Prueba: ' + (wa.test_number ? wa.test_number : 'sin número (wa.me/?text=, eliges el chat)') + (wa.test_number_invalid ? ' · número inválido, se ignora' : ''),
        'En modo prueba se usa: ' + text(effective, 'ninguno (wa.me/?text=)')
      ]));
      grid.appendChild(statCard('fas fa-file-code', 'info', 'Scripts públicos', 'Versiones', [
        'mdn-contact.js: ' + text(as.contact_js_version),
        'mdn-events.js: ' + text(as.events_js_version)
      ]));
      grid.appendChild(statCard('fas fa-clock', '', 'Último evento', text(s.last_event_at_mx, 'Sin eventos'), ['Hora de México']));
      grid.appendChild(statCard('fas fa-calendar-day', 'info', 'Hoy por tipo', 'Real / Prueba', [
        'Real: ' + typeCounts(today.real),
        'Prueba: ' + typeCounts(today.test)
      ]));

      var ul = $('lab-warnings');
      clear(ul);
      var warnings = Array.isArray(s.warnings) ? s.warnings : [];
      if (!warnings.length) {
        ul.appendChild(h('li', { class: 'lab-warning' }, [badge('success', 'Todo en orden'), h('span', { text: 'Sin advertencias.' })]));
      }
      warnings.forEach(function (w) {
        var kind = badgeForLevel(w.level);
        var label = kind === 'critical' ? 'Error' : (kind === 'warning' ? 'Aviso' : 'Info');
        ul.appendChild(h('li', { class: 'lab-warning' }, [badge(kind, label), h('span', { text: text(w.message) })]));
      });

      state.testActive = !!tm.active;
      updateTestModeUi();
    }

    async function loadStatus() {
      var err = $('lab-status-error');
      show(err, false);
      try {
        var data = await AU.request(routes.status);
        renderStatus(data.data || {});
      } catch (e) {
        err.textContent = 'No se pudo cargar el estado: ' + ((e && e.message) || 'error desconocido');
        show(err, true);
      }
    }

    /* ---------- 2. Modo prueba ---------- */

    function updateTestModeUi() {
      var on = state.testActive;
      var pill = $('lab-test-pill');
      clear(pill);
      pill.appendChild(badge(on ? 'success' : 'neutral', on ? 'Modo prueba activo en este navegador' : 'Modo prueba apagado'));
      $('lab-btn-activate').disabled = false;
      $('lab-btn-deactivate').disabled = !on;
      show($('lab-gen-warning'), !on);
      $('lab-open').disabled = !on;
    }

    async function toggleTestMode(url, btn) {
      setBusy(btn, true);
      try {
        var data = await AU.request(url, { method: 'POST' });
        if (data && data.status === 'success') {
          if (AU.toast) AU.toast.success(data.message || 'Listo');
          if (data.test_mode && typeof data.test_mode.active === 'boolean') {
            state.testActive = data.test_mode.active;
            updateTestModeUi();
          }
          await loadStatus();
        } else {
          toastErr({ message: data && data.message }, 'No se pudo cambiar el modo prueba');
        }
      } catch (e) {
        toastErr(e, 'No se pudo cambiar el modo prueba');
      } finally {
        setBusy(btn, false);
        updateTestModeUi();
      }
    }

    function checkLine(ok, label) {
      return h('li', { class: 'lab-check' }, [
        h('span', { class: 'lab-check-mark ' + (ok ? 'is-ok' : 'is-bad'), text: ok ? '✔' : '✖' }),
        h('span', { text: label })
      ]);
    }

    async function verifySite() {
      var btn = $('lab-btn-verify');
      var out = $('lab-verify-result');
      clear(out);
      setBusy(btn, true);
      try {
        var res = await win.fetch(cfg.site_url, { credentials: 'same-origin', cache: 'no-store' });
        var html = await res.text();
        // La asignacion impresa por el servidor (los JS publicos solo LEEN window.MDN_TEST_MODE, sin '= "').
        var hasMarker = /window\.MDN_TEST_MODE\s*=\s*["']/.test(html);
        // Etiquetas que cargan GTM/GA4/Ads (el <link rel=preconnect> al dominio no cuenta: no envia datos).
        var hasGtm = /googletagmanager\.com\/(gtm\.js|gtag\/js|ns\.html)/i.test(html);
        var ul = h('ul', { class: 'lab-checks' });
        ul.appendChild(checkLine(hasMarker, 'La página incluye MDN_TEST_MODE'));
        ul.appendChild(checkLine(!hasGtm, 'La página NO carga las etiquetas de Google (GTM/GA4/Ads)'));
        out.appendChild(ul);
        if (!state.testActive) {
          out.appendChild(h('p', { class: 'au-help-text', text: 'El modo prueba está apagado en este navegador: es normal que el primer criterio falle.' }));
        }
        out.appendChild(h('p', { class: 'au-help-text',
          text: 'Prueba también en una ventana sin cookie (incógnito): ahí el sitio debe verse idéntico al de siempre, sin MDN_TEST_MODE y con sus etiquetas normales.' }));
      } catch (e) {
        out.appendChild(h('p', { class: 'au-help-text lab-error',
          text: 'No se pudo leer el sitio desde esta página (¿otro dominio?). Ábrelo y revisa el código fuente a mano.' }));
      } finally {
        setBusy(btn, false);
      }
    }

    /* ---------- 3. Generador de visita simulada ---------- */

    function genField(id) { return ($(id).value || '').trim(); }

    function currentPath() {
      var dest = $('lab-dest').value;
      if (dest === 'product') {
        return state.product ? '/product-detail/' + encodeURIComponent(state.product.slug) : null;
      }
      if (dest === 'categorias') return '/categorias';
      return '/';
    }

    function openSimulatedVisit() {
      var errs = [];
      var path = currentPath();
      if (path === null) errs.push('Busca y elige un producto.');
      var gclid = genField('lab-gclid');
      if (!isValidGclid(gclid)) errs.push('El gclid debe tener al menos 20 caracteres y solo A-Z, a-z, 0-9, _ y -.');
      var gbraid = genField('lab-gbraid');
      var wbraid = genField('lab-wbraid');
      if (gbraid && !isValidBraid(gbraid)) errs.push('El gbraid debe tener al menos 10 caracteres (A-Z, a-z, 0-9, _ y -).');
      if (wbraid && !isValidBraid(wbraid)) errs.push('El wbraid debe tener al menos 10 caracteres (A-Z, a-z, 0-9, _ y -).');

      var box = $('lab-gen-errors');
      clear(box);
      if (errs.length) {
        errs.forEach(function (m) { box.appendChild(h('li', { text: m })); });
        show(box, true);
        return;
      }
      show(box, false);
      var url = buildAdUrl({
        siteUrl: cfg.site_url,
        path: path,
        gclid: gclid,
        gbraid: gbraid,
        wbraid: wbraid,
        utm: {
          source: genField('lab-utm-source'),
          medium: genField('lab-utm-medium'),
          campaign: genField('lab-utm-campaign'),
          term: genField('lab-utm-term'),
          content: genField('lab-utm-content')
        }
      });
      win.open(url, '_blank', 'noopener');
    }

    function regenGclid() { $('lab-gclid').value = randomTestGclid(); }

    function renderProductResults(results) {
      var box = $('lab-product-results');
      clear(box);
      if (!results.length) {
        box.appendChild(h('div', { class: 'au-sku-search-status', text: 'Sin resultados.' }));
        return;
      }
      results.forEach(function (p) {
        var btn = h('button', { type: 'button', class: 'au-sku-search-result' }, [
          h('span', { class: 'au-sku-search-result-code', text: text(p.sku, 'sin SKU') }),
          h('span', { class: 'au-sku-search-result-desc', text: text(p.name) })
        ]);
        btn.addEventListener('click', function () {
          state.product = { id: p.id, name: p.name, sku: p.sku, slug: p.slug };
          clear(box);
          $('lab-product-q').value = '';
          renderSelectedProduct();
        });
        box.appendChild(btn);
      });
    }

    function renderSelectedProduct() {
      var el = $('lab-product-selected');
      clear(el);
      if (!state.product) {
        el.appendChild(h('span', { class: 'au-help-text', text: 'Ningún producto elegido.' }));
        return;
      }
      el.appendChild(badge('info', 'Producto'));
      el.appendChild(h('span', { class: 'lab-product-name', text: ' ' + text(state.product.sku, '') + ' — ' + text(state.product.name) }));
    }

    async function searchProducts(term) {
      var box = $('lab-product-results');
      var q = (term || '').trim();
      if (q.length < 2) { clear(box); return; }
      var seq = ++state.productSeq;
      try {
        var data = await AU.request(routes.productsSearch + '?q=' + encodeURIComponent(q));
        if (seq !== state.productSeq) return; // llego una busqueda mas nueva
        renderProductResults(data.results || []);
      } catch (e) {
        if (seq === state.productSeq) toastErr(e, 'No se pudo buscar productos');
      }
    }

    function onDestChange() {
      show($('lab-product-wrap'), $('lab-dest').value === 'product');
    }

    /* ---------- 4. Eventos recientes ---------- */

    function sourceBadge(src) {
      var kind = src === 'event' ? 'success' : (src === 'session' ? 'info' : 'warning');
      return badge(kind, text(src, 'none'));
    }

    function productLabel(p) {
      if (p == null || p === '') return '—';
      if (typeof p === 'string') return p;
      if (typeof p === 'object') return text(p.sku || p.name, '—');
      return String(p);
    }

    function renderEvents() {
      var tbody = $('lab-events-body');
      clear(tbody);
      show($('lab-events-empty'), state.rows.length === 0);
      state.rows.forEach(function (ev) {
        var tr = h('tr', { class: 'lab-row' + (ev.id === state.selectedId ? ' is-selected' : ''), 'data-id': String(ev.id), tabindex: '0' });
        var when = ev.created_at_mx || formatMexico(ev.created_at);
        [
          [text(when), 'au-mono'],
          [text(ev.canonical_type || ev.type), 'au-mono'],
          [text(ev.placement), ''],
          [text(ev.contact_folio), 'au-mono'],
          [text(ev.gclid_short), 'au-mono'],
          [text(ev.utm_campaign), ''],
          [productLabel(ev.product), ''],
          [text(ev.session_short), 'au-mono']
        ].forEach(function (c) { tr.appendChild(h('td', { class: c[1], text: c[0] })); });
        tr.appendChild(h('td', {}, [sourceBadge(ev.attribution_source)]));
        tbody.appendChild(tr);
      });
    }

    function mergeEvents(list, reset) {
      var byId = {};
      if (!reset) state.rows.forEach(function (r) { byId[r.id] = r; });
      list.forEach(function (r) { byId[r.id] = r; });
      state.rows = Object.keys(byId).map(function (k) { return byId[k]; })
        .sort(function (a, b) { return b.id - a.id; })
        .slice(0, MAX_ROWS);
    }

    async function loadEvents(opts) {
      opts = opts || {};
      if (state.inflight) return;
      state.inflight = true;
      var err = $('lab-events-error');
      try {
        var reset = !!opts.reset || state.afterId === 0;
        var url = routes.events + (reset ? '' : '?after_id=' + encodeURIComponent(state.afterId));
        var data = await AU.request(url);
        var list = Array.isArray(data.events) ? data.events : [];
        mergeEvents(list, reset);
        var latest = Number(data.latest_id);
        if (reset) {
          state.afterId = isFinite(latest) ? latest : (state.rows[0] ? state.rows[0].id : 0);
        } else if (isFinite(latest) && latest > state.afterId) {
          state.afterId = latest;
        }
        show(err, false);
        renderEvents();
      } catch (e) {
        err.textContent = 'No se pudieron cargar los eventos: ' + ((e && e.message) || 'error desconocido');
        show(err, true);
      } finally {
        state.inflight = false;
      }
    }

    function stopPolling() {
      if (state.polling) { win.clearTimeout(state.polling); state.polling = null; }
    }

    function schedulePoll() {
      stopPolling();
      if (!$('lab-events-auto').checked) return;
      state.polling = win.setTimeout(async function () {
        state.polling = null;
        if (!doc.hidden && $('lab-events-auto').checked) {
          await loadEvents();
        }
        schedulePoll();
      }, POLL_MS);
    }

    async function showEventDetail(id) {
      state.selectedId = id;
      renderEvents();
      var card = $('lab-event-detail');
      var pre = $('lab-event-json');
      show(card, true);
      $('lab-event-detail-title').textContent = 'Detalle del evento #' + id;
      pre.textContent = 'Cargando…';
      try {
        var data = await AU.request(routes.event.replace('__ID__', encodeURIComponent(id)));
        if (state.selectedId !== id) return;
        pre.textContent = JSON.stringify(data.event != null ? data.event : data, null, 2);
      } catch (e) {
        pre.textContent = 'No se pudo cargar el evento: ' + ((e && e.message) || 'error');
      }
    }

    function closeEventDetail() {
      state.selectedId = null;
      show($('lab-event-detail'), false);
      renderEvents();
    }

    /* ---------- 5. Probador estilo n8n ---------- */

    function readLookupForm() {
      return {
        mode: state.mode,
        folio: $('lab-q-folio').value,
        message_at: $('lab-q-message-at').value,
        type: $('lab-q-type').value,
        from: $('lab-q-from').value,
        to: $('lab-q-to').value,
        tz: DEFAULT_TZ,
        scope: $('lab-q-scope').value
      };
    }

    function setMode(mode) {
      state.mode = mode;
      Array.prototype.forEach.call(doc.querySelectorAll('[data-lab-mode]'), function (t) {
        t.classList.toggle('is-active', t.getAttribute('data-lab-mode') === mode);
      });
      show($('lab-q-pane-folio'), mode === 'folio');
      show($('lab-q-pane-window'), mode === 'window');
      refreshCurl();
    }

    function curlText() {
      var built = buildLookupParams(readLookupForm());
      if (built.error) return { error: built.error };
      return { value: buildCurl({ apiUrl: cfg.api_lookup_url, params: built.params }) };
    }

    function refreshCurl() {
      var c = curlText();
      var btn = $('lab-q-curl');
      if (c.value) btn.setAttribute('data-au-copy', c.value);
      else btn.removeAttribute('data-au-copy');
    }

    function renderLookupResult(box, r) {
      clear(box);
      if (!r) return;
      var head = h('div', { class: 'lab-result-head' }, [
        badge(badgeForHttp(r.http_status), 'HTTP ' + text(r.http_status)),
        h('span', { class: 'lab-result-meta', text: (r.elapsed_ms != null ? r.elapsed_ms + ' ms' : '') }),
        r.scope ? h('span', { class: 'lab-result-meta', text: 'Alcance: ' + (r.scope === 'real' ? 'real' : 'prueba') }) : null
      ]);
      box.appendChild(head);
      box.appendChild(h('pre', { class: 'au-json', text: JSON.stringify(r.body, null, 2) }));
    }

    async function runLookup() {
      var form = readLookupForm();
      var built = buildLookupParams(form);
      var box = $('lab-q-result');
      if (built.error) {
        if (AU.toast) AU.toast.error(built.error);
        return;
      }
      var btn = $('lab-q-run');
      setBusy(btn, true);
      clear(box);
      box.appendChild(h('p', { class: 'au-help-text', text: 'Consultando…' }));
      try {
        var body = Object.assign({ mode: form.mode, tz: form.tz, scope: form.scope }, built.params);
        var data = await AU.request(routes.lookup, { method: 'POST', body: body });
        renderLookupResult(box, data);
      } catch (e) {
        clear(box);
        box.appendChild(h('p', { class: 'au-help-text lab-error', text: (e && e.message) || 'Error al consultar' }));
      } finally {
        setBusy(btn, false);
      }
    }

    function curlClick() {
      var c = curlText();
      if (c.error) {
        $('lab-q-curl').removeAttribute('data-au-copy');
        if (AU.toast) AU.toast.error(c.error);
        return;
      }
      // El manejador global de [data-au-copy] (admin.js) corre despues, en burbuja: toma este valor.
      $('lab-q-curl').setAttribute('data-au-copy', c.value);
    }

    /* ---------- 6. Simulador de mensaje ---------- */

    function labeledValue(label, value, none) {
      return h('div', { class: 'lab-sim-line' }, [
        h('span', { class: 'lab-sim-label', text: label }),
        value ? h('code', { class: 'au-mono lab-sim-value', text: value }) : h('span', { class: 'lab-sim-none', text: none })
      ]);
    }

    function renderExtraction(data) {
      var box = $('lab-sim-out');
      clear(box);
      var ex = data.extraction || {};
      box.appendChild(labeledValue('Regex exacta de n8n:', ex.exact_n8n, 'no extrae'));
      box.appendChild(labeledValue('Extracción tolerante:', ex.tolerant, 'no extrae'));
      if (ex.source) box.appendChild(labeledValue('Se encontró en la parte:', ex.source === 'invisible' ? 'invisible' : 'visible', ''));
      if (Array.isArray(ex.all) && ex.all.length > 1) {
        box.appendChild(labeledValue('Folios en el mensaje:', ex.all.join(', '), ''));
      }

      if (ex.tolerant && !ex.exact_n8n) {
        box.appendChild(h('div', { class: 'lab-alert lab-alert-critical', role: 'alert' }, [
          h('strong', { text: 'n8n NO lo extraería con su regex actual. ' }),
          h('span', { text: 'Solo la extracción tolerante encuentra el folio (' + ex.tolerant + '): ajusta el nodo de n8n o el mensaje.' })
        ]));
      } else if (ex.tolerant && ex.exact_n8n && ex.tolerant !== ex.exact_n8n) {
        box.appendChild(h('div', { class: 'lab-alert lab-alert-warning', role: 'alert' }, [
          h('strong', { text: 'Los resultados difieren. ' }),
          h('span', { text: 'n8n tomaría ' + ex.exact_n8n + ' y la extracción tolerante ' + ex.tolerant + '.' })
        ]));
      } else if (!ex.tolerant && !ex.exact_n8n) {
        box.appendChild(h('div', { class: 'lab-alert lab-alert-info' }, [h('span', { text: 'No hay ningún folio válido en este mensaje.' })]));
      } else {
        box.appendChild(h('div', { class: 'lab-alert lab-alert-success' }, [h('span', { text: 'n8n extraería este folio con su regex actual.' })]));
      }

      var lookupBox = h('div', { class: 'lab-sim-lookup' });
      box.appendChild(h('div', { class: 'lab-sim-subtitle', text: 'Resultado de la consulta' }));
      if (data.lookup) {
        renderLookupResult(lookupBox, { http_status: data.lookup.http_status, body: data.lookup.body, scope: null });
      } else {
        lookupBox.appendChild(h('p', { class: 'au-help-text', text: 'No se consultó: no hay folio que buscar.' }));
      }
      box.appendChild(lookupBox);
    }

    async function runExtract() {
      var textVal = $('lab-sim-text').value;
      if (!textVal.trim()) {
        if (AU.toast) AU.toast.error('Pega el mensaje del cliente.');
        return;
      }
      var btn = $('lab-sim-run');
      setBusy(btn, true);
      try {
        var data = await AU.request(routes.extract, { method: 'POST', body: { text: textVal } });
        renderExtraction(data);
      } catch (e) {
        toastErr(e, 'No se pudo analizar el mensaje');
      } finally {
        setBusy(btn, false);
      }
    }

    /* ---------- 7. Limpieza ---------- */

    async function destroyTests() {
      var res = await AU.confirm({
        title: '¿Borrar tus eventos de prueba?',
        text: 'Se eliminan todas las filas marcadas como prueba. Los eventos reales no se tocan. Esta acción no se puede deshacer.',
        tone: 'critical',
        confirmText: 'Sí, borrar',
        cancelText: 'Cancelar'
      });
      var confirmed = res.confirmed;
      if (!confirmed) return;
      var btn = $('lab-destroy');
      setBusy(btn, true);
      try {
        var data = await AU.request(routes.destroyTests, { method: 'DELETE' });
        if (data && data.status === 'success') {
          var n = Number(data.deleted || 0);
          if (AU.toast) AU.toast.success(n === 1 ? 'Se borró 1 evento de prueba.' : 'Se borraron ' + n + ' eventos de prueba.');
          state.rows = [];
          state.afterId = 0;
          closeEventDetail();
          await loadEvents({ reset: true });
          await loadStatus();
        } else {
          toastErr({ message: data && data.message }, 'No se pudieron borrar los eventos');
        }
      } catch (e) {
        toastErr(e, 'No se pudieron borrar los eventos');
      } finally {
        setBusy(btn, false);
      }
    }

    /* ---------- arranque ---------- */

    function bind() {
      $('lab-btn-activate').addEventListener('click', function () { toggleTestMode(routes.activate, this); });
      $('lab-btn-deactivate').addEventListener('click', function () { toggleTestMode(routes.deactivate, this); });
      $('lab-btn-verify').addEventListener('click', verifySite);
      $('lab-status-refresh').addEventListener('click', loadStatus);

      $('lab-dest').addEventListener('change', onDestChange);
      $('lab-gclid-regen').addEventListener('click', regenGclid);
      $('lab-open').addEventListener('click', openSimulatedVisit);
      $('lab-product-q').addEventListener('input', AU.debounce(function (e) { searchProducts(e.target.value); }, 300));

      $('lab-events-refresh').addEventListener('click', function () { loadEvents(); });
      $('lab-events-auto').addEventListener('change', schedulePoll);
      $('lab-event-close').addEventListener('click', closeEventDetail);
      var tbody = $('lab-events-body');
      tbody.addEventListener('click', function (e) {
        var tr = e.target.closest('tr[data-id]');
        if (tr) showEventDetail(Number(tr.getAttribute('data-id')));
      });
      tbody.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        var tr = e.target.closest('tr[data-id]');
        if (tr) { e.preventDefault(); showEventDetail(Number(tr.getAttribute('data-id'))); }
      });
      doc.addEventListener('visibilitychange', function () {
        if (!doc.hidden && $('lab-events-auto').checked) loadEvents();
      });

      Array.prototype.forEach.call(doc.querySelectorAll('[data-lab-mode]'), function (t) {
        t.addEventListener('click', function (e) { e.preventDefault(); setMode(t.getAttribute('data-lab-mode')); });
      });
      ['lab-q-folio', 'lab-q-message-at', 'lab-q-type', 'lab-q-from', 'lab-q-to', 'lab-q-scope'].forEach(function (id) {
        var el = $(id);
        el.addEventListener('input', refreshCurl);
        el.addEventListener('change', refreshCurl);
      });
      $('lab-q-run').addEventListener('click', runLookup);
      $('lab-q-curl').addEventListener('click', curlClick);

      $('lab-sim-run').addEventListener('click', runExtract);
      $('lab-destroy').addEventListener('click', destroyTests);
    }

    function start() {
      $('lab-gclid').value = randomTestGclid();
      var now = new Date();
      $('lab-q-to').value = datetimeLocalFromDate(now, DEFAULT_TZ);
      $('lab-q-from').value = datetimeLocalFromDate(new Date(now.getTime() - 10 * 60 * 1000), DEFAULT_TZ);
      bind();
      onDestChange();
      renderSelectedProduct();
      updateTestModeUi();
      setMode('folio');
      loadStatus();
      loadEvents({ reset: true }).then(schedulePoll);
    }

    start();
  }

  /* ------------------------------------------------------------------ *
   * Exportacion UMD
   * ------------------------------------------------------------------ */

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = pure;
  }

  var win = root && typeof root === 'object' ? root : null;
  if (win && win.document) {
    win.AttributionLab = pure;
    if (win.AU_LAB) {
      var go = function () { initDom(win, win.document, win.AU_LAB); };
      if (win.document.readyState === 'loading') win.document.addEventListener('DOMContentLoaded', go);
      else go();
    }
  }
})(typeof window !== 'undefined' ? window : (typeof globalThis !== 'undefined' ? globalThis : this));

'use strict';
/**
 * Modo prueba de atribucion (cliente): mdn-test-mode.js (boot-clear con nonce + pill), y su efecto en
 * mdn-events.js (test:1 en el payload) y mdn-contact.js (numero de prueba / numero vacio / wa.link).
 */
const { test, describe } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { webcrypto } = require('node:crypto');
const H = require('./helpers/load.cjs');

const MODE = H.loadBrowserModule('mdn-test-mode.js').exports;
const WA_LINK = 'https://wa.link/f28njw';
const REAL = '528135825559';
const TEST_NUMBER = '528199999999';

/* ---------------- entorno minimo para bootClear ---------------- */

function makeLs(initial, opts) {
  opts = opts || {};
  const store = Object.assign(Object.create(null), initial || {});
  return {
    _store: store,
    get length() { return Object.keys(store).length; },
    key: (i) => Object.keys(store)[i] === undefined ? null : Object.keys(store)[i],
    getItem(k) { if (opts.throwOnGet) throw new Error('storage bloqueado'); return k in store ? store[k] : null; },
    setItem(k, v) { if (opts.throwOnSet) throw new Error('cuota'); store[k] = String(v); },
    removeItem(k) { delete store[k]; }
  };
}

function makeEnv(opts) {
  opts = opts || {};
  const writes = [];
  const document = {};
  Object.defineProperty(document, 'cookie', {
    get() { return ''; },
    set(v) { if (opts.throwOnCookie) throw new Error('cookie bloqueada'); writes.push(String(v)); }
  });
  return {
    writes,
    ls: makeLs(opts.ls, opts),
    env: {
      document,
      localStorage: null, // se asigna abajo
      location: { hostname: opts.hostname || 'localhost', protocol: opts.protocol || 'http:' }
    }
  };
}

function boot(nonce, opts) {
  const e = makeEnv(opts);
  e.env.localStorage = e.ls;
  const result = MODE.bootClear(nonce, e.env);
  return { result, ls: e.ls, writes: e.writes };
}

const STALE = {
  mdn_attr: '{"gclid":"x"}',
  gclid: 'TEST_GCLID_ABCDEFGHIJ',
  gbraid: 'gb', wbraid: 'wb', fbclid: 'fb',
  utm_source: 'google', utm_medium: 'cpc', utm_campaign: 'c', utm_term: 't', utm_content: 'k',
  landing_page: '/p',
  mdn_visit_sent: 'v-1',
  mdn_quote_pending: '{"folio":"MN23456"}',
  mdn_queue: '[]',
  mdn_quote_77: 'MN9999X'
};

describe('bootClear: modo prueba (nonce)', () => {
  test('nonce distinto limpia mdn_attr, claves heredadas y mdn_visit_sent, y guarda el marcador', () => {
    const r = boot('N2', { ls: Object.assign({ mdn_test_boot: 'N1', otro: 'se queda' }, STALE) });
    assert.equal(r.result.cleared, true);
    for (const k of Object.keys(STALE)) assert.equal(r.ls.getItem(k), null, k);
    assert.equal(r.ls.getItem('otro'), 'se queda');
    assert.equal(r.ls.getItem('mdn_test_boot'), 'N2');
  });

  test('primera activacion (sin marcador previo) tambien limpia y marca', () => {
    const r = boot('N1', { ls: Object.assign({}, STALE) });
    assert.equal(r.result.cleared, true);
    assert.equal(r.ls.getItem('mdn_attr'), null);
    assert.equal(r.ls.getItem('mdn_test_boot'), 'N1');
  });

  test('mismo nonce: no toca nada (la atribucion de la prueba se conserva entre paginas)', () => {
    const r = boot('N1', { ls: Object.assign({ mdn_test_boot: 'N1' }, STALE) });
    assert.equal(r.result.cleared, false);
    for (const k of Object.keys(STALE)) assert.equal(r.ls.getItem(k), STALE[k], k);
    assert.equal(r.writes.length, 0, 'tampoco se expira la cookie');
  });

  test('expira la cookie mdn_attr con el dominio .macdelnorte.com en el sitio real', () => {
    for (const host of ['macdelnorte.com', 'www.macdelnorte.com']) {
      const r = boot('N9', { hostname: host, protocol: 'https:', ls: {} });
      const withDomain = r.writes.filter((w) => /domain=\.macdelnorte\.com/.test(w));
      assert.equal(withDomain.length, 1, host);
      assert.match(withDomain[0], /^mdn_attr=; path=\/; max-age=0; expires=Thu, 01 Jan 1970/);
      assert.match(withDomain[0], /SameSite=Lax/);
      assert.match(withDomain[0], /; Secure/);
      // ademas, una sin dominio por si quedo una cookie host-only
      assert.ok(r.writes.some((w) => !/domain=/.test(w) && w.startsWith('mdn_attr=;')));
    }
  });

  test('en localhost / .test la cookie se expira sin dominio y sin Secure', () => {
    for (const host of ['localhost', 'foo.test', '127.0.0.1']) {
      const r = boot('N9', { hostname: host, ls: {} });
      assert.equal(r.writes.length, 1, host);
      assert.ok(!/domain=/.test(r.writes[0]));
      assert.ok(!/Secure/.test(r.writes[0]));
      assert.ok(r.writes[0].startsWith('mdn_attr=;'));
    }
  });
});

describe('bootClear: fuera de modo prueba (nonce null)', () => {
  test('con marcador: limpia igual y borra el marcador (la atribucion de prueba no la hereda una visita real)', () => {
    for (const nonce of [null, undefined, '']) {
      const r = boot(nonce, { ls: Object.assign({ mdn_test_boot: 'N1', otro: 'ok' }, STALE) });
      assert.equal(r.result.cleared, true);
      assert.equal(r.result.mode, 'normal');
      for (const k of Object.keys(STALE)) assert.equal(r.ls.getItem(k), null, k);
      assert.equal(r.ls.getItem('mdn_test_boot'), null);
      assert.equal(r.ls.getItem('otro'), 'ok');
      assert.ok(r.writes.length >= 1);
    }
  });

  test('sin marcador: no hace nada (visitante normal)', () => {
    const r = boot(null, { ls: Object.assign({}, STALE) });
    assert.equal(r.result.cleared, false);
    for (const k of Object.keys(STALE)) assert.equal(r.ls.getItem(k), STALE[k], k);
    assert.equal(r.writes.length, 0);
  });
});

describe('bootClear: nunca lanza', () => {
  test('localStorage bloqueado al leer: no limpia (evita borrar la atribucion en cada pagina) y no lanza', () => {
    for (const nonce of ['N1', null]) {
      const r = boot(nonce, { throwOnGet: true, ls: Object.assign({ mdn_test_boot: 'N0' }, STALE) });
      assert.equal(r.result.cleared, false);
      assert.equal(r.ls._store.mdn_attr, STALE.mdn_attr);
    }
  });

  test('cuota/escritura fallida y cookies bloqueadas no lanzan', () => {
    assert.doesNotThrow(() => boot('N1', { throwOnSet: true, ls: Object.assign({}, STALE) }));
    assert.doesNotThrow(() => boot('N1', { throwOnCookie: true, ls: Object.assign({}, STALE) }));
    const r = boot(null, { throwOnCookie: true, ls: { mdn_test_boot: 'x', mdn_attr: 'y' } });
    assert.equal(r.ls.getItem('mdn_attr'), null, 'aun sin cookies se limpia localStorage');
  });

  test('entornos incompletos o argumentos raros no lanzan', () => {
    assert.doesNotThrow(() => MODE.bootClear('N1', {}));
    assert.doesNotThrow(() => MODE.bootClear('N1', { localStorage: null, document: null }));
    assert.doesNotThrow(() => MODE.bootClear(null));
    assert.doesNotThrow(() => MODE.bootClear(123, { localStorage: makeLs({}) }));
    assert.doesNotThrow(() => MODE.bootClear({}, undefined));
    assert.doesNotThrow(() => MODE.bootClear('N1', { localStorage: { getItem() { throw new Error('x'); } } }));
  });
});

describe('banner "Modo prueba de atribucion activo"', () => {
  function fakeDoc(existing) {
    const body = new H.FakeElement('body');
    const doc = {
      body,
      createElement: (t) => new H.FakeElement(t),
      getElementById: (id) => (existing && id === MODE.BANNER_ID ? existing : null)
    };
    return doc;
  }

  test('pill fija, accesible, sin interceptar clics y sin mover el layout', () => {
    const doc = fakeDoc();
    const el = MODE.banner(doc);
    assert.ok(el);
    assert.equal(doc.body.children.length, 1);
    assert.equal(el.getAttribute('role'), 'status');
    assert.equal(el.getAttribute('id'), 'mdn-test-mode-banner');
    const style = el.getAttribute('style');
    assert.match(style, /position:fixed/);
    assert.match(style, /z-index:2147483000/);
    assert.match(style, /pointer-events:none/);
    assert.match(style, /left:12px/);
    assert.match(style, /bottom:12px/);
    assert.equal(el.textContent, 'Modo prueba de atribución activo');
  });

  test('es idempotente y nunca lanza', () => {
    assert.equal(MODE.banner(fakeDoc({})), null);
    assert.doesNotThrow(() => MODE.banner(null));
    assert.doesNotThrow(() => MODE.banner({}));
    assert.doesNotThrow(() => MODE.banner({ createElement() { throw new Error('x'); }, body: {} }));
  });
});

/* ---------------- mdn-contact con el numero de prueba ---------------- */

function contact() {
  return H.loadBrowserModule('mdn-contact.js').exports;
}

describe('mdn-contact en modo prueba', () => {
  const folioCfg = (number, extra) => Object.assign({
    enabled: true, placements: '*', testMode: true,
    whatsapp: { url: WA_LINK, number }
  }, extra || {});

  test('con numero de prueba: el wa.link configurado se reescribe a wa.me/<prueba>?text=...', () => {
    const C = contact();
    C.configure(folioCfg(TEST_NUMBER));
    const a = H.anchor({ href: WA_LINK, 'data-type': 'whatsapp_hero' });
    const r = C.prepare(a, { lang: 'es' });
    assert.ok(r.href.startsWith('https://wa.me/' + TEST_NUMBER + '?text='), r.href);
    assert.ok(decodeURIComponent(r.href).includes('Folio: ' + r.folio));
    assert.equal(a.getAttribute('href'), r.href);
    assert.equal(a.getAttribute('data-mdn-orig-href'), WA_LINK);
    assert.ok(!r.href.includes(REAL));
  });

  test('con numero vacio: https://wa.me/?text=... (sin numero, nunca la linea real)', () => {
    const C = contact();
    C.configure(folioCfg(''));
    assert.equal(C.getConfig().whatsapp.number, '');
    const a = H.anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' });
    const r = C.prepare(a, { lang: 'es' });
    assert.ok(r.href.startsWith('https://wa.me/?text='), r.href);
    assert.ok(!r.href.includes(REAL));
    assert.match(decodeURIComponent(r.href), /Folio: MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}$/);
  });

  test('placements "*": reescribe un wa.link sin importar su ubicacion', () => {
    const C = contact();
    C.configure(folioCfg('', { placements: '*' }));
    for (const type of ['whatsapp_footer_oficina', 'whatsapp-flotante', 'whatsapp_producto']) {
      const a = H.anchor({ href: WA_LINK, 'data-type': type });
      assert.ok(C.prepare(a, { lang: 'es' }).href.startsWith('https://wa.me/?text='), type);
    }
  });

  test('sin la bandera testMode un numero vacio NO se respeta (comportamiento de siempre: numero por defecto)', () => {
    const C = contact();
    C.configure({ enabled: true, placements: '*', whatsapp: { url: WA_LINK, number: '' } });
    assert.equal(C.getConfig().whatsapp.number, REAL);
  });

  test('testMode no cambia nada si el servidor no manda whatsapp.number', () => {
    const C = contact();
    C.configure({ enabled: true, testMode: true, whatsapp: { url: WA_LINK } });
    assert.equal(C.getConfig().whatsapp.number, REAL);
  });
});

/* ---------------- mdn-events con MDN_TEST_MODE ---------------- */

const LANDMARKS = ['header', 'footer', 'nav', 'aside', 'main', 'section', 'article'];

class El extends H.FakeElement {
  closest(sel) {
    const parts = sel.split(',').map((s) => s.trim());
    let n = this;
    while (n) {
      for (const p of parts) if (matches(n, p)) return n;
      n = n.parentNode;
    }
    return null;
  }
}

function matches(n, sel) {
  if (!n || !n._attrs) return false;
  let m;
  if ((m = /^a\[href\]$/.exec(sel))) return n.tagName === 'A' && 'href' in n._attrs;
  if ((m = /^a\.([a-z-]+)$/.exec(sel))) return n.tagName === 'A' && n.classList.contains(m[1]);
  if ((m = /^\[([a-z-]+)="([^"]*)"\]$/.exec(sel))) return n._attrs[m[1]] === m[2];
  if ((m = /^\[([a-z-]+)\]$/.exec(sel))) return m[1] in n._attrs;
  if (/^[a-z]+$/.test(sel)) return LANDMARKS.includes(sel) && n.tagName.toLowerCase() === sel;
  if ((m = /^#([\w-]+)$/.exec(sel))) return n._attrs.id === m[1];
  return false;
}

function eventsEnv(opts) {
  opts = opts || {};
  const listeners = {};
  const fetched = [];
  const doc = H.makeDocument({ lang: 'es' });
  doc.readyState = 'complete';
  doc.addEventListener = (t, f) => { (listeners[t] = listeners[t] || []).push(f); };
  doc.createElement = (tag) => new El(tag, {});
  doc.querySelector = () => null;
  const ls = H.makeLocalStorage();
  const jar = Object.create(null);
  Object.defineProperty(doc, 'cookie', {
    configurable: true,
    get() { return Object.entries(jar).map(([k, v]) => k + '=' + v).join('; '); },
    set(v) { const f = String(v).split(';')[0]; const i = f.indexOf('='); if (i > 0) jar[f.slice(0, i)] = f.slice(i + 1); }
  });
  const win = {
    document: doc,
    localStorage: ls,
    location: { hostname: 'localhost', protocol: 'http:', pathname: opts.pathname || '/', search: '', href: 'http://localhost' + (opts.pathname || '/') },
    crypto: webcrypto,
    URLSearchParams,
    setInterval: () => 1,
    clearInterval() {},
    setTimeout: (fn, ms) => setTimeout(fn, ms),
    dataLayer: [],
    fetch: (url, o) => { fetched.push({ url, body: JSON.parse(o.body) }); return Promise.resolve({ ok: true }); },
    MDNEventsConfig: { url: '/track-conversion', csrf: 'T', folio: opts.folio }
  };
  if (opts.testMode) win.MDN_TEST_MODE = opts.testMode;
  win.window = win;
  win.globalThis = win;
  vm.createContext(win);
  for (const f of ['attribution.js', 'mdn-contact.js', 'mdn-events.js']) {
    vm.runInContext(fs.readFileSync(path.join(H.JS_DIR, f), 'utf8'), win, { filename: f });
    if (f === 'attribution.js' && win.MDNAttribution) win.MDNAttribution.capture();
  }
  const fire = (type, target) => (listeners[type] || []).forEach((fn) => fn({ target, button: 0 }));
  return { win, fetched, fire };
}

const link = (attrs, parent) => { const a = new El('a', attrs); if (parent) parent.appendChild(a); return a; };

describe('mdn-events: test:1 solo con window.MDN_TEST_MODE', () => {
  test('con MDN_TEST_MODE los POST a /track-conversion llevan test:1 (WhatsApp, telefono, cotizacion)', () => {
    const e = eventsEnv({ testMode: 'nonce-1' });
    e.fire('click', link({ href: 'https://wa.link/f28njw', 'data-placement': 'a' }));
    e.fire('click', link({ href: 'tel:+528181234567', 'data-placement': 'b' }));
    const b = new El('button', { 'data-action': 'solicitar-cotizacion' });
    b.textContent = 'Solicitar cotizacion';
    e.fire('click', b);
    assert.ok(e.fetched.length >= 3, 'se enviaron ' + e.fetched.length);
    for (const f of e.fetched) {
      assert.equal(f.url, '/track-conversion');
      assert.equal(f.body.test, 1, f.body.type);
    }
  });

  test('sin MDN_TEST_MODE el payload no trae la clave test', () => {
    const e = eventsEnv({});
    e.fire('click', link({ href: 'https://wa.link/f28njw', 'data-placement': 'a' }));
    e.fire('click', link({ href: 'tel:+528181234567', 'data-placement': 'b' }));
    assert.ok(e.fetched.length >= 2);
    for (const f of e.fetched) assert.ok(!('test' in f.body), f.body.type);
  });

  test('MDN_TEST_MODE falsy (""/0/null) tampoco agrega test', () => {
    for (const v of ['', 0, null, false]) {
      const e = eventsEnv({});
      e.win.MDN_TEST_MODE = v;
      e.fire('click', link({ href: 'https://wa.link/f28njw', 'data-placement': 'z' }));
      assert.equal(e.fetched.length, 1);
      assert.ok(!('test' in e.fetched[0].body));
    }
  });

  test('extremo a extremo: config de modo prueba (numero vacio) -> href wa.me/?text= con folio y evento con ese folio y test:1', () => {
    const e = eventsEnv({
      testMode: 'nonce-2',
      folio: { enabled: true, placements: '*', testMode: true, whatsapp: { url: WA_LINK, number: '' } }
    });
    const a = link({ href: WA_LINK, 'data-placement': 'home_hero' });
    e.fire('click', a);
    const href = a.getAttribute('href');
    assert.ok(href.startsWith('https://wa.me/?text='), href);
    assert.ok(!href.includes(REAL));
    const folio = /Folio: (MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5})/.exec(decodeURIComponent(href))[1];
    assert.equal(e.fetched.length, 1);
    assert.equal(e.fetched[0].body.contact_folio, folio);
    assert.equal(e.fetched[0].body.test, 1);
  });
});

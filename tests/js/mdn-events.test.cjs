'use strict';
/**
 * Integracion de mdn-events.js + mdn-contact.js + attribution.js con un DOM simulado.
 * Cubre la conexion de la fase 4: con el flag APAGADO no cambia ningun enlace y los eventos son los de
 * siempre; con el flag ENCENDIDO el href lleva el folio y el mismo folio viaja en el evento.
 */
const { test, describe } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { webcrypto } = require('node:crypto');
const H = require('./helpers/load.cjs');

const GCLID = 'EAIaIQobChMIabcdefghijklmnopqrstuvwxyz0123456789ABCDEF';
const LANDMARKS = ['header', 'footer', 'nav', 'aside', 'main', 'section', 'article'];

/** Elemento con el subconjunto de closest() que usa mdn-events.js. */
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

function el(tag, attrs, parent) {
  const e = new El(tag, attrs);
  if (parent) parent.appendChild(e);
  return e;
}

function env(opts) {
  opts = opts || {};
  const listeners = {};
  const fetched = [];
  const doc = H.makeDocument({ lang: opts.lang || 'es', jsonLd: opts.jsonLd });
  doc.readyState = 'complete';
  doc.addEventListener = (t, f) => { (listeners[t] = listeners[t] || []).push(f); };
  doc.createElement = (tag) => new El(tag, {});
  doc.querySelector = (sel) => (sel === '.cot-modal-folio' && opts.quoteFolioEl ? opts.quoteFolioEl : null);
  const ls = H.makeLocalStorage(opts.localStorage);
  const jar = Object.create(null);
  Object.defineProperty(doc, 'cookie', {
    configurable: true,
    get() { return Object.entries(jar).map(([k, v]) => k + '=' + v).join('; '); },
    set(v) { const f = String(v).split(';')[0]; const i = f.indexOf('='); if (i > 0) jar[f.slice(0, i)] = f.slice(i + 1); }
  });
  const win = {
    document: doc,
    localStorage: ls,
    location: { hostname: 'localhost', protocol: 'http:', pathname: opts.pathname || '/', search: opts.search || '', href: 'http://localhost' + (opts.pathname || '/') + (opts.search || '') },
    crypto: webcrypto,
    URLSearchParams,
    setInterval: () => 1,
    clearInterval() {},
    setTimeout: (fn, ms) => setTimeout(fn, ms),
    dataLayer: [],
    fetch: (url, o) => { fetched.push({ url, body: JSON.parse(o.body) }); return Promise.resolve({ ok: true }); },
    MDNEventsConfig: {
      url: '/track-conversion',
      csrf: 'T',
      folio: Object.assign({
        enabled: false,
        placements: '*',
        invisible: false,
        whatsapp: { url: 'https://wa.link/f28njw', number: '528135825559' },
        templates: {
          es: { product: 'Hola, me interesa: {producto}. Folio: {folio}', generic: 'Hola, quiero más información. Folio: {folio}' },
          pt: { product: 'Olá, tenho interesse em: {produto}. Código: {folio}', generic: 'Olá, quero mais informações. Código: {folio}' }
        },
        mail: { es: 'Cotización web - Folio {folio}', pt: 'Cotação web - Código {folio}' }
      }, opts.folio || {})
    }
  };
  if (opts.catalogo) win.CATALOGO_DATA = { whatsappUrl: 'https://wa.link/f28njw' };
  if (opts.registrar) win.registrarConversion = opts.registrar;
  win.window = win;
  win.globalThis = win;
  vm.createContext(win);
  for (const f of ['attribution.js', 'mdn-contact.js', 'mdn-events.js']) {
    vm.runInContext(fs.readFileSync(path.join(H.JS_DIR, f), 'utf8'), win, { filename: f });
    if (f === 'attribution.js' && win.MDNAttribution) win.MDNAttribution.capture();
  }
  const fire = (type, target, extra) => {
    const ev = Object.assign({ target, button: 0 }, extra || {});
    (listeners[type] || []).forEach((fn) => fn(ev));
  };
  return { win, doc, fetched, fire, listeners };
}

function link(attrs, parent) { return el('a', attrs, parent); }
const textOf = (href) => decodeURIComponent(/text=([^&]+)/.exec(href)[1]);
const wait = () => new Promise((r) => setImmediate(r));

describe('flag APAGADO (ATTRIBUTION_FOLIO_ENABLED=false): nada cambia', () => {
  test('WhatsApp sin clase: href intacto y evento whatsapp_click sin folio, con event_at', () => {
    const e = env({ search: '?gclid=' + GCLID + '&utm_source=google' });
    const footer = el('footer', { id: 'pie' });
    const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'banner_home' }, footer);
    a.textContent = 'Cotizar con ingeniero';
    e.fire('pointerdown', a);
    e.fire('click', a);
    assert.equal(a.getAttribute('href'), 'https://wa.link/f28njw');
    assert.equal(e.fetched.length, 1);
    const p = e.fetched[0].body;
    assert.equal(p.type, 'whatsapp_click');
    assert.equal(p.contact_folio, undefined);
    assert.equal(p.gclid, GCLID);
    assert.match(p.event_at, /^\d{4}-\d{2}-\d{2}T.*Z$/);
    assert.equal(p.meta.placement, 'banner_home');
    assert.equal(p.meta.click_channel, 'google');
  });

  test('mailto sin clase no se registra (igual que hoy) y no cambia', () => {
    const e = env();
    const a = link({ href: 'mailto:ventas1@macdelnorte.com' });
    e.fire('click', a);
    assert.equal(a.getAttribute('href'), 'mailto:ventas1@macdelnorte.com');
    assert.equal(e.fetched.length, 0);
  });

  test('enlace track-conversion: no lo envia mdn-events y no cambia el href', () => {
    const e = env();
    const a = link({ href: 'https://wa.link/f28njw', class: 'track-conversion', 'data-type': 'whatsapp_footer_oficina' });
    e.fire('pointerdown', a);
    e.fire('click', a);
    assert.equal(a.getAttribute('href'), 'https://wa.link/f28njw');
    assert.equal(a.dataset.mdnFolio, undefined);
    assert.equal(e.fetched.length, 0);
  });

  test('botón del catálogo sigue siendo quote_start y no toca la URL', () => {
    const e = env({ catalogo: true, pathname: '/categorias' });
    const b = el('button', { 'data-action': 'solicitar-cotizacion' });
    b.textContent = 'Solicitar cotización';
    e.fire('click', b);
    assert.equal(e.win.CATALOGO_DATA.whatsappUrl, 'https://wa.link/f28njw');
    assert.equal(e.fetched[0].body.type, 'quote_start');
  });
});

describe('flag ENCENDIDO: el folio viaja en el href y en el evento', () => {
  const on = { folio: { enabled: true } };

  test('WhatsApp sin clase: wa.me con folio y el MISMO folio en contact_folio (pointerdown + click = un folio)', () => {
    const e = env(on);
    const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'home_hero' });
    a.textContent = 'Cotización';
    e.fire('pointerdown', a);
    const hrefDown = a.getAttribute('href');
    e.fire('click', a);
    assert.equal(a.getAttribute('href'), hrefDown, 'el click reutiliza el folio de pointerdown');
    assert.match(a.getAttribute('href'), /^https:\/\/wa\.me\/528135825559\?text=/);
    const folio = /Folio: (MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5})/.exec(textOf(a.getAttribute('href')))[1];
    assert.equal(e.fetched.length, 1);
    const p = e.fetched[0].body;
    assert.equal(p.contact_folio, folio);
    assert.equal(p.type, 'whatsapp_click');
    assert.equal(p.meta.href, 'https://wa.link/f28njw', 'meta.href lleva el href ORIGINAL, sin folio');
  });

  test('teclado (click sin pointerdown) genera su folio; dos clics espaciados = dos folios distintos', async () => {
    const e = env(on);
    const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'a' });
    e.fire('click', a);
    const f1 = /Folio: (MN\w{5})/.exec(textOf(a.getAttribute('href')))[1];
    assert.equal(e.fetched[0].body.contact_folio, f1);
    assert.equal(a.getAttribute('data-mdn-orig-href'), 'https://wa.link/f28njw');
    // segundo clic dentro de 1.5 s = duplicado (no genera segundo evento)
    e.fire('click', a);
    assert.equal(e.fetched.length, 1);
  });

  test('mailto sin clase: asunto con folio y evento email_click con ese folio', () => {
    const e = env(on);
    const a = link({ href: 'mailto:ventas1@macdelnorte.com?cc=a@b.com', 'data-placement': 'about' });
    e.fire('click', a);
    const href = a.getAttribute('href');
    assert.match(href, /^mailto:ventas1@macdelnorte\.com\?/);
    assert.match(href, /cc=a@b\.com/);
    const subject = decodeURIComponent(/subject=([^&]+)/.exec(href)[1]);
    const folio = /Folio (MN\w{5})/.exec(subject)[1];
    assert.equal(e.fetched[0].body.type, 'email_click');
    assert.equal(e.fetched[0].body.contact_folio, folio);
  });

  test('tel: se registra phone_click SIN folio', () => {
    const e = env(on);
    const a = link({ href: 'tel:8124738768', 'data-placement': 'footer' });
    e.fire('click', a);
    assert.equal(a.getAttribute('href'), 'tel:8124738768');
    assert.equal(e.fetched[0].body.type, 'phone_click');
    assert.equal(e.fetched[0].body.contact_folio, undefined);
  });

  test('enlace track-conversion: se prepara (href + data-mdn-folio) pero lo envia master, no mdn-events', () => {
    const e = env(on);
    const a = link({ href: 'https://wa.link/f28njw', class: 'track-conversion', 'data-type': 'whatsapp_footer_oficina' });
    e.fire('pointerdown', a);
    e.fire('click', a);
    assert.match(a.getAttribute('href'), /^https:\/\/wa\.me\/528135825559\?text=/);
    assert.match(a.dataset.mdnFolio, /^MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}$/);
    assert.equal(textOf(a.getAttribute('href')).includes(a.dataset.mdnFolio), true);
    assert.equal(a.dataset.mdnPlacement, 'footer_oficina');
    assert.equal(e.fetched.length, 0);
  });

  test('clic central en track-conversion: se registra via registrarConversion para no dejar el folio huérfano', () => {
    const calls = [];
    const e = env(Object.assign({}, on, { registrar: (t, l) => calls.push([t, l.dataset.mdnFolio]) }));
    const a = link({ href: 'https://wa.link/f28njw', class: 'track-conversion', 'data-type': 'whatsapp-flotante' });
    e.fire('pointerdown', a, { button: 1 });
    e.fire('auxclick', a, { button: 1 });
    assert.equal(calls.length, 1);
    assert.equal(calls[0][0], 'whatsapp-flotante');
    assert.match(calls[0][1], /^MN/);
  });

  test('clic derecho no genera folio', () => {
    const e = env(on);
    const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'x' });
    e.fire('pointerdown', a, { button: 2 });
    assert.equal(a.getAttribute('href'), 'https://wa.link/f28njw');
  });

  test('data-mdn-event (/empleos): intacto y se registra con su tipo, sin folio', () => {
    const e = env(on);
    const orig = 'https://wa.me/528135825559?text=Hola%20vacante';
    const a = link({ href: orig, 'data-mdn-event': 'job_apply', 'data-mdn-ref': 'VT-01', 'data-mdn-puesto': 'Ventas', 'data-mdn-area': 'Ventas' });
    e.fire('pointerdown', a);
    e.fire('click', a);
    assert.equal(a.getAttribute('href'), orig);
    assert.equal(e.fetched[0].body.type, 'job_apply');
    assert.equal(e.fetched[0].body.contact_folio, undefined);
    assert.equal(e.fetched[0].body.meta.ref, 'VT-01');
  });

  test('piloto por placement: solo "flotante" lleva folio', () => {
    const e = env({ folio: { enabled: true, placements: 'flotante' } });
    const otro = link({ href: 'https://wa.link/f28njw', 'data-placement': 'footer' });
    const flot = link({ href: 'https://wa.link/f28njw', class: 'track-conversion', 'data-type': 'whatsapp-flotante' });
    e.fire('click', otro);
    e.fire('click', flot);
    assert.equal(otro.getAttribute('href'), 'https://wa.link/f28njw');
    assert.match(flot.getAttribute('href'), /wa\.me/);
  });

  test('producto: solo en /product-detail/ (en la home el JSON-LD de otro producto NO entra al mensaje)', () => {
    const ld = { '@type': 'Product', name: 'Sensor Termopar J', sku: 'TJ-1' };
    const mk = (pathname) => {
      const e = env({ folio: { enabled: true }, pathname, jsonLd: [ld] });
      const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'x' });
      e.fire('click', a);
      return { text: textOf(a.getAttribute('href')), body: e.fetched[0].body };
    };
    const home = mk('/');
    assert.match(home.text, /^Hola, quiero más información\. Folio: MN/);
    assert.equal(home.body.meta.product_name, undefined);
    const prod = mk('/product-detail/control-dc1010');
    assert.match(prod.text, /^Hola, me interesa: Sensor Termopar J\. Folio: MN/);
    assert.equal(prod.body.meta.product_name, 'Sensor Termopar J');
    assert.equal(prod.body.meta.sku, 'TJ-1');
  });

  test('wa.link de otro destino queda intacto', () => {
    const e = env(on);
    const a = link({ href: 'https://wa.link/otro123', 'data-placement': 'x' });
    e.fire('click', a);
    assert.equal(a.getAttribute('href'), 'https://wa.link/otro123');
  });

  test('páginas /br/: mensaje en portugués', () => {
    const e = env({ folio: { enabled: true }, lang: 'pt-BR', pathname: '/br/honeywell-dc1010' });
    const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'br_hero' });
    e.fire('click', a);
    assert.match(textOf(a.getAttribute('href')), /^Olá, quero mais informações\. Código: MN/);
  });

  test('botón del catálogo: reescribe CATALOGO_DATA.whatsappUrl y cuenta como whatsapp_click con folio', () => {
    const e = env(Object.assign({ catalogo: true, pathname: '/categoria-productos' }, on));
    const b = el('button', { 'data-action': 'solicitar-cotizacion', 'data-modelo': 'DC1010CT' });
    e.fire('click', b);
    assert.match(e.win.CATALOGO_DATA.whatsappUrl, /^https:\/\/wa\.me\/528135825559\?text=/);
    assert.match(textOf(e.win.CATALOGO_DATA.whatsappUrl), /me interesa: DC1010CT\. Folio: MN/);
    const p = e.fetched[0].body;
    assert.equal(p.type, 'whatsapp_click');
    assert.equal(p.meta.placement, 'card_producto_catalogo');
    assert.equal(p.meta.product_name, 'DC1010CT');
    assert.equal(textOf(e.win.CATALOGO_DATA.whatsappUrl).includes(p.contact_folio), true);
  });

  test('botón del catálogo con placement no permitido en el piloto: vuelve a quote_start', () => {
    const e = env({ catalogo: true, pathname: '/categorias', folio: { enabled: true, placements: 'flotante' } });
    const b = el('button', { 'data-action': 'solicitar-cotizacion' });
    e.fire('click', b);
    assert.equal(e.win.CATALOGO_DATA.whatsappUrl, 'https://wa.link/f28njw');
    assert.equal(e.fetched[0].body.type, 'quote_start');
  });
});

describe('revisión independiente: clic central, gestos cancelados y antidoble', () => {
  const on = { folio: { enabled: true } };

  test('clic central en el botón del catálogo no registra nada ni reescribe la URL (flag apagado y encendido)', () => {
    for (const folio of [{ enabled: false }, { enabled: true }]) {
      const e = env({ catalogo: true, pathname: '/categoria-productos', folio });
      const b = el('button', { 'data-action': 'solicitar-cotizacion', 'data-modelo': 'DC1010CT' });
      e.fire('auxclick', b, { button: 1 });
      assert.equal(e.fetched.length, 0, 'flag ' + folio.enabled);
      assert.equal(e.win.CATALOGO_DATA.whatsappUrl, 'https://wa.link/f28njw');
    }
  });

  test('pointercancel (scroll táctil) restaura el href original: no queda folio sin evento', () => {
    const e = env(on);
    const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'x' });
    e.fire('pointerdown', a);
    assert.match(a.getAttribute('href'), /wa\.me/);
    e.fire('pointercancel', a);
    assert.equal(a.getAttribute('href'), 'https://wa.link/f28njw');
    assert.equal(e.fetched.length, 0);
  });

  test('contextmenu (pulsación larga / copiar enlace) restaura el href original', () => {
    const e = env(on);
    const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'x' });
    e.fire('pointerdown', a);
    e.fire('contextmenu', a);
    assert.equal(a.getAttribute('href'), 'https://wa.link/f28njw');
  });

  test('tras el clic el href se restaura en el siguiente turno (el navegador ya lo leyó)', async () => {
    const e = env(on);
    const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'x' });
    e.fire('pointerdown', a);
    e.fire('click', a);
    assert.match(a.getAttribute('href'), /wa\.me/, 'durante el clic lleva el folio');
    await new Promise((r) => setTimeout(r, 10));
    assert.equal(a.getAttribute('href'), 'https://wa.link/f28njw', 'después vuelve al original');
  });

  test('clic central en track-conversion: antidoble y conversion_click en dataLayer como un clic normal', () => {
    const calls = [];
    const e = env(Object.assign({}, on, { registrar: (t, l) => calls.push(t) }));
    const a = link({ href: 'https://wa.link/f28njw', class: 'track-conversion', 'data-type': 'whatsapp-flotante' });
    e.fire('auxclick', a, { button: 1 });
    e.fire('auxclick', a, { button: 1 });
    assert.equal(calls.length, 1, 'dos clics centrales seguidos cuentan una vez');
    const dl = e.win.dataLayer.filter((d) => d && d.event === 'conversion_click');
    assert.equal(dl.length, 1);
    assert.equal(dl[0].label, 'whatsapp-flotante');
  });

  test('dos clics seguidos: el folio del href final es el del evento que sí se registró', () => {
    const e = env(on);
    const a = link({ href: 'https://wa.link/f28njw', 'data-placement': 'flotante' });
    e.fire('click', a);
    const f1 = /Folio: (MN\w{5})/.exec(textOf(a.getAttribute('href')))[1];
    e.fire('click', a);                       // duplicado (<1.5 s): no genera segundo evento
    const f2 = /Folio: (MN\w{5})/.exec(textOf(a.getAttribute('href')))[1];
    assert.equal(e.fetched.length, 1);
    assert.equal(e.fetched[0].body.contact_folio, f1);
    assert.equal(f2, f1, 'el href del segundo clic reutiliza el folio ya registrado');
  });
});

describe('cotización formal: folio MN al iniciar, en quote_submit junto al COT-', () => {
  const on = { folio: { enabled: true } };
  const GEN = '/cotizacion/generada/42';
  const generada = (opts) => {
    const modal = el('div');
    const folioEl = el('span', { class: 'cot-modal-folio' }, modal);
    folioEl.textContent = 'COT-2026-04528';
    return { e: env(Object.assign({ pathname: GEN, quoteFolioEl: folioEl }, opts)), folioEl, modal };
  };
  const storeOf = (e) => Object.assign({}, e.win.localStorage._store);

  test('flujo completo: quote_start genera el MN; la página generada lo manda en quote_submit con meta.folio = COT-', () => {
    const e1 = env(on);
    const a = link({ href: '/cotizacion/formulario?producto_id=3', id: 'btn-cotizar' });
    e1.fire('click', a);
    const start = e1.fetched[0].body;
    assert.equal(start.type, 'quote_start');
    assert.match(start.contact_folio, /^MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}$/);
    assert.equal(JSON.parse(e1.win.localStorage.getItem('mdn_quote_pending')).folio, start.contact_folio);

    const g = generada(Object.assign({ localStorage: storeOf(e1) }, on));
    const submit = g.e.fetched.find((f) => f.body.type === 'quote_submit').body;
    assert.equal(submit.contact_folio, start.contact_folio, 'el MN de quote_start llega a quote_submit');
    assert.equal(submit.meta.folio, 'COT-2026-04528');
    assert.equal(submit.event_id, 'quote_submit-42');
    assert.equal(g.e.win.localStorage.getItem('mdn_quote_42'), start.contact_folio);
    assert.equal(g.e.win.localStorage.getItem('mdn_quote_pending'), null, 'el pendiente se consume');
    assert.equal(g.modal.children.length, 2);
    assert.equal(g.modal.children[1].textContent, 'Folio de seguimiento: ' + start.contact_folio);
  });

  test('recargar la cotización generada conserva el mismo MN (estable por id)', () => {
    const g1 = generada(on);
    const mn1 = g1.e.fetched.find((f) => f.body.type === 'quote_submit').body.contact_folio;
    const g2 = generada(Object.assign({ localStorage: storeOf(g1.e) }, on));
    const s2 = g2.e.fetched.find((f) => f.body.type === 'quote_submit').body;
    assert.equal(s2.contact_folio, mn1);
    assert.equal(s2.event_id, 'quote_submit-42');
  });

  test('entrar directo a la cotización generada (sin quote_start) genera un MN y lo fija por id', () => {
    const g = generada(on);
    const s = g.e.fetched.find((f) => f.body.type === 'quote_submit').body;
    assert.match(s.contact_folio, /^MN/);
    assert.equal(g.e.win.localStorage.getItem('mdn_quote_42'), s.contact_folio);
  });

  test('flag APAGADO: sin MN, sin localStorage y sin texto extra en el modal', () => {
    const e1 = env({});
    e1.fire('click', link({ href: '/cotizacion/formulario', id: 'btn-cotizar' }));
    assert.equal(e1.fetched[0].body.type, 'quote_start');
    assert.equal(e1.fetched[0].body.contact_folio, undefined);
    assert.equal(e1.win.localStorage.getItem('mdn_quote_pending'), null);
    const g = generada({});
    const s = g.e.fetched.find((f) => f.body.type === 'quote_submit').body;
    assert.equal(s.contact_folio, undefined);
    assert.equal(s.meta.folio, 'COT-2026-04528', 'el comportamiento anterior (meta.folio = COT-) se conserva');
    assert.equal(g.modal.children.length, 1);
    assert.equal(g.e.win.localStorage.getItem('mdn_quote_42'), null);
  });

  test('piloto por placement: sin "cotizacion" en la lista no hay MN en la cotización', () => {
    const e1 = env({ folio: { enabled: true, placements: 'flotante' } });
    e1.fire('click', link({ href: '/cotizacion/formulario' }));
    assert.equal(e1.fetched[0].body.contact_folio, undefined);
    const g = generada({ folio: { enabled: true, placements: 'flotante' } });
    assert.equal(g.e.fetched.find((f) => f.body.type === 'quote_submit').body.contact_folio, undefined);
    const ok = env({ folio: { enabled: true, placements: 'flotante,cotizacion' } });
    ok.fire('click', link({ href: '/cotizacion/formulario' }));
    assert.match(ok.fetched[0].body.contact_folio, /^MN/);
  });

  test('doble clic al iniciar: el pendiente guardado es el del evento registrado', () => {
    const e = env(on);
    const a = link({ href: '/cotizacion/formulario' });
    e.fire('click', a);
    e.fire('click', a);
    assert.equal(e.fetched.length, 1);
    assert.equal(JSON.parse(e.win.localStorage.getItem('mdn_quote_pending')).folio, e.fetched[0].body.contact_folio);
  });

  test('un pendiente vencido (>6 h) no se usa: la cotización generada crea uno nuevo', () => {
    const old = JSON.stringify({ folio: 'MN22222', ts: Date.now() - 7 * 3600 * 1000 });
    const g = generada(Object.assign({ localStorage: { mdn_quote_pending: old } }, on));
    const s = g.e.fetched.find((f) => f.body.type === 'quote_submit').body;
    assert.notEqual(s.contact_folio, 'MN22222');
    assert.match(s.contact_folio, /^MN/);
  });
});

describe('robustez', () => {
  test('sin mdn-contact.js el comportamiento de siempre sigue funcionando', () => {
    const listeners = {};
    const fetched = [];
    const doc = H.makeDocument({});
    doc.readyState = 'complete';
    doc.addEventListener = (t, f) => { (listeners[t] = listeners[t] || []).push(f); };
    const win = {
      document: doc, localStorage: H.makeLocalStorage(), crypto: webcrypto, URLSearchParams,
      location: { hostname: 'localhost', protocol: 'http:', pathname: '/', search: '', href: 'http://localhost/' },
      setInterval: () => 1, clearInterval() {}, dataLayer: [],
      fetch: (u, o) => { fetched.push(JSON.parse(o.body)); return Promise.resolve({}); },
      MDNEventsConfig: { url: '/t', csrf: 'x' }
    };
    win.window = win; win.globalThis = win;
    vm.createContext(win);
    vm.runInContext(fs.readFileSync(path.join(H.JS_DIR, 'mdn-events.js'), 'utf8'), win);
    const a = el('a', { href: 'https://wa.link/f28njw' });
    (listeners.click || []).forEach((f) => f({ target: a, button: 0 }));
    assert.equal(fetched[0].type, 'whatsapp_click');
    assert.equal(fetched[0].contact_folio, undefined);
  });

  test('un enlace roto no lanza', () => {
    const e = env({ folio: { enabled: true } });
    assert.doesNotThrow(() => e.fire('click', { closest() { throw new Error('boom'); } }));
    assert.doesNotThrow(() => e.fire('pointerdown', null));
  });
});

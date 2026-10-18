'use strict';
const { test, describe, beforeEach } = require('node:test');
const assert = require('node:assert/strict');
const { webcrypto } = require('node:crypto');
const H = require('./helpers/load.cjs');
const { loadBrowserModule, anchor, FakeElement, makeDocument, microNode, plain } = H;

const WA_LINK = 'https://wa.link/f28njw';
const NUMBER = '528135825559';
const FOLIO_RE = /^MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}$/;

function fresh(opts) {
  const m = loadBrowserModule('mdn-contact.js', opts);
  return m.exports;
}

function enabledCfg(extra) {
  return Object.assign({ enabled: true, placements: '*' }, extra || {});
}

describe('folio', () => {
  const C = fresh();

  test('formato MN + 5 simbolos del alfabeto', () => {
    assert.equal(C.ALPHABET, '23456789BCDFGHJKMNPQRSTVWXYZ');
    assert.equal(C.ALPHABET.length, 28);
    for (let i = 0; i < 200; i++) {
      const f = C.newFolio();
      assert.match(f, FOLIO_RE);
      assert.equal(f.length, 7);
      assert.equal(C.isValidFolio(f), true);
    }
  });

  test('isValidFolio rechaza formatos invalidos', () => {
    for (const bad of ['', 'MN', 'MN1ABCD', 'MNA2345', 'MN23456X', 'mn23456', 'XX23456', 'MN2345', 'MN23 56', null, undefined, 12]) {
      assert.equal(C.isValidFolio(bad), false, String(bad));
    }
    assert.equal(C.isValidFolio('MN23456'), true);
    assert.equal(C.isValidFolio('MNZZZZZ'), true);
  });

  test('10 000 folios: todos validos, solo alfabeto, sin colisiones masivas, sin sesgo por simbolo', () => {
    const N = 10000;
    const seen = new Set();
    const counts = Object.create(null);
    for (const ch of C.ALPHABET) counts[ch] = 0;
    for (let i = 0; i < N; i++) {
      const f = C.newFolio();
      assert.match(f, FOLIO_RE);
      seen.add(f);
      for (const ch of f.slice(2)) counts[ch]++;
    }
    // Esperado de colisiones ~3 con 17.2M combinaciones.
    assert.ok(N - seen.size <= 15, 'demasiadas colisiones: ' + (N - seen.size));
    const expected = (N * 5) / 28; // ~1785.7
    const sigma = Math.sqrt(N * 5 * (1 / 28) * (27 / 28)); // ~41.5
    for (const ch of C.ALPHABET) {
      assert.ok(Math.abs(counts[ch] - expected) < 5 * sigma, `${ch}: ${counts[ch]} (esperado ~${expected.toFixed(0)})`);
    }
  });

  test('muestreo por rechazo: descarta bytes >= 224 y no usa modulo sesgado', () => {
    // Bytes guionados: 224, 225, 255 deben descartarse; luego 0,1,2,3,4 -> '2','3','4','5','6'.
    const script = [224, 225, 255, 0, 1, 2, 3, 4];
    const mockCrypto = { getRandomValues(arr) { for (let i = 0; i < arr.length; i++) arr[i] = script.length ? script.shift() : 27; return arr; } };
    const C2 = loadBrowserModule('mdn-contact.js', { crypto: mockCrypto }).exports;
    assert.equal(C2.newFolio(), 'MN23456');
  });

  test('el byte 223 (ultimo aceptado) mapea a 223 % 28 = 27 -> Z; 222 -> Y', () => {
    const script = [223, 222, 252, 251, 223];
    const mockCrypto = { getRandomValues(arr) { for (let i = 0; i < arr.length; i++) arr[i] = script.length ? script.shift() : 0; return arr; } };
    const C2 = loadBrowserModule('mdn-contact.js', { crypto: mockCrypto }).exports;
    // 223%28=27 'Z', 222%28=26 'Y', 252,251 rechazados, 223 'Z', luego 0 '2' x2
    assert.equal(C2.newFolio(), 'MNZYZ22');
  });

  test('bytes todos rechazables al inicio: sigue pidiendo hasta completar', () => {
    let calls = 0;
    const mockCrypto = { getRandomValues(arr) { calls++; arr.fill(calls < 4 ? 255 : 5); return arr; } };
    const C2 = loadBrowserModule('mdn-contact.js', { crypto: mockCrypto }).exports;
    assert.equal(C2.newFolio(), 'MN' + C2.ALPHABET[5].repeat(5));
    assert.ok(calls >= 4);
  });

  test('sin crypto cae a Math.random', () => {
    const C2 = loadBrowserModule('mdn-contact.js', { crypto: false }).exports;
    for (let i = 0; i < 100; i++) assert.match(C2.newFolio(), FOLIO_RE);
  });

  test('crypto que lanza cae a Math.random', () => {
    const C2 = loadBrowserModule('mdn-contact.js', { crypto: { getRandomValues() { throw new Error('x'); } } }).exports;
    assert.match(C2.newFolio(), FOLIO_RE);
  });

  test('con el crypto real tambien es valido (webcrypto)', () => {
    const C2 = loadBrowserModule('mdn-contact.js', { crypto: webcrypto }).exports;
    assert.match(C2.newFolio(), FOLIO_RE);
  });
});

describe('canonicalType (espejo del PHP)', () => {
  const C = fresh();
  test('mapea por prefijo', () => {
    assert.equal(C.canonicalType('whatsapp'), 'whatsapp_click');
    assert.equal(C.canonicalType('whatsapp_flotante'), 'whatsapp_click');
    assert.equal(C.canonicalType('whatsapp-flotante'), 'whatsapp_click');
    assert.equal(C.canonicalType('whatsapp_br_dc1010_hero'), 'whatsapp_click');
    assert.equal(C.canonicalType('whatsapp_click'), 'whatsapp_click');
    assert.equal(C.canonicalType('telefono'), 'phone_click');
    assert.equal(C.canonicalType('telefono_servicios'), 'phone_click');
    assert.equal(C.canonicalType('phone_click'), 'phone_click');
    assert.equal(C.canonicalType('correo'), 'email_click');
    assert.equal(C.canonicalType('correo_footer'), 'email_click');
    assert.equal(C.canonicalType('email_click'), 'email_click');
  });
  test('respeta page_visit, quote_*, add_to_cart*, job_* y desconocidos', () => {
    for (const t of ['page_visit', 'quote_start', 'quote_submit', 'add_to_cart', 'add_to_cart_modal', 'job_apply', 'job_whatsapp', 'algo_raro']) {
      assert.equal(C.canonicalType(t), t);
    }
  });
});

describe('placementOf', () => {
  const C = fresh();
  const cases = [
    ['whatsapp_footer_oficina', 'footer_oficina'],
    ['whatsapp-flotante', 'flotante'],
    ['whatsapp_flotante', 'flotante'],
    ['whatsapp_br_dc1010_hero', 'br_dc1010_hero'],
    ['whatsapp_servicios', 'servicios'],
    ['telefono_servicios', 'servicios'],
    ['correo_footer', 'footer'],
    ['whatsapp', 'producto'],
    ['telefono', 'producto'],
    ['', '']
  ];
  for (const [dt, expected] of cases) {
    test(`data-type "${dt}" -> "${expected}"`, () => {
      const a = anchor(dt ? { href: WA_LINK, 'data-type': dt } : { href: WA_LINK });
      assert.equal(C.placementOf(a), expected);
    });
  }

  test('data-placement propio tiene prioridad sobre data-type', () => {
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_servicios', 'data-placement': 'hero' });
    assert.equal(C.placementOf(a), 'hero');
  });

  test('data-placement de un ancestro', () => {
    const wrap = new FakeElement('section', { 'data-placement': 'banner_home' });
    const mid = new FakeElement('div', {}, []);
    wrap.appendChild(mid);
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_servicios' }, mid);
    assert.equal(C.placementOf(a), 'banner_home');
  });

  test('data-placement del propio enlace gana al del ancestro', () => {
    const wrap = new FakeElement('section', { 'data-placement': 'ancestro' });
    const a = anchor({ href: WA_LINK, 'data-placement': 'propio' }, wrap);
    assert.equal(C.placementOf(a), 'propio');
  });

  test('sin datos -> cadena vacia; no lanza con entradas rotas', () => {
    assert.equal(C.placementOf(anchor({ href: WA_LINK })), '');
    assert.equal(C.placementOf(null), '');
    assert.equal(C.placementOf({}), '');
    assert.equal(C.placementOf({ getAttribute() { throw new Error('x'); } }), '');
  });

  test('un data-type generico (whatsapp_click) no inventa placement', () => {
    assert.equal(C.placementOf(anchor({ href: WA_LINK, 'data-type': 'whatsapp_click' })), '');
  });
});

describe('classify', () => {
  let C;
  beforeEach(() => { C = fresh(); });

  test('whatsapp wa.link configurado', () => {
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_footer_oficina' });
    assert.deepEqual(plain(C.classify(a)), {
      kind: 'whatsapp', origHref: WA_LINK, legacyType: 'whatsapp_footer_oficina',
      canonicalType: 'whatsapp_click', placement: 'footer_oficina', skip: false
    });
  });

  test('funciona aunque enabled=false y no muta', () => {
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' });
    const before = JSON.stringify(a._attrs);
    C.classify(a);
    assert.equal(JSON.stringify(a._attrs), before);
  });

  test('detecta todas las formas de WhatsApp', () => {
    for (const href of [WA_LINK, 'https://wa.me/' + NUMBER, 'https://api.whatsapp.com/send?phone=' + NUMBER,
                        'whatsapp://send?phone=' + NUMBER, 'https://web.whatsapp.com/send?phone=' + NUMBER,
                        'https://www.wa.me/' + NUMBER + '?text=hola']) {
      const c = C.classify(anchor({ href }));
      assert.equal(c.kind, 'whatsapp', href);
      assert.equal(c.canonicalType, 'whatsapp_click');
      assert.equal(c.skip, false, href);
    }
  });

  test('tel y mailto', () => {
    const t = C.classify(anchor({ href: 'tel:+528135825559', 'data-type': 'telefono_servicios' }));
    assert.equal(t.kind, 'tel');
    assert.equal(t.canonicalType, 'phone_click');
    assert.equal(t.placement, 'servicios');
    const m = C.classify(anchor({ href: 'mailto:ventas@macdelnorte.com?subject=hola', 'data-type': 'correo_footer' }));
    assert.equal(m.kind, 'mailto');
    assert.equal(m.canonicalType, 'email_click');
    assert.equal(m.origHref, 'mailto:ventas@macdelnorte.com?subject=hola');
  });

  test('kind sin data-type: canonico segun el tipo de enlace', () => {
    assert.equal(C.classify(anchor({ href: WA_LINK })).canonicalType, 'whatsapp_click');
    assert.equal(C.classify(anchor({ href: 'tel:123' })).canonicalType, 'phone_click');
    assert.equal(C.classify(anchor({ href: 'mailto:a@b.c' })).canonicalType, 'email_click');
    assert.equal(C.classify(anchor({ href: WA_LINK })).legacyType, '');
  });

  test('no contacto -> null', () => {
    for (const href of ['/productos', 'https://example.com', '#', 'javascript:void(0)', 'https://wa.link.evil.com/x', '']) {
      assert.equal(C.classify(anchor({ href })), null, href);
    }
    assert.equal(C.classify(anchor({})), null);
    assert.equal(C.classify(null), null);
  });

  test('skip: data-mdn-event (incluso vacio)', () => {
    assert.equal(C.classify(anchor({ href: WA_LINK, 'data-mdn-event': 'job_apply' })).skip, true);
    assert.equal(C.classify(anchor({ href: WA_LINK, 'data-mdn-event': '' })).skip, true);
    assert.equal(C.classify(anchor({ href: WA_LINK, 'data-mdn-event': 'job_apply' })).kind, 'whatsapp');
  });

  test('skip: wa.link con otro slug, wa.me con otro numero, api.whatsapp con otro phone', () => {
    const a = C.classify(anchor({ href: 'https://wa.link/otro123', 'data-type': 'whatsapp_x' }));
    assert.equal(a.skip, true);
    assert.equal(a.kind, 'whatsapp');
    assert.equal(a.canonicalType, 'whatsapp_click');
    assert.equal(C.classify(anchor({ href: 'https://wa.me/521555000111' })).skip, true);
    assert.equal(C.classify(anchor({ href: 'https://api.whatsapp.com/send?phone=521555000111' })).skip, true);
    assert.equal(C.classify(anchor({ href: 'https://wa.me/message/ABCDEF' })).skip, true);
  });

  test('origHref sale de data-mdn-orig-href si existe', () => {
    const a = anchor({ href: 'https://wa.me/528135825559?text=hola%20MN23456', 'data-mdn-orig-href': WA_LINK });
    const c = C.classify(a);
    assert.equal(c.origHref, WA_LINK);
    assert.equal(c.skip, false);
  });

  test('canonico por data-type de contacto; tipo raro no pisa el tipo de enlace', () => {
    assert.equal(C.classify(anchor({ href: WA_LINK, 'data-type': 'cotizar_ahora' })).canonicalType, 'whatsapp_click');
  });

  test('con configure() distinto: el slug configurado es el que no se salta', () => {
    C.configure({ enabled: true, whatsapp: { url: 'https://wa.link/zzz999', number: NUMBER } });
    assert.equal(C.classify(anchor({ href: 'https://wa.link/zzz999' })).skip, false);
    assert.equal(C.classify(anchor({ href: WA_LINK })).skip, true);
  });
});

describe('prepare: WhatsApp', () => {
  let C, clock;
  beforeEach(() => {
    const m = loadBrowserModule('mdn-contact.js');
    C = m.exports; clock = m.clock;
    C.configure(enabledCfg());
  });

  const wa = (extra) => anchor(Object.assign({ href: WA_LINK, 'data-type': 'whatsapp_flotante' }, extra || {}));

  test('sin producto: mensaje generico en espanol con folio', () => {
    const a = wa();
    const r = C.prepare(a, { lang: 'es' });
    assert.match(r.folio, FOLIO_RE);
    assert.equal(r.kind, 'whatsapp');
    assert.equal(r.origHref, WA_LINK);
    assert.equal(r.canonicalType, 'whatsapp_click');
    assert.equal(r.legacyType, 'whatsapp_flotante');
    assert.equal(r.placement, 'flotante');
    assert.equal(r.product, null);
    const expected = 'https://wa.me/' + NUMBER + '?text=' + encodeURIComponent('Hola, quiero m\u00e1s informaci\u00f3n. Folio: ' + r.folio);
    assert.equal(r.href, expected);
    assert.equal(a.href, expected);
    assert.equal(a.getAttribute('data-mdn-orig-href'), WA_LINK);
    assert.equal(a.dataset.mdnFolio, r.folio);
    assert.equal(a.dataset.mdnPlacement, 'flotante');
  });

  test('con producto: usa la plantilla de producto', () => {
    const a = wa();
    const r = C.prepare(a, { lang: 'es', product: { name: 'Quemador DC1010', sku: 'DC1010' } });
    const msg = 'Hola, me interesa: Quemador DC1010. Folio: ' + r.folio;
    assert.equal(r.href, 'https://wa.me/' + NUMBER + '?text=' + encodeURIComponent(msg));
    assert.deepEqual(plain(r.product), { name: 'Quemador DC1010', sku: 'DC1010' });
  });

  test('producto sin nombre -> mensaje generico', () => {
    const r = C.prepare(wa(), { lang: 'es', product: { name: '', sku: 'X1' } });
    assert.ok(decodeURIComponent(r.href).includes('Hola, quiero m\u00e1s informaci\u00f3n. Folio: '));
  });

  test('portugues: ctx.lang, <html lang> y ruta /br/', () => {
    const r1 = C.prepare(wa(), { lang: 'pt-BR', product: { name: 'Ma\u00e7arico', sku: 'M1' } });
    assert.equal(decodeURIComponent(r1.href.split('text=')[1]), 'Ol\u00e1, tenho interesse em: Ma\u00e7arico. C\u00f3digo: ' + r1.folio);
    const r2 = C.prepare(wa(), { lang: 'pt' });
    assert.equal(decodeURIComponent(r2.href.split('text=')[1]), 'Ol\u00e1, quero mais informa\u00e7\u00f5es. C\u00f3digo: ' + r2.folio);

    const docPt = makeDocument({ lang: 'pt-BR' });
    const r3 = C.prepare(wa(), { doc: docPt });
    assert.match(decodeURIComponent(r3.href.split('text=')[1]), /^Ol\u00e1, quero mais informa\u00e7\u00f5es\. C\u00f3digo: MN/);

    const Cbr = loadBrowserModule('mdn-contact.js', { pathname: '/br/calibracao', lang: '' }).exports;
    Cbr.configure(enabledCfg());
    const r4 = Cbr.prepare(wa());
    assert.match(decodeURIComponent(r4.href.split('text=')[1]), /C\u00f3digo: MN/);

    const r5 = C.prepare(wa(), { lang: 'es', doc: docPt });
    assert.match(decodeURIComponent(r5.href.split('text=')[1]), /Folio: MN/);
  });

  test('lang del documento es -> espanol; sin datos -> espanol', () => {
    assert.match(decodeURIComponent(C.prepare(wa(), { doc: makeDocument({ lang: 'es-MX' }) }).href), /Folio: MN/);
    assert.match(decodeURIComponent(C.prepare(wa()).href), /Folio: MN/);
  });

  test('folio NUEVO en cada llamada', () => {
    const a = wa();
    const folios = new Set();
    for (let i = 0; i < 25; i++) { folios.add(C.prepare(a, {}).folio); clock.now += 2000; }
    assert.ok(folios.size >= 24);
    assert.equal(a.dataset.mdnFolio.length, 7);
  });

  test('idempotente: reconstruye desde data-mdn-orig-href, que se guarda solo la primera vez', () => {
    const a = wa();
    const r1 = C.prepare(a, { lang: 'es' });
    clock.now += 2000;
    const r2 = C.prepare(a, { lang: 'es' });
    clock.now += 2000;
    const r3 = C.prepare(a, { lang: 'es', product: { name: 'P', sku: 's' } });
    assert.equal(a.getAttribute('data-mdn-orig-href'), WA_LINK);
    assert.equal(r2.origHref, WA_LINK);
    assert.equal(r3.origHref, WA_LINK);
    assert.notEqual(r1.folio, r2.folio);
    // El texto solo contiene UN folio (no se acumulan folios)
    const text = decodeURIComponent(r3.href.split('text=')[1]);
    assert.equal((text.match(/MN[2-9A-Z]{5}/g) || []).length, 1);
    assert.equal(text, 'Hola, me interesa: P. Folio: ' + r3.folio);
    assert.equal(a.href, r3.href);
    assert.equal(a.dataset.mdnFolio, r3.folio);
  });

  test('data-mdn-orig-href ya existente (otra carga) no se pisa', () => {
    const a = anchor({ href: 'https://wa.me/' + NUMBER + '?text=viejo', 'data-mdn-orig-href': WA_LINK, 'data-type': 'whatsapp' });
    const r = C.prepare(a);
    assert.equal(a.getAttribute('data-mdn-orig-href'), WA_LINK);
    assert.equal(r.origHref, WA_LINK);
    assert.ok(decodeURIComponent(r.href).includes('Folio: ' + r.folio));
    assert.ok(!decodeURIComponent(r.href).includes('viejo'));
  });

  test('wa.me con text= existente: se conserva y se agrega el folio al final', () => {
    const a = anchor({ href: 'https://wa.me/' + NUMBER + '?text=' + encodeURIComponent('Quiero cotizar el modelo X'), 'data-type': 'whatsapp_hero' });
    const r = C.prepare(a, { lang: 'es', product: { name: 'Ignorado', sku: '1' } });
    assert.equal(decodeURIComponent(r.href.split('text=')[1]), 'Quiero cotizar el modelo X Folio: ' + r.folio);
    assert.ok(r.href.startsWith('https://wa.me/' + NUMBER + '?text='));
    const rpt = C.prepare(a, { lang: 'pt' });
    assert.equal(decodeURIComponent(rpt.href.split('text=')[1]), 'Quiero cotizar el modelo X C\u00f3digo: ' + rpt.folio);
  });

  test('wa.me sin text= usa la plantilla', () => {
    const a = anchor({ href: 'https://wa.me/' + NUMBER, 'data-type': 'whatsapp' });
    const r = C.prepare(a, { lang: 'es' });
    assert.equal(r.placement, 'producto');
    assert.equal(decodeURIComponent(r.href.split('text=')[1]), 'Hola, quiero m\u00e1s informaci\u00f3n. Folio: ' + r.folio);
  });

  test('data-mdn-event queda intacto', () => {
    const a = wa({ 'data-mdn-event': 'job_apply' });
    const before = JSON.stringify(a._attrs);
    const r = C.prepare(a, {});
    assert.equal(JSON.stringify(a._attrs), before);
    assert.equal(a.href, WA_LINK);
    assert.equal(r.skip, true);
    assert.equal(r.folio, undefined);
  });

  test('wa.link de otro slug y wa.me de otro numero quedan intactos', () => {
    for (const href of ['https://wa.link/otro123', 'https://wa.me/521555000111?text=hola']) {
      const a = anchor({ href, 'data-type': 'whatsapp_x' });
      const before = JSON.stringify(a._attrs);
      const r = C.prepare(a, {});
      assert.equal(JSON.stringify(a._attrs), before, href);
      assert.equal(r.skip, true);
    }
  });

  test('enabled=false (por defecto) no muta nada y devuelve la clasificacion', () => {
    const C0 = fresh(); // sin configure
    const a = wa();
    const before = JSON.stringify(a._attrs);
    const r = C0.prepare(a, {});
    assert.equal(JSON.stringify(a._attrs), before);
    assert.equal(r.kind, 'whatsapp');
    assert.equal(r.origHref, WA_LINK);
    assert.equal(r.folio, undefined);
    C0.configure({ enabled: false, placements: '*' });
    C0.prepare(a, {});
    assert.equal(JSON.stringify(a._attrs), before);
    assert.equal(C0.prepare(anchor({ href: '/x' }), {}), null);
  });

  test('placement no permitido: sin mutacion; permitido (lista y cadena) funciona', () => {
    const a = wa(); // placement flotante
    const before = JSON.stringify(a._attrs);
    C.configure({ enabled: true, placements: ['footer', 'hero'] });
    let r = C.prepare(a, {});
    assert.equal(JSON.stringify(a._attrs), before);
    assert.equal(r.folio, undefined);
    C.configure({ enabled: true, placements: ['Hero', 'flotante'] });
    r = C.prepare(a, {});
    assert.match(r.folio, FOLIO_RE);
    const b = wa();
    C.configure({ enabled: true, placements: 'hero, flotante' });
    assert.match(C.prepare(b, {}).folio, FOLIO_RE);
    const c = wa();
    C.configure({ enabled: true, placements: 'hero' });
    assert.equal(C.prepare(c, {}).folio, undefined);
    assert.equal(c.href, WA_LINK);
    // placement vacio con lista -> no permitido; con '*' -> permitido
    const d = anchor({ href: WA_LINK });
    assert.equal(C.prepare(d, {}).folio, undefined);
    C.configure({ enabled: true, placements: '*' });
    assert.match(C.prepare(d, {}).folio, FOLIO_RE);
  });

  test('configure: plantillas, numero y arreglos personalizados', () => {
    C.configure(enabledCfg({
      whatsapp: { url: WA_LINK, number: '521999000111' },
      templates: { es: { generic: 'Buenas {folio}' } }
    }));
    const r = C.prepare(wa(), {});
    assert.equal(r.href, 'https://wa.me/521999000111?text=' + encodeURIComponent('Buenas ' + r.folio));
    // El resto de plantillas conserva el default
    const rp = C.prepare(wa(), { product: { name: 'Z', sku: '1' } });
    assert.equal(decodeURIComponent(rp.href.split('text=')[1]), 'Hola, me interesa: Z. Folio: ' + rp.folio);
  });

  test('configure sin argumentos / basura -> inerte', () => {
    C.configure();
    assert.equal(C.getConfig().enabled, false);
    C.configure('x');
    assert.equal(C.getConfig().enabled, false);
    C.configure({ enabled: 'false' });
    assert.equal(C.getConfig().enabled, false);
    C.configure({ enabled: 'true' });
    assert.equal(C.getConfig().enabled, true);
  });

  test('api.whatsapp.com con nuestro numero tambien se reescribe', () => {
    const a = anchor({ href: 'https://api.whatsapp.com/send?phone=' + NUMBER + '&text=Hola%20equipo', 'data-type': 'whatsapp_x' });
    const r = C.prepare(a, {});
    assert.equal(decodeURIComponent(r.href.split('text=')[1]), 'Hola equipo Folio: ' + r.folio);
    assert.ok(r.href.startsWith('https://wa.me/' + NUMBER));
  });
});

describe('prepare: mailto', () => {
  let C, clock;
  beforeEach(() => {
    const m = loadBrowserModule('mdn-contact.js');
    C = m.exports; clock = m.clock;
    C.configure(enabledCfg());
  });
  const mail = (href) => anchor({ href, 'data-type': 'correo_footer' });

  test('sin parametros previos agrega subject codificado', () => {
    const a = mail('mailto:ventas@macdelnorte.com');
    const r = C.prepare(a, { lang: 'es' });
    assert.equal(r.kind, 'mailto');
    assert.equal(r.canonicalType, 'email_click');
    assert.equal(r.placement, 'footer');
    assert.match(r.folio, FOLIO_RE);
    assert.equal(r.href, 'mailto:ventas@macdelnorte.com?subject=Cotizaci%C3%B3n%20web%20-%20Folio%20' + r.folio);
    assert.equal(a.href, r.href);
    assert.equal(a.getAttribute('data-mdn-orig-href'), 'mailto:ventas@macdelnorte.com');
  });

  test('conserva otros parametros y reemplaza subject existente', () => {
    const a = mail('mailto:ventas@macdelnorte.com?subject=Viejo&body=Hola%20equipo&cc=a@b.com');
    const r = C.prepare(a, { lang: 'es' });
    assert.equal(r.href,
      'mailto:ventas@macdelnorte.com?subject=Cotizaci%C3%B3n%20web%20-%20Folio%20' + r.folio + '&body=Hola%20equipo&cc=a@b.com');
    const b = mail('mailto:ventas@macdelnorte.com?body=Hola&cc=x@y.z');
    const rb = C.prepare(b, { lang: 'es' });
    assert.equal(rb.href,
      'mailto:ventas@macdelnorte.com?body=Hola&cc=x@y.z&subject=Cotizaci%C3%B3n%20web%20-%20Folio%20' + rb.folio);
  });

  test('portugues', () => {
    const r = C.prepare(mail('mailto:ventas@macdelnorte.com'), { lang: 'pt' });
    assert.equal(r.href, 'mailto:ventas@macdelnorte.com?subject=Cota%C3%A7%C3%A3o%20web%20-%20C%C3%B3digo%20' + r.folio);
  });

  test('idempotente y folio nuevo por llamada', () => {
    const a = mail('mailto:ventas@macdelnorte.com?body=Hola');
    const r1 = C.prepare(a, { lang: 'es' });
    clock.now += 2000;
    const r2 = C.prepare(a, { lang: 'es' });
    assert.notEqual(r1.folio, r2.folio);
    assert.equal(a.getAttribute('data-mdn-orig-href'), 'mailto:ventas@macdelnorte.com?body=Hola');
    assert.equal(a.href.match(/subject=/g).length, 1);
    assert.equal(a.href, r2.href);
  });

  test('conserva body= y fragmento (#); el folio del asunto es el devuelto', () => {
    const a = mail('mailto:ventas@macdelnorte.com?body=Hola%20equipo&cc=x@y.z#seccion');
    const r = C.prepare(a, { lang: 'es' });
    assert.equal(r.href,
      'mailto:ventas@macdelnorte.com?body=Hola%20equipo&cc=x@y.z&subject=Cotizaci%C3%B3n%20web%20-%20Folio%20' + r.folio + '#seccion');
    assert.equal(a.href, r.href);
    assert.equal(a.getAttribute('data-mdn-orig-href'), 'mailto:ventas@macdelnorte.com?body=Hola%20equipo&cc=x@y.z#seccion');
    // Segunda pasada (fuera de ventana) reconstruye desde el original sin duplicar subject
    clock.now += 2000;
    const r2 = C.prepare(a, { lang: 'es' });
    assert.equal(r2.href.match(/subject=/g).length, 1);
    assert.ok(r2.href.endsWith('#seccion'));
  });

  test('subject en mayusculas se reemplaza y no se invisibiliza', () => {
    const C2 = fresh();
    C2.configure(enabledCfg({ invisible: true }));
    const a = mail('mailto:a@b.com?Subject=Hola');
    const r = C2.prepare(a, {});
    assert.equal((r.href.match(/ubject=/g) || []).length, 1);
    assert.ok(!/%E2%80%8B|%E2%81%A0/.test(r.href), 'el correo no lleva folio invisible');
  });
});

describe('prepare: tel', () => {
  test('no genera folio ni muta', () => {
    const C = fresh();
    C.configure(enabledCfg());
    const a = anchor({ href: 'tel:+528135825559', 'data-type': 'telefono_servicios' });
    const before = JSON.stringify(a._attrs);
    const r = C.prepare(a, {});
    assert.equal(r.kind, 'tel');
    assert.equal(r.folio, null);
    assert.equal(r.canonicalType, 'phone_click');
    assert.equal(r.origHref, 'tel:+528135825559');
    assert.equal(JSON.stringify(a._attrs), before);
  });
});

describe('folio invisible', () => {
  const C = fresh();

  test('ida y vuelta', () => {
    for (const f of ['MN7K4Q2', 'MN23456', 'MNZZZZZ']) {
      const enc = C.encodeInvisible(f);
      assert.equal(enc.charAt(0), '\u2060');
      assert.equal(enc.charAt(enc.length - 1), '\u2060');
      assert.equal(enc.length, 2 + f.length * 8);
      assert.match(enc, /^\u2060[\u200b\u200c]+\u2060$/);
      assert.equal(C.decodeInvisible(enc), f);
      assert.equal(C.decodeInvisible('Hola, texto. Folio: ' + f + ' ' + enc), f);
    }
  });

  test('codificacion exacta: MSB primero, 0=U+200B, 1=U+200C', () => {
    // 'M' = 0x4D = 01001101
    const enc = C.encodeInvisible('M');
    const bits = [...enc.slice(1, -1)].map((c) => (c === '\u200c' ? '1' : '0')).join('');
    assert.equal(bits, '01001101');
  });

  test('decode sin invisible -> cadena vacia', () => {
    assert.equal(C.decodeInvisible('Hola Folio: MN23456'), '');
    assert.equal(C.decodeInvisible(''), '');
    assert.equal(C.decodeInvisible(null), '');
  });

  test('apagado por defecto: el mensaje no lleva caracteres invisibles', () => {
    const C1 = fresh();
    C1.configure(enabledCfg());
    const r = C1.prepare(anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' }), {});
    const text = decodeURIComponent(r.href.split('text=')[1]);
    assert.ok(!/[\u2060\u200b\u200c]/.test(text));
    assert.equal(C1.getConfig().invisible, false);
  });

  test('encendido: se agrega al FINAL despues del folio visible y se recupera', () => {
    const C1 = fresh();
    C1.configure(enabledCfg({ invisible: true }));
    const r = C1.prepare(anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' }), { lang: 'es' });
    const text = decodeURIComponent(r.href.split('text=')[1]);
    assert.ok(text.startsWith('Hola, quiero m\u00e1s informaci\u00f3n. Folio: ' + r.folio + '\u2060'));
    assert.ok(text.endsWith('\u2060'));
    assert.equal(C1.decodeInvisible(text), r.folio);
    assert.equal(text.replace(/[\u2060\u200b\u200c]/g, ''), 'Hola, quiero m\u00e1s informaci\u00f3n. Folio: ' + r.folio);
  });
});

describe('productContext', () => {
  const C = fresh();

  test('JSON-LD Product', () => {
    const doc = makeDocument({ jsonLd: [{ '@context': 'https://schema.org', '@type': 'Product', name: ' Quemador DC1010 ', sku: 'DC1010' }] });
    assert.deepEqual(plain(C.productContext(doc)), { name: 'Quemador DC1010', sku: 'DC1010' });
  });

  test('Product dentro de @graph, y otros scripts invalidos/ajenos se ignoran', () => {
    const doc = makeDocument({
      jsonLd: [
        '{ esto no es json',
        { '@type': 'Organization', name: 'Mac del Norte' },
        { '@context': 'https://schema.org', '@graph': [{ '@type': 'BreadcrumbList' }, { '@type': ['Thing', 'Product'], name: 'Valvula', sku: 'V-9' }] }
      ]
    });
    assert.deepEqual(plain(C.productContext(doc)), { name: 'Valvula', sku: 'V-9' });
  });

  test('JSON-LD en arreglo', () => {
    const doc = makeDocument({ jsonLd: [[{ '@type': 'WebSite' }, { '@type': 'Product', name: 'A', sku: 'B' }]] });
    assert.deepEqual(plain(C.productContext(doc)), { name: 'A', sku: 'B' });
  });

  test('microdatos: sku por itemprop y nombre por h1[itemprop=name] cuando no hay JSON-LD', () => {
    const doc = makeDocument({ nodes: [microNode('sku', 'SKU-77'), microNode('name', 'Regulador', undefined, 'h1')] });
    assert.deepEqual(plain(C.productContext(doc)), { name: 'Regulador', sku: 'SKU-77' });
  });

  // product-detail.blade.php: la marca es <span itemprop="name"> ANTES del <h1 itemprop="name">.
  const BRAND_THEN_H1 = () => [
    microNode('name', 'Honeywell', undefined, 'span'),
    microNode('name', 'Valvula 1/2&quot; Control &amp; Safety', undefined, 'h1'),
    microNode('sku', 'SKU-500')
  ];

  test('JSON-LD invalido + marca antes del h1: devuelve el nombre del h1, no la marca', () => {
    const invalid = '{"@context":"https://schema.org","@type":"Product","description":"Valvula de 1\\&quot; para gas\n con salto crudo","brand":{"@type":"Brand","name":"Honeywell"}}';
    assert.throws(() => JSON.parse(invalid));
    const doc = makeDocument({ jsonLd: [invalid], nodes: BRAND_THEN_H1() });
    assert.deepEqual(plain(C.productContext(doc)), { name: 'Valvula 1/2" Control & Safety', sku: 'SKU-500' });
  });

  test('sin JSON-LD y con marca antes del h1: nombre del h1, entidades decodificadas', () => {
    const doc = makeDocument({ nodes: BRAND_THEN_H1() });
    const r = C.productContext(doc);
    assert.equal(r.name, 'Valvula 1/2" Control & Safety');
    assert.notEqual(r.name, 'Honeywell');
  });

  test('JSON-LD invalido: extrae name/sku del Product con regex, a profundidad del producto (no la marca)', () => {
    const invalid = '{"@context":"https://schema.org","@type":"Product","brand":{"@type":"Brand","name":"Honeywell"},'
      + '"name":"Regulador 3/4\\&quot; Fisher","sku":"FS-99","description":"Linea 1\nLinea 2 \\&#039;x\\&#039;"}';
    assert.throws(() => JSON.parse(invalid));
    const doc = makeDocument({ jsonLd: [invalid], nodes: [microNode('name', 'Honeywell', undefined, 'span')] });
    assert.deepEqual(plain(C.productContext(doc)), { name: 'Regulador 3/4" Fisher', sku: 'FS-99' });
  });

  test('JSON-LD invalido dentro de @graph: se encuentra el Product', () => {
    const invalid = '{"@graph":[{"@type":"Organization","name":"Mac del Norte"},{"@type":"Product","name":"Piloto &amp; Termopar","sku":"P-1","description":"a\nb"}]}';
    assert.throws(() => JSON.parse(invalid));
    assert.deepEqual(plain(C.productContext(makeDocument({ jsonLd: [invalid] }))), { name: 'Piloto & Termopar', sku: 'P-1' });
  });

  test('entidades decodificadas en JSON-LD valido', () => {
    const doc = makeDocument({ jsonLd: [{ '@type': 'Product', name: 'Valvula 1/2&quot; Control &amp; Safety &#039;X&#39; &lt;b&gt;&nbsp;fin', sku: 'S&amp;1' }] });
    assert.deepEqual(plain(C.productContext(doc)), { name: 'Valvula 1/2" Control & Safety \'X\' <b> fin', sku: 'S&1' });
  });

  test('saltos de linea y espacios colapsados', () => {
    const doc = makeDocument({ jsonLd: [{ '@type': 'Product', name: '  Quemador\n\n  DC1010 \t Gas  ', sku: ' A\nB ' }] });
    assert.deepEqual(plain(C.productContext(doc)), { name: 'Quemador DC1010 Gas', sku: 'A B' });
    const doc2 = makeDocument({ nodes: [microNode('name', '  Regulador\n   doble  ', undefined, 'h1')] });
    assert.equal(C.productContext(doc2).name, 'Regulador doble');
  });

  test('respaldo del nombre: h1.product-title, luego un unico h1; varios h1 sin pistas -> vacio', () => {
    const t = new FakeElement('h1', { class: 'product-title' }); t.textContent = 'Por clase';
    const other = new FakeElement('h1'); other.textContent = 'Otro';
    assert.equal(C.productContext(makeDocument({ nodes: [other, t] })).name, 'Por clase');
    const only = new FakeElement('h1'); only.textContent = 'Unico';
    assert.equal(C.productContext(makeDocument({ nodes: [only] })).name, 'Unico');
    const h2 = new FakeElement('h1'); h2.textContent = 'Dos';
    assert.equal(C.productContext(makeDocument({ nodes: [only, h2] })).name, '');
    // un span itemprop=name suelto (marca) nunca se usa
    assert.equal(C.productContext(makeDocument({ nodes: [microNode('name', 'Honeywell', undefined, 'span')] })).name, '');
  });

  test('el nombre limpio llega al mensaje de WhatsApp', () => {
    const M = loadBrowserModule('mdn-contact.js');
    M.exports.configure(enabledCfg());
    const ctx = { lang: 'es', product: M.exports.productContext(makeDocument({ nodes: BRAND_THEN_H1() })) };
    const r = M.exports.prepare(anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' }), ctx);
    assert.equal(decodeURIComponent(r.href.split('text=')[1]), 'Hola, me interesa: Valvula 1/2" Control & Safety. Folio: ' + r.folio);
  });

  test('microdatos con atributo content; completa el campo faltante del JSON-LD', () => {
    const doc = makeDocument({
      jsonLd: [{ '@type': 'Product', name: 'Solo nombre' }],
      microdata: [microNode('sku', '', 'SKU-CONTENT')]
    });
    assert.deepEqual(plain(C.productContext(doc)), { name: 'Solo nombre', sku: 'SKU-CONTENT' });
  });

  test('sin datos -> vacio; no lanza con documentos rotos', () => {
    assert.deepEqual(plain(C.productContext(makeDocument())), { name: '', sku: '' });
    assert.deepEqual(plain(C.productContext(null)), { name: '', sku: '' });
    assert.deepEqual(plain(C.productContext({})), { name: '', sku: '' });
    assert.deepEqual(plain(C.productContext({ querySelectorAll() { throw new Error('x'); } })), { name: '', sku: '' });
  });
});

describe('dedupe', () => {
  let C;
  beforeEach(() => { C = fresh(); });

  test('dedupeKey = canonicalType|origHref|placement', () => {
    const c = C.classify(anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' }));
    assert.equal(C.dedupeKey(c), 'whatsapp_click|' + WA_LINK + '|flotante');
    assert.equal(C.dedupeKey({ canonicalType: 'a', origHref: 'b', placement: 'c' }), 'a|b|c');
  });

  test('misma key en menos de 1500 ms -> true; a partir de 1500 ms -> false', () => {
    assert.equal(C.shouldDedupe('k', 1000), false);
    assert.equal(C.shouldDedupe('k', 1000), true);
    assert.equal(C.shouldDedupe('k', 2499), true);
    assert.equal(C.shouldDedupe('k', 2500), false); // 1500 ms exactos: ya no es duplicado
    assert.equal(C.shouldDedupe('k', 2600), true);  // se registro la vista de 2500
  });

  test('keys distintas no se afectan', () => {
    assert.equal(C.shouldDedupe('a', 1000), false);
    assert.equal(C.shouldDedupe('b', 1100), false);
    assert.equal(C.shouldDedupe('a', 1200), true);
    assert.equal(C.shouldDedupe('b', 1200), true);
  });

  test('sin now usa Date.now() y resetDedupe limpia', () => {
    assert.equal(C.shouldDedupe('x'), false);
    assert.equal(C.shouldDedupe('x'), true);
    C.resetDedupe();
    assert.equal(C.shouldDedupe('x'), false);
  });

  test('dedupeKey sin placement usa origHref + texto del enlace (primeros 30) como respaldo', () => {
    const card = (txt) => {
      const a = anchor({ href: WA_LINK });
      a.textContent = txt;
      return a;
    };
    const k1 = C.dedupeKey(C.classify(card('Cotizar Quemador DC1010')));
    const k2 = C.dedupeKey(C.classify(card('Cotizar Valvula V-9')));
    assert.notEqual(k1, k2);
    assert.equal(k1, C.dedupeKey(C.classify(card('  Cotizar   Quemador DC1010 '))));
    const long = 'x'.repeat(30);
    assert.equal(C.dedupeKey(C.classify(card(long + 'AAA'))), C.dedupeKey(C.classify(card(long + 'BBB'))));
    // con placement el texto no interviene
    const p = (txt) => { const a = anchor({ href: WA_LINK, 'data-placement': 'hero' }); a.textContent = txt; return a; };
    assert.equal(C.dedupeKey(C.classify(p('uno'))), C.dedupeKey(C.classify(p('dos'))));
    assert.equal(C.classify(p('uno')).text, undefined);
  });
});

describe('prepare: folio reutilizado dentro de la ventana de dedupe', () => {
  let C, clock;
  beforeEach(() => {
    const m = loadBrowserModule('mdn-contact.js');
    C = m.exports; clock = m.clock;
    C.configure(enabledCfg());
  });
  const wa = (extra) => anchor(Object.assign({ href: WA_LINK, 'data-type': 'whatsapp_flotante' }, extra || {}));
  const folioIn = (href) => (/(MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5})/.exec(decodeURIComponent(href)) || [])[1];

  test('dos prepare seguidos (< 1500 ms) sobre el mismo enlace -> mismo folio y href coincide', () => {
    const a = wa();
    const r1 = C.prepare(a, {});
    clock.now += 1499;
    const r2 = C.prepare(a, {});
    assert.equal(r2.folio, r1.folio);
    assert.equal(folioIn(a.href), r1.folio);
  });

  test('otro enlace con la misma key (mismo href, tipo y placement) tambien reutiliza', () => {
    const r1 = C.prepare(wa(), {});
    clock.now += 500;
    const r2 = C.prepare(wa(), {});
    assert.equal(r2.folio, r1.folio);
  });

  test('pasados 1500 ms -> folio distinto', () => {
    const a = wa();
    const r1 = C.prepare(a, {});
    clock.now += 1500;
    const r2 = C.prepare(a, {});
    assert.notEqual(r2.folio, r1.folio);
    assert.equal(folioIn(a.href), r2.folio);
  });

  test('la ventana no se extiende con los reintentos (igual que shouldDedupe)', () => {
    const a = wa();
    const r1 = C.prepare(a, {});
    clock.now += 1000;
    assert.equal(C.prepare(a, {}).folio, r1.folio);
    clock.now += 1000; // 2000 ms desde el primero
    assert.notEqual(C.prepare(a, {}).folio, r1.folio);
  });

  test('keys distintas -> folios distintos (placement, href, tipo)', () => {
    const f = new Set();
    f.add(C.prepare(wa(), {}).folio);
    f.add(C.prepare(wa({ 'data-type': 'whatsapp_footer' }), {}).folio);
    f.add(C.prepare(anchor({ href: 'mailto:a@b.com', 'data-type': 'correo_footer' }), {}).folio);
    f.add(C.prepare(anchor({ href: 'mailto:c@d.com', 'data-type': 'correo_footer' }), {}).folio);
    assert.equal(f.size, 4);
  });

  test('tarjetas sin placement con texto distinto -> folios distintos', () => {
    const card = (t) => { const a = anchor({ href: WA_LINK }); a.textContent = t; return a; };
    const r1 = C.prepare(card('Producto A'), {});
    const r2 = C.prepare(card('Producto B'), {});
    assert.notEqual(r1.folio, r2.folio);
    assert.equal(r1.text, 'Producto A');
  });

  test('resetDedupe limpia la memoria de folios; newFolio sigue generando uno nuevo', () => {
    const a = wa();
    const r1 = C.prepare(a, {});
    C.resetDedupe();
    assert.notEqual(C.prepare(a, {}).folio, r1.folio);
    assert.notEqual(C.newFolio(), C.newFolio());
  });

  test('el folio reutilizado coincide con shouldDedupe/dedupeKey del flujo real', () => {
    const a = wa();
    const r1 = C.prepare(a, {});
    assert.equal(C.shouldDedupe(C.dedupeKey(r1), clock.now), false); // evento registrado
    clock.now += 300;
    const r2 = C.prepare(a, {});
    assert.equal(C.shouldDedupe(C.dedupeKey(r2), clock.now), true);  // descartado
    assert.equal(folioIn(a.href), r1.folio);                         // el href lleva el folio registrado
  });
});

describe('prepare: nombre de producto con caracteres especiales', () => {
  let C, clock;
  beforeEach(() => {
    const m = loadBrowserModule('mdn-contact.js');
    C = m.exports; clock = m.clock;
    C.configure(enabledCfg());
  });
  const folioIn = (href) => (/(MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5})/.exec(decodeURIComponent(href)) || [])[1];

  test('& # " \' salto de linea y emoji llegan codificados correctamente a la URL final', () => {
    const name = 'Valvula 1/2" & #5 O\'Neil\n\u{1F525} Pro';
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' });
    const r = C.prepare(a, { lang: 'es', product: { name, sku: 'X' } });
    const query = r.href.split('?text=')[1];
    assert.ok(!/[&#"\s]/.test(query), 'la parte codificada no debe llevar & # " espacios ni saltos crudos');
    assert.ok(query.includes('%F0%9F%94%A5'));
    assert.ok(query.includes('%26') && query.includes('%23') && query.includes('%22'));
    // Un solo parametro en la URL y texto ida-y-vuelta (el salto de linea se colapsa a espacio)
    assert.equal(r.href.split('?').length, 2);
    assert.equal(decodeURIComponent(query),
      'Hola, me interesa: Valvula 1/2" & #5 O\'Neil \u{1F525} Pro. Folio: ' + r.folio);
    assert.equal(folioIn(r.href), r.folio);
    assert.equal(a.href, r.href);
    assert.equal(a.dataset.mdnFolio, r.folio);
  });

  test('portugues con acentos: el folio de la URL == folio devuelto', () => {
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_br_hero' });
    const r = C.prepare(a, { lang: 'pt', product: { name: 'Ma\u00e7arico & Cia', sku: '1' } });
    assert.equal(folioIn(r.href), r.folio);
    assert.equal(decodeURIComponent(r.href.split('text=')[1]), 'Ol\u00e1, tenho interesse em: Ma\u00e7arico & Cia. C\u00f3digo: ' + r.folio);
  });

  test('wa.me con text= existente y mailto: folio de la URL == folio devuelto', () => {
    const w = anchor({ href: 'https://wa.me/' + NUMBER + '?text=' + encodeURIComponent('Hola & adios #1'), 'data-type': 'whatsapp_x' });
    const rw = C.prepare(w, { lang: 'es' });
    assert.equal(folioIn(rw.href), rw.folio);
    clock.now += 2000;
    const m = anchor({ href: 'mailto:a@b.com?body=x', 'data-type': 'correo_x' });
    const rm = C.prepare(m, { lang: 'pt' });
    assert.equal(folioIn(rm.href), rm.folio);
  });

  test('/br/ con <html lang> vacio -> portugues y folio coherente', () => {
    const M = loadBrowserModule('mdn-contact.js', { pathname: '/br/calibracao', lang: '' });
    M.exports.configure(enabledCfg());
    const r = M.exports.prepare(anchor({ href: WA_LINK, 'data-type': 'whatsapp_br_hero' }));
    const text = decodeURIComponent(r.href.split('text=')[1]);
    assert.equal(text, 'Ol\u00e1, quero mais informa\u00e7\u00f5es. C\u00f3digo: ' + r.folio);
    assert.equal(folioIn(r.href), r.folio);
  });
});

describe('robustez: nunca lanza', () => {
  const C = fresh();
  C.configure(enabledCfg());

  const exploding = {
    getAttribute() { throw new Error('boom'); },
    setAttribute() { throw new Error('boom'); },
    get href() { throw new Error('boom'); },
    set href(v) { throw new Error('boom'); },
    get dataset() { throw new Error('boom'); },
    closest() { throw new Error('boom'); }
  };

  test('anchors rotos', () => {
    for (const bad of [null, undefined, {}, 42, 'str', exploding, { getAttribute: () => null }, { href: 5 }]) {
      assert.doesNotThrow(() => C.classify(bad));
      assert.doesNotThrow(() => C.prepare(bad, {}));
      assert.doesNotThrow(() => C.prepare(bad));
      assert.doesNotThrow(() => C.placementOf(bad));
    }
    assert.equal(C.prepare(null, {}), null);
  });

  test('si escribir el href falla, devuelve la clasificacion, deja el original y no escribe orig-href', () => {
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' });
    const realSet = a.setAttribute.bind(a);
    a.setAttribute = (n, v) => { if (n === 'href') throw new Error('ro'); realSet(n, v); };
    let r;
    assert.doesNotThrow(() => { r = C.prepare(a, {}); });
    assert.equal(r.kind, 'whatsapp');
    assert.equal(a.getAttribute('href'), WA_LINK);
    assert.equal(a.getAttribute('data-mdn-orig-href'), null);
  });

  test('<a> de SVG (propiedad href de solo lectura): se reescribe con setAttribute sin lanzar', () => {
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' });
    Object.defineProperty(a, 'href', { get: () => ({ baseVal: WA_LINK }), set() { throw new TypeError('ro'); }, configurable: true });
    let r;
    assert.doesNotThrow(() => { r = C.prepare(a, {}); });
    assert.match(r.folio, FOLIO_RE);
    assert.equal(a.getAttribute('href'), r.href);
    assert.equal(a.getAttribute('data-mdn-orig-href'), WA_LINK);
  });

  test('si falla escribir data-mdn-orig-href, revierte el href', () => {
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' });
    Object.defineProperty(a, 'dataset', { get() { throw new Error('x'); }, configurable: true });
    const realSet = a.setAttribute.bind(a);
    a.setAttribute = (n, v) => { if (n === 'data-mdn-orig-href') throw new Error('x'); realSet(n, v); };
    const r = C.prepare(a, {});
    assert.equal(r.folio, undefined);
    assert.equal(a.getAttribute('href'), WA_LINK);
  });

  test('ctx rotos', () => {
    const a = anchor({ href: WA_LINK, 'data-type': 'whatsapp_flotante' });
    for (const ctx of [null, undefined, 5, 'x', { product: 5 }, { product: null }, { doc: {} }, { lang: 7 }]) {
      assert.doesNotThrow(() => C.prepare(a, ctx));
    }
    assert.doesNotThrow(() => C.shouldDedupe(undefined, 'x'));
    assert.doesNotThrow(() => C.dedupeKey(null));
    assert.doesNotThrow(() => C.encodeInvisible(null));
    assert.doesNotThrow(() => C.configure({ placements: { a: 1 }, whatsapp: 5, templates: 'x', mail: null }));
  });

  test('window.MDNContact expone la API', () => {
    const m = loadBrowserModule('mdn-contact.js');
    assert.equal(typeof m.window.MDNContact.prepare, 'function');
    assert.equal(m.window.MDNContact, m.exports);
    for (const k of ['configure', 'newFolio', 'isValidFolio', 'canonicalType', 'placementOf', 'classify', 'prepare',
                     'productContext', 'shouldDedupe', 'dedupeKey', 'encodeInvisible', 'decodeInvisible']) {
      assert.equal(typeof m.exports[k], 'function', k);
    }
  });
});

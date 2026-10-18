'use strict';
const { test, describe } = require('node:test');
const assert = require('node:assert/strict');
const { loadBrowserModule, plain } = require('./helpers/load.cjs');

const DAY = 24 * 60 * 60 * 1000;
const NOW = Date.UTC(2026, 6, 1, 12, 0, 0);

// Click ids con la misma forma que los reales de Google (longitudes 55 / 91 / 92).
const FILL = 'a1B2c3D4e5F6g7H8i9J0k1L2m3N4o5P6q7R8s9T0u1V2w3X4y5Z6_-a1B2c3D4e5F6g7H8i9J0k1L2m3N4o5P6q7R8s9T0u1V2w3X4y5Z6_-';
const mk = (prefix, len) => (prefix + FILL.repeat(3)).slice(0, len);
const GCLID_55 = mk('EAIaIQobChMI', 55);
const GCLID_91 = mk('Cj0KCQ', 91);
const GCLID_92 = mk('CjwKCA', 92);
const GBRAID = 'ABCDEFGHIJ_klmnop-123';
const WBRAID = 'wbraid_abcdefghij12';
const FBCLID = 'IwAR0abcdefghijklmnopqrstuvwxyz';

function load(opts) {
  const m = loadBrowserModule('attribution.js', Object.assign({ now: NOW }, opts));
  return m;
}

describe('isValidClickId', () => {
  const { exports: A } = load();

  test('las muestras tienen la longitud esperada', () => {
    assert.equal(GCLID_55.length, 55);
    assert.equal(GCLID_91.length, 91);
    assert.equal(GCLID_92.length, 92);
  });

  test('acepta gclid reales (55 / 91 / 92 caracteres)', () => {
    for (const g of [GCLID_55, GCLID_91, GCLID_92]) assert.equal(A.isValidClickId(g, 'gclid'), true, g);
  });

  test('acepta gclid de 20 y 255, rechaza 19 y 256', () => {
    assert.equal(A.isValidClickId('a'.repeat(20), 'gclid'), true);
    assert.equal(A.isValidClickId('a'.repeat(255), 'gclid'), true);
    assert.equal(A.isValidClickId('a'.repeat(19), 'gclid'), false);
    assert.equal(A.isValidClickId('a'.repeat(256), 'gclid'), false);
  });

  test('rechaza basura conocida', () => {
    assert.equal(A.isValidClickId('TEST123', 'gclid'), false);
    assert.equal(A.isValidClickId('gtm_5yabcdefghijklmnopqrstu', 'gclid'), false);
    assert.equal(A.isValidClickId('GTM_5yabcdefghijklmnopqrstu', 'gclid'), false);
    assert.equal(A.isValidClickId('Gtm-abcdefghijklmnopqrstuv', 'gclid'), false);
    assert.equal(A.isValidClickId('81172123456789012345', 'gclid'), false); // solo digitos
    assert.equal(A.isValidClickId('811721', 'gclid'), false);
    assert.equal(A.isValidClickId('', 'gclid'), false);
    assert.equal(A.isValidClickId(' ' + GCLID_55, 'gclid'), false);
    assert.equal(A.isValidClickId(GCLID_55 + ' ', 'gclid'), false);
    assert.equal(A.isValidClickId('abcdefghij klmnopqrstuvwxyz', 'gclid'), false);
    assert.equal(A.isValidClickId('abcdefghijklmnopqrst!', 'gclid'), false);
    assert.equal(A.isValidClickId(null, 'gclid'), false);
    assert.equal(A.isValidClickId(undefined, 'gclid'), false);
    assert.equal(A.isValidClickId(12345678901234567890, 'gclid'), false);
  });

  test('un gclid de 20+ con letras que empieza distinto de gtm es valido (digitos mezclados)', () => {
    assert.equal(A.isValidClickId('1234567890123456789a', 'gclid'), true);
    assert.equal(A.isValidClickId('gt' + 'x'.repeat(20), 'gclid'), true);
  });

  test('gbraid / wbraid / fbclid: minimo 10', () => {
    for (const kind of ['gbraid', 'wbraid', 'fbclid']) {
      assert.equal(A.isValidClickId('a'.repeat(10), kind), true, kind);
      assert.equal(A.isValidClickId('a'.repeat(9), kind), false, kind);
      assert.equal(A.isValidClickId('a'.repeat(255), kind), true, kind);
      assert.equal(A.isValidClickId('a'.repeat(256), kind), false, kind);
      assert.equal(A.isValidClickId('abc def ghij', kind), false, kind);
      assert.equal(A.isValidClickId('', kind), false, kind);
    }
    // gbraid no aplica la regla gtm ni de solo digitos
    assert.equal(A.isValidClickId('1234567890', 'gbraid'), true);
    assert.equal(A.isValidClickId('gtm1234567', 'wbraid'), true);
  });

  test('kind desconocido -> false', () => {
    assert.equal(A.isValidClickId(GCLID_55, 'otro'), false);
    assert.equal(A.isValidClickId(GCLID_55), false);
  });

  test('window.MDNAttribution expone la API publica', () => {
    const { window: w } = load();
    assert.deepEqual(Object.keys(w.MDNAttribution).sort(),
      ['capture', 'eventAt', 'get', 'getMeta', 'newEventId'].sort());
  });
});

describe('parseParams con validacion de click ids', () => {
  const { exports: A } = load();

  test('descarta un gclid invalido sin romper el resto', () => {
    const out = A.parseParams('?gclid=TEST123&utm_source=google&utm_medium=cpc&fbclid=' + FBCLID);
    assert.equal(out.gclid, undefined);
    assert.equal(out.utm_source, 'google');
    assert.equal(out.utm_medium, 'cpc');
    assert.equal(out.fbclid, FBCLID);
  });

  test('acepta click ids validos', () => {
    const out = A.parseParams('?gclid=' + GCLID_55 + '&gbraid=' + GBRAID + '&wbraid=' + WBRAID);
    assert.equal(out.gclid, GCLID_55);
    assert.equal(out.gbraid, GBRAID);
    assert.equal(out.wbraid, WBRAID);
  });

  test('gtm_ y numericos se descartan', () => {
    assert.deepEqual(plain(A.parseParams('?gclid=gtm_5yabcdefghijklmnopqrstu')), {});
    assert.deepEqual(plain(A.parseParams('?gclid=81172112345678901234')), {});
  });

  test('un gclid invalido no consume el lugar de uno valido posterior', () => {
    assert.equal(A.parseParams('?gclid=bad&gclid=' + GCLID_55).gclid, GCLID_55);
  });
});

describe('mergeAttribution: semilla legacy y cts', () => {
  const { exports: A } = load();

  test('siembra gclid valido desde claves legacy y lo marca en cts', () => {
    const s = A.mergeAttribution(null, {}, {
      now: NOW, pathname: '/', uuid: 'u1',
      legacy: { gclid: GCLID_55, utm_source: 'google', utm_medium: 'cpc', utm_campaign: 'x' }
    });
    assert.equal(s.last.gclid, GCLID_55);
    assert.equal(s.last.utm_source, 'google');
    assert.equal(s.last.cts.gclid, NOW);
  });

  test('descarta gclid legacy invalido (gtm_) pero conserva el resto', () => {
    const s = A.mergeAttribution(null, {}, {
      now: NOW, pathname: '/', uuid: 'u1',
      legacy: { gclid: 'gtm_5yabcdefghijklmnopqrstu', utm_source: 'google', utm_medium: 'cpc' }
    });
    assert.equal(s.last.gclid, '');
    assert.equal(s.last.utm_source, 'google');
    assert.equal(s.last.utm_medium, 'cpc');
    assert.equal(s.last.cts.gclid, 0);
  });

  test('registra cts por click id y utm_ts', () => {
    const s = A.mergeAttribution(null, A.parseParams('?gclid=' + GCLID_55 + '&utm_source=google'), { now: NOW, uuid: 'u' });
    assert.equal(s.last.cts.gclid, NOW);
    assert.equal(s.last.cts.fbclid, 0);
    assert.equal(s.last.utm_ts, NOW);
  });

  test('un click id de Google nuevo reemplaza al anterior y limpia su cts', () => {
    const s1 = A.mergeAttribution(null, { gclid: GCLID_55 }, { now: NOW, uuid: 'u' });
    const s2 = A.mergeAttribution(s1, { gbraid: GBRAID }, { now: NOW + 1000, uuid: 'u' });
    assert.equal(s2.last.gclid, '');
    assert.equal(s2.last.cts.gclid, 0);
    assert.equal(s2.last.gbraid, GBRAID);
    assert.equal(s2.last.cts.gbraid, NOW + 1000);
  });

  test('captured_at no cambia de significado (last.ts || first.ts)', () => {
    const s1 = A.mergeAttribution(null, { gclid: GCLID_55 }, { now: NOW, uuid: 'u' });
    const s2 = A.mergeAttribution(s1, {}, { now: NOW + 5 * 60 * 1000, uuid: 'u' }); // sin params
    assert.equal(A.flatten(s2).captured_at, new Date(NOW).toISOString());
  });
});

describe('estado v1 (sin cts) sigue siendo valido', () => {
  const v1 = () => ({
    v: 1,
    first: { landing_page: '/', referrer: '', utm_source: '', utm_medium: '', utm_campaign: '', utm_term: '', utm_content: '', ts: NOW - DAY },
    last: { gclid: GCLID_55, gbraid: '', wbraid: '', fbclid: '', utm_source: 'google', utm_medium: 'cpc',
            utm_campaign: 'c', utm_term: '', utm_content: '', ts: NOW - DAY },
    sid: 'sid-v1',
    sid_ts: NOW - 60 * 1000,
    expires: NOW + 80 * DAY
  });

  test('flatten, merge y get funcionan; cts se completa en 0', () => {
    const { exports: A, window: w } = load({ localStorage: { mdn_attr: JSON.stringify(v1()) } });
    const f = A.flatten(v1());
    assert.equal(f.gclid, GCLID_55);
    assert.equal(f.session_id, 'sid-v1');
    assert.equal(f.captured_at, new Date(NOW - DAY).toISOString());

    const merged = A.mergeAttribution(v1(), {}, { now: NOW, uuid: 'zz' });
    assert.equal(merged.sid, 'sid-v1');
    assert.equal(merged.last.cts.gclid, 0);
    assert.equal(merged.last.utm_ts, 0);

    assert.equal(w.MDNAttribution.get().gclid, GCLID_55);
    assert.equal(w.MDNAttribution.get().session_id, 'sid-v1');
  });

  test('getMeta sobre v1: canal google, sin click_id_at ni conflicto', () => {
    const { window: w } = load({ localStorage: { mdn_attr: JSON.stringify(v1()) } });
    const m = plain(w.MDNAttribution.getMeta());
    assert.equal(m.click_channel, 'google');
    assert.equal(m.click_id_at, '');
    assert.equal(m.touch_conflict, '0');
    assert.equal(m.attribution_at, new Date(NOW - DAY).toISOString());
  });

  test('un v1 con utm no-google posterior (sin cts) y luego un utm nuevo marca conflicto', () => {
    const { exports: A } = load();
    const s = A.mergeAttribution(v1(), { utm_source: 'newsletter' }, { now: NOW, uuid: 'u' });
    const m = A.computeMeta(s, NOW);
    assert.equal(m.touch_conflict, '1');
  });
});

describe('get(): session_id memoizado, expiracion, nunca lanza', () => {
  test('sin estado: mismo session_id en llamadas repetidas', () => {
    const { window: w } = load();
    const a = w.MDNAttribution.get();
    const b = w.MDNAttribution.get();
    assert.ok(a.session_id.length > 10);
    assert.equal(a.session_id, b.session_id);
    assert.equal(a.gclid, '');
  });

  test('capture() reutiliza el session_id ya generado por get()', () => {
    const { window: w } = load();
    const first = w.MDNAttribution.get().session_id;
    const cap = w.MDNAttribution.capture();
    assert.equal(cap.session_id, first);
    assert.equal(w.MDNAttribution.get().session_id, first);
  });

  test('capture() guarda estado y get() lo devuelve', () => {
    const { window: w } = load({ search: '?gclid=' + GCLID_55 + '&utm_source=google&utm_medium=cpc', pathname: '/producto/x' });
    const cap = w.MDNAttribution.capture();
    assert.equal(cap.gclid, GCLID_55);
    assert.equal(cap.landing_page, '/producto/x');
    assert.deepEqual(plain(w.MDNAttribution.get()), plain(cap));
  });

  test('capture() con gclid invalido en URL no lo guarda ni escribe clave legacy', () => {
    const { window: w, localStorage: ls } = load({ search: '?gclid=TEST123&utm_source=google' });
    const cap = w.MDNAttribution.capture();
    assert.equal(cap.gclid, '');
    assert.equal(cap.utm_source, 'google');
    assert.equal(ls.getItem('gclid'), null);
    assert.equal(ls.getItem('utm_source'), 'google');
  });

  test('capture() siembra desde localStorage legacy valido e ignora gtm_', () => {
    const a = load({ localStorage: { gclid: GCLID_55, utm_source: 'google' } });
    assert.equal(a.window.MDNAttribution.capture().gclid, GCLID_55);
    const b = load({ localStorage: { gclid: 'gtm_5yabcdefghijklmnopqrstu', utm_source: 'google' } });
    const cap = b.window.MDNAttribution.capture();
    assert.equal(cap.gclid, '');
    assert.equal(cap.utm_source, 'google');
  });

  test('estado vencido: devuelve vacio sin click ids y con session_id memoizado', () => {
    const expired = {
      v: 1,
      first: { landing_page: '/', referrer: '', utm_source: '', utm_medium: '', utm_campaign: '', utm_term: '', utm_content: '', ts: NOW - 100 * DAY },
      last: { gclid: GCLID_55, gbraid: '', wbraid: '', fbclid: '', utm_source: 'google', utm_medium: '', utm_campaign: '',
              utm_term: '', utm_content: '', ts: NOW - 100 * DAY },
      sid: 'old-sid', sid_ts: NOW - 100 * DAY, expires: NOW - DAY
    };
    const { window: w } = load({ localStorage: { mdn_attr: JSON.stringify(expired) } });
    const g = w.MDNAttribution.get();
    assert.equal(g.gclid, '');
    assert.equal(g.utm_source, '');
    assert.notEqual(g.session_id, 'old-sid');
    assert.ok(g.session_id.length > 10);
    assert.equal(w.MDNAttribution.get().session_id, g.session_id);
    const m = plain(w.MDNAttribution.getMeta());
    assert.deepEqual(m, { click_id_at: '', click_channel: 'none', touch_conflict: '0', attribution_at: '' });
  });

  test('nunca lanza aunque el almacenamiento reviente', () => {
    const { window: w } = load();
    Object.defineProperty(w.document, 'cookie', { get() { throw new Error('boom'); }, configurable: true });
    w.localStorage = { getItem() { throw new Error('boom'); }, setItem() { throw new Error('boom'); } };
    let g;
    assert.doesNotThrow(() => { g = w.MDNAttribution.get(); });
    assert.equal(g.gclid, '');
    assert.ok(g.session_id);
    assert.doesNotThrow(() => w.MDNAttribution.getMeta());
    assert.doesNotThrow(() => w.MDNAttribution.capture());
  });
});

describe('getMeta / computeMeta', () => {
  const { exports: A } = load();
  const T0 = NOW - 10 * DAY;

  function stateWith(last) {
    const s = A.mergeAttribution(null, {}, { now: T0, uuid: 'u' });
    Object.assign(s.last, last.fields || {});
    Object.assign(s.last.cts, last.cts || {});
    if (last.utm_ts !== undefined) s.last.utm_ts = last.utm_ts;
    s.last.ts = last.ts || T0;
    return s;
  }

  test('sin click ids: none', () => {
    const m = A.computeMeta(A.mergeAttribution(null, {}, { now: NOW, uuid: 'u' }), NOW);
    assert.equal(m.click_channel, 'none');
    assert.equal(m.click_id_at, '');
    assert.equal(m.touch_conflict, '0');
  });

  test('gclid: google, click_id_at ISO con Z', () => {
    const s = A.mergeAttribution(null, { gclid: GCLID_55 }, { now: NOW, uuid: 'u' });
    const m = A.computeMeta(s, NOW);
    assert.equal(m.click_channel, 'google');
    assert.equal(m.click_id_at, '2026-07-01T12:00:00.000Z');
    assert.match(m.click_id_at, /Z$/);
    assert.equal(m.attribution_at, '2026-07-01T12:00:00.000Z');
    assert.equal(m.touch_conflict, '0');
  });

  test('gbraid y wbraid cuentan como google', () => {
    for (const p of [{ gbraid: GBRAID }, { wbraid: WBRAID }]) {
      const m = A.computeMeta(A.mergeAttribution(null, p, { now: NOW, uuid: 'u' }), NOW);
      assert.equal(m.click_channel, 'google');
    }
  });

  test('solo fbclid: meta', () => {
    const m = A.computeMeta(A.mergeAttribution(null, { fbclid: FBCLID }, { now: NOW, uuid: 'u' }), NOW);
    assert.equal(m.click_channel, 'meta');
    assert.equal(m.touch_conflict, '0'); // no hay click id de Google
  });

  test('fbclid mas reciente que gclid: canal meta y conflicto', () => {
    const s = stateWith({ fields: { gclid: GCLID_55, fbclid: FBCLID }, cts: { gclid: T0, fbclid: T0 + 1000 } });
    const m = A.computeMeta(s, NOW);
    assert.equal(m.click_channel, 'meta');
    assert.equal(m.touch_conflict, '1');
    assert.equal(m.click_id_at, new Date(T0 + 1000).toISOString());
  });

  test('fbclid mas antiguo que gclid: google, sin conflicto', () => {
    const s = stateWith({ fields: { gclid: GCLID_55, fbclid: FBCLID }, cts: { gclid: T0 + 1000, fbclid: T0 } });
    const m = A.computeMeta(s, NOW);
    assert.equal(m.click_channel, 'google');
    assert.equal(m.touch_conflict, '0');
  });

  test('mismo instante (gclid + fbclid en la misma URL): sin conflicto', () => {
    const s = A.mergeAttribution(null, { gclid: GCLID_55, fbclid: FBCLID }, { now: NOW, uuid: 'u' });
    assert.equal(A.computeMeta(s, NOW).touch_conflict, '0');
  });

  test('utm_source no-google posterior al gclid: conflicto', () => {
    const s1 = A.mergeAttribution(null, { gclid: GCLID_55, utm_source: 'google' }, { now: T0, uuid: 'u' });
    const s2 = A.mergeAttribution(s1, { utm_source: 'newsletter' }, { now: T0 + DAY, uuid: 'u' });
    const m = A.computeMeta(s2, NOW);
    assert.equal(m.touch_conflict, '1');
    assert.equal(m.click_channel, 'google');
  });

  test('utm_source google posterior (cualquier mayuscula): sin conflicto', () => {
    const s1 = A.mergeAttribution(null, { gclid: GCLID_55 }, { now: T0, uuid: 'u' });
    const s2 = A.mergeAttribution(s1, { utm_source: 'Google-Ads' }, { now: T0 + DAY, uuid: 'u' });
    assert.equal(A.computeMeta(s2, NOW).touch_conflict, '0');
  });

  test('utm_source no-google ANTERIOR al gclid: sin conflicto', () => {
    const s1 = A.mergeAttribution(null, { utm_source: 'newsletter' }, { now: T0, uuid: 'u' });
    const s2 = A.mergeAttribution(s1, { gclid: GCLID_55 }, { now: T0 + DAY, uuid: 'u' });
    assert.equal(A.computeMeta(s2, NOW).touch_conflict, '0');
  });

  test('utm_source vacio no cuenta como no-google', () => {
    assert.equal(A.isNonGoogleSource(''), false);
    assert.equal(A.isNonGoogleSource('  '), false);
    assert.equal(A.isNonGoogleSource('GOOGLE'), false);
    assert.equal(A.isNonGoogleSource('facebook'), true);
  });

  test('un click id guardado con formato invalido se ignora', () => {
    const s = stateWith({ fields: { gclid: 'TEST123' }, cts: { gclid: T0 } });
    const m = A.computeMeta(s, NOW);
    assert.equal(m.click_channel, 'none');
    assert.equal(m.click_id_at, '');
  });

  test('getMeta() lee el estado guardado y todo son strings', () => {
    const { window: w } = load({ search: '?gclid=' + GCLID_55 + '&utm_source=google' });
    w.MDNAttribution.capture();
    const m = plain(w.MDNAttribution.getMeta());
    assert.deepEqual(Object.keys(m).sort(), ['attribution_at', 'click_channel', 'click_id_at', 'touch_conflict']);
    for (const v of Object.values(m)) assert.equal(typeof v, 'string');
    assert.equal(m.click_channel, 'google');
    assert.equal(m.click_id_at, '2026-07-01T12:00:00.000Z');
    assert.equal(m.attribution_at, w.MDNAttribution.get().captured_at);
  });

  test('eventAt() es la hora actual en ISO UTC', () => {
    const { window: w, clock } = load();
    clock.now = Date.UTC(2026, 6, 2, 3, 4, 5, 6);
    assert.equal(w.MDNAttribution.eventAt(), '2026-07-02T03:04:05.006Z');
  });

  test('flujo completo: gclid, luego fbclid -> conflicto y canal meta', () => {
    const m1 = load({ search: '?gclid=' + GCLID_55 + '&utm_source=google' });
    m1.window.MDNAttribution.capture();
    // Segunda visita en el mismo navegador (mismo storage / cookie), 2 dias despues
    const m2 = load({ now: NOW + 2 * DAY, search: '?fbclid=' + FBCLID + '&utm_source=facebook',
                      cookie: Object.entries(m1.jar).map(([k, v]) => k + '=' + v).join('; ') });
    m2.window.MDNAttribution.capture();
    const meta = plain(m2.window.MDNAttribution.getMeta());
    assert.equal(meta.click_channel, 'meta');
    assert.equal(meta.touch_conflict, '1');
    assert.equal(m2.window.MDNAttribution.get().gclid, GCLID_55);
  });
});

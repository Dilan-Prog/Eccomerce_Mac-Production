'use strict';
/**
 * Pruebas de las funciones PURAS de public/admin-ui/js/attribution-lab/lab.js.
 * package.json tiene "type":"module": el archivo se ejecuta con `vm` (sin DOM), igual que los
 * modulos de public/frontend/js en tests/js/helpers/load.cjs.
 */
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { webcrypto } = require('node:crypto');

const ROOT = path.resolve(__dirname, '..', '..');
const LAB = path.join(ROOT, 'public', 'admin-ui', 'js', 'attribution-lab', 'lab.js');
const FIXTURE = path.join(ROOT, 'tests', 'fixtures', 'folio-extraction.json');

function loadLab() {
  const mod = { exports: {} };
  const sandbox = { module: mod, exports: mod.exports, console, crypto: webcrypto };
  vm.createContext(sandbox);
  vm.runInContext(fs.readFileSync(LAB, 'utf8'), sandbox, { filename: 'lab.js' });
  return mod.exports;
}

const lab = loadLab();

/** Generador determinista: bytes consecutivos a partir de `start`. */
function seqRand(start) {
  let n = start || 0;
  return (len) => {
    const b = new Uint8Array(len);
    for (let i = 0; i < len; i++) b[i] = n++ % 256;
    return b;
  };
}

/* ---------------------------------------------------------------- *
 * Casos compartidos con PHP (tests/fixtures/folio-extraction.json)
 * ---------------------------------------------------------------- */

test('casos compartidos folio-extraction.json', (t) => {
  if (!fs.existsSync(FIXTURE)) {
    t.skip('tests/fixtures/folio-extraction.json aun no existe');
    return;
  }
  const cases = JSON.parse(fs.readFileSync(FIXTURE, 'utf8'));
  assert.ok(Array.isArray(cases) && cases.length > 0, 'el fixture debe ser una lista no vacia');
  for (const c of cases) {
    assert.equal(lab.extractFolioExact(c.text), c.exact_n8n == null ? null : c.exact_n8n, 'exact_n8n: ' + c.name);
    // El folio invisible (bits en U+200B/U+200C) no lo decodifica lab.js: solo se compara lo visible.
    if (c.source !== 'invisible') {
      assert.equal(lab.extractFolio(c.text), c.tolerant == null ? null : c.tolerant, 'tolerant: ' + c.name);
    }
  }
});

/* ---------------------------------------------------------------- *
 * extractFolio / extractFolioExact
 * ---------------------------------------------------------------- */

test('extractFolio: formato basico, guion, espacio y minusculas', () => {
  assert.equal(lab.extractFolio('Hola, me interesa. Folio: MN7K4Q2'), 'MN7K4Q2');
  assert.equal(lab.extractFolio('folio MN-7K4Q2 gracias'), 'MN7K4Q2');
  assert.equal(lab.extractFolio('folio MN 7K4Q2'), 'MN7K4Q2');
  assert.equal(lab.extractFolio('folio mn7k4q2'), 'MN7K4Q2');
  assert.equal(lab.extractFolio('mn-7k4q2'), 'MN7K4Q2');
});

test('extractFolio: ignora invisibles y trata NBSP como espacio', () => {
  assert.equal(lab.extractFolio('Folio: MN⁠7K4Q2'), 'MN7K4Q2');
  assert.equal(lab.extractFolio('M​N7K‌4Q‍2﻿'), 'MN7K4Q2');
  assert.equal(lab.extractFolio('⁠MN7K4Q2⁠'), 'MN7K4Q2');
  assert.equal(lab.extractFolio('MN 7K4Q2'), 'MN7K4Q2');
});

test('extractFolio: pegado a letras o digitos no cuenta', () => {
  assert.equal(lab.extractFolio('HOLAMN7K4Q2'), null);
  assert.equal(lab.extractFolio('MN7K4Q2X'), null);
  assert.equal(lab.extractFolio('MN7K4Q23'), null);
  assert.equal(lab.extractFolio('1MN7K4Q2'), null);
});

test('extractFolio: alfabeto invalido, texto sin folio y "$23456 MN"', () => {
  assert.equal(lab.extractFolio('MNAEIOU'), null);   // vocales fuera del alfabeto
  assert.equal(lab.extractFolio('MN7K4L2'), null);   // L no pertenece
  assert.equal(lab.extractFolio('MN1K4Q2'), null);   // 1 no pertenece
  assert.equal(lab.extractFolio('$23456 MN'), null); // MN va despues, no hay 5 caracteres detras
  assert.equal(lab.extractFolio(''), null);
  assert.equal(lab.extractFolio(null), null);
  assert.equal(lab.extractFolio(undefined), null);
});

test('extractFolio: con dos folios gana el ultimo', () => {
  assert.equal(lab.extractFolio('antes MN7K4Q2 y luego MN3CDFG'), 'MN3CDFG');
});

test('extractFolioExact: regex literal de n8n sobre texto crudo', () => {
  assert.equal(lab.extractFolioExact('Folio: MN7K4Q2'), 'MN7K4Q2');
  assert.equal(lab.extractFolioExact('Folio: MN7K4Q2.'), 'MN7K4Q2');
  // Lo que la regex de n8n NO ve y la tolerante si:
  assert.equal(lab.extractFolioExact('MN-7K4Q2'), null);
  assert.equal(lab.extractFolioExact('MN 7K4Q2'), null);
  assert.equal(lab.extractFolioExact('mn7k4q2'), null);
  assert.equal(lab.extractFolioExact('MN⁠7K4Q2'), null);
  assert.equal(lab.extractFolioExact('HOLAMN7K4Q2'), null);
  assert.equal(lab.extractFolioExact(''), null);
  assert.equal(lab.extractFolioExact(null), null);
  // Con dos folios, la regex de n8n (match) toma el primero; la tolerante, el ultimo.
  assert.equal(lab.extractFolioExact('antes MN7K4Q2 y luego MN3CDFG'), 'MN7K4Q2');
});

/* ---------------------------------------------------------------- *
 * buildCurl
 * ---------------------------------------------------------------- */

test('buildCurl: usa el literal $TOKEN y url-encodea los parametros', () => {
  const c = lab.buildCurl({ apiUrl: 'https://macdelnorte.com/api/attribution/lookup', params: { folio: 'MN7K4Q2' } });
  assert.equal(c, 'curl -s -H "Authorization: Bearer $TOKEN" "https://macdelnorte.com/api/attribution/lookup?folio=MN7K4Q2"');
  assert.ok(c.includes('$TOKEN'));
});

test('buildCurl: nunca incluye un token real', () => {
  const c = lab.buildCurl({ apiUrl: 'https://x.test/api/attribution/lookup', params: { folio: 'MN7K4Q2' } });
  const bearers = c.match(/Bearer\s+\S+/g) || [];
  assert.deepEqual(bearers, ['Bearer $TOKEN"']);
  assert.ok(!/Bearer\s+[A-Za-z0-9]{8,}/.test(c));
});

test('buildCurl: ventana con fechas ISO url-encoded (: y +)', () => {
  const c = lab.buildCurl({
    apiUrl: 'https://x.test/api/attribution/lookup',
    params: { type: 'phone_click', from: '2026-01-15T18:00:00Z', to: '2026-01-15T18:10:00+00:00' }
  });
  assert.ok(c.includes('type=phone_click'));
  assert.ok(c.includes('from=2026-01-15T18%3A00%3A00Z'));
  assert.ok(c.includes('to=2026-01-15T18%3A10%3A00%2B00%3A00'));
  assert.ok(!c.includes('&&'));
});

test('buildCurl: escapa comillas, $, backticks y signos que el shell interpreta', () => {
  const c = lab.buildCurl({ apiUrl: 'https://x.test/a"b$c`d', params: { folio: 'a"b$c`d\'!x (y)*' } });
  // El apiUrl se escapa con barra dentro de las comillas dobles...
  assert.ok(c.includes('a\\"b\\$c\\`d'));
  // ...y los parametros salen codificados: nada especial queda crudo despues del "?".
  const query = c.slice(c.indexOf('?') + 1, c.lastIndexOf('"'));
  assert.ok(!/["$`'!() *]/.test(query), 'query con caracteres peligrosos: ' + query);
  assert.ok(query.includes('%22') && query.includes('%24') && query.includes('%60') && query.includes('%27'));
  assert.ok(c.startsWith('curl -s -H "Authorization: Bearer $TOKEN" "'));
  assert.ok(c.endsWith('"'));
});

test('buildCurl: omite parametros vacios y respeta un "?" previo', () => {
  assert.equal(
    lab.buildCurl({ apiUrl: 'https://x.test/l', params: { folio: 'MN7K4Q2', message_at: '', type: null } }),
    'curl -s -H "Authorization: Bearer $TOKEN" "https://x.test/l?folio=MN7K4Q2"'
  );
  assert.equal(
    lab.buildCurl({ apiUrl: 'https://x.test/l?v=1', params: { folio: 'MN7K4Q2' } }),
    'curl -s -H "Authorization: Bearer $TOKEN" "https://x.test/l?v=1&folio=MN7K4Q2"'
  );
  assert.equal(
    lab.buildCurl({ apiUrl: 'https://x.test/l', params: {} }),
    'curl -s -H "Authorization: Bearer $TOKEN" "https://x.test/l"'
  );
});

/* ---------------------------------------------------------------- *
 * buildAdUrl
 * ---------------------------------------------------------------- */

test('buildAdUrl: parametros en orden, url-encoded y sin vacios', () => {
  const url = lab.buildAdUrl({
    siteUrl: 'https://macdelnorte.com/',
    path: '/product-detail/mi-producto',
    gclid: 'TEST_GCLID_0123456789ABCDEF',
    gbraid: '',
    wbraid: undefined,
    utm: { source: 'google', medium: 'cpc', campaign: 'prueba lab&x', term: '', content: null }
  });
  assert.equal(
    url,
    'https://macdelnorte.com/product-detail/mi-producto?gclid=TEST_GCLID_0123456789ABCDEF&utm_source=google&utm_medium=cpc&utm_campaign=prueba%20lab%26x'
  );
});

test('buildAdUrl: inicio sin parametros, ruta sin barra inicial y gbraid/wbraid', () => {
  assert.equal(lab.buildAdUrl({ siteUrl: 'https://x.test', path: '/' }), 'https://x.test/');
  assert.equal(lab.buildAdUrl({ siteUrl: 'https://x.test///', path: 'categorias' }), 'https://x.test/categorias');
  const url = lab.buildAdUrl({ siteUrl: 'https://x.test', path: '/', gbraid: 'GBRAID_12345', wbraid: 'WBRAID_12345' });
  assert.equal(url, 'https://x.test/?gbraid=GBRAID_12345&wbraid=WBRAID_12345');
});

test('buildAdUrl: acepta claves utm_ con prefijo', () => {
  const url = lab.buildAdUrl({ siteUrl: 'https://x.test', path: '/', utm: { utm_source: 'google', utm_term: 'a b' } });
  assert.equal(url, 'https://x.test/?utm_source=google&utm_term=a%20b');
});

/* ---------------------------------------------------------------- *
 * randomTestGclid / validadores
 * ---------------------------------------------------------------- */

test('randomTestGclid: prefijo + 16 alfanumericos y cumple el gclid del servidor', () => {
  const g = lab.randomTestGclid(seqRand(0));
  assert.match(g, /^TEST_GCLID_[A-Za-z0-9]{16}$/);
  assert.match(g, /^[A-Za-z0-9_-]{20,255}$/);
  assert.equal(lab.isValidGclid(g), true);
});

test('randomTestGclid: con el generador real de crypto son distintos y validos', () => {
  const seen = new Set();
  for (let i = 0; i < 200; i++) {
    const g = lab.randomTestGclid();
    assert.match(g, /^TEST_GCLID_[A-Za-z0-9]{16}$/);
    seen.add(g);
  }
  assert.equal(seen.size, 200);
});

test('randomTestGclid: descarta bytes sesgados (>= 248) y falla si el generador no sirve', () => {
  // Primero bytes 255 (rechazados) y luego 0..: el resultado solo usa los aceptados.
  let calls = 0;
  const rand = (len) => {
    calls++;
    return new Uint8Array(len).fill(calls === 1 ? 255 : 0); // 0 -> 'A'
  };
  assert.equal(lab.randomTestGclid(rand), 'TEST_GCLID_' + 'A'.repeat(16));
  assert.equal(calls, 2);
  assert.throws(() => lab.randomTestGclid((len) => new Uint8Array(len).fill(255)), /generador/i);
});

test('isValidGclid', () => {
  assert.equal(lab.isValidGclid('TEST_GCLID_0123456789ABCDEF'), true);
  assert.equal(lab.isValidGclid('a'.repeat(20)), true);
  assert.equal(lab.isValidGclid('a'.repeat(255)), true);
  assert.equal(lab.isValidGclid('a'.repeat(19)), false);
  assert.equal(lab.isValidGclid('a'.repeat(256)), false);
  assert.equal(lab.isValidGclid('TEST_GCLID_con espacio_123'), false);
  assert.equal(lab.isValidGclid('TEST_GCLID_ñandú_123456789'), false);
  assert.equal(lab.isValidGclid('TEST_GCLID_&x=1_123456789'), false);
  assert.equal(lab.isValidGclid('gtm_' + 'a'.repeat(20)), false);
  assert.equal(lab.isValidGclid('1'.repeat(20)), false);
  assert.equal(lab.isValidGclid(''), false);
  assert.equal(lab.isValidGclid(null), false);
  assert.equal(lab.isValidGclid(undefined), false);
});

test('isValidBraid', () => {
  assert.equal(lab.isValidBraid('0123456789'), true);
  assert.equal(lab.isValidBraid('abc_def-ghi'), true);
  assert.equal(lab.isValidBraid('012345678'), false);
  assert.equal(lab.isValidBraid('a'.repeat(256)), false);
  assert.equal(lab.isValidBraid('abc def ghi'), false);
  assert.equal(lab.isValidBraid(''), false);
  assert.equal(lab.isValidBraid(undefined), false);
});

/* ---------------------------------------------------------------- *
 * Fechas
 * ---------------------------------------------------------------- */

test('formatMexico: YYYY-MM-DD HH:mm:ss en America/Mexico_City', () => {
  assert.equal(lab.formatMexico('2026-01-15T18:30:45Z'), '2026-01-15 12:30:45');
  assert.equal(lab.formatMexico('2026-07-15T18:30:45Z'), '2026-07-15 12:30:45'); // sin horario de verano desde 2022
  assert.equal(lab.formatMexico(new Date(Date.UTC(2026, 6, 1, 5, 59, 59))), '2026-06-30 23:59:59');
  assert.equal(lab.formatMexico('2026-01-01T06:00:00Z'), '2026-01-01 00:00:00'); // medianoche: nunca "24:00"
  assert.equal(lab.formatMexico('2026-01-01T05:59:59Z'), '2025-12-31 23:59:59');
});

test('formatMexico: entradas invalidas devuelven cadena vacia', () => {
  assert.equal(lab.formatMexico('no es fecha'), '');
  assert.equal(lab.formatMexico(''), '');
  assert.equal(lab.formatMexico(null), '');
  assert.equal(lab.formatMexico(undefined), '');
});

test('naiveLocalToUtcIso: Mexico en invierno y verano (UTC-6 todo el ano)', () => {
  assert.equal(lab.naiveLocalToUtcIso('2026-01-15T12:00', 'America/Mexico_City'), '2026-01-15T18:00:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-07-15T12:00', 'America/Mexico_City'), '2026-07-15T18:00:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-07-15T12:34:56', 'America/Mexico_City'), '2026-07-15T18:34:56Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-01-15T20:00', 'America/Mexico_City'), '2026-01-16T02:00:00Z'); // cruza medianoche UTC
  assert.equal(lab.naiveLocalToUtcIso('2026-01-15T12:00'), '2026-01-15T18:00:00Z');                       // zona por defecto
});

test('naiveLocalToUtcIso: Mexico con horario de verano historico (antes de 2022)', () => {
  assert.equal(lab.naiveLocalToUtcIso('2021-07-15T12:00', 'America/Mexico_City'), '2021-07-15T17:00:00Z'); // CDT, UTC-5
  assert.equal(lab.naiveLocalToUtcIso('2021-01-15T12:00', 'America/Mexico_City'), '2021-01-15T18:00:00Z'); // CST, UTC-6
});

test('naiveLocalToUtcIso: otras zonas, invierno y verano', () => {
  assert.equal(lab.naiveLocalToUtcIso('2026-01-15T12:00', 'America/New_York'), '2026-01-15T17:00:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-07-15T12:00', 'America/New_York'), '2026-07-15T16:00:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-01-15T12:00', 'Europe/Madrid'), '2026-01-15T11:00:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-07-15T12:00', 'Europe/Madrid'), '2026-07-15T10:00:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-07-15T12:00', 'Asia/Kolkata'), '2026-07-15T06:30:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-07-15T12:00', 'UTC'), '2026-07-15T12:00:00Z');
});

test('naiveLocalToUtcIso: dia del cambio de horario (America/New_York, 2026-03-08 y 2026-11-01)', () => {
  // Primavera: 02:00 -> 03:00. 01:30 es EST (UTC-5); 03:30 es EDT (UTC-4).
  assert.equal(lab.naiveLocalToUtcIso('2026-03-08T01:30', 'America/New_York'), '2026-03-08T06:30:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-03-08T03:30', 'America/New_York'), '2026-03-08T07:30:00Z');
  // 02:30 no existe: cae en la hora siguiente (03:30 EDT = 07:30Z).
  assert.equal(lab.naiveLocalToUtcIso('2026-03-08T02:30', 'America/New_York'), '2026-03-08T07:30:00Z');
  // Otono: 02:00 -> 01:00. 00:30 es EDT, 01:30 ocurre dos veces (se toma la primera, EDT), 02:30 es EST.
  assert.equal(lab.naiveLocalToUtcIso('2026-11-01T00:30', 'America/New_York'), '2026-11-01T04:30:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-11-01T01:30', 'America/New_York'), '2026-11-01T05:30:00Z');
  assert.equal(lab.naiveLocalToUtcIso('2026-11-01T02:30', 'America/New_York'), '2026-11-01T07:30:00Z');
});

test('naiveLocalToUtcIso: ida y vuelta con formatMexico', () => {
  for (const local of ['2026-01-15T00:00', '2026-06-30T23:59', '2026-12-31T23:59:59', '2027-02-28T08:05']) {
    const iso = lab.naiveLocalToUtcIso(local, 'America/Mexico_City');
    const back = lab.formatMexico(iso);
    const want = local.length === 16 ? local.replace('T', ' ') + ':00' : local.replace('T', ' ');
    assert.equal(back, want);
  }
});

test('naiveLocalToUtcIso: entradas invalidas devuelven null', () => {
  assert.equal(lab.naiveLocalToUtcIso('', 'UTC'), null);
  assert.equal(lab.naiveLocalToUtcIso(null, 'UTC'), null);
  assert.equal(lab.naiveLocalToUtcIso('2026-01-15', 'UTC'), null);
  assert.equal(lab.naiveLocalToUtcIso('2026-13-15T12:00', 'UTC'), null);
  assert.equal(lab.naiveLocalToUtcIso('2026-02-31T12:00', 'UTC'), null);
  assert.equal(lab.naiveLocalToUtcIso('2026-01-15T25:00', 'UTC'), null);
  assert.equal(lab.naiveLocalToUtcIso('2026-01-15T12:00', 'Zona/Inexistente'), null);
});

test('datetimeLocalFromDate: valor para datetime-local en la zona', () => {
  assert.equal(lab.datetimeLocalFromDate(new Date('2026-01-15T18:30:45Z'), 'America/Mexico_City'), '2026-01-15T12:30');
  assert.equal(lab.datetimeLocalFromDate(new Date('x'), 'America/Mexico_City'), '');
});

/* ---------------------------------------------------------------- *
 * buildLookupParams / badges
 * ---------------------------------------------------------------- */

const plain = (x) => JSON.parse(JSON.stringify(x));

test('buildLookupParams: folio en mayusculas, con message_at en UTC', () => {
  assert.deepEqual(plain(lab.buildLookupParams({ mode: 'folio', folio: '  mn7k4q2 ' })), { params: { folio: 'MN7K4Q2' } });
  assert.deepEqual(
    plain(lab.buildLookupParams({ mode: 'folio', folio: 'cot-123', message_at: '2026-01-15T12:00', tz: 'America/Mexico_City' })),
    { params: { folio: 'COT-123', message_at: '2026-01-15T18:00:00Z' } }
  );
  assert.ok(lab.buildLookupParams({ mode: 'folio', folio: '   ' }).error);
  assert.ok(lab.buildLookupParams({ mode: 'folio', folio: 'MN7K4Q2', message_at: 'basura' }).error);
});

test('buildLookupParams: ventana a ISO UTC y validaciones', () => {
  assert.deepEqual(
    plain(lab.buildLookupParams({ mode: 'window', type: 'phone_click', from: '2026-07-15T12:00', to: '2026-07-15T12:10', tz: 'America/Mexico_City' })),
    { params: { type: 'phone_click', from: '2026-07-15T18:00:00Z', to: '2026-07-15T18:10:00Z' } }
  );
  assert.ok(lab.buildLookupParams({ mode: 'window', type: '', from: '2026-07-15T12:00', to: '2026-07-15T12:10' }).error);
  assert.ok(lab.buildLookupParams({ mode: 'window', type: 'phone_click', from: '', to: '2026-07-15T12:10' }).error);
  assert.ok(lab.buildLookupParams({ mode: 'window', type: 'phone_click', from: '2026-07-15T12:10', to: '2026-07-15T12:00' }).error);
  assert.ok(lab.buildLookupParams({ mode: 'window', type: 'phone_click', from: '2026-07-15T12:00', to: '2026-07-15T12:00' }).error);
});

test('buildLookupParams + buildCurl: la ventana sale en ISO UTC con Z', () => {
  const built = lab.buildLookupParams({ mode: 'window', type: 'phone_click', from: '2026-01-15T12:00', to: '2026-01-15T12:10' });
  const c = lab.buildCurl({ apiUrl: 'https://x.test/api/attribution/lookup', params: built.params });
  assert.ok(c.includes('from=2026-01-15T18%3A00%3A00Z'));
  assert.ok(c.includes('to=2026-01-15T18%3A10%3A00Z'));
});

test('badgeForHttp y badgeForLevel', () => {
  assert.equal(lab.badgeForHttp(200), 'success');
  assert.equal(lab.badgeForHttp(404), 'warning');
  assert.equal(lab.badgeForHttp(422), 'warning');
  assert.equal(lab.badgeForHttp(503), 'critical');
  assert.equal(lab.badgeForHttp(0), 'critical');
  assert.equal(lab.badgeForLevel('error'), 'critical');
  assert.equal(lab.badgeForLevel('warning'), 'warning');
  assert.equal(lab.badgeForLevel('info'), 'info');
  assert.equal(lab.badgeForLevel(undefined), 'info');
});

/* ---------------------------------------------------------------- *
 * Ajustes de prueba (numero de WhatsApp y webhook de n8n)
 * ---------------------------------------------------------------- */

test('normalizePhoneDigits: quita espacios, +, guiones, parentesis y puntos', () => {
  assert.equal(lab.normalizePhoneDigits('+52 1 (55) 1234-5678'), '5215512345678');
  assert.equal(lab.normalizePhoneDigits('  55.1234.5678 '), '5512345678');
  assert.equal(lab.normalizePhoneDigits('52 1551234 5678'), '5215512345678'); // NBSP incluido
  assert.equal(lab.normalizePhoneDigits(''), '');
  assert.equal(lab.normalizePhoneDigits(null), '');
  assert.equal(lab.normalizePhoneDigits(undefined), '');
  assert.equal(lab.normalizePhoneDigits(5215512345678), '5215512345678');
  // Lo que no es separador se conserva para que la validacion lo rechace.
  assert.equal(lab.normalizePhoneDigits('55-abc'), '55abc');
});

test('validateSettings: numero vacio o de 10 a 15 digitos', () => {
  assert.equal(lab.validateSettings({ test_whatsapp_number: '', n8n_url: '' }).ok, true);
  assert.equal(lab.validateSettings({}).ok, true);
  const good = lab.validateSettings({ test_whatsapp_number: '+52 1 55 1234 5678' });
  assert.equal(good.ok, true);
  assert.equal(good.values.test_whatsapp_number, '5215512345678');
  assert.equal(lab.validateSettings({ test_whatsapp_number: '5512345678' }).ok, true);      // 10
  assert.equal(lab.validateSettings({ test_whatsapp_number: '123456789012345' }).ok, true); // 15
  for (const bad of ['551234567', '1234567890123456', '55123abc78', '5512345678x', 'abc']) {
    const r = lab.validateSettings({ test_whatsapp_number: bad });
    assert.equal(r.ok, false, bad);
    assert.ok(r.errors.test_whatsapp_number, bad);
    assert.equal(r.errors.n8n_url, undefined);
  }
});

test('validateSettings: URL de n8n vacia o https sin credenciales y de 500 caracteres o menos', () => {
  const ok = (url, opts) => lab.validateSettings({ n8n_url: url }, opts).ok;
  assert.equal(ok(''), true);
  assert.equal(ok('   '), true);
  assert.equal(ok('https://n8n.example.com/webhook/abc-123'), true);
  assert.equal(ok('https://n8n.example.com:5678/webhook/abc?x=1#y'), true);
  assert.equal(ok('HTTPS://N8N.EXAMPLE.COM/webhook'), true);
  assert.equal(ok('https://[2001:db8::1]:443/webhook'), true);
  // http solo con allowHttp
  assert.equal(ok('http://n8n.example.com/webhook'), false);
  assert.equal(ok('http://n8n.example.com/webhook', { allowHttp: false }), false);
  assert.equal(ok('http://n8n.example.com/webhook', { allowHttp: true }), true);
  // esquemas y formas invalidas
  assert.equal(ok('ftp://n8n.example.com/webhook', { allowHttp: true }), false);
  assert.equal(ok('javascript://x'), false);
  assert.equal(ok('n8n.example.com/webhook'), false);
  assert.equal(ok('https://'), false);
  assert.equal(ok('https:///webhook'), false);
  assert.equal(ok('https://:443/webhook'), false);
  assert.equal(ok('https://n8n.example.com/web hook'), false);
  // credenciales en la URL
  assert.equal(ok('https://user@n8n.example.com/webhook'), false);
  assert.equal(ok('https://user:pass@n8n.example.com/webhook'), false);
  // un @ en la ruta o el query no son credenciales
  assert.equal(ok('https://n8n.example.com/webhook/a@b'), true);
  // largo
  const base = 'https://n8n.example.com/';
  assert.equal(ok(base + 'a'.repeat(500 - base.length)), true);
  assert.equal(ok(base + 'a'.repeat(501 - base.length)), false);
});

test('validateSettings: recorta la URL y reporta los dos errores a la vez', () => {
  const okRes = lab.validateSettings({ test_whatsapp_number: '5512345678', n8n_url: '  https://n8n.example.com/webhook/x  ' });
  assert.deepEqual(plain(okRes.values), { test_whatsapp_number: '5512345678', n8n_url: 'https://n8n.example.com/webhook/x' });
  const both = lab.validateSettings({ test_whatsapp_number: '123', n8n_url: 'http://x.test/a' });
  assert.equal(both.ok, false);
  assert.deepEqual(Object.keys(both.errors).sort(), ['n8n_url', 'test_whatsapp_number']);
});

test('describeSource: textos de la fuente del numero de prueba', () => {
  assert.equal(lab.describeSource('db'), 'Guardado en el panel');
  assert.equal(lab.describeSource('env'), 'Tomado del .env');
  assert.equal(lab.describeSource('none'), 'Sin configurar');
  assert.equal(lab.describeSource(undefined), 'Sin configurar');
  assert.equal(lab.describeSource('otra'), 'Sin configurar');
});

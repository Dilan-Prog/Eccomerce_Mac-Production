'use strict';
/**
 * Carga los modulos del navegador (IIFE, UMD-ish) dentro de un contexto `vm`.
 *
 * package.json tiene "type":"module", asi que require('attribution.js') devolveria {}.
 * Aqui se ejecuta el archivo con un `module` simulado y un `window`/`document` minimos.
 * Sin dependencias (nada de jsdom).
 */
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { webcrypto } = require('node:crypto');

const ROOT = path.resolve(__dirname, '..', '..', '..');
const JS_DIR = path.join(ROOT, 'public', 'frontend', 'js');

/** Reloj controlable: Date() sin argumentos y Date.now() devuelven clock.now. */
function makeClock(start) {
  const clock = { now: typeof start === 'number' ? start : Date.UTC(2026, 6, 1, 12, 0, 0) };
  clock.FakeDate = class FakeDate extends Date {
    constructor(...args) {
      if (args.length === 0) super(clock.now); else super(...args);
    }
    static now() { return clock.now; }
  };
  return clock;
}

/* ---------------- DOM simulado minimo ---------------- */

function camelToKebab(s) { return s.replace(/[A-Z]/g, (m) => '-' + m.toLowerCase()); }

class FakeElement {
  constructor(tag, attrs, children) {
    this.tagName = String(tag || 'div').toUpperCase();
    this._attrs = Object.create(null);
    this.parentNode = null;
    this.children = [];
    this.textContent = '';
    this.classList = {
      _el: this,
      contains: (c) => (this._attrs.class || '').split(/\s+/).includes(c),
      add: (c) => { if (!this.classList.contains(c)) this._attrs.class = ((this._attrs.class || '') + ' ' + c).trim(); },
      remove: (c) => { this._attrs.class = (this._attrs.class || '').split(/\s+/).filter((x) => x && x !== c).join(' '); }
    };
    const self = this;
    this.dataset = new Proxy({}, {
      get(_t, prop) {
        if (typeof prop !== 'string') return undefined;
        const v = self._attrs['data-' + camelToKebab(prop)];
        return v === undefined ? undefined : v;
      },
      set(_t, prop, value) {
        self._attrs['data-' + camelToKebab(String(prop))] = String(value);
        return true;
      },
      has(_t, prop) { return ('data-' + camelToKebab(String(prop))) in self._attrs; },
      deleteProperty(_t, prop) { delete self._attrs['data-' + camelToKebab(String(prop))]; return true; }
    });
    for (const [k, v] of Object.entries(attrs || {})) this._attrs[k] = String(v);
    (children || []).forEach((c) => this.appendChild(c));
  }
  getAttribute(n) { return n in this._attrs ? this._attrs[n] : null; }
  setAttribute(n, v) { this._attrs[n] = String(v); }
  removeAttribute(n) { delete this._attrs[n]; }
  hasAttribute(n) { return n in this._attrs; }
  get href() { return this._attrs.href === undefined ? '' : this._attrs.href; }
  set href(v) { this._attrs.href = String(v); }
  get lang() { return this._attrs.lang || ''; }
  appendChild(c) { c.parentNode = this; this.children.push(c); return c; }
  closest(sel) {
    // Solo soporta selectores de atributo simples: [data-foo]
    const m = /^\[([a-z0-9-]+)\]$/i.exec(sel);
    if (!m) throw new Error('selector no soportado: ' + sel);
    let n = this;
    while (n) {
      if (n._attrs && m[1] in n._attrs) return n;
      n = n.parentNode;
    }
    return null;
  }
  _all(out) { out.push(this); this.children.forEach((c) => c._all(out)); return out; }
}

function anchor(attrs, parent) {
  const a = new FakeElement('a', attrs);
  if (parent) parent.appendChild(a);
  return a;
}

/** Documento simulado: querySelector(All) para los pocos selectores que usa mdn-contact. */
function makeDocument(opts) {
  opts = opts || {};
  const html = new FakeElement('html', opts.lang ? { lang: opts.lang } : {});
  const scripts = (opts.jsonLd || []).map((j) => {
    const s = new FakeElement('script', { type: 'application/ld+json' });
    s.textContent = typeof j === 'string' ? j : JSON.stringify(j);
    return s;
  });
  const micro = opts.nodes || opts.microdata || []; // nodos en orden de documento
  const doc = {
    documentElement: html,
    cookie: '',
    referrer: opts.referrer || '',
    querySelectorAll(sel) {
      if (sel === 'script[type="application/ld+json"]') return scripts;
      // Selectores simples: tag, tag.clase, [attr="v"], tag[attr="v"]
      const m = /^([a-z0-9]*)(?:\.([a-z0-9_-]+))?(?:\[([a-z-]+)="([^"]*)"\])?$/i.exec(sel);
      if (!m) return [];
      const [, tag, cls, attr, val] = m;
      return micro.filter((e) => {
        if (tag && e.tagName !== tag.toUpperCase()) return false;
        if (cls && !e.classList.contains(cls)) return false;
        if (attr && e._attrs[attr] !== val) return false;
        return true;
      });
    },
    querySelector(sel) { return this.querySelectorAll(sel)[0] || null; }
  };
  return doc;
}

function microNode(prop, text, content, tag, extraAttrs) {
  const attrs = Object.assign({ itemprop: prop }, extraAttrs || {});
  if (content !== undefined) attrs.content = content;
  const n = new FakeElement(tag || 'span', attrs);
  n.textContent = text || '';
  return n;
}

/* ---------------- localStorage / cookies simulados ---------------- */

function makeLocalStorage(initial) {
  const store = Object.assign(Object.create(null), initial || {});
  return {
    _store: store,
    getItem: (k) => (k in store ? store[k] : null),
    setItem: (k, v) => { store[k] = String(v); },
    removeItem: (k) => { delete store[k]; }
  };
}

/**
 * Carga un archivo de public/frontend/js y devuelve { exports, window, clock, sandbox }.
 * opts: { now, lang, pathname, search, hostname, protocol, referrer, crypto (false = sin crypto),
 *         localStorage (objeto inicial), cookie (valor inicial de document.cookie) }
 */
function loadBrowserModule(file, opts) {
  opts = opts || {};
  const clock = makeClock(opts.now);
  const doc = opts.document || makeDocument({ lang: opts.lang, referrer: opts.referrer });
  // Cookie simulada de una sola clave por nombre (suficiente para mdn_attr).
  const jar = Object.create(null);
  if (opts.cookie) {
    for (const part of String(opts.cookie).split('; ')) {
      const i = part.indexOf('=');
      if (i > 0) jar[part.slice(0, i)] = part.slice(i + 1);
    }
  }
  Object.defineProperty(doc, 'cookie', {
    configurable: true,
    get() { return Object.entries(jar).map(([k, v]) => k + '=' + v).join('; '); },
    set(v) {
      const first = String(v).split(';')[0];
      const i = first.indexOf('=');
      if (i > 0) jar[first.slice(0, i)] = first.slice(i + 1);
    }
  });

  const ls = makeLocalStorage(opts.localStorage);
  const win = {
    document: doc,
    localStorage: ls,
    location: {
      hostname: opts.hostname || 'localhost',
      protocol: opts.protocol || 'http:',
      pathname: opts.pathname || '/',
      search: opts.search || ''
    },
    Date: clock.FakeDate
  };
  if (opts.crypto !== false) win.crypto = opts.crypto || webcrypto;
  win.window = win;
  win.globalThis = win;

  const mod = { exports: {} };
  const sandbox = Object.assign(win, { module: mod, exports: mod.exports, console });
  vm.createContext(sandbox);
  const code = fs.readFileSync(path.join(JS_DIR, file), 'utf8');
  vm.runInContext(code, sandbox, { filename: file });
  return { exports: mod.exports, window: win, clock, sandbox, jar, localStorage: ls, document: doc };
}

module.exports = {
  ROOT,
  JS_DIR,
  loadBrowserModule,
  makeClock,
  makeDocument,
  makeLocalStorage,
  microNode,
  FakeElement,
  anchor,
  plain: (x) => JSON.parse(JSON.stringify(x))
};

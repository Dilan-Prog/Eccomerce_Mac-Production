/* Tiny vanilla-JS DOM helpers. No jQuery dependency in admin-ui. */
window.AU = window.AU || {};

AU.qs = (sel, ctx) => (ctx || document).querySelector(sel);
AU.qsa = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

AU.on = (el, event, selectorOrHandler, maybeHandler) => {
  if (typeof selectorOrHandler === "function") {
    el.addEventListener(event, selectorOrHandler);
    return;
  }
  const selector = selectorOrHandler;
  const handler = maybeHandler;
  el.addEventListener(event, (e) => {
    const target = e.target.closest(selector);
    if (target && el.contains(target)) {
      handler(e, target);
    }
  });
};

AU.debounce = (fn, wait) => {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), wait);
  };
};

AU.escapeHtml = (str) => {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
};

AU.csrfToken = () => {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute("content") : "";
};

/**
 * fetch() wrapper: injects CSRF header + JSON parsing, mirrors the
 * {status, message} contract already used across existing delete endpoints.
 */
AU.request = async (url, options = {}) => {
  const opts = Object.assign({ headers: {} }, options);
  opts.headers = Object.assign(
    {
      "X-CSRF-TOKEN": AU.csrfToken(),
      "X-Requested-With": "XMLHttpRequest",
      Accept: "application/json",
    },
    opts.headers
  );
  if (opts.body && !(opts.body instanceof FormData) && typeof opts.body !== "string") {
    opts.headers["Content-Type"] = "application/json";
    opts.body = JSON.stringify(opts.body);
  }
  const res = await fetch(url, opts);
  const contentType = res.headers.get("content-type") || "";
  const data = contentType.includes("application/json") ? await res.json() : await res.text();
  if (!res.ok) {
    const error = new Error((data && data.message) || res.statusText);
    error.status = res.status;
    error.data = data;
    throw error;
  }
  return data;
};

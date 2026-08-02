/* Lightweight toast component. Replaces Toastr on admin-ui pages. */
window.AU = window.AU || {};

(function () {
  let stack = null;

  function ensureStack() {
    if (stack) return stack;
    stack = document.createElement("div");
    stack.className = "au-toast-stack";
    // .au-toast-stack/.au-toast rules are scoped under .admin-ui-v2 —
    // appending to document.body directly would leave it completely unstyled.
    (document.querySelector(".admin-ui-v2") || document.body).appendChild(stack);
    return stack;
  }

  function show(message, type) {
    const el = document.createElement("div");
    el.className = "au-toast au-toast-" + (type || "info");
    el.innerHTML =
      '<span class="au-toast-message"></span><button type="button" class="au-toast-close" aria-label="Cerrar">&times;</button>';
    el.querySelector(".au-toast-message").textContent = message;

    const container = ensureStack();
    container.appendChild(el);
    requestAnimationFrame(() => el.classList.add("is-visible"));

    const remove = () => {
      el.classList.remove("is-visible");
      setTimeout(() => el.remove(), 200);
    };
    el.querySelector(".au-toast-close").addEventListener("click", remove);
    setTimeout(remove, 4500);
  }

  AU.toast = {
    success: (message) => show(message, "success"),
    error: (message) => show(message, "error"),
    info: (message) => show(message, "info"),
  };
})();

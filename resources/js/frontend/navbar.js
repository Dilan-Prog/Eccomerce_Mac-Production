/**
 * navbar.js — búsqueda móvil y mejoras de header
 * Vanilla JS, sin dependencias.
 */
(function () {
  'use strict';

  function initMobileSearch() {
    var toggle = document.getElementById('mobile-search-toggle');
    var bar    = document.getElementById('mobile-search-bar');
    if (!toggle || !bar) return;

    toggle.addEventListener('click', function () {
      var isOpen = bar.classList.contains('is-open');
      bar.classList.toggle('is-open', !isOpen);
      toggle.classList.toggle('active', !isOpen);
      toggle.setAttribute('aria-expanded', String(!isOpen));

      if (!isOpen) {
        var input = bar.querySelector('input[type="text"]');
        if (input) {
          // Pequeño delay para que la animación CSS termine antes de enfocar
          setTimeout(function () { input.focus(); }, 50);
        }
      }
    });

    // Cerrar al presionar Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && bar.classList.contains('is-open')) {
        bar.classList.remove('is-open');
        toggle.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
      }
    });

    // Cerrar al hacer click fuera del header
    document.addEventListener('click', function (e) {
      if (!e.target.closest('header') && bar.classList.contains('is-open')) {
        bar.classList.remove('is-open');
        toggle.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileSearch);
  } else {
    initMobileSearch();
  }
})();

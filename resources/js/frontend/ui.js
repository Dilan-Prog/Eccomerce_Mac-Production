/**
 * ui.js — utilidades UI responsive
 * Vanilla JS, sin dependencias.
 */
(function () {
  'use strict';

  /* ── FOOTER ACCORDION EN MÓVIL ── */
  function initFooterAccordion() {
    var cols = document.querySelectorAll('.footer-col:not(:first-child)');
    cols.forEach(function (col) {
      var heading = col.querySelector('.footer-col-heading');
      var body    = col.querySelector('.footer-col-body');
      if (!heading || !body) return;

      /* Evitar registrar listeners duplicados */
      if (heading.dataset.accordionInit) return;
      heading.dataset.accordionInit = '1';

      heading.setAttribute('role', 'button');
      heading.setAttribute('tabindex', '0');
      heading.setAttribute('aria-expanded', 'false');

      function toggle() {
        var isOpen = col.classList.contains('is-open');
        col.classList.toggle('is-open', !isOpen);
        heading.setAttribute('aria-expanded', String(!isOpen));
      }

      heading.addEventListener('click', toggle);
      heading.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggle();
        }
      });
    });
  }

  /* ── BLOQUEA SCROLL DEL BODY cuando mobile menu está abierto ── */
  function watchMobileMenu() {
    var menuIcon  = document.querySelector('.wsus__mobile_menu_icon');
    var menuClose = document.querySelector('.wsus__mobile_menu_close');
    if (!menuIcon) return;

    menuIcon.addEventListener('click', function () {
      document.body.classList.add('mobile-menu-open');
    });

    if (menuClose) {
      menuClose.addEventListener('click', function () {
        document.body.classList.remove('mobile-menu-open');
      });
    }
  }

  /* ── DESBORDAMIENTO HORIZONTAL: alerta en dev ── */
  function checkOverflow() {
    try {
      if (typeof import.meta !== 'undefined' && import.meta.env && import.meta.env.PROD) return;
    } catch (e) { return; }
    var docWidth = document.documentElement.offsetWidth;
    Array.from(document.querySelectorAll('*')).forEach(function (el) {
      if (el.offsetWidth > docWidth) {
        console.warn('[overflow]', el);
      }
    });
  }

  /* ── INIT ── */
  function init() {
    initFooterAccordion();
    watchMobileMenu();

    // Re-run accordion si cambia el viewport (ej. rotar pantalla)
    var mq = window.matchMedia('(max-width: 767px)');
    mq.addEventListener('change', function () {
      initFooterAccordion();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

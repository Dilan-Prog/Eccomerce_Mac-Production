/**
 * filters.js — drawer de filtros en móvil
 * Vanilla JS, sin dependencias.
 */
(function () {
  'use strict';

  function initFilterDrawer() {
    var btnOpen  = document.getElementById('btn-open-filters');
    var sidebar  = document.querySelector('.wsus__sidebar_main_area');
    var overlay  = document.getElementById('filter-overlay');
    var btnClose = document.getElementById('filter-drawer-close');

    if (!btnOpen || !sidebar) return;

    function openDrawer() {
      sidebar.classList.add('is-open');
      if (overlay) overlay.classList.add('is-open');
      document.body.classList.add('filter-drawer-open');
      if (btnClose) btnClose.focus();
    }

    function closeDrawer() {
      sidebar.classList.remove('is-open');
      if (overlay) overlay.classList.remove('is-open');
      document.body.classList.remove('filter-drawer-open');
      if (btnOpen) btnOpen.focus();
    }

    btnOpen.addEventListener('click', openDrawer);

    if (overlay) overlay.addEventListener('click', closeDrawer);

    if (btnClose) btnClose.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
        closeDrawer();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFilterDrawer);
  } else {
    initFilterDrawer();
  }
})();

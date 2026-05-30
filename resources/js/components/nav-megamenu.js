/**
 * nav-megamenu.js — Mega menú de navegación Mac Del Norte
 * Vanilla JS puro. Sin jQuery, Alpine ni Vue.
 * Responsabilidades:
 *   A) Aria: sincroniza aria-expanded cuando se abre/cierra
 *   B) Teclado: Tab, Enter, Space, Escape, ArrowKeys
 *   C) Click fuera: cierra cualquier mega menú abierto
 *   D) Mobile: en <960px el .main-nav está oculto por CSS
 */

(function () {
    'use strict';

    /** Inicializa el mega menú solo si existe en el DOM */
    function initMegaMenu() {
        const mainNav = document.querySelector('nav.main-nav');
        if (!mainNav) return;

        const navItems = Array.from(mainNav.querySelectorAll('.nav-item'));

        // ── A) HOVER + ARIA ────────────────────────────────────
        navItems.forEach(function (item) {
            const trigger = item.querySelector('.nav-link');
            const megaMenu = item.querySelector('.mega-menu');
            if (!trigger || !megaMenu) return;

            item.addEventListener('mouseenter', function () {
                trigger.setAttribute('aria-expanded', 'true');
            });

            item.addEventListener('mouseleave', function () {
                trigger.setAttribute('aria-expanded', 'false');
                megaMenu.classList.remove('is-open');
            });
        });

        // ── B) TECLADO ─────────────────────────────────────────
        navItems.forEach(function (item) {
            const trigger = item.querySelector('.nav-link');
            const megaMenu = item.querySelector('.mega-menu');
            if (!trigger) return;

            trigger.addEventListener('keydown', function (e) {
                switch (e.key) {
                    case 'Enter':
                    case ' ':
                        if (megaMenu) {
                            e.preventDefault();
                            toggleMegaMenu(item, megaMenu, trigger);
                        }
                        break;

                    case 'Escape':
                        closeMegaMenu(item, megaMenu, trigger);
                        trigger.focus();
                        break;

                    case 'ArrowDown':
                        if (megaMenu) {
                            e.preventDefault();
                            openMegaMenu(item, megaMenu, trigger);
                            const firstLink = megaMenu.querySelector('a, button');
                            if (firstLink) firstLink.focus();
                        }
                        break;
                }
            });

            // Escape dentro del mega menú cierra y regresa al trigger
            if (megaMenu) {
                megaMenu.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        closeMegaMenu(item, megaMenu, trigger);
                        trigger.focus();
                    }
                });
            }
        });

        // ── C) CLICK FUERA ─────────────────────────────────────
        document.addEventListener('click', function (e) {
            if (!e.target.closest('nav.main-nav')) {
                navItems.forEach(function (item) {
                    const megaMenu = item.querySelector('.mega-menu');
                    const trigger = item.querySelector('.nav-link');
                    if (megaMenu) closeMegaMenu(item, megaMenu, trigger);
                });
            }
        });

        // ── D) FOCUS OUT (accesibilidad Tab fuera) ─────────────
        mainNav.addEventListener('focusout', function (e) {
            // Si el foco sale del nav completamente
            if (!mainNav.contains(e.relatedTarget)) {
                navItems.forEach(function (item) {
                    const megaMenu = item.querySelector('.mega-menu');
                    const trigger = item.querySelector('.nav-link');
                    if (megaMenu) closeMegaMenu(item, megaMenu, trigger);
                });
            }
        });
    }

    function openMegaMenu(item, megaMenu, trigger) {
        megaMenu.classList.add('is-open');
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
    }

    function closeMegaMenu(item, megaMenu, trigger) {
        if (!megaMenu) return;
        megaMenu.classList.remove('is-open');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    function toggleMegaMenu(item, megaMenu, trigger) {
        if (megaMenu.classList.contains('is-open')) {
            closeMegaMenu(item, megaMenu, trigger);
        } else {
            openMegaMenu(item, megaMenu, trigger);
        }
    }

    // Ejecuta al cargar el DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMegaMenu);
    } else {
        initMegaMenu();
    }

})();

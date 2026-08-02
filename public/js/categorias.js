'use strict';

/**
 * Página de Categorías de Productos (diseño puro, sin layout).
 * Las categorías (con sus subcategorías y child categorías) vienen de
 * Laravel (modelos Category/Subcategory/ChildCategory) inyectadas en
 * window.CATALOGO_DATA.categorias desde el Blade.
 */

// Datos reales inyectados por el Blade desde el Controller (ver categorias.blade.php).
var CATEGORIAS = (window.CATALOGO_DATA && window.CATALOGO_DATA.categorias) || [];

var ARROW_ICON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="categorias__card-arrow"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>';
var CHEVRON_ICON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>';

// Genera el <li> de una subcategoría, con su propio dropdown de child categorías si tiene.
function renderSubcategoriaItem(sub) {
    var tieneHijas = sub.childCategorias && sub.childCategorias.length > 0;

    var hijasHtml = '';
    if (tieneHijas) {
        hijasHtml = '<ul class="categorias__childcats-list">' +
            sub.childCategorias.map(function (child) {
                return '<li><a href="' + child.url + '">' + child.nombre + '</a></li>';
            }).join('') +
        '</ul>';
    }

    var toggleHtml = tieneHijas
        ? '<button type="button" class="categorias__childcats-toggle" data-action="toggle-childcats" aria-expanded="false" aria-label="Ver subcategorías de ' + sub.nombre + '">' + CHEVRON_ICON_SVG + '</button>'
        : '';

    return (
        '<li class="categorias__subcat">' +
            '<div class="categorias__subcat-row">' +
                '<a href="' + sub.url + '" class="categorias__subcat-link">' + sub.nombre + '</a>' +
                toggleHtml +
            '</div>' +
            hijasHtml +
        '</li>'
    );
}

// Equivalente a un componente presentacional de React: genera el HTML de una tarjeta.
function renderCategoriaCard(categoria) {
    var tieneSubcategorias = categoria.subcategorias && categoria.subcategorias.length > 0;

    var subcatsHtml = '';
    if (tieneSubcategorias) {
        subcatsHtml =
            '<details class="categorias__card-subcats">' +
                '<summary class="categorias__card-subcats-summary">' +
                    CHEVRON_ICON_SVG + ' Subcategorías' +
                '</summary>' +
                '<ul class="categorias__subcats-list">' +
                    categoria.subcategorias.map(renderSubcategoriaItem).join('') +
                '</ul>' +
            '</details>';
    }

    return (
        '<div class="categorias__card-wrap">' +
            '<a href="' + categoria.url + '" class="categorias__card" data-action="ir-categoria">' +
                '<div class="categorias__card-thumb"></div>' +
                '<p class="categorias__card-name">' + categoria.nombre + '</p>' +
                '<p class="categorias__card-count">' + categoria.productos + ' producto' + (categoria.productos === 1 ? '' : 's') + '</p>' +
                '<div class="categorias__card-arrow-row">' + ARROW_ICON_SVG + '</div>' +
            '</a>' +
            subcatsHtml +
        '</div>'
    );
}

// Renderiza el grid completo a partir de una lista de categorías filtradas.
function renderCategorias(lista, grid, emptyState) {
    grid.innerHTML = lista.map(renderCategoriaCard).join('');
    emptyState.hidden = lista.length > 0;
}

// Equivalente a un useMemo: filtra categorías por nombre según el texto buscado.
function filtrarCategorias(termino) {
    var query = termino.trim().toLowerCase();
    if (!query) {
        return CATEGORIAS;
    }
    return CATEGORIAS.filter(function (categoria) {
        return categoria.nombre.toLowerCase().includes(query);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var grid = document.getElementById('categoriasGrid');
    var emptyState = document.getElementById('categoriasEmpty');
    var searchInput = document.getElementById('categoriasSearchInput');
    var menuBtn = document.getElementById('categoriasMenuBtn');

    renderCategorias(CATEGORIAS, grid, emptyState);

    searchInput.addEventListener('input', function () {
        renderCategorias(filtrarCategorias(searchInput.value), grid, emptyState);
    });

    // Delegación de eventos: el grid se re-renderiza al buscar, así que una sola
    // escucha en el contenedor cubre el dropdown de child categorías de cada subcategoría.
    grid.addEventListener('click', function (e) {
        var toggle = e.target.closest('[data-action="toggle-childcats"]');
        if (!toggle) {
            return;
        }
        var lista = toggle.closest('.categorias__subcat').querySelector('.categorias__childcats-list');
        if (!lista) {
            return;
        }
        var isOpen = lista.classList.toggle('is-open');
        toggle.classList.toggle('is-open', isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    if (menuBtn) {
        menuBtn.addEventListener('click', function () {
            var isOpen = menuBtn.getAttribute('aria-expanded') === 'true';
            menuBtn.setAttribute('aria-expanded', String(!isOpen));
        });
    }

    var quoteBtn = document.querySelector('[data-action="solicitar-cotizacion"]');
    if (quoteBtn) {
        quoteBtn.addEventListener('click', function () {
            console.log('Solicitar cotización: pendiente de conectar.');
        });
    }
});

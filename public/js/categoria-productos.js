'use strict';

/**
 * Página de Productos por Categoría (diseño puro, sin layout).
 * Categoría, sidebar y productos vienen de Laravel (modelos Category/Product)
 * inyectados en window.CATALOGO_DATA desde el Blade. Los iconos del menú
 * lateral usan las clases FontAwesome guardadas en `categories.icon`.
 */

var ZOOM_ICON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y1="21" y2="16.65"></line><line x1="11" x2="11" y1="8" y2="14"></line><line x1="8" x2="14" y1="11" y2="11"></line></svg>';
var QUOTE_ICON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path></svg>';
var CHEVRON_ICON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>';

// Genera el <li> de una subcategoría dentro del sidebar, con su propio dropdown de child categorías.
function renderSidebarSubcategoriaItem(sub) {
    var tieneHijas = sub.childCategorias && sub.childCategorias.length > 0;

    var hijasHtml = '';
    if (tieneHijas) {
        hijasHtml = '<ul class="cat-prod__childcats-list">' +
            sub.childCategorias.map(function (child) {
                return '<li><a href="' + child.url + '">' + child.nombre + '</a></li>';
            }).join('') +
        '</ul>';
    }

    var toggleHtml = tieneHijas
        ? '<button type="button" class="cat-prod__childcats-toggle" data-action="toggle-childcats" aria-expanded="false" aria-label="Ver subcategorías de ' + sub.nombre + '">' + CHEVRON_ICON_SVG + '</button>'
        : '';

    return (
        '<li class="cat-prod__subcat">' +
            '<div class="cat-prod__subcat-row">' +
                '<a href="' + sub.url + '" class="cat-prod__subcat-link">' + sub.nombre + '</a>' +
                toggleHtml +
            '</div>' +
            hijasHtml +
        '</li>'
    );
}

function renderSidebar(categorias, slugActivo) {
    var nav = document.getElementById('catProdSidebarNav');
    nav.innerHTML = categorias.map(function (categoria) {
        var activa = categoria.slug === slugActivo;
        var clase = 'cat-prod__sidebar-link' + (activa ? ' cat-prod__sidebar-link--active' : '');
        var icono = categoria.icono ? '<i class="' + categoria.icono + '"></i>' : '';
        var tieneSubcategorias = categoria.subcategorias && categoria.subcategorias.length > 0;

        var subcatsHtml = '';
        if (tieneSubcategorias) {
            subcatsHtml =
                '<details class="cat-prod__sidebar-subcats">' +
                    '<summary class="cat-prod__sidebar-subcats-summary">' +
                        CHEVRON_ICON_SVG + ' Subcategorías' +
                    '</summary>' +
                    '<ul class="cat-prod__subcats-list">' +
                        categoria.subcategorias.map(renderSidebarSubcategoriaItem).join('') +
                    '</ul>' +
                '</details>';
        }

        return (
            '<div class="cat-prod__sidebar-item">' +
                '<a href="' + categoria.url + '" class="' + clase + '">' +
                    icono +
                    '<span>' + categoria.nombre + '</span>' +
                '</a>' +
                subcatsHtml +
            '</div>'
        );
    }).join('');
}

function renderProductoCard(producto) {
    var masModelos = producto.masModelos
        ? '<p class="cat-prod__card-mas-modelos">+ modelos disponibles</p>'
        : '';
    var marca = producto.marca
        ? '<span class="cat-prod__card-badge">' + producto.marca + '</span>'
        : '';
    var thumbStyle = producto.imagen
        ? ' style="background-image:url(\'' + producto.imagen + '\');background-size:cover;background-position:center;"'
        : '';

    return (
        '<article class="cat-prod__card">' +
            '<a class="cat-prod__card-row" href="' + producto.url + '" style="text-decoration:none;color:inherit;">' +
                '<div class="cat-prod__card-thumb"' + thumbStyle + '>' +
                    '<div class="cat-prod__card-thumb-overlay">' + ZOOM_ICON_SVG + '</div>' +
                '</div>' +
                '<div class="cat-prod__card-body">' +
                    (producto.modelo ? '<p class="cat-prod__card-modelo">Modelo: ' + producto.modelo + '</p>' : '') +
                    '<h3 class="cat-prod__card-nombre">' + producto.nombre + '</h3>' +
                    masModelos +
                    marca +
                '</div>' +
            '</a>' +
            '<button type="button" class="cat-prod__card-quote-btn" data-action="solicitar-cotizacion" data-modelo="' + (producto.modelo || producto.nombre) + '">' +
                QUOTE_ICON_SVG + ' Solicitar Cotización' +
            '</button>' +
        '</article>'
    );
}

// Renderiza un grupo de productos: si tiene nombre (subcategoría), antepone un
// separador de título que ocupa todo el ancho del grid; si no, solo las cards.
function renderGrupoProductos(grupo) {
    var header = grupo.nombre
        ? '<div class="cat-prod__group-header"><span class="cat-prod__group-title">' + grupo.nombre + '</span></div>'
        : '';
    return header + grupo.productos.map(renderProductoCard).join('');
}

document.addEventListener('DOMContentLoaded', function () {
    var data = window.CATALOGO_DATA || {};
    var categoria = data.categoria || {};
    var sidebarCategorias = data.sidebarCategorias || [];
    // Cada elemento es un grupo { nombre: <subcategoría o null>, productos: [...] }.
    var gruposProductos = data.productos || [];

    document.getElementById('catProdHeaderTitle').textContent = categoria.nombre || '';
    document.getElementById('catProdBreadcrumbCurrent').textContent = categoria.nombre || '';
    document.getElementById('catProdBannerTitle').textContent = categoria.nombre || '';
    document.getElementById('catProdBannerDesc').textContent = categoria.descripcion || '';
    if (categoria.nombre) {
        document.title = categoria.nombre + ' | Mac Del Norte';
    }

    // Cuando hay un filtro de subcategoría/child categoría activo, el breadcrumb
    // muestra también la categoría padre como un nivel intermedio enlazado.
    var breadcrumbParent = document.getElementById('catProdBreadcrumbParent');
    if (categoria.filtroActivo && categoria.categoriaPadreNombre) {
        breadcrumbParent.innerHTML =
            '<a href="' + categoria.categoriaPadreUrl + '">' + categoria.categoriaPadreNombre + '</a>' +
            '<span class="cat-prod__breadcrumb-sep">&rsaquo;</span>';
    }

    renderSidebar(sidebarCategorias, categoria.slug);

    var sidebarNav = document.getElementById('catProdSidebarNav');
    sidebarNav.addEventListener('click', function (e) {
        var toggle = e.target.closest('[data-action="toggle-childcats"]');
        if (!toggle) {
            return;
        }
        var lista = toggle.closest('.cat-prod__subcat').querySelector('.cat-prod__childcats-list');
        if (!lista) {
            return;
        }
        var isOpen = lista.classList.toggle('is-open');
        toggle.classList.toggle('is-open', isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    var grid = document.getElementById('catProdGrid');
    var emptyState = document.getElementById('catProdEmpty');

    grid.innerHTML = gruposProductos.map(renderGrupoProductos).join('');
    emptyState.hidden = gruposProductos.length > 0;

    // Delegación de eventos para los botones "Solicitar Cotización" de cada tarjeta.
    grid.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action="solicitar-cotizacion"]');
        if (!btn) {
            return;
        }
        e.preventDefault();
        console.log('Solicitar cotización del producto:', btn.dataset.modelo);
    });

    var mobileQuoteBtn = document.querySelector('.cat-prod__mobile-cta [data-action="solicitar-cotizacion"]');
    if (mobileQuoteBtn) {
        mobileQuoteBtn.addEventListener('click', function () {
            console.log('Solicitar cotización general: pendiente de conectar.');
        });
    }

    var shareBtn = document.getElementById('catProdShareBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            var shareData = { title: categoria.nombre || '', url: window.location.href };
            if (navigator.share) {
                navigator.share(shareData).catch(function () {});
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(shareData.url).catch(function () {});
            }
        });
    }

    var menuBtn = document.getElementById('catProdMenuBtn');
    if (menuBtn) {
        menuBtn.addEventListener('click', function () {
            var isOpen = menuBtn.getAttribute('aria-expanded') === 'true';
            menuBtn.setAttribute('aria-expanded', String(!isOpen));
        });
    }
});

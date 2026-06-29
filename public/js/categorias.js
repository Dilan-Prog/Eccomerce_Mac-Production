'use strict';

/**
 * Página de Categorías de Productos (diseño puro, sin layout).
 * Los datos de categorías son estáticos por ahora; cuando se conecte al
 * backend, reemplazar CATEGORIAS por un fetch() a una ruta de Laravel
 * (p.ej. /api/categorias) y volver a llamar a renderCategorias().
 */

// Equivalente a "const [categorias] = useState([...])" en React: datos estáticos.
var CATEGORIAS = [
    { slug: 'controladores-programadores', nombre: 'Controladores y Programadores', productos: 4 },
    { slug: 'automatizacion-control', nombre: 'Automatización y Control', productos: 3 },
    { slug: 'combustion-gas', nombre: 'Combustión y Gas', productos: 4 },
    { slug: 'videoregistradores', nombre: 'Videoregistradores', productos: 3 },
    { slug: 'termopares-rtd', nombre: 'Termopares y RTD', productos: 4 },
    { slug: 'limit-switch', nombre: 'Limit Switch', productos: 3 },
    { slug: 'transductores-presion', nombre: 'Transductores de Presión', productos: 4 },
    { slug: 'sensores-proximidad', nombre: 'Sensores de Proximidad', productos: 3 },
    { slug: 'micro-switch', nombre: 'Micro Switch', productos: 3 },
    { slug: 'relevadores-ssr', nombre: 'Relevadores y SSR', productos: 4 },
    { slug: 'valvulas-mcdonnell-miller', nombre: 'Válvulas (McDonnell & Miller)', productos: 4 },
    { slug: 'timer-relays-contadores', nombre: 'Timer Relays y Contadores', productos: 4 }
];

var ARROW_ICON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="categorias__card-arrow"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>';

// Equivalente a un componente presentacional de React: genera el HTML de una tarjeta.
function renderCategoriaCard(categoria) {
    return (
        '<button type="button" class="categorias__card" data-slug="' + categoria.slug + '" data-action="ir-categoria">' +
            '<div class="categorias__card-thumb"></div>' +
            '<p class="categorias__card-name">' + categoria.nombre + '</p>' +
            '<p class="categorias__card-count">' + categoria.productos + ' producto' + (categoria.productos === 1 ? '' : 's') + '</p>' +
            '<div class="categorias__card-arrow-row">' + ARROW_ICON_SVG + '</div>' +
        '</button>'
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

    // Delegación de eventos: una sola escucha para todas las tarjetas, ya que se re-renderizan.
    grid.addEventListener('click', function (e) {
        var card = e.target.closest('[data-action="ir-categoria"]');
        if (!card) {
            return;
        }
        window.location.href = '/categorias/' + card.dataset.slug;
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

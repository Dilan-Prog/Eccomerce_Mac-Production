'use strict';

/**
 * Página de Productos por Categoría (diseño puro, sin layout).
 * El slug de la categoría viene de Laravel (route param) inyectado en
 * window.CATALOGO_DATA desde el Blade. Los productos son estáticos por
 * ahora; cuando se conecte al backend, reemplazar CATEGORIA_DETALLE por un
 * fetch() a /api/categorias/{slug}/productos.
 */

// Iconos del menú lateral (equivalente a los iconos lucide-react usados en cada categoría).
var SIDEBAR_ICONS = {
    settings: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
    cpu: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"></rect><rect width="6" height="6" x="9" y="9" rx="1"></rect><path d="M15 2v2"></path><path d="M15 20v2"></path><path d="M2 15h2"></path><path d="M2 9h2"></path><path d="M20 15h2"></path><path d="M20 9h2"></path><path d="M9 2v2"></path><path d="M9 20v2"></path></svg>',
    flame: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path></svg>',
    video: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.87a.5.5 0 0 0-.752-.432L16 10.5"></path><rect x="2" y="6" width="14" height="12" rx="2"></rect></svg>',
    thermometer: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"></path></svg>',
    'toggle-right': '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="6" ry="6"></rect><circle cx="16" cy="12" r="2"></circle></svg>',
    gauge: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"></path><path d="M3.34 19a10 10 0 1 1 17.32 0"></path></svg>',
    radio: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.9 19.1C1 15.2 1 8.8 4.9 4.9"></path><path d="M7.8 16.2c-2.3-2.3-2.3-6.1 0-8.5"></path><circle cx="12" cy="12" r="2"></circle><path d="M16.2 7.8c2.3 2.3 2.3 6.1 0 8.5"></path><path d="M19.1 4.9C23 8.8 23 15.1 19.1 19"></path></svg>',
    zap: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path></svg>',
    droplets: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"></path><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"></path></svg>',
    clock: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>'
};

var ZOOM_ICON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y1="21" y2="16.65"></line><line x1="11" x2="11" y1="8" y2="14"></line><line x1="8" x2="14" y1="11" y2="11"></line></svg>';
var QUOTE_ICON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path></svg>';

// Menú lateral de categorías (debe reflejar las mismas categorías de /categorias).
var SIDEBAR_CATEGORIAS = [
    { slug: 'controladores-programadores', nombre: 'Controladores y Programadores', icono: 'settings' },
    { slug: 'automatizacion-control', nombre: 'Automatización y Control', icono: 'cpu' },
    { slug: 'combustion-gas', nombre: 'Combustión y Gas', icono: 'flame' },
    { slug: 'videoregistradores', nombre: 'Videoregistradores', icono: 'video' },
    { slug: 'termopares-rtd', nombre: 'Termopares y RTD', icono: 'thermometer' },
    { slug: 'limit-switch', nombre: 'Limit Switch', icono: 'toggle-right' },
    { slug: 'transductores-presion', nombre: 'Transductores de Presión', icono: 'gauge' },
    { slug: 'sensores-proximidad', nombre: 'Sensores de Proximidad', icono: 'radio' },
    { slug: 'micro-switch', nombre: 'Micro Switch', icono: 'zap' },
    { slug: 'relevadores-ssr', nombre: 'Relevadores y SSR', icono: 'zap' },
    { slug: 'valvulas-mcdonnell-miller', nombre: 'Válvulas (McDonnell & Miller)', icono: 'droplets' },
    { slug: 'timer-relays-contadores', nombre: 'Timer Relays y Contadores', icono: 'clock' }
];

// Detalle (banner + productos) por categoría. Las categorías sin datos aún
// muestran el estado vacío del HTML (#catProdEmpty).
var CATEGORIA_DETALLE = {
    'controladores-programadores': {
        descripcion: 'Control de lazo simple y doble para procesos industriales de alta precisión.',
        productos: [
            { modelo: 'UDC2800', nombre: 'Controlador Universal de Lazo Simple', descripcion: 'Control PID avanzado con autoajuste, comunicación RS-485 y salidas configurables.', marca: 'Honeywell', masModelos: true },
            { modelo: 'UDC3500', nombre: 'Controlador de Lazo Duplex', descripcion: 'Doble lazo PID, pantalla a color, comunicación Modbus RTU y Ethernet.', marca: 'Honeywell', masModelos: false },
            { modelo: 'DCP551', nombre: 'Programador/Controlador de Perfiles', descripcion: 'Programador de temperatura por rampas y escalones, 30 segmentos, salida relay.', marca: 'Honeywell', masModelos: true },
            { modelo: 'DC1020', nombre: 'Controlador de Panel Compacto', descripcion: 'Formato 48×48mm, autoajuste PID, entrada universal termopar/RTD/mA.', marca: 'Honeywell', masModelos: false }
        ]
    }
};

function buscarCategoria(slug) {
    return SIDEBAR_CATEGORIAS.find(function (categoria) {
        return categoria.slug === slug;
    });
}

function renderSidebar(slugActivo, baseUrl) {
    var nav = document.getElementById('catProdSidebarNav');
    nav.innerHTML = SIDEBAR_CATEGORIAS.map(function (categoria) {
        var activa = categoria.slug === slugActivo;
        var clase = 'cat-prod__sidebar-link' + (activa ? ' cat-prod__sidebar-link--active' : '');
        return (
            '<a href="' + baseUrl + '/' + categoria.slug + '" class="' + clase + '">' +
                SIDEBAR_ICONS[categoria.icono] +
                '<span>' + categoria.nombre + '</span>' +
            '</a>'
        );
    }).join('');
}

function renderProductoCard(producto) {
    var masModelos = producto.masModelos
        ? '<p class="cat-prod__card-mas-modelos">+ modelos disponibles</p>'
        : '';

    return (
        '<article class="cat-prod__card">' +
            '<div class="cat-prod__card-row">' +
                '<div class="cat-prod__card-thumb">' +
                    '<div class="cat-prod__card-thumb-overlay">' + ZOOM_ICON_SVG + '</div>' +
                '</div>' +
                '<div class="cat-prod__card-body">' +
                    '<p class="cat-prod__card-modelo">Modelo: ' + producto.modelo + '</p>' +
                    '<h3 class="cat-prod__card-nombre">' + producto.nombre + '</h3>' +
                    '<p class="cat-prod__card-desc">' + producto.descripcion + '</p>' +
                    masModelos +
                    '<span class="cat-prod__card-badge">' + producto.marca + '</span>' +
                '</div>' +
            '</div>' +
            '<button type="button" class="cat-prod__card-quote-btn" data-action="solicitar-cotizacion" data-modelo="' + producto.modelo + '">' +
                QUOTE_ICON_SVG + ' Solicitar Cotización' +
            '</button>' +
        '</article>'
    );
}

document.addEventListener('DOMContentLoaded', function () {
    var slug = (window.CATALOGO_DATA && window.CATALOGO_DATA.categoriaSlug) || '';
    var baseUrl = '/categorias';

    var categoria = buscarCategoria(slug);
    var detalle = CATEGORIA_DETALLE[slug];
    var nombreCategoria = categoria ? categoria.nombre : slug;

    document.getElementById('catProdHeaderTitle').textContent = nombreCategoria;
    document.getElementById('catProdBreadcrumbCurrent').textContent = nombreCategoria;
    document.getElementById('catProdBannerTitle').textContent = nombreCategoria;
    document.getElementById('catProdBannerDesc').textContent = detalle
        ? detalle.descripcion
        : 'Próximamente más información sobre esta categoría.';
    document.title = nombreCategoria + ' | Mac Del Norte';

    renderSidebar(slug, baseUrl);

    var grid = document.getElementById('catProdGrid');
    var emptyState = document.getElementById('catProdEmpty');
    var productos = (detalle && detalle.productos) || [];

    grid.innerHTML = productos.map(renderProductoCard).join('');
    emptyState.hidden = productos.length > 0;

    // Delegación de eventos para los botones "Solicitar Cotización" de cada tarjeta.
    grid.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action="solicitar-cotizacion"]');
        if (!btn) {
            return;
        }
        console.log('Solicitar cotización del modelo:', btn.dataset.modelo);
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
            var shareData = { title: nombreCategoria, url: window.location.href };
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

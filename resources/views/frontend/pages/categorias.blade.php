<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categorías de Productos | Mac Del Norte</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/categorias.css') }}">
</head>
<body>
  <div class="categorias">

    <header class="categorias__header">
      <div class="categorias__header-inner">
        <a href="/" class="categorias__logo">
          <span class="categorias__logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"></rect><rect width="6" height="6" x="9" y="9" rx="1"></rect><path d="M15 2v2"></path><path d="M15 20v2"></path><path d="M2 15h2"></path><path d="M2 9h2"></path><path d="M20 15h2"></path><path d="M20 9h2"></path><path d="M9 2v2"></path><path d="M9 20v2"></path></svg>
          </span>
          <span class="categorias__logo-text">Mac Del Norte</span>
        </a>

        <nav class="categorias__nav">
          <button type="button" class="categorias__nav-link categorias__nav-link--active" data-action="ir-catalogo">Catálogo</button>
          <a href="{{ route('contact') }}" class="categorias__nav-link">Contacto</a>
          <div class="categorias__nav-contact">
            <span class="categorias__nav-contact-line">+52 (81) 0000-0000</span>
            <span class="categorias__nav-contact-line">ventas@macdelnorte.com</span>
          </div>
        </nav>

        <button type="button" class="categorias__menu-btn" id="categoriasMenuBtn" aria-label="Abrir menú" aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="18" y2="18"></line></svg>
        </button>
      </div>
    </header>

    <main class="categorias__main">
      <div class="categorias__container">

        <div class="categorias__heading">
          <p class="categorias__eyebrow">Nuestras Categorías</p>
          <h2 class="categorias__title">¿Qué estás buscando?</h2>
          <p class="categorias__subtitle">Selecciona una categoría para explorar productos</p>
        </div>

        <div class="categorias__search">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="categorias__search-icon"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
          <input type="text" id="categoriasSearchInput" class="categorias__search-input" placeholder="Buscar categoría o producto...">
        </div>

        <div class="categorias__grid" id="categoriasGrid"></div>

        <p class="categorias__empty" id="categoriasEmpty" hidden>No se encontraron categorías para tu búsqueda.</p>

      </div>
    </main>

    <div class="categorias__mobile-cta">
      <button type="button" class="categorias__quote-btn" data-action="solicitar-cotizacion">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path></svg>
        Solicitar Cotización
      </button>
    </div>

  </div>

  <script src="{{ asset('js/categorias.js') }}" defer></script>
</body>
</html>

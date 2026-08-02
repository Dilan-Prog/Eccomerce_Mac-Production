<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $category->name }} | Mac Del Norte</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Inter:wght@400;500;600;700&family=Roboto+Mono:wght@400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/categoria-productos.css') }}?v={{ filemtime(public_path('css/categoria-productos.css')) }}">
</head>
<body>
  <div class="cat-prod">

    <header class="cat-prod__header">
      <div class="cat-prod__header-inner">
        <a href="{{ route('categorias') }}" class="cat-prod__back">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
          Categorías
        </a>

        <span class="cat-prod__header-title" id="catProdHeaderTitle">&nbsp;</span>

        <nav class="cat-prod__nav">
          <a href="{{ route('catalogo') }}" class="cat-prod__nav-link">Catálogo</a>
          <a href="{{ route('contact') }}" class="cat-prod__nav-link">Contacto</a>
          <div class="cat-prod__nav-contact">
            <span class="cat-prod__nav-contact-line">+52 (81) 0000-0000</span>
            <span class="cat-prod__nav-contact-line">ventas@macdelnorte.com</span>
          </div>
        </nav>

        <div class="cat-prod__header-actions">
          <button type="button" class="cat-prod__icon-btn" id="catProdShareBtn" aria-label="Compartir">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"></line><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"></line></svg>
          </button>
          <button type="button" class="cat-prod__icon-btn cat-prod__icon-btn--dark" id="catProdMenuBtn" aria-label="Abrir menú" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="18" y2="18"></line></svg>
          </button>
        </div>
      </div>
    </header>

    <main class="cat-prod__main">

      <aside class="cat-prod__sidebar">
        <div class="cat-prod__sidebar-brand">
          <span class="cat-prod__sidebar-brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"></rect><rect width="6" height="6" x="9" y="9" rx="1"></rect><path d="M15 2v2"></path><path d="M15 20v2"></path><path d="M2 15h2"></path><path d="M2 9h2"></path><path d="M20 15h2"></path><path d="M20 9h2"></path><path d="M9 2v2"></path><path d="M9 20v2"></path></svg>
          </span>
          <span class="cat-prod__sidebar-brand-text">Catálogo de Productos</span>
        </div>
        <nav class="cat-prod__sidebar-nav" id="catProdSidebarNav"></nav>
      </aside>

      <div class="cat-prod__content">

        <p class="cat-prod__breadcrumb">
          <a href="/">Inicio</a>
          <span class="cat-prod__breadcrumb-sep">&rsaquo;</span>
          <a href="{{ route('categorias') }}">Categorías</a>
          <span class="cat-prod__breadcrumb-sep">&rsaquo;</span>
          <span id="catProdBreadcrumbParent"></span>
          <span class="cat-prod__breadcrumb-current" id="catProdBreadcrumbCurrent">&nbsp;</span>
        </p>

        <div class="cat-prod__banner">
          <div class="cat-prod__banner-bar"></div>
          <div>
            <h2 class="cat-prod__banner-title" id="catProdBannerTitle">&nbsp;</h2>
            <p class="cat-prod__banner-desc" id="catProdBannerDesc">&nbsp;</p>
          </div>
        </div>

        <div class="cat-prod__grid" id="catProdGrid"></div>

        <p class="cat-prod__empty" id="catProdEmpty" hidden>
          Aún no hay productos cargados para esta categoría. Vuelve pronto.
        </p>

      </div>
    </main>

    <div class="cat-prod__mobile-cta">
      <button type="button" class="cat-prod__quote-btn" data-action="solicitar-cotizacion">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path></svg>
        Solicitar Cotización
      </button>
    </div>

  </div>

  <script>
    window.CATALOGO_DATA = {
        categoria: @json($categoriaData),
        sidebarCategorias: @json($sidebarCategoriasData),
        productos: @json($productosData)
    };
  </script>
  <script src="{{ asset('js/categoria-productos.js') }}?v={{ filemtime(public_path('js/categoria-productos.js')) }}" defer></script>
</body>
</html>

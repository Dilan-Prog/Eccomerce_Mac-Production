<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo de Productos Industriales 2025 | Mac Del Norte</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
</head>
<body>
  <main>
    <section class="catalogo-hero">

      {{-- Versión escritorio (>= 992px) --}}
      <div class="catalogo-hero__desktop">
        <div class="catalogo-hero__desktop-media">
          <img src="https://images.unsplash.com/photo-1780034766228-3fd70d9463c3?w=1400&h=900&fit=crop&auto=format" alt="Panel de control industrial" class="catalogo-hero__desktop-img">
          <div class="catalogo-hero__desktop-overlay"></div>
          <div class="catalogo-hero__glow"></div>
        </div>

        <div class="catalogo-hero__desktop-panel">
          <div class="catalogo-hero__bottom-bar"></div>

          <div class="catalogo-hero__brand">
            <span class="catalogo-hero__brand-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"></rect><rect width="6" height="6" x="9" y="9" rx="1"></rect><path d="M15 2v2"></path><path d="M15 20v2"></path><path d="M2 15h2"></path><path d="M2 9h2"></path><path d="M20 15h2"></path><path d="M20 9h2"></path><path d="M9 2v2"></path><path d="M9 20v2"></path></svg>
            </span>
            <span class="catalogo-hero__brand-name">Mac Del Norte</span>
          </div>

          <h1 class="catalogo-hero__title">Catálogo de Productos Industriales 2025</h1>
          <div class="catalogo-hero__divider"></div>

          <div class="catalogo-hero__tags">
            <span class="catalogo-hero__tag">Instrumentación</span>
            <span class="catalogo-hero__tag">Automatización</span>
            <span class="catalogo-hero__tag">Control</span>
          </div>

          <div class="catalogo-hero__actions">
            <a href="{{ route('categorias') }}" class="catalogo-hero__btn catalogo-hero__btn--primary" data-action="ver-catalogo">
              Ver Catálogo
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            </a>
            <a href="https://wa.link/f28njw" target="_blank" class="catalogo-hero__btn catalogo-hero__btn--secondary" data-action="contactar-asesor">
              Contactar Asesor
            </a>
          </div>
        </div>
      </div>

      {{-- Versión móvil/tablet (< 992px) --}}
      <div class="catalogo-hero__mobile">
        <img src="https://images.unsplash.com/photo-1780034766228-3fd70d9463c3?w=1400&h=900&fit=crop&auto=format" alt="Panel de control industrial" class="catalogo-hero__mobile-img">
        <div class="catalogo-hero__mobile-overlay"></div>
        <div class="catalogo-hero__glow catalogo-hero__glow--mobile"></div>

        <div class="catalogo-hero__mobile-content">
          <div class="catalogo-hero__brand catalogo-hero__brand--mobile">
            <span class="catalogo-hero__brand-icon catalogo-hero__brand-icon--mobile">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"></rect><rect width="6" height="6" x="9" y="9" rx="1"></rect><path d="M15 2v2"></path><path d="M15 20v2"></path><path d="M2 15h2"></path><path d="M2 9h2"></path><path d="M20 15h2"></path><path d="M20 9h2"></path><path d="M9 2v2"></path><path d="M9 20v2"></path></svg>
            </span>
            <span class="catalogo-hero__brand-name catalogo-hero__brand-name--mobile">Mac Del Norte</span>
          </div>

          <h1 class="catalogo-hero__title catalogo-hero__title--mobile">Catálogo de Productos Industriales 2025</h1>
          <div class="catalogo-hero__divider catalogo-hero__divider--mobile"></div>

          <div class="catalogo-hero__tags">
            <span class="catalogo-hero__tag catalogo-hero__tag--mobile">Instrumentación</span>
            <span class="catalogo-hero__tag catalogo-hero__tag--mobile">Automatización</span>
            <span class="catalogo-hero__tag catalogo-hero__tag--mobile">Control</span>
          </div>

          <a href="{{ route('categorias') }}" class="catalogo-hero__btn catalogo-hero__btn--primary catalogo-hero__btn--full" data-action="ver-catalogo">
            Ver Catálogo
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
          </a>

          <p class="catalogo-hero__scroll-hint">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
            Desliza para explorar
          </p>
        </div>
      </div>

    </section>
  </main>

  <script src="{{ asset('js/catalogo.js') }}" defer></script>
</body>
</html>

@push('styles')
<style>
  /* ============ CATEGORÍAS PRINCIPALES ============ */
  .categories-hero-section {
    padding: 64px 0 56px;
    background: var(--blanco, #fff);
  }
  .home-section-header {
    text-align: center;
    margin-bottom: 48px;
  }
  .home-section-eyebrow {
    display: inline-block;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--accent-cta, #F7941D);
    font-weight: 800;
    margin-bottom: 10px;
  }
  .home-section-title {
    font-size: 36px;
    font-weight: 800;
    color: var(--azul-principal, #003E7E);
    letter-spacing: -0.8px;
    line-height: 1.2;
    max-width: 720px;
    margin: 0 auto 14px;
  }
  .home-section-sub {
    font-size: 16px;
    color: var(--gris-texto, #4A5568);
    max-width: 600px;
    margin: 0 auto;
  }

  .categories-main-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
  .cat-main-card {
    background: #fff;
    border: 1.5px solid var(--gris-borde, #DDE3EA);
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s;
    position: relative;
    display: flex;
    flex-direction: column;
  }
  .cat-main-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(0,62,126,0.12);
    border-color: var(--azul-principal, #003E7E);
    text-decoration: none;
    color: inherit;
  }
  .cat-main-card.brand {
    border-color: var(--amarillo-destacado, #F6AD1C);
    border-width: 2px;
    background: linear-gradient(135deg, var(--azul-claro, #E6EFF8) 0%, #fff 100%);
  }
  .cat-main-card.brand:hover {
    border-color: var(--azul-principal, #003E7E);
    box-shadow: 0 16px 32px rgba(0,62,126,0.18);
  }
  .cat-main-image {
    height: 150px;
    background: linear-gradient(135deg, var(--azul-principal, #003E7E) 0%, var(--azul-medio, #0057A8) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    position: relative;
    overflow: hidden;
  }
  .cat-main-card.brand .cat-main-image {
    background: linear-gradient(135deg, var(--azul-oscuro, #002856) 0%, var(--azul-principal, #003E7E) 100%);
    border-bottom: 3px solid var(--amarillo-destacado, #F6AD1C);
  }
  .cat-main-image::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
    background-size: 20px 20px;
    pointer-events: none;
  }
  .cat-main-image svg {
    width: 60px;
    height: 60px;
    opacity: 0.95;
    position: relative;
    z-index: 2;
  }
  .cat-brand-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--amarillo-destacado, #F6AD1C);
    color: var(--azul-oscuro, #002856);
    font-size: 9px;
    font-weight: 800;
    padding: 5px 10px;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 4;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    line-height: 1;
  }
  .cat-main-body {
    padding: 22px;
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  .cat-main-body h3 {
    font-size: 18px;
    font-weight: 800;
    color: var(--azul-principal, #003E7E);
    margin-bottom: 8px;
    letter-spacing: -0.3px;
  }
  .cat-main-body > p {
    font-size: 13px;
    color: var(--gris-texto, #4A5568);
    line-height: 1.5;
    margin-bottom: 14px;
    flex: 1;
  }
  .cat-subcategories {
    font-size: 12px;
    color: var(--gris-claro-texto, #718096);
    margin-bottom: 14px;
    line-height: 1.5;
  }
  .cat-subcategories strong {
    color: var(--azul-principal, #003E7E);
    font-weight: 700;
  }
  .cat-main-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: var(--accent-cta, #F7941D);
    margin-top: auto;
  }
  .cat-main-link svg { width: 14px; height: 14px; }

  /* ============ POR QUÉ ELEGIRNOS ============ */
  .why-us-section {
    padding: 64px 0 56px;
    background: linear-gradient(180deg, #fff 0%, var(--gris-fondo, #F5F7FA) 100%);
    position: relative;
  }
  .why-us-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-top: 40px;
  }
  .why-us-card {
    background: #fff;
    border: 1.5px solid var(--gris-borde, #DDE3EA);
    border-radius: 12px;
    padding: 28px 22px;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
  }
  .why-us-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(0,62,126,0.12);
    border-color: var(--azul-principal, #003E7E);
  }
  .why-us-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--accent-cta, #F7941D);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s;
  }
  .why-us-card:hover::before { transform: scaleX(1); }
  .why-us-card.featured {
    background: linear-gradient(135deg, var(--azul-oscuro, #002856) 0%, var(--azul-principal, #003E7E) 100%);
    color: #fff;
    border-color: var(--amarillo-destacado, #F6AD1C);
  }
  .why-us-card.featured::before {
    background: var(--amarillo-destacado, #F6AD1C);
    transform: scaleX(1);
  }
  .why-us-card.featured::after {
    content: '';
    position: absolute;
    top: -30%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(246,173,28,0.18) 0%, transparent 60%);
    pointer-events: none;
  }
  .why-us-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 18px;
    background: var(--azul-claro, #E6EFF8);
    color: var(--azul-principal, #003E7E);
    transition: all 0.3s;
    position: relative;
    z-index: 2;
  }
  .why-us-card:hover .why-us-icon {
    background: var(--accent-cta, #F7941D);
    color: #fff;
    transform: scale(1.05);
  }
  .why-us-card.featured .why-us-icon {
    background: rgba(246,173,28,0.18);
    color: var(--amarillo-destacado, #F6AD1C);
    border: 1px solid rgba(246,173,28,0.4);
  }
  .why-us-card.featured:hover .why-us-icon {
    background: var(--amarillo-destacado, #F6AD1C);
    color: var(--azul-oscuro, #002856);
  }
  .why-us-icon svg { width: 28px; height: 28px; }
  .why-us-badge {
    display: inline-block;
    background: var(--amarillo-destacado, #F6AD1C);
    color: var(--azul-oscuro, #002856);
    font-size: 9px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 10px;
    position: relative;
    z-index: 2;
  }
  .why-us-card h3 {
    font-size: 18px;
    font-weight: 800;
    color: var(--azul-principal, #003E7E);
    line-height: 1.2;
    margin-bottom: 10px;
    letter-spacing: -0.3px;
    position: relative;
    z-index: 2;
  }
  .why-us-card.featured h3 { color: #fff; }
  .why-us-card p {
    font-size: 13px;
    color: var(--gris-texto, #4A5568);
    line-height: 1.6;
    margin-bottom: 14px;
    position: relative;
    z-index: 2;
  }
  .why-us-card.featured p { color: rgba(255,255,255,0.92); }
  .why-us-card-stat {
    background: var(--gris-fondo, #F5F7FA);
    border-radius: 8px;
    padding: 10px 12px;
    border-left: 3px solid var(--accent-cta, #F7941D);
    margin-top: auto;
    position: relative;
    z-index: 2;
  }
  .why-us-card.featured .why-us-card-stat {
    background: rgba(255,255,255,0.1);
    border-left-color: var(--amarillo-destacado, #F6AD1C);
  }
  .why-us-card-stat-num {
    font-size: 20px;
    font-weight: 800;
    color: var(--azul-principal, #003E7E);
    line-height: 1;
    letter-spacing: -0.5px;
    margin-bottom: 2px;
  }
  .why-us-card.featured .why-us-card-stat-num { color: var(--amarillo-destacado, #F6AD1C); }
  .why-us-card-stat-label {
    font-size: 11px;
    color: var(--gris-claro-texto, #718096);
    font-weight: 600;
  }
  .why-us-card.featured .why-us-card-stat-label { color: rgba(255,255,255,0.8); }
  .why-us-card-flex {
    display: flex;
    flex-direction: column;
    height: 100%;
  }

  /* ============ BANDA DE MARCAS ============ */
  .brands-bar-home {
    background: var(--gris-fondo, #F5F7FA);
    padding: 36px 0;
    border-top: 1px solid var(--gris-borde, #DDE3EA);
  }
  .brands-bar-title {
    text-align: center;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    color: var(--gris-claro-texto, #718096);
    font-weight: 700;
    margin-bottom: 22px;
  }
  .brands-bar-list {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 40px;
    flex-wrap: wrap;
  }
  .brand-logo-item {
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.5px;
    color: var(--gris-claro-texto, #718096);
    padding: 10px 20px;
    transition: all 0.2s;
    filter: grayscale(100%);
    opacity: 0.55;
  }
  .brand-logo-item:hover { filter: none; opacity: 1; }
  .brand-logo-item.honeywell { color: #EE1C25; filter: none; opacity: 1; }

  /* ============ RESPONSIVE ============ */
  @media (max-width: 960px) {
    .categories-main-grid { grid-template-columns: repeat(2, 1fr); }
    .why-us-grid { grid-template-columns: repeat(2, 1fr); }
    .home-section-title { font-size: 28px; }
  }
  @media (max-width: 560px) {
    .categories-main-grid { grid-template-columns: 1fr; }
    .why-us-grid { grid-template-columns: 1fr; }
    .brands-bar-list { gap: 20px; }
  }
</style>
@endpush

<!-- BANDA DE MARCAS -->
<section class="brands-bar-home">
  <div class="container">
    <div class="brands-bar-title">Distribuidor autorizado de las marcas líderes</div>
    <div class="brands-bar-list">
        @foreach ($brands as $brand)
          <div class="brand-logo-item"><img src="{{ $brand->logo }}" alt="{{ $brand->name }}"></div>
        @endforeach
      {{-- <div class="brand-logo-item honeywell">Honeywell</div>
      <div class="brand-logo-item">Resideo</div>
      <div class="brand-logo-item">McDonnell &amp; Miller</div>
      <div class="brand-logo-item">+ marcas</div> --}}
    </div>
  </div>
</section>

<!-- ============ SECCIÓN DE 6 CATEGORÍAS PRINCIPALES ============ -->
<section class="categories-hero-section">
  <div class="container">
    <div class="home-section-header">
      <div class="home-section-eyebrow">Catálogo</div>
      <h2 class="home-section-title">Encuentra lo que necesitas en 1 clic</h2>
      <p class="home-section-sub">Categorías reorganizadas por función industrial. Más de 500 productos clasificados para que encuentres lo que buscas en segundos.</p>
    </div>

    <div class="categories-main-grid">

      <a href="#?category=instrumentacion" class="cat-main-card">
        <div class="cat-main-image">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11 9H9V6H7v3H5v2h2v3h2v-3h2V9zm7-4H8v2h10V5zm0 8h-3v-3h-2v3h-3v2h3v3h2v-3h3v-2z"/></svg>
        </div>
        <div class="cat-main-body">
          <h3>Instrumentación</h3>
          <p>Para medir variables de proceso en tu planta con precisión y confiabilidad.</p>
          <div class="cat-subcategories">
            <strong>Incluye:</strong> Termopares y RTD, transductores de presión, sensores de proximidad, sensores de nivel, medidores de flujo
          </div>
          <span class="cat-main-link">
            Ver productos
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </span>
        </div>
      </a>

      <a href="#?category=automatizacion-y-control" class="cat-main-card">
        <div class="cat-main-image">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        </div>
        <div class="cat-main-body">
          <h3>Automatización y Control</h3>
          <p>El cerebro de tu proceso: controla, regula y automatiza tus operaciones industriales.</p>
          <div class="cat-subcategories">
            <strong>Incluye:</strong> Controladores UDC, programadores, PLCs y HMI, timer relays, válvulas de control
          </div>
          <span class="cat-main-link">
            Ver productos
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </span>
        </div>
      </a>

      <a href="#?category=combustion-y-gas-industrial" class="cat-main-card">
        <div class="cat-main-image">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.66 11.2c-.23-.3-.51-.56-.77-.82-.66-.6-1.41-1.03-2.05-1.66C13.36 7.26 13 4.85 13.95 3c-.95.23-1.78.75-2.49 1.32-2.59 2.08-3.61 5.75-2.39 8.9.04.1.08.2.08.33 0 .22-.15.42-.35.5-.23.1-.47.04-.66-.12-.06-.05-.1-.1-.15-.17-1.13-1.43-1.31-3.48-.55-5.12C5.78 10 4.87 12.3 5 14.47c.06.5.12 1 .29 1.5.14.6.41 1.2.71 1.73 1.08 1.73 2.95 2.97 4.96 3.22 2.14.27 4.43-.12 6.07-1.6 1.83-1.66 2.47-4.32 1.53-6.6l-.13-.26z"/></svg>
        </div>
        <div class="cat-main-body">
          <h3>Combustión y Gas Industrial</h3>
          <p>Soluciones completas para sistemas de combustión, calderas y manejo de gas industrial.</p>
          <div class="cat-subcategories">
            <strong>Incluye:</strong> Detectores de flama, válvulas de gas, reguladores, quemadores, línea Resideo
          </div>
          <span class="cat-main-link">
            Ver productos
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </span>
        </div>
      </a>

      <a href="#?category=switches-y-relevadores" class="cat-main-card">
        <div class="cat-main-image">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        </div>
        <div class="cat-main-body">
          <h3>Switches y Relevadores</h3>
          <p>Componentes de señalización, maniobra y conmutación eléctrica industrial.</p>
          <div class="cat-subcategories">
            <strong>Incluye:</strong> Limit switches, micro switches, relevadores y SSR, contadores, contactores
          </div>
          <span class="cat-main-link">
            Ver productos
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </span>
        </div>
      </a>

      <a href="#?category=registro-y-monitoreo" class="cat-main-card">
        <div class="cat-main-image">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
        </div>
        <div class="cat-main-body">
          <h3>Registro y Monitoreo</h3>
          <p>Captura, registro y supervisión de datos de proceso con tecnología de última generación.</p>
          <div class="cat-subcategories">
            <strong>Incluye:</strong> Video registradores, registradores de papel, data loggers, indicadores, software SCADA
          </div>
          <span class="cat-main-link">
            Ver productos
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </span>
        </div>
      </a>

      <a href="#?category=mcdonell-miller" class="cat-main-card brand">
        <div class="cat-main-image">
          <span class="cat-brand-badge">Distribuidor autorizado</span>
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14.5 6c0-1.38-1.12-2.5-2.5-2.5S9.5 4.62 9.5 6v.5h5V6zm-2.5 5h-7v9h14v-9h-7zM12 16c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
        </div>
        <div class="cat-main-body">
          <h3>McDonnell &amp; Miller</h3>
          <p>Línea completa para control de calderas industriales, sistemas de vapor y manejo de agua.</p>
          <div class="cat-subcategories">
            <strong>Incluye:</strong> Controles LWCO, controles de bombeo, switches de flujo, indicadores de nivel, refacciones
          </div>
          <span class="cat-main-link">
            Ver línea completa
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </span>
        </div>
      </a>

    </div>
  </div>
</section>

<!-- ============ SECCIÓN POR QUÉ ELEGIRNOS ============ -->
<section class="why-us-section">
  <div class="container">
    <div class="home-section-header">
      <div class="home-section-eyebrow">Por qué elegirnos</div>
      <h2 class="home-section-title">Más de 500 plantas industriales ya nos eligieron</h2>
      <p class="home-section-sub">No somos un revendedor más. Somos canal autorizado Honeywell con respaldo técnico humano y velocidad real para tu operación.</p>
    </div>

    <div class="why-us-grid">

      <div class="why-us-card featured">
        <div class="why-us-card-flex">
          <span class="why-us-badge">⭐ Autenticidad</span>
          <div class="why-us-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 15l-4-4 1.41-1.41L10 13.17l6.59-6.59L18 8l-8 8z"/></svg>
          </div>
          <h3>Distribuidor autorizado Honeywell</h3>
          <p>No revendemos: somos canal oficial. Productos 100% originales, garantía de fábrica y certificados directos del fabricante.</p>
          <div class="why-us-card-stat">
            <div class="why-us-card-stat-num">3 marcas</div>
            <div class="why-us-card-stat-label">Honeywell · Resideo · McDonnell &amp; Miller</div>
          </div>
        </div>
      </div>

      <div class="why-us-card">
        <div class="why-us-card-flex">
          <div class="why-us-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          </div>
          <h3>Velocidad que tu planta necesita</h3>
          <p>Cotización con ingeniero en menos de 2 horas. Compra antes de las 4:00 PM y tu equipo sale el mismo día con paquetería confiable.</p>
          <div class="why-us-card-stat">
            <div class="why-us-card-stat-num">&lt; 2 horas</div>
            <div class="why-us-card-stat-label">Tiempo promedio de cotización</div>
          </div>
        </div>
      </div>

      <div class="why-us-card">
        <div class="why-us-card-flex">
          <div class="why-us-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
          </div>
          <h3>Respaldo técnico humano</h3>
          <p>No es un call center. Hablas con ingenieros certificados que conocen tu equipo, te ayudan en la selección, instalación y puesta en marcha.</p>
          <div class="why-us-card-stat">
            <div class="why-us-card-stat-num">100%</div>
            <div class="why-us-card-stat-label">Atención de ingenieros mexicanos</div>
          </div>
        </div>
      </div>

      <div class="why-us-card">
        <div class="why-us-card-flex">
          <div class="why-us-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
          </div>
          <h3>Más de 7 años en la industria</h3>
          <p>Conocemos las necesidades del comprador industrial mexicano. Servimos a plantas, técnicos y revendedores en 25 estados del país.</p>
          <div class="why-us-card-stat">
            <div class="why-us-card-stat-num">7+ años</div>
            <div class="why-us-card-stat-label">Sirviendo a la industria mexicana</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

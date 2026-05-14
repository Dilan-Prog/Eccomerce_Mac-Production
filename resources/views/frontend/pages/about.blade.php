@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} || Nosotros
@endsection

@push('styles')
<style>
  :root {
    --azul-principal: #003E7E;
    --azul-oscuro: #002856;
    --azul-medio: #0057A8;
    --azul-claro: #E6EFF8;
    --blanco: #FFFFFF;
    --gris-fondo: #F5F7FA;
    --gris-borde: #DDE3EA;
    --gris-texto: #4A5568;
    --gris-claro-texto: #718096;
    --negro-texto: #1A202C;
    --accent-cta: #F7941D;
    --accent-cta-hover: #E08416;
    --verde-disponible: #2F855A;
    --amarillo-destacado: #F6AD1C;
  }

  /* ========== HERO NOSOTROS ========== */
  .about-hero {
    background: linear-gradient(135deg, var(--azul-oscuro) 0%, var(--azul-principal) 60%, var(--azul-medio) 100%);
    color: var(--blanco);
    padding: 80px 0;
    position: relative;
    overflow: hidden;
  }
  .about-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
    background-size: 50px 50px;
  }
  .about-hero-inner {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
  }
  .about-eyebrow {
    display: inline-block;
    background: rgba(246,173,28,0.18);
    border: 1px solid rgba(246,173,28,0.45);
    color: var(--amarillo-destacado);
    font-size: 12px;
    font-weight: 800;
    padding: 8px 16px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 20px;
  }
  .about-hero .about-hero-title {
    font-size: 52px;
    line-height: 1.1;
    font-weight: 800;
    margin-bottom: 20px;
    letter-spacing: -1.5px;
    color: var(--blanco);
  }
  .about-hero .about-hero-title .accent {
    color: var(--amarillo-destacado);
    position: relative;
    white-space: nowrap;

  }
  .about-hero .about-hero-title .accent::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--amarillo-destacado);
    opacity: 0.4;
  }
  .about-hero-sub {
    font-size: 18px;
    line-height: 1.6;
    opacity: 0.9;
    max-width: 700px;
    margin: 0 auto;
    color: var(--blanco)
  }

  /* ========== INTRO QUIÉNES SOMOS ========== */
  .intro-section {
    padding: 80px 0;
    background: var(--blanco);
  }
  .intro-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 56px;
    align-items: center;
  }
  .intro-text h2 {
    font-size: 38px;
    font-weight: 800;
    color: var(--azul-principal);
    line-height: 1.2;
    margin-bottom: 20px;
    letter-spacing: -0.8px;
  }
  .intro-text h2 .accent { color: var(--accent-cta); }
  .intro-text p {
    font-size: 16px;
    line-height: 1.75;
    color: var(--gris-texto);
    margin-bottom: 16px;
  }
  .intro-text strong { color: var(--azul-principal); font-weight: 700; }
  .intro-image {
    aspect-ratio: 4/5;
    background: linear-gradient(135deg, var(--azul-claro) 0%, #B8D4F0 100%);
    border-radius: 12px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--azul-principal);
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,62,126,0.15);
  }
  .placeholder-tag {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(255,255,255,0.95);
    color: var(--azul-principal);
    font-size: 11px;
    font-weight: 800;
    padding: 6px 12px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 3;
  }
  .placeholder-content {
    text-align: center;
    padding: 24px;
  }
  .placeholder-content svg {
    width: 80px;
    height: 80px;
    opacity: 0.5;
    margin-bottom: 12px;
  }
  .placeholder-content p {
    font-weight: 600;
    font-size: 14px;
    opacity: 0.7;
  }

  /* ========== NÚMEROS / TRAYECTORIA ========== */
  .stats-section {
    padding: 64px 0;
    background: var(--azul-principal);
    color: var(--blanco);
    position: relative;
    overflow: hidden;
  }
  .stats-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size: 40px 40px;
  }
  .stats-inner {
    position: relative;
    z-index: 2;
    text-align: center;
  }
  .stats-eyebrow {
    color: var(--amarillo-destacado);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 12px;
  }
  .stats-section h3 {
    color: var(--blanco)
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 48px;
    letter-spacing: -0.5px;
  }
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 32px;
  }
  .stat { text-align: center; }
  .stat-num-big {
    font-size: 56px;
    font-weight: 800;
    color: var(--amarillo-destacado);
    line-height: 1;
    margin-bottom: 8px;
    letter-spacing: -2px;
  }
  .stat-label-big {
    font-size: 14px;
    font-weight: 600;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  /* ========== MISIÓN VISIÓN VALORES ========== */
  .mvv-section {
    padding: 80px 0;
    background: var(--gris-fondo);
  }
  .section-header {
    text-align: center;
    margin-bottom: 56px;
  }
  .section-eyebrow {
    display: inline-block;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--accent-cta);
    font-weight: 800;
    margin-bottom: 12px;
  }
  .section-title {
    font-size: 38px;
    font-weight: 800;
    color: var(--azul-principal);
    letter-spacing: -0.8px;
    line-height: 1.2;
    max-width: 720px;
    margin: 0 auto 14px;
  }
  .section-sub {
    font-size: 16px;
    color: var(--gris-texto);
    max-width: 640px;
    margin: 0 auto;
  }
  .mvv-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
  }
  .mvv-card {
    background: var(--blanco);
    border-radius: 12px;
    padding: 36px 28px;
    border: 1px solid var(--gris-borde);
    border-top: 4px solid var(--azul-principal);
    transition: all 0.3s;
    position: relative;
  }
  .mvv-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(0,62,126,0.1);
    border-top-color: var(--accent-cta);
  }
  .mvv-icon {
    width: 64px;
    height: 64px;
    background: var(--azul-claro);
    color: var(--azul-principal);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
  }
  .mvv-icon svg { width: 32px; height: 32px; }
  .mvv-card h3 {
    font-size: 22px;
    font-weight: 800;
    color: var(--azul-principal);
    margin-bottom: 12px;
    letter-spacing: -0.3px;
  }
  .mvv-card p {
    font-size: 15px;
    line-height: 1.7;
    color: var(--gris-texto);
  }
  .values-list {
    margin-top: 16px;
    list-style: none;
    padding: 0;
  }
  .values-list li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 14px;
    color: var(--gris-texto);
  }
  .values-list li::before {
    content: '';
    width: 18px;
    height: 18px;
    background: var(--azul-claro);
    color: var(--azul-principal);
    border-radius: 50%;
    flex-shrink: 0;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%23003E7E'%3E%3Cpath d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 12px 12px;
    margin-top: 2px;
  }
  .values-list li strong {
    color: var(--azul-principal);
    font-weight: 700;
  }

  /* ========== EQUIPO ========== */
  .team-section {
    padding: 80px 0;
    background: var(--blanco);
  }
  .team-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
  }
  .team-card {
    background: var(--blanco);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--gris-borde);
    transition: all 0.3s;
  }
  .team-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(0,62,126,0.12);
  }
  .team-photo {
    aspect-ratio: 1;
    background: linear-gradient(135deg, var(--azul-claro) 0%, #B8D4F0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--azul-principal);
    position: relative;
  }
  .team-photo svg {
    width: 64px;
    height: 64px;
    opacity: 0.6;
  }
  .team-info {
    padding: 20px;
    text-align: center;
  }
  .team-name {
    font-size: 17px;
    font-weight: 800;
    color: var(--azul-principal);
    margin-bottom: 4px;
  }
  .team-role {
    font-size: 13px;
    color: var(--accent-cta);
    font-weight: 700;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .team-specialty {
    font-size: 13px;
    color: var(--gris-claro-texto);
    line-height: 1.5;
  }
  .team-cta {
    text-align: center;
    margin-top: 32px;
    padding: 24px;
    background: var(--gris-fondo);
    border-radius: 8px;
    font-size: 15px;
    color: var(--gris-texto);
  }
  .team-cta strong { color: var(--azul-principal); }

  /* ========== UBICACIÓN / OFICINAS ========== */
  .location-section {
    padding: 80px 0;
    background: var(--gris-fondo);
  }
  .location-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 48px;
    align-items: stretch;
  }
  .location-info {
    background: var(--blanco);
    border-radius: 12px;
    padding: 40px;
    border: 1px solid var(--gris-borde);
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .location-info h3 {
    font-size: 28px;
    font-weight: 800;
    color: var(--azul-principal);
    margin-bottom: 24px;
    letter-spacing: -0.5px;
  }
  .location-item {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--gris-borde);
  }
  .location-item:last-of-type { border-bottom: none; }
  .location-item-icon {
    width: 44px;
    height: 44px;
    background: var(--azul-claro);
    color: var(--azul-principal);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .location-item-icon svg { width: 22px; height: 22px; }
  .location-item-content h4 {
    font-size: 14px;
    font-weight: 800;
    color: var(--azul-principal);
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .location-item-content p {
    font-size: 15px;
    color: var(--gris-texto);
    line-height: 1.5;
  }
  .location-item-content a {
    color: var(--accent-cta);
    text-decoration: none;
    font-weight: 700;
  }
  .location-map {
    aspect-ratio: 4/5;
    background: linear-gradient(135deg, var(--azul-claro) 0%, #B8D4F0 100%);
    border-radius: 12px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--azul-principal);
    overflow: hidden;
  }
  .map-embed {
    width: 100%;
    height: 100%;
    border-radius: 12px;
    overflow: hidden;
    display: block;
  }
  .map-embed iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
  }

  /* ========== GALERÍA EVENTOS / CONVENCIONES ========== */
  .events-section {
    padding: 80px 0;
    background: var(--blanco);
  }
  .events-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: 16px;
    height: 600px;
  }
  .event-card {
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, var(--azul-medio) 0%, var(--azul-principal) 100%);
    color: var(--blanco);
    display: flex;
    align-items: flex-end;
    padding: 24px;
    transition: all 0.3s;
    cursor: pointer;
  }
  .event-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,40,86,0.85) 0%, rgba(0,40,86,0.2) 60%, transparent 100%);
    z-index: 1;
  }
  .event-card:hover {
    transform: scale(1.02);
    box-shadow: 0 20px 40px rgba(0,62,126,0.25);
  }
  .event-card:nth-child(1) {
    grid-row: span 2;
    background: linear-gradient(135deg, #003E7E 0%, #0057A8 100%);
  }
  .event-content {
    position: relative;
    z-index: 2;
  }
  .event-tag {
    display: inline-block;
    background: var(--accent-cta);
    color: var(--blanco);
    font-size: 10px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
  }
  .event-card h4 {
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 4px;
    line-height: 1.2;
  }
  .event-card:nth-child(1) h4 { font-size: 24px; }
  .event-card p { font-size: 13px; opacity: 0.9; }
  .event-icon-bg {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.15;
    z-index: 0;
  }
  .event-icon-bg svg {
    width: 80px;
    height: 80px;
    color: var(--blanco);
  }

  /* ========== POR QUÉ NOS ELIGEN ========== */
  .why-section {
    padding: 80px 0;
    background: var(--azul-principal);
    color: var(--blanco);
    position: relative;
    overflow: hidden;
  }
  .why-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
    background-size: 50px 50px;
  }
  .why-section .section-header { position: relative; z-index: 2; }
  .why-section .section-title { color: var(--blanco); }
  .why-section .section-sub { color: rgba(255,255,255,0.85); }
  .why-section .section-eyebrow { color: var(--amarillo-destacado); }
  .why-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    position: relative;
    z-index: 2;
  }
  .why-card {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    padding: 28px;
    backdrop-filter: blur(10px);
    display: flex;
    gap: 20px;
  }
  .why-num {
    font-size: 48px;
    font-weight: 800;
    color: var(--amarillo-destacado);
    line-height: 1;
    flex-shrink: 0;
    letter-spacing: -2px;
  }
  .why-content h4 {
    color: var(--blanco);
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 8px;
  }
  .why-content p {
    color: var(--blanco);
    font-size: 14px;
    line-height: 1.6;
    opacity: 0.9;
  }

  /* ========== CTA FINAL ========== */
  .about-cta-section {
    padding: 80px 0;
    background: var(--blanco);
    text-align: center;
  }
  .about-cta-section h2 {
    font-size: 42px;
    font-weight: 800;
    color: var(--azul-principal);
    letter-spacing: -1px;
    line-height: 1.15;
    margin-bottom: 16px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
  }
  .about-cta-section h2 .accent { color: var(--accent-cta); }
  .about-cta-section p {
    font-size: 17px;
    color: var(--gris-texto);
    margin-bottom: 32px;
    max-width: 620px;
    margin-left: auto;
    margin-right: auto;
  }
  .cta-buttons {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
  }
  .about-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 16px 32px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    text-decoration: none;
    border: none;
    font-family: inherit;
    transition: all 0.2s;
  }
  .about-btn-primary {
    background: var(--accent-cta);
    color: var(--blanco);
    box-shadow: 0 4px 14px rgba(247,148,29,0.4);
  }
  .about-btn-primary:hover {
    background: var(--accent-cta-hover);
    transform: translateY(-1px);
    color: var(--blanco);
    text-decoration: none;
  }
  .about-btn-secondary {
    background: var(--blanco);
    color: var(--azul-principal);
    border: 2px solid var(--azul-principal);
  }
  .about-btn-secondary:hover {
    background: var(--azul-claro);
    color: var(--azul-principal);
    text-decoration: none;
  }

  /* ========== RESPONSIVE ========== */
  @media (max-width: 960px) {
    .intro-grid, .location-grid, .why-grid { grid-template-columns: 1fr; }
    .mvv-grid, .team-grid, .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .events-grid { grid-template-columns: 1fr 1fr; grid-template-rows: auto; height: auto; }
    .events-grid .event-card:nth-child(1) { grid-column: span 2; grid-row: auto; height: 280px; }
    .about-hero h1 { font-size: 36px; }
    .section-title { font-size: 28px; }
  }
  @media (max-width: 560px) {
    .mvv-grid, .team-grid, .stats-grid, .events-grid { grid-template-columns: 1fr; }
    .events-grid .event-card:nth-child(1) { grid-column: auto; height: 240px; }
    .about-hero { padding: 56px 0; }
    .about-hero h1 { font-size: 28px; }
    .stat-num-big { font-size: 42px; }
    .about-cta-section h2 { font-size: 28px; }
  }
</style>
@endpush

@section('content')

<!-- HERO -->
<section class="about-hero">
  <div class="container">
    <div class="about-hero-inner">
      <div class="about-eyebrow">Quiénes somos</div>
      <h1 class="about-hero-title">7+ años respaldando a la <span class="accent">industria mexicana</span></h1>
      <p class="about-hero-sub">
        Más que un distribuidor, somos el aliado técnico que tu planta necesita. Conoce al equipo que está detrás de cada cotización, cada envío y cada solución que entregamos.
      </p>
    </div>
  </div>
</section>

<!-- INTRO QUIÉNES SOMOS -->
<section class="intro-section">
  <div class="container">
    <div class="intro-grid">
      <div class="intro-text">
        <h2>Más que vender equipos, <span class="accent">resolvemos procesos.</span></h2>
        <p>
          <strong>Mac Del Norte</strong> nació en Monterrey con una misión clara: cerrar la brecha entre las marcas líderes de instrumentación industrial del mundo y las plantas mexicanas que dependen de ellas para operar.
        </p>
        <p>
          Como <strong>distribuidor autorizado de Honeywell</strong>, hemos pasado más de 7 años acompañando a ingenieros de mantenimiento, jefes de planta y compradores industriales en sus proyectos más críticos —desde la cotización inicial hasta la puesta en marcha y el soporte post-venta.
        </p>
        <p>
          No somos un catálogo más. Somos el equipo que contesta el WhatsApp un sábado en la noche cuando tu controlador falló, el que envía el datasheet antes que la competencia, y el que se asegura de que tu transmisor llegue calibrado y listo para operar.
        </p>
      </div>

      <div class="intro-image">
        <div class="placeholder-tag">Foto Real</div>
        <div class="placeholder-content">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.66 0-3 1.34-3 3v12c0 1.66 1.34 3 3 3h14c1.66 0 3-1.34 3-3V6c0-1.66-1.34-3-3-3zm-3 7c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm-7 4l3 4 3-4 4 5H5l4-5z"/></svg>
          <p>Foto del equipo de Mac Del Norte<br>en oficinas de Monterrey</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ESTADÍSTICAS / TRAYECTORIA -->
<section class="stats-section">
  <div class="container stats-inner">
    <div class="stats-eyebrow">Nuestra trayectoria en números</div>
    <h3>Resultados que respaldan cada cotización</h3>
    <div class="stats-grid">
      <div class="stat">
        <div class="stat-num-big">7+</div>
        <div class="stat-label-big">Años en el mercado</div>
      </div>
      <div class="stat">
        <div class="stat-num-big">2,500+</div>
        <div class="stat-label-big">Clientes atendidos</div>
      </div>
      <div class="stat">
        <div class="stat-num-big">25</div>
        <div class="stat-label-big">Estados de la república</div>
      </div>
      <div class="stat">
        <div class="stat-num-big">98%</div>
        <div class="stat-label-big">Clientes satisfechos</div>
      </div>
    </div>
  </div>
</section>

<!-- MISIÓN VISIÓN VALORES -->
<section class="mvv-section">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Nuestra filosofía</div>
      <h2 class="section-title">Misión, Visión y Valores</h2>
      <p class="section-sub">Lo que nos mueve cada día y lo que nos compromete con cada cliente que confía en nosotros.</p>
    </div>

    <div class="mvv-grid">

      <div class="mvv-card">
        <div class="mvv-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
        </div>
        <h3>Misión</h3>
        <p>
          Suministrar a la industria mexicana instrumentación y automatización de las marcas líderes del mundo, acompañadas del respaldo técnico, la rapidez y la cercanía que ningún distribuidor genérico puede ofrecer.
        </p>
      </div>

      <div class="mvv-card">
        <div class="mvv-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        <h3>Visión</h3>
        <p>
          Ser reconocidos como el distribuidor de instrumentación industrial más confiable y técnicamente capaz de México, el primero al que llaman los ingenieros de planta cuando un proyecto crítico no puede esperar.
        </p>
      </div>

      <div class="mvv-card">
        <div class="mvv-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
        <h3>Valores</h3>
        <ul class="values-list">
          <li><span><strong>Respaldo técnico:</strong> ingenieros, no solo vendedores.</span></li>
          <li><span><strong>Honestidad:</strong> precio justo, tiempos reales.</span></li>
          <li><span><strong>Rapidez:</strong> cotización en menos de 2 horas.</span></li>
          <li><span><strong>Compromiso:</strong> contigo antes y después de la venta.</span></li>
          <li><span><strong>Calidad:</strong> producto original, garantía de fábrica.</span></li>
        </ul>
      </div>

    </div>
  </div>
</section>

<!-- EQUIPO -->
<section class="team-section">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Nuestro equipo</div>
      <h2 class="section-title">La gente que hace la diferencia</h2>
      <p class="section-sub">Ingenieros, especialistas y un equipo de soporte que vive y respira automatización industrial. Cuando llamas a Mac Del Norte, hablas con personas que conocen tu industria.</p>
    </div>

    <div class="team-grid">

      <div class="team-card">
        <div class="team-photo">
          <div class="placeholder-tag">Foto Real</div>
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>
        <div class="team-info">
          <div class="team-name">[Nombre del Director]</div>
          <div class="team-role">Director General</div>
          <p class="team-specialty">7+ años liderando proyectos industriales en el norte de México.</p>
        </div>
      </div>

      <div class="team-card">
        <div class="team-photo">
          <div class="placeholder-tag">Foto Real</div>
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>
        <div class="team-info">
          <div class="team-name">[Nombre del Ingeniero]</div>
          <div class="team-role">Ingeniero de Aplicación</div>
          <p class="team-specialty">Especialista en transmisores y sistemas de control Honeywell.</p>
        </div>
      </div>

      <div class="team-card">
        <div class="team-photo">
          <div class="placeholder-tag">Foto Real</div>
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>
        <div class="team-info">
          <div class="team-name">[Nombre del Ejecutivo]</div>
          <div class="team-role">Ventas y Cotizaciones</div>
          <p class="team-specialty">Atención técnica-comercial. Tu primer contacto en el equipo.</p>
        </div>
      </div>

      <div class="team-card">
        <div class="team-photo">
          <div class="placeholder-tag">Foto Real</div>
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>
        <div class="team-info">
          <div class="team-name">[Nombre del Coordinador]</div>
          <div class="team-role">Logística y Soporte</div>
          <p class="team-specialty">Coordina envíos, garantías y soporte post-venta.</p>
        </div>
      </div>

    </div>

    <div class="team-cta">
      <strong>¿Quieres conocernos en persona?</strong> Visita nuestras oficinas en Monterrey o agenda una llamada técnica con uno de nuestros ingenieros.
    </div>
  </div>
</section>

<!-- UBICACIÓN -->
<section class="location-section">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Dónde estamos</div>
      <h2 class="section-title">Atendemos desde Monterrey a toda la república</h2>
      <p class="section-sub">Nuestras oficinas y centro de distribución están en el corazón industrial del norte de México.</p>
    </div>

    <div class="location-grid">

      <div class="location-info">
        <h3>Oficina Mac Del Norte</h3>

        <div class="location-item">
          <div class="location-item-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
          </div>
          <div class="location-item-content">
            <h4>Dirección</h4>
            <p>C. Castaño No.718

                Col. Ebanos Norte 2do Sector
                Apodaca N.L. CP.66612</p>
          </div>
        </div>

        <div class="location-item">
          <div class="location-item-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/></svg>
          </div>
          <div class="location-item-content">
            <h4>Teléfonos</h4>
            <p>81-3582-5559<br>81-2473-8768</p>
          </div>
        </div>

        <div class="location-item">
          <div class="location-item-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
          </div>
          <div class="location-item-content">
            <h4>Correo</h4>
            <p><a href="mailto:contacto@macdelnorte.com">contacto@macdelnorte.com</a></p>
          </div>
        </div>

        <div class="location-item">
          <div class="location-item-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
          </div>
          <div class="location-item-content">
            <h4>Horario de atención</h4>
            <p>Lunes a viernes · 9:00 - 18:00<br>Sábados · 9:00 - 13:00</p>
          </div>
        </div>
      </div>

      <div class="location-map">
        <div class="placeholder-tag">Mapa Real</div>
        <div class="map-embed" aria-label="Mapa de ubicación de Mac del Norte">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4256.577954706007!2d-100.2386896883936!3d25.791539977238703!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8662edfface52d03%3A0x694b9c59e1a56759!2sMac%20del%20Norte!5e1!3m2!1ses-419!2smx!4v1778727380707!5m2!1ses-419!2smx" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          <noscript>
            <p>Activa JavaScript para ver el mapa o <a href="https://maps.app.goo.gl/TNtHa1UAiSx8CcLf8" target="_blank" rel="noopener noreferrer">abrir en Google Maps</a>.</p>
          </noscript>
          <p style="margin-top:8px;font-size:13px;text-align:center;"><a href="https://maps.app.goo.gl/TNtHa1UAiSx8CcLf8" target="_blank" rel="noopener noreferrer">Abrir en Google Maps</a></p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- EVENTOS / CONVENCIONES -->
<section class="events-section">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Eventos y capacitaciones</div>
      <h2 class="section-title">Siempre actualizados con la industria</h2>
      <p class="section-sub">Participamos activamente en convenciones, capacitaciones técnicas y eventos del sector. Es nuestra forma de mantenernos al frente de la tecnología que ofrecemos.</p>
    </div>

    <div class="events-grid">

      <div class="event-card">
        <div class="event-icon-bg">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
        </div>
        <div class="placeholder-tag">Foto Real</div>
        <div class="event-content">
          <span class="event-tag">Honeywell Connect</span>
          <h4>Convención anual Honeywell</h4>
          <p>Capacitación oficial en nuevas tecnologías de instrumentación</p>
        </div>
      </div>

      <div class="event-card">
        <div class="event-icon-bg">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.11 0-2 .89-2 2v14c0 1.1.89 2 2 2h14c1.11 0 2-.9 2-2V5c0-1.11-.89-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
        </div>
        <div class="placeholder-tag">Foto Real</div>
        <div class="event-content">
          <span class="event-tag">Expo industrial</span>
          <h4>Expo Manufactura</h4>
          <p>Monterrey 2025</p>
        </div>
      </div>

      <div class="event-card">
        <div class="event-icon-bg">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        </div>
        <div class="placeholder-tag">Foto Real</div>
        <div class="event-content">
          <span class="event-tag">Capacitación</span>
          <h4>Training técnico</h4>
          <p>Equipo certificado en sistemas Honeywell</p>
        </div>
      </div>

      <div class="event-card">
        <div class="event-icon-bg">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
        </div>
        <div class="placeholder-tag">Foto Real</div>
        <div class="event-content">
          <span class="event-tag">Visita técnica</span>
          <h4>En planta de cliente</h4>
          <p>Soporte y puesta en marcha</p>
        </div>
      </div>

      <div class="event-card">
        <div class="event-icon-bg">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-2h2v2zm0-4H7v-2h2v2zm0-4H7V7h2v2zm4 8h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V7h2v2zm4 8h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V7h2v2z"/></svg>
        </div>
        <div class="placeholder-tag">Foto Real</div>
        <div class="event-content">
          <span class="event-tag">Feria regional</span>
          <h4>Industrial Transformation MX</h4>
          <p>León, Guanajuato</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- POR QUÉ NOS ELIGEN -->
<section class="why-section">
  <div class="container">
    <div class="section-header">
      <div class="section-eyebrow">Por qué nos eligen</div>
      <h2 class="section-title">No vendemos productos, vendemos certeza</h2>
      <p class="section-sub">Lo que nos hace diferentes en un mercado lleno de distribuidores genéricos.</p>
    </div>

    <div class="why-grid">

      <div class="why-card">
        <div class="why-num">01</div>
        <div class="why-content">
          <h4>Distribuidor autorizado de fábrica</h4>
          <p>Trabajamos directo con Honeywell. Producto original, con garantía de fábrica y acceso a soporte técnico oficial. Sin intermediarios, sin "equivalentes".</p>
        </div>
      </div>

      <div class="why-card">
        <div class="why-num">02</div>
        <div class="why-content">
          <h4>Atendemos como ingenieros, no como vendedores</h4>
          <p>Cuando preguntas por un transmisor de presión, te respondemos con datasheet, rangos, certificaciones y aplicaciones —no con un PDF genérico.</p>
        </div>
      </div>

      <div class="why-card">
        <div class="why-num">03</div>
        <div class="why-content">
          <h4>Cotización en menos de 2 horas</h4>
          <p>Sabemos que en mantenimiento industrial cada hora cuesta. Por eso nuestro estándar es responder con precio, tiempo de entrega y datasheet en menos de 2 horas hábiles.</p>
        </div>
      </div>

      <div class="why-card">
        <div class="why-num">04</div>
        <div class="why-content">
          <h4>Soporte real después de la venta</h4>
          <p>Te acompañamos en la puesta en marcha, la calibración y la resolución de dudas técnicas. Tu proyecto no termina cuando llega la caja —y el nuestro tampoco.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="about-cta-section">
  <div class="container">
    <h2>¿Listo para trabajar con un <span class="accent">aliado técnico real?</span></h2>
    <p>Cotiza con nuestro equipo, agenda una visita a nuestras oficinas en Monterrey o llámanos directo. Estamos aquí para ayudarte a resolver tu próximo proyecto.</p>
    <div class="cta-buttons">
      <a href="{{ route('contact') }}" class="about-btn about-btn-primary">
        Cotizar con un ingeniero
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="" class="about-btn about-btn-secondary">
        Ver catálogo de productos
      </a>
    </div>
  </div>
</section>

@endsection

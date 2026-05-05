@push('styles')
<style>
  .banner-b2b-section {
    padding: 56px 0;
    background: var(--blanco, #fff);
  }
  .banner-b2b {
    background: linear-gradient(135deg, var(--azul-oscuro, #002856) 0%, var(--azul-principal, #003E7E) 100%);
    color: #fff;
    border-radius: 14px;
    overflow: hidden;
    position: relative;
  }
  .banner-b2b::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
  }
  .banner-b2b::after {
    content: '';
    position: absolute;
    top: -30%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(246,173,28,0.15) 0%, transparent 60%);
    pointer-events: none;
  }
  .banner-b2b-inner {
    padding: 48px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 40px;
    align-items: center;
    position: relative;
    z-index: 2;
  }
  .banner-b2b-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(246,173,28,0.18);
    border: 1px solid rgba(246,173,28,0.4);
    color: var(--amarillo-destacado, #F6AD1C);
    font-size: 11px;
    font-weight: 800;
    padding: 7px 14px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 16px;
  }
  .banner-b2b-eyebrow svg { width: 12px; height: 12px; }
  .banner-b2b h3 {
    font-size: 32px;
    font-weight: 800;
    line-height: 1.15;
    margin-bottom: 14px;
    letter-spacing: -0.8px;
    color: #fff;
  }
  .banner-b2b h3 .accent-yellow {
    color: var(--amarillo-destacado, #F6AD1C);
    font-style: italic;
  }
  .banner-b2b-text {
    font-size: 15px;
    opacity: 0.92;
    line-height: 1.6;
    max-width: 540px;
    margin-bottom: 22px;
  }
  .banner-b2b-perks {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
  }
  .perk-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    opacity: 0.95;
  }
  .perk-item svg {
    width: 18px;
    height: 18px;
    color: var(--amarillo-destacado, #F6AD1C);
    flex-shrink: 0;
  }
  .banner-b2b-cta {
    background: #fff;
    color: var(--azul-principal, #003E7E);
    padding: 32px;
    border-radius: 10px;
    min-width: 280px;
    text-align: center;
  }
  .banner-b2b-cta-num {
    font-size: 56px;
    font-weight: 800;
    color: var(--accent-cta, #F7941D);
    line-height: 1;
    margin-bottom: 4px;
    letter-spacing: -2px;
  }
  .banner-b2b-cta-label {
    font-size: 14px;
    color: var(--gris-texto, #4A5568);
    margin-bottom: 18px;
    font-weight: 600;
    line-height: 1.4;
  }
  .banner-b2b-cta-divider {
    height: 1px;
    background: var(--gris-borde, #DDE3EA);
    margin: 16px 0;
  }
  .banner-b2b-cta-btn {
    background: var(--azul-principal, #003E7E);
    color: #fff;
    width: 100%;
    padding: 14px;
    border-radius: 6px;
    font-weight: 800;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
    text-decoration: none;
    display: block;
  }
  .banner-b2b-cta-btn:hover {
    background: var(--azul-oscuro, #002856);
    color: #fff;
    text-decoration: none;
  }
  .banner-b2b-cta-note {
    font-size: 11px;
    color: var(--gris-claro-texto, #718096);
    margin-top: 10px;
    font-style: italic;
  }

  @media (max-width: 960px) {
    .banner-b2b-inner { grid-template-columns: 1fr; padding: 36px 28px; }
    .banner-b2b h3 { font-size: 24px; }
    .banner-b2b-cta { min-width: 0; }
  }
  @media (max-width: 560px) {
    .banner-b2b-perks { flex-direction: column; gap: 10px; }
    .banner-b2b-inner { padding: 28px 20px; }
  }
</style>
@endpush

<!-- ============ BANNER PROGRAMA B2B / DISTRIBUIDORES ============ -->
<section class="banner-b2b-section">
  <div class="container">

    <div class="banner-b2b">
      <div class="banner-b2b-inner">

        <div>
          <div class="banner-b2b-eyebrow">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 4l7.53 13H4.47L12 6z"/></svg>
            Programa B2B Mac Del Norte
          </div>
          <h3>¿Eres revendedor o técnico? <span class="accent-yellow">Sé nuestro socio</span> y compra al mejor precio.</h3>
          <p class="banner-b2b-text">Accede a precios mayoristas, ingeniero de aplicación dedicado, soporte prioritario y herramientas para que crezcas vendiendo equipo Honeywell, Resideo y McDonnell &amp; Miller con respaldo real.</p>

          <div class="banner-b2b-perks">
            <div class="perk-item">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z"/></svg>
              Precios B2B
            </div>
            <div class="perk-item">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2z"/></svg>
              Crédito a 30 días
            </div>
            <div class="perk-item">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3z"/></svg>
              Ingeniero asignado
            </div>
            <div class="perk-item">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
              Soporte prioritario
            </div>
          </div>
        </div>

        <div class="banner-b2b-cta">
          <div class="banner-b2b-cta-num">+15%</div>
          <div class="banner-b2b-cta-label">Descuento promedio sobre precio público</div>
          <div class="banner-b2b-cta-divider"></div>
          <a href="{{ route('contact') }}" class="banner-b2b-cta-btn">Solicitar acceso B2B</a>
          <div class="banner-b2b-cta-note">Validamos en 24-48h hábiles</div>
        </div>

      </div>
    </div>

  </div>
</section>

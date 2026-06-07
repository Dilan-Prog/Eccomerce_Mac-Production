@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Contacto
@endsection

@push('styles')
<style>
/* ===== VARIABLES (complementan las del master) ===== */
:root {
  --negro-texto: #1A202C;
  --verde-disponible: #2F855A;
  --verde-claro: #F0FDF4;
  --rojo-error: #DC2626;
  --rojo-claro: #FEF2F2;
  --amarillo-claro: #FFFBEB;
  --radius-sm: 4px;
  --radius-md: 6px;
  --radius-lg: 8px;
  --radius-xl: 12px;
  --radius-full: 999px;
  --shadow-sm: 0 2px 4px rgba(0,0,0,0.04);
  --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
  --shadow-lg: 0 12px 28px rgba(0,62,126,0.12);
  --font-base: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  --container-max: 1280px;
}

/* ===== RESET BÁSICO ===== */
*, *::before, *::after { box-sizing: border-box; }

/* ===== CONTAINER ===== */
.mdn-container {
  max-width: var(--container-max);
  margin: 0 auto;
  padding: 0 20px;
}

/* ===== BREADCRUMB ===== */
.breadcrumb-mdn {
  background: var(--blanco);
  padding: 14px 0;
  border-bottom: 1px solid var(--gris-borde);
}
.breadcrumb-list-mdn {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  flex-wrap: wrap;
  list-style: none;
  margin: 0;
  padding: 0;
}
.breadcrumb-list-mdn a {
  color: var(--gris-claro-texto);
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}
.breadcrumb-list-mdn a:hover { color: var(--azul-principal); }
.breadcrumb-separator-mdn { color: var(--gris-borde); font-size: 11px; }
.breadcrumb-current-mdn { color: var(--azul-principal); font-weight: 700; }

/* ===== PAGE HEADER ===== */
.page-header-mdn {
  background: linear-gradient(135deg, var(--azul-oscuro) 0%, var(--azul-principal) 60%, var(--azul-medio) 100%);
  color: var(--blanco);
  padding: 48px 0 40px;
  position: relative;
  overflow: hidden;
}
.page-header-mdn::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
  background-size: 50px 50px;
}
.page-header-content-mdn {
  position: relative;
  z-index: 2;
  text-align: center;
}
.page-eyebrow-mdn {
  display: inline-block;
  background: rgba(246,173,28,0.18);
  border: 1px solid rgba(246,173,28,0.45);
  color: var(--amarillo-destacado);
  font-size: 12px;
  font-weight: 800;
  padding: 7px 14px;
  border-radius: var(--radius-sm);
  text-transform: uppercase;
  letter-spacing: 1.5px;
  margin-bottom: 16px;
}
.page-header-mdn h1 {
  font-size: 32px;
  font-weight: 800;
  color: var(--blanco);
  margin-bottom: 10px;
  letter-spacing: -0.6px;
}
.page-header-sub-mdn {
  font-size: 16px;
  opacity: 0.92;
  max-width: 640px;
  margin: 0 auto;
  color: var(--blanco);
}

/* ===== CONTACT PAGE ===== */
.contact-page-mdn {
  padding: 32px 0 64px;
  background: var(--gris-fondo);
}
.contact-grid-mdn {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 32px;
}

/* ===== CONTACT FORM CARD ===== */
.contact-form-card-mdn {
  background: var(--blanco);
  border-radius: var(--radius-xl);
  border: 1px solid var(--gris-borde);
  padding: 32px;
}
.contact-form-card-mdn h2 {
  font-size: 22px;
  font-weight: 800;
  color: var(--azul-principal);
  margin-bottom: 6px;
}
.contact-form-card-mdn .subtitle-mdn {
  font-size: 14px;
  color: var(--gris-claro-texto);
  margin-bottom: 24px;
}

/* ===== FORM ELEMENTS ===== */
.form-row-mdn {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.form-group-mdn { margin-bottom: 18px; }
.form-group-mdn label {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: var(--azul-principal);
  margin-bottom: 6px;
}
.form-group-mdn .required-mdn { color: var(--rojo-error); margin-left: 2px; }
.form-group-mdn .optional-mdn { color: var(--gris-claro-texto); font-weight: 500; font-size: 12px; margin-left: 4px; }
.form-input-mdn,
.form-select-mdn,
.form-textarea-mdn {
  width: 100%;
  padding: 12px 14px;
  border: 1.5px solid var(--gris-borde);
  border-radius: var(--radius-md);
  font-size: 14px;
  font-family: inherit;
  color: var(--negro-texto);
  background: var(--blanco);
  transition: all 0.2s;
}
.form-input-mdn:focus,
.form-select-mdn:focus,
.form-textarea-mdn:focus {
  outline: none;
  border-color: var(--azul-principal);
  box-shadow: 0 0 0 3px rgba(0,62,126,0.1);
}
.form-textarea-mdn { min-height: 120px; resize: vertical; }
.form-select-mdn {
  cursor: pointer;
  appearance: none;
  padding-right: 38px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23718096' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
}

/* ===== CHECKBOX ===== */
.filter-checkbox-mdn {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 13px;
  color: var(--gris-texto);
  cursor: pointer;
  padding: 4px 0;
  margin-bottom: 18px;
}
.filter-checkbox-mdn input { width: 16px; height: 16px; accent-color: var(--azul-principal); cursor: pointer; flex-shrink: 0; margin-top: 2px; }

/* ===== BTN ===== */
.btn-mdn {
  padding: 12px 22px;
  border-radius: var(--radius-md);
  font-size: 14px;
  font-weight: 800;
  cursor: pointer;
  border: none;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s;
  font-family: inherit;
  white-space: nowrap;
}
.btn-mdn svg { width: 16px; height: 16px; }
.btn-primary-mdn {
  background: var(--accent-cta);
  color: var(--blanco);
  box-shadow: 0 4px 14px rgba(247,148,29,0.4);
}
.btn-primary-mdn:hover {
  background: var(--accent-cta-hover);
  transform: translateY(-1px);
  color: var(--blanco);
}
.btn-block-mdn { width: 100%; }

/* ===== CONTACT INFO ===== */
.contact-info-mdn {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.contact-info-card-mdn {
  background: var(--blanco);
  border-radius: var(--radius-xl);
  border: 1px solid var(--gris-borde);
  padding: 20px;
}
.contact-info-card-mdn.featured-mdn {
  background: linear-gradient(135deg, var(--azul-oscuro) 0%, var(--azul-principal) 100%);
  color: var(--blanco);
  border: none;
}
.contact-info-card-mdn h3 {
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--azul-principal);
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 800;
}
.contact-info-card-mdn h3 svg { width: 18px; height: 18px; }
.contact-info-card-mdn.featured-mdn h3 { color: var(--amarillo-destacado); }
.contact-info-item-mdn {
  display: flex;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid var(--gris-borde);
}
.contact-info-card-mdn.featured-mdn .contact-info-item-mdn { border-color: rgba(255,255,255,0.15); }
.contact-info-item-mdn:last-child { border-bottom: none; }
.contact-info-icon-mdn {
  width: 36px;
  height: 36px;
  background: var(--azul-claro);
  color: var(--azul-principal);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.contact-info-card-mdn.featured-mdn .contact-info-icon-mdn {
  background: rgba(246,173,28,0.18);
  color: var(--amarillo-destacado);
}
.contact-info-icon-mdn svg { width: 16px; height: 16px; }
.contact-info-text-mdn { flex: 1; min-width: 0; }
.contact-info-label-mdn {
  font-size: 11px;
  font-weight: 700;
  color: var(--gris-claro-texto);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 2px;
}
.contact-info-card-mdn.featured-mdn .contact-info-label-mdn { color: rgba(255,255,255,0.7); }
.contact-info-value-mdn { font-size: 14px; font-weight: 600; color: var(--negro-texto); }
.contact-info-card-mdn.featured-mdn .contact-info-value-mdn { color: var(--blanco); }
.contact-info-value-mdn a { color: inherit; text-decoration: none; }
.contact-info-value-mdn a:hover { text-decoration: underline; }

/* ===== CONTACT MAP ===== */
.contact-map-mdn {
  height: 200px;
  background: linear-gradient(135deg, var(--azul-claro) 0%, var(--blanco) 100%);
  border-radius: var(--radius-xl);
  border: 1px solid var(--gris-borde);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--azul-principal);
  position: relative;
  overflow: hidden;
}
.contact-map-mdn::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(0,62,126,0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0,62,126,0.05) 1px, transparent 1px);
  background-size: 30px 30px;
}
.contact-map-pin-mdn {
  position: relative;
  z-index: 2;
  text-align: center;
}
.contact-map-pin-mdn svg { width: 48px; height: 48px; color: var(--accent-cta); margin-bottom: 8px; display: block; margin-left: auto; margin-right: auto; }
.contact-map-pin-mdn strong { display: block; font-size: 14px; color: var(--azul-principal); margin-bottom: 4px; }
.contact-map-pin-mdn span { font-size: 12px; color: var(--gris-claro-texto); }

/* ===== RESPONSIVE ===== */
@media (max-width: 1100px) {
  .contact-grid-mdn { grid-template-columns: 1fr 320px; }
}
@media (max-width: 960px) {
  .contact-grid-mdn { grid-template-columns: 1fr; }
  .contact-form-card-mdn { padding: 24px; }
}
@media (max-width: 720px) {
  .form-row-mdn { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

<div class="breadcrumb-mdn">
  <div class="mdn-container">
    <div class="breadcrumb-list-mdn">
      <a href="{{ route('index') }}">Inicio</a>
      <span class="breadcrumb-separator-mdn">›</span>
      <span class="breadcrumb-current-mdn">Contacto</span>
    </div>
  </div>
</div>

<section class="page-header-mdn">
  <div class="mdn-container">
    <div class="page-header-content-mdn">
      <div class="page-eyebrow-mdn">Hablemos</div>
      <h1>Estamos para servirte</h1>
      <p class="page-header-sub-mdn">Cotizaciones en menos de 2 horas, soporte técnico con ingenieros reales y atención humana de lunes a viernes.</p>
    </div>
  </div>
</section>

<section class="contact-page-mdn">
  <div class="mdn-container">
    <div class="contact-grid-mdn">

      <!-- FORMULARIO -->
      <div class="contact-form-card-mdn">
        <h2>Envíanos un mensaje</h2>
        <p class="subtitle-mdn">Te respondemos en menos de 2 horas hábiles. Si es urgente, escríbenos por WhatsApp.</p>

        <form id="contact-form">

          <div class="form-row-mdn">
            <div class="form-group-mdn">
              <label>Nombre completo <span class="required-mdn">*</span></label>
              <input type="text" class="form-input-mdn" placeholder="Tu nombre" required>
            </div>
            <div class="form-group-mdn">
              <label>Empresa <span class="optional-mdn">(opcional)</span></label>
              <input type="text" class="form-input-mdn" placeholder="Nombre de tu empresa">
            </div>
          </div>

          <div class="form-row-mdn">
            <div class="form-group-mdn">
              <label>Correo electrónico <span class="required-mdn">*</span></label>
              <input type="email" class="form-input-mdn" placeholder="tu@correo.com" required>
            </div>
            <div class="form-group-mdn">
              <label>Teléfono <span class="required-mdn">*</span></label>
              <input type="tel" class="form-input-mdn" placeholder="10 dígitos" required>
            </div>
          </div>

          <div class="form-group-mdn">
            <label>Tipo de consulta <span class="required-mdn">*</span></label>
            <select class="form-select-mdn" required>
              <option value="">Selecciona el tipo de consulta</option>
              <option value="cotizacion">Solicitar cotización</option>
              <option value="soporte">Soporte técnico</option>
              <option value="ventas">Atención comercial</option>
              <option value="b2b">Programa de revendedores B2B</option>
              <option value="garantia">Garantías y devoluciones</option>
              <option value="otro">Otra consulta</option>
            </select>
          </div>

          <div class="form-group-mdn">
            <label>Producto o número de parte <span class="optional-mdn">(opcional)</span></label>
            <input type="text" class="form-input-mdn" placeholder="Ej. STT850-A1H7BB · Si tu consulta es sobre un producto específico">
          </div>

          <div class="form-group-mdn">
            <label>Mensaje <span class="required-mdn">*</span></label>
            <textarea class="form-textarea-mdn" placeholder="Cuéntanos en qué podemos ayudarte. Mientras más detalles nos des, más rápida será la respuesta." required></textarea>
          </div>

          <label class="filter-checkbox-mdn">
            <input type="checkbox" required>
            Acepto el <a href="#" style="color:var(--azul-medio);font-weight:700;">aviso de privacidad</a> y autorizo el uso de mis datos para esta consulta. <span class="required-mdn">*</span>
          </label>

          <button type="submit" class="btn-mdn btn-primary-mdn btn-block-mdn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Enviar mensaje
          </button>
        </form>
      </div>

      <!-- INFO DE CONTACTO -->
      <div class="contact-info-mdn">

        <div class="contact-info-card-mdn featured-mdn">
          <h3>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
            Atención inmediata
          </h3>
          <div class="contact-info-item-mdn">
            <div class="contact-info-icon-mdn">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
            </div>
            <div class="contact-info-text-mdn">
              <div class="contact-info-label-mdn">WhatsApp Business</div>
              <div class="contact-info-value-mdn"><a href="https://wa.me/528135825559">81 3582 5559</a></div>
            </div>
          </div>
          <div class="contact-info-item-mdn">
            <div class="contact-info-icon-mdn">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
            </div>
            <div class="contact-info-text-mdn">
              <div class="contact-info-label-mdn">Soporte directo</div>
              <div class="contact-info-value-mdn"><a href="tel:528124738768">81 2473 8768</a></div>
            </div>
          </div>
        </div>

        <div class="contact-info-card-mdn">
          <h3>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            Por correo
          </h3>
          <div class="contact-info-item-mdn">
            <div class="contact-info-icon-mdn">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            </div>
            <div class="contact-info-text-mdn">
              <div class="contact-info-label-mdn">Cotizaciones y ventas</div>
              <div class="contact-info-value-mdn"><a href="mailto:ventasmty@macdelnorte.com">ventasmty@macdelnorte.com</a></div>
              <div class="contact-info-value-mdn"><a href="mailto:ventas1@macdelnorte.com">ventas1@macdelnorte.com</a></div>
            </div>
          </div>
        </div>

        <div class="contact-info-card-mdn">
          <h3>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            Oficina matriz
          </h3>
          <div class="contact-info-item-mdn">
            <div class="contact-info-icon-mdn">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            </div>
            <div class="contact-info-text-mdn">
              <div class="contact-info-label-mdn">Dirección</div>
              <div class="contact-info-value-mdn">C. Castaño No.718, Col. Ebanos Norte 2do Sector<br>Apodaca N.L. CP.66612</div>
            </div>
          </div>
          <div class="contact-info-item-mdn">
            <div class="contact-info-icon-mdn">
              <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            </div>
            <div class="contact-info-text-mdn">
              <div class="contact-info-label-mdn">Horario</div>
              <div class="contact-info-value-mdn">Lunes a Viernes<br>8:30 AM a 6:00 PM</div>
            </div>
          </div>
        </div>

        <div class="contact-map-mdn">
          <div class="contact-map-pin-mdn">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            <strong>Mac Del Norte</strong>
            <span>Monterrey, N.L.</span>
          </div>
        </div>

      </div>

    </div>
  </div>
</section>

@endsection

@extends('frontend.layouts.master')

{{-- Bolsa de trabajo. Datos en config/empleos.php; número de WhatsApp en config/contact.php.
     Sin formularios: toda postulación o duda abre WhatsApp con el mensaje listo.
     Los enlaces de postulación llevan data-mdn-event (no la clase track-conversion): se miden como
     "job_apply" y NO cuentan como conversión de ventas (ver public/frontend/js/mdn-events.js). --}}

@php
    $urlCanonica = route('empleos');
    $nVacantes = $vacantes->count();
    $nAreas = $areas->filter(fn ($a) => $a['cantidad'] > 0)->count();
    $waNum = config('contact.whatsapp.display');
    $horario = config('contact.hours.es');
    $descripcion = 'Vacantes en Mac del Norte, Monterrey: técnicos en instrumentación, ingeniería de automatización, ventas industriales y prácticas. Postúlate por WhatsApp.';
@endphp

@section('title', 'Empleos | Trabaja en Mac del Norte')

@section('meta_description', $descripcion)

@section('canonical_URL')
    <link rel="canonical" href="{{ $urlCanonica }}">
@endsection

@section('social_meta')
    <meta property="og:type" content="website">
    <meta property="og:title" content="Empleos | Trabaja en Mac del Norte">
    <meta property="og:description" content="{{ $descripcion }}">
    <meta property="og:url" content="{{ $urlCanonica }}">
    <meta property="og:locale" content="es_MX">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Empleos | Trabaja en Mac del Norte">
    <meta name="twitter:description" content="{{ $descripcion }}">
@endsection

@section('meta_robots', 'index, follow')

@push('styles')
<style>
  .mdn-job { --j-blue:#003E7E; --j-blue-dark:#002856; --j-blue-soft:#E6EFF8; --j-accent:#F7941D; --j-accent-hover:#E08416; --j-yellow:#F6AD1C;
             --j-ink:#16202B; --j-muted:#4A5568; --j-line:#DDE3EA; --j-bg:#F5F7FA; --j-wa:#25D366; --j-wa-dark:#1DA851;
             font-family:'Poppins',sans-serif; color:var(--j-ink); width:100%; overflow-x:hidden; }
  .mdn-job *, .mdn-job *::before, .mdn-job *::after { box-sizing:border-box; }
  .mdn-job a { text-decoration:none; }
  .mdn-job h1, .mdn-job h2, .mdn-job h3, .mdn-job p { margin:0; }
  .mdn-job-wrap { max-width:1200px; margin:0 auto; padding:clamp(48px,6vw,88px) clamp(16px,4vw,32px); }
  .mdn-job-sec--bg { background:var(--j-bg); }
  .mdn-job-sec--blue { background:var(--j-blue); color:#fff; }
  .mdn-job-kicker { font-size:11.5px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--j-accent); margin-bottom:10px; }
  .mdn-job-h2 { font-size:clamp(24px,3.2vw,36px); font-weight:800; line-height:1.15; letter-spacing:-.01em; }
  .mdn-job-lead { color:var(--j-muted); font-size:15px; line-height:1.65; max-width:560px; }
  .mdn-job-sec--blue .mdn-job-lead { color:#CFE0F3; }
  .mdn-job-head { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-end; gap:16px 40px; margin-bottom:32px; }

  /* Botones */
  .mdn-job-btn { display:inline-flex; align-items:center; justify-content:center; gap:9px; padding:14px 22px; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; border:2px solid transparent; transition:background .15s, color .15s, border-color .15s; line-height:1.2; text-align:center; font-family:inherit; }
  .mdn-job-btn svg { width:18px; height:18px; flex:none; }
  .mdn-job-btn--wa { background:var(--j-wa); color:#fff; }
  .mdn-job-btn--wa:hover { background:var(--j-wa-dark); color:#fff; }
  .mdn-job-btn--line { background:transparent; color:#fff; border-color:rgba(255,255,255,.55); }
  .mdn-job-btn--line:hover { background:#fff; color:var(--j-blue); }
  .mdn-job-btn--ghost { background:#fff; color:var(--j-blue); border-color:var(--j-line); }
  .mdn-job-btn--ghost:hover { border-color:var(--j-blue); }

  /* Portada */
  .mdn-job-hero { background:linear-gradient(135deg,var(--j-blue-dark) 0%,var(--j-blue) 100%); color:#fff; }
  .mdn-job-hero .mdn-job-wrap { padding-bottom:clamp(40px,5vw,64px); }
  .mdn-job-eyebrow { display:inline-block; font-size:11.5px; font-weight:700; letter-spacing:.14em; background:rgba(255,255,255,.12); padding:7px 14px; border-radius:999px; margin-bottom:18px; }
  .mdn-job-hero h1 { font-size:clamp(30px,5vw,54px); line-height:1.07; font-weight:800; letter-spacing:-.02em; max-width:820px; }
  .mdn-job-hero p { margin-top:18px; max-width:680px; color:#D3E2F4; font-size:clamp(14.5px,1.5vw,16.5px); line-height:1.7; }
  .mdn-job-hero-cta { display:flex; flex-wrap:wrap; gap:12px; margin-top:28px; }
  .mdn-job-search { margin-top:36px; background:#fff; color:var(--j-ink); border-radius:14px; padding:18px; display:grid; gap:14px; grid-template-columns:1fr; box-shadow:0 20px 50px rgba(0,20,60,.3); }
  .mdn-job-search label { display:flex; flex-direction:column; gap:6px; font-size:12px; font-weight:700; color:var(--j-muted); }
  .mdn-job-search input, .mdn-job-search select { height:46px; border:1px solid var(--j-line); border-radius:9px; padding:0 14px; font-size:14px; font-family:inherit; color:var(--j-ink); background:#fff; width:100%; }
  .mdn-job-search input:focus, .mdn-job-search select:focus { outline:2px solid var(--j-blue); outline-offset:0; border-color:var(--j-blue); }
  .mdn-job-search .mdn-job-btn { align-self:end; height:46px; padding:0 20px; background:var(--j-accent); color:#fff; }
  .mdn-job-search .mdn-job-btn:hover { background:var(--j-accent-hover); }

  /* Cifras */
  .mdn-job-stats { background:#fff; border-bottom:1px solid var(--j-line); }
  .mdn-job-stats .mdn-job-wrap { padding-top:28px; padding-bottom:28px; display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }
  .mdn-job-stat b { display:block; font-size:clamp(24px,3vw,32px); font-weight:800; color:var(--j-blue); line-height:1.1; }
  .mdn-job-stat span { font-size:13px; color:var(--j-muted); }

  /* Áreas */
  .mdn-job-areas { display:grid; grid-template-columns:1fr; gap:16px; }
  .mdn-job-area { text-align:left; background:#fff; border:1px solid var(--j-line); border-radius:14px; padding:22px; cursor:pointer; display:flex; flex-direction:column; gap:10px; font-family:inherit; color:inherit; transition:border-color .15s, box-shadow .15s, transform .15s; }
  .mdn-job-area:hover { border-color:var(--j-blue); box-shadow:0 12px 30px rgba(0,62,126,.12); transform:translateY(-2px); }
  .mdn-job-area-top { display:flex; justify-content:space-between; align-items:center; gap:10px; }
  .mdn-job-area-top b { font-size:17px; font-weight:800; }
  .mdn-job-area-count { font-size:11.5px; font-weight:700; background:var(--j-blue-soft); color:var(--j-blue); padding:4px 10px; border-radius:999px; white-space:nowrap; }
  .mdn-job-area p { font-size:13.5px; color:var(--j-muted); line-height:1.6; }
  .mdn-job-area em { font-style:normal; font-size:13px; font-weight:700; color:var(--j-blue); margin-top:auto; }

  /* Vacantes */
  .mdn-job-chips { display:flex; flex-wrap:wrap; gap:8px; }
  .mdn-job-chip { padding:9px 15px; border-radius:999px; font-size:13px; font-weight:600; cursor:pointer; background:#fff; color:#33445E; border:1px solid var(--j-line); font-family:inherit; }
  .mdn-job-chip[aria-pressed="true"] { background:var(--j-blue); color:#fff; border-color:var(--j-blue); }
  .mdn-job-board { display:grid; grid-template-columns:1fr; gap:20px; align-items:start; }
  .mdn-job-list { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:10px; }
  .mdn-job-card { width:100%; text-align:left; background:#fff; border:1px solid var(--j-line); border-radius:12px; padding:16px 18px; cursor:pointer; display:flex; flex-direction:column; gap:4px; font-family:inherit; color:var(--j-ink); }
  .mdn-job-card:hover { border-color:var(--j-blue); }
  .mdn-job-card[aria-pressed="true"] { background:var(--j-blue); border-color:var(--j-blue); color:#fff; box-shadow:0 14px 34px rgba(0,40,86,.22); }
  .mdn-job-card-top { display:flex; justify-content:space-between; gap:10px; align-items:flex-start; }
  .mdn-job-card-top b { font-size:15.5px; font-weight:700; line-height:1.3; }
  .mdn-job-tag { flex:none; font-size:11px; font-weight:700; padding:4px 10px; border-radius:999px; background:var(--j-blue-soft); color:var(--j-blue-dark); }
  .mdn-job-card[aria-pressed="true"] .mdn-job-tag { background:var(--j-yellow); }
  .mdn-job-card small { font-size:12.5px; color:var(--j-muted); }
  .mdn-job-card[aria-pressed="true"] small { color:#C5D8F0; }
  .mdn-job-empty { background:#fff; border:1px dashed var(--j-line); border-radius:12px; padding:24px; font-size:14px; color:var(--j-muted); line-height:1.7; }
  .mdn-job-empty button { background:none; border:0; padding:0; font:inherit; font-weight:700; color:var(--j-blue); cursor:pointer; text-decoration:underline; }
  .mdn-job-detail { background:#fff; border:1px solid var(--j-line); border-radius:14px; overflow:hidden; scroll-margin-top:90px; }
  .mdn-job-detail[hidden] { display:none; }
  .mdn-job-detail-head { background:var(--j-blue); color:#fff; padding:24px; }
  .mdn-job-detail-head small { font-size:11.5px; font-weight:700; letter-spacing:.12em; color:var(--j-yellow); }
  .mdn-job-detail-head h3 { font-size:clamp(20px,2.4vw,26px); font-weight:800; line-height:1.2; margin-top:6px; }
  .mdn-job-sueldo { display:inline-block; margin-top:12px; font-size:13px; font-weight:700; background:rgba(255,255,255,.14); padding:6px 12px; border-radius:8px; }
  .mdn-job-facts { display:grid; grid-template-columns:repeat(2,1fr); gap:14px 18px; margin-top:18px; }
  .mdn-job-facts dt { font-size:11px; letter-spacing:.08em; text-transform:uppercase; color:#A8C4E6; }
  .mdn-job-facts dd { margin:2px 0 0; font-size:13.5px; font-weight:600; }
  .mdn-job-detail-body { padding:24px; display:flex; flex-direction:column; gap:22px; }
  .mdn-job-detail-body > p { font-size:14.5px; line-height:1.7; color:var(--j-muted); }
  .mdn-job-cols { display:grid; grid-template-columns:1fr; gap:22px; }
  .mdn-job-detail h4 { margin:0 0 10px; font-size:11.5px; font-weight:700; letter-spacing:.12em; color:var(--j-blue); text-transform:uppercase; }
  .mdn-job-detail ul { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:8px; }
  .mdn-job-detail li { display:flex; gap:9px; font-size:13.5px; line-height:1.5; }
  .mdn-job-detail li::before { content:'✓'; color:var(--j-accent); font-weight:800; flex:none; }
  .mdn-job-detail .mdn-job-req li::before { content:'—'; color:var(--j-muted); }
  .mdn-job-detail .mdn-job-plus li::before { content:'+'; color:var(--j-blue); }
  .mdn-job-actions { display:flex; flex-wrap:wrap; gap:10px; align-items:center; padding-top:18px; border-top:1px solid var(--j-line); }
  .mdn-job-share { background:none; border:0; font:inherit; font-size:13px; font-weight:600; color:var(--j-blue); cursor:pointer; padding:8px 4px; margin-left:auto; }

  /* Carrera y pasos */
  .mdn-job-steps { display:grid; grid-template-columns:1fr; gap:14px; }
  .mdn-job-step { background:#fff; border:1px solid var(--j-line); border-radius:14px; padding:20px; display:flex; flex-direction:column; gap:8px; }
  .mdn-job-sec--bg .mdn-job-step { background:#fff; }
  .mdn-job-step-top { display:flex; justify-content:space-between; align-items:center; gap:10px; }
  .mdn-job-step-top b { font-size:22px; font-weight:800; color:var(--j-accent); }
  .mdn-job-step-top span { font-size:11.5px; font-weight:700; color:var(--j-muted); }
  .mdn-job-step h3 { font-size:16px; font-weight:800; }
  .mdn-job-step p { font-size:13.5px; line-height:1.6; color:var(--j-muted); }
  .mdn-job-skills { display:flex; flex-wrap:wrap; gap:6px; margin-top:4px; }
  .mdn-job-skills span { font-size:11px; font-weight:600; background:var(--j-blue-soft); color:var(--j-blue-dark); padding:4px 9px; border-radius:6px; }

  /* Beneficios */
  .mdn-job-ben { display:grid; grid-template-columns:1fr; gap:28px; }
  .mdn-job-ben-groups { display:grid; gap:16px; }
  .mdn-job-ben-group { background:#fff; border:1px solid var(--j-line); border-radius:14px; padding:20px 22px; }
  .mdn-job-ben-group > b { display:block; font-size:11.5px; letter-spacing:.14em; text-transform:uppercase; color:var(--j-accent); margin-bottom:12px; }
  .mdn-job-ben-item { padding:10px 0; border-top:1px solid #EEF1F5; }
  .mdn-job-ben-item:first-of-type { border-top:0; padding-top:0; }
  .mdn-job-ben-item strong { display:block; font-size:14.5px; }
  .mdn-job-ben-item span { font-size:13px; color:var(--j-muted); line-height:1.55; }

  /* Documentos */
  .mdn-job-docs { margin-top:28px; background:var(--j-blue-soft); border-radius:14px; padding:22px; display:flex; flex-direction:column; gap:14px; }
  .mdn-job-docs b { font-size:15px; }
  .mdn-job-docs p { font-size:13px; color:var(--j-muted); margin-top:2px; }
  .mdn-job-docs-list { display:flex; flex-wrap:wrap; gap:8px; }
  .mdn-job-docs-list span { background:#fff; border-radius:999px; padding:7px 13px; font-size:12.5px; font-weight:600; color:var(--j-blue-dark); }

  /* Postular */
  .mdn-job-apply { display:grid; grid-template-columns:1fr; gap:32px; align-items:center; }
  .mdn-job-apply-steps { display:grid; gap:12px; }
  .mdn-job-apply-step { display:flex; gap:14px; align-items:flex-start; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.18); border-radius:12px; padding:16px 18px; }
  .mdn-job-apply-step b { flex:none; width:32px; height:32px; border-radius:50%; background:var(--j-yellow); color:var(--j-blue-dark); display:flex; align-items:center; justify-content:center; font-weight:800; }
  .mdn-job-apply-step span { font-size:14px; line-height:1.55; color:#E6EFF8; }
  .mdn-job-apply-step strong { display:block; color:#fff; font-size:15px; }
  .mdn-job-apply-note { margin-top:18px; font-size:13px; color:#CFE0F3; line-height:1.7; }

  /* FAQ */
  .mdn-job-faq { max-width:820px; margin:0 auto; }
  .mdn-job-faq details { border-bottom:1px solid var(--j-line); }
  .mdn-job-faq summary { list-style:none; cursor:pointer; display:flex; justify-content:space-between; gap:16px; align-items:center; padding:18px 4px; font-size:15.5px; font-weight:700; }
  .mdn-job-faq summary::-webkit-details-marker { display:none; }
  .mdn-job-faq summary::after { content:'+'; font-size:22px; color:var(--j-blue); flex:none; line-height:1; }
  .mdn-job-faq details[open] summary::after { content:'–'; }
  .mdn-job-faq details p { padding:0 4px 18px; font-size:14px; line-height:1.7; color:var(--j-muted); }
  .mdn-job-aviso { max-width:820px; margin:28px auto 0; font-size:12.5px; line-height:1.7; color:var(--j-muted); text-align:center; }

  /* CTA final */
  .mdn-job-cta { background:var(--j-blue-dark); color:#fff; }
  .mdn-job-cta .mdn-job-wrap { padding-top:36px; padding-bottom:36px; display:flex; flex-wrap:wrap; gap:18px 32px; align-items:center; justify-content:space-between; }
  .mdn-job-cta b { display:block; font-size:clamp(18px,2.2vw,24px); }
  .mdn-job-cta span { font-size:13.5px; color:#C5D8F0; }

  @media (min-width:640px) {
    .mdn-job-search { grid-template-columns:1.6fr 1fr 1fr auto; align-items:end; }
    .mdn-job-stats .mdn-job-wrap { grid-template-columns:repeat(4,1fr); }
    .mdn-job-areas { grid-template-columns:repeat(2,1fr); }
    .mdn-job-steps { grid-template-columns:repeat(2,1fr); }
    .mdn-job-cols { grid-template-columns:1fr 1fr; }
    .mdn-job-docs { flex-direction:row; align-items:center; justify-content:space-between; }
  }
  @media (min-width:960px) {
    .mdn-job-areas { grid-template-columns:repeat(4,1fr); }
    .mdn-job-steps { grid-template-columns:repeat(5,1fr); }
    .mdn-job-board { grid-template-columns:minmax(300px,.8fr) 1.5fr; }
    .mdn-job-list { position:sticky; top:96px; max-height:calc(100vh - 120px); overflow:auto; padding-right:4px; }
    .mdn-job-ben { grid-template-columns:.8fr 1.4fr; }
    .mdn-job-apply { grid-template-columns:1fr 1fr; }
  }
</style>
@endpush

@section('content')
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <symbol id="mdn-job-wa" viewBox="0 0 24 24"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></symbol>
</svg>

<main class="mdn-job" id="mdn-job-app">

  {{-- PORTADA --}}
  <section class="mdn-job-hero">
    <div class="mdn-job-wrap">
      <div class="mdn-job-eyebrow">BOLSA DE TRABAJO · MONTERREY, N.L.</div>
      <h1>Construye tu carrera en instrumentación y control industrial</h1>
      <p>Más de 30 años instalando y configurando los equipos que mantienen operando a la industria del noreste. Buscamos personas que quieran aprender, crecer y resolver problemas reales en planta.</p>
      <div class="mdn-job-hero-cta">
        <a href="#vacantes" class="mdn-job-btn mdn-job-btn--ghost"><span>Ver <span data-job-count>{{ $nVacantes }}</span> vacantes</span></a>
        <a href="{{ $waGeneral }}" target="_blank" rel="noopener" class="mdn-job-btn mdn-job-btn--wa"
           data-mdn-event="job_apply" data-mdn-ref="GENERAL" data-mdn-puesto="Cartera general" data-mdn-area="General">
          <svg aria-hidden="true"><use href="#mdn-job-wa"/></svg> Enviar mi CV por WhatsApp
        </a>
      </div>

      <div class="mdn-job-search" role="search">
        <label>Puesto o palabra clave
          <input type="search" id="mdn-job-q" placeholder="Ej. técnico, PLC, ventas" autocomplete="off">
        </label>
        <label>Área
          <select id="mdn-job-area">
            <option value="Todas">Todas</option>
            @foreach ($areas as $a)
              <option value="{{ $a['nombre'] }}">{{ $a['nombre'] }}</option>
            @endforeach
          </select>
        </label>
        <label>Modalidad
          <select id="mdn-job-mod">
            <option value="Todas">Todas</option>
            @foreach ($modalidades as $m)
              <option value="{{ $m }}">{{ $m }}</option>
            @endforeach
          </select>
        </label>
        <a href="#vacantes" class="mdn-job-btn"><span>Buscar · <span data-job-count>{{ $nVacantes }}</span> resultados</span></a>
      </div>
    </div>
  </section>

  {{-- CIFRAS --}}
  <section class="mdn-job-stats">
    <div class="mdn-job-wrap">
      <div class="mdn-job-stat"><b>+30</b><span>años en la industria</span></div>
      <div class="mdn-job-stat"><b>{{ $nVacantes }}</b><span>vacantes abiertas</span></div>
      <div class="mdn-job-stat"><b>{{ $nAreas }}</b><span>áreas de trabajo</span></div>
      <div class="mdn-job-stat"><b>5 días</b><span>tiempo máximo de respuesta</span></div>
    </div>
  </section>

  {{-- ÁREAS --}}
  <section class="mdn-job-sec--bg">
    <div class="mdn-job-wrap">
      <div class="mdn-job-head">
        <div><div class="mdn-job-kicker">Áreas de trabajo</div><h2 class="mdn-job-h2">¿Dónde encaja tu talento?</h2></div>
        <p class="mdn-job-lead">Equipos que trabajan juntos para que cada instalación llegue a tiempo y funcionando.</p>
      </div>
      <div class="mdn-job-areas">
        @foreach ($areas as $a)
          <button type="button" class="mdn-job-area" data-area-card="{{ $a['nombre'] }}">
            <span class="mdn-job-area-top"><b>{{ $a['nombre'] }}</b><span class="mdn-job-area-count">{{ $a['cantidad'] }} {{ $a['cantidad'] === 1 ? 'vacante' : 'vacantes' }}</span></span>
            <p>{{ $a['descripcion'] }}</p>
            <em>Ver vacantes →</em>
          </button>
        @endforeach
      </div>
    </div>
  </section>

  {{-- VACANTES --}}
  <section id="vacantes">
    <div class="mdn-job-wrap">
      <div class="mdn-job-head">
        <div><div class="mdn-job-kicker">Vacantes abiertas</div><h2 class="mdn-job-h2"><span data-job-count>{{ $nVacantes }}</span> posiciones disponibles</h2></div>
        <div class="mdn-job-chips" role="group" aria-label="Filtrar por área">
          <button type="button" class="mdn-job-chip" data-chip="Todas" aria-pressed="true">Todas</button>
          @foreach ($areas as $a)
            <button type="button" class="mdn-job-chip" data-chip="{{ $a['nombre'] }}" aria-pressed="false">{{ $a['nombre'] }}</button>
          @endforeach
        </div>
      </div>

      @if ($vacantes->isEmpty())
        <div class="mdn-job-empty">
          Por ahora no tenemos vacantes abiertas, pero guardamos tu CV en la cartera general y te contactamos cuando se abra una posición afín.
          <br><br>
          <a href="{{ $waGeneral }}" target="_blank" rel="noopener" class="mdn-job-btn mdn-job-btn--wa"
             data-mdn-event="job_apply" data-mdn-ref="GENERAL" data-mdn-puesto="Cartera general" data-mdn-area="General">
            <svg aria-hidden="true"><use href="#mdn-job-wa"/></svg> Enviar mi CV por WhatsApp
          </a>
        </div>
      @else
        <div class="mdn-job-board">
          <div>
            <ul class="mdn-job-list" id="mdn-job-list">
              @foreach ($vacantes as $v)
                <li data-job-item
                    data-ref="{{ $v['ref'] }}" data-area="{{ $v['area'] }}" data-mod="{{ $v['modalidad'] }}"
                    data-text="{{ $v['titulo'] }} {{ $v['descripcion'] }} {{ $v['area'] }} {{ $v['modalidad'] }}">
                  <button type="button" class="mdn-job-card" data-open="{{ $v['ref'] }}" aria-pressed="false">
                    <span class="mdn-job-card-top"><b>{{ $v['titulo'] }}</b><span class="mdn-job-tag">{{ $v['modalidad'] }}</span></span>
                    <small>{{ $v['area'] }} · {{ $v['horario'] }}</small>
                    <small>Sueldo a tratar @if ($v['fecha']) · Publicada el {{ $v['fecha'] }} @endif</small>
                  </button>
                </li>
              @endforeach
            </ul>
            <div class="mdn-job-empty" id="mdn-job-none" hidden>
              No hay vacantes con esos filtros. <button type="button" id="mdn-job-clear">Limpiar filtros</button> o
              <a href="{{ $waGeneral }}" target="_blank" rel="noopener"
                 data-mdn-event="job_apply" data-mdn-ref="GENERAL" data-mdn-puesto="Cartera general" data-mdn-area="General"
                 style="font-weight:700;color:var(--j-blue);text-decoration:underline">envía tu CV a cartera general por WhatsApp</a>.
            </div>
          </div>

          <div id="mdn-job-details">
            @foreach ($vacantes as $v)
              <article class="mdn-job-detail" id="vacante-{{ $v['ref'] }}" data-detail="{{ $v['ref'] }}" hidden>
                <div class="mdn-job-detail-head">
                  <small>{{ strtoupper($v['area']) }} · REF. {{ $v['ref'] }}</small>
                  <h3>{{ $v['titulo'] }}</h3>
                  <span class="mdn-job-sueldo">Sueldo a tratar</span>
                  <dl class="mdn-job-facts">
                    <div><dt>Ubicación</dt><dd>Monterrey, N.L.</dd></div>
                    <div><dt>Modalidad</dt><dd>{{ $v['modalidad'] }}</dd></div>
                    <div><dt>Horario</dt><dd>{{ $v['horario'] }}</dd></div>
                    <div><dt>Experiencia</dt><dd>{{ $v['experiencia'] }}</dd></div>
                    <div><dt>Escolaridad</dt><dd>{{ $v['escolaridad'] }}</dd></div>
                  </dl>
                </div>
                <div class="mdn-job-detail-body">
                  <p>{{ $v['descripcion'] }}</p>
                  <div class="mdn-job-cols">
                    <div><h4>Responsabilidades</h4><ul>@foreach ($v['responsabilidades'] as $r)<li>{{ $r }}</li>@endforeach</ul></div>
                    <div class="mdn-job-req"><h4>Requisitos</h4><ul>@foreach ($v['requisitos'] as $r)<li>{{ $r }}</li>@endforeach</ul></div>
                  </div>
                  <div class="mdn-job-cols">
                    @if (!empty($v['deseable']))
                      <div class="mdn-job-plus"><h4>Deseable</h4><ul>@foreach ($v['deseable'] as $r)<li>{{ $r }}</li>@endforeach</ul></div>
                    @endif
                    <div><h4>Te ofrecemos</h4><ul>@foreach ($v['ofrecemos'] as $r)<li>{{ $r }}</li>@endforeach</ul></div>
                  </div>
                  <div class="mdn-job-actions">
                    <a href="{{ $v['wa_postular'] }}" target="_blank" rel="noopener" class="mdn-job-btn mdn-job-btn--wa"
                       data-mdn-event="job_apply" data-mdn-ref="{{ $v['ref'] }}" data-mdn-puesto="{{ $v['titulo'] }}" data-mdn-area="{{ $v['area'] }}">
                      <svg aria-hidden="true"><use href="#mdn-job-wa"/></svg> Postularme por WhatsApp
                    </a>
                    <a href="{{ $v['wa_duda'] }}" target="_blank" rel="noopener" class="mdn-job-btn mdn-job-btn--ghost"
                       data-mdn-event="job_question" data-mdn-ref="{{ $v['ref'] }}" data-mdn-puesto="{{ $v['titulo'] }}" data-mdn-area="{{ $v['area'] }}">
                      Preguntar por esta vacante
                    </a>
                    <button type="button" class="mdn-job-share" data-share="{{ $v['ref'] }}">Compartir vacante</button>
                  </div>
                </div>
              </article>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </section>

  {{-- PLAN DE CARRERA --}}
  <section class="mdn-job-sec--bg">
    <div class="mdn-job-wrap">
      <div class="mdn-job-head">
        <div><div class="mdn-job-kicker">Plan de carrera</div><h2 class="mdn-job-h2">De practicante a líder de proyecto</h2></div>
        <p class="mdn-job-lead">Una ruta clara en el área técnica, con capacitación y evaluación en cada etapa.</p>
      </div>
      <div class="mdn-job-steps">
        @foreach ($cfg['carrera'] as $c)
          <div class="mdn-job-step">
            <div class="mdn-job-step-top"><b>{{ $c['n'] }}</b><span>{{ $c['tiempo'] }}</span></div>
            <h3>{{ $c['titulo'] }}</h3>
            <p>{{ $c['descripcion'] }}</p>
            <div class="mdn-job-skills">@foreach ($c['habilidades'] as $h)<span>{{ $h }}</span>@endforeach</div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- BENEFICIOS --}}
  <section>
    <div class="mdn-job-wrap">
      <div class="mdn-job-ben">
        <div>
          <div class="mdn-job-kicker">Beneficios</div>
          <h2 class="mdn-job-h2" style="margin-bottom:14px">Lo que recibes por formar parte del equipo</h2>
          <p class="mdn-job-lead">Prestaciones, formación y herramientas para que hagas bien tu trabajo y sigas creciendo.</p>
        </div>
        <div class="mdn-job-ben-groups">
          @foreach ($cfg['beneficios'] as $g)
            <div class="mdn-job-ben-group">
              <b>{{ $g['titulo'] }}</b>
              @foreach ($g['items'] as $i)
                <div class="mdn-job-ben-item"><strong>{{ $i['t'] }}</strong><span>{{ $i['d'] }}</span></div>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- PROCESO --}}
  <section class="mdn-job-sec--bg">
    <div class="mdn-job-wrap">
      <div class="mdn-job-head">
        <div><div class="mdn-job-kicker">Proceso de selección</div><h2 class="mdn-job-h2">Transparente de principio a fin</h2></div>
        <p class="mdn-job-lead">Te avisamos en cada etapa, también si el perfil no coincide.</p>
      </div>
      <div class="mdn-job-steps">
        @foreach ($cfg['proceso'] as $p)
          <div class="mdn-job-step">
            <div class="mdn-job-step-top"><b>{{ $p['n'] }}</b><span>{{ $p['tiempo'] }}</span></div>
            <h3>{{ $p['titulo'] }}</h3>
            <p>{{ $p['descripcion'] }}</p>
          </div>
        @endforeach
      </div>
      <div class="mdn-job-docs">
        <div><b>Documentos para tu ingreso</b><p>Tenlos listos para agilizar la contratación.</p></div>
        <div class="mdn-job-docs-list">@foreach ($cfg['documentos'] as $d)<span>{{ $d }}</span>@endforeach</div>
      </div>
    </div>
  </section>

  {{-- POSTULAR (solo WhatsApp) --}}
  <section id="postular" class="mdn-job-sec--blue">
    <div class="mdn-job-wrap">
      <div class="mdn-job-apply">
        <div>
          <div class="mdn-job-kicker" style="color:var(--j-yellow)">Postúlate</div>
          <h2 class="mdn-job-h2">Envía tu postulación por WhatsApp en 3 pasos</h2>
          <p class="mdn-job-lead" style="margin-top:14px">Sin formularios. Si no encuentras una vacante para tu perfil, usa la cartera general: guardamos tu CV y te contactamos cuando se abra una posición afín.</p>
          <div style="margin-top:26px">
            <a href="{{ $waGeneral }}" target="_blank" rel="noopener" class="mdn-job-btn mdn-job-btn--wa"
               data-mdn-event="job_apply" data-mdn-ref="GENERAL" data-mdn-puesto="Cartera general" data-mdn-area="General">
              <svg aria-hidden="true"><use href="#mdn-job-wa"/></svg> Enviar mi CV por WhatsApp
            </a>
          </div>
          <p class="mdn-job-apply-note">WhatsApp {{ $waNum }} · {{ $horario }}</p>
        </div>
        <div class="mdn-job-apply-steps">
          <div class="mdn-job-apply-step"><b>1</b><span><strong>Elige la vacante</strong>Busca en la lista o entra directo a la cartera general.</span></div>
          <div class="mdn-job-apply-step"><b>2</b><span><strong>Toca "Postularme por WhatsApp"</strong>Se abre el chat con el mensaje ya escrito con el puesto y la referencia.</span></div>
          <div class="mdn-job-apply-step"><b>3</b><span><strong>Envía tu CV en el chat</strong>Adjunta tu CV (PDF, Word o foto legible) y tu nombre completo. Te respondemos en máximo 5 días hábiles.</span></div>
        </div>
      </div>
    </div>
  </section>

  {{-- FAQ --}}
  <section>
    <div class="mdn-job-wrap">
      <div class="mdn-job-head" style="justify-content:center;text-align:center">
        <div><div class="mdn-job-kicker">Preguntas frecuentes</div><h2 class="mdn-job-h2">Resolvemos tus dudas</h2></div>
      </div>
      <div class="mdn-job-faq">
        @foreach ($cfg['faq'] as $f)
          <details>
            <summary>{{ $f['q'] }}</summary>
            <p>{{ $f['a'] }}</p>
          </details>
        @endforeach
      </div>
      @if (!empty($cfg['aviso']))
        <p class="mdn-job-aviso">{{ $cfg['aviso'] }}</p>
      @endif
    </div>
  </section>

  {{-- CTA --}}
  <section class="mdn-job-cta">
    <div class="mdn-job-wrap">
      <div><b>¿Tienes dudas sobre alguna vacante?</b><span>WhatsApp {{ $waNum }} · {{ $horario }}</span></div>
      <a href="{{ $waGeneral }}" target="_blank" rel="noopener" class="mdn-job-btn mdn-job-btn--wa"
         data-mdn-event="job_question" data-mdn-ref="GENERAL" data-mdn-puesto="Cartera general" data-mdn-area="General">
        <svg aria-hidden="true"><use href="#mdn-job-wa"/></svg> Escribir por WhatsApp
      </a>
    </div>
  </section>

</main>
@endsection

@push('scripts')
<script>
(function () {
  var root = document.getElementById('mdn-job-app');
  if (!root) return;

  var items = [].slice.call(root.querySelectorAll('[data-job-item]'));
  var cards = [].slice.call(root.querySelectorAll('.mdn-job-card'));
  var details = [].slice.call(root.querySelectorAll('[data-detail]'));
  var chips = [].slice.call(root.querySelectorAll('[data-chip]'));
  var counters = [].slice.call(root.querySelectorAll('[data-job-count]'));
  var qEl = document.getElementById('mdn-job-q');
  var areaEl = document.getElementById('mdn-job-area');
  var modEl = document.getElementById('mdn-job-mod');
  var noneEl = document.getElementById('mdn-job-none');
  var state = { area: 'Todas', mod: 'Todas', q: '', sel: null };

  function norm(s) {
    return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
  }

  // writeHash solo cuando la persona elige una vacante: la URL no debe cambiar sola al cargar la pagina.
  function show(ref, scroll, writeHash) {
    state.sel = ref;
    details.forEach(function (d) { d.hidden = d.getAttribute('data-detail') !== ref; });
    cards.forEach(function (c) { c.setAttribute('aria-pressed', c.getAttribute('data-open') === ref ? 'true' : 'false'); });
    if (writeHash) { try { history.replaceState(null, '', '#vacante-' + ref); } catch (e) {} }
    if (scroll && window.matchMedia('(max-width: 959px)').matches) {
      var el = document.getElementById('vacante-' + ref);
      if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function apply() {
    var q = norm(state.q).trim();
    var visible = [];
    items.forEach(function (li) {
      var ok = (state.area === 'Todas' || li.getAttribute('data-area') === state.area)
        && (state.mod === 'Todas' || li.getAttribute('data-mod') === state.mod)
        && (!q || norm(li.getAttribute('data-text')).indexOf(q) !== -1);
      li.hidden = !ok;
      if (ok) visible.push(li.getAttribute('data-ref'));
    });
    counters.forEach(function (c) { c.textContent = visible.length; });
    chips.forEach(function (c) { c.setAttribute('aria-pressed', c.getAttribute('data-chip') === state.area ? 'true' : 'false'); });
    if (areaEl) areaEl.value = state.area;
    if (modEl) modEl.value = state.mod;
    if (noneEl) noneEl.hidden = visible.length > 0;
    if (!visible.length) { details.forEach(function (d) { d.hidden = true; }); state.sel = null; return; }
    if (!state.sel || visible.indexOf(state.sel) === -1) show(visible[0], false, false);
  }

  if (qEl) {
    qEl.addEventListener('input', function () { state.q = qEl.value; apply(); });
    qEl.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); location.hash = 'vacantes'; } });
  }
  if (areaEl) areaEl.addEventListener('change', function () { state.area = areaEl.value; apply(); });
  if (modEl) modEl.addEventListener('change', function () { state.mod = modEl.value; apply(); });
  chips.forEach(function (c) { c.addEventListener('click', function () { state.area = c.getAttribute('data-chip'); apply(); }); });
  cards.forEach(function (c) { c.addEventListener('click', function () { show(c.getAttribute('data-open'), true, true); }); });

  [].slice.call(root.querySelectorAll('[data-area-card]')).forEach(function (b) {
    b.addEventListener('click', function () {
      state.area = b.getAttribute('data-area-card'); state.mod = 'Todas'; state.q = '';
      if (qEl) qEl.value = '';
      apply();
      var sec = document.getElementById('vacantes');
      if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  var clear = document.getElementById('mdn-job-clear');
  if (clear) clear.addEventListener('click', function () {
    state.area = 'Todas'; state.mod = 'Todas'; state.q = '';
    if (qEl) qEl.value = '';
    apply();
  });

  [].slice.call(root.querySelectorAll('[data-share]')).forEach(function (b) {
    b.addEventListener('click', function () {
      var url = location.origin + location.pathname + '#vacante-' + b.getAttribute('data-share');
      var done = function () { var old = b.textContent; b.textContent = 'Enlace copiado ✓'; setTimeout(function () { b.textContent = old; }, 2000); };
      if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(url).then(done, function () {});
      else { try { window.prompt('Copia este enlace:', url); } catch (e) {} }
    });
  });

  // Enlace directo: /empleos#vacante-ST-01
  var m = /^#vacante-(.+)$/.exec(decodeURIComponent(location.hash || ''));
  apply();
  if (m && items.some(function (li) { return li.getAttribute('data-ref') === m[1]; })) {
    show(m[1], false, false);
    var target = document.getElementById('vacante-' + m[1]);
    if (target) setTimeout(function () { target.scrollIntoView({ block: 'start' }); }, 50);
  }
})();
</script>
@endpush

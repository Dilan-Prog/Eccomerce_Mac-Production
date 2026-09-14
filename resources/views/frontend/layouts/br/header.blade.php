{{-- Cabecera de la sección Brasil. Sin catálogo, sin carrito, sin precios:
     el único camino de conversión es WhatsApp. --}}
@php
    $br = config('brasil');
@endphp

@push('styles')
<style>
    .brh-top { background: var(--azul-oscuro, #002856); color: #fff; font-size: 13px; }
    .brh-top-inner { max-width: 1240px; margin: 0 auto; padding: 8px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .brh-top a { color: #fff; text-decoration: none; }
    .brh-top a:hover { text-decoration: underline; }
    .brh-top-list { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
    .brh-top-list svg { width: 14px; height: 14px; vertical-align: -2px; margin-right: 5px; fill: currentColor; }
    .brh-lang { display: inline-flex; align-items: center; gap: 6px; border: 1px solid rgba(255,255,255,.35); border-radius: 999px; padding: 3px 11px; font-size: 12px; }

    .brh-main { background: #fff; border-bottom: 1px solid var(--gris-borde, #DDE3EA); position: sticky; top: 0; z-index: 900; box-shadow: 0 1px 3px rgba(0,40,86,.06); }
    .brh-main-inner { max-width: 1240px; margin: 0 auto; padding: 12px 20px; display: flex; align-items: center; gap: 28px; }
    .brh-logo img { height: 48px; width: auto; display: block; }
    .brh-nav { display: flex; align-items: center; gap: 26px; margin-left: auto; }
    .brh-nav a { font-size: 14.5px; font-weight: 600; color: var(--azul-principal, #003E7E); text-decoration: none; }
    .brh-nav a:hover { color: var(--azul-medio, #0057A8); }
    .brh-wa { display: inline-flex; align-items: center; gap: 8px; background: #25D366; color: #fff !important; font-weight: 700; font-size: 14.5px; padding: 11px 18px; border-radius: 999px; box-shadow: 0 4px 14px rgba(37,211,102,.35); transition: transform .15s; }
    .brh-wa:hover { transform: translateY(-1px); color: #fff; }
    .brh-wa svg { width: 18px; height: 18px; fill: currentColor; }

    .brh-dd { position: relative; }
    .brh-dd-btn { display: inline-flex; align-items: center; gap: 4px; }
    .brh-dd-menu { position: absolute; top: calc(100% + 10px); left: 0; min-width: 320px; background: #fff; border: 1px solid var(--gris-borde, #DDE3EA); border-radius: 8px; box-shadow: 0 10px 30px rgba(0,40,86,.14); padding: 8px; display: none; z-index: 950; }
    .brh-dd:hover .brh-dd-menu, .brh-dd:focus-within .brh-dd-menu { display: grid; }
    .brh-dd-menu a { display: grid; gap: 2px; padding: 10px 12px; border-radius: 6px; font-size: 14px; font-weight: 600; color: var(--azul-oscuro, #002856); }
    .brh-dd-menu a:hover, .brh-dd-menu a.is-active { background: var(--azul-claro, #E6EFF8); color: var(--azul-principal, #003E7E); }
    .brh-dd-marca { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--gris-claro-texto, #718096); }

    @media (max-width: 991px) {
        .brh-nav a:not(.brh-wa), .brh-dd { display: none; }
        .brh-top-list .brh-hide-m { display: none; }
    }
    @media (max-width: 575px) {
        .brh-top-inner { justify-content: center; }
        .brh-logo img { height: 40px; }
        .brh-wa { padding: 10px 14px; font-size: 13.5px; }
    }
</style>
@endpush

<div class="brh-top">
    <div class="brh-top-inner">
        <div class="brh-top-list">
            <span>
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                Atendimento ao Brasil em português
            </span>
            <a href="mailto:{{ $br['email'] }}" class="brh-hide-m">
                <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
                {{ $br['email'] }}
            </a>
            <span class="brh-hide-m">
                <svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                {{ $br['hours'] }}
            </span>
        </div>
        <a href="{{ route('index') }}" class="brh-lang" hreflang="es-MX" title="Sitio en español para México">
            🇲🇽 Site México (ES)
        </a>
    </div>
</div>

<header class="brh-main">
    <div class="brh-main-inner">
        <a href="{{ route('br.honeywell-dc1040') }}" class="brh-logo" aria-label="Mac del Norte">
            <img src="{{ asset('uploads/logo/webp-horizontal.webp') }}" alt="Mac del Norte — Distribuidor autorizado Honeywell" width="200" height="48">
        </a>
        <nav class="brh-nav" aria-label="Navegação">
            {{-- Desplegable con todas las landings: navegacion cruzada entre productos. --}}
            <div class="brh-dd">
                <a href="#" class="brh-dd-btn" aria-haspopup="true" aria-expanded="false">Produtos <span aria-hidden="true">▾</span></a>
                <div class="brh-dd-menu">
                    @foreach(config('brasil.landings') as $clave => $l)
                        <a href="{{ route($l['ruta']) }}" class="{{ request()->routeIs($l['ruta']) ? 'is-active' : '' }}">
                            <span class="brh-dd-marca">{{ $l['marca'] }}</span>
                            {{ $l['nombre'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="#especificacoes">Especificações</a>
            <a href="#equivalencias">Equivalências</a>
            <a href="#faq">Perguntas frequentes</a>
            <a href="{{ $br['whatsapp'] }}" target="_blank" rel="noopener" class="brh-wa track-conversion" data-type="whatsapp_br_header">
                <svg viewBox="0 0 24 24"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.8h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.9-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.89 9.88zm8.41-18.3A11.8 11.8 0 0 0 12.05 0C5.5 0 .16 5.33.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.9 11.9 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.33 11.89-11.89 0-3.18-1.24-6.16-3.48-8.41z"/></svg>
                Falar no WhatsApp
            </a>
        </nav>
    </div>
</header>

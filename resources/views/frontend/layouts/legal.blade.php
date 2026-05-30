@extends('frontend.layouts.master')

@section('title')
{{ $settings->site_name }} || @yield('legal-title')
@endsection

@push('styles')
<style>
  /* ===== LEGAL PAGES — from pages.css design system ===== */
  .legal-page { padding: 32px 0 64px; background: var(--gris-fondo); }
  .legal-grid {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 32px;
  }

  /* TOC SIDEBAR */
  .legal-toc {
    background: var(--blanco); border-radius: 12px;
    border: 1px solid var(--gris-borde); padding: 20px;
    align-self: start; position: sticky; top: 20px;
  }
  .legal-toc h4 {
    font-size: 11px; font-weight: 800; text-transform: uppercase;
    letter-spacing: 1px; color: var(--azul-principal); margin-bottom: 14px;
  }
  .legal-toc ul { list-style: none; padding: 0; margin: 0; }
  .legal-toc ul li { margin-bottom: 4px; }
  .legal-toc ul li a {
    display: block; padding: 7px 10px; font-size: 13px;
    color: var(--gris-texto); text-decoration: none;
    border-radius: 6px; border-left: 3px solid transparent;
    transition: all 0.2s;
  }
  .legal-toc ul li a:hover {
    background: var(--gris-fondo); color: var(--azul-principal);
    border-left-color: var(--accent-cta);
  }
  .legal-toc ul li a.active {
    background: var(--azul-claro); color: var(--azul-principal);
    font-weight: 700; border-left-color: var(--azul-principal);
  }

  /* CONTENT AREA */
  .legal-content {
    background: var(--blanco); border-radius: 12px;
    border: 1px solid var(--gris-borde); padding: 40px;
    min-width: 0;
  }
  .legal-meta {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 16px; background: var(--azul-claro);
    border-radius: 6px; font-size: 12px; color: var(--azul-principal);
    margin-bottom: 28px;
  }
  .legal-meta svg { width: 14px; height: 14px; flex-shrink: 0; }
  .legal-meta strong { font-weight: 800; }

  .legal-content h2 {
    font-size: 20px; font-weight: 800; color: var(--azul-principal);
    margin-top: 36px; margin-bottom: 12px; padding-bottom: 8px;
    border-bottom: 1px solid var(--gris-borde);
    scroll-margin-top: 24px;
  }
  .legal-content h2:first-of-type { margin-top: 0; }
  .legal-content h3 {
    font-size: 15px; font-weight: 700; color: var(--azul-medio);
    margin-top: 20px; margin-bottom: 8px;
  }
  .legal-content p {
    font-size: 14px; line-height: 1.75; color: var(--gris-texto); margin-bottom: 14px;
  }
  .legal-content ul, .legal-content ol {
    margin-bottom: 14px; padding-left: 22px;
  }
  .legal-content ul li, .legal-content ol li {
    font-size: 14px; line-height: 1.75; color: var(--gris-texto); margin-bottom: 6px;
  }
  .legal-content strong { color: var(--azul-principal); }
  .legal-content a { color: var(--azul-medio); }
  .legal-content a:hover { color: var(--azul-principal); }

  .legal-placeholder {
    background: #FFFBEB; border: 1px dashed #92400E;
    border-radius: 6px; padding: 14px 16px; margin: 14px 0;
    font-size: 13px; color: #92400E; font-style: italic;
  }
  .legal-placeholder strong { color: #92400E; font-style: normal; font-weight: 800; }

  /* RESPONSIVE */
  @media (max-width: 1100px) {
    .legal-grid { grid-template-columns: 220px 1fr; }
  }
  @media (max-width: 960px) {
    .legal-grid { grid-template-columns: 1fr; }
    .legal-toc { position: relative; top: 0; }
    .legal-content { padding: 24px; }
  }
  @media (max-width: 480px) {
    .legal-content { padding: 16px; }
  }
</style>
@endpush

@section('content')

{{-- BREADCRUMB --}}
<div style="background:var(--blanco);border-bottom:1px solid var(--gris-borde);padding:14px 0;">
    <div class="container">
        <nav style="display:flex;align-items:center;gap:8px;font-size:13px;flex-wrap:wrap;">
            <a href="{{ route('index') }}" style="color:var(--gris-claro-texto);text-decoration:none;font-weight:600;">Inicio</a>
            <span style="color:var(--gris-borde);">/</span>
            <span style="color:var(--azul-principal);font-weight:700;">@yield('legal-title')</span>
        </nav>
    </div>
</div>

{{-- PAGE HEADER --}}
<section style="background:linear-gradient(135deg,var(--azul-oscuro) 0%,var(--azul-principal) 60%,var(--azul-medio) 100%);color:var(--blanco);padding:40px 0;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.04) 1px,transparent 1px);background-size:50px 50px;"></div>
    <div class="container" style="position:relative;z-index:2;text-align:center;">
        <div style="display:inline-block;background:rgba(246,173,28,0.18);border:1px solid rgba(246,173,28,0.45);color:#F6AD1C;font-size:12px;font-weight:800;padding:7px 14px;border-radius:4px;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px;">
            @yield('legal-eyebrow')
        </div>
        <h1 style="font-size:28px;font-weight:800;color:var(--blanco);margin-bottom:10px;letter-spacing:-0.4px;">
            @yield('legal-title')
        </h1>
        <p style="font-size:15px;opacity:0.9;max-width:640px;margin:0 auto;">
            @yield('legal-subtitle')
        </p>
    </div>
</section>

{{-- LEGAL GRID --}}
<section class="legal-page">
    <div class="container">
        <div class="legal-grid">

            {{-- TOC SIDEBAR --}}
            <aside class="legal-toc" id="legal-toc">
                <h4>Contenido</h4>
                <ul id="toc-list">
                    @yield('legal-toc-items')
                </ul>
            </aside>

            {{-- CONTENT --}}
            <div class="legal-content" id="legal-content">

                <div class="legal-meta">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    @yield('legal-meta')
                </div>

                @yield('legal-body')

            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    // Intersection Observer — resalta el ítem del TOC visible en pantalla
    var headings = document.querySelectorAll('.legal-content h2[id]');
    var tocLinks = document.querySelectorAll('#toc-list a[href^="#"]');
    if (!headings.length || !tocLinks.length) return;

    function setActive(id) {
        tocLinks.forEach(function (link) {
            var href = link.getAttribute('href').replace('#', '');
            link.classList.toggle('active', href === id);
        });
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) setActive(entry.target.id);
        });
    }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });

    headings.forEach(function (h) { observer.observe(h); });

    // Smooth scroll al clickear TOC
    tocLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setActive(target.id);
            }
        });
    });
})();
</script>
@endpush

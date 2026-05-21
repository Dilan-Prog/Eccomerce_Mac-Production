@extends('frontend.layouts.master')

@section('canonical_URL')
    @if ($product->canonical_url)
        <link rel="canonical" href="{{ $product->canonical_url }}">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif
@endsection

@section('title')
{{ $settings->site_name }} || {{ $product->name }}
@endsection

@push('styles')
<style>
  :root {
    --negro-texto: #1A202C;
    --verde-disponible: #2F855A;
    --verde-claro: #F0FDF4;
    --rojo-error: #DC2626;
  }

  /* BREADCRUMB */
  .product-breadcrumb { background: var(--blanco); padding: 12px 0; border-bottom: 1px solid var(--gris-borde); }
  .product-breadcrumb-list { display: flex; align-items: center; gap: 8px; font-size: 13px; flex-wrap: wrap; }
  .product-breadcrumb-list a { color: var(--gris-claro-texto); text-decoration: none; font-weight: 600; }
  .product-breadcrumb-list a:hover { color: var(--azul-principal); }
  .breadcrumb-sep { color: var(--gris-borde); }
  .breadcrumb-cur { color: var(--azul-principal); font-weight: 700; }

  /* PRODUCT MAIN */
  .product-main { padding: 28px 0 40px; background: var(--gris-fondo); }
  .product-grid {
    display: grid; grid-template-columns: 1.1fr 1fr; gap: 32px;
    background: var(--blanco); padding: 32px; border-radius: 14px; border: 1px solid var(--gris-borde);
  }
  /* Evita que los hijos del grid desborden (fix clásico de CSS Grid) */
  .product-grid > * { min-width: 0; overflow: hidden; }

  /* GALLERY */
  .product-gallery { display: flex; gap: 14px; min-width: 0; overflow: visible; }
  .gallery-thumbs { display: flex; flex-direction: column; gap: 8px; }
  .gallery-thumb {
    width: 68px; height: 68px; border: 2px solid var(--gris-borde); border-radius: 8px;
    cursor: pointer; transition: all 0.2s; background: var(--blanco); overflow: hidden; flex-shrink: 0;
  }
  .gallery-thumb img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }
  .gallery-thumb:hover { border-color: var(--azul-medio); }
  .gallery-thumb.active { border-color: var(--accent-cta); }
  .gallery-main {
    flex: 1; background: linear-gradient(135deg, var(--gris-fondo) 0%, var(--blanco) 100%);
    border-radius: 12px; border: 1px solid var(--gris-borde);
    padding: 28px; position: relative; aspect-ratio: 1;
    display: flex; align-items: center; justify-content: center; overflow: hidden;
  }
  .gallery-main::before {
    content: ''; position: absolute; inset: 0;
    background-image: linear-gradient(rgba(0,62,126,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(0,62,126,0.03) 1px, transparent 1px);
    background-size: 30px 30px;
  }
  #gallery-main-img { max-width: 100%; max-height: 100%; object-fit: contain; position: relative; z-index: 2; }
  .gallery-badges { position: absolute; top: 14px; left: 14px; display: flex; flex-direction: column; gap: 6px; z-index: 3; }
  .gallery-badge {
    background: var(--accent-cta); color: var(--blanco);
    font-size: 10px; font-weight: 800; padding: 4px 9px;
    border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;
  }
  .gallery-badge.badge-new { background: var(--verde-disponible); }
  .gallery-badge.badge-brand { background: var(--azul-oscuro); }
  .image-zoom-btn {
    position: absolute; bottom: 14px; right: 14px;
    width: 38px; height: 38px; background: var(--blanco); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: var(--azul-principal); cursor: pointer; border: none;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 3; transition: all 0.2s;
  }
  .image-zoom-btn:hover { background: var(--azul-principal); color: var(--blanco); transform: scale(1.08); }
  .image-zoom-btn svg { width: 16px; height: 16px; }

  /* PRODUCT INFO */
  .product-info { display: flex; flex-direction: column; gap: 0; min-width: 0; overflow: visible; }
  .product-brand-badge {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 11px; font-weight: 800; color: var(--azul-principal);
    background: var(--azul-claro); padding: 5px 11px; border-radius: 4px;
    text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; align-self: flex-start;
  }
  .product-title {
    font-size: 22px; font-weight: 800; color: var(--azul-principal);
    line-height: 1.25; letter-spacing: -0.3px; margin-bottom: 12px;
    overflow-wrap: break-word; word-break: break-word;
  }

  /* CODES */
  .product-codes {
    display: flex; flex-wrap: wrap; gap: 12px;
    padding: 10px 13px; background: var(--gris-fondo);
    border-radius: 8px; margin-bottom: 13px; border: 1px solid var(--gris-borde);
  }
  .product-code-item { display: flex; flex-direction: column; min-width: 0; }
  .product-code-label { font-size: 10px; font-weight: 800; color: var(--gris-claro-texto); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
  .product-code-value {
    font-size: 13px; font-weight: 700; color: var(--azul-principal);
    font-family: 'Courier New', monospace;
    overflow-wrap: break-word; word-break: break-all;
  }

  /* RATING */
  .product-rating { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; font-size: 13px; }
  .rating-stars-row { display: flex; gap: 2px; color: #F6AD1C; font-size: 13px; }
  .rating-text { color: var(--gris-claro-texto); }
  .rating-text strong { color: var(--azul-principal); }
  .rating-text a { color: var(--azul-medio); font-weight: 700; text-decoration: none; }

  /* SHORT DESC */
  .product-short-desc { font-size: 14px; color: var(--gris-texto); line-height: 1.6; margin-bottom: 14px; }

  /* VARIANT PICKER */
  .mdn-variant-block { margin-bottom: 14px; }
  .mdn-variant-label { font-size: 11px; font-weight: 800; color: var(--gris-claro-texto); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 7px; }
  .mdn-variant-options { display: flex; flex-wrap: wrap; gap: 7px; }
  .mdn-variant-option, .product-details-variant, .product-details-variant-selected {
    padding: 7px 13px; border-radius: 6px; font-size: 13px; font-weight: 700;
    border: 2px solid var(--gris-borde); background: var(--blanco);
    color: var(--gris-texto); cursor: pointer; text-decoration: none; transition: all 0.2s; display: inline-block;
  }
  .product-details-variant:hover { border-color: var(--azul-principal); color: var(--azul-principal); }
  .product-details-variant-selected { border-color: var(--accent-cta) !important; color: var(--accent-cta) !important; background: #FFF8F0 !important; }
  .variant-unavailable { border-style: dashed !important; opacity: 0.45; }

  /* PRICE */
  .product-price-section {
    background: linear-gradient(135deg, var(--gris-fondo) 0%, var(--blanco) 100%);
    border: 1px solid var(--gris-borde); border-radius: 10px; padding: 16px; margin-bottom: 13px;
    min-width: 0; overflow: hidden;
  }
  .price-del { font-size: 14px; color: var(--gris-claro-texto); text-decoration: line-through; margin-bottom: 2px; }
  .price-main {
    font-size: 26px; font-weight: 800; color: var(--negro-texto);
    line-height: 1.1; letter-spacing: -0.6px;
    overflow-wrap: break-word; word-break: break-word;
  }
  .price-off-badge {
    display: inline-block; background: #FFF0E0; color: var(--accent-cta);
    font-size: 11px; font-weight: 800; padding: 3px 7px; border-radius: 4px; margin-left: 6px; vertical-align: middle;
  }
  .price-iva { font-size: 12px; color: var(--gris-claro-texto); margin: 5px 0 8px; }
  .price-iva strong { color: var(--verde-disponible); }
  .price-net {
    display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--gris-claro-texto);
    background: var(--blanco); padding: 4px 9px; border-radius: 4px; border: 1px solid var(--gris-borde);
    margin-bottom: 10px; flex-wrap: wrap;
  }
  .price-net strong { color: var(--azul-principal); font-weight: 700; }
  .price-msi {
    padding: 8px 11px; background: var(--verde-claro); border: 1px solid var(--verde-disponible);
    border-radius: 6px; font-size: 12px; color: var(--gris-texto); margin-bottom: 8px;
    overflow-wrap: break-word;
  }
  .price-msi strong { color: var(--verde-disponible); }
  .price-volume { font-size: 12px; color: var(--gris-claro-texto); padding-top: 8px; border-top: 1px dashed var(--gris-borde); overflow-wrap: break-word; }
  .price-volume strong { color: var(--accent-cta); }

  /* AVAILABILITY */
  .product-availability {
    display: flex; align-items: flex-start; gap: 8px;
    border-radius: 8px; padding: 11px 13px; margin-bottom: 13px; font-size: 13px;
  }
  .product-availability.in-stock { background: var(--verde-claro); border: 1px solid var(--verde-disponible); }
  .product-availability.out-of-stock { background: #FFF5F5; border: 1px solid #FC8181; }
  .product-availability.needs-advisor { background: var(--azul-claro); border: 1px solid var(--azul-principal); }
  .product-availability svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
  .product-availability.in-stock svg { color: var(--verde-disponible); }
  .product-availability.out-of-stock svg { color: var(--rojo-error); }
  .product-availability.needs-advisor svg { color: var(--azul-principal); }
  .avail-text { color: var(--gris-texto); font-weight: 500; line-height: 1.5; }
  .avail-text strong { display: block; font-weight: 800; }
  .in-stock .avail-text strong { color: var(--verde-disponible); }
  .out-of-stock .avail-text strong { color: var(--rojo-error); }
  .needs-advisor .avail-text strong { color: var(--azul-principal); }

  /* BUY SECTION */
  .product-buy { display: flex; flex-direction: column; gap: 9px; margin-bottom: 13px; }
  .quantity-row { display: flex; gap: 12px; align-items: center; }
  .quantity-label { font-size: 13px; font-weight: 700; color: var(--azul-principal); white-space: nowrap; }
  .quantity-control { display: flex; align-items: center; border: 1.5px solid var(--gris-borde); border-radius: 6px; overflow: hidden; background: var(--blanco); }
  .qty-btn { width: 36px; height: 40px; background: var(--gris-fondo); border: none; cursor: pointer; color: var(--azul-principal); font-size: 18px; font-weight: 700; transition: background 0.2s; }
  .qty-btn:hover { background: var(--azul-claro); }
  .qty-display { width: 52px; height: 40px; border: none; border-left: 1px solid var(--gris-borde); border-right: 1px solid var(--gris-borde); text-align: center; font-size: 14px; font-weight: 700; color: var(--negro-texto); background: var(--blanco); }
  .qty-display:focus { outline: none; }
  .qty-max { font-size: 12px; color: var(--gris-claro-texto); margin-left: auto; white-space: nowrap; }

  /* BUTTONS */
  .btn-mdn {
    padding: 12px 16px; border-radius: 6px; font-size: 13px; font-weight: 800;
    cursor: pointer; border: none; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center;
    gap: 6px; transition: all 0.2s; font-family: inherit; white-space: nowrap;
    max-width: 100%; overflow: hidden; box-sizing: border-box;
  }
  .btn-mdn svg { width: 15px; height: 15px; flex-shrink: 0; }
  .btn-mdn-primary { background: var(--accent-cta); color: var(--blanco); box-shadow: 0 4px 12px rgba(247,148,29,0.35); }
  .btn-mdn-primary:hover { background: var(--accent-cta-hover); color: var(--blanco); transform: translateY(-1px); }
  .btn-mdn-secondary { background: var(--blanco); color: var(--azul-principal); border: 2px solid var(--azul-principal); }
  .btn-mdn-secondary:hover { background: var(--azul-claro); color: var(--azul-principal); }
  .btn-mdn-wa { background: #25D366; color: var(--blanco); }
  .btn-mdn-wa:hover { background: #1da851; color: var(--blanco); }
  .btn-mdn-phone { background: var(--azul-oscuro); color: var(--blanco); }
  .btn-mdn-phone:hover { background: var(--azul-principal); color: var(--blanco); }
  .btn-mdn-block { width: 100%; }
  .btn-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; min-width: 0; }
  .btn-row-2 > * { min-width: 0; overflow: hidden; }

  /* EXTRAS */
  .product-extras { display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid var(--gris-borde); font-size: 12px; }
  .extras-btn { display: flex; align-items: center; gap: 5px; color: var(--gris-claro-texto); background: none; border: none; cursor: pointer; font-size: 12px; font-weight: 600; transition: color 0.2s; padding: 0; }
  .extras-btn:hover { color: var(--accent-cta); }
  .extras-btn svg { width: 15px; height: 15px; }
  .extras-shipping { color: var(--verde-disponible); font-weight: 700; display: flex; align-items: center; gap: 4px; }

  /* MARKETPLACES */
  .marketplaces-row { display: flex; align-items: center; gap: 12px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--gris-borde); flex-wrap: wrap; }
  .marketplaces-label { font-size: 12px; font-weight: 700; color: var(--gris-claro-texto); }
  .marketplaces-row img { height: 24px; width: auto; }

  /* BANNER B2B */
  .banner-b2b {
    background: linear-gradient(90deg, var(--azul-claro) 0%, var(--blanco) 100%);
    border: 1.5px solid var(--azul-principal); border-left: 5px solid var(--accent-cta);
    border-radius: 8px; padding: 14px 18px; display: flex; align-items: center; gap: 14px; margin: 22px 0;
  }
  .b2b-icon { width: 40px; height: 40px; background: var(--azul-principal); color: var(--blanco); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .b2b-icon svg { width: 20px; height: 20px; }
  .b2b-content { flex: 1; }
  .b2b-title { font-size: 14px; font-weight: 800; color: var(--azul-principal); margin-bottom: 2px; }
  .b2b-sub { font-size: 12px; color: var(--gris-texto); }
  .b2b-sub a { color: var(--azul-medio); font-weight: 700; text-decoration: none; font-style: italic; }
  .b2b-cta { flex-shrink: 0; }
  .btn-compact { padding: 8px 15px; font-size: 12px; }

  /* ASSURANCE */
  .assurance-strip { display: flex; border: 1px solid var(--gris-borde); border-radius: 8px; overflow: hidden; margin-bottom: 22px; }
  .assurance-item { flex: 1; padding: 11px 12px; display: flex; align-items: center; gap: 9px; font-size: 11px; color: var(--gris-texto); border-right: 1px solid var(--gris-borde); background: var(--blanco); }
  .assurance-item:last-child { border-right: none; }
  .assurance-item img { width: 30px; height: 30px; object-fit: contain; flex-shrink: 0; }
  .assurance-item p { margin: 0; line-height: 1.4; }
  .assurance-item p span { font-weight: 700; color: var(--azul-principal); display: block; font-size: 11px; }

  /* TABS */
  .product-tabs { background: var(--blanco); border-radius: 14px; border: 1px solid var(--gris-borde); overflow: hidden; margin-bottom: 28px; }
  .tabs-nav { display: flex; background: var(--gris-fondo); border-bottom: 1px solid var(--gris-borde); overflow-x: auto; }
  .mdn-tab-btn {
    flex: 1; min-width: 110px; padding: 15px 16px; background: none; border: none; cursor: pointer;
    font-size: 13px; font-weight: 700; color: var(--gris-claro-texto); transition: all 0.2s;
    border-bottom: 3px solid transparent; display: flex; align-items: center; justify-content: center;
    gap: 6px; font-family: inherit; white-space: nowrap;
  }
  .mdn-tab-btn svg { width: 15px; height: 15px; }
  .mdn-tab-btn:hover { color: var(--azul-principal); background: var(--blanco); }
  .mdn-tab-btn.active { color: var(--azul-principal); background: var(--blanco); border-bottom-color: var(--accent-cta); }
  .mdn-tab-pane { display: none; padding: 26px; }
  .mdn-tab-pane.active { display: block; }

  /* DESCRIPTION CONTENT */
  .description-content h3 { font-size: 17px; font-weight: 800; color: var(--azul-principal); margin: 20px 0 10px; }
  .description-content h3:first-child { margin-top: 0; }
  .description-content p { font-size: 14px; color: var(--gris-texto); line-height: 1.7; margin-bottom: 12px; }
  .description-content ul { padding-left: 0; list-style: none; margin: 12px 0; }
  .description-content ul li { padding: 6px 0 6px 24px; position: relative; font-size: 14px; color: var(--gris-texto); line-height: 1.5; }
  .description-content ul li::before { content: '✓'; position: absolute; left: 4px; color: var(--verde-disponible); font-weight: 800; }

  /* SPECS TABLE */
  .specs-table { width: 100%; border-collapse: collapse; font-size: 14px; }
  .specs-table tr { border-bottom: 1px solid var(--gris-borde); }
  .specs-table tr:last-child { border-bottom: none; }
  .specs-table tr:nth-child(even) { background: var(--gris-fondo); }
  .specs-table td { padding: 12px 14px; }
  .specs-table td:first-child { font-weight: 700; color: var(--azul-principal); width: 38%; }
  .specs-section-title { font-size: 13px; font-weight: 800; color: var(--accent-cta); text-transform: uppercase; letter-spacing: 1px; padding: 14px 14px 8px !important; background: var(--azul-claro) !important; border-bottom: 2px solid var(--azul-principal); }

  /* DOCS */
  .docs-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .doc-card { background: var(--gris-fondo); border: 1.5px solid var(--gris-borde); border-radius: 8px; padding: 16px; display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; transition: all 0.2s; }
  .doc-card:hover { border-color: var(--azul-principal); background: var(--blanco); box-shadow: 0 6px 18px rgba(0,62,126,0.08); transform: translateY(-2px); }
  .doc-icon { width: 46px; height: 46px; background: var(--rojo-error); color: var(--blanco); border-radius: 7px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 11px; font-weight: 800; }
  .doc-info { flex: 1; min-width: 0; }
  .doc-title { font-size: 13px; font-weight: 800; color: var(--azul-principal); margin-bottom: 2px; }
  .doc-meta { font-size: 11px; color: var(--gris-claro-texto); }
  .doc-download { color: var(--accent-cta); flex-shrink: 0; }
  .doc-download svg { width: 18px; height: 18px; }

  /* REVIEWS */
  .review-summary { display: flex; gap: 28px; align-items: flex-start; margin-bottom: 22px; flex-wrap: wrap; }
  .review-avg-number { font-size: 52px; font-weight: 800; color: var(--azul-principal); line-height: 1; }
  .review-avg-stars { display: flex; gap: 3px; color: #F6AD1C; margin-top: 5px; }
  .review-avg-count { font-size: 12px; color: var(--gris-claro-texto); margin-top: 4px; }
  .review-bars { flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 5px; }
  .review-bar-row { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--gris-texto); }
  .review-bar-track { flex: 1; height: 7px; background: var(--gris-borde); border-radius: 4px; overflow: hidden; }
  .review-bar-fill { height: 100%; background: linear-gradient(90deg, var(--azul-principal), var(--azul-medio)); border-radius: 4px; }
  .review-item { border-bottom: 1px solid var(--gris-borde); padding: 14px 0; }
  .review-item:last-child { border-bottom: none; }
  .review-user { font-size: 14px; font-weight: 700; color: var(--negro-texto); }
  .review-stars { color: #F6AD1C; font-size: 13px; margin: 4px 0; }
  .review-text { font-size: 14px; color: var(--gris-texto); line-height: 1.6; }
  .review-imgs { display: flex; gap: 7px; margin-top: 8px; flex-wrap: wrap; }
  .review-imgs img { width: 76px; height: 76px; object-fit: cover; border-radius: 6px; border: 1px solid var(--gris-borde); }
  .review-form-section { margin-top: 22px; padding-top: 22px; border-top: 1px solid var(--gris-borde); }
  .review-form-section h4 { font-size: 16px; font-weight: 800; color: var(--azul-principal); margin-bottom: 14px; }

  /* RELATED */
  .related-section { padding: 0 0 36px; }
  .section-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 18px; }
  .section-header h2 { font-size: 21px; font-weight: 800; color: var(--azul-principal); }
  .section-header p { font-size: 13px; color: var(--gris-claro-texto); margin-top: 2px; }
  .related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
  .related-card { background: var(--blanco); border: 1.5px solid var(--gris-borde); border-radius: 10px; overflow: hidden; transition: all 0.2s; text-decoration: none; color: inherit; display: block; }
  .related-card:hover { border-color: var(--azul-principal); box-shadow: 0 10px 26px rgba(0,62,126,0.1); transform: translateY(-3px); }
  .related-image { height: 130px; background: var(--gris-fondo); display: flex; align-items: center; justify-content: center; overflow: hidden; }
  .related-image img { max-width: 100%; max-height: 100%; object-fit: contain; padding: 10px; }
  .related-info { padding: 13px; }
  .related-pn { font-size: 10px; font-weight: 800; color: var(--gris-claro-texto); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-family: 'Courier New', monospace; }
  .related-name { font-size: 13px; font-weight: 700; color: var(--azul-principal); margin-bottom: 7px; line-height: 1.3; min-height: 32px; }
  .related-price { font-size: 15px; font-weight: 800; color: var(--negro-texto); }
  .related-price small { font-size: 10px; color: var(--gris-claro-texto); font-weight: 600; }
  .related-stock { font-size: 11px; color: var(--verde-disponible); font-weight: 700; margin-top: 4px; }

  /* RESPONSIVE */
  @media (max-width: 1024px) { .related-grid { grid-template-columns: repeat(3, 1fr); } }
  @media (max-width: 960px) {
    .product-grid { grid-template-columns: 1fr; padding: 18px; }
    .product-gallery { flex-direction: column-reverse; }
    .gallery-thumbs { flex-direction: row; overflow-x: auto; }
    .related-grid { grid-template-columns: repeat(2, 1fr); }
    .docs-grid { grid-template-columns: 1fr; }
    .assurance-strip { flex-direction: column; }
    .assurance-item { border-right: none; border-bottom: 1px solid var(--gris-borde); }
    .assurance-item:last-child { border-bottom: none; }
    .banner-b2b { flex-wrap: wrap; }
    .review-summary { flex-direction: column; }
  }
  @media (max-width: 680px) {
    .related-grid { grid-template-columns: 1fr 1fr; }
    .product-codes { flex-direction: column; gap: 8px; }
    .btn-row-2 { grid-template-columns: 1fr; }
  }
  @media (max-width: 420px) {
    .related-grid { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

@php
    /* ======================================================
       CÁLCULO DE PRECIOS Y VARIABLES PRINCIPALES
    ====================================================== */
    $sku          = $product->sku;
    $variantNames = [];

    // Precio base
    $price = $product->price_personalizated == 1
        ? $product->price
        : ($product->aspel_price ?? $product->price);

    // Precio oferta
    $offerPrice = $product->price_offert_personalizated == 1
        ? ($product->offert_price ?? null)
        : ($product->aspel_offert_price ?? $product->offert_price ?? null);

    // Sobreescribir con combinación seleccionada
    if ($selectedCombination) {
        $selectedIds = json_decode($selectedCombination->variants_item_ids, true);
        foreach ($selectedIds as $itemId) {
            foreach ($product->variants as $variant) {
                $variantItem = $variant->productVariantItems->where('id', $itemId)->first();
                if ($variantItem) { $variantNames[] = $variantItem->name; }
            }
        }
        $sku        = $selectedCombination->sku ?? $sku;
        $price      = $selectedCombination->price ?? $price;
        $offerPrice = $selectedCombination->offert_price ?? $offerPrice;
    }

    $fullProductName = $product->name . (count($variantNames) ? ' ' . implode(' ', $variantNames) : '');

    // Stock
    $stockQty = $selectedCombination
        ? $selectedCombination->qty
        : ($product->qty_personalizated == 0 ? $product->qty_aspel : $product->qty);

    // Descuento con fechas
    $today = date('Y-m-d');
    if ($selectedCombination) {
        $hasDiscount = checkCombinationDiscount($selectedCombination);
        $normalPrice = $selectedCombination->price;
    } else {
        $normalPrice  = $price;
        $offerStart   = $product->offer_start_date;
        $offerEnd     = $product->offer_end_date;
        $hasDiscount  = $offerPrice > 0
            && !empty($offerStart) && !empty($offerEnd)
            && $today >= $offerStart && $today <= $offerEnd
            && $offerPrice < $normalPrice;
    }
    $finalPrice = $hasDiscount ? $offerPrice : $normalPrice;

    // MSI
    $msiMeses  = 3;
    $msiMonto  = $finalPrice ? round($finalPrice / $msiMeses, 2) : 0;
    $priceNoIva = $finalPrice ? round($finalPrice / 1.16, 2) : 0;

    // Combinaciones para JS
    $jsCombinations = [];
    foreach ($productCombinations as $comb) {
        $jsCombinations[] = [
            'id'               => $comb->id,
            'variant_item_ids' => json_decode($comb->variants_item_ids, true),
            'price'            => $comb->price,
            'offert_price'     => $comb->offert_price,
            'qty'              => $comb->qty,
            'sku'              => $comb->sku,
        ];
    }
    $activeCombinationItems = [];
    foreach ($productCombinations->where('status', 1) as $comb) {
        $activeCombinationItems = array_merge($activeCombinationItems, json_decode($comb->variants_item_ids, true));
    }
    $activeCombinationItems = array_unique($activeCombinationItems);
    $selectedItemIds = $selectedCombination ? json_decode($selectedCombination->variants_item_ids, true) : [];

    // Marketplaces
    $marketplaces = collect($product->moreEccomerce ?? [])->filter(fn($m) => in_array($m->nameEccomerce, ['Mercado Libre', 'Amazon']));
@endphp

{{-- BREADCRUMB --}}
<div class="product-breadcrumb">
    <div class="container">
        <nav class="product-breadcrumb-list">
            <a href="{{ route('index') }}">Inicio</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}"
               itemprop="category" content="{{ $product->category->name }}">{{ $product->category->name }}</a>
            <span class="breadcrumb-sep">›</span>
            <span class="breadcrumb-cur">{{ Str::limit($product->name, 60) }}</span>
        </nav>
    </div>
</div>

{{-- SECCIÓN PRINCIPAL --}}
<section class="product-main" itemscope itemtype="http://schema.org/Product">
    <div class="container">

        <div class="product-grid">

            {{-- ===== GALERÍA ===== --}}
            <div class="product-gallery">
                <div class="gallery-thumbs" id="gallery-thumbs">
                    <div class="gallery-thumb active"
                         onclick="switchGalleryImage(this, '{{ asset($product->thumb_image) }}')"
                         role="button" tabindex="0">
                        <img src="{{ asset($product->thumb_image) }}" alt="{{ $product->name }}" loading="eager">
                    </div>
                    @foreach($product->productImageGalleries as $gi)
                    <div class="gallery-thumb"
                         onclick="switchGalleryImage(this, '{{ asset($gi->image) }}')"
                         role="button" tabindex="0">
                        <img src="{{ asset($gi->image) }}" alt="{{ $product->name }}" loading="lazy">
                    </div>
                    @endforeach
                </div>

                <div class="gallery-main">
                    <div class="gallery-badges">
                        @switch($product->product_type)
                            @case('new_arrival')
                                <div class="gallery-badge badge-new">Nuevo</div>
                                @break
                            @case('top_product')
                                <div class="gallery-badge">🔥 Hot Sale</div>
                                @break
                            @case('best_product')
                                <div class="gallery-badge badge-new">Más Vendido</div>
                                @break
                        @endswitch
                        <div class="gallery-badge badge-brand">⭐ {{ $product->brand->name }}</div>
                    </div>

                    <img id="gallery-main-img"
                         src="{{ asset($product->thumb_image) }}"
                         alt="{{ $product->name }}"
                         itemprop="image">

                    <button type="button" class="image-zoom-btn" onclick="openImageZoom()" title="Ver imagen grande">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                            <line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- ===== INFORMACIÓN ===== --}}
            <div class="product-info">

                {{-- Marca --}}
                <div class="product-brand-badge" itemprop="brand" itemscope itemtype="http://schema.org/Brand">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="width:11px;height:11px;color:var(--accent-cta)"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z"/></svg>
                    Distribuidor autorizado · <span itemprop="name">{{ $product->brand->name }}</span>
                </div>

                {{-- Título --}}
                <h1 class="product-title" itemprop="name">{{ $fullProductName }}</h1>

                {{-- Códigos P/N --}}
                <div class="product-codes">
                    <div class="product-code-item">
                        <span class="product-code-label">SKU / Clave</span>
                        <span class="product-code-value" id="dynamic_sku" itemprop="sku">{!! $sku !!}</span>
                    </div>
                    <div class="product-code-item">
                        <span class="product-code-label">Modelo</span>
                        <span class="product-code-value" itemprop="mpn">{{ $product->productModel }}</span>
                    </div>
                    <div class="product-code-item">
                        <span class="product-code-label">Marca</span>
                        <span class="product-code-value">{{ $product->brand->name }}</span>
                    </div>
                </div>

                {{-- Rating --}}
                <div class="product-rating">
                    <div class="rating-stars-row">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= floor($averageRating))
                                <i class="fas fa-star"></i>
                            @elseif ($i == ceil($averageRating) && $averageRating - floor($averageRating) >= 0.5)
                                <i class="fas fa-star-half-alt"></i>
                            @else
                                <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </div>
                    <span class="rating-text">
                        <strong>{{ number_format($averageRating, 1) }}</strong> ·
                        <a href="#tab-reviews" onclick="openReviewsTab()">{{ count($reviews) }} opiniones</a>
                    </span>
                </div>

                {{-- Descripción corta --}}
                @if ($product->short_description)
                <p class="product-short-desc">{{ $product->short_description }}</p>
                @endif

                {{-- Variantes --}}
                @foreach ($product->variants as $variant)
                    @php $filteredItems = $variant->productVariantItems->whereIn('id', $activeCombinationItems); @endphp
                    @if ($variant->status != 0 && $filteredItems->where('status', '!=', 0)->where('name', '!=', '')->isNotEmpty())
                    <div class="mdn-variant-block product-variant-picker" data-variant-id="{{ $variant->id }}">
                        <div class="mdn-variant-label">{{ $variant->name }}</div>
                        <div class="mdn-variant-options product-variant-picker-default">
                            @foreach ($filteredItems as $variantItem)
                                @if ($variantItem->status != 0)
                                <a role="button"
                                   class="{{ in_array($variantItem->id, $selectedItemIds) ? 'product-details-variant-selected' : 'product-details-variant' }}"
                                   href="#"
                                   data-variant-item-id="{{ $variantItem->id }}">
                                    {{ $variantItem->name }}
                                </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach

                {{-- ===== PRECIO ===== --}}
                <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                    <meta itemprop="priceCurrency" content="MXN">
                    <link itemprop="url" href="{{ url()->current() }}">

                    @if ($finalPrice)

                    <div class="product-price-section">
                        @if ($hasDiscount)
                        <div class="price-del">
                            {{ $settings->currency_icon }}{{ number_format($normalPrice, 2, '.', ',') }} MXN
                        </div>
                        @endif
                        <div class="price-main" id="dynamic_price">
                            <meta itemprop="price" content="{{ $finalPrice }}">
                            {{ $settings->currency_icon }}{{ number_format($finalPrice, 2, '.', ',') }} MXN
                            @if ($hasDiscount)
                            <span class="price-off-badge">
                                {{ round((($normalPrice - $offerPrice) / $normalPrice) * 100) }}% OFF
                            </span>
                            @endif
                        </div>
                        <div class="price-iva"><strong>✓ IVA incluido</strong> · Precio público</div>
                        <div class="price-net">
                            Neto sin IVA: <strong>{{ $settings->currency_icon }}{{ number_format($priceNoIva, 2, '.', ',') }} MXN</strong>
                        </div>
                        @if ($finalPrice >= 3000)
                        <div class="price-msi">
                            Págalo a <strong>{{ $msiMeses }} meses sin intereses de {{ $settings->currency_icon }}{{ number_format($msiMonto, 2) }} MXN</strong>
                            pagando con
                            <img src="{{ asset('frontend/images/iconos-empresas-sin-fondo/Paypal-logo.png') }}" alt="PayPal MSI" style="height:17px;vertical-align:middle;margin-left:3px;">
                        </div>
                        @else
                        <div class="price-msi">
                            Págalo a <strong>{{ $msiMeses }} meses sin intereses</strong> a partir de {{ $settings->currency_icon }}3,000 MXN en carrito con
                            <img src="{{ asset('frontend/images/iconos-empresas-sin-fondo/Paypal-logo.png') }}" alt="PayPal MSI" style="height:17px;vertical-align:middle;margin-left:3px;">
                        </div>
                        @endif
                        <div class="price-volume">
                            💼 <strong>¿Compras 5 o más?</strong> Cotiza con tu ingeniero para precio especial.
                        </div>
                    </div>

                    {{-- Disponibilidad --}}
                    @if ($stockQty > 0)
                    <div class="product-availability in-stock">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        <div class="avail-text">
                            <meta itemprop="availability" content="https://schema.org/InStock">
                            <strong>{{ $stockQty }} piezas en stock</strong>
                            Envío inmediato · Entrega en 1–3 días hábiles a todo México
                        </div>
                    </div>
                    @else
                    <div class="product-availability out-of-stock">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        <div class="avail-text">
                            <meta itemprop="availability" content="https://schema.org/OutOfStock">
                            <strong>Agotado temporalmente</strong>
                            Contáctanos para disponibilidad y tiempo de entrega
                        </div>
                    </div>
                    @endif

                    {{-- Formulario carrito --}}
                    <form class="shopping-cart-form">
                        <input type="hidden" name="product_id"    value="{{ $product->id }}">
                        <input type="hidden" name="combination_id" id="combination_id" value="{{ $selectedCombination ? $selectedCombination->id : '' }}">
                        <input type="hidden" name="brand_name"    value="{{ $product->brand->name }}">
                        <input type="hidden" name="sku"           value="{{ $sku }}">
                        <input type="hidden" name="productModel"  value="{{ $product->productModel }}">
                        <input type="hidden" name="qty"           id="qty-hidden" min="1" max="{{ $stockQty }}" value="1">

                        <div class="product-buy">
                            @if ($stockQty > 0)
                            <div class="quantity-row">
                                <span class="quantity-label">Cantidad:</span>
                                <div class="quantity-control">
                                    <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                                    <input type="text" class="qty-display" id="qty-display" value="1" readonly>
                                    <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                                </div>
                                <span class="qty-max">Máx. {{ $stockQty }} pzas</span>
                            </div>
                            <button type="submit" class="btn-mdn btn-mdn-primary btn-mdn-block">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                                </svg>
                                Agregar al carrito
                            </button>
                            @endif

                            <div class="btn-row-2">
                                <a href="{{ route('contact') }}" class="btn-mdn btn-mdn-secondary">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    Solicitar cotización
                                </a>
                                <a href="https://wa.link/f28njw" target="_blank"
                                   class="btn-mdn btn-mdn-wa track-conversion" data-type="whatsapp"
                                   onclick="dataLayer.push({'event':'whatsapp_conversion','action':'click','label':'whatsapp_producto'});">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                                    Hablar con ingeniero
                                </a>
                            </div>

                            <a href="tel:8124738768"
                               class="btn-mdn btn-mdn-phone btn-mdn-block track-conversion" data-type="telefono"
                               onclick="dataLayer.push({'event':'Telefono_Conversion','telefono':'8124738768'});">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                                Atención Inmediata: 81-3582-5559
                            </a>
                        </div>
                    </form>

                    @else
                    {{-- SIN PRECIO — REQUIERE ASESORÍA --}}
                    <div class="product-availability needs-advisor">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <div class="avail-text">
                            <meta itemprop="availability" content="https://schema.org/MadeToOrder">
                            <strong>Requiere Asesoría</strong>
                            La venta de este producto requiere contacto directo con un ingeniero
                        </div>
                    </div>
                    <div class="product-buy">
                        <div class="btn-row-2">
                            <a href="{{ route('contact') }}" class="btn-mdn btn-mdn-secondary">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                Contacto Directo
                            </a>
                            <a href="https://wa.link/f28njw" target="_blank"
                               class="btn-mdn btn-mdn-wa track-conversion" data-type="whatsapp">
                                <i class="fa fa-whatsapp"></i> Cotizar Ahora
                            </a>
                        </div>
                        <a href="tel:8124738768" class="btn-mdn btn-mdn-phone btn-mdn-block track-conversion" data-type="telefono">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                            Llamar: 81-3582-5559
                        </a>
                    </div>
                    @endif
                </div>{{-- /offers --}}

                {{-- Extras --}}
                <div class="product-extras">
                    <button type="button" class="extras-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                        Favoritos
                    </button>
                    <button type="button" class="extras-btn" onclick="navigator.share && navigator.share({title:'{{ $product->name }}', url: window.location.href})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        Compartir
                    </button>
                    @if ($finalPrice && $finalPrice >= $shippingRules->min_cost)
                    <span class="extras-shipping">
                        <svg viewBox="0 0 24 24" fill="currentColor" style="width:13px;height:13px"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4z"/></svg>
                        Envío gratis
                    </span>
                    @endif
                </div>

                {{-- Marketplaces --}}
                @if ($marketplaces->count() > 0)
                <div class="marketplaces-row">
                    <span class="marketplaces-label">Disponible en:</span>
                    @foreach ($marketplaces as $mp)
                        @if ($mp->nameEccomerce == 'Mercado Libre')
                        <a href="{{ $mp->linkProduct }}" target="_blank"
                           onclick="dataLayer.push({event:'Mercado_libre-action',action:'click',label:'Mercado-Libre-product-detail',value:'{{ $finalPrice }}'});">
                            <img src="{{ asset('frontend/images/iconos-empresas/MercadoLibre_Logo.webp') }}" alt="{{ $product->name }} Mercado Libre">
                        </a>
                        @endif
                        @if ($mp->nameEccomerce == 'Amazon')
                        <a href="{{ $mp->linkProduct }}" target="_blank">
                            <img src="{{ asset('frontend/images/iconos-empresas/Amazon_logo.png') }}" alt="{{ $product->name }} Amazon">
                        </a>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- Link schema --}}
                <link itemprop="url" href="{{ url()->current() }}" />
                <span itemprop="mpn" content="{{ $product->productModel }}" style="display:none;">{{ $product->productModel }}</span>

            </div>{{-- /product-info --}}
        </div>{{-- /product-grid --}}

        {{-- BANNER B2B --}}
        <div class="banner-b2b">
            <div class="b2b-icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 7H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm-1 10H5c-.55 0-1-.45-1-1v-7c0-.55.45-1 1-1h14c.55 0 1 .45 1 1v7c0 .55-.45 1-1 1z"/></svg>
            </div>
            <div class="b2b-content">
                <div class="b2b-title">¿Eres revendedor o técnico?</div>
                <div class="b2b-sub">Accede a precios B2B con descuentos especiales por volumen. <a href="{{ route('contact') }}">Solicitar acceso →</a></div>
            </div>
            <div class="b2b-cta">
                <a href="{{ route('contact') }}" class="btn-mdn btn-mdn-secondary btn-compact">Saber más</a>
            </div>
        </div>

        {{-- SELLOS DE CONFIANZA --}}
        <div class="assurance-strip">
            <div class="assurance-item">
                <img src="{{ asset('animations-icons/payment-protected-detail.gif') }}" alt="Compra Protegida">
                <p><span>Compra Protegida</span>Seguridad SSL en todas las transacciones</p>
            </div>
            <div class="assurance-item">
                <img src="{{ asset('frontend/images/iconos/guarantee.webp') }}" alt="Garantía">
                <p><span>Garantía de Fábrica</span>Producto 100% original</p>
            </div>
            <div class="assurance-item">
                <img src="{{ asset('frontend/images/iconos/how-to-pay.webp') }}" alt="Formas de Pago">
                <p><span>Múltiples Formas de Pago</span>Tarjeta, transferencia, PayPal y más</p>
            </div>
        </div>

        {{-- TABS --}}
        <div class="product-tabs">
            <div class="tabs-nav">
                <button class="mdn-tab-btn active" onclick="mdnSwitchTab(event,'description')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>
                    Descripción
                </button>
                <button class="mdn-tab-btn" onclick="mdnSwitchTab(event,'specs')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    Especificaciones
                </button>
                <button class="mdn-tab-btn" onclick="mdnSwitchTab(event,'docs')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/></svg>
                    Documentación
                </button>
                @if ($product->video_link)
                <button class="mdn-tab-btn" onclick="mdnSwitchTab(event,'video')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                    Video
                </button>
                @endif
                <button class="mdn-tab-btn" id="reviews-tab-btn" onclick="mdnSwitchTab(event,'reviews')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    Opiniones ({{ count($reviews) }})
                </button>
            </div>

            {{-- TAB: DESCRIPCIÓN --}}
            <div class="mdn-tab-pane active" id="mdn-tab-description">
                <div class="description-content" itemprop="description" content="{{ strip_tags($product->long_description) }}">
                    {!! $product->long_description !!}
                </div>
            </div>

            {{-- TAB: ESPECIFICACIONES --}}
            {{-- TODO: Campo $product->specifications (JSON/tabla dedicada) para especificaciones detalladas --}}
            <div class="mdn-tab-pane" id="mdn-tab-specs">
                <table class="specs-table">
                    <tr><td colspan="2" class="specs-section-title">Información General</td></tr>
                    <tr><td>Marca</td><td>{{ $product->brand->name }}</td></tr>
                    <tr><td>Modelo</td><td>{{ $product->productModel }}</td></tr>
                    <tr><td>SKU / Clave</td><td>{{ $sku }}</td></tr>
                    <tr><td>Categoría</td><td>{{ $product->category->name }}</td></tr>
                    {{-- TODO: Agregar hasMany ProductSpecification o campo JSON para tabla completa --}}
                </table>
                @if ($product->long_description)
                <div class="description-content" style="margin-top:20px;">
                    {!! $product->long_description !!}
                </div>
                @endif
            </div>

            {{-- TAB: DOCUMENTACIÓN --}}
            <div class="mdn-tab-pane" id="mdn-tab-docs">
                @if ($product->url_PDF)
                <p style="font-size:13px;color:var(--gris-texto);margin-bottom:16px;">
                    Descarga la documentación oficial para evaluar especificaciones e instalar correctamente el equipo.
                </p>
                <div class="docs-grid">
                    <a href="{{ $product->url_PDF }}" target="_blank" class="doc-card">
                        <div class="doc-icon">PDF</div>
                        <div class="doc-info">
                            <div class="doc-title">Datasheet / Ficha Técnica</div>
                            <div class="doc-meta">Documento oficial del fabricante</div>
                        </div>
                        <div class="doc-download">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                        </div>
                    </a>
                    {{-- TODO: Relación hasMany ProductDocument para múltiples PDFs (manual, certificados, diagramas) --}}
                </div>
                @else
                <p style="font-size:14px;color:var(--gris-claro-texto);">No hay documentación disponible para este producto por el momento.</p>
                @endif
            </div>

            {{-- TAB: VIDEO --}}
            @if ($product->video_link)
            <div class="mdn-tab-pane" id="mdn-tab-video">
                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;background:var(--negro-texto);">
                    <iframe
                        src="{{ $product->video_link }}"
                        style="position:absolute;top:0;left:0;width:100%;height:100%;"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen loading="lazy">
                    </iframe>
                </div>
            </div>
            @endif

            {{-- TAB: OPINIONES --}}
            <div class="mdn-tab-pane" id="mdn-tab-reviews">
                <div id="reviews-section">
                    <h3 style="font-size:18px;font-weight:800;color:var(--azul-principal);margin-bottom:20px;">Opiniones del Producto</h3>

                    <div class="review-summary">
                        <div>
                            <div class="review-avg-number">{{ number_format($averageRating, 1) }}</div>
                            <div class="review-avg-stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= floor($averageRating)) <i class="fas fa-star"></i>
                                    @elseif ($i == ceil($averageRating) && $averageRating - floor($averageRating) >= 0.5) <i class="fas fa-star-half-alt"></i>
                                    @else <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <div class="review-avg-count">{{ count($reviews) }} opiniones</div>
                        </div>
                        <div class="review-bars">
                            @foreach ([5,4,3,2,1] as $star)
                                @php $pct = count($reviews) > 0 ? max(($ratingCounts[$star] / count($reviews)) * 100, 1) : 1; @endphp
                                <div class="review-bar-row">
                                    <span style="min-width:36px;">{{ $star }} <i class="fas fa-star" style="font-size:10px;color:#F6AD1C;"></i></span>
                                    <div class="review-bar-track"><div class="review-bar-fill" style="width:{{ $pct }}%;"></div></div>
                                    <span style="min-width:22px;text-align:right;font-size:11px;">{{ $ratingCounts[$star] ?? 0 }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @forelse ($reviews as $review)
                    <div class="review-item">
                        <div class="review-user">{{ $review->user->name }}</div>
                        <div class="review-stars">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                            @endfor
                        </div>
                        <p class="review-text">{{ $review->review }}</p>
                        @if (count($review->productReviewGalleries) > 0)
                        <div class="review-imgs">
                            @foreach ($review->productReviewGalleries as $img)
                            <img src="{{ asset($img->image) }}" alt="Foto de opinión">
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @empty
                    <p style="font-size:14px;color:var(--gris-claro-texto);padding:12px 0;">Aún no hay opiniones. ¡Sé el primero en opinar!</p>
                    @endforelse

                    @if ($reviews->hasPages())
                    <div class="mt-4">{{ $reviews->links() }}</div>
                    @endif

                    {{-- Formulario de opinión --}}
                    @auth
                    @php
                        $isBrought = false;
                        $orders = \App\Models\Order::where(['user_id' => auth()->user()->id, 'order_status' => 'delivered'])->get();
                        foreach ($orders as $order) {
                            if ($order->orderProducts()->where('product_id', $product->id)->first()) { $isBrought = true; break; }
                        }
                    @endphp
                    @if ($isBrought)
                    <div class="review-form-section">
                        <h4>Deja tu opinión</h4>
                        <form action="{{ route('user.review.create') }}" enctype="multipart/form-data" method="POST">
                            @csrf
                            <div style="margin-bottom:12px;">
                                <p style="font-size:13px;font-weight:700;margin-bottom:7px;">Calificación:</p>
                                <div style="display:flex;gap:7px;font-size:26px;color:#ccc;">
                                    @for ($s = 1; $s <= 5; $s++)
                                    <i class="fas fa-star" id="star" onclick="setRating({{ $s }})" style="cursor:pointer;transition:color 0.15s;"></i>
                                    @endfor
                                </div>
                                <input type="hidden" id="rating_value" name="rating" value="0">
                            </div>
                            <div style="margin-bottom:12px;">
                                <label style="font-size:13px;font-weight:700;display:block;margin-bottom:6px;">Tu comentario:</label>
                                <textarea name="review" rows="4" placeholder="El producto cumple con mis expectativas..."
                                    style="width:100%;padding:10px 12px;border:1.5px solid var(--gris-borde);border-radius:6px;font-size:14px;font-family:inherit;resize:vertical;"></textarea>
                            </div>
                            <div style="margin-bottom:14px;">
                                <label style="font-size:13px;font-weight:700;display:block;margin-bottom:6px;">Imágenes (opcional, máx. 4):</label>
                                <label for="file-input" id="upload-icon"
                                    style="cursor:pointer;padding:9px 14px;border:2px dashed var(--gris-borde);border-radius:6px;display:inline-block;font-size:13px;color:var(--gris-claro-texto);">
                                    <i class="fas fa-image"></i> Seleccionar imágenes
                                </label>
                                <input type="file" id="file-input" name="images[]" multiple style="display:none;">
                                <div id="image-preview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;"></div>
                            </div>
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="btn-mdn btn-mdn-primary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Enviar opinión
                            </button>
                        </form>
                    </div>
                    @endif
                    @endauth
                </div>
            </div>

        </div>{{-- /product-tabs --}}

        {{-- PRODUCTOS RELACIONADOS --}}
        {{-- TODO: Pasar $relatedProducts desde ProductController@show --}}
        {{-- Ejemplo: $relatedProducts = Product::where('category_id',$product->category_id)->where('id','!=',$product->id)->limit(4)->with(['brand','combinations'])->get(); --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
        <section class="related-section">
            <div class="section-header">
                <div>
                    <h2>Productos relacionados</h2>
                    <p>Equipos compatibles y complementarios para tu proyecto</p>
                </div>
                <a href="{{ route('products.index', ['category' => $product->category->slug]) }}"
                   class="btn-mdn btn-mdn-secondary btn-compact">Ver todos</a>
            </div>
            <div class="related-grid">
                @foreach ($relatedProducts as $related)
                @php
                    $relComb  = $related->combinations->where('is_default', 1)->first();
                    $relPrice = $relComb
                        ? ($relComb->offert_price ?? $relComb->price)
                        : ($related->price_personalizated == 1 ? $related->price : ($related->aspel_price ?? $related->price));
                    $relQty   = $relComb ? $relComb->qty : ($related->qty_personalizated == 0 ? $related->qty_aspel : $related->qty);
                @endphp
                <a href="{{ route('product-detail', $related->slug) }}" class="related-card">
                    <div class="related-image">
                        <img src="{{ asset($related->thumb_image) }}" alt="{{ $related->name }}" loading="lazy">
                    </div>
                    <div class="related-info">
                        <div class="related-pn">{{ $related->sku }}</div>
                        <div class="related-name">{{ Str::limit($related->name, 55) }}</div>
                        @if ($relPrice)
                        <div class="related-price">{{ $settings->currency_icon }}{{ number_format($relPrice, 2, '.', ',') }} <small>/ pza · IVA inc.</small></div>
                        @endif
                        <div class="related-stock">
                            @if ($relQty > 0) ✓ {{ $relQty }} disponibles
                            @else <span style="color:var(--rojo-error);">Agotado</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>{{-- /container --}}
</section>

@endsection

@push('scripts')
<script>
window.productCombinations = @json($jsCombinations);

/* ===== TABS ===== */
function mdnSwitchTab(event, tabName) {
    document.querySelectorAll('.mdn-tab-pane').forEach(function(p) { p.classList.remove('active'); });
    document.querySelectorAll('.mdn-tab-btn').forEach(function(b) { b.classList.remove('active'); });
    var pane = document.getElementById('mdn-tab-' + tabName);
    if (pane) pane.classList.add('active');
    event.currentTarget.classList.add('active');
}

function openReviewsTab() {
    var btn = document.getElementById('reviews-tab-btn');
    if (btn) btn.click();
    setTimeout(function() {
        var el = document.getElementById('reviews-section');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

/* ===== CANTIDAD ===== */
function changeQty(delta) {
    var display = document.getElementById('qty-display');
    var hidden  = document.getElementById('qty-hidden');
    if (!display) return;
    var max = parseInt(display.getAttribute('max') || hidden && hidden.getAttribute('max') || 99);
    var val = parseInt(display.value) + delta;
    if (val < 1) val = 1;
    if (val > max) val = max;
    display.value = val;
    if (hidden) hidden.value = val;
}

/* ===== GALERÍA ===== */
function switchGalleryImage(thumb, src) {
    document.getElementById('gallery-main-img').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(function(t) { t.classList.remove('active'); });
    thumb.classList.add('active');
}

function openImageZoom() {
    var src = document.getElementById('gallery-main-img').src;
    var w = window.open('', '_blank', 'width=900,height=700,scrollbars=yes');
    if (w) w.document.write('<body style="margin:0;background:#111;display:flex;align-items:center;justify-content:center;min-height:100vh;"><img src="' + src + '" style="max-width:100%;max-height:100vh;object-fit:contain;"></body>');
}

/* ===== ESTRELLAS REVIEW ===== */
function setRating(rating) {
    var input = document.getElementById('rating_value');
    if (input) input.value = rating;
    document.querySelectorAll('#star').forEach(function(star, i) {
        star.style.color = i < rating ? '#F6AD1C' : '#ccc';
    });
}

document.addEventListener('DOMContentLoaded', function() {

    /* ===== VARIANTES ===== */
    var selectedVariantItems = {};
    document.querySelectorAll('.product-details-variant-selected').forEach(function(el) {
        var picker = el.closest('.product-variant-picker');
        if (picker) selectedVariantItems[picker.dataset.variantId] = parseInt(el.dataset.variantItemId);
    });

    function updateVariantAvailability() {
        document.querySelectorAll('.product-variant-picker').forEach(function(picker) {
            var thisId = picker.dataset.variantId;
            picker.querySelectorAll('.product-details-variant, .product-details-variant-selected').forEach(function(opt) {
                var itemId = Number(opt.dataset.variantItemId);
                var sim = Object.assign({}, selectedVariantItems);
                sim[thisId] = itemId;
                var simIds = Object.values(sim).map(Number).sort(function(a,b){return a-b;});
                var available = window.productCombinations.some(function(c) {
                    var cIds = c.variant_item_ids.map(Number).sort(function(a,b){return a-b;});
                    return JSON.stringify(cIds) === JSON.stringify(simIds);
                });
                opt.classList.toggle('variant-unavailable', !available);
            });
        });
    }

    updateVariantAvailability();

    document.querySelectorAll('.product-details-variant, .product-details-variant-selected').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            var picker  = el.closest('.product-variant-picker');
            var variantId = picker.dataset.variantId;
            var itemId    = el.dataset.variantItemId;
            var sim = Object.assign({}, selectedVariantItems);
            sim[variantId] = parseInt(itemId);
            var simIds = Object.values(sim).map(Number).sort(function(a,b){return a-b;});

            var match = window.productCombinations.find(function(c) {
                return JSON.stringify(c.variant_item_ids.map(Number).sort(function(a,b){return a-b;})) === JSON.stringify(simIds);
            });
            if (!match) {
                match = window.productCombinations.find(function(c) {
                    return c.variant_item_ids.map(Number).includes(Number(itemId));
                });
            }
            if (match) {
                var slug    = "{{ $product->slug }}";
                var current = new URLSearchParams(window.location.search).get('comb');
                if (current != match.id.toString()) {
                    window.location.href = '/product-detail/' + slug + '?comb=' + match.id;
                }
            }
            updateVariantAvailability();
        });
    });

    /* ===== PREVIEW IMÁGENES REVIEW ===== */
    var fileInput   = document.getElementById('file-input');
    var preview     = document.getElementById('image-preview');
    var uploadLabel = document.getElementById('upload-icon');
    if (fileInput && preview) {
        fileInput.addEventListener('change', function() {
            preview.innerHTML = '';
            if (uploadLabel) uploadLabel.style.display = 'none';
            Array.from(fileInput.files).forEach(function(file) {
                if (!file.type.startsWith('image/')) return;
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width:76px;height:76px;object-fit:cover;border-radius:6px;border:1px solid var(--gris-borde);';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    /* ===== TECLADO EN GALERÍA (accesibilidad) ===== */
    document.querySelectorAll('.gallery-thumb').forEach(function(thumb) {
        thumb.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') thumb.click();
        });
    });
});
</script>

@if ($finalPrice)
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "sku": "{{ $product->sku }}",
  "image": "{{ asset($product->thumb_image) }}",
  "additionalImage": [
    @foreach($product->productImageGalleries as $productImage)
    "{{ asset($productImage->image) }}"{!! $loop->last ? '' : ',' !!}
    @endforeach
  ],
  "identifier_exists": "true",
  "mpn": "{{ $product->productModel }}",
  "name": "{{ $product->name }}",
  "description": "{{ addslashes(strip_tags($product->long_description)) }}",
  "brand": {
    "@type": "Brand",
    "name": "{{ $product->brand->name }}"
  },
  "offers": {
    "@type": "Offer",
    "url": "{{ url()->current() }}",
    "itemCondition": "https://schema.org/NewCondition",
    "availability": "https://schema.org/{{ $stockQty > 0 ? 'InStock' : 'OutOfStock' }}",
    "price": "{{ $finalPrice }}",
    "priceCurrency": "MXN"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{ number_format($averageRating, 1) }}",
    "reviewCount": "{{ count($reviews) > 0 ? count($reviews) : 1 }}"
  }
}
</script>
@endif
@endpush

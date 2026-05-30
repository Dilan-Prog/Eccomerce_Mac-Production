@extends('frontend.layouts.master')

@section('title')
{{ $settings->site_name }} || Todos Los Productos
@endsection

@push('styles')
<style>
  /* ===== PRODUCTS PAGE — page-specific styles ===== */

  /* PAGE WRAPPER */
  .products-page { background: var(--gris-fondo); padding: 28px 0 48px; }

  /* PAGE HEADER */
  .products-page-header {
    background: var(--blanco); border-bottom: 1px solid var(--gris-borde);
    padding: 14px 0;
  }
  .products-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; flex-wrap: wrap; }
  .products-breadcrumb a { color: var(--gris-claro-texto); text-decoration: none; font-weight: 600; }
  .products-breadcrumb a:hover { color: var(--azul-principal); }
  .breadcrumb-sep { color: var(--gris-borde); }
  .breadcrumb-cur { color: var(--azul-principal); font-weight: 700; }

  /* LAYOUT */
  .products-page-layout {
    display: grid;
    grid-template-columns: 272px 1fr;
    gap: 24px;
    align-items: start;
    margin-top: 24px;
  }
  .products-page-layout > * { min-width: 0; }

  /* SIDEBAR */
  .filters-sidebar {
    background: var(--blanco);
    border: 1px solid var(--gris-borde);
    border-radius: 14px;
    overflow: hidden;
    position: sticky;
    top: 80px;
  }
  .filters-sidebar-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--gris-borde);
  }
  .filters-sidebar-title {
    font-size: 14px; font-weight: 800; color: var(--azul-principal);
    text-transform: uppercase; letter-spacing: 0.8px;
  }
  .filters-clear-btn {
    font-size: 12px; font-weight: 700; color: var(--accent-cta);
    background: none; border: none; cursor: pointer; padding: 0;
    text-decoration: underline;
  }
  .filters-clear-btn:hover { color: var(--accent-cta-hover); }

  /* FILTER GROUP */
  .filter-group { border-bottom: 1px solid var(--gris-borde); }
  .filter-group:last-child { border-bottom: none; }
  .filter-group-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; cursor: pointer;
    font-size: 13px; font-weight: 700; color: var(--azul-principal);
    background: none; border: none; width: 100%; text-align: left;
    transition: background 0.15s;
  }
  .filter-group-header:hover { background: var(--gris-fondo); }
  .filter-group-chevron {
    width: 16px; height: 16px; color: var(--gris-claro-texto);
    transition: transform 0.2s;
    flex-shrink: 0;
  }
  .filter-group-header.collapsed .filter-group-chevron { transform: rotate(-90deg); }
  .filter-group-body { padding: 4px 20px 16px; }
  .filter-group-body.hidden { display: none; }

  /* FILTER OPTIONS */
  .filter-option {
    display: flex; align-items: center; gap: 10px;
    padding: 6px 0; font-size: 13px; color: var(--gris-texto); text-decoration: none;
    border-radius: 5px; transition: color 0.15s;
  }
  .filter-option:hover { color: var(--azul-principal); }
  .filter-option.active { color: var(--azul-principal); font-weight: 700; }
  .filter-option-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--gris-borde); flex-shrink: 0;
    transition: background 0.15s;
  }
  .filter-option:hover .filter-option-dot,
  .filter-option.active .filter-option-dot { background: var(--azul-principal); }

  /* PRICE RANGE */
  .filter-price-form { display: flex; flex-direction: column; gap: 10px; }
  .filter-price-inputs { display: flex; gap: 8px; }
  .filter-price-input {
    flex: 1; padding: 7px 10px; border: 1px solid var(--gris-borde);
    border-radius: 7px; font-size: 13px; color: var(--gris-texto);
    background: var(--gris-fondo);
  }
  .filter-price-input:focus { outline: none; border-color: var(--azul-principal); }
  .filter-apply-btn {
    padding: 8px 0; background: var(--azul-principal); color: var(--blanco);
    border: none; border-radius: 7px; font-size: 13px; font-weight: 700;
    cursor: pointer; transition: background 0.2s;
  }
  .filter-apply-btn:hover { background: var(--azul-medio); }
  .filter-slider-wrap { padding: 6px 0 4px; }

  /* MAIN CONTENT */
  .products-main { display: flex; flex-direction: column; gap: 16px; }

  /* RESULTS HEADER */
  .results-header {
    display: flex; align-items: center; justify-content: space-between;
    background: var(--blanco); border: 1px solid var(--gris-borde);
    border-radius: 10px; padding: 12px 18px; gap: 12px; flex-wrap: wrap;
  }
  .results-count { font-size: 13px; color: var(--gris-texto); }
  .results-count strong { color: var(--azul-principal); font-weight: 800; }
  .results-header-right { display: flex; align-items: center; gap: 12px; }
  .sort-label { font-size: 13px; color: var(--gris-claro-texto); white-space: nowrap; }
  .sort-select {
    padding: 7px 10px; border: 1px solid var(--gris-borde); border-radius: 7px;
    font-size: 13px; color: var(--gris-texto); background: var(--gris-fondo);
    cursor: pointer;
  }
  .sort-select:focus { outline: none; border-color: var(--azul-principal); }
  .view-toggle { display: flex; gap: 4px; }
  .view-btn {
    width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--gris-borde); border-radius: 7px; background: var(--blanco);
    cursor: pointer; color: var(--gris-claro-texto); transition: all 0.15s;
  }
  .view-btn:hover, .view-btn.active {
    background: var(--azul-principal); color: var(--blanco); border-color: var(--azul-principal);
  }
  .view-btn svg { width: 15px; height: 15px; }

  /* PRODUCTS GRID */
  .products-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }
  .products-grid > * { min-width: 0; }

  /* PRODUCT CARD */
  .product-card {
    background: var(--blanco); border: 1px solid var(--gris-borde);
    border-radius: 12px; overflow: hidden;
    display: flex; flex-direction: column;
    transition: box-shadow 0.2s, transform 0.2s;
    position: relative;
  }
  .product-card:hover {
    box-shadow: 0 8px 32px rgba(0,62,126,0.10);
    transform: translateY(-2px);
  }

  /* CARD IMAGE */
  .product-card-image {
    position: relative;
    aspect-ratio: 1;
    background: linear-gradient(135deg, var(--gris-fondo) 0%, var(--blanco) 100%);
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
  }
  .product-card-image img {
    max-width: 100%; max-height: 100%; object-fit: contain; padding: 12px;
    transition: transform 0.35s ease;
  }
  .product-card:hover .product-card-image img { transform: scale(1.05); }
  .product-card-image .img-hover { display: none; }
  .product-card:hover .product-card-image .img-hover { display: block; }
  .product-card:hover .product-card-image .img-main { display: none; }

  /* CARD BADGES */
  .card-badge {
    position: absolute; top: 10px; left: 10px;
    font-size: 10px; font-weight: 800; padding: 3px 8px;
    border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;
    color: var(--blanco); z-index: 2;
  }
  .card-badge--new { background: #2F855A; }
  .card-badge--hot { background: #DC2626; }
  .card-badge--best { background: var(--accent-cta); }
  .card-badge--off {
    position: absolute; top: 10px; right: 10px;
    background: #FFF0E0; color: var(--accent-cta);
    font-size: 11px; font-weight: 800; padding: 4px 8px; border-radius: 4px;
    z-index: 2;
  }
  .card-hot-img {
    position: absolute; top: 8px; right: 8px; width: 56px; z-index: 2;
  }

  /* CARD INFO */
  .product-card-info {
    flex: 1; padding: 14px 16px; display: flex; flex-direction: column; gap: 6px;
    min-width: 0;
  }
  .card-category {
    font-size: 11px; font-weight: 700; color: var(--gris-claro-texto);
    text-transform: uppercase; letter-spacing: 0.6px; text-decoration: none;
  }
  .card-category:hover { color: var(--azul-principal); }
  .card-name {
    font-size: 14px; font-weight: 700; color: var(--azul-principal);
    line-height: 1.35; text-decoration: none; display: -webkit-box;
    -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    word-break: break-word;
  }
  .card-name:hover { color: var(--azul-medio); }
  .card-sku { font-size: 11px; color: var(--gris-claro-texto); font-family: monospace; }

  /* CARD RATING */
  .card-rating { display: flex; align-items: center; gap: 5px; }
  .card-stars { display: flex; gap: 1px; font-size: 11px; color: #F6AD1C; }
  .card-rating-count { font-size: 11px; color: var(--gris-claro-texto); }

  /* CARD STOCK */
  .card-stock { font-size: 12px; font-weight: 600; }
  .card-stock--in { color: #2F855A; }
  .card-stock--out { color: #DC2626; }
  .card-stock--consult { color: var(--gris-claro-texto); }

  /* CARD PRICE */
  .card-price-block { margin-top: 2px; }
  .card-price-del { font-size: 12px; color: var(--gris-claro-texto); text-decoration: line-through; }
  .card-price-main {
    font-size: 18px; font-weight: 800; color: var(--azul-principal);
    line-height: 1.1; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
  }
  .card-price-off {
    font-size: 11px; font-weight: 800; background: #FFF0E0; color: var(--accent-cta);
    padding: 2px 6px; border-radius: 3px;
  }
  .card-price-iva { font-size: 11px; color: var(--gris-claro-texto); }
  .card-free-shipping {
    font-size: 11px; font-weight: 700; color: #2F855A;
    display: flex; align-items: center; gap: 4px;
  }
  .card-price-na { font-size: 14px; color: var(--gris-claro-texto); font-style: italic; }

  /* CARD ACTIONS */
  .product-card-actions { padding: 0 16px 14px; }
  .card-add-btn {
    display: block; width: 100%; padding: 9px 0;
    background: var(--azul-principal); color: var(--blanco);
    border: none; border-radius: 7px; font-size: 13px; font-weight: 700;
    cursor: pointer; transition: background 0.2s; text-align: center; text-decoration: none;
  }
  .card-add-btn:hover { background: var(--azul-medio); color: var(--blanco); }
  .card-consult-btn {
    display: block; width: 100%; padding: 9px 0;
    background: var(--gris-fondo); color: var(--azul-principal);
    border: 1px solid var(--gris-borde); border-radius: 7px;
    font-size: 13px; font-weight: 700; cursor: pointer;
    transition: all 0.2s; text-align: center; text-decoration: none;
  }
  .card-consult-btn:hover { background: var(--azul-claro); border-color: var(--azul-principal); }

  /* EMPTY STATE */
  .products-empty {
    grid-column: 1 / -1; text-align: center;
    padding: 60px 24px; background: var(--blanco);
    border: 1px solid var(--gris-borde); border-radius: 12px;
  }
  .products-empty-icon { font-size: 52px; margin-bottom: 12px; opacity: 0.35; }
  .products-empty h2 { font-size: 18px; font-weight: 800; color: var(--azul-principal); margin-bottom: 6px; }
  .products-empty p { font-size: 14px; color: var(--gris-claro-texto); }

  /* PAGINATION */
  .products-pagination {
    display: flex; justify-content: center; padding: 8px 0;
  }

  /* LIST VIEW */
  .products-grid.list-view {
    grid-template-columns: 1fr;
  }
  .products-grid.list-view .product-card {
    flex-direction: row;
  }
  .products-grid.list-view .product-card-image {
    width: 180px; min-width: 180px; aspect-ratio: auto; height: 180px;
    border-right: 1px solid var(--gris-borde); border-radius: 0;
  }
  .products-grid.list-view .product-card-info {
    flex-direction: column; justify-content: center;
  }
  .products-grid.list-view .card-name {
    -webkit-line-clamp: 3; font-size: 15px;
  }
  .products-grid.list-view .card-price-main { font-size: 20px; }
  .products-grid.list-view .product-card-actions { padding: 14px 16px 14px 0; display: flex; align-items: flex-end; }

  /* RESPONSIVE */
  @media (max-width: 1100px) {
    .products-page-layout { grid-template-columns: 240px 1fr; }
    .products-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 768px) {
    .products-page-layout { grid-template-columns: 1fr; }
    .filters-sidebar { position: static; }
    .products-grid { grid-template-columns: repeat(2, 1fr); }
    .products-grid.list-view .product-card { flex-direction: column; }
    .products-grid.list-view .product-card-image { width: 100%; height: auto; aspect-ratio: 1; border-right: none; border-bottom: 1px solid var(--gris-borde); border-radius: 0; }
    .mobile-filters-toggle {
      display: flex; align-items: center; gap: 8px;
      padding: 10px 16px; background: var(--blanco);
      border: 1px solid var(--gris-borde); border-radius: 8px;
      font-size: 13px; font-weight: 700; color: var(--azul-principal);
      cursor: pointer; margin-bottom: 8px;
    }
    .filters-sidebar { display: none; }
    .filters-sidebar.open { display: block; }
  }
  @media (max-width: 480px) {
    .products-grid { grid-template-columns: 1fr; }
    .results-header { flex-direction: column; align-items: flex-start; }
  }
</style>
@endpush

@section('content')

    {{-- PAGE HEADER --}}
    <div class="products-page-header">
        <div class="container">
            <nav class="products-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('index') }}">Inicio</a>
                <span class="breadcrumb-sep">/</span>
                @if(request()->has('category'))
                    <a href="{{ route('products.index') }}">Productos</a>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-cur">{{ request()->category }}</span>
                @elseif(request()->has('brand'))
                    <a href="{{ route('products.index') }}">Productos</a>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-cur">{{ request()->brand }}</span>
                @elseif(request()->has('search'))
                    <a href="{{ route('products.index') }}">Productos</a>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-cur">Búsqueda: "{{ request()->search }}"</span>
                @else
                    <span class="breadcrumb-cur">Todos los Productos</span>
                @endif
            </nav>
        </div>
    </div>

    <section class="products-page">
        <div class="container">

            {{-- Mobile filters toggle --}}
            <button class="mobile-filters-toggle d-md-none" id="mobile-filters-toggle" type="button" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .354.854l-4.5 4.5A.5.5 0 0 1 9.5 6.5v3.793l-3 3V6.5a.5.5 0 0 1-.146-.354L1.146 1.854A.5.5 0 0 1 1.5 1.5z"/>
                </svg>
                Filtros
            </button>

            <div class="products-page-layout">

                {{-- ===== SIDEBAR FILTERS ===== --}}
                <aside class="filters-sidebar" id="filters-sidebar">
                    <div class="filters-sidebar-header">
                        <span class="filters-sidebar-title">Filtros</span>
                        @if(request()->hasAny(['category','brand','subcategory','childcategory','range','search']))
                            <a href="{{ route('products.index') }}" class="filters-clear-btn">Limpiar</a>
                        @endif
                    </div>

                    {{-- CATEGORIES --}}
                    <div class="filter-group">
                        <button class="filter-group-header" type="button" data-filter-toggle="categories">
                            Categorías
                            <svg class="filter-group-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="filter-group-body" id="filter-categories">
                            @foreach ($categories as $category)
                                <a class="filter-option {{ request('category') == $category->slug ? 'active' : '' }}"
                                   href="{{ route('products.index', ['category' => $category->slug]) }}">
                                    <span class="filter-option-dot"></span>
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- BRANDS --}}
                    <div class="filter-group">
                        <button class="filter-group-header" type="button" data-filter-toggle="brands">
                            Marca
                            <svg class="filter-group-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="filter-group-body" id="filter-brands">
                            @foreach ($brands as $brand)
                                <a class="filter-option {{ request('brand') == $brand->name ? 'active' : '' }}"
                                   href="{{ route('products.index', ['brand' => $brand->name]) }}">
                                    <span class="filter-option-dot"></span>
                                    {{ $brand->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- PRICE RANGE --}}
                    <div class="filter-group">
                        <button class="filter-group-header" type="button" data-filter-toggle="price">
                            Precio
                            <svg class="filter-group-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="filter-group-body" id="filter-price">
                            <form class="filter-price-form" action="{{ url()->current() }}">
                                @foreach (request()->query() as $key => $value)
                                    @if($key != 'range')
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
                                    @endif
                                @endforeach
                                <div class="filter-slider-wrap">
                                    <input type="hidden" id="slider_range" name="range" class="flat-slider" />
                                </div>
                                <button type="submit" class="filter-apply-btn">Aplicar Filtro</button>
                            </form>
                        </div>
                    </div>

                </aside>

                {{-- ===== MAIN CONTENT ===== --}}
                <div class="products-main">

                    {{-- RESULTS HEADER --}}
                    <div class="results-header">
                        <p class="results-count">
                            <strong>{{ $products->total() }}</strong> productos encontrados
                        </p>
                        <div class="results-header-right">
                            <div class="view-toggle" role="group" aria-label="Vista">
                                <button class="view-btn {{ !session()->has('product_list_style') || session('product_list_style') == 'grid' ? 'active' : '' }}"
                                        id="btn-view-grid" type="button" title="Vista cuadrícula" data-view="grid">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5zm8 0A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5zm-8 8A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5zm8 0A1.5 1.5 0 0 1 10.5 9h3A1.5 1.5 0 0 1 15 10.5v3A1.5 1.5 0 0 1 13.5 15h-3A1.5 1.5 0 0 1 9 13.5z"/>
                                    </svg>
                                </button>
                                <button class="view-btn {{ session('product_list_style') == 'list' ? 'active' : '' }}"
                                        id="btn-view-list" type="button" title="Vista lista" data-view="list">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- PRODUCTS GRID --}}
                    <div class="products-grid {{ session('product_list_style') == 'list' ? 'list-view' : '' }}" id="products-grid">

                        @forelse ($products as $product)
                        @php
                            $defaultCombination = $product->combinations->where('is_default', 1)->first();
                            $showCombination = $defaultCombination ?: null;
                            $combinationId   = $showCombination ? $showCombination->id : '';
                            $productModel    = $showCombination ? ($showCombination->model ?? $product->productModel) : $product->productModel;
                            $sku             = $showCombination ? $showCombination->sku : $product->sku;
                            $qty             = $showCombination
                                ? $showCombination->qty
                                : ($product->qty_personalizated == 0 ? $product->qty_aspel : $product->qty);

                            $today = date('Y-m-d');
                            if ($showCombination) {
                                $normalPrice = $showCombination->price;
                                $offerPrice  = $showCombination->offert_price;
                                $offerStart  = $showCombination->offer_start_date;
                                $offerEnd    = $showCombination->offer_end_date;
                                $hasDiscount = $offerPrice > 0
                                    && $offerStart && $offerEnd
                                    && $today >= $offerStart && $today <= $offerEnd
                                    && $offerPrice < $normalPrice;
                            } else {
                                $normalPrice = $product->price_personalizated == 1
                                    ? $product->price
                                    : ($product->aspel_price ?? $product->price);
                                $offerPrice  = $product->price_offert_personalizated == 1
                                    ? $product->offert_price
                                    : ($product->aspel_offert_price ?? $product->offert_price);
                                $offerStart  = $product->offer_start_date;
                                $offerEnd    = $product->offer_end_date;
                                $hasDiscount = $offerPrice > 0
                                    && $offerStart && $offerEnd
                                    && $today >= $offerStart && $today <= $offerEnd
                                    && $offerPrice < $normalPrice;
                            }
                            $finalPrice = $hasDiscount ? $offerPrice : $normalPrice;
                            $discountPct = ($hasDiscount && $normalPrice > 0)
                                ? round((($normalPrice - $offerPrice) / $normalPrice) * 100)
                                : 0;
                            $avgRating   = $product->reviews->avg('rating');
                            $reviewCount = $product->reviews->count();
                            $hoverImage  = $product->productImageGalleries[0]->image ?? null;
                        @endphp

                        <div class="product-card" itemscope itemtype="http://schema.org/Product">

                            {{-- IMAGE --}}
                            <a class="product-card-image" href="{{ route('product-detail', $product->slug) }}" aria-label="{{ $product->name }}">
                                <img class="img-main" src="{{ asset($product->thumb_image) }}"
                                     alt="{{ $product->name }}" loading="lazy" itemprop="image" />
                                @if($hoverImage)
                                    <img class="img-hover" src="{{ asset($hoverImage) }}"
                                         alt="{{ $product->name }}" loading="lazy" />
                                @endif

                                {{-- TYPE BADGES --}}
                                @switch($product->product_type)
                                    @case('new_arrival')
                                        <span class="card-badge card-badge--new">Nuevo</span>
                                        @break
                                    @case('top_product')
                                        <img class="card-hot-img" src="{{ asset('frontend/images/logo/hot_sale.png') }}" alt="Hot Sale" />
                                        <span class="card-badge card-badge--hot">Hot Sale</span>
                                        @break
                                    @case('best_product')
                                        <span class="card-badge card-badge--best">Más Vendido</span>
                                        @break
                                @endswitch

                                {{-- DISCOUNT BADGE --}}
                                @if($hasDiscount && $discountPct > 0)
                                    <span class="card-badge--off">-{{ $discountPct }}%</span>
                                @endif
                            </a>

                            {{-- INFO --}}
                            <div class="product-card-info">
                                <a class="card-category" href="#" itemprop="category">{{ $product->category->name }}</a>

                                <a class="card-name" href="{{ route('product-detail', $product->slug) }}"
                                   itemprop="name">{{ $product->name }}</a>

                                @if($sku)
                                    <span class="card-sku">SKU: {{ $sku }}</span>
                                @endif

                                {{-- RATING --}}
                                @if($reviewCount > 0)
                                    <div class="card-rating">
                                        <div class="card-stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star{{ $i <= $avgRating ? '' : ($i - $avgRating < 1 ? '-half-alt' : '') }} fa-star{{ $i > ceil($avgRating) ? '' : '' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="card-rating-count">({{ $reviewCount }})</span>
                                    </div>
                                @endif

                                {{-- PRICE --}}
                                @if($finalPrice)
                                    {{-- STOCK --}}
                                    @if($qty > 0)
                                        <span class="card-stock card-stock--in" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                            <meta itemprop="availability" content="http://schema.org/InStock">
                                            ✓ Disponible
                                        </span>
                                    @else
                                        <span class="card-stock card-stock--out" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                            <meta itemprop="availability" content="http://schema.org/OutOfStock">
                                            Agotado
                                        </span>
                                    @endif

                                    @auth
                                        {{-- Usuario autenticado: ve el precio completo --}}
                                        <div class="card-price-block" itemscope itemtype="http://schema.org/Offer">
                                            <meta itemprop="priceCurrency" content="MXN">
                                            @if($hasDiscount)
                                                <div class="card-price-del">
                                                    {{ $settings->currency_icon }}{{ number_format($normalPrice, 2, '.', ',') }} MXN
                                                </div>
                                            @endif
                                            <div class="card-price-main">
                                                <span itemprop="price" content="{{ $finalPrice }}">
                                                    {{ $settings->currency_icon }}{{ number_format($finalPrice, 2, '.', ',') }} MXN
                                                </span>
                                                @if($hasDiscount && $discountPct > 0)
                                                    <span class="card-price-off">-{{ $discountPct }}% OFF</span>
                                                @endif
                                            </div>
                                            <div class="card-price-iva">IVA incluido</div>
                                        </div>
                                        @if($shippingRules && $finalPrice >= $shippingRules->min_cost)
                                            <div class="card-free-shipping">
                                                <i class="fas fa-shipping-fast"></i> Envío Gratis
                                            </div>
                                        @endif
                                    @else
                                        {{-- Guest: precio oculto --}}
                                        <div class="price-hidden-badge">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                            Precio disponible al iniciar sesión
                                        </div>
                                        <a href="{{ route('login') }}" class="btn-ver-precio">Ver precio</a>
                                    @endauth
                                @else
                                    <span class="card-stock card-stock--consult">Requiere Asesoría</span>
                                    <div class="card-price-na">Solicita tu cotización</div>
                                @endif
                            </div>

                            {{-- ACTIONS --}}
                            <div class="product-card-actions">
                                @if($finalPrice)
                                    @auth
                                        <form class="shopping-cart-form">
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="combination_id" value="{{ $combinationId }}">
                                            <input type="hidden" name="brand_name" itemprop="brand" content="{{ $product->brand->name }}" value="{{ $product->brand->name }}">
                                            <input type="hidden" name="sku" value="{{ $sku }}">
                                            <input type="hidden" name="productModel" value="{{ $productModel ?? '' }}">
                                            <input type="hidden" name="qty" value="1" min="1" max="{{ $qty }}">
                                            <button type="submit" class="card-add-btn">
                                                <i class="fas fa-shopping-cart" style="margin-right:6px;font-size:12px;"></i>
                                                Agregar al Carrito
                                            </button>
                                        </form>
                                    @else
                                        {{-- Guest: acciones alternativas --}}
                                        <div class="guest-actions">
                                            <a href="{{ route('contact') }}" class="btn-cotizar">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                Cotizar
                                            </a>
                                            <a href="https://wa.link/f28njw" target="_blank" class="btn-whatsapp">
                                                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                                                WhatsApp
                                            </a>
                                        </div>
                                    @endauth
                                @else
                                    <a href="{{ route('contact') }}" class="card-consult-btn">Requiere Asesoría</a>
                                @endif
                            </div>

                        </div>

                        @empty
                        <div class="products-empty">
                            <div class="products-empty-icon">📦</div>
                            <h2>Sin productos</h2>
                            <p>Estamos trabajando para brindarte los mejores productos. Intenta con otra búsqueda.</p>
                        </div>
                        @endforelse

                    </div>

                    {{-- PAGINATION --}}
                    @if($products->hasPages())
                        <div class="products-pagination">
                            {{ $products->withQueryString()->links() }}
                        </div>
                    @endif

                </div>{{-- /products-main --}}
            </div>{{-- /products-page-layout --}}
        </div>{{-- /container --}}
    </section>

@endsection

@push('scripts')
<script>
(function () {
    // Price range slider
    @php
        if(request()->has('range') && request()->range != ''){
            $price = explode(';', request()->range);
            $from = $price[0];
            $to   = $price[1];
        } else {
            $from = 0;
            $to   = 8000;
        }
    @endphp
    jQuery(function () {
        jQuery('#slider_range').flatslider({
            min: 0, max: 10000, step: 100,
            values: [{{ $from }}, {{ $to }}],
            range: true,
            einheit: '{{ $settings->currency_icon }}'
        });
    });

    // View toggle (grid / list)
    document.querySelectorAll('.view-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var view = this.dataset.view;
            document.querySelectorAll('.view-btn').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            var grid = document.getElementById('products-grid');
            if (view === 'list') {
                grid.classList.add('list-view');
            } else {
                grid.classList.remove('list-view');
            }
            $.ajax({
                method: 'GET',
                url: '{{ route('change-product-list-view') }}',
                data: { style: view }
            });
        });
    });

    // Filter group collapse
    document.querySelectorAll('.filter-group-header').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = 'filter-' + this.dataset.filterToggle;
            var body = document.getElementById(targetId);
            if (!body) return;
            var hidden = body.classList.toggle('hidden');
            this.classList.toggle('collapsed', hidden);
        });
    });

    // Mobile filters toggle
    var mobileToggle = document.getElementById('mobile-filters-toggle');
    var sidebar = document.getElementById('filters-sidebar');
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            var expanded = sidebar.classList.contains('open');
            this.setAttribute('aria-expanded', expanded);
        });
    }
})();
</script>
@endpush

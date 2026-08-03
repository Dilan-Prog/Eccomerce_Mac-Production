<section id="wsus__electronic2" style="margin-top: 10px; background: white;">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="home-section-header">
                    {{-- Hacer dinamico --}}
                    <h2 class="home-section-title">Lo Mejor De La Industria</h3>
                </div>
            </div>
        </div>
        <div class="cps-carousel" id="wsus__electronic2_slider-one">
            <button type="button" class="cps-arrow cps-arrow--prev" aria-label="Anterior">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="cps-track">
            @foreach ($categoryProductsSectionsOne as $product)
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

                <div class="cps-slide">
                    <div class="product-card" itemscope itemtype="http://schema.org/Product">

                        {{-- IMAGE --}}
                        <a class="product-card-image" href="{{ route('product-detail', $product->slug) }}" aria-label="{{ $product->name }}">
                            <x-responsive-product-image :product="$product" variant="card" class="img-main" itemprop="image" :alt="$product->name" loading="lazy" />
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
                                            <i class="fas fa-star{{ $i <= $avgRating ? '' : '-half-alt' }}"></i>
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
                </div>
            @endforeach
            </div>

            <button type="button" class="cps-arrow cps-arrow--next" aria-label="Siguiente">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('#wsus__electronic2_slider-one').forEach(function (carousel) {
            var track = carousel.querySelector('.cps-track');
            var prevBtn = carousel.querySelector('.cps-arrow--prev');
            var nextBtn = carousel.querySelector('.cps-arrow--next');
            if (!track) {
                return;
            }

            function step() {
                var slide = track.querySelector('.cps-slide');
                return slide ? slide.getBoundingClientRect().width + 16 : track.clientWidth;
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    track.scrollBy({ left: -step(), behavior: 'smooth' });
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    track.scrollBy({ left: step(), behavior: 'smooth' });
                });
            }
        });
    })();
</script>
@endpush

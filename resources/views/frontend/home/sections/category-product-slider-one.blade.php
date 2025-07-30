<section id="wsus__electronic2">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="wsus__section_header">
                    {{-- Hacer dinamico --}}
                    <h3>Lo Mejor De La Industria</h3>

                </div>
            </div>
        </div>
        <div class="row" id="wsus__electronic2_slider-one">
            @foreach ($categoryProductsSectionsOne as $product)
                <div class="col-xl-3 col-sm-6 col-lg-4">
                    <div class="wsus__product_item">

                        @switch($product->product_type)
                            @case('new_arrival')
                                <span class="wsus__new wsus__new--new-arrival" style="background: #00468c">Nuevo

                                </span>
                                @break
                            @case('featured_product')
                                <span class="wsus__new" style="display: none">

                                </span>
                                @break
                            @case('top_product')
                                <div id="hot-sale_wsus_new" style="position: absolute; top: 10px; right: 10px; z-index: 2; width: 70px;">
                                    <img src="{{asset('frontend/images/logo/hot_sale.png')}}" alt="Promocion de Hot Sale Industrial" >
                                </div>
                                <span class="wsus__new wsus__new--top-product" style="background: #FF0000">Hot sale <i class="fa fa-fire" aria-hidden="true"></i>
                                </span>
                                @break
                            @case('best_product')
                                <span class="wsus__new wsus__new--best-product" style="background: #fa7c04">Más Vendido

                                </span>
                                @break
                            @default

                        @endswitch

                        <a class="wsus__pro_link" href="{{ route('product-detail', $product->slug) }}">
                            <img src="{{ asset($product->thumb_image) }}" alt="product" class="img_1"
                                loading="lazy" />
                            <img src="
                            @if (isset($product->productImageGalleries[0]->image)) {{ asset($product->productImageGalleries[0]->image) }}
                            @else
                                {{ asset($product->thumb_image) }} @endif
                            "
                                class="img_2" loading="lazy" />
                        </a>
                        <div class="wsus__product_details">
                            <a class="wsus__category"
                                href="{{ route('product-detail', $product->slug) }}">{{ $product->category->name }}</a>
                                <p class="">
                                @if ($product->price)
                                    @if ($product->qty > 0)
                                        <p class="wsus__stock_area"><span class="in_stock">Disponibles</span></p>
                                    @elseif ($product->qty === 0)
                                        <p class="wsus__stock_area"><span class="in_stock">Agotado</span></p>
                                    @endif
                                    <p class="wsus__pro_rating">
                                        @php
                                            $averageRating = $product->reviews->avg('rating'); // Promedio de las calificaciones
                                            $reviewCount = $product->reviews->count(); // Número total de reviews
                                        @endphp

                                        @if ($reviewCount > 0)
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star{{ $i <= $averageRating ? '' : '-half-alt' }}" aria-hidden="true"></i>
                                            @endfor
                                            <span>({{ $reviewCount }} Opinion)</span>
                                        @else
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="far fa-star" aria-hidden="true"></i> <!-- Estrellas vacías -->
                                            @endfor
                                        @endif
                                    </p>

                                </p>


                                    <a class="wsus__pro_name"
                                        href="{{ route('product-detail', $product->slug) }}">{{ $product->name }}</a>
                                    @if (checkDiscount($product))
                                        <p class="wsus__price"
                                            content="{{ $settings->currency_icon }}{{ $product->offert_price }} MXN <del>{{ $settings->currency_icon }}{{ $product->price }} MXN</del>">
                                            {{ $settings->currency_icon }}{{ $product->offert_price }} MXN
                                            <del>{{ $settings->currency_icon }}{{ $product->price }} MXN</del></p>
                                            <span class="mdn_iva">IVA INCLUIDO</span>
                                            <p>
                                                @if ($product->offert_price >= $shippingRules->min_cost)
                                                    <span class="free-shipping-text"><i class="fas fa-shipping-fast"></i> Envío Gratis </span>
                                                @endif
                                            </p>
                                    @else
                                        <p class="wsus__price"
                                            content="{{ $settings->currency_icon }}{{ number_format($product->price, 2, '.', ',') }} MXN">
                                            {{ $settings->currency_icon }}{{ number_format($product->price, 2, '.', ',') }}
                                            MXN</p>
                                            <span class="mdn_iva">IVA INCLUIDO</span>
                                            <p>
                                                @if ($product->price >= $shippingRules->min_cost)
                                                    <span class="free-shipping-text"><i class="fas fa-shipping-fast"></i> Envío Gratis </span>
                                                @endif
                                            </p>
                                    @endif
                                @else
                                    <p class="wsus__stock_area"><span class="in_stock">Disponible</span></p>
                                    <a class="wsus__pro_name"
                                        href="{{ route('product-detail', $product->slug) }}">{{ $product->name }}</a>

                                    <p class="wsus__price">N/A +<small> Requiere Asesoria</small> </p>
                                @endif

                            <form class="shopping-cart-form">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="brand_name" value="{{ $product->brand->name }}">
                                <input type="hidden" name="sku" value="{{ $product->sku }}">
                                <input type="hidden" name="productModel" value="{{ $product->productModel }}">
                                @if ($product->price)
                                    <button type="submit" class="add_cart" href="#">Agregar al Carrito</button>
                                @else
                                    <a class="add_cart2" href="{{ route('contact') }}">Requiere Asesoria</a>
                                @endif

                                <input name="qty" type="hidden" min="1" max="100" value="1" />
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

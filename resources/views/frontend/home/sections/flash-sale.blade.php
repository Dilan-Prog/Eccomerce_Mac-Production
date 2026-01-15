
    <section id="wsus__flash_sell" class="wsus__flash_sell_2">
        <div class=" container">
            <div class="product-solicited-site">
                <div class="row">
                    <div class="col-8 col-sm-9 col-md-9 col-lg-9 col-xl-9">
                        <h4 style="color: #ffffff">Productos M&aacute;s Vendidos</h4>
                    </div>
                    <div class="col-4 col-sm-3 col-md-3 col-lg-3 col-xl-3">
                        <button><a href="{{route('flash-sale')}}">M&aacute;s Productos <i class="fas fa-plus"></i></a></button>
                    </div>
                </div>
            </div>

            <div class="row flash_sell_slider">


                @foreach ($flashSaleItems as $item )

                @php
                    $product = \App\Models\Product::find($item->product_id); // Producto ya cargado con las relaciones
                    // Lógica de precio base y oferta considerando aspel_price y price_offert_personalizated
                    $basePrice = $product->price_personalizated == 1
                        ? $product->price
                        : ($product->aspel_price ?? $product->price);
                    $baseOfferPrice = $product->price_offert_personalizated == 1
                        ? $product->offert_price
                        : ($product->aspel_offert_price ?? $product->offert_price);
                    $hasDiscount = checkDiscount($product);
                    $price = $hasDiscount ? $baseOfferPrice : $basePrice;
                @endphp
                <div class="col-xl-3 col-sm-6 col-lg-4">
                    <div class="wsus__product_item">
                        @switch($product->product_type)
                            @case('new_arrival')
                                <span id class="wsus__new wsus__new--new-arrival" style="background: #00468c">Nuevo

                                </span>
                                @break
                            @case('featured_product')

                                <span class="wsus__new" style="display: none">Hot sale
                                </span>
                                @break
                            @case('top_product')
                                <div id="hot-sale_wsus_new" style="position: absolute; top: 10px; right: 10px; z-index: 1; width: 70px;">
                                    <img src="{{asset('frontend/images/logo/hot_sale.png')}}" alt="Promocion de Hot Sale Industrial" >
                                </div>
                                <span class="wsus__new wsus__new--top-product" style="background: #FF0000">Hot sale
                                </span>
                                @break
                            @case('best_product')
                                <span class="wsus__new wsus__new--best-product" style="background: #fa7c04">Más Vendido

                                </span>
                                @break
                            @default

                        @endswitch




                        @if (checkDiscount($product))

                            {{-- <span class="wsus__minus">-{{calculatedDiscountPercent($product->price, $product->offert_price)}}%</span> --}}

                        @endif
                        <a class="wsus__pro_link" href="{{route('product-detail', $product->slug)}}">
                            <img src="{{asset($product->thumb_image)}}" alt="product" class="img_1" loading="lazy" />
                            <img src="
                            @if (isset($product->productImageGalleries[0]->image))
                                {{asset($product->productImageGalleries[0]->image)}}
                            @else
                                {{asset($product->thumb_image)}}
                            @endif
                            " class="img_2" loading="lazy" />
                        </a>

                        <div class="wsus__product_details">

                            <a class="wsus__category" href="{{route('product-detail', $product->slug)}}">{{$product->category->name}}</a>
                            <p class="">
                                @php
                                    $stockQty = ($product->qty_personalizated == 0 ? $product->qty_aspel : $product->qty);
                                @endphp

                                @if ($stockQty > 0)
                                <p class="wsus__stock_area"><span class="in_stock">Disponibles</span></p>
                                @elseif ($stockQty === 0)
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

                            <a class="wsus__pro_name" href="{{route('product-detail', $product->slug)}}">{{$product->name}}</a>
                            

                @if ($basePrice)
                    <p itemscope itemtype="http://schema.org/Offer">
                        <meta itemprop="priceCurrency" content="MXN">
                        <span class="wsus__price" itemprop="price" content="{{ $price }}">
                            @if($hasDiscount)
                                <del>{{ $settings->currency_icon }}{{ number_format($basePrice, 2, '.', ',') }} MXN</del>
                            @endif
                            <span style="color: #333; font-weight: 500;">
                                {{ $settings->currency_icon }}{{ number_format($price, 2, '.', ',') }} MXN
                                @if($hasDiscount)
                                    <span style="color: #00a650; font-weight: 600;">
                                        {{ calculatedDiscountPercent($basePrice, $baseOfferPrice) }}% OFF
                                    </span>
                                @endif
                            </span>
                        </span>
                        <span class="mdn_iva">IVA INCLUIDO</span>
                    </p>
                    <p>
                        @if ($price >= $shippingRules->min_cost)
                            <span class="free-shipping-text"><i class="fas fa-shipping-fast"></i> Envío Gratis </span>
                        @endif
                    </p>
                @else
                    <p class="wsus__price">N/A +<small> Requiere Asesoria</small> </p>
                @endif

                <form class="shopping-cart-form">
                    <input type="hidden" name="product_id" value="{{$product->id}}">
                    <input type="hidden" name="brand_name" value="{{ $product->brand->name }}">
                    <input type="hidden" name="sku" value="{{$product->sku}}">
                    <input type="hidden" name="productModel" value="{{$product->productModel}}">
                    @if ($basePrice)
                        <button type="submit" class="add_cart" href="#">Agregar al Carrito</button>
                    @else
                        <a class="add_cart2" href="{{route('contact')}}">Requiere Asesoria</a>
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

    @push('scripts')

    <script>

        $(document).ready(function(){


                simplyCountdown('.simply-countdown-one', {
                    year: {{date('Y', strtotime($flashSaleDate->end_date))}},
                    month:{{date('m', strtotime($flashSaleDate->end_date))}},
                    day: {{date('d', strtotime($flashSaleDate->end_date))}},

            });
        })
    </script>

    @endpush

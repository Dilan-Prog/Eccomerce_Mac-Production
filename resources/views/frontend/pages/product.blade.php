@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Todos Los Productos
@endsection

@section('content')
    <!--============================
        PRODUCT PAGE START
    ==============================-->
    <section id="wsus__product_page">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    {{-- banner --}}
                </div>
                <div class="col-xl-3 col-lg-4">
                    <div class="wsus__sidebar_filter ">
                        <p>filtros</p>
                        <span class="wsus__filter_icon">
                            <i class="far fa-minus" id="minus"></i>
                            <i class="far fa-plus" id="plus"></i>
                        </span>
                    </div>
                    <div class="wsus__product_sidebar" id="sticky_sidebar">
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Todas las Categorias
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show"
                                    aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <ul>
                                            @foreach ($categories as $category)

                                            <li><a href="{{route('products.index', ['category' => $category->slug])}}">{{$category->name}}</a></li>
                                            @endforeach

                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Precio
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse show"
                                    aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="price_ranger">
                                            <form action="{{url()->current()}}">
                                                @foreach (request()->query() as $key => $value)
                                                @if($key != 'range')
                                                    <input type="hidden" name="{{$key}}" value="{{$value}}" />
                                                @endif
                                                @endforeach
                                                <input type="hidden" id="slider_range" name="range" class="flat-slider" />
                                                <button type="submit" class="common_btn">Filtros</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree3">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseThree3" aria-expanded="false"
                                        aria-controls="collapseThree">
                                        Marca
                                    </button>
                                </h2>
                                <div id="collapseThree3" class="accordion-collapse collapse show"
                                    aria-labelledby="headingThree3" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <ul>
                                            @foreach ($brands  as $brand)

                                            <li><a href="{{route('products.index', ['brand' => $brand->name])}}">{{$brand->name}}</a></li>
                                            @endforeach

                                        </ul>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="row">
                        <div class="col-xl-12 d-none d-md-block mt-md-4 mt-lg-0">
                            <div class="wsus__product_topbar">
                                <div class="wsus__product_topbar_left">
                                    <div class="nav nav-pills" id="v-pills-tab" role="tablist"
                                        aria-orientation="vertical">
                                        <button class="nav-link active: {{session()->has('product_list_style') && session()->get('product_list_style') == 'grid' ? 'active' : ''}} {{!session()->has('product_list_style') ? 'active' : ''}} list-view" data-id="grid" id="v-pills-home-tab" data-bs-toggle="pill"
                                            data-bs-target="#v-pills-home" type="button" role="tab"
                                            aria-controls="v-pills-home" aria-selected="true">
                                            <i class="fas fa-th"></i>
                                        {{-- </button>
                                        <button class="nav-link list-view {{session()->has('product_list_style') && session()->get('product_list_style') == 'list' ? 'active' : ''}}" data-id="list" id="v-pills-profile-tab" data-bs-toggle="pill"
                                            data-bs-target="#v-pills-profile" type="button" role="tab"
                                            aria-controls="v-pills-profile" aria-selected="false">
                                            <i class="fas fa-list-ul"></i>
                                        </button> --}}
                                    </div>

                                </div>

                            </div>
                        </div>
                        <div class="tab-content" id="v-pills-tabContent">
                            <div class="tab-pane fade {{session()->has('product_list_style') && session()->get('product_list_style') == 'grid' ? 'show active' : ''}} {{!session()->has('product_list_style') ? 'show active' : ''}}" id="v-pills-home" role="tabpanel"
                                aria-labelledby="v-pills-home-tab">
                                <div class="row">
                                    

                                    @foreach ($products as $product )
                                    @php
                                        $defaultCombination = $product->combinations->where('is_default', 1)->first();
                                        $showCombination = $defaultCombination ?: null;
                                        $price = $showCombination ? ($showCombination->offert_price ?? $showCombination->price) : ($product->offert_price ?? $product->price);
                                        $qty = $showCombination ? $showCombination->qty : $product->qty;
                                        $sku = $showCombination ? $showCombination->sku : $product->sku;
                                    @endphp

                                    {{-- <div class="col-xl-4 col-sm-6 col-lg-4">
                                        <div class="wsus__product_item">
                                            <span class="wsus__new">{{productType($product->product_type)}}</span>
                                            @if (checkDiscount($product))

                                            <span class="wsus__minus">-{{calculatedDiscountPercent($product->price, $product->offert_price)}}%</span>

                                            @endif
                                            <a class="wsus__pro_link" href="{{route('product-detail', $product->slug)}}">
                                                <img src="{{asset($product->thumb_image)}}" alt="product" class="img-fluid w-100 img_1" />
                                                <img src="
                                                @if (isset($product->productImageGalleries[0]->image))
                                                    {{asset($product->productImageGalleries[0]->image)}}
                                                @else
                                                    {{asset($product->thumb_image)}}
                                                @endif
                                                " class="img-fluid w-100 img_2" />
                                            </a>
                                            <div class="wsus__product_details">
                                                <a class="wsus__category" href="#">{{$product->category->name}}</a>
                                                <p class="wsus__pro_rating">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star-half-alt"></i>
                                                    <span>(133 review)</span>
                                                </p>
                                                <a class="wsus__pro_name" href="{{route('product-detail', $product->slug)}}">{{$product->name}}</a>
                                                @if (checkDiscount($product))
                                                    <p class="wsus__price">{{$settings->currency_icon}}{{$product->offert_price}} MXN <del>{{$settings->currency_icon}}{{$product->price}} MXN</del></p>
                                                @else
                                                    <p class="wsus__price">{{$settings->currency_icon}}{{$product->price}} </p>
                                                @endif
                                                <form class="shopping-cart-form">
                                                    <input type="hidden" name="product_id" value="{{$product->id}}">
                                                    <input type="hidden" name="brand_name" value="{{ $product->brand->name }}">
                                                    <button type="submit" class="add_cart" href="#">Agregar al Carrito</button>

                                                    <input name="qty" type="hidden" min="1" max="100" value="1" />
                                                </form>
                                            </div>
                                        </div>
                                    </div> --}}
                                    <div class="col-xl-4 col-sm-6 col-lg-4">


                                        <div class="wsus__product_item" itemscope itemtype="http://schema.org/Product">

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
                                                        <div id="hot-sale_wsus_new" style="position: absolute; top: 10px; right: 10px; z-index: 1; width: 70px;">
                                                            <img src="{{asset('frontend/images/logo/hot_sale.png')}}" alt="Promocion de Hot Sale Industrial"style="width: 70px;">
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
                                                <img src="{{asset($product->thumb_image)}}" alt="product" class="img-fluid w-100 img_1" loading="lazy" />
                                                <img src="
                                                @if (isset($product->productImageGalleries[0]->image))
                                                    {{asset($product->productImageGalleries[0]->image)}}
                                                @else
                                                    {{asset($product->thumb_image)}}
                                                @endif
                                                " class="img-fluid w-100 img_2" loading="lazy" />
                                            </a>
                                            <div class="wsus__product_details">


                                                <a class="wsus__category" href="#" itemprop="category">{{$product->category->name}}</a>




                                                @if ($product->price)


                                                    @if ($qty > 0)
                                                        <p class="wsus__stock_area"><span class="in_stock" itemprop="offers" itemtype="http://schema.org/Offer"><meta itemprop="availability" content="http://schema.org/InStock">Disponibles</span></p>
                                                    @elseif ($qty === 0)
                                                        <p class="wsus__stock_area"><span class="in_stock" itemprop="offers" itemtype="http://schema.org/Offer"><meta itemprop="availability" content="http://schema.org/OutOfStock">Agotado</span></p>
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


                                                    <a class="wsus__pro_name" href="{{route('product-detail', $product->slug)}}" itemprop="name" content="{{$product->name}}">{{$product->name}}</a>

                                                    @php
    $defaultCombination = $product->combinations->where('is_default', 1)->first();
    $showCombination = $defaultCombination ?: null;
    $combinationId = $showCombination ? $showCombination->id : '';
    $productModel = $showCombination ? ($showCombination->model ?? $product->productModel) : $product->productModel;
    $price = $showCombination ? ($showCombination->offert_price ?? $showCombination->price) : ($product->offert_price ?? $product->price);
    $qty = $showCombination ? $showCombination->qty : $product->qty;
    $sku = $showCombination ? $showCombination->sku : $product->sku;
    $today = date('Y-m-d');

    if ($showCombination) {
        $offerPrice = $showCombination->offert_price;
        $normalPrice = $showCombination->price;
        $offerStart = $showCombination->offer_start_date;
        $offerEnd = $showCombination->offer_end_date;
        $hasDiscount = $offerPrice > 0
            && $offerStart && $offerEnd
            && $today >= $offerStart
            && $today <= $offerEnd
            && $offerPrice < $normalPrice;
    } else {
        $offerPrice = $product->offert_price;
        $normalPrice = $product->price;
        $offerStart = $product->offer_start_date;
        $offerEnd = $product->offer_end_date;
        $hasDiscount = $offerPrice > 0
            && $offerStart && $offerEnd
            && $today >= $offerStart
            && $today <= $offerEnd
            && $offerPrice < $normalPrice;
    }
    $finalPrice = $hasDiscount ? $offerPrice : $normalPrice;
@endphp

                                                    <p itemscope itemtype="http://schema.org/Offer">
                                                        <meta itemprop="priceCurrency" content="MXN">
                                                        <span class="wsus__price" itemprop="price" content="{{ $finalPrice }}">
        @if($hasDiscount)
            <del>{{ $settings->currency_icon }}{{ number_format($normalPrice, 2, '.', ',') }} MXN</del>
        @endif
        <span style="color: #333; font-weight: 500;">
            {{ $settings->currency_icon }}{{ number_format($finalPrice, 2, '.', ',') }} MXN
            @if($hasDiscount)
                <span style="color: #00a650; font-weight: 600;">
                    {{ round((($normalPrice - $offerPrice) / $normalPrice) * 100) }}% OFF
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
                                                    <p class="wsus__stock_area"><span class="in_stock" itemprop="availability" content="http://schema.org/InStock" >Requiere Asesoria</span></p>
                                                    <a class="wsus__pro_name" href="{{route('product-detail', $product->slug)}}">{{$product->name}}</a>

                                                    <p class="wsus__price">N/A +<small> Requiere Asesoria</small> </p>
                                                @endif
                                                
                                                @php
                                                    // Usar helpers personalizados para validar descuentos y fechas
                                                    $defaultCombination = $product->combinations->where('is_default', 1)->first();
                                                    $showCombination = $defaultCombination ?: null;

                                                    if ($showCombination) {
                                                        // Usa tu helper para combinaciones
                                                        $hasDiscount = checkCombinationDiscount($showCombination);
                                                        $offerPrice = $showCombination->offert_price;
                                                        $normalPrice = $showCombination->price;
                                                    } else {
                                                        // Usa tu helper para producto base
                                                        $hasDiscount = checkDiscount($product);
                                                        $offerPrice = $product->offert_price;
                                                        $normalPrice = $product->price;
                                                    }
                                                    $finalPrice = $hasDiscount ? $offerPrice : $normalPrice;
                                                @endphp

                                                <form class="shopping-cart-form">
                                                    <input type="hidden" name="product_id" value="{{$product->id}}">
                                                    <input type="hidden" name="combination_id" id="combination_id" value="{{ $combinationId }}">
                                                    <input type="hidden" name="brand_name" itemprop="brand" content="{{$product->brand->name}}" value="{{ $product->brand->name }}">
                                                    <input type="hidden" name="sku" value="{{$sku}}">
                                                    <input type="hidden" name="productModel" value="{{$productModel}}">
                                                    
                                                    @if ($product->price)
                                                        <button type="submit" class="add_cart" href="#">Agregar al Carrito</button>
                                                    @else
                                                        <a class="add_cart2" href="{{route('contact')}}">Requiere Asesoria</a>
                                                    @endif

                                                    <input name="qty" type="hidden" min="1" max="{{ $qty }}" value="1" />
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    @endforeach

                                </div>
                            </div>
                            {{-- list view --}}
                            {{-- <div class="tab-pane fade {{session()->has('product_list_style') && session()->get('product_list_style') == 'list' ? 'show active' : ''}}" id="v-pills-profile" role="tabpanel"
                                aria-labelledby="v-pills-profile-tab">
                                <div class="row">
                                    @foreach ($products as $product )
                                    <div class="col-xl-12">
                                        <div class="wsus__product_item wsus__list_view" itemscope itemtype="http://schema.org/Product">
                                            <span class="wsus__new">{{productType($product->product_type)}}</span>

                                            @if (checkDiscount($product))

                                            <span class="wsus__minus">-{{calculatedDiscountPercent($product->price, $product->offert_price)}}%</span>

                                            @endif
                                            <a class="wsus__pro_link" href="{{route('product-detail', $product->slug)}}">
                                                <img src="{{asset($product->thumb_image)}}" alt="product"
                                                    class="img-fluid w-100 img_1" loading="lazy" />
                                                <img src="
                                                @if (isset($product->productImageGalleries[0]->image))
                                                    {{asset($product->productImageGalleries[0]->image)}}
                                                @else
                                                    {{asset($product->thumb_image)}}
                                                @endif
                                                " alt="product"
                                                    class="img-fluid w-100 img_2" loading="lazy" />
                                            </a>
                                            <div class="wsus__product_details">
                                                <a class="wsus__category" href="#" itemprop="category">{{$product->category->name}} </a>

                                                @if ($product->price)
                                                    @if ($product->qty > 0)
                                                        <p class="wsus__stock_area"><span class="in_stock" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><meta itemprop="availability" content="http://schema.org/InStock">Disponible
                                                        </span>{{$product->qty}} piezas</p>
                                                        <small><strong>Ya incluye IVA</strong></small>
                                                    @elseif ($product->qty === 0)
                                                        <p class="wsus__stock_area"><span class="in_stock">Agotado</span></p>
                                                    @endif
                                                    <a class="wsus__pro_name" href="{{route('product-detail', $product->slug)}}">{{$product->name}}</a>

                                                    @if (checkDiscount($product))

                                                    <p class="wsus__price" content="{{$product->offert_price}}" itemscope itemtype="http://schema.org/Offer">
                                                        <meta itemprop="priceCurrency" content="MXN">
                                                        <span itemprop="price">
                                                        {{$settings->currency_icon}}{{ number_format($product->offert_price, 2, ',', '.') }} MXN <del>{{$settings->currency_icon}}{{ number_format($product->price, 2, '.', ',') }} MXN</del>
                                                        </span>
                                                    </p>
                                                    @else
                                                        <p class="wsus__price" content="{{$product->price}}" itemscope itemtype="http://schema.org/Offer">
                                                            <meta itemprop="priceCurrency" content="MXN">
                                                            <span itemprop="price">
                                                                {{$settings->currency_icon}}{{ number_format($product->price, 2, '.', ',') }}
                                                            </span>
                                                        </p>
                                                    @endif

                                                @else
                                                    <p class="wsus__stock_area"><span class="in_stock">Disponible</span></p>
                                                    <a class="wsus__pro_name" href="{{route('product-detail', $product->slug)}}">{{$product->name}}</a>

                                                    <p class="wsus__price">N/A +<small> Solicita tu Cotizacion</small> </p>
                                                @endif


                                                <p class="list_description">{{$product->short_description}}</p>
                                                    <form class="shopping-cart-form">
                                                        <input type="hidden" name="product_id" value="{{$product->id}}">
                                                        <input type="hidden" name="brand_name" value="{{ $product->brand->name }}">
                                                        <input type="hidden" name="sku" value="{{$product->sku}}">
                                                        <input type="hidden" name="model" value="{{$product->model}}">
                                                        @if ($product->price)
                                                        <button type="submit" class="add_cart" href="#">Agregar al Carrito</button>
                                                        @else
                                                        <a type="submit" class="add_cart2" href="{{route('contact')}}">Requiere Asesoria</a>
                                                        @endif
                                                        <input name="qty" type="hidden" min="1" max="100" value="1" />
                                                    </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    @if (count($products) === 0)
                    <div class="text-center mt-5">
                        <div class="card">
                            <div class="card-body">
                                <h2>Productos Vacio</h2>
                                <small>Estamos Trabajando para Brindarte los Mejores Productos</small>
                                <br>
                                <small>Intentalo mas tarde</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>



                <div class="col-xl-12 text-center">
                    <div class="mt-5" style="display:flex; justify-content:center">
                        @if ($products->hasPages())
                            {{$products->withQueryString()->links()}}
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!--============================
        PRODUCT PAGE END
    ==============================-->

@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            $('.list-view').on('click', function(){
                let style = $(this).data('id');

                $.ajax({
                    method: 'GET',
                    url: "{{route('change-product-list-view')}}",
                    data: {style: style},
                    success: function(data){

                    }
                })
            })
        })
        @php
            if(request()->has('range') && request()->range !=  ''){
                $price = explode(';', request()->range);
                $from = $price[0];
                $to = $price[1];
            }else {
                $from = 0;
                $to = 8000;
            }
        @endphp
        jQuery(function () {
        jQuery("#slider_range").flatslider({
            min: 0, max: 10000,
            step: 100,
            values: [{{$from}}, {{$to}}],
            range: true,
            einheit: '{{$settings->currency_icon}}'
        });
    });
    </script>
@endpush



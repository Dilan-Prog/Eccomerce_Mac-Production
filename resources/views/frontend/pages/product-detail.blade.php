@extends('frontend.layouts.master')

@section('canonical_URL')
    @if ($product->canonical_url)
        <link rel="canonical" href="{{ $product->canonical_url }}">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif
@endsection

@section('title')
{{$settings->site_name}} || {{$product->name}}
@endsection



@section('content')
    <section id="wsus__product_details">
        <div class="container" itemscope itemtype="http://schema.org/Product">
            {{-- Miga de pan o ruta jerarquica --}}
            <section id="wsus__breadcrumb">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <ul>
                                    <li><a href="#" onclick="window.history.back(); setTimeout(function(){ location.reload(); }, 500); return false;">Volver</a></li>
                                    <li><a href="{{route('products.index', ['category' => $product->category->slug])}}" itemprop="category" content="{{$product->category->name}}">{{$product->category->name}}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
            </section>
            {{-- Final de miga de pan o ruta jerarquica --}}

            <div class="wsus__details_bg" data-name="{{$product->name}}">
                <div class="row">
                    <div class="col-xl-8 ">
                        <div id="sticky_pro_zoom">
                            <div class="exzoom hidden" id="exzoom">
                                <div class="exzoom_img_box">

                                    <ul class='exzoom_img_ul'>
                                        <li>
                                            <div rel="schema:image" resource="{{asset($product->thumb_image)}}"></div>
                                            <img class="zoom ing-fluid w-100" itemprop="image" src="{{asset($product->thumb_image)}}" alt="{{$product->name}}"></li>
                                            {{--foreach que recorre toda la galeria de imagenes dentro de ProductGallery existente de cada producto  --}}
                                        @foreach($product->productImageGalleries as $productImage)
                                            <li><div rel="schema:additionalImage" resource="{{asset($productImage->image)}}"></div>
                                            <img class="zoom ing-fluid w-100" itemprop="additionalImage" src="{{asset($productImage->image)}}" alt="{{$product->name}}" ></li>
                                        @endforeach
                                            {{-- Final de foreach que recorre toda la galeria de imagenes dentro de ProductGallery existente de cada producto --}}
                                    </ul>
                                </div>
                                <div class="exzoom_nav" ></div>
                                <p class="exzoom_btn">
                                    <a href="javascript:void(0);" class="exzoom_prev_btn"> <i
                                            class="far fa-chevron-left"></i> </a>
                                    <a href="javascript:void(0);" class="exzoom_next_btn"> <i
                                            class="far fa-chevron-right"></i> </a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 ">
                        <div class="wsus__pro_details_text" >
                            {{-- Definicion de tipo de producto agregar otro campo que diga condicion del producto --}}
                            <span>
                                @switch($product->product_type)
                                    @case('new_arrival')
                                        <span>
                                            Nuevo{{ ($selectedCombination ? ' | ' . $selectedCombination->qty . ' piezas' : ($product->price ? ' | ' . $product->qty . ' piezas' : '')) }}
                                        </span>
                                        @break
                                    @case('featured_product')
                                        <span>
                                            Producto Favorito{{ ($selectedCombination ? ' | ' . $selectedCombination->qty . ' piezas' : ($product->price ? ' | ' . $product->qty . ' piezas' : '')) }}
                                        </span>
                                        @break
                                    @case('top_product')
                                        <span>
                                            Producto Top{{ ($selectedCombination ? ' | ' . $selectedCombination->qty . ' piezas' : ($product->price ? ' | ' . $product->qty . ' piezas' : '')) }}
                                        </span>
                                        @break
                                    @case('best_product')
                                        <span>
                                            Mas Vendido{{ ($selectedCombination ? ' | ' . $selectedCombination->qty . ' piezas' : ($product->price ? ' | ' . $product->qty . ' piezas' : '')) }}
                                        </span>
                                        @break
                                    @default
                                @endswitch
                            </span>
                            {{-- Final del tipo de producto --}}
                            {{-- Calificacion o rating del producto basado en comentarios --}}
                            <p class="wsus__pro_rating" style="color: #1e77fc;">
                                <div style="color: #1e77fc;">
                                    <span style="color: #1e77fc;" >{{ number_format($averageRating, 1) }}</span>
                                    <!-- Estrellas según el promedio -->
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= floor($averageRating))
                                            <i class="fas fa-star" aria-hidden="true"></i> <!-- Estrella completa -->
                                        @elseif ($i == ceil($averageRating) && $averageRating - floor($averageRating) >= 0.5)
                                            <i class="fas fa-star-half-alt" aria-hidden="true"></i> <!-- Estrella media -->
                                        @else
                                            <i class="far fa-star" aria-hidden="true"></i> <!-- Estrella vacía -->
                                        @endif
                                    @endfor
                                    <span style="color: #1e77fc;">( {{ count($reviews) ?? 0 }} opiniones)</span>
                                </div>
                            </p>
                            {{-- Final de Calificacion o Rating del producto basado en comentarios --}}
                            {{-- Llamadas PHP para la obtencion de variant_item_ids e iniciacion a las variantes --}}
                                    @php
                                        // Número de meses para MSI (Meses Sin Intereses)
                                        $msiMeses = 3;

                                        // Inicialización de variables de variantes y precios
                                        $variantNames = [];
                                        $sku = $product->sku;
                                        $price = $product->price;
                                        $offerPrice = $product->offert_price ?? null;

                                        // Si hay una combinación seleccionada, se actualizan los datos según la combinación
                                        if ($selectedCombination) {
                                            // Obtiene los IDs de los items de variante seleccionados
                                            $selectedIds = json_decode($selectedCombination->variants_item_ids, true);
                                            foreach ($selectedIds as $itemId) {
                                                foreach ($product->variants as $variant) {
                                                    // Busca el nombre del item de variante seleccionado
                                                    $variantItem = $variant->productVariantItems->where('id', $itemId)->first();
                                                    if ($variantItem) {
                                                        $variantNames[] = $variantItem->name;
                                                    }
                                                }
                                            }
                                            // Actualiza SKU, precio y precio de oferta según la combinación seleccionada
                                            $sku = $selectedCombination->sku ?? $sku;
                                            $price = $selectedCombination->price ?? $price;
                                            $offerPrice = $selectedCombination->offert_price ?? $offerPrice;
                                        }

                                        // Calcula el monto mensual para MSI y oferta
                                        $msiMonto = $price ? $price / $msiMeses : 0;
                                        $msioffert = $offerPrice ? $offerPrice / $msiMeses : 0;

                                        // Formatea los precios para mostrar enteros y decimales por separado
                                        $precioOffert = number_format($offerPrice, 2, '.', ',');
                                        [$entero, $decimales] = explode('.', $precioOffert);

                                        $precio = number_format($price, 2, '.', ',');
                                        [$enteroNormal, $decimalesNormal] = explode('.', $precio);

                                        // Nombre completo del producto incluyendo variantes seleccionadas
                                        $fullProductName = $product->name . (count($variantNames) ? ' ' . implode(' ', $variantNames) : '');
                                    @endphp
                            {{-- Nombre dinamico dependiendo si el producto tiene variantes o no --}}
                            <a class="title" href="javascript:;" itemprop="name" content="{{ $fullProductName }}">
                                {{ $fullProductName }}
                            </a>
                            <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                {{-- Inicio de codicion de precio, Si el producto tiene precio --}}
                                @if ($product->price)
                                        {{-- Incio de condicion de stock, Si el producto cuenta con stock --}}
                                        @php
                                            $stockQty = $selectedCombination ? $selectedCombination->qty : $product->qty;
                                        @endphp
                                        @if ($stockQty > 0)
                                            {{-- Condicion de stock, Si el producto esta en stock --}}
                                            <p class="wsus__stock_area">
                                                <span class="in_stock" itemprop="availability" content="https://schema.org/InStock">Stok Disponible</span>
                                            </p>
                                        @elseif ($stockQty === 0)
                                            {{-- Condicion de stock, Si el producto esta agotado --}}
                                            <p class="wsus__stock_area" >
                                                <span class="in_stock" itemprop="availability" content="https://schema.org/OutOfStock">Agotado</span>
                                            </p>
                                        @endif
                                         
                                        {{-- Final de llamadas PHP para la obtencion de datos de las variantes del producto --}}
                                    {{-- Si tiene el Producto Descuento se muestra el precio con descuento --}}
                                    @if (checkDiscount($product))
                                        <h4>
                                            {{-- Meta Etiqueta Indicador de la moneda utilizada --}}
                                            <meta itemprop="priceCurrency" content="MXN">
                                            {{-- Precio Normal Tachado --}}
                                            <span itemprop="price" content="{{$product->offert_price}}">
                                                <del>{{$settings->currency_icon}}{{ number_format($product->price, 2, '.', ',') }} MXN</del>
                                            </span>
                                            {{-- Precio con Descuento --}}
                                            <span itemprop="price" content="{{ $product->offert_price }}">
                                                {{$settings->currency_icon}}{{ $entero }}<span style="font-size: 15px; vertical-align: super;">.{{ $decimales }}</span> MXN {{ calculatedDiscountPercent($product->price, $product->offert_price) }}%OFF
                                            </span>
                                        </h4>

                                            {{-- Si el producto es mayor a 3000 pesos entra a meses sin intereses --}}
                                            @if ($product->offert_price >= 3000)
                                                {{-- Se muestra en cuanto quedarian los pagos a meses sin interses --}}
                                                <p class="wsus__msi_product">
                                                    Pagalo a <span style="color: #00a650;">{{ $msiMeses }} Meses sin intereses de {{$settings->currency_icon}}{{number_format($msioffert,2)}} MXN</span>
                                                    pagando con
                                                    <img src="{{ asset('frontend/images/iconos-empresas-sin-fondo/Paypal-logo.png') }}" alt="Meses sin intereses PayPal" style="height: 22px; vertical-align: middle; margin-left: 3px;">
                                                </p>
                                            {{-- No aplica meses sin intereses ya que el monto es menor a 3000 pesos --}}
                                            @else
                                                {{-- Se muestra que lo puede pagar a meses sin intereses teniendo un carrito mayor o igual a 3000 pesos --}}
                                                <p class="wsus__msi_product">
                                                    Pagalo a <span style="color: #00a650;">{{ $msiMeses }} Meses sin intereses a partir de {{$settings->currency_icon}}3,000 MXN</span> en carrito pagando con
                                                    <img src="{{ asset('frontend/images/iconos-empresas-sin-fondo/Paypal-logo.png') }}" alt="Meses sin intereses PayPal" style="height: 22px; vertical-align: middle; margin-left: 3px;">
                                                </p>
                                            {{-- Final de meses sin intereses @endif --}}
                                            @endif

                                        {{-- Indicador de IVA incluido --}}
                                        <span class="mdn_iva">IVA INCLUIDO</span>

                                    {{-- Si el producto no tiene descuento se muestra con precio normal --}}
                                    @else

                                        <h4>
                                            {{-- Area de edicion de combinaciones de productos --}}
                                            <meta itemprop="priceCurrency" content="MXN">
                                            {{-- Precio normal --}}
                                            <span itemprop="price" content="{{$price}}" id="dynamic_price">
                                                {{$settings->currency_icon}}{{ $price }}<span style="font-size: 15px; vertical-align: super;">.{{ $decimalesNormal }}</span> MXN
                                            </span>
                                            {{-- <span itemprop="price" content="{{$price}}">
                                                {{$settings->currency_icon}}{{ $enteroNormal }}<span style="font-size: 15px; vertical-align: super;">.{{ $decimalesNormal }}</span> MXN
                                            </span> --}}
                                        </h4>
                                            {{-- Si el producto es mayor a 3000 pesos entra a meses sin intereses --}}
                                            @if ($price >= 3000)
                                                {{-- Se muestra en cuanto quedarian los pagos a meses sin interses --}}
                                                <p class="wsus__msi_product">
                                                    Pagalo a <span style="color: #00a650;">{{ $msiMeses }} Meses sin intereses de {{$settings->currency_icon}}{{number_format($msiMonto,2)}} MXN</span>
                                                    pagando con
                                                    <img src="{{ asset('frontend/images/iconos-empresas-sin-fondo/Paypal-logo.png') }}" alt="Meses sin intereses PayPal" style="height: 22px; vertical-align: middle; margin-left: 3px;">
                                                </p>
                                            {{-- No aplica meses sin intereses ya que el monto es menor a 3000 pesos --}}
                                            @else
                                                {{-- Se muestra que lo puede pagar a meses sin intereses teniendo un carrito mayor o igual a 3000 pesos --}}
                                                <p class="wsus__msi_product">
                                                    Pagalo a <span style="color: #00a650;">{{ $msiMeses }} Meses sin intereses teniendo {{$settings->currency_icon}}3,000 MXN</span> en carrito pagando con
                                                    <img src="{{ asset('frontend/images/iconos-empresas-sin-fondo/Paypal-logo.png') }}" alt="Meses sin intereses PayPal" style="height: 22px; vertical-align: middle; margin-left: 3px;">
                                                </p>
                                            {{-- Final de meses sin intereses @endif --}}
                                            @endif
                                            {{-- Indicador de IVA incluido --}}
                                            <span class="mdn_iva">IVA INCLUIDO</span>
                                    {{-- Final de Precio de Descuento @endif --}}
                                    @endif

                            {{-- En caso de que no tenga precio --}}
                            @else
                                {{-- Se pone en stock pero que requiere asesoría porque implica un proceso por detras --}}
                                <p class="wsus__stock_area">
                                    <span class="in_stock" itemprop="availability" content="https://schema.org/MadeToOrder">La venta de este producto requiere asesoria</span>
                                </p>
                            {{-- Final de Condicion de Precio --}}
                            @endif
                            </div>
                            {{-- Redireccion a Detalles del producto este es una etiqueta que contiene el itemprop --}}
                            <link itemprop="url" href="https://www.macdelnorte.com/public/product-detail/{{$product->slug}}" />
                            {{-- Clave Unica del producto registrada en ASPELL y tambien en algun momento se hara conexion --}}
                            <p class="sku">Clave: <span itemprop="sku" id="dynamic_sku" content="{!! $sku !!}">{!!$sku !!}</span></p>
                            {{-- Modelo del Producto  no visible--}}
                            <p class="mpn"><span itemprop="mpn"content="{!! $product->productModel !!}" style="display: none; visibility: hidden;">{{$product->productModel}}</span></p>
                            {{-- <p class="description" itemprop="sku">Clave: <span>{!! $product->sku !!}</span></p> --}}
                            {{-- Marca del producto  --}}
                            <p class="brand_model" itemprop="brand" itemscope itemtype="http://schema.org/Brand">
                                Marca:<span itemprop="name" content="{{$product->brand->name}}">{{$product->brand->name}}</span>
                            </p>
                            <br>
                            {{-- Modelo del Producto visible--}}
                            <p class="brand_model" itemprop="model">Modelo :{{$product->productModel}}</p>
                            {{-- Envio Gratis y tiempo de envio --}}
                                <div class="wsus__shipping">
                                    <p class="wsus__shipping-text-one"><i class="fas fa-shipping-fast" aria-hidden="true" style="color: #00a650;"></i><span > Env&iacute;o gratis</span> a partir de $2,299.00</p>
                                    <p>La entrega se realiza en un plazo de 1 a 3 d&iacute;as h&aacute;biles. Envio a todo el pais.</p>

                                </div>
                            
                            @php
                                    // Prepara las combinaciones para JS
                                    $jsCombinations = [];
                                    foreach ($productCombinations as $comb) {
                                        $jsCombinations[] = [
                                            'id' => $comb->id,
                                            'variant_item_ids' => json_decode($comb->variants_item_ids, true),
                                            'price' => $comb->price,
                                            'offert_price' => $comb->offert_price,
                                            'qty' => $comb->qty,
                                            'sku' => $comb->sku,
                                            // Puedes agregar más campos si necesitas
                                        ];
                                        
                                    }
                                    $activeCombinationItems = [];
                                    foreach ($productCombinations->where('status', 1) as $comb) {
                                        $activeCombinationItems = array_merge(
                                            $activeCombinationItems,
                                            json_decode($comb->variants_item_ids, true)
                                        );
                                    }
                                    $activeCombinationItems = array_unique($activeCombinationItems);
                                    $defaultCombination = $productCombinations->where('is_default', 1)->first();
                                    $defaultItemIds = $defaultCombination ? json_decode($defaultCombination->variants_item_ids, true) : [];
                                    $selectedItemIds = $selectedCombination ? json_decode($selectedCombination->variants_item_ids, true) : [];
                            @endphp
                            @foreach ($product->variants as $variant)
                            @php
                                // Filtra los items de variante que están en combinaciones activas
                                $filteredItems = $variant->productVariantItems->whereIn('id', $activeCombinationItems);
                            @endphp
                            @if ($variant->status != 0 && $filteredItems->where('status', '!=', 0)->where('name', '!=', '')->isNotEmpty())
                                <div class="product-variant-picker" data-variant-id="{{ $variant->id }}">
                                    <div class="product-variant-tittle">
                                        <p class="product-variant-label">
                                            <span>{{ $variant->name }}:</span>
                                        </p>
                                    </div>
                                    <div class="product-variant-picker-default">
                                        @foreach ($filteredItems as $variantItem)
                                            @if ($variantItem->status != 0)
                                                <a role="button"
                                                class="product-details-variant{{ in_array($variantItem->id, $selectedItemIds) ? '-selected' : '' }}"
                                                href="#"
                                                data-variant-item-id="{{ $variantItem->id }}">
                                                    <div class="product-details-variant-container">
                                                        <p class="product-details-variant-label">
                                                            {{ $variantItem->name }}
                                                        </p>
                                                    </div>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                            {{--Variantes antiguo--}}
                            {{--
                            @foreach ($product->variants as $variant)
                                 @if ($variant->status != 0 && $variant->productVariantItems->where('status', '!=', 0)->where('name', '!=', '')->isNotEmpty())
                                    <div class="product-variant-picker">
                                        <div class="product-variant-tittle">
                                            <p class="product-variant-label">

                                                <span>{{ $variant->name }}:</span>
                                                @foreach ($variant->productVariantItems as $variantItem)
                                                    @if ($variantItem->status !=0 && $variantItem->is_default != 0)
                                                        <span id="por definir" class="product-variant-label-select">{{ $variantItem->name }}</span>
                                                    @endif
                                                @endforeach
                                            </p>
                                        </div>
                                        <div class="product-variant-picker-default">
                                            @foreach ($variant->productVariantItems as $variantItem)
                                                @if ($variantItem->status != 0)
                                                    @if ($variantItem->is_default != 0)
                                                        <a role="button" class="product-details-variant-selected" href="">
                                                            <div class="product-details-variant-container">
                                                                <p class="product-details-variant-label">
                                                                    {{ $variantItem->name }}
                                                                </p>
                                                            </div>
                                                        </a>
                                                    @else
                                                        <a role="button" class="product-details-variant" href="">
                                                            <div class="product-details-variant-container">
                                                                <p class="product-details-variant-label">
                                                                    {{ $variantItem->name }}
                                                                </p>
                                                            </div>
                                                        </a>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            --}}

                            
                            {{-- Formulario para agregar al carrito --}}
                            <form class="shopping-cart-form">
                                <div class="wsus__quentity">
                                    {{-- Pasando del Id del producto --}}
                                    <input type="hidden" name="product_id" value="{{$product->id}}">
                                    {{-- Pasando el Id de la combinacion --}}
                                    <input type="hidden" name="combination_id" id="combination_id" value="{{ $selectedCombination ? $selectedCombination->id : '' }}">
                                    {{-- Pasando el nombre de la marca --}}
                                    <input type="hidden" name="brand_name" value="{{ $product->brand->name }}">
                                    {{-- Pasando el SKU del producto --}}
                                    <input type="hidden" name="sku" value="{{$product->sku}}">
                                    {{-- Pasando el modelo del producto --}}
                                    <input type="hidden" name="productModel" value="{{$product->productModel}}">
                                    {{-- Condicion si el producto tiene precio muestra la cantidad que decea agregar --}}
                                    @php
                                        $currentPrice = $selectedCombination ? $selectedCombination->price : $product->price;
                                        $currentQty = $selectedCombination ? $selectedCombination->qty : $product->qty;
                                    @endphp

                                    @if ($currentPrice)
                                        <h5>Cantidad :</h5>
                                        <div class="select_number">
                                            <input class="number_area" name="qty" type="text" min="1" max="{{ $currentQty }}" value="1" />
                                        </div>
                                    @else
                                        <div class="shopping-cart-form-cotize" style="display: flex; align-items: center">
                                            <button class="common_btn"><a href="{{route('contact')}}" target="_black" style="text-decoration: none; color:white">Contacto Directo <i class="fa fa-envelope"></i></a></button>
                                            <p style="margin-left: 10px; margin-right: 10px;" > o </p>
                                            <button class="common_btn"><a href="https://wa.link/f28njw" target="_black" style="text-decoration: none; color:white"><i class="fa fa-whatsapp"></i> Llamanos al  81-35825559 </a></button>
                                        </div>
                                    {{-- Final de Condicion que muestra la cantidad o si se nececita contacto directo para la venta del producto  @endif--}}
                                    @endif
                                </div>
                                {{-- Condicion que muestra el boton de agregar al carrito --}}
                                @if ($currentPrice)
                                <div class="shopping_buttons">
                                    <div class="button_container">
                                        <ul class="wsus__button_area">
                                            <li><button type="submit" class="add_cart"><i class="fas fa-shopping-cart"></i> Agregar al Carrito</button></li>
                                        </ul>
                                    </div>
                                </div>
                                {{-- Final de Condicion que muestra el boton de agregar al carrito --}}
                                @endif
                            </form>
                            {{-- Botones de contacto --}}
                            <div class="button_container">
                                <a class="track-conversion" data-type="whatsapp" href="https://wa.link/f28njw" target="_blank" style="width: 100%;" onclick="dataLayer.push({'event': 'whatsapp_conversion', 'action': 'click', 'label': 'whatsapp-icon'});" >
                                    <ul class="wsus__button_area">
                                        <li><button type="button" class="whastapp_cart_cotize"><i class="fa fa-whatsapp" aria-hidden="true"></i> Cotizar Ahora!</button></li>
                                    </ul>
                                </a>
                                <a class="track-conversion" data-type="telefono" href="tel:8124738768" style="width: 100%;" onclick="dataLayer.push({'event': 'Telefono_Conversion', 'telefono': '8124738768'});">
                                    <ul class="wsus__button_area">
                                        <li><button id="button_call_product_details" type="button" class="phone_number_cart_cotize"><i class="fa fa-phone-alt" aria-hidden="true"></i> Atencion Inmediata!</button></li>
                                    </ul>
                                </a>
                            </div>
                            {{-- VALDACION CON reCAPTCHA google
                            <div class="button_container">
                                <a id="whatsappBtn" class="track-conversion" data-type="whatsapp" href="#" style="width: 100%;">
                                    <ul class="wsus__button_area">
                                        <li>
                                            <button type="button" class="whastapp_cart_cotize">
                                                <i class="fa fa-whatsapp" aria-hidden="true"></i> Cotizar Ahora!
                                            </button>
                                        </li>
                                    </ul>
                                </a>
                                <a id="telefonoBtn" class="track-conversion" data-type="telefono" href="#" style="width: 100%;">
                                    <ul class="wsus__button_area">
                                        <li>
                                            <button id="button_call_product_details" type="button" class="phone_number_cart_cotize">
                                                <i class="fa fa-phone-alt" aria-hidden="true"></i> Atención Inmediata!
                                            </button>
                                        </li>
                                    </ul>
                                </a>
                            </div> --}}


                            {{-- Final de Botones de contacto --}}

                            </p>
                            {{-- Beneficios de comprar con nosotros --}}
                                <div class="wsus__assurance">
                                    <ul>
                                        <li>
                                            <div>
                                                <img id="animated-gif" src="{{ asset('animations-icons/payment-protected-detail.gif') }}" alt="Compra Protegida">
                                                <p><span>Compra Protegida,</span> contamos con seguridad SLL para una transaccion segura.</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <img id="animated-gif" src="{{ asset('frontend/images/iconos/guarantee.webp') }}" alt="Garantia">
                                                {{-- HACER EL NUMERO DE GARANTIA DINAMICO --}}
                                                <p><span>Protecci&oacute;n Adicional,</span> cuenta con garantia de 1 año.</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <img id="animated-gif" src="{{ asset('frontend/images/iconos/how-to-pay.webp') }}" alt="Formas de Pago">
                                                <p><span>Elije Como Pagar,</span> puedes pagar con tarjeta, D&eacute;bito, Credito, Paypal, etc.</p>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                            {{-- Final Beneficios de comprar con nosotros --}}
                            {{-- Inicio de PHP para la agregacion de mas comercios como mercado Libre y amazon --}}
                                @php
                                    $marketplaces = collect($product->moreEccomerce ?? [])->filter(function($moreEccomerce) {
                                        return in_array($moreEccomerce->nameEccomerce, ['Mercado Libre', 'Amazon']);
                                    });
                                @endphp
                            {{-- Final de PHP para la agregacion de mas comercios como mercado Libre y amazon --}}
                                {{-- Inico de Condicion para mostrar los demas comercios que ahi disponibles --}}
                                @if ($marketplaces->count() > 0)
                                    <div class="wsus__more_eccomerce" style="margin: 5px 0px">
                                        <p><b>Disponible en:</b></p>
                                        @foreach ($marketplaces as $moreEccomerce)
                                            @if ($moreEccomerce->nameEccomerce == 'Mercado Libre')
                                                <a href="{{ $moreEccomerce->linkProduct }}" target="_blank" style="text-decoration: none; color: #333;"
                                                        onclick="dataLayer.push({
                                                        event: 'Mercado_libre-action',
                                                        action: 'click',
                                                        label: 'Mercado-Libre-product-detail',
                                                        value: '{{ $product->price }}'
                                                    });">
                                                    <img src="{{ asset('frontend/images/iconos-empresas/MercadoLibre_Logo.webp') }}" alt="{{ $product->name . ' ' . $moreEccomerce->nameEccomerce }}" style="width: 125px;">
                                                </a>
                                            @endif
                                            @if ($moreEccomerce->nameEccomerce == 'Amazon')
                                                <a href="{{ $moreEccomerce->linkProduct }}" target="_blank" style="text-decoration: none; color: #333;">
                                                    <img src="{{ asset('frontend/images/iconos-empresas/Amazon_logo.png') }}" alt="{{ $product->name . ' ' . $moreEccomerce->nameEccomerce }}" style="width: 125px;">
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                {{-- Final de Condicion para mostrar los demas comercios que ahi disponibles --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="wsus__pro_det_description">
                        <div class="wsus__details_bg">
                            <ul class="nav nav-pills mb-3" id="pills-tab3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pills-home-tab7" data-bs-toggle="pill"
                                        data-bs-target="#pills-home22" type="button" role="tab"
                                        aria-controls="pills-home" aria-selected="true">Descripci&oacute;n</button>
                                </li>
                                {{-- Ficha Tecnica del Producto mediante drive --}}
                                <li class="nav-item ">
                                    <a class="common_btn text-center" href="{{ $product->url_PDF }}" style="text-decoration: none; color:white" content="Ficha Tecnica:{{ $product->url_PDF }}">Descargar Pdf/Ficha tecnica</a>
                                </li>
                                {{-- Final de Ficha Tecnica del Producto mediante drive --}}
                            </ul>
                            <div class="tab-content" id="pills-tabContent4">
                                <div class="tab-pane fade  show active " id="pills-home22" role="tabpanel"
                                    aria-labelledby="pills-home-tab7">
                                    <div class="row">
                                        {{-- Inicio de Condicion para mostrar el posible Video que tenga el producto --}}
                                        @if ($product->video_link)
                                            <div class="col-xl-5 col-md-5 col-lg-5 ">
                                                <div class="wsus__description_area">
                                                    {{-- Llamada a la descripcion larga del producto --}}
                                                    <div class="wsus__description_area" itemprop="description" content="{{$product->long_description}}">
                                                        {!! $product->long_description !!}
                                                    </div>
                                                    {{-- Final de llamada a la descripcion larga del producto --}}
                                                    {{-- Inicio de condicion para mostrar el video --}}
                                                    @if ($product->video_link)

                                                    <p>Aqui te presentamos un breve video sobre nuestro producto. Recuerda que si necesitas asesoria no dudes en preguntarnos.</p>
                                                    <a class="common_btn mt-2 ml-2">Contactar</a>
                                                    @endif
                                                    {{-- Final de condicion para mostrar el video --}}

                                                </div>
                                            </div>
                                            {{--En caso contrario solo se muestra la descripcion larga del producto --}}
                                        @else
                                        <div class="col-xl-12 col-md-5 col-lg-5 ">
                                            <div class="wsus__description_area" itemprop="description" content="{{$product->long_description}}">
                                                {!! $product->long_description !!}
                                            </div>
                                        </div>
                                        {{-- Final de condicion para mostrar el video --}}
                                        @endif
                                        @if ($product->video_link)
                                            <div id="video_product" class="col-xl-7 col-md-7 col-lg-7 ">
                                                {{-- MIGRAR A LIBRERIA LITE-YOUTUBE PARA UN MEJOR RENDIMIENTO --}}
                                                <iframe src="{{$product->video_link}}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

            </div>
            {{-- Opiniones del Producto y donde se deja el comentario --}}
            <section class="review_rating_coment">
                <div class="row">
                    <div class="col-12">
                        <div class="rating__content">
                            <h2>Opiniones del Producto</h2>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="rating__wrapper">
                            <!-- El 4.9 como un elemento separado -->
                            <div class="rating__statistics">
                                {{-- Estadisticas del las opiniones del producto --}}
                                <p class="rating__statistics__number" >
                                    {{ number_format($averageRating, 1) }}
                                </p>
                                <div class="rating__stars_and_reviews">
                                    <div class="rating__stars_and_reviews_stars">
                                        <!-- Estrellas -->
                                        <div>
                                            <!-- Estrellas según el promedio -->
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= floor($averageRating))
                                                    <i class="fas fa-star" aria-hidden="true"></i> <!-- Estrella completa -->
                                                @elseif ($i == ceil($averageRating) && $averageRating - floor($averageRating) >= 0.5)
                                                    <i class="fas fa-star-half-alt" aria-hidden="true"></i> <!-- Estrella media -->
                                                @else
                                                    <i class="far fa-star" aria-hidden="true"></i> <!-- Estrella vacía -->
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <p>( {{ count($reviews) ?? 0 }} Opiniones)</p>
                                </div>
                            </div>

                        </div>
                        <!-- Barra de Mediciones -->
                        <div class="rating_medition">
                            @foreach ([5, 4, 3, 2, 1] as $star)
                                @php
                                // Cálculo del porcentaje, con un mínimo de 1%
                                    $percentage = count($reviews) > 0 ? max(($ratingCounts[$star] / count($reviews)) * 100, 1) : 1;
                                @endphp
                                <div class="rating-bar-container">
                                    <div class="rating-bar-container-inside" >
                                        <!-- Barra de medición -->
                                        <div class="bar_medition-margin"style="width: 100%; height: 100%; background-color: #f1f1f1; border: 1px solid #d3d3d3; border-radius: 5px; ">
                                            <div class="bar-medition" style="width: {{ $percentage }}%; background: linear-gradient(90deg, rgba(0, 123, 255, 1) 0%, rgba(87, 171, 255, 1) 100%);">
                                                <div ></div>
                                            </div>
                                        </div>
                                        <!-- Mostrar número de estrella -->
                                        <div class="number_start">
                                            <span>{{ $star }}</span> <!-- Número de estrella -->
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @auth
                            @php
                                $isBrought = false;
                                $orders = \App\Models\Order::where(['user_id' => auth()->user()->id, 'order_status' => 'delivered'])->get();
                                foreach ($orders as $key => $order) {
                                    $exisItem = $order->orderProducts()->where('product_id', $product->id)->first();
                                    if ($exisItem) {
                                        $isBrought = true;
                                    }
                                }
                            @endphp
                            @if ($isBrought === true)
                            <div class="commet_review_send">
                                <form action="{{route('user.review.create')}}" class="commet_review_send_form" enctype="multipart/form-data" method="POST" style="padding: 20px;">
                                    @csrf
                                    <!-- Selección de estrellas -->
                                    <div class="rating">
                                        <div class="rating_title">
                                            <h4 for="rating">Califica este producto:</h4>
                                        </div>
                                        <div class="rating-select-star">
                                            <i class="fas fa-star" id="star" onclick="setRating(1)"></i>
                                            <i class="fas fa-star" id="star" onclick="setRating(2)"></i>
                                            <i class="fas fa-star" id="star" onclick="setRating(3)"></i>
                                            <i class="fas fa-star" id="star" onclick="setRating(4)"></i>
                                            <i class="fas fa-star" id="star" onclick="setRating(5)"></i>
                                        </div>
                                        <input type="hidden" id="rating_value" name="rating" value="0">
                                    </div>

                                    <!-- Comentario -->
                                    <div class="rating_write_commet" style="margin-bottom: 15px;">
                                        <label for="comment">Escribe tu comentario:</label>
                                        <textarea id="comment" name="review" rows="4" placeholder="El producto cumple con mis expectativas..."></textarea>
                                    </div>
                                    <!-- Contenedor para subir imágenes -->
                                    <div class="image_upload_section">
                                        <label for="images">Sube hasta 4 imágenes (opcional):</label>
                                        <!-- Input funcional con estilos mejorados -->
                                        <div class="img_upload">
                                            <label for="file-input" id="upload-icon" class="upload_area">
                                                <i class="fas fa-image"></i>

                                            </label>
                                            <input type="file" id="file-input" name="images[]" multiple>
                                            <!-- Área para mostrar la vista previa de las imágenes -->
                                            <div id="image-preview" class="image-preview"></div>
                                        </div>

                                    </div>
                                        <input type="hidden" name="product_id" id="" value="{{$product->id}}">
                                        {{-- <input type="hidden" name="_id" id="" value="{{$user->id}}">                 --}}
                                    <!-- Botón para enviar -->
                                    <button type="submit" class="common_btn">Enviar comentario</button>
                                </form>
                            </div>

                            @endif
                    @endauth

                    </div>
                    <div class="col-8">
                        <!-- Sección de Comentarios a la derecha -->
                        <div class="rating__comment" style=" margin-left: 20px;">
                            @foreach ($reviews as $review )
                                <div class="comment_item" style="margin-bottom: 20px; padding: 10px; border-bottom: 1px solid #ddd;">
                                    {{-- Nomre Usuario --}}
                                    <span class="rating__comment_nameUser" style="font-size: 16px; color: #333; margin-right: 10px; font-weight: 300;">
                                        {{$review->user->name}}
                                    </span>
                                    {{-- Estrellas --}}
                                    <div style="display: flex; align-items: center;">
                                        <div style="color: #1e77fc; margin-right: 10px;">
                                           <!-- Estrellas del comentario según la calificación -->
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $review->rating)
                                                    <i class="fas fa-star" aria-hidden="true"></i> <!-- Estrella completa -->
                                                @elseif ($i == floor($review->rating) + 1 && $review->rating - floor($review->rating) >= 0.5)
                                                    <i class="fas fa-star-half-alt" aria-hidden="true"></i> <!-- Estrella media -->
                                                @else
                                                    <i class="far fa-star" aria-hidden="true"></i> <!-- Estrella vacía -->
                                                @endif
                                            @endfor
                                        </div>

                                    </div>
                                    {{-- Descripcion --}}
                                    <p style="margin-top: 10px;">{{$review->review}}</p>
                                    <div class="img_review_galleries">
                                        <div style="border-color: 1px solid gray; border-radius: 10px; height: 210px; display: flex; justify-content: start; align-items: center">
                                            @if (count($review->productReviewGalleries) > 0)
                                                @foreach ($review->productReviewGalleries as $image )
                                                    <img src="{{asset($image->image)}}" alt="" style="border-color: 1px solid gray; width: 180px; height: 200px; margin: 0px 5px;" >
                                                @endforeach
                                            @endif
                                        </div>

                                    </div>
                                </div>

                            @endforeach
                            <div class="mt-5">
                                @if ($reviews->hasPages())
                                    {{$reviews->links()}}
                                @endif
                            </div>


                        </div>

                        {{-- <!-- Aquí puedes agregar más comentarios -->
                        <div style="text-align: center; margin-top: 20px;">
                            <button class="common_btn">Ver más opiniones</button>
                        </div> --}}
                    </div>

                </div>
            </section>


        </div>
    </section>






@endsection

@push('scripts')

{{-- Combinaciones desde PHP a JS --}}
<script>
window.productCombinations = @json($jsCombinations);

document.addEventListener('DOMContentLoaded', function() {
    let selectedVariantItems = {};

    // Inicializar con los valores por defecto
    document.querySelectorAll('.product-details-variant-selected').forEach(function(el) {
        const variantId = el.closest('.product-variant-picker').dataset.variantId;
        const itemId = el.dataset.variantItemId;
        selectedVariantItems[variantId] = parseInt(itemId);
    });

    // Función para actualizar visualmente las opciones disponibles/no disponibles
    function updateAvailableVariantItems() {
        const selectedIds = Object.values(selectedVariantItems).map(Number);

        document.querySelectorAll('.product-variant-picker').forEach(function(variantPicker) {
            const thisVariantId = variantPicker.dataset.variantId;

            variantPicker.querySelectorAll('.product-details-variant, .product-details-variant-selected').forEach(function(option) {
                const itemId = Number(option.dataset.variantItemId);

                // Simula la selección si el usuario eligiera este item en este grupo
                let simulatedSelection = {...selectedVariantItems};
                simulatedSelection[thisVariantId] = itemId;
                const simulatedIds = Object.values(simulatedSelection).map(Number).sort((a, b) => a - b);

                // ¿Existe alguna combinación EXACTA con esta selección simulada?
                let isAvailable = window.productCombinations.some(function(comb) {
                    const combIds = comb.variant_item_ids.map(Number).sort((a, b) => a - b);
                    return JSON.stringify(combIds) === JSON.stringify(simulatedIds);
                });

                // Limpia clases previas
                option.classList.remove('variant-unavailable');

                // Si NO está disponible, pon borde punteado
                if (!isAvailable) {
                    option.classList.add('variant-unavailable');
                }
            });
        });
    }

    // Llama a la función al cargar la página
    updateAvailableVariantItems();

    // Evento al hacer click en una variante
    document.querySelectorAll('.product-details-variant, .product-details-variant-selected').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();

            const variantPicker = el.closest('.product-variant-picker');
            const variantId = variantPicker.dataset.variantId;
            const itemId = el.dataset.variantItemId;

            // Simula la selección con este item
            let simulatedSelection = {...selectedVariantItems};
            simulatedSelection[variantId] = parseInt(itemId);
            const simulatedIds = Object.values(simulatedSelection).map(Number).sort((a, b) => a - b);

            // Buscar la combinación EXACTA con esta selección simulada
            let matchingCombination = null;
            window.productCombinations.forEach(function(comb) {
                const combIds = comb.variant_item_ids.map(Number).sort((a, b) => a - b);
                if (JSON.stringify(combIds) === JSON.stringify(simulatedIds)) {
                    matchingCombination = comb;
                }
            });

            // Si existe una combinación exacta, recarga la página
            if (matchingCombination) {
                let slug = "{{ $product->slug }}";
                let currentComb = new URLSearchParams(window.location.search).get('comb');
                if (currentComb != matchingCombination.id.toString()) {
                    window.location.href = `/product-detail/${slug}?comb=${matchingCombination.id}`;
                }
                return;
            } else {
                // Si no existe combinación exacta, busca la más cercana que incluya este item
                let fallbackCombination = null;
                window.productCombinations.forEach(function(comb) {
                    const combIds = comb.variant_item_ids.map(Number);
                    if (combIds.includes(Number(itemId))) {
                        if (
                            !fallbackCombination ||
                            combIds.length < fallbackCombination.variant_item_ids.length
                        ) {
                            fallbackCombination = comb;
                        }
                    }
                });
                if (fallbackCombination) {
                    let slug = "{{ $product->slug }}";
                    let currentComb = new URLSearchParams(window.location.search).get('comb');
                    if (currentComb != fallbackCombination.id.toString()) {
                        window.location.href = `/product-detail/${slug}?comb=${fallbackCombination.id}`;
                    }
                    return;
                }
            }

            // Actualiza visualmente las opciones disponibles
            updateAvailableVariantItems();
        });
    });
});
</script>





{{--    VALIDACION reCAPTCHA Google
<script>
    const siteKey = '6LfT84IrAAAAAKVhNXXrFPDAgMFAiCGdj1-tYz2B';
    const whatsappUrl = 'https://wa.link/f28njw';
    const telefonoUrl = 'tel:8124738768';

    function validarReCaptcha(action, callback) {
        grecaptcha.ready(() => {
            grecaptcha.execute(siteKey, { action: action }).then(token => {
                if(token) {
                    callback(token);
                } else {
                    alert('No se pudo validar tu acción. Intenta de nuevo.');
                }
            });
        });
    }

    document.getElementById('whatsappBtn').addEventListener('click', e => {
        e.preventDefault();
        validarReCaptcha('whatsapp_click', token => {
            dataLayer.push({
                event: 'whatsapp_conversion',
                action: 'click',
                label: 'whatsapp-icon',
                recaptcha_token: token
            });
            window.open(whatsappUrl, '_blank');
        });
    });

    document.getElementById('telefonoBtn').addEventListener('click', e => {
        e.preventDefault();
        validarReCaptcha('telefono_click', token => {
            dataLayer.push({
                event: 'Telefono_Conversion',
                telefono: '8124738768',
                recaptcha_token: token
            });
            window.location.href = telefonoUrl;
        });
    });
</script> --}}


<script>

const fileInput = document.getElementById('file-input');
const imagePreviewContainer = document.getElementById('image-preview');
const uploadIcon = document.getElementById('upload-icon');

// Función que se ejecuta cuando se seleccionan archivos
fileInput.addEventListener('change', function() {
    // Limpiar cualquier vista previa anterior
    imagePreviewContainer.innerHTML = '';

    // Ocultar el icono de carga una vez que se seleccionan archivos
    uploadIcon.style.display = 'none';  // Ocultar el icono

    // Obtener los archivos seleccionados
    const files = fileInput.files;

    // Verificar que se hayan seleccionado archivos
    if (files.length > 0) {
        // Iterar sobre los archivos seleccionados
        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // Solo permitir imágenes
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();

                // Función que se ejecuta cuando el archivo se lee
                reader.onload = function(e) {
                    // Crear una nueva imagen para la vista previa
                    const imgElement = document.createElement('img');
                    imgElement.src = e.target.result;  // Asignar el resultado de la lectura del archivo

                    // Añadir la clase "single-image" si es la primera imagen (para que ocupe todo el espacio)
                    if (i === 0) {
                        imgElement.classList.add('single-image');
                    }

                    // Añadir la imagen al contenedor de vista previa
                    imagePreviewContainer.appendChild(imgElement);

                    // Si hay más de una imagen, ajustar el estilo para alinearlas horizontalmente
                    if (i > 0) {
                        imgElement.classList.remove('single-image');
                    }
                };

                // Leer el archivo como URL de datos
                reader.readAsDataURL(file);
            }
        }
    }
});

    </script>
<script>
   function setRating(rating) {
    // Establece el valor del input oculto con el valor de la estrella seleccionada
    document.getElementById('rating_value').value = rating;

    // Cambia el color de las estrellas para reflejar la selección
    updateStarDisplay(rating);
}

function updateStarDisplay(rating) {
    const stars = document.querySelectorAll('#star');

    // Actualiza el color de las estrellas según la calificación seleccionada
    stars.forEach((star, index) => {
        if (index < rating) {
            star.style.color = '#1e77fc';  // Color de las estrellas seleccionadas
        } else {
            star.style.color = '#ccc';  // Color de las estrellas no seleccionadas
        }
    });
}
</script>

    @if ($product->price)
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
              "description": "{{ $product->long_description }}",
              "brand": {
                "@type": "Brand",
                "name": "{{ $product->brand->name }}"
              },
              "offers": {
                "@type": "Offer",
                "url": "{{$product->slug }}",
                "itemCondition": "https://schema.org/NewCondition",
                "availability": "https://schema.org/{{ $product->qty > 0 ? 'InStock' : 'OutOfStock' }}",
                "price": "{{ $product->offer_price ?: $product->price }}",
                "priceCurrency": "MXN"
              },
              "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "{{ number_format($averageRating, 1) }}",
                "reviewCount": "{{ count($reviews) ?? 0 }}"
              }
            }
        </script>
    @endif
@endpush

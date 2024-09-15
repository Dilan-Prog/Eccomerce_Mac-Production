@extends('frontend.layouts.master')
@section('title')
{{$settings->site_name}} || {{$product->name}}
@endsection

@section('content')
    <section id="wsus__product_details">
        <div class="container">
            <section id="wsus__breadcrumb">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <ul>                                    
                                    <li><a href="#" onclick="window.history.back(); setTimeout(function(){ location.reload(); }, 500); return false;">Volver</a></li>
                                    <li><a href="{{route('products.index', ['category' => $product->category->slug])}}">{{$product->category->name}}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
              
            </section>
            <div class="wsus__details_bg">
                
                <div class="row">
                    <div class="col-xl-8 ">
                        <div id="sticky_pro_zoom">
                            <div class="exzoom hidden" id="exzoom">
                                <div class="exzoom_img_box">

                                    <ul class='exzoom_img_ul'>
                                        <li><img class="zoom ing-fluid w-100" src="{{asset($product->thumb_image)}}" alt="product"></li>
                                        @foreach($product->productImageGalleries as $productImage)
                                        <li><img class="zoom ing-fluid w-100" src="{{asset($productImage->image)}}" alt="product"></li>
                                        @endforeach

                                    </ul>
                                </div>
                                <div class="exzoom_nav"></div>
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
                        <div class="wsus__pro_details_text">
                            <span>@switch($product->product_type)
                                @case('new_arrival')
                                    <span>Producto Nuevo | {{$product->qty}} piezas</span>
                                    @break
                                @case('featured_product')
                                    <span>Producto Favorito | {{$product->qty}} piezas </span>
                                    @break                           
                                @case('top_product')
                                    <span>Producto Top | {{$product->qty}} piezas</span>
                                    @break
                                @case('best_product')
                                    <span>Producto Mas Vendido | {{$product->qty}} piezas</span>
                                    @break
                                @default
                                    
                            @endswitch</span>
                            <a class="title" href="javascript:;" itemprop="name" content="{{$product->name}}">{{$product->name}}</a>
                            {{-- en caso de que tenga produto --}}
                            @if ($product->price)

                                @if ($product->qty > 0)
                                    <p class="wsus__stock_area"><span class="in_stock" itemprop="offers" itemtype="http://schema.org/Offer"><meta itemprop="availability" content="http://schema.org/InStock">Stok Disponible</span></p>
                                @elseif ($product->qty === 0)
                                    <p class="wsus__stock_area"><span class="in_stock" itemprop="offers" itemtype="http://schema.org/Offer"><meta itemprop="availability" content="http://schema.org/OutOfStock">Agotado</span></p>
                                @endif


                                @if (checkDiscount($product))
                                <h4 itemscope itemtype="http://schema.org/Offer">
                                    <meta itemprop="priceCurrency" content="MXN">
                                    <span itemprop="price" content="{{$product->offer_price}}">
                                        {{$settings->currency_icon}}{{ number_format($product->offert_price, 2, '.', ',') }} MXN <del>{{$settings->currency_icon}}{{ number_format($product->price, 2, '.', ',') }} MXN</del>
                                        
                                    </span>
                                </h4>
                                <small><strong>IVA INCLUIDO</strong></small>
                                
                                @else
                                <h4 itemscope itemtype="http://schema.org/Offer">
                                    <meta itemprop="priceCurrency" content="MXN">
                                    <span itemprop="price" content="{{$product->price}}">
                                        {{$settings->currency_icon}}{{ number_format($product->price, 2, '.', ',') }} MXN 
                                    </span>    
                                </h4>
                                <small><strong>IVA INCLUIDO</strong></small>
                                @endif
                            
                            @else
                            <p class="wsus__stock_area"><span class="in_stock" itemprop="availability" contet="http://schema.org/InStock" >Disponible (Con Asesoria)</span></p>
                           

                            @endif
                            <p class="description" itemprop="sku">{!! $product->short_description !!}</p>

                            {{-- Agregrar wsus__shipping a la pagina web --}}
                            <div class="wsus__shipping">
                                <p class="wsus__shipping-text-one"><i class="fas fa-shipping-fast" aria-hidden="true"></i><span> Env&iacute;o gratis</span> a todo el pa&iacute;s</p>
                                <p>La entrega se realiza en un plazo de 1 a 3 d&iacute;as h&aacute;biles.</p>
                            </div>

                            
                            <form class="shopping-cart-form">
                                <div class="wsus__quentity">
                                    <input type="hidden" name="product_id" value="{{$product->id}}">
                                    <input type="hidden" name="brand_name" value="{{ $product->brand->name }}">
                                    <input type="hidden" name="sku" value="{{$product->sku}}">
                            @if ($product->price)
                            <h5>Cantidad :</h5>
                            <div class="select_number">
                                <input class="number_area" name="qty" type="text" min="1" max="100" value="1" />
                            </div>
                            @else
                            <button class="common_btn mr-2"><a href="{{route('contact')}}" target="_black" style="text-decoration: none; color:white">Contacto Directo <i class="fa fa-envelope"></i></a></button>
                            <p style="margin-left: 10px; margin-right: 10px;" > o </p>
                            <button class="common_btn"><a href="https://wa.link/f28njw" target="_black" style="text-decoration: none; color:white"><i class="fa fa-whatsapp"></i> Llamanos al  81-35825559 </a></button>
                            @endif
                                </div>
                            @if ($product->price)
                            <ul class="wsus__button_area">
                                <li><button type="submit" class="add_cart" href="#">Agregar al Carrito</button></li>

                            </ul>

                            @endif
                            </form>
                            {{-- Agregar el campo para modelo a la seccion de productos --}}
                            <p class="brand_model" itemprop="brand" content="{{$product->brand->name}}"><span>Marca :</span>{{$product->brand->name}}</p>
                            <br>
                            <p class="brand_model" ><span>Modelo :</span> {{$product->sku}}</p>
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
                                <li class="nav-item ">
                                    <a class="common_btn text-center" href="{{ $product->url_PDF }}" style="text-decoration: none; color:white" content="Ficha Tecnica:{{ $product->url_PDF }}">Descargar Pdf/Ficha tecnica</a>
                                </li>

                            </ul>
                            <div class="tab-content" id="pills-tabContent4">
                                <div class="tab-pane fade  show active " id="pills-home22" role="tabpanel"
                                    aria-labelledby="pills-home-tab7">
                                    <div class="row">
                                        @if ($product->video_link)
                                            <div class="col-xl-5 col-md-5 col-lg-5 ">
                                                <div class="wsus__description_area">
                                                    <div class="wsus__description_area" itemprop="description" content="{{$product->long_description}}">
                                                        {!! $product->long_description !!}
                                                    </div>

                                                    @if ($product->video_link)

                                                    <p>Aqui te presentamos un breve video sobre nuestro producto. Recuerda que si necesitas asesoria no dudes en preguntarnos.</p>
                                                    <a class="common_btn mt-2 ml-2">Contactar</a>
                                                    @endif

                                                </div>
                                            </div>
                                        @else
                                        <div class="col-xl-12 col-md-5 col-lg-5 ">
                                            <div class="wsus__description_area" itemprop="description" content="{{$product->long_description}}">
                                                {!! $product->long_description !!}
                                            </div>
                                        </div>
                                        @endif
                                        @if ($product->video_link)
                                            <div id="video_product" class="col-xl-7 col-md-7 col-lg-7 ">
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
        </div>
    </section>


    {{-- Video Link
    @if ($product->video_link)
    <section id="video_product">
        <div class="container">
            <div class="video-section">
                <div class="row">
                    <div class="col-xl-4">
                        <h1>Presentacion: {{$product->name}}</h1>
                        <p>Aqui te presentamos un breve video sobre nuestro producto. Recuerda que si necesitas asesoria no dudes en preguntarnos.</p>
                        <a class="common_btn mt-2 ml-2">Contactar</a>
                    </div>
                    <div class="col-xl-8">
                        <iframe src="{{$product->video_link}}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif --}}



@endsection

@push('scripts')

    
@endpush

    <!--============================
        HEADER START
    ==============================-->
    <header>
        <div class="container">
            <div class="row">
                <div class="col-2 col-md-1 d-lg-none">
                    <div class="wsus__mobile_menu_area">
                        <span class="wsus__mobile_menu_icon"><i class="fal fa-bars"></i></span>
                    </div>
                </div>
                <div class="col-xl-2 col-7 col-md-8 col-lg-2">
                    <div class="wsus_logo_area">
                        <a class="wsus__header_logo" href="{{route('index')}}">
                            <img src="{{asset('frontend/images/logo/logo-blanco.png')}}" alt="logo" loading="lazy">
                        </a>
                    </div>
                </div>
                <div class="col-xl-5 col-md-6 col-lg-4 d-none d-lg-block">
                    <div class="wsus__search">
                        <form action="{{route('products.index')}}">
                            <input type="text" placeholder="Buscar..." name="search" value="{{request()->search}}">
                            <button type="submit" aria-label="Buscar"><i class="far fa-search"></i></button>
                        </form>
                    </div>
                </div>
                <div class="col-xl-5 col-3 col-md-3 col-lg-6">
                    <div class="wsus__call_icon_area">
                        <div class="wsus__call_area">
                            <div class="wsus__call">
                                <i class="fas fa-user-headset"></i>
                            </div>
                            <div class="wsus__call_text">
                                <p style="line-height: 22px;text-transform: none;">contacto@macdelnorte.com</p>
                                <p><a style="color: #fff;" target="_blank" href="https://wa.link/f28njw" onclick="dataLayer.push({'event': 'whatsapp_conversion', 'action': 'click', 'label': 'whatsapp-icon'});"><i class="fa fa-whatsapp" aria-hidden="true"></i> +81-35825559</a></p>
                            </div>
                        </div>
                        <ul class="wsus__icon_area">

                            <li><a class="wsus__cart_icon" href="#">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-cart4" viewBox="0 0 16 16">
                                    <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0"/>
                                  </svg><span id="cart-count">{{Cart::content()->count()}}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="wsus__mini_cart">
            <h4>Mi Carrito<span class="wsus_close_mini_cart"><i class="far fa-times"></i></span></h4>
            <ul class="mini_cart_wrapper">
                @foreach (Cart::content() as $sidebarProduct)
                <li id="mini_cart_{{$sidebarProduct->rowId}}">
                    <div class="wsus__cart_img">
                        <a href="{{ route('product-detail', $sidebarProduct->options->slug) }}"><img src="{{asset($sidebarProduct->options->image)}}" alt="product" class="img-fluid w-100" loading="lazy"></a>
                        <a class="wsis__del_icon remove_sidebar_product" data-Id="{{ $sidebarProduct->rowId }}" href=""><i class="fas fa-minus-circle"></i></a>
                    </div>
                    <div class="wsus__cart_text">
                        <a class="wsus__cart_title" href="{{ route('product-detail', $sidebarProduct->options->slug) }}">{{ $sidebarProduct->name }}</a>

                        <p>{{ $settings->currency_icon }}{{ formatCurrency($sidebarProduct->price) }}</p>
                        <small>Modelo: <br> {{$sidebarProduct->options->productModel}}</small>
                        <br>
                        <small>Cantidad: {{ $sidebarProduct->qty }}</small>
                    </div>
                </li>
                @endforeach
                @if (Cart::content()->count() == 0)
                    <li class="text-center">Compra tus productos al mejor Precio Tu Carrito te esta Esperando!</li>
                @endif
            </ul>
            <div class="mini_cart_actions {{Cart::content()->count() == 0 ? 'd-none' : ''}}">
                <h5>sub total <span id="mini_cart_subtotal">{{ $settings->currency_icon }}{{ formatCurrency(getCartTotal()) }}</span></h5>
                <div class="wsus__minicart_btn_area">
                    <a class="common_btn" href="{{route('cart-details')}}">Ver Carrito</a>
                    
                </div>
            </div>
        </div>

    </header>
    <!--============================
        HEADER END
    ==============================-->








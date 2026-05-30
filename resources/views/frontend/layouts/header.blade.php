<!--============================
    HEADER START
==============================-->
<header>
    <div class="container header-inner">

        {{-- Botón menú móvil — solo visible en pantallas pequeñas --}}
        <div class="wsus__mobile_menu_area d-lg-none me-2">
            <span class="wsus__mobile_menu_icon"><i class="fal fa-bars"></i></span>
        </div>

        <a href="{{ route('index') }}" class="logo">
            <img src="{{ asset('uploads/logo/webp-horizontal.webp') }}" alt="Mac Del Norte" class="logo-img" loading="eager">
            
        </a>

        <div class="search-bar d-none d-lg-block">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            <form action="{{ route('products.index') }}">
                <input type="text"
                       placeholder="Busca por número de parte, marca o categoría…"
                       name="search"
                       value="{{ request()->search }}">
            </form>
        </div>

        <div class="header-actions">

            @php
                if (auth()->check()) {
                    $role = auth()->user()->role;
                    $accountUrl = $role === 'admin'
                        ? route('admin.dashboard')
                        : ($role === 'associate' ? route('associate.dashboard') : route('user.profile'));
                    $accountLabel = 'Mi cuenta';
                } else {
                    $accountUrl = route('login');
                    $accountLabel = 'Iniciar sesión';
                }
            @endphp

            <a href="{{ $accountUrl }}" class="header-icon-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>{{ $accountLabel }}</span>
            </a>

            <a href="{{ route('cart-details') }}" class="header-icon-btn wsus__cart_icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                </svg>
                <span>Carrito</span>
                <span class="cart-badge" id="cart-count">{{ Cart::content()->count() }}</span>
            </a>

        </div>
    </div>

    {{-- Mini carrito lateral (funcionalidad existente preservada) --}}
    <div class="wsus__mini_cart">
        <h4>Mi Carrito<span class="wsus_close_mini_cart"><i class="far fa-times"></i></span></h4>
        <ul class="mini_cart_wrapper">
            @foreach (Cart::content() as $sidebarProduct)
            <li id="mini_cart_{{ $sidebarProduct->rowId }}">
                <div class="wsus__cart_img">
                    <a href="{{ route('product-detail', $sidebarProduct->options->slug) }}">
                        <img src="{{ asset($sidebarProduct->options->image) }}" alt="product" class="img-fluid w-100" loading="lazy">
                    </a>
                    <a class="wsis__del_icon remove_sidebar_product" data-Id="{{ $sidebarProduct->rowId }}" href="">
                        <i class="fas fa-minus-circle"></i>
                    </a>
                </div>
                <div class="wsus__cart_text">
                    <a class="wsus__cart_title" href="{{ route('product-detail', $sidebarProduct->options->slug) }}">{{ $sidebarProduct->name }}</a>
                    <p>{{ $settings->currency_icon }}{{ formatCurrency($sidebarProduct->price) }}</p>
                    <small>Modelo: <br> {{ $sidebarProduct->options->productModel }}</small>
                    <br>
                    <small>Cantidad: {{ $sidebarProduct->qty }}</small>
                </div>
            </li>
            @endforeach
            @if (Cart::content()->count() == 0)
                <li class="text-center">Compra tus productos al mejor Precio ¡Tu Carrito te está Esperando!</li>
            @endif
        </ul>
        <div class="mini_cart_actions {{ Cart::content()->count() == 0 ? 'd-none' : '' }}">
            <h5>Sub total <span id="mini_cart_subtotal">{{ $settings->currency_icon }}{{ formatCurrency(getCartTotal()) }}</span></h5>
            <div class="wsus__minicart_btn_area">
                <a class="common_btn" href="{{ route('cart-details') }}">Ver Carrito</a>
            </div>
        </div>
    </div>

</header>
<!--============================
    HEADER END
==============================-->
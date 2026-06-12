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

            {{-- Botón búsqueda — visible solo en móvil/tablet (<992px) --}}
            <button class="mobile-search-toggle d-lg-none"
                    id="mobile-search-toggle"
                    type="button"
                    aria-label="Buscar"
                    aria-expanded="false"
                    aria-controls="mobile-search-bar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
            </button>

            @auth
                @php $role = auth()->user()->role; @endphp

                @if($role === 'user')
                {{-- Usuario regular: dropdown de cuenta --}}
                <div class="account-dropdown-wrapper" id="account-dropdown-wrapper">

                    <button class="header-icon-btn account-trigger" id="account-trigger"
                            type="button" aria-expanded="false" aria-haspopup="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>Mi cuenta</span>
                    </button>

                    <div class="account-dropdown" id="account-dropdown" role="menu" aria-hidden="true">

                        <div class="account-dropdown-header">
                            <div class="account-dropdown-name">{{ Auth::user()->name }}</div>
                            <div class="account-dropdown-email">{{ Auth::user()->email }}</div>
                        </div>

                        {{-- Grupo 1: Cuenta --}}
                        <div class="account-dropdown-group">
                            <a href="{{ route('user.profile') }}" class="account-dropdown-item" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Datos personales
                            </a>
                            <a href="{{ route('user.profile') }}#fiscal" class="account-dropdown-item" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                Datos fiscales
                            </a>
                            <a href="{{ route('user.profile') }}#password" class="account-dropdown-item" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                Contraseña
                            </a>
                            {{-- TODO: crear tab de notificaciones en user.profile --}}
                            <a href="{{ route('user.profile') }}#notifications" class="account-dropdown-item" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                                Notificaciones
                            </a>
                            {{-- TODO: crear tab de Plan B2B en user.profile --}}
                            <a href="{{ route('user.profile') }}#b2b" class="account-dropdown-item" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                                Plan B2B
                            </a>
                        </div>

                        {{-- Grupo 2: Actividad --}}
                        <div class="account-dropdown-group">
                            <a href="{{ route('user.orders.index') }}" class="account-dropdown-item" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                                Mis compras
                            </a>
                            <a href="{{ route('user.address.index') }}" class="account-dropdown-item" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                Direcciones
                            </a>
                        </div>

                        {{-- Grupo 3: Cerrar sesión --}}
                        <div class="account-dropdown-group">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="account-dropdown-item account-dropdown-logout" role="menuitem">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

                @elseif($role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="header-icon-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Admin</span>
                </a>

                @else
                <a href="{{ route('associate.dashboard') }}" class="header-icon-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Mi cuenta</span>
                </a>
                @endif

            @else
            {{-- Visitante: enlace directo al login --}}
            <a href="{{ route('login') }}" class="header-icon-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Iniciar sesión</span>
            </a>
            @endauth

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

    {{-- Barra de búsqueda expandible en móvil (< 992px) --}}
    <div class="mobile-search-bar d-lg-none" id="mobile-search-bar" role="search">
        <div class="container">
            <form action="{{ route('products.index') }}" style="position:relative;">
                <svg class="search-icon-abs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text"
                       name="search"
                       value="{{ request()->search }}"
                       placeholder="Busca por número de parte, marca o categoría…"
                       autocomplete="off">
                <button type="submit">Buscar</button>
            </form>
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

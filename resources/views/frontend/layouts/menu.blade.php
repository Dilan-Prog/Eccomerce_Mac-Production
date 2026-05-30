{{-- $navCategories viene del View Composer en AppServiceProvider (Cache 1h) --}}
{{-- $categories = alias para mantener compatibilidad con el menú móvil existente --}}
@php $categories = $navCategories ?? collect(); @endphp

{{-- ============ BARRA INSTITUCIONAL ============ --}}
<div class="nav-secondary d-none d-lg-block">
    <div class="container nav-secondary-inner">
        <div class="nav-secondary-links">
            <a href="{{ route('index') }}" class="nav-secondary-link {{ request()->routeIs('index') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                Inicio
            </a>
            <div class="nav-secondary-divider"></div>
            <a href="{{ route('about') }}" class="nav-secondary-link {{ request()->routeIs('about') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                Nosotros
            </a>
            <div class="nav-secondary-divider"></div>
            {{-- <div class="nav-sec-dropdown-wrap">
                <a href="javascript:void(0)" class="nav-secondary-link {{ request()->routeIs('products.index') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 7H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm0 12H4V9h16v10zM12 3H8v2h8V3h-4z"/><rect x="6" y="11" width="4" height="2"/><rect x="10" y="11" width="4" height="2"/><rect x="14" y="11" width="4" height="2"/><rect x="6" y="15" width="4" height="2"/><rect x="10" y="15" width="4" height="2"/></svg>
                    Productos
                    <svg class="nav-sec-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                </a>
                <div class="nav-sec-dropdown">
                    @foreach ($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}">
                            <i class="{{ $category->icon }}"></i>
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div> --}}
            <div class="nav-secondary-divider"></div>
            <div class="nav-sec-dropdown-wrap">
                <a href="javascript:void(0)" class="nav-secondary-link">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                    Servicios
                    <svg class="nav-sec-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                </a>
                <div class="nav-sec-dropdown">
                    <a href="{{ route('servicio-controladores-temperatura') }}">Instalación de Controladores</a>
                    <a href="{{ route('servicio-instalacion-videoregistradores') }}">Instalación de Videoregistradores</a>
                    <a href="{{ route('servicio-instalacion-medidoresdeflujo') }}">Instalación de Medidores de Flujo</a>
                    <a href="{{ route('servicio-instalacion-plc') }}">PLC — Configuración, Instalación y Llave en mano</a>
                    <a href="{{ route('servicio-reparacion-videoregistradores') }}">Reparación de Videoregistradores</a>
                    <a href="{{ route('servicio-calibracion-ema') }}">Calibraciones EMA</a>
                </div>
            </div>
            <div class="nav-secondary-divider"></div>
            <a href="{{ route('contact') }}" class="nav-secondary-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/></svg>
                Contacto
            </a>
            <div class="nav-secondary-divider"></div>
            <a href="{{ route('associate') }}" class="nav-secondary-link {{ request()->routeIs('associate') ? 'active' : '' }}">
                Asociados y Revendedores
            </a>
        </div>
        <div class="nav-secondary-right">
            <a href="https://wa.link/f28njw" target="_blank" class="nav-secondary-quote">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                Solicitar cotización
            </a>
        </div>
    </div>
</div>
{{-- ============ FIN BARRA INSTITUCIONAL ============ --}}

{{-- ============ MEGA MENÚ PRINCIPAL ============ --}}
@include('frontend.layouts.partials.nav-megamenu')
{{-- ============ FIN MEGA MENÚ ============ --}}






<!--============================
        MOBILE MENU START
    ==============================-->
<section id="wsus__mobile_menu">
    <span class="wsus__mobile_menu_close"><i class="fal fa-times"></i></span>
    <br>
    <form action="{{ route('products.index') }}">
        <input type="text" placeholder="Buscar..." name="search" value="{{ request()->search }}">
        <button type="submit" aria-label="Buscar"><i class="far fa-search"></i></button>
    </form>

    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-profile-tab" data-bs-toggle="pill"
                data-bs-target="#pills-profile" role="tab" aria-controls="pills-profile"
                aria-selected="false">Menu</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
                role="tab" aria-controls="pills-home" aria-selected="true">Categorias</button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-profile" role="tabpanel"
            aria-labelledby="pills-profile-tab">
            <div class="wsus__mobile_menu_main_menu">
                <div class="accordion accordion-flush" id="accordionFlushExample2">
                    <ul>
                        <li><a href="{{ route('index') }}">Inicio</a></li>
                        {{-- <li><a href="{{ route('price') }}">Cotizacion</a></li> --}}
                        <li><a href="{{ route('products.index') }}" id="productos-menu-trigger">Productos</a></li>
                        <li><a href="{{ route('about') }}">Nosotros</a></li>
                        <li class="wsus__relative_li">
                            <a href="#" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#services-collapse" aria-expanded="false" aria-controls="services-collapse">Servicios</a>
                            <div id="services-collapse" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <ul>
                                        <li><a href="{{ route('servicio-controladores-temperatura') }}">Instalacion de Controladores</a></li>
                                        <li><a href="{{ route('servicio-instalacion-videoregistradores') }}">Instalacion de Videoregistradores</a></li>
                                        <li><a href="{{ route('servicio-instalacion-medidoresdeflujo') }}">Instalacion de Medidores de Flujo</a></li>
                                        <li><a href="{{ route('servicio-instalacion-plc') }}">Configuracion, Instalacion y Proyecto llave en mano de PLC</a></li>
                                        <li><a href="{{ route('servicio-reparacion-videoregistradores') }}">Reparacion de Videoregistradores</a></li>
                                        <li><a href="{{ route('servicio-calibracion-ema') }}">Calibraciones EMA</a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <li><a href="{{ route('contact') }}">Contacto</a></li>
                        <li><a href="{{ route('associate') }}">Asociados y Revendedores</a></li>

                    </ul>
                    <ul>
                        @if (@auth()->check())
                            @if (@auth()->user()->role == 'user')
                                <li><a href="{{ route('user.profile') }}">Mi Cuenta</a></li>
                            @elseif (@auth()->user()->role == 'admin')
                                <li><a href="{{ route('admin.dashboard') }}">Mi Cuenta</a></li>
                            @endif
                        @else
                            <li><a href="{{ route('login') }}">Iniciar Sesión</a></li>
                            <li><a href="{{ route('login-register') }}" style="color: white">Registrarse</a></li>
                        @endif
                        <li>
                            <div class="wsus_logos-parther">
                                <img src="{{asset('frontend/images/iconos-empresas/mastercard-logo_with_bgc.webp')}}" alt="">
                                <img src="{{asset('frontend/images/iconos-empresas/Visa_logo_with_bgc.webp')}}" alt="">
                                <img src="{{asset('frontend/images/iconos-empresas/bank_BBVA-logo_with_bgc.webp')}}" alt="">
                                <img src="{{asset('frontend/images/iconos-empresas/Paypal-logo_with_bgc.webp')}}" alt="">
                                <img src="{{asset('frontend/images/iconos-empresas/delivery_DHL-logo_with_bgc.webp')}}" alt="">
                                <img src="{{asset('frontend/images/iconos-empresas/delivery_Estafeta-logo_with_bgc.webp')}}" alt="">
                                <img src="{{asset('frontend/images/iconos-empresas/delivery_paquete_express-logo.webp')}}" alt="">
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <div class="wsus__mobile_menu_main_menu">
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <ul class="wsus_mobile_menu_category">
                        @foreach ($categories as $categoryItem)
                        <li>
                            <a href="{{ route('products.index', ['category' => $categoryItem->slug]) }}"
                                class="{{ count($categoryItem->subCategories) > 0 ? 'accordion-button' : '' }}"
                                @if (count($categoryItem->subCategories) > 0)
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapseCategory{{ $loop->index }}"
                                @endif
                            >
                                <i class="{{ $categoryItem->icon }}"></i> {{ $categoryItem->name }}
                            </a>

                            @if (count($categoryItem->subCategories) > 0)
                                <div id="collapseCategory{{ $loop->index }}" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <ul>
                                            @foreach ($categoryItem->subCategories as $subCategoryItem)
                                                <li>
                                                    <a href="{{ route('products.index', ['subcategory' => $subCategoryItem->slug]) }}">
                                                        {{ $subCategoryItem->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </li>
                    @endforeach


                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!--============================
        MOBILE MENU END
    ==============================-->

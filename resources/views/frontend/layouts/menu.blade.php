@php
    $categories = \App\Models\Category::where('status', 1)
        ->with([
            'subCategories' => function ($query) {
                $query->where('status', 1)->with([
                    'childCategories' => function ($query) {
                        $query->where('status', 1);
                    },
                ]);
            },
        ])
        ->get();

@endphp
<nav class="wsus__main_menu d-none d-lg-block">
    <nav class="">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="relative_contect d-flex">
                        {{-- Menu --}}
                        {{-- <div class="wsus_menu_category_bar" >
                                <i class="far fa-bars"></i>
                            </div> --}}
                        {{-- <ul class="wsus_menu_cat_item show_home toggle_menu">
                                @foreach ($categories as $category)
                                <li><a class="{{count($category->subCategories) > 0 ? 'wsus__droap_arrow' : '' }}" href="#"><i class="{{$category->icon}}"></i>{{$category->name}}</a>
                                    @if (count($category->subCategories) > 0)
                                        <ul class="wsus_menu_cat_droapdown">
    
                                            @foreach ($category->subCategories as $subCategory)
    
                                            <li><a href="#">{{$subCategory->name}}<i class="{{count($subCategory->childCategories) > 0 ? 'fas fa-angle-right' : ''}}"></i></a>
                                                @if (count($subCategory->childCategories) > 0)
                                                <ul class="wsus__sub_category">
                                                    @foreach ($subCategory->childCategories as $childCategory)
                                                    <li><a href="#">{{$childCategory->name}}</a> </li>
                                                    @endforeach
                                                </ul>
                                                @endif
                                            </li>
                                            @endforeach
    
    
                                        </ul>
                                    @endif
                                </li>
                                @endforeach
                            </ul> --}}
    
                        <ul class="wsus__menu_item">
                            <li><a class="active" href="{{ route('index') }}">Inicio</a></li>
                            <li><a href="{{ route('price') }}">Cotizacion</a></li>
                            <li><a href="{{ route('about') }}">Nosotros</a></li>
                        </ul>
    
                        <div class="wsus_menu_category_bar">
                            <p style="text-decoration: none;">Productos<i class="fas fa-caret-down"></i></p>
                        </div>
                        {{-- <li class="wsus__menu_item show_home toggle_menu"> --}}
                        <ul class="wsus_menu_cat_item show_home toggle_menu">
                            @foreach ($categories as $category)
                                <li>
                                    <a class="{{ count($category->subCategories) > 0 ? 'wsus__droap_arrow' : '' }}"
                                        href="{{ route('products.index', ['category' => $category->slug]) }}"><i></i>{{ $category->name }}</a>
                                    @if (count($category->subCategories) > 0)
                                        <ul class="wsus_menu_cat_droapdown">
                                            @foreach ($category->subCategories as $subCategory)
                                                <li><a
                                                        href="{{ route('products.index', ['subcategory' => $subCategory->slug]) }}">{{ $subCategory->name }}<i
                                                            class="{{ count($subCategory->childCategories) > 0 ? 'fas fa-angle-right' : '' }}"></i></a>
    
                                                    @if (count($subCategory->childCategories) > 0)
                                                        <ul class="wsus__sub_category">
                                                            @foreach ($subCategory->childCategories as $childCategory)
                                                                <li><a
                                                                        href="{{ route('products.index', ['childcategory' => $childCategory->slug]) }}">{{ $childCategory->name }}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        {{-- </li> --}}
                        {{-- <li><a href="vendor.html">vendor</a></li> --}}
                        <ul class="wsus__menu_item">
                            
                            <li class="wsus__relative_li"><a href="#">Servicios <i class="fas fa-caret-down"></i></a>
                                <ul class="wsus__menu_droapdown">
                                    <li><a href="{{ route('sistemas') }}">Sistemas De Control</a></li>
                                    
                                    <li><a href="{{ route('calibracion-puesta') }}">Calibración y Puesta en Marcha</a></li>
                                </ul>
                            </li>
                            <li><a href="{{ route('contact') }}">Contacto</a></li>
                            <li><a href="{{ route('associate') }}">Asociados y Revendedores</a></li>
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
        </div>
    </nav>
    <nav class="wsus__main_menu2">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="relative_contect2 d-flex">
                        <ul class="wsus__menu_item" id="social-menu">
                            <li class="d-flex justify-content-center align-items-center" style="height: 100%;">
                                <p style="color: white;">Envio Gratis a partir de $3,000MXN</p>
                            </li>
                            <li class="">
                                <a href="https://wa.link/f28njw" class="whatsapp" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffffff"
                                        class="bi bi-whatsapp" viewBox="0 0 16 16">
                                        <path
                                            d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                    </svg>
                                </a>
                            </li>
                            <li class="">
                                <a href="https://www.facebook.com/macdelnorteofficial" class="facebook" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffffff"
                                        class="bi bi-facebook " viewBox="0 0 16 16">
                                        <path
                                            d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
                                    </svg>
                                </a>
        
                            </li>
                            <li class="">
                                <a href="https://twitter.com/MacDelNorte" class="twitter" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffffff"
                                        class="bi bi-twitter-x" viewBox="0 0 16 16">
                                        <path
                                            d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z" />
                                    </svg>
                                </a>
        
                            </li>
                            <li class="">
                                <a href="https://www.youtube.com/@macdelnorte7889" class="youtube" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffffff"
                                        class="bi bi-youtube" viewBox="0 0 16 16">
                                        <path
                                            d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z" />
                                    </svg>
                                </a>
        
                            </li>
                            <li class="">
                                <a href="https://www.instagram.com/mac.del.norte/" class="instagram" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffffff"
                                        class="bi bi-instagram" viewBox="0 0 16 16">
                                        <path
                                            d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.420a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.920s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334" />
                                    </svg>
                                </a>
        
                            </li>
                            <li class="">
                                <a href="https://www.linkedin.com/company/mac-del-norte/" class="linkedin" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffffff"
                                        class="bi bi-linkedin" viewBox="0 0 16 16">
                                        <path
                                            d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z" />
                                    </svg>
                                </a>
        
                            </li>
                            <li class="">
                                <a href="https://www.tiktok.com/@macdelnorteoficial" class="instagram" target="_blank" ><i class="fab fa-tiktok" style="color: white"></i></a>
                            </li>
                            <li class="d-flex justify-content-center align-items-center" style="height: 100%;">
                                <p style="color: white;">Ya Incluyen IVA!!</p>
                            </li>
                        </ul>
                        <ul class="wsus__menu_item wsus__menu_item_right">
                            @if (@auth()->check())
                                @if (@auth()->user()->role == 'user')
                                    <li><a href="{{ route('user.profile') }}" style="color: white">Mi Cuenta</a></li>
                                @elseif (@auth()->user()->role == 'admin')
                                    <li><a href="{{ route('admin.dashboard') }}" style="color: white">Mi Cuenta</a></li>
                                @elseif (@auth()->user()->role == 'associate')
                                    <li><a href="{{ route('associate.dashboard') }}" style="color: white">Mi Cuenta</a></li>
                                @endif
                            @else
                                <li><a href="{{ route('login') }}" style="color: white" >Iniciar Sesion</a></li>
                                <li><a href="{{ route('login-register') }}" style="color: white">Registrarse</a></li>
    
                            @endif
    
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
</nav>






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
                        <li><a href="{{ route('price') }}">Cotizacion</a></li>
                        <li><a href="{{ route('products.index') }}" id="productos-menu-trigger">Productos</a></li>
                        <li><a href="{{ route('about') }}">Nosotros</a></li>
                        <li class="wsus__relative_li">
                            <a href="#" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#services-collapse" aria-expanded="false" aria-controls="services-collapse">Servicios</a>
                            <div id="services-collapse" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <ul>
                                        <li><a href="{{ route('sistemas') }}">Sistemas De Control</a></li>
                                        
                                        <li><a href="{{ route('calibracion-puesta') }}">Calibración y Puesta en Marcha</a></li>
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
                            <li><a href="{{ route('login') }}">Iniciar Sesion</a></li>
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
    
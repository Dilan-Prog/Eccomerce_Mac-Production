{{--
    MEGA MENÚ DE NAVEGACIÓN — Mac Del Norte
    Recibe: $navCategories (Collection de Category con subCategories → childCategories)
    Íconos: FontAwesome class strings almacenados en $category->icon
    Rutas: products.index con ?category=slug / ?subcategory=slug
--}}
<nav class="main-nav" role="navigation" aria-label="Categorías de productos">
    <div class="container nav-inner">

        {{-- GRID DE CATEGORÍAS (6 columnas) --}}
        <div class="nav-categories">

            @foreach($navCategories as $category)
            <div class="nav-item" aria-haspopup="{{ $category->subCategories->isNotEmpty() ? 'true' : 'false' }}">

                {{-- LINK PRINCIPAL DE LA CATEGORÍA --}}
                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                   class="nav-link {{ $loop->last ? 'brand-line' : '' }}"
                   aria-expanded="false"
                   aria-label="Categoría {{ $category->name }}">

                    @if($category->icon)
                        <i class="{{ $category->icon }} icon" aria-hidden="true"></i>
                    @endif

                    <span>{{ $category->name }}</span>

                    @if($category->subCategories->isNotEmpty())
                        <svg class="chevron" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    @endif
                </a>

                {{-- MEGA MENÚ DE ESTA CATEGORÍA --}}
                @if($category->subCategories->isNotEmpty())
                <div class="mega-menu" role="region" aria-label="Subcategorías de {{ $category->name }}">
                    <div class="container">
                        <div class="mega-menu-inner">

                            {{-- SIDEBAR IZQUIERDO --}}
                            <div class="mega-sidebar {{ $loop->last ? 'brand' : '' }}">

                                <div class="mega-sidebar-icon" aria-hidden="true">
                                    @if($category->icon)
                                        <i class="{{ $category->icon }}"></i>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
                                        </svg>
                                    @endif
                                </div>

                                @if($loop->last)
                                    <span class="mega-sidebar-brand-badge">Dist. autorizado</span>
                                @endif

                                <h4>{{ $category->name }}</h4>

                                <p>Consulta nuestro catálogo completo de {{ strtolower($category->name) }}.</p>

                                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                                   class="mega-sidebar-cta">
                                    Ver catálogo
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2.5" width="14" height="14" aria-hidden="true">
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>

                            {{-- COLUMNAS DE SUBCATEGORÍAS --}}
                            <div class="mega-cols {{ $category->subCategories->count() <= 4 ? 'mega-cols-4' : '' }}">

                                @foreach($category->subCategories->take(5) as $sub)
                                <div class="mega-col">
                                    <a href="{{ route('products.index', ['subcategory' => $sub->slug]) }}"
                                       class="mega-col-title">
                                        {{ $sub->name }}
                                    </a>

                                    @if($sub->childCategories->isNotEmpty())
                                        <ul class="mega-col-items">
                                            @foreach($sub->childCategories->take(6) as $child)
                                            <li>
                                                <a href="{{ route('products.index', ['subcategory' => $child->slug]) }}">
                                                    {{ $child->name }}
                                                </a>
                                            </li>
                                            @endforeach
                                        </ul>

                                        @if($sub->childCategories->count() > 6)
                                        <a href="{{ route('products.index', ['subcategory' => $sub->slug]) }}"
                                           class="mega-col-more">
                                            Ver todos →
                                        </a>
                                        @endif
                                    @else
                                        {{-- Sin tercer nivel: link directo a la subcategoría --}}
                                        <ul class="mega-col-items">
                                            <li>
                                                <a href="{{ route('products.index', ['subcategory' => $sub->slug]) }}">
                                                    Ver todos los productos
                                                </a>
                                            </li>
                                        </ul>
                                    @endif
                                </div>
                                @endforeach

                            </div>
                            {{-- /mega-cols --}}

                        </div>
                        {{-- /mega-menu-inner --}}
                    </div>
                </div>
                {{-- /mega-menu --}}
                @endif

            </div>
            {{-- /nav-item --}}
            @endforeach

        </div>
        {{-- /nav-categories --}}

        {{-- REDES SOCIALES --}}
        <div class="nav-social">
            @include('frontend.layouts.partials.nav-social-links')
        </div>

    </div>
    {{-- /nav-inner --}}
</nav>

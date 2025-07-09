<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('index') }}">Mac Del Norte</a>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-header">Panel De Administracion</li>

            <li class="dropdown active">
                <a href="{{ route('admin.dashboard') }}" class="nav-link"><i
                        class="fas fa-home"></i><span>Escritorio</span></a>

            </li>

            <li class="menu-header">Paneles</li>

            <li
                class="dropdown {{ setActive([
                    'admin.order.*',
                    'admin.pending.orders',
                    'admin.processed-orders',
                    'admin.dropped-off-orders',
                    'admin.shipped-orders',
                    'admin.out-for-delivery-orders',
                    'admin.delivered-orders',
                    'admin.canceled-orders',
                ]) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-clipboard-list"></i> <span>Panel De Control</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.order.*']) }}"><a class="nav-link"
                            href="{{ route('admin.order.index') }}">Todas Las Ordenes</a></li>
                    <li class="mb-3 {{ setActive(['admin.pending.orders']) }}"><a class="nav-link"
                            href="{{ route('admin.pending.orders') }}">Ordenes Pendientes</a></li>
                    <li class="mb-3 {{ setActive(['admin.processed-orders']) }}"><a class="nav-link"
                            href="{{ route('admin.processed-orders') }}">Procesadas y listas para enviar</a></li>
                    <li class="{{ setActive(['admin.dropped-off']) }}"><a class="nav-link"
                            href="{{ route('admin.dropped-off-orders') }}">Entregadas al transportista</a></li>
                    <li class="{{ setActive(['admin.shipped-orders']) }}"><a class="nav-link"
                            href="{{ route('admin.shipped-orders') }}">Ordenes Enviado</a></li>
                    <li class="{{ setActive(['admin.out-for-delivery-orders']) }}"><a class="nav-link"
                            href="{{ route('admin.out-for-delivery-orders') }}">En ruta de entrega</a></li>
                    <li class="{{ setActive(['admin.delivered-orders']) }}"><a class="nav-link"
                            href="{{ route('admin.delivered-orders') }}">Ordenes Entregadas</a></li>
                    <li class="{{ setActive(['admin.canceled-orders']) }}"><a class="nav-link"
                            href="{{ route('admin.canceled-orders') }}">Ordenes Canceladas</a></li>

                </ul>
            </li>
            <li class="{{ setActive(['admin.transaction']) }}"><a class="nav-link"
                    href="{{ route('admin.transaction') }}"><i class="fas fa-money-check-alt"></i>
                    <span>Transacciones</span></a>
            </li>

            <li
                class="dropdown {{ setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-tags"></i>
                    <span>Administrar Categorias</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.category.*']) }}"><a class="nav-link"
                            href="{{ route('admin.category.index') }}">Categoria</a></li>
                    <li class="{{ setActive(['admin.sub-category.*']) }}"><a class="nav-link"
                            href="{{ route('admin.sub-category.index') }}">Sub Categorias</a></li>
                    <li class="{{ setActive(['admin.child-category.*']) }}"><a class="nav-link"
                            href="{{ route('admin.child-category.index') }}">Categorias Secundarias</a></li>

                </ul>
            </li>

            <li class="dropdown {{ setActive(['admin.brand.*', 'admin.products.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-box"></i>
                    <span>Administrar Productos</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.brand.*']) }}"><a class="nav-link"
                            href="{{ route('admin.brand.index') }}"><i class="fas fa-cube"></i>Marcas</a></li>
                    <li class="{{ setActive(['admin.products.*']) }}"><a class="nav-link"
                            href="{{ route('admin.products.index') }}"> <i class="fas fa-box"></i>Productos</a></li>
                </ul>
            </li>

            <li
                class="dropdown {{ setActive(['admin.flash-sale.*', 'admin.coupons.*', 'admin.shipping-rule.*', 'admin.payment-settings.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-store"></i>
                    <span>Eccomerce</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.flash-sale.*']) }}"><a class="nav-link"
                            href="{{ route('admin.flash-sale.index') }}"> <i class="fas fa-bolt"></i>Flash Sale</a>
                    </li>
                    <li class="{{ setActive(['admin.coupons.*']) }}"><a class="nav-link"
                            href="{{ route('admin.coupons.index') }}"><i class="fas fa-ticket-alt"></i>Cupones</a></li>
                    <li class="{{ setActive(['admin.shipping-rule.*']) }}"><a class="nav-link"
                            href="{{ route('admin.shipping-rule.index') }}"> <i class="fas fa-truck"></i>Reglas de
                            envío</a></li>
                    <li class="{{ setActive(['admin.payment-settings.*']) }}"><a class="nav-link"
                            href="{{ route('admin.payment-settings.index') }}"> <i
                                class="fas fa-credit-card"></i>Ajustes De Pago</a></li>

                </ul>
            </li>

            <li class="dropdown {{ setActive(['admin.flash-sale.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"> <i class="fas fa-ad"></i>
                    <span>Publicidad</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.flash-sale.*']) }}"><a class="nav-link"
                            href="{{ route('admin.flash-sale.index') }}"> 
                            {{-- color: #868e96; ANIMAR COLOR --}}
                            <svg role="img" viewBox="0 0 24 24" style="width: 28px; height: 13px;"
                                xmlns="http://www.w3.org/2000/svg">
                                <title>Google Ads</title>
                                <path
                                    d="M3.9998 22.9291C1.7908 22.9291 0 21.1383 0 18.9293s1.7908-3.9998 3.9998-3.9998 3.9998 1.7908 3.9998 3.9998-1.7908 3.9998-3.9998 3.9998zm19.4643-6.0004L15.4632 3.072C14.3586 1.1587 11.9121.5028 9.9988 1.6074S7.4295 5.1585 8.5341 7.0718l8.0009 13.8567c1.1046 1.9133 3.5511 2.5679 5.4644 1.4646 1.9134-1.1046 2.568-3.5511 1.4647-5.4644zM7.5137 4.8438L1.5645 15.1484A4.5 4.5 0 0 1 4 14.4297c2.5597-.0075 4.6248 2.1585 4.4941 4.7148l3.2168-5.5723-3.6094-6.25c-.4499-.7793-.6322-1.6394-.5878-2.4784z" />
                            </svg>Google Ads</a></li>
                </ul>
            </li>

            <li
                class="dropdown {{ setActive(['admin.manage-user.*', 'admin.admin-list.index', 'admin.customer.index']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-users"></i><span>Usuarios</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.customer.index']) }}"><a class="nav-link"
                            href="{{ route('admin.customer.index') }}"> <i
                                class="fas fa-user"></i>Usuarios/Clientes</a></li>
                    <li class="{{ setActive(['admin.manage-user.*']) }}"><a class="nav-link"
                            href="{{ route('admin.manage-user') }}"> <i class="fas fa-users"></i>Control De
                            Usuarios</a></li>
                    <li class="{{ setActive(['admin.admin-list.index']) }}"><a class="nav-link"
                            href="{{ route('admin.admin-list.index') }}"> <i
                                class="fas fa-user-shield"></i>Administradores</a></li>
                </ul>
            </li>


            <li class="dropdown {{ setActive(['admin.slider.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-globe"></i>
                    <span>Administrar Sitio Web</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.slider.*']) }}">
                        <a class="nav-link" href="{{ route('admin.slider.index') }}"> <i
                                class="fas fa-image"></i>Slider</a>
                    </li>

                </ul>
            </li>
            <li><a class="nav-link" href="{{ route('admin.settings.index') }}"> <i
                        class="fas fa-cog"></i><span>Configuracion General</span></a></li>

            {{-- <li class="dropdown">
          <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i> <span>Layout</span></a>
          <ul class="dropdown-menu">
            <li><a class="nav-link" href="layout-default.html">Default Layout</a></li>
            <li><a class="nav-link" href="layout-transparent.html">Transparent Sidebar</a></li>
            <li><a class="nav-link" href="layout-top-navigation.html">Top Navigation</a></li>
          </ul>
        </li> --}}



        </ul>
    </aside>
</div>

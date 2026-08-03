{{-- New admin-ui sidebar. Mirrors the IA of resources/views/admin/layouts/sidebar.blade.php,
     reusing the existing setActive() helper (framework-agnostic, no changes needed).

     Every nav item below is gated by User::canAccessModule(module_key), the
     same check each controller's `can-access-module:{key}` middleware runs —
     module_key values come from config/admin-modules.php. For a legacy/full
     admin (role_id === null) canAccessModule() always returns true, so this
     is a no-op for them; only a restricted custom-role admin ever sees a
     trimmed-down menu. "Escritorio" is the one exception: it's the mandatory
     post-login landing page (AdminController::dashboard() is deliberately
     NOT gated either — see its doc comment) and is always shown so a
     restricted admin always has a way back to it. --}}
@php
    $__auUser = auth()->user();
@endphp
<aside class="au-sidebar" id="au-sidebar">
    <div class="au-sidebar-brand">
        <a href="{{ route('index') }}" class="au-sidebar-brand-link" style="color:inherit">
            <img src="{{ asset('uploads/logo/webp-horizontal.webp') }}" alt="Mac Del Norte" class="au-sidebar-brand-logo">
        </a>
    </div>

    <nav class="au-sidebar-nav">
        <div class="au-nav-header">Panel De Administracion</div>

        <div class="au-nav-item {{ setActive(['admin.dashboard']) ? 'is-active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="au-nav-link">
                <i class="fas fa-home au-nav-icon"></i><span>Escritorio</span>
            </a>
        </div>

        <div class="au-nav-header">Paneles</div>

        @php
            $orderActive = setActive([
                'admin.order.*',
                'admin.pending.orders',
                'admin.processed-orders',
                'admin.dropped-off-orders',
                'admin.shipped-orders',
                'admin.out-for-delivery-orders',
                'admin.delivered-orders',
                'admin.canceled-orders',
            ]);
        @endphp
        @if ($__auUser->canAccessModule('orders'))
        <div class="au-nav-item has-submenu {{ $orderActive ? 'is-active is-open' : '' }}" data-nav-key="orders">
            <a href="#" class="au-nav-link">
                <i class="fas fa-clipboard-list au-nav-icon"></i><span>Panel De Control</span>
                <i class="fas fa-chevron-right au-nav-caret"></i>
            </a>
            <div class="au-nav-submenu">
                <a href="{{ route('admin.order.index') }}" class="au-nav-link">Todas Las Ordenes</a>
                <a href="{{ route('admin.order.index') }}?filter=pendiente" class="au-nav-link">Ordenes Pendientes</a>
                <a href="{{ route('admin.order.index') }}?filter=procesado" class="au-nav-link">Procesadas y listas para enviar</a>
                <a href="{{ route('admin.order.index') }}?filter=entregado_transportista" class="au-nav-link">Entregadas al transportista</a>
                <a href="{{ route('admin.order.index') }}?filter=enviado" class="au-nav-link">Ordenes Enviado</a>
                <a href="{{ route('admin.order.index') }}?filter=en_ruta" class="au-nav-link">En ruta de entrega</a>
                <a href="{{ route('admin.order.index') }}?filter=entregado" class="au-nav-link">Ordenes Entregadas</a>
                <a href="{{ route('admin.order.index') }}?filter=cancelado" class="au-nav-link">Ordenes Canceladas</a>
            </div>
        </div>
        @endif

        @if ($__auUser->canAccessModule('transactions'))
        <div class="au-nav-item {{ setActive(['admin.transaction']) ? 'is-active' : '' }}">
            <a href="{{ route('admin.transaction') }}" class="au-nav-link">
                <i class="fas fa-money-check-alt au-nav-icon"></i><span>Transacciones</span>
            </a>
        </div>
        @endif

        @if ($__auUser->canAccessModule('cotizaciones'))
        <div class="au-nav-item {{ setActive(['admin.cotizaciones.*']) ? 'is-active' : '' }}">
            <a href="{{ route('admin.cotizaciones.index') }}" class="au-nav-link">
                <i class="fas fa-file-invoice au-nav-icon"></i><span>Cotizaciones</span>
            </a>
        </div>
        @endif

        @php
            $catActive = setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*']);
        @endphp
        @if ($__auUser->canAccessModule('categories'))
        <div class="au-nav-item has-submenu {{ $catActive ? 'is-active is-open' : '' }}" data-nav-key="categories">
            <a href="#" class="au-nav-link">
                <i class="fas fa-tags au-nav-icon"></i><span>Administrar Categorias</span>
                <i class="fas fa-chevron-right au-nav-caret"></i>
            </a>
            <div class="au-nav-submenu">
                <a href="{{ route('admin.category.index') }}" class="au-nav-link">Categoria</a>
                <a href="{{ route('admin.sub-category.index') }}" class="au-nav-link">Sub Categorias</a>
                <a href="{{ route('admin.child-category.index') }}" class="au-nav-link">Categorias Secundarias</a>
            </div>
        </div>
        @endif

        @php
            $productActive = setActive(['admin.brand.*', 'admin.products.*']);
        @endphp
        @if ($__auUser->canAccessModule('products'))
        <div class="au-nav-item has-submenu {{ $productActive ? 'is-active is-open' : '' }}" data-nav-key="products">
            <a href="#" class="au-nav-link">
                <i class="fas fa-box au-nav-icon"></i><span>Administrar Productos</span>
                <i class="fas fa-chevron-right au-nav-caret"></i>
            </a>
            <div class="au-nav-submenu">
                <a href="{{ route('admin.brand.index') }}" class="au-nav-link">Marcas</a>
                <a href="{{ route('admin.products.index') }}" class="au-nav-link">Productos</a>
            </div>
        </div>
        @endif

        @if ($__auUser->canAccessModule('aspel'))
        <div class="au-nav-item has-submenu {{ setActive(['admin.sync-aspel.*']) ? 'is-active is-open' : '' }}" data-nav-key="aspel">
            <a href="#" class="au-nav-link">
                <i class="fas fa-tags au-nav-icon"></i><span>Aspel Sincronizacion</span>
                <i class="fas fa-chevron-right au-nav-caret"></i>
            </a>
            <div class="au-nav-submenu">
                <a href="{{ route('admin.sync-aspel.index') }}" class="au-nav-link">Productos</a>
            </div>
        </div>
        @endif

        @php
            $ecommerceActive = setActive(['admin.flash-sale.*', 'admin.coupons.*', 'admin.shipping-rule.*', 'admin.payment-settings.*']);
        @endphp
        @if ($__auUser->canAccessModule('ecommerce'))
        <div class="au-nav-item has-submenu {{ $ecommerceActive ? 'is-active is-open' : '' }}" data-nav-key="ecommerce">
            <a href="#" class="au-nav-link">
                <i class="fas fa-store au-nav-icon"></i><span>Eccomerce</span>
                <i class="fas fa-chevron-right au-nav-caret"></i>
            </a>
            <div class="au-nav-submenu">
                <a href="{{ route('admin.flash-sale.index') }}" class="au-nav-link">Venta Flash</a>
                <a href="{{ route('admin.coupons.index') }}" class="au-nav-link">Cupones</a>
                <a href="{{ route('admin.shipping-rule.index') }}" class="au-nav-link">Reglas de envío</a>
                <a href="{{ route('admin.payment-settings.index') }}" class="au-nav-link">Ajustes De Pago</a>
            </div>
        </div>
        @endif

        @if ($__auUser->canAccessModule('ads'))
        <div class="au-nav-item has-submenu {{ setActive(['admin.track-conversion.*']) ? 'is-active is-open' : '' }}" data-nav-key="ads">
            <a href="#" class="au-nav-link">
                <i class="fas fa-ad au-nav-icon"></i><span>Publicidad</span>
                <i class="fas fa-chevron-right au-nav-caret"></i>
            </a>
            <div class="au-nav-submenu">
                <a href="{{ route('admin.track-conversion.index') }}" class="au-nav-link">Google Ads</a>
            </div>
        </div>
        @endif

        @php
            $usersActive = setActive(['admin.customer.index', 'admin.roles.*', 'admin.staff-users.*']);
        @endphp
        @if ($__auUser->canAccessModule('clientes') || $__auUser->canAccessModule('staff'))
        <div class="au-nav-item has-submenu {{ $usersActive ? 'is-active is-open' : '' }}" data-nav-key="users">
            <a href="#" class="au-nav-link">
                <i class="fas fa-users au-nav-icon"></i><span>Usuarios</span>
                <i class="fas fa-chevron-right au-nav-caret"></i>
            </a>
            <div class="au-nav-submenu">
                @if ($__auUser->canAccessModule('clientes'))
                    <a href="{{ route('admin.customer.index') }}" class="au-nav-link">Usuarios/Clientes</a>
                @endif
                @if ($__auUser->canAccessModule('staff'))
                    <a href="{{ route('admin.staff-users.index') }}" class="au-nav-link">Personal / Staff</a>
                    <a href="{{ route('admin.roles.index') }}" class="au-nav-link">Roles y Permisos</a>
                @endif
            </div>
        </div>
        @endif

        @php
            $siteActive = setActive(['admin.slider.*', 'admin.media-library.*']);
        @endphp
        @if ($__auUser->canAccessModule('site'))
        <div class="au-nav-item has-submenu {{ $siteActive ? 'is-active is-open' : '' }}" data-nav-key="site">
            <a href="#" class="au-nav-link">
                <i class="fas fa-globe au-nav-icon"></i><span>Administrar Sitio Web</span>
                <i class="fas fa-chevron-right au-nav-caret"></i>
            </a>
            <div class="au-nav-submenu">
                <a href="{{ route('admin.slider.index') }}" class="au-nav-link">Slider</a>
                @if (Route::has('admin.media-library.index'))
                    <a href="{{ route('admin.media-library.index') }}" class="au-nav-link">Biblioteca de Medios</a>
                @endif
            </div>
        </div>
        @endif

        @if ($__auUser->canAccessModule('settings'))
        <div class="au-nav-item {{ setActive(['admin.settings.index']) ? 'is-active' : '' }}">
            <a href="{{ route('admin.settings.index') }}" class="au-nav-link">
                <i class="fas fa-cog au-nav-icon"></i><span>Configuracion General</span>
            </a>
        </div>
        @endif
    </nav>
</aside>

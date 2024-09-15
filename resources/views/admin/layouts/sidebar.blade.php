<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
      <div class="sidebar-brand">
        <a href="{{route('index')}}">Mac Del Norte</a>
      </div>

      <ul class="sidebar-menu">
        <li class="menu-header">Panel De Administracion</li>

            <li class="dropdown active">
                <a href="{{route('admin.dashboard')}}" class="nav-link"><i class="fas fa-fire"></i><span>Escritorio</span></a>

            </li>

        <li class="menu-header">Paneles</li>

        <li class="dropdown {{ setActive([
            'admin.order.*',
            'admin.pending.orders',
            'admin.processed-orders',
            'admin.dropped-off-orders',
            'admin.shipped-orders',
            'admin.out-for-delivery-orders',
            'admin.delivered-orders',
            'admin.canceled-orders',

            ])}}">
          <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i> <span>Panel De Control</span></a>
          <ul class="dropdown-menu">
            <li class="{{setActive(['admin.order.*'])}}" ><a class="nav-link" href="{{route('admin.order.index')}}">Todas Las Ordenes</a></li>
            <li class="mb-3 {{setActive(['admin.pending.orders'])}}" ><a class="nav-link" href="{{route('admin.pending.orders')}}">Ordenes Pendientes</a></li>
            <li class="mb-3 {{ setActive(['admin.processed-orders']) }}"><a class="nav-link" href="{{ route('admin.processed-orders') }}">Procesadas y listas para enviar</a></li>
            <li class="{{ setActive(['admin.dropped-off']) }}"><a class="nav-link" href="{{ route('admin.dropped-off-orders') }}">Entregadas al transportista</a></li>
            <li class="{{ setActive(['admin.shipped-orders']) }}"><a class="nav-link" href="{{ route('admin.shipped-orders') }}">Ordenes Enviado</a></li>
            <li class="{{ setActive(['admin.out-for-delivery-orders']) }}"><a class="nav-link" href="{{ route('admin.out-for-delivery-orders') }}">En ruta de entrega</a></li>
            <li class="{{ setActive(['admin.delivered-orders']) }}"><a class="nav-link" href="{{ route('admin.delivered-orders') }}">Ordenes Entregadas</a></li>
            <li class="{{ setActive(['admin.canceled-orders']) }}"><a class="nav-link" href="{{ route('admin.canceled-orders') }}">Ordenes Canceladas</a></li>

          </ul>
        </li>
        <li class="{{ setActive(['admin.transaction']) }}"><a class="nav-link"
            href="{{ route('admin.transaction') }}"><i class="fas fa-money-bill-alt"></i>
            <span>Transactions</span></a>
        </li>

        <li class="dropdown {{ setActive([
            'admin.category.*',
            'admin.sub-category.*',
            'admin.child-category.*'
            ])}}">
          <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i> <span>Administrar Categorias</span></a>
          <ul class="dropdown-menu">
            <li class="{{setActive(['admin.category.*'])}}" ><a class="nav-link" href="{{route('admin.category.index')}}">Categoria</a></li>
            <li class="{{setActive(['admin.sub-category.*'])}}" ><a class="nav-link" href="{{route('admin.sub-category.index')}}">Sub Categorias</a></li>
            <li class="{{setActive(['admin.child-category.*'])}}" ><a class="nav-link" href="{{route('admin.child-category.index')}}">Categorias Secundarias</a></li>

          </ul>
        </li>

        <li class="dropdown {{ setActive([
          'admin.brand.*',
          'admin.products.*'

        ])}}">
          <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i> <span>Administrar Productos</span></a>
          <ul class="dropdown-menu">
            <li class="{{setActive(['admin.brand.*'])}}"><a class="nav-link" href="{{route('admin.brand.index')}}">Marcas</a></li>
            <li class="{{setActive(['admin.products.*'])}}"><a class="nav-link" href="{{route('admin.products.index')}}">Productos</a></li>
          </ul>
        </li>

        <li class="dropdown {{ setActive([
          'admin.flash-sale.*',
          'admin.coupons.*',
          'admin.shipping-rule.*',
          'admin.payment-settings.*',


        ])}}">
          <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i> <span>Eccomerce</span></a>
          <ul class="dropdown-menu">
            <li class="{{setActive(['admin.flash-sale.*'])}}"><a class="nav-link" href="{{route('admin.flash-sale.index')}}">Flash Sale</a></li>
            <li class="{{setActive(['admin.coupons.*'])}}"><a class="nav-link" href="{{route('admin.coupons.index')}}">Cupones</a></li>
            <li class="{{setActive(['admin.shipping-rule.*'])}}"><a class="nav-link" href="{{route('admin.shipping-rule.index')}}">Reglas de envío</a></li>
            <li class="{{setActive(['admin.payment-settings.*'])}}"><a class="nav-link" href="{{route('admin.payment-settings.index')}}">Ajustes De Pago</a></li>

          </ul>
        </li>

        <li class="dropdown {{ setActive([
            'admin.manage-user.*',
            'admin.admin-list.index',
            'admin.customer.index',


          ])}}">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i> <span>Usuarios</span></a>
            <ul class="dropdown-menu">
                <li class="{{ setActive(['admin.customer.index']) }}"><a class="nav-link" href="{{ route('admin.customer.index') }}">Customer list</a></li>
              <li class="{{setActive(['admin.manage-user.*'])}}"><a class="nav-link" href="{{route('admin.manage-user')}}">Control De Usuarios</a></li>
              <li class="{{setActive(['admin.admin-list.index'])}}"><a class="nav-link" href="{{route('admin.admin-list.index')}}">Admin List</a></li>
            </ul>
          </li>


        <li class="dropdown {{ setActive([
          'admin.slider.*',

        ])}}">
          <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i> <span>Administrar Website</span></a>
          <ul class="dropdown-menu">
            <li class="{{setActive(['admin.slider.*'])}}">
              <a class="nav-link" href="{{route('admin.slider.index')}}">Slider</a></li>

          </ul>
        </li>
        <li><a class="nav-link" href="{{route('admin.settings.index')}}"><i class="far far-square"></i><span>Configuracion General</span></a></li>

        {{--<li class="dropdown">
          <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i> <span>Layout</span></a>
          <ul class="dropdown-menu">
            <li><a class="nav-link" href="layout-default.html">Default Layout</a></li>
            <li><a class="nav-link" href="layout-transparent.html">Transparent Sidebar</a></li>
            <li><a class="nav-link" href="layout-top-navigation.html">Top Navigation</a></li>
          </ul>
        </li>--}}



      </ul>
    </aside>
  </div>

<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
      <div class="sidebar-brand">
        <a href="{{route('index')}}">Mac Del Norte</a>
      </div>

      <ul class="sidebar-menu">
        <li class="menu-header">Panel De Asociados</li>

            <li class="dropdown active">
                <a href="{{route('associate.dashboard')}}" class="nav-link"><i class="fas fa-fire"></i><span>Noticias</span></a>
            </li>

        <li class="menu-header">Paneles</li>

        <li class="dropdown {{ setActive([
            'associate.products.*',
            ])}}">
          <a href="{{route('associate.products.index')}}" class="nav-link" > <i class="fas fa-clipboard-list" style='font-size:24px' aria-hidden="true"></i> <span>Productos</span></a>
          
        </li>
      </ul>
    </aside>
  </div>

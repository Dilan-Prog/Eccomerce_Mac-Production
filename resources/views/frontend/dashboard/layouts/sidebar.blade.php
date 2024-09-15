<div class="dashboard_sidebar">
    <span class="close_icon">
      <i class="far fa-bars dash_bar"></i>
      <i class="far fa-times dash_close"></i>
    </span>
    <a href="{{route('index')}}" class="dash_logo"><img src="{{asset('frontend/images/logo/Aviblnco-largo.png')}}" alt="logo" class="img-fluid"></a>
    <ul class="dashboard_link">

      <li><a href="{{ route('index') }}"><i class="fas fa-home"></i> Ir a Inicio</a></li>
      <li><a href="{{ route('user.orders.index') }}"><i class="fas fa-list-ul"></i> Pedidos</a></li>
      <li><a href="{{route('user.profile')}}"><i class="far fa-user"></i> Mi Perfil</a></li>
      <li><a href="{{route('user.address.index')}}"><i class="fal fa-gift-card"></i> Direcciones</a></li>
      <li>
        <!-- Authentication -->
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <a href="{{route('logout')}}" onclick="event.preventDefault(); this.closest('form').submit();"> <i class="far fa-sign-out-alt"></i> Cerrar Sesion</a>

      </form>


      </li>

    </ul>
  </div>

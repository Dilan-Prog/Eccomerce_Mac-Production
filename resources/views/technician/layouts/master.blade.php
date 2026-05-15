{{-- resources/views/technician/layouts/master.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Panel Técnico — MAC DEL NORTE</title>

  <link rel="stylesheet" href="{{ asset('backend/assets/modules/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/assets/modules/fontawesome/css/all.min.css') }}">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <link rel="stylesheet" href="{{ asset('backend/assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/assets/css/components.css') }}">
  @stack('styles')

  <style>
    :root {
      /* Paleta MAC DEL NORTE */
      --Primary-Blue-Dark:       #00468c;
      --Secondaryy-Blue-Dark:    #7fbae7;
      --Tercer-Blue-Dark:        #53bdfe;
      --Secondary-Blue-Low:      #bfdfff;
      --Primary-Blue-Dark-Hover: #005ab5;
      --Backgroud-pure-white:    #ffffff;
      --Background-black-2:      #212121;
      --Backgroud-black:         #000000;
      --Backgroud-Product:       #ececec;
      --Background:              #f4f4f4;
      --White-Box:               #ffffff;
      --Text-Primary-white:      #ffffff;
      --Text-Primary-black:      #000000;
      --Text-Secondary:          #313131;
      /* Aliases semánticos */
      --navy:         var(--Primary-Blue-Dark);
      --navy-light:   var(--Primary-Blue-Dark);
      --navy-mid:     var(--Primary-Blue-Dark-Hover);
      --orange:       var(--Tercer-Blue-Dark);
      --orange-hover: var(--Primary-Blue-Dark-Hover);
      --green:        #10B981;
      --red:          #EF4444;
      --amber:        #F59E0B;
    }
    .navbar-bg,
    .main-navbar           { background: var(--Primary-Blue-Dark) !important; }
    .main-sidebar          { background: var(--Primary-Blue-Dark) !important; }
    .sidebar-brand a       { color: var(--Tercer-Blue-Dark) !important; font-weight: 700; letter-spacing: 1px; }
    .sidebar-menu li a     { color: #b0bec5 !important; }
    .sidebar-menu li a:hover,
    .sidebar-menu li.active > a { color: #fff !important; background: var(--Primary-Blue-Dark-Hover) !important; }
    .sidebar-menu .menu-header { color: var(--Tercer-Blue-Dark) !important; font-size: 11px; font-weight: 700; letter-spacing: 1px; }
    .nav-link-user         { color: #fff !important; }
    .badge-orange          { background: var(--Tercer-Blue-Dark); color: #fff; }
  </style>
</head>
<body>
<div id="app">
  <div class="main-wrapper main-wrapper-1">
    <div class="navbar-bg"></div>

    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg main-navbar">
      <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
          <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
        </ul>
      </form>
      <ul class="navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            <div class="d-sm-none d-lg-inline-block" style="margin-top:15px">
              <p style="margin-bottom:0;line-height:3px;color:#fff">{{ auth()->user()->name }}</p>
              <small style="color:#b0bec5">{{ auth()->user()->email }}</small>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <a href="{{ route('logout') }}"
                 onclick="event.preventDefault();this.closest('form').submit();"
                 class="dropdown-item has-icon text-danger">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
              </a>
            </form>
          </div>
        </li>
      </ul>
    </nav>

    {{-- SIDEBAR --}}
    <div class="main-sidebar sidebar-style-2">
      <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
          <a href="{{ route('index') }}">MAC DEL NORTE</a>
        </div>
        <ul class="sidebar-menu">
          <li class="menu-header">Panel Técnico</li>
          <li class="{{ request()->routeIs('technician.dashboard') ? 'active' : '' }}">
            <a href="{{ route('technician.dashboard') }}" class="nav-link">
              <i class="fas fa-home"></i><span>Inicio</span>
            </a>
          </li>

          <li class="menu-header">Reportes</li>
          <li class="{{ request()->routeIs('technician.reports.index') ? 'active' : '' }}">
            <a href="{{ route('technician.reports.index') }}" class="nav-link">
              <i class="fas fa-clipboard-list"></i><span>Mis Reportes</span>
            </a>
          </li>
          <li class="{{ request()->routeIs('technician.reports.create') ? 'active' : '' }}">
            <a href="{{ route('technician.reports.create') }}" class="nav-link">
              <i class="fas fa-plus-circle"></i><span>Nuevo Reporte</span>
            </a>
          </li>
        </ul>
      </aside>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="main-content">
      @yield('content')
    </div>
  </div>
</div>

<script src="{{ asset('backend/assets/modules/jquery.min.js') }}"></script>
<script src="{{ asset('backend/assets/modules/popper.js') }}"></script>
<script src="{{ asset('backend/assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
<script src="{{ asset('backend/assets/modules/moment.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/stisla.js') }}"></script>
<script src="{{ asset('backend/assets/js/scripts.js') }}"></script>
<script src="{{ asset('backend/assets/js/custom.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  @if($errors->any())
    @foreach($errors->all() as $error)
      toastr.error("{{ $error }}");
    @endforeach
  @endif

  @if(session('success'))
    toastr.success("{{ session('success') }}");
  @endif
</script>
@stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <title>
    @yield('title')
  </title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link rel="icon" type="image/png" href="{{asset("frontend/images/logo/AVIAzul-Celeste.png")}}">
  {{-- <link rel="stylesheet" href="{{asset('frontend/css/all.min.css')}}"> --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- <link rel="stylesheet" href="{{asset('frontend/css/bootstrap.min.css')}}"> --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <link rel="stylesheet" href="{{asset('frontend/css/select2.min.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/slick.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/jquery.nice-number.min.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/jquery.calendar.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/add_row_custon.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/mobile_menu.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/jquery.exzoom.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/multiple-image-video.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/ranger_style.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/jquery.classycountdown.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/venobox.min.css')}}">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

  <link rel="stylesheet" href="{{asset('frontend/css/style.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/responsive.css')}}">



  <link rel="stylesheet" href="{{asset('frontend/css/style.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/responsive.css')}}">
  <style>
    /* ===== MDN DASHBOARD — DESIGN SYSTEM ===== */
    :root {
      --azul-oscuro:      #002856;
      --azul-principal:   #003E7E;
      --azul-medio:       #0057A8;
      --azul-claro:       #E6EFF8;
      --blanco:           #FFFFFF;
      --gris-fondo:       #F5F7FA;
      --gris-borde:       #DDE3EA;
      --gris-texto:       #4A5568;
      --gris-claro-texto: #718096;
      --negro-texto:      #1A202C;
      --accent-cta:       #F7941D;
      --accent-cta-hover: #E08416;
      --amarillo-destacado:#F6AD1C;
      --verde-disponible: #2F855A;
      --verde-claro:      #F0FFF4;
      --rojo-error:       #DC2626;
      --rojo-claro:       #FEE2E2;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Poppins', sans-serif; background: var(--gris-fondo); color: var(--negro-texto); }

    /* ── SIDEBAR ──────────────────────────────── */
    .mdn-dashboard-sidebar {
      width: 260px; min-height: 100vh;
      background: var(--azul-oscuro);
      display: flex; flex-direction: column;
      position: fixed; left: 0; top: 0; z-index: 300;
      border-right: 1px solid rgba(255,255,255,0.08);
    }
    .mdn-sidebar-logo {
      padding: 22px 20px 18px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      display: flex; align-items: center; gap: 12px;
      text-decoration: none;
    }
    .mdn-sidebar-logo-symbol {
      width: 40px; height: 40px; background: var(--azul-principal);
      border-radius: 8px; display: flex; align-items: center; justify-content: center;
      color: var(--blanco); font-weight: 800; font-size: 18px; flex-shrink: 0;
    }
    .mdn-sidebar-logo-text { color: var(--blanco); font-weight: 800; font-size: 15px; line-height: 1.2; }
    .mdn-sidebar-logo-sub  { color: rgba(255,255,255,0.45); font-size: 11px; font-weight: 400; }

    .mdn-sidebar-user {
      padding: 14px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      background: rgba(0,0,0,0.15);
    }
    .mdn-sidebar-user-name  { color: var(--blanco); font-size: 13px; font-weight: 700; margin-bottom: 2px; }
    .mdn-sidebar-user-email { color: rgba(255,255,255,0.45); font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .mdn-sidebar-nav { flex: 1; padding: 10px 0; overflow-y: auto; }
    .mdn-sidebar-group { padding-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,0.06); margin-bottom: 6px; }
    .mdn-sidebar-group:last-child { border-bottom: none; margin-bottom: 0; }
    .mdn-sidebar-group-label {
      font-size: 10px; font-weight: 800; text-transform: uppercase;
      letter-spacing: 1.5px; color: rgba(255,255,255,0.28);
      padding: 8px 20px 4px;
    }
    .mdn-sidebar-link {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 20px;
      color: rgba(255,255,255,0.65); text-decoration: none;
      font-size: 13px; font-weight: 600; transition: all 0.15s;
      border-left: 3px solid transparent;
      cursor: pointer; background: none;
      border-top: none; border-right: none; border-bottom: none;
      width: 100%; text-align: left; font-family: inherit;
    }
    .mdn-sidebar-link svg { width: 16px; height: 16px; flex-shrink: 0; opacity: 0.65; transition: opacity 0.15s; }
    .mdn-sidebar-link:hover {
      background: rgba(255,255,255,0.06); color: var(--blanco);
      border-left-color: var(--accent-cta);
    }
    .mdn-sidebar-link:hover svg { opacity: 1; }
    .mdn-sidebar-link.active {
      background: rgba(0,62,126,0.45); color: var(--blanco);
      border-left-color: var(--accent-cta); font-weight: 700;
    }
    .mdn-sidebar-link.active svg { opacity: 1; }
    .mdn-sidebar-link.logout { color: rgba(220,38,38,0.75); }
    .mdn-sidebar-link.logout svg { opacity: 0.75; color: rgba(220,38,38,0.75); }
    .mdn-sidebar-link.logout:hover {
      background: rgba(220,38,38,0.1); color: #FCA5A5;
      border-left-color: var(--rojo-error);
    }

    /* ── MAIN CONTENT ─────────────────────────── */
    .mdn-dashboard-main {
      margin-left: 260px; min-height: 100vh;
      background: var(--gris-fondo); padding: 32px;
    }
    .mdn-page-header { margin-bottom: 28px; }
    .mdn-page-title  { font-size: 22px; font-weight: 800; color: var(--azul-principal); margin-bottom: 4px; }
    .mdn-page-sub    { font-size: 13px; color: var(--gris-claro-texto); }

    /* ── CARDS ────────────────────────────────── */
    .card {
      background: var(--blanco); border: 1px solid var(--gris-borde);
      border-radius: 14px; overflow: hidden; margin-bottom: 20px;
    }
    .card-header {
      padding: 22px 28px 18px;
      border-bottom: 1px solid var(--gris-borde);
      margin-bottom: 0;
    }
    .card-body { padding: 24px 28px; }
    .card-title  { font-size: 16px; font-weight: 800; color: var(--azul-principal); margin-bottom: 2px; }
    .card-subtitle { font-size: 12px; color: var(--gris-claro-texto); }

    /* ── FORMS ────────────────────────────────── */
    .form-row {
      display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;
    }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label {
      font-size: 12px; font-weight: 700; color: var(--gris-texto);
      text-transform: uppercase; letter-spacing: 0.5px;
    }
    .form-input, .form-select, .form-textarea {
      padding: 10px 14px; border: 1.5px solid var(--gris-borde);
      border-radius: 8px; font-size: 14px; font-family: inherit;
      color: var(--negro-texto); background: var(--blanco);
      transition: border-color 0.15s, box-shadow 0.15s;
      width: 100%;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
      outline: none; border-color: var(--azul-principal);
      box-shadow: 0 0 0 3px rgba(0,62,126,0.1);
    }
    .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23718096' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 38px; }
    .form-textarea { resize: vertical; min-height: 90px; }
    .form-error { color: var(--rojo-error); font-size: 12px; margin-top: 2px; }
    .form-help  { color: var(--gris-claro-texto); font-size: 12px; margin-top: 2px; }

    /* ── BUTTONS ──────────────────────────────── */
    .btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 20px; border-radius: 8px;
      font-size: 13px; font-weight: 700; font-family: inherit;
      cursor: pointer; transition: all 0.2s; border: none; text-decoration: none;
      white-space: nowrap;
    }
    .btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .btn-primary { background: var(--azul-principal); color: var(--blanco); }
    .btn-primary:hover { background: var(--azul-medio); color: var(--blanco); }
    .btn-secondary { background: var(--azul-claro); color: var(--azul-principal); border: 1.5px solid var(--gris-borde); }
    .btn-secondary:hover { background: var(--azul-principal); color: var(--blanco); border-color: var(--azul-principal); }
    .btn-ghost { background: transparent; color: var(--gris-texto); border: 1.5px solid var(--gris-borde); }
    .btn-ghost:hover { background: var(--gris-fondo); color: var(--azul-principal); }
    .btn-danger { background: var(--rojo-error); color: var(--blanco); }
    .btn-danger:hover { background: #b91c1c; color: var(--blanco); }
    .btn-sm { padding: 7px 14px; font-size: 12px; }

    /* ── ALERT / FLASH ────────────────────────── */
    .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .alert-success { background: var(--verde-claro); border: 1px solid var(--verde-disponible); color: var(--verde-disponible); }
    .alert-error   { background: var(--rojo-claro); border: 1px solid var(--rojo-error); color: var(--rojo-error); }
    .alert svg { width: 16px; height: 16px; flex-shrink: 0; }

    /* ── PLACEHOLDER SECTION ──────────────────── */
    .section-placeholder {
      padding: 40px 28px; text-align: center; color: var(--gris-claro-texto);
    }
    .section-placeholder svg { margin: 0 auto 12px; display: block; color: var(--gris-borde); }
    .section-placeholder p { font-size: 14px; }

    /* ── MOBILE TOGGLE ────────────────────────── */
    .mdn-sidebar-toggle {
      display: none;
      position: fixed; top: 16px; left: 16px; z-index: 400;
      width: 40px; height: 40px;
      background: var(--azul-principal); color: var(--blanco);
      border: none; border-radius: 8px;
      align-items: center; justify-content: center; cursor: pointer;
    }
    .mdn-sidebar-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.45); z-index: 299;
    }
    .mdn-sidebar-overlay.active { display: block; }

    /* ── RESPONSIVE ───────────────────────────── */
    @media (max-width: 960px) {
      .mdn-dashboard-sidebar { transform: translateX(-100%); transition: transform 0.28s ease; }
      .mdn-dashboard-sidebar.open { transform: translateX(0); }
      .mdn-dashboard-main { margin-left: 0; padding: 20px 16px; }
      .mdn-sidebar-toggle { display: flex; }
      .form-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
      .mdn-dashboard-main { padding: 16px 12px; }
    }
  </style>
</head>

<body>

@yield('content')


  <!--============================
      SCROLL BUTTON START
    ==============================-->
  <div class="wsus__scroll_btn">
    <i class="fas fa-chevron-up"></i>
  </div>
  <!--============================
    SCROLL BUTTON  END
  ==============================-->


  <!--jquery library js-->
  {{-- <script src="{{asset('frontend/js/jquery-3.6.0.min.js')}}"></script> --}}
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!--bootstrap js-->
  {{-- <script src="{{asset('frontend/js/bootstrap.bundle.min.js')}}"></script> --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!--font-awesome js-->
  <script src="{{asset('frontend/js/Font-Awesome.js')}}"></script>
  <!--select2 js-->
  <script src="{{asset('frontend/js/select2.min.js')}}"></script>
  <!--slick slider js-->
  <script src="{{asset('frontend/js/slick.min.js')}}"></script>
  <!--simplyCountdown js-->
  <script src="{{asset('frontend/js/simplyCountdown.js')}}"></script>

  <!--product zoomer js-->

  {{-- <script src="{{asset('frontend/js/jquery.exzoom.js')}}"></script> --}}

  <!--nice-number js-->
  {{-- <script src="{{asset('frontend/js/jquery.nice-number.min.js')}}"></script> --}}

  <!--counter js-->
  {{-- <script src="{{asset('frontend/js/jquery.waypoints.min.js')}}"></script> --}}
  
  {{-- <script src="{{asset('frontend/js/jquery.countup.min.js')}}"></script> --}}
  <!--add row js-->
  <script src="{{asset('frontend/js/add_row_custon.js')}}"></script>
  <!--multiple-image-video js-->
  <script src="{{asset('frontend/js/multiple-image-video.js')}}"></script>
  <!--sticky sidebar js-->
  <script src="{{asset('frontend/js/sticky_sidebar.js')}}"></script>
  <!--price ranger js-->
  <script src="{{asset('frontend/js/ranger_jquery-ui.min.js')}}"></script>
  <script src="{{asset('frontend/js/ranger_slider.js')}}"></script>
  <!--isotope js-->
  <script src="{{asset('frontend/js/isotope.pkgd.min.js')}}"></script>
  <!--venobox js-->
  <script src="{{asset('frontend/js/venobox.min.js')}}"></script>
  <!--classycountdown js-->
  {{-- <script src="{{asset('frontend/js/jquery.classycountdown.js')}}"></script> --}}
  <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <!--Sweetalert js-->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

  <!--main/custom js-->
  <script src="{{asset('frontend/js/main.js')}}"></script>
  

  <!-- Show Dynamic Validation Erros-->
  <script>
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            toastr.error("{{$error}}")
        @endforeach
    @endif
  </script>

    <!-- Dynamic Delete alart -->
    <script>
        $(document).ready(function(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('body').on('click', '.delete-item', function(event){
                event.preventDefault();

                let deleteUrl = $(this).attr('href');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                    if (result.isConfirmed) {

                        $.ajax({
                            type: 'DELETE',
                            url: deleteUrl,

                            success: function(data){

                                if(data.status == 'success'){
                                    Swal.fire(
                                        'Deleted!',
                                        data.message,
                                        'success'
                                    )
                                    window.location.reload();
                                }else if (data.status == 'error'){
                                    Swal.fire(
                                        'Cant Delete',
                                        data.message,
                                        'error'
                                    )
                                }
                            },
                            error: function(xhr, status, error){
                                console.log(error);
                            }
                        })
                    }
                })
            })

        })
    </script>
  @stack('scripts')
</body>

</html>

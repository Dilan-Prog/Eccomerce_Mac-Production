{{--
    Sidebar de "Mi cuenta" (diseño nuevo) — compartido entre
    frontend.dashboard.profile (el panel con pestañas) y
    frontend.dashboard.order.show (detalle de un pedido), para no volver a
    tener 3 copias distintas del mismo menú con links que se desincronizan
    entre sí (justo lo que pasaba antes: el detalle de pedido seguía en el
    panel viejo con su propio sidebar aparte).

    Espera una variable $activeTab ('personal'|'fiscal'|'password'|
    'notifications'|'b2b'|'orders'|'addresses').
--}}
@php $activeTab = $activeTab ?? 'personal'; @endphp
<aside class="profile-sidebar">

    <a href="{{ route('user.profile') }}"
       class="profile-sidebar-link {{ $activeTab === 'personal' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Datos personales</span>
    </a>

    <a href="{{ route('user.profile') }}?tab=fiscal"
       class="profile-sidebar-link {{ $activeTab === 'fiscal' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <span>Datos fiscales</span>
    </a>

    <a href="{{ route('user.profile') }}?tab=password"
       class="profile-sidebar-link {{ $activeTab === 'password' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span>Contraseña</span>
    </a>

    <a href="{{ route('user.profile') }}?tab=notifications"
       class="profile-sidebar-link {{ $activeTab === 'notifications' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        <span>Notificaciones</span>
    </a>

    <a href="{{ route('user.profile') }}?tab=b2b"
       class="profile-sidebar-link {{ $activeTab === 'b2b' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L1 21h22L12 2z"/></svg>
        <span>Plan B2B</span>
    </a>

    <div class="profile-sidebar-divider"></div>

    <a href="{{ route('user.profile') }}?tab=orders"
       class="profile-sidebar-link {{ $activeTab === 'orders' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <span>Mis pedidos</span>
    </a>

    <a href="{{ route('user.profile') }}?tab=addresses"
       class="profile-sidebar-link {{ $activeTab === 'addresses' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <span>Direcciones</span>
    </a>

    <div class="profile-sidebar-divider"></div>

    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="profile-sidebar-link danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Cerrar sesión</span>
        </button>
    </form>

</aside>

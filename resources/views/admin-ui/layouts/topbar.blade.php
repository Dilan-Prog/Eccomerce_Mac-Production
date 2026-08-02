{{-- New admin-ui topbar. Keeps only the real functionality from
     resources/views/admin/layouts/navbar.blade.php (user menu + logout) —
     the fake messages/notifications dropdowns there are unused demo content. --}}
<header class="au-topbar">
    <button type="button" class="au-btn au-btn-plain au-btn-icon" data-au-sidebar-toggle aria-label="Alternar menú">
        <i class="fas fa-bars"></i>
    </button>

    <div class="au-topbar-search">
        <i class="fas fa-search" style="font-size:12px"></i>
        <input type="text" placeholder="Buscar órdenes, clientes, productos…" disabled>
        <span class="au-topbar-kbd">&#8984;K</span>
    </div>

    <div class="au-dropdown au-topbar-user">
        <button type="button" class="au-btn au-btn-plain au-flex au-gap-2" data-au-dropdown-trigger>
            <span class="au-avatar">{{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}</span>
            <span>
                <span class="au-topbar-user-name" style="display:block">{{ auth()->user()->name }}</span>
                <span class="au-topbar-user-email" style="display:block">{{ auth()->user()->email }}</span>
            </span>
            <i class="fas fa-chevron-down" style="font-size:10px"></i>
        </button>
        <div class="au-dropdown-panel">
            <a href="{{ route('admin.profile') }}" class="au-dropdown-item">
                <i class="far fa-user"></i> Perfil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="au-dropdown-item is-critical" style="width:100%">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</header>

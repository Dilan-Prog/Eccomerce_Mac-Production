{{-- ── MDN DASHBOARD SIDEBAR ───────────────────────────────── --}}
<aside class="mdn-dashboard-sidebar" id="mdn-sidebar">

    {{-- Logo --}}
    <a href="{{ route('index') }}" class="mdn-sidebar-logo">
        <div class="mdn-sidebar-logo-symbol">M</div>
        <div>
            <div class="mdn-sidebar-logo-text">Mac Del Norte</div>
            <div class="mdn-sidebar-logo-sub">Panel de cuenta</div>
        </div>
    </a>

    {{-- Usuario --}}
    <div class="mdn-sidebar-user">
        <div class="mdn-sidebar-user-name">{{ Auth::user()->name }}</div>
        <div class="mdn-sidebar-user-email">{{ Auth::user()->email }}</div>
    </div>

    {{-- Navegación --}}
    <nav class="mdn-sidebar-nav">

        {{-- Grupo: Mi cuenta --}}
        <div class="mdn-sidebar-group">
            <div class="mdn-sidebar-group-label">Mi cuenta</div>

            <a href="{{ route('user.profile') }}"
               class="mdn-sidebar-link {{ request()->routeIs('user.profile') && !request()->get('tab') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Datos personales
            </a>

            <a href="{{ route('user.profile') }}?tab=fiscal"
               class="mdn-sidebar-link {{ request()->get('tab') === 'fiscal' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Datos fiscales
            </a>

            <a href="{{ route('user.profile') }}?tab=password"
               class="mdn-sidebar-link {{ request()->get('tab') === 'password' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                Contraseña
            </a>

            {{-- TODO: crear lógica de notificaciones --}}
            <a href="{{ route('user.profile') }}?tab=notifications"
               class="mdn-sidebar-link {{ request()->get('tab') === 'notifications' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
                Notificaciones
            </a>

            {{-- TODO: crear lógica de Plan B2B --}}
            <a href="{{ route('user.profile') }}?tab=b2b"
               class="mdn-sidebar-link {{ request()->get('tab') === 'b2b' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                </svg>
                Plan B2B
            </a>
        </div>

        {{-- Grupo: Actividad --}}
        <div class="mdn-sidebar-group">
            <div class="mdn-sidebar-group-label">Actividad</div>

            <a href="{{ route('user.profile', ['tab' => 'orders']) }}"
               class="mdn-sidebar-link {{ request()->routeIs('user.orders*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
                Mis pedidos
            </a>

            <a href="{{ route('user.profile', ['tab' => 'addresses']) }}"
               class="mdn-sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Direcciones
            </a>
        </div>

        {{-- Grupo: Navegación --}}
        <div class="mdn-sidebar-group">
            <a href="{{ route('index') }}" class="mdn-sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Ir al inicio
            </a>

            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="mdn-sidebar-link logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Cerrar sesión
                </button>
            </form>
        </div>

    </nav>
</aside>

{{-- Overlay para móvil --}}
<div class="mdn-sidebar-overlay" id="mdn-sidebar-overlay"></div>

{{-- Toggle móvil --}}
<button class="mdn-sidebar-toggle" id="mdn-sidebar-toggle" aria-label="Abrir menú">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
</button>

<script>
(function () {
    var toggle  = document.getElementById('mdn-sidebar-toggle');
    var sidebar = document.getElementById('mdn-sidebar');
    var overlay = document.getElementById('mdn-sidebar-overlay');
    if (!toggle || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    overlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });
})();
</script>

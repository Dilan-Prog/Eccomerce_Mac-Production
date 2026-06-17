<section class="hero-home">

    <div class="container hero-container">

        {{-- Capas de foto dentro del container (respetan los márgenes) --}}
        <div class="hero-bg-layer" id="hero-bg-a"></div>
        <div class="hero-bg-layer" id="hero-bg-b" style="opacity:0"></div>

        {{-- Gradiente encima de la foto --}}
        <div class="hero-grad-overlay"></div>

        {{-- Todo el contenido encima del gradiente --}}
        <div class="hero-content-wrap">

            <div class="hero-badge-distribuidor">
                <svg viewBox="0 0 20 20" fill="currentColor" width="15" height="15" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Distribuidor Autorizado Honeywell · 8 años en el mercado
            </div>

            <h1 class="hero-titulo">
                 Instrumentación, control y combustión industrial
                <span class="highlight">para procesos </span>
                donde fallar no es opción
            </h1>

            <p class="hero-subtitulo">
                Suministro especializado de instrumentación, control y combustión industrial Honeywell, Maxon y McDonnell & Miller, con inventario disponible y soporte técnico para seleccionar correctamente desde el primer contacto.
            </p>

            <div class="hero-cta-grupo">
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    Ver catálogo de productos &nbsp;→
                </a>
                <a href="https://wa.link/f28njw" class="btn btn-secondary" target="_blank" rel="noopener noreferrer">
                    Cotización con ingeniero
                </a>
            </div>

            <div class="hero-stats-bar">

                <div class="hero-stat-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="28" height="28" aria-hidden="true">
                        <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                        <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                        <line x1="12" y1="12" x2="12" y2="16"/>
                        <line x1="10" y1="14" x2="14" y2="14"/>
                    </svg>
                    <div class="hero-stat-numero">+2000</div>
                    <div class="hero-stat-label">Productos en stock</div>
                </div>

                <div class="hero-stat-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="28" height="28" aria-hidden="true">
                        <rect x="1" y="3" width="15" height="13" rx="1"/>
                        <path d="M16 8h4l3 5v3h-7V8z"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <div class="hero-stat-numero">48h</div>
                    <div class="hero-stat-label">Entrega promedio</div>
                </div>

                <div class="hero-stat-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="28" height="28" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>
                    </svg>
                    <div class="hero-stat-numero">7+</div>
                    <div class="hero-stat-label">Años en el mercado</div>
                </div>

                <div class="hero-stat-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="28" height="28" aria-hidden="true">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                        <path d="M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                    <div class="hero-stat-numero">98%</div>
                    <div class="hero-stat-label">Clientes satisfechos</div>
                </div>

            </div>

        </div>{{-- /hero-content-wrap --}}

    </div>{{-- /hero-container --}}

</section>

@push('scripts')
<script>
(function () {
    var banners = @json($sliders->map(fn($s) => asset($s->banner))->values());
    if (!banners.length) return;

    var bgA = document.getElementById('hero-bg-a');
    var bgB = document.getElementById('hero-bg-b');

    // Primera imagen: fetchPriority high (es el LCP de la página)
    var firstImg = new Image();
    firstImg.fetchPriority = 'high';
    firstImg.src = banners[0];
    bgA.style.backgroundImage = "url('" + banners[0] + "')";

    if (banners.length === 1) return;

    var current = 0;
    var front   = bgA;
    var back    = bgB;

    // Carga lazy: precarga la siguiente imagen justo antes de mostrarla
    function preloadLazy(index) {
        var img = new Image();
        img.loading      = 'lazy';
        img.fetchPriority = 'low';
        img.src = banners[index];
    }

    // Precarga la segunda imagen después de que la primera ya cargó
    setTimeout(function () { preloadLazy(1); }, 1500);

    setInterval(function () {
        current = (current + 1) % banners.length;
        back.style.backgroundImage = "url('" + banners[current] + "')";
        back.style.opacity  = '1';
        front.style.opacity = '0';
        var tmp = front; front = back; back = tmp;

        // Precarga la siguiente en la rotación (lazy, baja prioridad)
        var nextIndex = (current + 1) % banners.length;
        preloadLazy(nextIndex);
    }, 4500);
}());
</script>
@endpush

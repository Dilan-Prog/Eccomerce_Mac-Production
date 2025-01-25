

<section id="wsus__banner">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="wsus__banner_content">
                    <div class="row banner_slider">
                        @foreach ($sliders as $slider)
                        <div class="col-xl-12">
                            <a href="{{ $slider->btn_url }}">
                                @if ($loop->first)
                                <!-- Primera imagen cargada inmediatamente -->
                                <img src="{{ asset($slider->banner) }}" alt="slider image" class="wsus__single_slider">
                                @else
                                <!-- Imágenes diferidas con data-src -->
                                <img data-src="{{ asset($slider->banner) }}" alt="slider image" class="wsus__single_slider lazy-image">
                                @endif
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
    const lazyImages = document.querySelectorAll(".lazy-image");

    if ("IntersectionObserver" in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src; // Mueve la URL de data-src a src
                    img.classList.remove("lazy-image"); // Opcional: elimina la clase para evitar reobservación
                    observer.unobserve(img); // Deja de observar la imagen una vez cargada
                }
            });
        });

        lazyImages.forEach(image => {
            imageObserver.observe(image);
        });
    } else {
        // Fallback para navegadores antiguos: Cargar todas las imágenes de inmediato
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }
});
</script>
    
@endpush




< id="wsus__banner">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="wsus__banner_content">
                    <div class="row banner_slider">
                        @foreach ($sliders as $slider)
                        <div class="col-12">
                            <a href="{{ $slider->btn_url }}">
                                @if ($loop->first)
                                <!-- Primera imagen cargada inmediatamente -->
                                    <img
                                        class="wsus__single_slider"
                                        alt="slider image"
                                        decoding="sync"
                                        fetchpriority="high"
                                        loading="eager"
                                        src="{{ asset($slider->banner) }}"
                                        srcset="
                                            {{ asset($slider->banner_phone) }} 370w,
                                            {{ asset($slider->banner_tablet) }} 720w,
                                            {{ asset($slider->banner_laptop) }} 1140w,
                                            {{ asset($slider->banner) }} 1250w
                                        "
                                        sizes="
                                            (max-width: 576px) 370px,
                                            (max-width: 768px) 720px,
                                            (max-width: 992px) 1140px,
                                            100vw
                                        "
                                        >
                                @else
                                    <!-- Imágenes diferidas (puedes aplicar lazy-loading si lo deseas) -->
                                    <img
                                        class="wsus__single_slider lazy-image"
                                        alt="slider image"
                                        decoding="async"
                                        fetchpriority="low"
                                        loading="lazy"
                                        data-src="{{ asset($slider->banner) }}"
                                        data-srcset="
                                            {{ asset($slider->banner_phone) }} 370w,
                                            {{ asset($slider->banner_tablet) }} 720w,
                                            {{ asset($slider->banner_laptop) }} 1140w,
                                            {{ asset($slider->banner) }} 1250w
                                        "
                                        sizes="
                                            (max-width: 576px) 370px,
                                            (max-width: 768px) 720px,
                                            (max-width: 992px) 1140px,
                                            100vw
                                        "
                                        >
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
                    img.src = img.dataset.src;
                    img.srcset = img.dataset.srcset;
                    img.classList.remove("lazy-image");
                    observer.unobserve(img);
                }
            });
        });

        lazyImages.forEach(image => {
            imageObserver.observe(image);
        });
    } else {
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.srcset = img.dataset.srcset;
        });
    }
});
</script>
@endpush


{{--
    DOCUMENTACIÓN - Integración de Opiniones de Google

    Resumen:
    - Este partial muestra un carrusel (Swiper.js) con las reseñas que se pasan
        desde el controlador en la variable `$reviews`.
    - El bloque de diseño (HTML/CSS/JS) queda activo más abajo; la documentación
        está comentada en Blade para no renderizar al cliente.

    Estructura esperada de `$reviews` (array de reseñas normalizadas):
    - author_name: string
    - profile_photo_url: string (URL de avatar; opcional)
    - rating: int (0-5)
    - text: string (contenido de la reseña)
    - time: int o string (timestamp) - opcional
    - relative_time_description: string (ej. "hace 2 semanas") - opcional
    - url: string (enlace a la reseña en Google)

    Ejemplo (controlador) mínimo:
    ```php
    $reviews = [
            [
                    'author_name' => 'María Pérez',
                    'profile_photo_url' => 'https://lh3.googleusercontent.com/..',
                    'rating' => 5,
                    'text' => 'Excelente servicio y productos de calidad.',
                    'relative_time_description' => 'hace 2 semanas',
                    'url' => 'https://maps.google.com/?cid=...'
            ],
    ];

    return view('frontend.home.index', compact('reviews'));
    ```

    Recomendaciones para obtener y normalizar reseñas desde Google (servidor):
    - Use la Places API / Place Details para obtener `reviews` (requiere place_id
        y API key). También puede usar la Web API de Google Maps si su flujo lo
        permite.
    - Siempre haga la petición desde el servidor (no exponer API key en frontend).
    - Cachee la respuesta (ej. Redis, cache de Laravel) por varias horas para evitar
        límites y mejorar rendimiento.
    - Normalice los campos a la estructura esperada antes de pasarlos a la vista.

    Normalización ejemplo (pseudo):
    ```php
    $google = /* respuesta de Places API */;
    $reviews = collect($google['reviews'] ?? [])->map(function($r){
            return [
                    'author_name' => $r['author_name'] ?? 'Usuario',
                    'profile_photo_url' => $r['profile_photo_url'] ?? null,
                    'rating' => isset($r['rating']) ? (int)$r['rating'] : 0,
                    'text' => $r['text'] ?? '',
                    'time' => $r['time'] ?? null,
                    'relative_time_description' => $r['relative_time_description'] ?? null,
                    'url' => $r['author_url'] ?? null,
            ];
    })->toArray();
    ```

    Notas de cumplimiento y seguridad:
    - Revise los Términos de Servicio de Google antes de mostrar reseñas.
    - No exponga la `API key` en el cliente; use variables de entorno en servidor.
    - Respete la atribución de Google si es requerida.

    Personalización / mejoras posibles:
    - Lazy-loading de imágenes con `loading="lazy"` (compatible con HTML5).
    - Añadir `aria-live` y controles accesibles para usuarios de teclado.
    - Integrar un endpoint AJAX que devuelva las reseñas ya cacheadas y
        actualice el carrusel sin recargar la página.

    Ubicación de este partial:
    - resources/views/frontend/home/sections/opinion-google.blade.php

--}}

<!-- Google Reviews Carousel (uses Swiper.js via CDN) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
<div class="container">
    <div class="google-reviews-section">
        <div class="section-header">
            <h3>Nuestros Clientes</h3>
        </div>
    
        <div class="swiper opinion-swiper">
            <div class="swiper-wrapper">
                <!-- Static slide 1 -->
                <div class="swiper-slide review-slide">
                    <article class="review-card">
                        <div class="review-top">
                            <i class="fas fa-user"></i>
                            <div class="review-meta">
                                <div class="review-author">Fabian Medina Lopez</div>
                                <div class="review-rating" aria-hidden="true">
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <p>Hace 3 meses</p>
                                </div>
                            </div>
                        </div>
    
                        <div class="review-body">
                            <p class="review-text">ELa verdad muy pero muy bueno en su atención con y excelente en todo la extensión de la palabra, si me dicen si lo recomendaría así es lo recomiendo ampliamente y seguro en su entrega</p>
                        </div>
    
                        <div class="review-footer">
                            <time class="review-time">hace 2 semanas</time>
                            <a href="#" target="_blank" rel="noopener" class="review-link">Ver en Google</a>
                        </div>
                    </article>
                </div>
    
                <!-- Static slide 2 -->
                <div class="swiper-slide review-slide">
                    <article class="review-card">
                        <div class="review-top">
                            <img class="review-avatar" src="https://via.placeholder.com/48/888" alt="Carlos Gómez">
                            <div class="review-meta">
                                <div class="review-author">Carlos Gómez</div>
                                <div class="review-rating" aria-hidden="true">
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                </div>
                            </div>
                        </div>
    
                        <div class="review-body">
                            <p class="review-text">Buena atención al cliente, entrega rápida pero empaques podrían mejorar.</p>
                        </div>
    
                        <div class="review-footer">
                            <time class="review-time">hace 1 mes</time>
                            <a href="#" target="_blank" rel="noopener" class="review-link">Ver en Google</a>
                        </div>
                    </article>
                </div>
    
                <!-- Static slide 3 -->
                <div class="swiper-slide review-slide">
                    <article class="review-card">
                        <div class="review-top">
                            <img class="review-avatar" src="https://via.placeholder.com/48/555" alt="Lucía Martínez">
                            <div class="review-meta">
                                <div class="review-author">Lucía Martínez</div>
                                <div class="review-rating" aria-hidden="true">
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                    <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.5 9.748l-5.75 5.6L19.335 24 12 19.771 4.665 24l1.585-8.652L.5 9.748l7.832-1.73L12 .587z"/></svg>
                                </div>
                            </div>
                        </div>
    
                        <div class="review-body">
                            <p class="review-text">Productos tal cual como en la foto, excelente relación calidad-precio.</p>
                        </div>
    
                        <div class="review-footer">
                            <time class="review-time">hace 3 días</time>
                            <a href="#" target="_blank" rel="noopener" class="review-link">Ver en Google</a>
                        </div>
                    </article>
                </div>
            </div>
    
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev" aria-label="Anterior"></div>
            <div class="swiper-button-next" aria-label="Siguiente"></div>
        </div>
    </div>
</div>

<style>
    .google-reviews-section { padding: 1rem 0; }
    .section-header h3 { margin: 0 0 0.75rem 0; font-size: 1.25rem; }
    .review-card { background:#fff; border-radius:8px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,.06); height:100%; display:flex; flex-direction:column; }
    .review-top { display:flex; gap:12px; align-items:center; }
    .review-avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; }
    .review-meta { display:flex; flex-direction:column; }
    .review-author { font-weight:600; }
    .review-rating { display:flex; gap:4px; color:#f5b301; }
    .review-rating .star { color:#ddd; }
    .review-rating .star.filled { color:#f5b301; }
    .review-body { margin-top:12px; flex:1 1 auto; }
    .review-text { margin:0; color:#333; font-size:0.95rem; }
    .review-footer { display:flex; justify-content:space-between; align-items:center; margin-top:12px; font-size:0.85rem; color:#666; }
    .review-link { color:#1a73e8; text-decoration:none; }
    .opinion-swiper .swiper-slide { height: auto; }
    @media (min-width:768px){ .opinion-swiper .swiper-slide { height:100%; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swiper === 'undefined') return;

        new Swiper('.opinion-swiper', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            slidesPerView: 1,
            spaceBetween: 20,
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            breakpoints: {
                640: { slidesPerView: 1 },
                768: { slidesPerView: 2 },
                1024: { slidesPerView: 3 }
            }
        });
    });
</script>

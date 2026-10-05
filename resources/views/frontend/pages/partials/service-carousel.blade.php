{{--
    Carrusel de imágenes para las páginas de Servicios.

    Reutiliza la misma mecánica del carrusel de productos del inicio
    (resources/css/components/product-carousel.css): scroll-snap horizontal +
    flechas con scrollBy, sin jQuery ni Slick. Lo único que cambia es que aquí
    se ve UNA imagen por vista en vez de cuatro, y que avanza solo.

    Variables:
      imagenes  (array)  items ['image' => ruta de asset(), 'alt' => texto]
      id        (string) identificador único — hay varios carruseles por página
      ratio     (string, opcional) aspect-ratio del recuadro. '4/3' por defecto
                en el hero; las tarjetas de la página de categoría usan '1/1'.
      borde     (string, opcional) borde del recuadro

    Con menos de 2 imágenes no se dibuja carrusel: lo resuelve quien incluye.
--}}
@php
    $ratio = $ratio ?? '4/3';
    $borde = $borde ?? '1px solid #DDE3EA';
@endphp

<div class="cps-carousel cps-carousel--svc" id="{{ $id }}" style="--svc-ratio:{{ $ratio }};--svc-borde:{{ $borde }}">
    <button type="button" class="cps-arrow cps-arrow--prev" aria-label="Imagen anterior"><i class="fas fa-chevron-left"></i></button>
    <div class="cps-track" role="group" aria-label="Galería del servicio">
        @foreach ($imagenes as $i => $foto)
            <div class="cps-slide">
                <img src="{{ asset($foto['image']) }}" alt="{{ $foto['alt'] }}"
                     loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
            </div>
        @endforeach
    </div>
    <button type="button" class="cps-arrow cps-arrow--next" aria-label="Imagen siguiente"><i class="fas fa-chevron-right"></i></button>
    <div class="cps-dots" aria-hidden="true">
        @foreach ($imagenes as $i => $foto)
            <span class="cps-dot {{ $i === 0 ? 'is-active' : '' }}"></span>
        @endforeach
    </div>
</div>

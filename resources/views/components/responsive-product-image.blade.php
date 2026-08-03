@props([
    'product',
    'alt' => null,
    'variant' => 'card', // 'card' = single small <img> (thumb_image_carrusel) | 'hero' = full <picture> with breakpoint srcset
    'loading' => 'lazy',
    'imgId' => null,
    'pictureId' => null,
])

@php
    $altText = $alt ?? $product->name;
    $fallback = $product->thumb_image;
@endphp

@if ($variant === 'hero')
    <picture @if($pictureId) id="{{ $pictureId }}" @endif>
        @if (!empty($product->thumb_image_phone))
            <source media="(max-width: 767.98px)" srcset="{{ asset($product->thumb_image_phone) }}">
        @endif
        @if (!empty($product->thumb_image_tablet))
            <source media="(min-width: 768px) and (max-width: 991.98px)" srcset="{{ asset($product->thumb_image_tablet) }}">
        @endif
        @if (!empty($product->thumb_image_laptop))
            <source media="(min-width: 992px) and (max-width: 1199.98px)" srcset="{{ asset($product->thumb_image_laptop) }}">
        @endif
        <img
            @if($imgId) id="{{ $imgId }}" @endif
            src="{{ asset($fallback) }}"
            alt="{{ $altText }}"
            loading="{{ $loading }}"
            {{ $attributes }}
        >
    </picture>
@else
    <img
        @if($imgId) id="{{ $imgId }}" @endif
        src="{{ asset($product->thumb_image_carrusel ?: $fallback) }}"
        alt="{{ $altText }}"
        loading="{{ $loading }}"
        {{ $attributes }}
    >
@endif

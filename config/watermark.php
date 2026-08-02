<?php

return [
    // Activo: se decidió aplicar la marca de agua repetida en diagonal.
    'enabled' => env('WATERMARK_ENABLED', true),

    // Ruta relativa a public_path() del logo usado como marca de agua.
    'image' => env('WATERMARK_IMAGE', 'uploads/logo/webp-horizontal.webp'),

    // Opacidad de cada repetición del logo (0-100).
    'opacity' => (int) env('WATERMARK_OPACITY', 20),

    // Ángulo de rotación del logo (grados). Negativo = diagonal hacia arriba-derecha.
    'rotation' => (float) env('WATERMARK_ROTATION', -30),

    // Ancho de cada repetición como porcentaje del lado más corto de la imagen base.
    'tile_scale_percent' => (int) env('WATERMARK_TILE_SCALE_PERCENT', 22),

    // Separación entre repeticiones, como múltiplo del ancho de la repetición
    // (rotada). 1.0 = pegadas, valores mayores = más espaciadas.
    'spacing_factor' => (float) env('WATERMARK_SPACING_FACTOR', 1.4),
];

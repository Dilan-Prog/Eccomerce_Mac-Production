<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duplicate Image Detection
    |--------------------------------------------------------------------------
    |
    | hamming_threshold: max Hamming distance (out of 64 bits) between two
    | dHashes for them to be considered the same photo. Needs calibration
    | against the real catalog after the first few scans — start conservative
    | and widen it if legitimately-different photos never group, or narrow it
    | if visually-different products get grouped together.
    */
    'hamming_threshold' => (int) env('DUPLICATE_IMAGE_HAMMING_THRESHOLD', 6),

    'hash_algo_version' => 1,

    /*
    | Every in-scope column that stores a single image reference on an
    | Eloquent model. All 8 rows share the same shape (model + nullable text
    | column + value-format convention), so a single generic source reader
    | handles all of them — see App\Services\DuplicateImages\ImageReferenceRegistry.
    */
    'sources' => [
        ['model' => \App\Models\Product::class, 'column' => 'thumb_image', 'format' => 'full_url', 'label' => 'Producto (imagen principal)'],
        ['model' => \App\Models\Brand::class, 'column' => 'logo', 'format' => 'full_url', 'label' => 'Marca (logo)'],
        ['model' => \App\Models\Slider::class, 'column' => 'banner', 'format' => 'full_url', 'label' => 'Slider (banner escritorio)'],
        ['model' => \App\Models\Slider::class, 'column' => 'banner_laptop', 'format' => 'full_url', 'label' => 'Slider (banner laptop)'],
        ['model' => \App\Models\Slider::class, 'column' => 'banner_tablet', 'format' => 'full_url', 'label' => 'Slider (banner tablet)'],
        ['model' => \App\Models\Slider::class, 'column' => 'banner_phone', 'format' => 'full_url', 'label' => 'Slider (banner phone)'],
        ['model' => \App\Models\ProductImageGallery::class, 'column' => 'image', 'format' => 'bare_relative', 'label' => 'Galería de producto'],
        ['model' => \App\Models\ProductReviewGallery::class, 'column' => 'image', 'format' => 'bare_relative', 'label' => 'Galería de reseñas'],
    ],

    'orphan_folders' => ['about', 'logo', 'slider', 'product'],

    'image_extensions' => ['webp', 'png', 'jpg', 'jpeg', 'gif'],
];

<?php

namespace App\Support\DuplicateImages;

/**
 * The two conventions this app uses for storing an image reference in a DB column.
 *
 * FullUrl mirrors ImageUploadTrait::uploadImage()/updateImage(), which store
 * asset($path.'/'.$imageName) — an absolute URL built from config('app.url').
 *
 * BareRelative mirrors ImageUploadTrait::uploadMultiImage()/uploadMultiImageReview(),
 * which store the bare "path/filename" with no host prefix.
 */
enum ValueFormat: string
{
    case FullUrl = 'full_url';
    case BareRelative = 'bare_relative';
}

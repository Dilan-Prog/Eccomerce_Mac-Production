<?php

namespace App\Support\DuplicateImages;

use App\Support\UploadPath;

/**
 * Converts between a DB-stored image value and the absolute physical filesystem
 * path, in both directions, for the two conventions this app uses (see
 * ValueFormat). Generalizes the string handling already used by
 * ImageUploadTrait::deleteImage() (strip app.url prefix, ltrim('/'), then
 * UploadPath::full()) and by ImageUploadTrait::uploadImage()/uploadMultiImage()
 * (asset($relative) vs bare $relative) so that both directions stay exact
 * inverses of each other.
 */
class PathNormalizer
{
    /** Raw DB value -> absolute physical filesystem path. */
    public static function toPhysical(string $rawValue, ValueFormat $format): string
    {
        $value = $rawValue;

        if ($format === ValueFormat::FullUrl) {
            // Same approach as ImageUploadTrait::deleteImage(): strip the app.url
            // prefix off the stored URL so what remains is relative to the uploads root.
            $appUrl = config('app.url');
            $value = str_replace($appUrl, '', $value);
        }

        // BareRelative values have no host prefix to strip; both formats end up
        // here as a path relative to the uploads root, possibly with a leading slash.
        $value = ltrim($value, '/');

        $physical = UploadPath::full($value);

        // Normalize backslashes to forward slashes (Windows dev environment).
        return str_replace('\\', '/', $physical);
    }

    /** Absolute physical filesystem path -> the value that SHOULD be stored in a column of the given format. */
    public static function toStoredValue(string $physicalPath, ValueFormat $format): string
    {
        $normalizedPhysical = str_replace('\\', '/', $physicalPath);
        $normalizedBase = rtrim(str_replace('\\', '/', UploadPath::base()), '/');

        // Strip the uploads-root prefix off the absolute path to recover the path
        // relative to the uploads root (e.g. "uploads/product/x/foo.webp", no
        // leading slash). This is the exact inverse of the ltrim('/') + UploadPath::full()
        // step in toPhysical(), so round-tripping through either format returns
        // to the same physical path.
        $relative = $normalizedPhysical;
        if (str_starts_with($relative, $normalizedBase)) {
            $relative = substr($relative, strlen($normalizedBase));
        }
        $relative = ltrim($relative, '/');

        if ($format === ValueFormat::FullUrl) {
            // Matches ImageUploadTrait::uploadImage()/updateImage()'s convention.
            return asset($relative);
        }

        // Matches ImageUploadTrait::uploadMultiImage()/uploadMultiImageReview()'s
        // convention: bare relative path, no asset() wrap.
        return $relative;
    }
}

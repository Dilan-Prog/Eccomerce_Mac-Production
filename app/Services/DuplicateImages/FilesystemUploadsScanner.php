<?php

namespace App\Services\DuplicateImages;

use App\Support\UploadPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Recursively lists every image file already living under public/uploads/**
 * (about, logo, slider, product/{brand}/...). Extracted from
 * App\Http\Controllers\Backend\MediaLibraryController::allImageFiles() so
 * the duplicate-image scan and the media library browser share one
 * implementation.
 */
class FilesystemUploadsScanner
{
    /**
     * @return Collection<int, array{relative_path:string, physical_path:string, name:string, folder:string, size:int, modified:int}>
     */
    public function scan(): Collection
    {
        $base = UploadPath::full('uploads');
        if (!File::exists($base)) {
            return collect();
        }

        $extensions = config('duplicate_images.image_extensions', ['webp', 'png', 'jpg', 'jpeg', 'gif']);

        return collect(File::allFiles($base))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), $extensions, true))
            ->map(function ($f) use ($base) {
                $relative = 'uploads/' . ltrim(str_replace('\\', '/', str_replace($base, '', $f->getPathname())), '/');
                $folder = explode('/', trim(str_replace('uploads/', '', $relative), '/'))[0] ?? '';
                $physicalPath = str_replace('\\', '/', $f->getPathname());

                return [
                    'relative_path' => $relative,
                    'physical_path' => $physicalPath,
                    'name' => $f->getFilename(),
                    'folder' => $folder,
                    'size' => $f->getSize(),
                    'modified' => $f->getMTime(),
                ];
            })
            ->values();
    }

    /**
     * Finds one scanned file whose md5(physical_path) equals the given hash —
     * used by the "search library" keeper picker.
     *
     * @return array{relative_path:string, physical_path:string, name:string, folder:string, size:int, modified:int}|null
     */
    public function findByPathHash(string $pathHash): ?array
    {
        return $this->scan()->first(fn ($f) => md5($f['physical_path']) === $pathHash);
    }

    /**
     * Case-insensitive filename substring search, capped at $limit results —
     * used by the library-search endpoint.
     *
     * @return Collection<int, array{relative_path:string, physical_path:string, name:string, folder:string, size:int, modified:int}>
     */
    public function searchByName(string $query, int $limit = 50): Collection
    {
        $needle = strtolower($query);

        return $this->scan()
            ->filter(fn ($f) => str_contains(strtolower($f['name']), $needle))
            ->take($limit)
            ->values();
    }
}

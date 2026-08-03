<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImageGallery;
use App\Services\DuplicateImages\FilesystemUploadsScanner;
use App\Support\UploadPath;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use ZipStream\ZipStream;

/**
 * Browses/manages every image already living under public/uploads/** (about,
 * logo, slider, product/{brand}/...) — a Shopify "Content > Files" style
 * library. Filesystem-backed (no Eloquent model), so it can't reuse
 * AdminTableQuery directly, but mirrors its exact JSON contract so the shared
 * admin-table.js frontend renders it with zero changes.
 */
class MediaLibraryController extends Controller
{
    use ImageUploadTrait;

    public function __construct()
    {
        $this->middleware('can-access-module:site');
    }

    private const IMAGE_EXTENSIONS = ['webp', 'png', 'jpg', 'jpeg', 'gif'];
    private const FOLDERS = ['about', 'logo', 'slider', 'product'];

    public function index()
    {
        return view('admin-ui.media-library.index');
    }

    /** JSON data source for the custom admin table, filesystem-backed equivalent of AdminTableQuery::paginate(). */
    public function data(Request $request)
    {
        $files = $this->allImageFiles();

        $folderCounts = $files->countBy(fn ($f) => $f['folder']);
        $filters = collect([['key' => 'todas', 'label' => 'Todas', 'count' => $files->count()]])
            ->concat(collect(self::FOLDERS)->map(fn ($key) => [
                'key' => $key,
                'label' => ucfirst($key),
                'count' => $folderCounts[$key] ?? 0,
            ]));

        $activeFilter = $request->input('filter', 'todas');
        $rows = $activeFilter && $activeFilter !== 'todas'
            ? $files->filter(fn ($f) => $f['folder'] === $activeFilter)
            : $files;

        if ($search = $request->input('search')) {
            $needle = strtolower($search);
            $rows = $rows->filter(fn ($f) => str_contains(strtolower($f['name']), $needle));
        }

        $sort = in_array($request->input('sort'), ['name', 'modified'], true) ? $request->input('sort') : 'modified';
        $dir = $request->input('dir', 'desc');
        $rows = $rows->sortBy($sort, SORT_REGULAR, $dir === 'desc')->values();

        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(200, (int) $request->input('per_page', 25)));
        $total = $rows->count();
        $paged = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        // SKU lookups, done once per page (not per row) to avoid N+1 queries:
        // a file can be linked to a product either via a ProductImageGallery
        // row (by relative path) or as a Product's own thumb_image (by full
        // asset URL, since that column stores a full URL, not a bare path).
        $relativePaths = $paged->pluck('path');
        $fullUrls = $paged->map(fn ($f) => asset($f['path']));

        $galleryProductIdByPath = ProductImageGallery::whereIn('image', $relativePaths)->pluck('product_id', 'image');
        $thumbProductIdByUrl = Product::whereIn('thumb_image', $fullUrls)->pluck('id', 'thumb_image');

        $productIds = $galleryProductIdByPath->values()->merge($thumbProductIdByUrl->values())->unique();
        $skuById = Product::whereIn('id', $productIds)->pluck('sku', 'id');

        return response()->json([
            'rows' => $paged->map(function ($f) use ($galleryProductIdByPath, $thumbProductIdByUrl, $skuById) {
                $rowId = $this->encodePath($f['path']);

                $productId = $galleryProductIdByPath[$f['path']] ?? $thumbProductIdByUrl[asset($f['path'])] ?? null;
                $sku = $productId ? ($skuById[$productId] ?? null) : null;

                return [
                    'row_id' => $rowId,
                    'cells' => [
                        'thumbnail' => ['url' => asset($f['path'])],
                        'name' => $f['name'],
                        'folder' => ['label' => ucfirst($f['folder']), 'tone' => 'info'],
                        'size' => $sku ? ['value' => $this->formatBytes($f['size']), 'subtext' => "SKU: {$sku}"] : $this->formatBytes($f['size']),
                        'modified' => date('c', $f['modified']),
                        'actions' => [
                            ['label' => 'Ver', 'url' => asset($f['path']), 'target' => '_blank'],
                            ['label' => 'Copiar URL', 'copy' => true, 'value' => asset($f['path'])],
                            ['label' => 'Marca de agua', 'url' => route('admin.media-library.watermark', ['path' => $rowId]), 'method' => 'POST'],
                            ['label' => 'Descargar', 'url' => route('admin.media-library.download', ['path' => $rowId])],
                            [
                                'label' => 'Borrar',
                                'url' => route('admin.media-library.destroy', ['path' => $rowId]),
                                'method' => 'DELETE',
                                'tone' => 'critical',
                                'confirm' => true,
                            ],
                        ],
                    ],
                ];
            })->all(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'sort' => $sort,
            'dir' => $dir,
            'active_filter' => $activeFilter,
            'filters' => $filters->values()->all(),
            'columns' => [
                ['key' => 'thumbnail', 'label' => '', 'type' => 'image', 'sortable' => false],
                ['key' => 'name', 'label' => 'Archivo', 'type' => 'mono', 'sortable' => true],
                ['key' => 'folder', 'label' => 'Carpeta', 'type' => 'badge', 'sortable' => false],
                ['key' => 'size', 'label' => 'Tamaño', 'type' => 'text', 'sortable' => false],
                ['key' => 'modified', 'label' => 'Modificado', 'type' => 'date', 'sortable' => true],
                ['key' => 'actions', 'label' => 'Acciones', 'type' => 'actions', 'sortable' => false],
            ],
        ]);
    }

    /** Uploads new image(s) into a chosen top-level folder. No watermark (site/logo assets, not product photos). */
    public function store(Request $request)
    {
        $request->validate(['image.*' => ['required', 'image', 'max:5120']]);

        $folder = preg_replace('/[^a-z0-9\-_]/i', '', (string) $request->input('folder')) ?: 'media';
        $paths = $this->uploadMultiImage($request, 'image', 'uploads/' . $folder) ?? [];

        return response()->json([
            'status' => 'success',
            'message' => count($paths) . ' imagen(es) subida(s).',
            'paths' => $paths,
        ]);
    }

    /** Deletes one file, after confirming the resolved path stays inside uploads/ (path-traversal guard). */
    public function destroy(string $path)
    {
        $relative = $this->decodePath($path);
        $full = realpath(UploadPath::full($relative));
        $uploadsRoot = realpath(UploadPath::full('uploads'));

        if (!$full || !$uploadsRoot || !str_starts_with($full, $uploadsRoot)) {
            return response()->json(['status' => 'error', 'message' => 'Ruta inválida.'], 422);
        }

        File::delete($full);

        return response()->json(['status' => 'success', 'message' => 'Imagen eliminada.']);
    }

    /** Applies the configured watermark to a single file, in place, after confirming the resolved path stays inside uploads/. */
    public function watermark(string $path)
    {
        $relative = $this->decodePath($path);
        $full = realpath(UploadPath::full($relative));
        $uploadsRoot = realpath(UploadPath::full('uploads'));

        if (!$full || !$uploadsRoot || !str_starts_with($full, $uploadsRoot)) {
            return response()->json(['status' => 'error', 'message' => 'Ruta inválida.'], 422);
        }

        if (!$this->watermarkFile($full)) {
            return response()->json(['status' => 'error', 'message' => 'No se pudo aplicar la marca de agua.'], 422);
        }

        return response()->json(['status' => 'success', 'message' => 'Marca de agua aplicada.']);
    }

    /** Downloads a single file, after confirming the resolved path stays inside uploads/ (path-traversal guard). */
    public function download(string $path)
    {
        $relative = $this->decodePath($path);
        $full = realpath(UploadPath::full($relative));
        $uploadsRoot = realpath(UploadPath::full('uploads'));

        if (!$full || !$uploadsRoot || !str_starts_with($full, $uploadsRoot)) {
            return response()->json(['status' => 'error', 'message' => 'Ruta inválida.'], 422);
        }

        return response()->download($full, basename($full));
    }

    /** Bulk actions from the table's multi-select bar: watermark or zip-download several files at once. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        $uploadsRoot = realpath(UploadPath::full('uploads'));
        $validFullPaths = collect($ids)
            ->map(fn ($id) => $this->decodePath($id))
            ->map(fn ($rel) => realpath(UploadPath::full($rel)))
            ->filter(fn ($full) => $full && $uploadsRoot && str_starts_with($full, $uploadsRoot))
            ->values();

        $action = $request->input('action');

        if ($action === 'watermark') {
            $count = 0;
            foreach ($validFullPaths as $full) {
                if ($this->watermarkFile($full)) {
                    $count++;
                }
            }

            return response()->json(['status' => 'success', 'message' => "{$count} imagen(es) marcada(s) con marca de agua."]);
        }

        if ($action === 'download') {
            return $this->streamZip($validFullPaths, $uploadsRoot);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /** Reads, watermarks (in place), and re-saves one file — encoding format is inferred from the path's extension. */
    private function watermarkFile(string $physicalPath): bool
    {
        try {
            $manager = new ImageManager(new Driver());
            $img = $this->applyWatermark($manager->read($physicalPath));
            $img->save($physicalPath);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Streams a zip archive containing every given file, named relative to $uploadsRoot so entries stay organized. */
    private function streamZip(Collection $fullPaths, string $uploadsRoot)
    {
        $filename = 'galeria-' . now()->format('Ymd-His') . '.zip';

        return response()->stream(function () use ($fullPaths, $uploadsRoot, $filename) {
            $zip = new ZipStream(outputName: $filename);

            foreach ($fullPaths as $full) {
                $entryName = ltrim(str_replace('\\', '/', substr($full, strlen($uploadsRoot))), '/');
                $zip->addFileFromPath(fileName: $entryName, path: $full);
            }

            $zip->finish();
        }, 200);
    }

    /** Recursively lists every image file under uploads/, with its top-level folder (about/logo/slider/product). */
    private function allImageFiles(): \Illuminate\Support\Collection
    {
        return (new FilesystemUploadsScanner())->scan()
            ->map(fn ($f) => [
                'path' => $f['relative_path'],
                'name' => $f['name'],
                'folder' => $f['folder'],
                'size' => $f['size'],
                'modified' => $f['modified'],
            ])
            ->values();
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1) . ' MB'
            : number_format($bytes / 1024, 1) . ' KB';
    }

    /** URL-safe base64 so a file's relative path (with slashes) can be a single route segment. */
    private function encodePath(string $path): string
    {
        return rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
    }

    private function decodePath(string $encoded): string
    {
        $b64 = strtr($encoded, '-_', '+/');
        if ($pad = strlen($b64) % 4) {
            $b64 .= str_repeat('=', 4 - $pad);
        }

        return base64_decode($b64);
    }
}

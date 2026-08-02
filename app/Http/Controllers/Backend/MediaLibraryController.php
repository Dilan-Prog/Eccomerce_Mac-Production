<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\DuplicateImages\FilesystemUploadsScanner;
use App\Support\UploadPath;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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

        return response()->json([
            'rows' => $paged->map(fn ($f) => [
                'row_id' => $this->encodePath($f['path']),
                'cells' => [
                    'thumbnail' => ['url' => asset($f['path'])],
                    'name' => $f['name'],
                    'folder' => ['label' => ucfirst($f['folder']), 'tone' => 'info'],
                    'size' => $this->formatBytes($f['size']),
                    'modified' => date('c', $f['modified']),
                    'actions' => [
                        ['label' => 'Ver', 'url' => asset($f['path']), 'target' => '_blank'],
                        ['label' => 'Copiar URL', 'copy' => true, 'value' => asset($f['path'])],
                        [
                            'label' => 'Borrar',
                            'url' => route('admin.media-library.destroy', ['path' => $this->encodePath($f['path'])]),
                            'method' => 'DELETE',
                            'tone' => 'critical',
                            'confirm' => true,
                        ],
                    ],
                ],
            ])->all(),
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

<?php

namespace App\Http\Controllers\Backend;

use App\Enums\DuplicateGroupStatus;
use App\Http\Controllers\Controller;
use App\Models\DuplicateImageGroup;
use App\Services\DuplicateImages\DuplicateConsolidationAction;
use App\Services\DuplicateImages\DuplicateScanOrchestrator;
use App\Services\DuplicateImages\FilesystemUploadsScanner;
use App\Services\DuplicateImages\ImageReferenceRegistry;
use App\Support\UploadPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Backend screen for reviewing image-duplicate groups detected under
 * public/uploads/**, discarding false positives, and consolidating real
 * duplicates onto a single "keeper" file (rewriting every DB reference that
 * pointed at a discarded copy before deleting it). Mirrors the JSON envelope
 * conventions already established by MediaLibraryController.
 */
class DuplicateImagesController extends Controller
{
    /** Known model classes we can confidently deep-link back to their admin edit screen. */
    private const EDIT_ROUTES = [
        \App\Models\Product::class => 'admin.products.edit',
        \App\Models\Slider::class => 'admin.slider.edit',
        \App\Models\Brand::class => 'admin.brand.edit',
        \App\Models\Category::class => 'admin.category.edit',
        \App\Models\Subcategory::class => 'admin.sub-category.edit',
        \App\Models\ChildCategory::class => 'admin.child-category.edit',
    ];

    public function __construct(
        private DuplicateScanOrchestrator $orchestrator,
        private ImageReferenceRegistry $registry,
        private DuplicateConsolidationAction $consolidation,
        private FilesystemUploadsScanner $scanner,
    ) {
    }

    /** POST duplicate-images/scan — triggers a scan, returns counts. */
    public function scan(Request $request)
    {
        $result = $this->orchestrator->scan();

        return response()->json([
            'status' => 'success',
            'message' => "Escaneo completo: {$result->groupsInserted} grupo(s) nuevo(s), {$result->filesScanned} archivo(s) revisado(s).",
            'data' => [
                'groups_inserted' => $result->groupsInserted,
                'groups_touched' => $result->groupsTouched,
                'groups_auto_resolved' => $result->groupsAutoResolved,
                'groups_deleted_stale' => $result->groupsDeletedStale,
                'files_scanned' => $result->filesScanned,
                'cache_misses' => $result->cacheMisses,
                'duration_ms' => $result->durationMs,
                'scanned_at' => $result->scannedAt->toIso8601String(),
            ],
        ]);
    }

    /** GET duplicate-images/data?status=pending|discarded|resolved|all&page=&per_page= — hand-built JSON for the card-grid UI. */
    public function data(Request $request)
    {
        $status = (string) $request->input('status', 'pending');

        $query = DuplicateImageGroup::query()->with('members')->orderByDesc('last_seen_at');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(100, (int) $request->input('per_page', 24)));

        $total = (clone $query)->count();
        $groups = $query->forPage($page, $perPage)->get();

        $tabs = [
            [
                'key' => 'pending',
                'label' => 'Pendientes',
                'count' => DuplicateImageGroup::where('status', DuplicateGroupStatus::Pending)->count(),
            ],
            [
                'key' => 'discarded',
                'label' => 'Descartados',
                'count' => DuplicateImageGroup::where('status', DuplicateGroupStatus::Discarded)->count(),
            ],
            [
                'key' => 'resolved',
                'label' => 'Resueltos',
                'count' => DuplicateImageGroup::where('status', DuplicateGroupStatus::Resolved)->count(),
            ],
            [
                'key' => 'all',
                'label' => 'Todos',
                'count' => DuplicateImageGroup::count(),
            ],
        ];

        $pendingStats = DuplicateImageGroup::where('status', DuplicateGroupStatus::Pending)
            ->selectRaw('COUNT(*) as groups_count, COALESCE(SUM(member_count), 0) as duplicate_files, COALESCE(SUM(recoverable_bytes), 0) as recoverable_bytes')
            ->first();

        $lastScanAt = DuplicateImageGroup::max('last_seen_at');

        $summary = [
            'groups_found' => $total,
            'duplicate_files' => (int) ($pendingStats->duplicate_files ?? 0),
            'recoverable_mb_estimate' => round(((int) ($pendingStats->recoverable_bytes ?? 0)) / 1048576, 1),
            'last_scan_at' => $lastScanAt ? \Illuminate\Support\Carbon::parse($lastScanAt)->toIso8601String() : null,
        ];

        $uploadsBase = UploadPath::base();

        $groupsPayload = $groups->map(function (DuplicateImageGroup $group) use ($uploadsBase) {
            return [
                'id' => $group->id,
                'group_key' => $group->group_key,
                'status' => $group->status->value,
                'member_count' => $group->member_count,
                'total_bytes' => $group->total_bytes,
                'recoverable_bytes' => $group->recoverable_bytes,
                'first_seen_at' => optional($group->first_seen_at)->toIso8601String(),
                'last_seen_at' => optional($group->last_seen_at)->toIso8601String(),
                'discarded_at' => optional($group->discarded_at)->toIso8601String(),
                'resolved_at' => optional($group->resolved_at)->toIso8601String(),
                'members' => $group->members->map(function ($member) use ($uploadsBase) {
                    $usedIn = $this->registry->findReferencesTo($member->physical_path)->map(function ($ref) {
                        $entry = [
                            'label' => $ref->label(),
                            'is_orphan' => $ref->isOrphan(),
                        ];

                        if (!$ref->isOrphan()) {
                            $editUrl = $this->inferEditUrl($ref->modelClass, $ref->modelId);
                            if ($editUrl) {
                                $entry['edit_url'] = $editUrl;
                            }
                        }

                        return $entry;
                    })->all();

                    return [
                        'id' => $member->id,
                        'path_hash' => $member->path_hash,
                        'url' => asset(ltrim(str_replace($uploadsBase, '', $member->physical_path), '/')),
                        'file_size' => $member->file_size,
                        'image_hash' => $member->image_hash,
                        'used_in' => $usedIn,
                    ];
                })->all(),
            ];
        })->values()->all();

        return response()->json([
            'status' => 'success',
            'summary' => $summary,
            'tabs' => $tabs,
            'groups' => $groupsPayload,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    /** GET duplicate-images/search?q=... — for the "buscar en toda la biblioteca" keeper picker. */
    public function search(Request $request)
    {
        $results = $this->scanner->searchByName((string) $request->input('q', ''));

        return response()->json([
            'status' => 'success',
            'results' => $results->map(fn ($f) => [
                'path_hash' => md5($f['physical_path']),
                'url' => asset($f['relative_path']),
                'name' => $f['name'],
                'size_label' => $f['size'] >= 1048576
                    ? number_format($f['size'] / 1048576, 1) . ' MB'
                    : number_format($f['size'] / 1024, 1) . ' KB',
            ])->values(),
        ]);
    }

    /** POST duplicate-images/{group}/discard */
    public function discard(DuplicateImageGroup $group)
    {
        $group->update(['status' => DuplicateGroupStatus::Discarded, 'discarded_at' => now()]);

        return response()->json(['status' => 'success', 'message' => 'Grupo marcado como no-duplicado.']);
    }

    /** POST duplicate-images/{group}/restore — safety valve back to pending. */
    public function restore(DuplicateImageGroup $group)
    {
        $group->update(['status' => DuplicateGroupStatus::Pending, 'discarded_at' => null, 'resolved_at' => null]);

        return response()->json(['status' => 'success', 'message' => 'Grupo restaurado a pendientes.']);
    }

    /**
     * POST duplicate-images/{group}/replace — the consolidation action. multipart/form-data:
     *   keeper_mode: "group_member" | "library_search" | "upload"
     *   keeper_path_hash: string (required for group_member/library_search)
     *   keeper_file: uploaded file (required for upload mode)
     *   discard_path_hashes: string[] (required, non-empty)
     */
    public function replace(Request $request, DuplicateImageGroup $group)
    {
        $request->validate([
            'keeper_mode' => ['required', 'in:group_member,library_search,upload'],
            'keeper_path_hash' => ['required_unless:keeper_mode,upload', 'nullable', 'string'],
            'keeper_file' => ['required_if:keeper_mode,upload', 'nullable', 'image', 'max:5120'],
            'discard_path_hashes' => ['required', 'array', 'min:1'],
            'discard_path_hashes.*' => ['string'],
        ]);

        $memberPathByHash = $group->members->keyBy('path_hash')->map(fn ($m) => $m->physical_path);

        $discardPaths = collect($request->input('discard_path_hashes'))
            ->map(fn ($h) => $memberPathByHash[$h] ?? null)
            ->filter()
            ->values()
            ->all();

        if (empty($discardPaths)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ninguno de los archivos seleccionados para descartar pertenece a este grupo.',
            ], 422);
        }

        $keeperPath = null;
        $uploadsRoot = realpath(UploadPath::full('uploads'));

        switch ($request->input('keeper_mode')) {
            case 'group_member':
                $keeperPath = $memberPathByHash[(string) $request->input('keeper_path_hash')] ?? null;
                if (!$keeperPath) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'El archivo a conservar no pertenece a este grupo.',
                    ], 422);
                }
                break;

            case 'library_search':
                $found = $this->scanner->findByPathHash((string) $request->input('keeper_path_hash'));
                $keeperPath = $found['physical_path'] ?? null;
                if (!$keeperPath) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No se encontró el archivo seleccionado en la biblioteca.',
                    ], 422);
                }

                $resolvedKeeper = realpath($keeperPath);
                if (!$resolvedKeeper || !$uploadsRoot || !str_starts_with($resolvedKeeper, $uploadsRoot)) {
                    return response()->json(['status' => 'error', 'message' => 'Ruta inválida.'], 422);
                }
                break;

            case 'upload':
                $file = $request->file('keeper_file');
                $uploadDir = UploadPath::full('uploads/media');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = uniqid('media_') . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $keeperPath = rtrim($uploadDir, '/') . '/' . $filename;
                break;
        }

        if (in_array($keeperPath, $discardPaths, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'El archivo a conservar no puede estar también en la lista de descarte.',
            ], 422);
        }

        $result = $this->consolidation->execute($group, $keeperPath, $discardPaths);

        $message = "{$result->referencesRewritten} referencia(s) actualizada(s), {$result->filesDeleted} archivo(s) eliminado(s).";
        if (!empty($result->filesSkipped)) {
            $message .= ' ' . count($result->filesSkipped) . ' archivo(s) no se eliminaron porque aún tienen referencias.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'references_rewritten' => $result->referencesRewritten,
                'files_deleted' => $result->filesDeleted,
                'files_skipped' => $result->filesSkipped,
                'group_status' => $result->groupStatus,
            ],
        ]);
    }

    /** Best-effort deep link to a referencing model's admin edit screen; null when we can't confidently infer a route. */
    private function inferEditUrl(?string $modelClass, mixed $modelId): ?string
    {
        if (!$modelClass || !$modelId || !isset(self::EDIT_ROUTES[$modelClass])) {
            return null;
        }

        $routeName = self::EDIT_ROUTES[$modelClass];
        if (!Route::has($routeName)) {
            return null;
        }

        return route($routeName, $modelId);
    }
}

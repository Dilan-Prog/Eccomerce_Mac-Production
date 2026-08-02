<?php

namespace App\Services\DuplicateImages;

use App\Enums\DuplicateGroupStatus;
use App\Models\DuplicateImageGroup;
use App\Models\DuplicateImageGroupMember;
use App\Support\DuplicateImages\ScanResult;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates one full duplicate-image scan: gathers every known image
 * reference, dHashes the underlying unique physical files, groups them by
 * perceptual similarity, and reconciles the result against the persisted
 * duplicate_image_groups/members tables (insert new groups, refresh
 * still-pending ones, auto-resolve or drop groups that no longer reproduce).
 */
class DuplicateScanOrchestrator
{
    public function __construct(
        private ImageReferenceRegistry $registry,
        private DHasher $hasher,
        private DuplicateGroupingService $grouping,
    ) {
    }

    public function scan(): ScanResult
    {
        $start = microtime(true);

        $refs = $this->registry->all();

        $uniquePaths = $refs
            ->pluck('physicalPath')
            ->unique()
            ->filter(fn (string $path) => file_exists($path))
            ->values();

        $hashesByPath = [];
        $cacheMisses = 0;

        foreach ($uniquePaths as $path) {
            $hash = $this->hasher->hash($path);
            if ($hash !== null) {
                $hashesByPath[$path] = $hash;
            } else {
                $cacheMisses++;
            }
        }

        $components = $this->grouping->group($hashesByPath, (int) config('duplicate_images.hamming_threshold'));

        $groupsInserted = 0;
        $groupsTouched = 0;
        $groupsAutoResolved = 0;
        $groupsDeletedStale = 0;
        $freshKeys = [];

        DB::transaction(function () use (
            $components,
            $hashesByPath,
            &$freshKeys,
            &$groupsInserted,
            &$groupsTouched,
            &$groupsAutoResolved,
            &$groupsDeletedStale,
        ) {
            foreach ($components as $paths) {
                sort($paths);
                $groupKey = hash('sha256', implode("\n", $paths));
                $freshKeys[] = $groupKey;

                $sizes = array_map(fn (string $p) => file_exists($p) ? (filesize($p) ?: 0) : 0, $paths);
                $totalBytes = array_sum($sizes);
                $recoverableBytes = $sizes === [] ? 0 : ($totalBytes - max($sizes));

                $existing = DuplicateImageGroup::where('group_key', $groupKey)->first();

                if ($existing) {
                    $existing->update(['last_seen_at' => now()]);

                    if ($existing->status === DuplicateGroupStatus::Pending) {
                        $existing->members()->delete();

                        foreach ($paths as $p) {
                            DuplicateImageGroupMember::create([
                                'duplicate_image_group_id' => $existing->id,
                                'path_hash' => md5($p),
                                'physical_path' => $p,
                                'image_hash' => $hashesByPath[$p] ?? '',
                                'file_size' => file_exists($p) ? (filesize($p) ?: 0) : 0,
                            ]);
                        }

                        $existing->update([
                            'member_count' => count($paths),
                            'total_bytes' => $totalBytes,
                            'recoverable_bytes' => $recoverableBytes,
                        ]);
                    }

                    $groupsTouched++;
                } else {
                    $group = DuplicateImageGroup::create([
                        'group_key' => $groupKey,
                        'status' => DuplicateGroupStatus::Pending,
                        'member_count' => count($paths),
                        'total_bytes' => $totalBytes,
                        'recoverable_bytes' => $recoverableBytes,
                        'first_seen_at' => now(),
                        'last_seen_at' => now(),
                    ]);

                    foreach ($paths as $p) {
                        DuplicateImageGroupMember::create([
                            'duplicate_image_group_id' => $group->id,
                            'path_hash' => md5($p),
                            'physical_path' => $p,
                            'image_hash' => $hashesByPath[$p] ?? '',
                            'file_size' => file_exists($p) ? (filesize($p) ?: 0) : 0,
                        ]);
                    }

                    $groupsInserted++;
                }
            }

            $stale = DuplicateImageGroup::whereNotIn('group_key', $freshKeys)->get();

            foreach ($stale as $group) {
                if ($group->status !== DuplicateGroupStatus::Pending) {
                    continue;
                }

                $stillExisting = $group->members->filter(fn ($m) => file_exists($m->physical_path))->count();

                if ($stillExisting < 2) {
                    $group->update([
                        'status' => DuplicateGroupStatus::Resolved,
                        'resolved_at' => now(),
                    ]);
                    $groupsAutoResolved++;
                } else {
                    $group->delete();
                    $groupsDeletedStale++;
                }
            }
        });

        return new ScanResult(
            groupsInserted: $groupsInserted,
            groupsTouched: $groupsTouched,
            groupsAutoResolved: $groupsAutoResolved,
            groupsDeletedStale: $groupsDeletedStale,
            filesScanned: count($hashesByPath),
            cacheMisses: $cacheMisses,
            durationMs: (int) round((microtime(true) - $start) * 1000),
            scannedAt: now(),
        );
    }
}

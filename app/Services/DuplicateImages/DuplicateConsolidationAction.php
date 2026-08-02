<?php

namespace App\Services\DuplicateImages;

use App\Enums\DuplicateGroupStatus;
use App\Models\DuplicateImageGroup;
use App\Models\ImageHashCache;
use App\Support\DuplicateImages\ConsolidationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Consolidates a duplicate-image group: rewrites every DB reference pointing
 * at the discarded physical paths so it points at the keeper instead, then
 * (post-commit, with a fresh re-check) deletes discarded files that no
 * longer have any surviving reference.
 */
class DuplicateConsolidationAction
{
    public function __construct(private ImageReferenceRegistry $registry)
    {
    }

    /**
     * @param  array<int, string>  $discardPhysicalPaths  physical paths to fold into the keeper and (if safe) delete
     */
    public function execute(DuplicateImageGroup $group, string $keeperPhysicalPath, array $discardPhysicalPaths): ConsolidationResult
    {
        $referencesRewritten = 0;

        DB::transaction(function () use ($group, $keeperPhysicalPath, $discardPhysicalPaths, &$referencesRewritten) {
            foreach ($discardPhysicalPaths as $discardPath) {
                if ($discardPath === $keeperPhysicalPath) {
                    continue;
                }

                $refs = $this->registry->findReferencesTo($discardPath);

                foreach ($refs as $ref) {
                    $this->registry->rewrite($ref, $keeperPhysicalPath);
                    $referencesRewritten++;
                }

                $group->members()->where('path_hash', md5($discardPath))->delete();
            }

            $remaining = $group->members()->count();

            $group->update([
                'member_count' => $remaining,
                'status' => $remaining < 2 ? DuplicateGroupStatus::Resolved : $group->status,
                'resolved_at' => $remaining < 2 ? now() : $group->resolved_at,
            ]);
        });

        // Transaction committed. Now, post-commit, re-verify before deleting
        // each physical file — a fresh query, not reused from inside the
        // transaction above, so we never delete a file another request
        // pointed a new reference at in the interim.
        $filesDeleted = 0;
        $filesSkipped = [];

        foreach ($discardPhysicalPaths as $discardPath) {
            if ($discardPath === $keeperPhysicalPath) {
                continue;
            }

            $freshRefs = $this->registry->findReferencesTo($discardPath);

            if ($freshRefs->isEmpty() && file_exists($discardPath)) {
                File::delete($discardPath);
                ImageHashCache::where('path_hash', md5($discardPath))->delete();
                $filesDeleted++;
            } elseif ($freshRefs->isNotEmpty()) {
                $filesSkipped[] = $discardPath;
            }
        }

        return new ConsolidationResult(
            referencesRewritten: $referencesRewritten,
            filesDeleted: $filesDeleted,
            filesSkipped: $filesSkipped,
            groupStatus: $group->fresh()->status->value,
        );
    }
}

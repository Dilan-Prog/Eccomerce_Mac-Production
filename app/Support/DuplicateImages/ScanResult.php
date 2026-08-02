<?php

namespace App\Support\DuplicateImages;

use Illuminate\Support\Carbon;

/**
 * Summary of one run of DuplicateScanOrchestrator::scan() — counts of what
 * changed in duplicate_image_groups plus basic timing/diagnostic info, used
 * to render a "scan complete" summary in the admin UI.
 */
final class ScanResult
{
    public function __construct(
        public readonly int $groupsInserted,
        public readonly int $groupsTouched,
        public readonly int $groupsAutoResolved,
        public readonly int $groupsDeletedStale,
        public readonly int $filesScanned,
        public readonly int $cacheMisses,
        public readonly int $durationMs,
        public readonly Carbon $scannedAt,
    ) {}
}

<?php

namespace App\Support\DuplicateImages;

/**
 * Outcome of one DuplicateConsolidationAction::execute() call — how many DB
 * references got rewritten to point at the keeper, how many discarded files
 * were actually deleted from disk, which ones were skipped because a
 * post-commit safety check found a lingering reference, and the group's
 * resulting status.
 */
final class ConsolidationResult
{
    public function __construct(
        public readonly int $referencesRewritten,
        public readonly int $filesDeleted,
        public readonly array $filesSkipped,
        public readonly string $groupStatus,
    ) {}
}

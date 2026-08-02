<?php

namespace App\Support\AdminTable;

use Illuminate\Http\Request;

/**
 * Normalized parameters for an AdminTableQuery::paginate() call, parsed from
 * the incoming HTTP request. Keeps AdminTableQuery decoupled from the HTTP
 * layer so it only ever deals with a plain value object.
 */
class AdminTableRequest
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 25,
        public ?string $search = null,
        public ?string $sort = null,
        public string $dir = 'asc',
        public ?string $filter = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $perPage = (int) $request->input('per_page', 25);
        $perPage = max(1, min($perPage, 200));

        $dir = strtolower((string) $request->input('dir', 'asc'));
        $dir = $dir === 'desc' ? 'desc' : 'asc';

        return new self(
            page: max(1, (int) $request->input('page', 1)),
            perPage: $perPage,
            search: $request->filled('search') ? (string) $request->input('search') : null,
            sort: $request->filled('sort') ? (string) $request->input('sort') : null,
            dir: $dir,
            filter: $request->filled('filter') ? (string) $request->input('filter') : null,
        );
    }
}

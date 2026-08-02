<?php

namespace App\Support\AdminTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base class for the custom, in-house replacement of Yajra\DataTables\Services\DataTable.
 * One subclass per admin module/listing (see app/Support/AdminTable/Queries/*).
 *
 * A module only declares WHAT to show (columns, saved-filter tabs, search,
 * row actions); this class handles the HOW (filtering, searching, sorting,
 * pagination, exporting, shaping the JSON contract) exactly once for every module.
 */
abstract class AdminTableQuery
{
    /** The Eloquent query this table lists. Mirrors the old DataTable::query(). */
    abstract public function baseQuery(): Builder;

    /**
     * Column definitions, in display order. Each entry:
     * [
     *   'key'        => string   unique column key (also the sort param & default cell source),
     *   'label'      => string   header label,
     *   'type'       => 'text'|'mono'|'badge'|'date'|'currency'|'person'|'actions' (default 'text'),
     *   'sortable'   => bool     whether clicking the header re-sorts by `key` (default false),
     *   'searchable' => bool     whether this column is matched by the default search() (default false),
     *   'width'      => ?int     px width hint, optional,
     *   'render'     => ?Closure(Model $row): mixed   computed cell value/badge/date, optional
     *                            (badge type expects ['label' => ..., 'tone' => 'success'|'warning'|'critical'|'info']),
     * ]
     */
    abstract public function columns(): array;

    /**
     * Saved-filter tabs (Shopify-style "Todas / Pendientes / ..."). Each entry:
     * ['key' => string, 'label' => string, 'apply' => Closure(Builder $q): Builder].
     * Return [] for modules with no tabs — a single implicit "Todas" tab is used.
     */
    public function filters(): array
    {
        return [];
    }

    /** Row action definitions (replaces hand-built "Acciones" HTML strings). */
    public function rowActions(Model $row): array
    {
        return [];
    }

    /**
     * Default full-text search across columns flagged `searchable`. Modules
     * with computed/relational search fields (e.g. Yajra's filterColumn)
     * should override this method entirely.
     */
    public function search(Builder $query, string $term): Builder
    {
        $searchableKeys = collect($this->columns())
            ->filter(fn ($col) => !empty($col['searchable']))
            ->pluck('key')
            ->all();

        if (empty($searchableKeys)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($searchableKeys, $term) {
            foreach ($searchableKeys as $key) {
                $q->orWhere($key, 'like', "%{$term}%");
            }
        });
    }

    /** Primary key used for row identity on the frontend (checkboxes, row id). */
    public function rowKey(): string
    {
        return 'id';
    }

    private function resolveActiveFilterKey(AdminTableRequest $request, array $filters): ?string
    {
        return $request->filter ?? ($filters[0]['key'] ?? null);
    }

    /** Applies the active saved-filter tab + free-text search. Shared by paginate() and exportRows(). */
    private function filteredQuery(AdminTableRequest $request): Builder
    {
        $filters = $this->filters();
        $activeFilterKey = $this->resolveActiveFilterKey($request, $filters);
        $activeFilter = collect($filters)->firstWhere('key', $activeFilterKey);

        $query = $this->baseQuery();
        if ($activeFilter) {
            $query = ($activeFilter['apply'])($query);
        }
        if ($request->search) {
            $query = $this->search($query, $request->search);
        }

        return $query;
    }

    private function resolveSort(AdminTableRequest $request): ?string
    {
        $sortableKeys = collect($this->columns())->filter(fn ($c) => !empty($c['sortable']))->pluck('key')->all();
        return in_array($request->sort, $sortableKeys, true) ? $request->sort : null;
    }

    /** Orchestrates filter -> search -> sort -> paginate -> shape response. */
    public function paginate(AdminTableRequest $request): array
    {
        $filters = $this->filters();
        $activeFilterKey = $this->resolveActiveFilterKey($request, $filters);

        $baseForCounts = $this->baseQuery();
        $filterCounts = [];
        foreach ($filters as $filter) {
            $countQuery = (clone $baseForCounts);
            $filterCounts[$filter['key']] = ($filter['apply'])($countQuery)->count();
        }

        $query = $this->filteredQuery($request);
        $total = (clone $query)->count();

        $columns = $this->columns();
        $sort = $this->resolveSort($request);
        if ($sort) {
            $query->orderBy($sort, $request->dir);
        } else {
            $query->orderByDesc($this->rowKey());
        }

        $records = $query
            ->forPage($request->page, $request->perPage)
            ->get();

        $rowKey = $this->rowKey();
        $rows = $records->map(function (Model $row) use ($columns, $rowKey) {
            $cells = [];
            foreach ($columns as $col) {
                if (($col['type'] ?? 'text') === 'actions') {
                    $cells[$col['key']] = $this->rowActions($row);
                    continue;
                }
                $cells[$col['key']] = isset($col['render'])
                    ? ($col['render'])($row)
                    : data_get($row, $col['key']);
            }
            return [
                'row_id' => $row->{$rowKey},
                'cells' => $cells,
            ];
        })->all();

        return AdminTableResponse::make(
            rows: $rows,
            total: $total,
            page: $request->page,
            perPage: $request->perPage,
            sort: $sort,
            dir: $request->dir,
            activeFilter: $activeFilterKey,
            filters: array_map(fn ($f) => [
                'key' => $f['key'],
                'label' => $f['label'],
                'count' => $filterCounts[$f['key']],
            ], $filters),
            columns: array_map(fn ($c) => [
                'key' => $c['key'],
                'label' => $c['label'],
                'type' => $c['type'] ?? 'text',
                'sortable' => !empty($c['sortable']),
                'width' => $c['width'] ?? null,
            ], $columns),
        );
    }

    /** Plain-text column headings for export (skips the non-exportable "actions" column). */
    public function exportHeadings(): array
    {
        return collect($this->columns())
            ->filter(fn ($c) => ($c['type'] ?? 'text') !== 'actions')
            ->pluck('label')
            ->all();
    }

    /** Flattens one row into plain scalar values (badges/person cells become plain text) for export. */
    public function exportRow(Model $row): array
    {
        $out = [];
        foreach ($this->columns() as $col) {
            $type = $col['type'] ?? 'text';
            if ($type === 'actions') {
                continue;
            }
            $value = isset($col['render']) ? ($col['render'])($row) : data_get($row, $col['key']);
            if (is_array($value)) {
                $value = $type === 'person'
                    ? trim(($value['title'] ?? '') . (!empty($value['subtitle']) ? ' (' . $value['subtitle'] . ')' : ''))
                    : ($value['label'] ?? '');
            }
            $out[] = (string) ($value ?? '');
        }
        return $out;
    }

    /** Every row matching the current filter/search (ignores pagination) — the export result set. */
    public function exportRows(AdminTableRequest $request): Collection
    {
        $query = $this->filteredQuery($request);
        $sort = $this->resolveSort($request);
        if ($sort) {
            $query->orderBy($sort, $request->dir);
        } else {
            $query->orderByDesc($this->rowKey());
        }

        return $query->get();
    }
}

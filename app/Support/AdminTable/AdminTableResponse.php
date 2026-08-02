<?php

namespace App\Support\AdminTable;

/**
 * Shapes the JSON contract consumed by public/admin-ui/js/table/admin-table.js.
 * Kept as a plain array-builder rather than a rigid DTO so column/row shapes
 * (which vary per module) stay flexible.
 */
class AdminTableResponse
{
    public static function make(
        array $rows,
        int $total,
        int $page,
        int $perPage,
        ?string $sort,
        string $dir,
        ?string $activeFilter,
        array $filters,
        array $columns,
    ): array {
        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'sort' => $sort,
            'dir' => $dir,
            'active_filter' => $activeFilter,
            'filters' => $filters,
            'columns' => $columns,
        ];
    }
}

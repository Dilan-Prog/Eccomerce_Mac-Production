<?php

namespace App\Support\AdminTable;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Generic Excel/CSV export shape shared by every admin module — built from
 * whatever AdminTableQuery::exportHeadings()/exportRow() produce, so no
 * per-module export class is needed (mirrors the AdminTableQuery abstraction:
 * write the export plumbing once, modules just declare columns).
 */
class AdminTableExport implements FromArray, WithHeadings
{
    public function __construct(
        private array $headings,
        private array $rows,
    ) {
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }
}

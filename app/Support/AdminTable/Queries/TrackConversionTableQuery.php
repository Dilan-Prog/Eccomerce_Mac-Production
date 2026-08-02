<?php

namespace App\Support\AdminTable\Queries;

use App\Models\TrackConversion;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;

/**
 * Replaces TrackConversionDataTable. Read-only tracking log (no create/edit/delete
 * UI in the old view — TrackConversionController only exposes index()+store(),
 * store() is used by the public-facing tracking pixel, not this admin table),
 * so there is no "actions" column and no bulk-delete support.
 */
class TrackConversionTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return TrackConversion::query();
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'gclid',
                'label' => 'Google ID (gclid)',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'type',
                'label' => 'Tipo de Conversion',
                'searchable' => true,
            ],
            [
                'key' => 'utm_source',
                'label' => 'utm_source',
                'searchable' => true,
            ],
            [
                'key' => 'utm_medium',
                'label' => 'utm_medium',
                'searchable' => true,
            ],
            [
                'key' => 'utm_campaign',
                'label' => 'Campaña',
                'searchable' => true,
            ],
            [
                'key' => 'landing_page',
                'label' => 'Url Conversion',
                'searchable' => true,
            ],
            [
                'key' => 'created_at',
                'label' => 'Fecha Registrada',
                'type' => 'date',
                'sortable' => true,
            ],
        ];
    }
}

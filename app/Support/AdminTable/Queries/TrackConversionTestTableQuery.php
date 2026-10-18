<?php

namespace App\Support\AdminTable\Queries;

use App\Models\TrackConversion;
use Illuminate\Database\Eloquent\Builder;

/**
 * Misma tabla que TrackConversionTableQuery (mismas columnas y pestañas) pero SOLO con los
 * eventos de prueba del Laboratorio de atribucion (is_test = true).
 */
class TrackConversionTestTableQuery extends TrackConversionTableQuery
{
    public function baseQuery(): Builder
    {
        return TrackConversion::query()->where('is_test', true);
    }
}

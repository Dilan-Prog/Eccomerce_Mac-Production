<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CotizacionMonedaAspel extends Model
{
    use HasFactory;

    protected $table = 'cotizacion_monedas_aspel';

    protected $fillable = [
        'num_moneda',
        'descripcion',
        'simbolo',
        'tipo_cambio',
        'status',
        'cve_moned',
    ];

    protected $casts = [
        'tipo_cambio' => 'float',
        'status' => 'boolean',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionPerfil extends Model
{
    protected $table = 'cotizacion_perfiles';

    protected $fillable = [
        'user_id',
        'tipo_persona',
        'razon_social',
        'rfc',
        'direccion_fiscal',
        'cif_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }
}

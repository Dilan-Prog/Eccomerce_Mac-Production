<?php
// app/Models/ServiceReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReport extends Model
{
    protected $fillable = [
        'user_id', 'folio', 'fecha_servicio', 'tipo_servicio', 'tecnico_nombre',
        'cliente_nombre', 'cliente_empresa', 'cliente_rfc', 'cliente_direccion',
        'cliente_telefono', 'cliente_email',
        'equipo_descripcion', 'equipo_marca', 'equipo_modelo', 'equipo_serie', 'equipo_ubicacion_tag',
        'mediciones', 'observaciones', 'recomendaciones', 'firma_tecnico', 'status',
    ];

    protected $casts = [
        'mediciones'      => 'array',
        'fecha_servicio'  => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

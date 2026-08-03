<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'folio',
        'user_id',
        'cotizacion_perfil_id',
        'created_by_admin_id',
        'source',
        'productos_json',
        'subtotal',
        'total',
        'currency',
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'productos_json' => 'array',
        'subtotal'       => 'decimal:2',
        'total'          => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function perfil()
    {
        return $this->belongsTo(CotizacionPerfil::class, 'cotizacion_perfil_id');
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CotizacionItem::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Precios extends Model
{
    use HasFactory;

    protected $table = 'precios';

    protected $fillable = [
        'cve_precio',
        'descripcion',
        'cve_bita',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relación 1 a Muchos: Un precio tiene muchos registros en la tabla intermedia
     */
    public function preciosXProductos()
    {
        return $this->hasMany(PrecioXProductAspel::class, 'cve_precio', 'cve_precio');
    }
}

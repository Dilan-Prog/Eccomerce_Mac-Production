<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioXProductAspel extends Model
{
    use HasFactory;

    protected $table = 'precio_x_product_aspel';

    protected $fillable = [
        'cve_art',
        'cve_precio',
        'precio'
    ];

    protected $casts = [
        'precio' => 'decimal:4',
    ];

    /**
     * Relación: Pertenece a un producto
     */
    public function producto()
    {
        return $this->belongsTo(AspelSync::class, 'cve_art', 'cve_art');
    }

    /**
     * Relación: Pertenece a un precio
     */
    public function precio_info()
    {
        return $this->belongsTo(Precios::class, 'cve_precio', 'cve_precio');
    }
}

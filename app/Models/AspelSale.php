<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Espejo de Aspel SAE FACTF01 (encabezado de facturas). Ver comentario de
 * la migración 2026_08_27_140000_create_aspel_sales_table para el porqué
 * de `fecha_cancela`.
 */
class AspelSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'cve_doc',
        'cve_clpv',
        'fecha_doc',
        'fecha_cancela',
        'importe',
        'rfc',
        'num_moned',
        'tipcamb',
        'uuid',
    ];

    protected $casts = [
        'fecha_doc' => 'datetime',
        'fecha_cancela' => 'datetime',
        'importe' => 'float',
        'tipcamb' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(AspelSaleItem::class, 'cve_doc', 'cve_doc');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Espejo de Aspel SAE PAR_FACTF01 (partidas de factura). `cve_doc` es FK
 * lógica a aspel_sales.cve_doc (sin FK real de BD — ver migración).
 */
class AspelSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cve_doc',
        'num_par',
        'cve_art',
        'cant',
        'prec',
        'tot_partida',
        'descr_art',
    ];

    protected $casts = [
        'num_par' => 'integer',
        'cant' => 'float',
        'prec' => 'float',
        'tot_partida' => 'float',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(AspelSale::class, 'cve_doc', 'cve_doc');
    }
}

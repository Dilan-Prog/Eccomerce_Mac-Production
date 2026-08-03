<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionItem extends Model
{
    protected $table = 'cotizacion_items';

    protected $fillable = [
        'cotizacion_id',
        'product_id',
        'product_variant_combination_id',
        'nombre',
        'sku',
        'modelo',
        'marca',
        'precio_unitario',
        'cantidad',
        'subtotal',
        'sort_order',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'cantidad'        => 'integer',
        'sort_order'      => 'integer',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariantCombination()
    {
        return $this->belongsTo(ProductVariantCombinations::class, 'product_variant_combination_id');
    }
}

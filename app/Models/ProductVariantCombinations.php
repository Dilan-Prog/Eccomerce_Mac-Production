<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantCombinations extends Model
{
    use HasFactory;
    protected $table = 'product_variants_combinations';

    protected $casts = [
        'variants_item_ids' => 'array',
    ];

public function product()
{
    return $this->belongsTo(Product::class);
}

    /**
     * Resuelve el precio efectivo de esta combinación replicando EXACTAMENTE
     * la lógica de CartController::addToCart() para combinaciones:
     *
     *     $combinationPrice = $combination->offert_price ?? $combination->price;
     *
     * A diferencia de Product::effectivePrice(), el código actual del
     * carrito NO aplica ningún gating por fecha de oferta a las
     * combinaciones (no llama a checkDiscount()/checkCombinationDiscount()
     * en esta ruta). Se replica ese comportamiento tal cual está hoy;
     * agregar el gating por fecha aquí cambiaría silenciosamente el
     * comportamiento real del checkout, lo cual está fuera de alcance.
     */
    public function effectivePrice(): float
    {
        return (float) ($this->offert_price ?? $this->price);
    }

}

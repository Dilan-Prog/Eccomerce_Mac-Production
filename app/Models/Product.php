<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
   use HasFactory;
   protected $fillable = [
    'thumb_image',
    'thumb_image_laptop',
    'thumb_image_tablet',
    'thumb_image_phone',
    'thumb_image_carrusel',
    'name',
    'slug',
    'category_id',
    'sub_category_id',
    'child_category_id',
    'brand_id',
    'qty',
    'qty_aspel',
    'qty_personalizated',
    'short_description',
    'long_description',
    'video_link',
    'url_PDF',
    'sku',
    'productModel',
    'price',
    'offert_price',
    'aspel_price',
    'aspel_price_tier',
    'price_personalizated',
    'aspel_offert_price',
    'aspel_offert_price_tier',
    'price_offert_personalizated',
    'offer_start_date',
    'offer_end_date',
    'product_type',
    'status',
    'is_approved',
    'seo_title',
    'seo_description',
    'canonical_Url',
    'is_canonical',
];


    public function category(){
        return $this->belongsTo(Category::class);
    }

    function productImageGalleries(){
        return $this->hasMany(ProductImageGallery::class);
    }

    // Relacion de productos
    function variants(){
        return $this->hasMany(ProductVariant::class);
    }

    function  combinations()
    {
        return $this->hasMany(ProductVariantCombinations::class);
    }

    function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function moreEccomerce(){
        return $this->hasMany(ProductMoreEccomerce::class);
    }

    /**
     * Resuelve el precio efectivo de este producto replicando EXACTAMENTE la
     * lógica de CartController::addToCart() (la versión autoritativa que
     * determina lo que realmente se cobra al cliente).
     *
     * No reinventa la comparación de fechas de oferta: reutiliza el helper
     * global checkDiscount() (app/Helper/helpers.php) para que el resultado
     * sea idéntico al que produce hoy el checkout.
     */
    public function effectivePrice(): float
    {
        // Determinar precio base usando la lógica de price_personalizated
        $basePrice = $this->price_personalizated == 1
            ? $this->price
            : ($this->aspel_price ?? $this->price);

        // Determinar precio de oferta usando la lógica de price_offert_personalizated
        $offerPrice = $this->price_offert_personalizated == 1
            ? $this->offert_price
            : ($this->aspel_offert_price ?? $this->offert_price);

        // Precio con descuento si aplica (misma condición que el checkout)
        return (float) (checkDiscount($this) ? $offerPrice : $basePrice);
    }

    /**
     * Resuelve el stock efectivo de este producto replicando EXACTAMENTE la
     * lógica de CartController::addToCart() / updateProductQty().
     */
    public function effectiveStock(): int
    {
        return (int) ($this->qty_personalizated == 0 ? $this->qty_aspel : $this->qty);
    }

}

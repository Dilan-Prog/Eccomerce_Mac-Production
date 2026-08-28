<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    /**
     * Aditiva — no existía. Usada por MarketingOfferBuilder para resolver la
     * categoría dominante de compra de un cliente (agrupando por
     * product->category_id) sin tener que ir a buscar el producto a mano en
     * cada sitio que lo necesite.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

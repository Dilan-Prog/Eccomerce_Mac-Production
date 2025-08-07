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

}

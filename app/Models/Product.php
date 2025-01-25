<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
   use HasFactory;
   protected $fillable = [
    'thumb_image',
    'name',
    'slug',
    'category_id',
    'sub_category_id',
    'child_category_id',
    'brand_id',
    'qty',
    'short_description',
    'long_description',
    'video_link',
    'url_PDF',
    'sku',
    'productModel',
    'price',
    'offert_price',
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

    function brand(){

        return $this->belongsTo(Brand::class);
        
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

}

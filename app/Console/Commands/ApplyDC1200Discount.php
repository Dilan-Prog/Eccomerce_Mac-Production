<?php

namespace App\Console\Commands;
use App\Models\Product;
use App\Models\Category;

use Illuminate\Console\Command;

class ApplyDC1200Discount extends Command
{
   protected $signature = 'products:discount-dc1200';
    protected $description = 'Aplica 10% de descuento a productos de la categoría DC1200';
    
    public function handle()
    {
        // Busca la categoría DC1200 por nombre o slug
        $products = Product::where('category_id', 4)
        ->where('sub_category_id', 23)
        ->get();



        foreach ($products as $product) {
            $product->offert_price = $product->price - ($product->price * 0.10);
            $product->offer_start_date = '2025-05-01';
            $product->offer_end_date = '2025-12-30';
            
            $product->save();
        }
        // dd($product);
        $this->info('Descuento aplicado a ' . $products->count() . ' productos.');
    }
}

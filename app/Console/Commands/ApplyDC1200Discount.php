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
        $products = Product::all();

        foreach ($products as $product) {
            // Si el modelo inicia con "TZ", asigna el video
            if (strpos($product->productModel, 'DC1010') === 0) {
                $product->video_link = 'https://www.youtube.com/embed/2-KNdxuNm8Q';
            }

            $product->save();
        }

        $this->info('Descuento aplicado a ' . $products->count() . ' productos.');
    }
}

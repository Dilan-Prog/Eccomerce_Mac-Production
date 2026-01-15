<?php

namespace App\Console\Commands;
use App\Models\Product;
use App\Models\Category;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyDC1200Discount extends Command
{
   protected $signature = 'products:discount-dc1200';
    protected $description = 'Sincroniza qty_aspel desde aspel_products.exist';
    
    public function handle()
    {
        DB::transaction(function () {
            $ivaValue = DB::table('general_settings')->value('iva_mexico');
            $products = \App\Models\Product::all();
            foreach ($products as $product) {
                $aspelProduct = \App\Models\AspelSync::where('cve_art', $product->sku)->first();
                $aspelPrice = null;
                $finalPrice = null;
                if ($aspelProduct) {
                    $aspelPriceObj = \App\Models\PrecioXProductAspel::where('cve_art', $aspelProduct->cve_art)
                        ->where('cve_precio', 1)
                        ->first();
                    if ($aspelPriceObj) {
                        $aspelPrice = $aspelPriceObj->precio;
                        // Obtener moneda y tipo de cambio
                        $aspelCurrency = DB::table('monedas_aspel')
                            ->where('num_moneda', $aspelProduct->num_mon)
                            ->first();
                        $isMXN = true;
                        $exchangeRate = 1.0;
                        if ($aspelCurrency) {
                            $isMXN = ($aspelCurrency->cve_moned === 'MXN');
                            if (!$isMXN) {
                                $exchangeRate = floatval($aspelCurrency->tipo_cambio);
                            }
                        }
                        if ($isMXN) {
                            $finalPrice = $aspelPrice * (1 + floatval($ivaValue) / 100);
                        } else {
                            $finalPrice = $aspelPrice * $exchangeRate * (1 + floatval($ivaValue) / 100);
                        }
                    }
                }
                if (!is_null($finalPrice)) {
                    $product->price = $finalPrice;
                    $product->price_personalizated = 0; 
                }
                $product->save();
            }
        });

        $this->info('✔ Stock Aspel sincronizado correctamente con IVA y tipo de cambio');
    }
}

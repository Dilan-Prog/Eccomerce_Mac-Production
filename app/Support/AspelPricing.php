<?php

namespace App\Support;

use App\Models\AspelSync;
use App\Models\PrecioXProductAspel;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for turning a raw precio_x_product_aspel row into
 * the MXN, IVA-inclusive amount shown/stored throughout the admin (was
 * previously duplicated between ProductController::edit()/editFragment()'s
 * Blade @php block and ProductController::getAspelPrices()).
 *
 * "Tier" here means a precio_x_product_aspel row's cve_precio (the price
 * level code — público/mínimo/liquidación/mayorista — see Precios model).
 */
class AspelPricing
{
    /**
     * All price-tier options currently available for a given Aspel SKU
     * (cve_art), converted to MXN with IVA applied.
     *
     * @return array<int, array{cve_precio:int, descripcion:string, raw:float, converted:float, with_iva:float}>
     */
    public static function optionsForSku(string $cveArt): array
    {
        $aspelProduct = AspelSync::where('cve_art', $cveArt)->first();
        if (!$aspelProduct) {
            return [];
        }

        $ivaValue = (float) (DB::table('general_settings')->value('iva_mexico') ?? 16.00);

        $currency = DB::table('monedas_aspel')->where('num_moneda', $aspelProduct->num_mon)->first();
        $isMxn = !$currency || $currency->cve_moned === 'MXN';
        $exchangeRate = $isMxn ? 1.0 : (float) $currency->tipo_cambio;

        return PrecioXProductAspel::with('precio_info')
            ->where('cve_art', $cveArt)
            ->get()
            ->map(function ($opt) use ($isMxn, $exchangeRate, $ivaValue) {
                $raw = (float) $opt->precio;
                $converted = $isMxn ? $raw : $raw * $exchangeRate;
                $withIva = $converted * (1 + $ivaValue / 100);

                return [
                    'cve_precio' => (int) $opt->cve_precio,
                    'descripcion' => optional($opt->precio_info)->descripcion ?? ('Precio ' . $opt->cve_precio),
                    'raw' => $raw,
                    'converted' => $converted,
                    'with_iva' => $withIva,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * The current IVA-inclusive MXN price for one specific tier of one SKU,
     * or null if that SKU/tier combination has no price row right now. Used
     * to recompute a stored aspel_price_tier's value at save time and by the
     * scheduled refresh job (app:sync-aspel-prices).
     */
    public static function amountForTier(string $cveArt, int $cvePrecio): ?float
    {
        foreach (static::optionsForSku($cveArt) as $option) {
            if ($option['cve_precio'] === $cvePrecio) {
                return $option['with_iva'];
            }
        }

        return null;
    }
}

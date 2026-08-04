<?php

namespace App\Support;

use App\Models\PrecioXProductAspel;

/**
 * Cotizaciones' own, isolated price-tier lookup — deliberately independent
 * of App\Support\AspelPricing. Reads the same underlying
 * precio_x_product_aspel table, but returns the raw MXN value as-is: no IVA
 * multiplication, no monedas_aspel currency conversion. Aspel's own price
 * tiers (used by the Products admin module) stay IVA-inclusive and
 * currency-converted; quotes need their own no-IVA reference prices plus a
 * manual, per-quote currency/exchange-rate system (see Cotizacion::displayAmount()),
 * so nothing here should ever call AspelPricing or touch monedas_aspel.
 */
class CotizacionPricing
{
    /**
     * All price-tier options currently available for a given Aspel SKU
     * (cve_art), raw MXN, no IVA.
     *
     * @return array<int, array{cve_precio:int, descripcion:string, precio:float}>
     */
    public static function optionsForSku(string $cveArt): array
    {
        return PrecioXProductAspel::with('precio_info')
            ->where('cve_art', $cveArt)
            ->get()
            ->map(fn ($opt) => [
                'cve_precio' => (int) $opt->cve_precio,
                'descripcion' => optional($opt->precio_info)->descripcion ?? ('Precio ' . $opt->cve_precio),
                'precio' => (float) $opt->precio,
            ])
            ->values()
            ->all();
    }

    /** cve_precio codes, per the existing convention documented in AdminCotizacionController::storeItem(). */
    private const CVE_PUBLICO = 1;
    private const CVE_MINIMO = 2;

    /**
     * The "mínimo" tier's raw price for a SKU, or null if that SKU has no
     * tiers at all / no mínimo tier. Used as the floor for the custom-price
     * field — a 0/missing mínimo means "no real floor", callers should treat
     * that as 1 (see docblock on the caller side).
     */
    public static function minimoFor(string $cveArt): ?float
    {
        return static::findTier($cveArt, self::CVE_MINIMO, 'minimo');
    }

    /** The "público" tier's raw price for a SKU, or null if not available. */
    public static function publicoFor(string $cveArt): ?float
    {
        return static::findTier($cveArt, self::CVE_PUBLICO, 'publico');
    }

    /**
     * Matches a tier primarily by its cve_precio code (1=público, 2=mínimo —
     * the fixed convention this app already relies on elsewhere), falling
     * back to matching its descripcion (accent/case-insensitively, so
     * "Público"/"PUBLICO"/"público " etc. all match "publico") in case a
     * given catalog doesn't follow that numbering.
     */
    private static function findTier(string $cveArt, int $cvePrecio, string $label): ?float
    {
        $options = static::optionsForSku($cveArt);

        foreach ($options as $option) {
            if ($option['cve_precio'] === $cvePrecio) {
                return $option['precio'];
            }
        }

        foreach ($options as $option) {
            if (static::normalize($option['descripcion']) === $label) {
                return $option['precio'];
            }
        }

        return null;
    }

    private static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);

        return $value;
    }
}

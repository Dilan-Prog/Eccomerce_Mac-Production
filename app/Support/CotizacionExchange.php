<?php

namespace App\Support;

use App\Models\AspelSync;
use App\Models\Cotizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Conversión USD⇄MXN exclusiva del módulo de Cotizaciones — deliberadamente
 * aislada de App\Support\AspelPricing y de monedas_aspel.tipo_cambio (esos
 * siguen sirviendo solo al catálogo de Productos). El tipo de cambio que usa
 * esta clase SIEMPRE viene de general_settings (default global) o de la
 * propia cotización (override del vendedor) — nunca de Aspel.
 *
 * Lo único que SÍ se lee de Aspel aquí es la *identidad* de moneda de un SKU
 * (aspel_products.num_mon -> monedas_aspel.cve_moned), porque ese dato no
 * tiene alternativa: es lo único que dice si un producto está cargado en
 * Aspel en USD o en MXN.
 */
class CotizacionExchange
{
    /**
     * Código de moneda nativo de un SKU Aspel ('MXN'/'USD'/otro), o null si
     * el SKU no existe en aspel_products o no tiene moneda asignada.
     */
    public static function nativeCurrencyForSku(string $cveArt): ?string
    {
        $numMon = AspelSync::where('cve_art', $cveArt)->value('num_mon');

        if (!$numMon) {
            return null;
        }

        return DB::table('monedas_aspel')->where('num_moneda', $numMon)->value('cve_moned');
    }

    /** Default global USD->MXN (pesos por dólar) desde Configuración General. */
    public static function defaultUsdToMxn(): float
    {
        $rate = DB::table('general_settings')->value('tipo_cambio_usd_mxn');

        if (!$rate) {
            Log::warning('CotizacionExchange: tipo_cambio_usd_mxn no configurado en Configuración General, usando 1.0 (sin conversión).');
            return 1.0;
        }

        return (float) $rate;
    }

    /** Default global MXN->USD (dólares por peso) desde Configuración General. */
    public static function defaultMxnToUsd(): float
    {
        $rate = DB::table('general_settings')->value('tipo_cambio_mxn_usd');

        if (!$rate) {
            Log::warning('CotizacionExchange: tipo_cambio_mxn_usd no configurado en Configuración General, usando 1.0 (sin conversión).');
            return 1.0;
        }

        return (float) $rate;
    }

    /**
     * Tipo de cambio USD->MXN EFECTIVO para una cotización concreta: su
     * propio override (cotizacion.exchange_rate) si lo tiene, si no el
     * default global.
     */
    public static function effectiveUsdToMxnRate(Cotizacion $cotizacion): float
    {
        return $cotizacion->exchange_rate ? (float) $cotizacion->exchange_rate : static::defaultUsdToMxn();
    }

    /**
     * Normaliza un precio crudo de Aspel a MXN: si la moneda nativa es USD
     * lo multiplica por el tipo de cambio USD->MXN; si es MXN o no se pudo
     * determinar (SKU sin datos Aspel, moneda desconocida), lo regresa tal
     * cual — misma suposición conservadora que ya existía implícitamente en
     * todo el resto del sistema (todo lo que no se sabe que es USD, se trata
     * como MXN).
     */
    public static function normalizeToMxn(float $rawPrice, ?string $nativeCurrency, float $rateUsdMxn): float
    {
        if ($nativeCurrency === 'USD') {
            return round($rawPrice * $rateUsdMxn, 2);
        }

        return round($rawPrice, 2);
    }
}

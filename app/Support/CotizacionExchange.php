<?php

namespace App\Support;

use App\Models\AspelSync;
use App\Models\Cotizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Conversión USD⇄MXN exclusiva del módulo de Cotizaciones — deliberadamente
 * aislada de App\Support\AspelPricing y de monedas_aspel.tipo_cambio (esos
 * siguen sirviendo solo al catálogo de Productos, sembrados manualmente, sin
 * sincronizar). El default global que usa esta clase viene de
 * cotizacion_monedas_aspel (sincronizado a diario desde Aspel vía
 * POST /api/aspel/tipo-cambio, tabla aislada — ver
 * CotizacionMonedaSyncController) cuando existe, o si no de general_settings
 * (valor manual de respaldo) — la propia cotización (override del vendedor)
 * sigue ganando siempre por encima de cualquiera de los dos.
 *
 * Lo único que SÍ se lee de la tabla del catálogo (monedas_aspel) aquí es la
 * *identidad* de moneda de un SKU (aspel_products.num_mon ->
 * monedas_aspel.cve_moned), porque ese dato no tiene alternativa: es lo
 * único que dice si un producto está cargado en Aspel en USD o en MXN.
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

    /** Tipo de cambio USD->MXN sincronizado hoy desde Aspel (cotizacion_monedas_aspel), o null si aún no ha llegado ninguno. */
    private static function syncedUsdToMxn(): ?float
    {
        $rate = DB::table('cotizacion_monedas_aspel')
            ->where('cve_moned', 'USD')
            ->where('status', 1)
            ->value('tipo_cambio');

        return ($rate && (float) $rate > 0) ? (float) $rate : null;
    }

    /**
     * Default global USD->MXN (pesos por dólar): el tipo de cambio
     * sincronizado hoy desde Aspel si existe, si no el valor manual de
     * respaldo en Configuración General.
     */
    public static function defaultUsdToMxn(): float
    {
        if ($synced = static::syncedUsdToMxn()) {
            return $synced;
        }

        $rate = DB::table('general_settings')->value('tipo_cambio_usd_mxn');

        if (!$rate) {
            Log::warning('CotizacionExchange: sin tipo de cambio sincronizado de Aspel ni manual en Configuración General, usando 1.0 (sin conversión).');
            return 1.0;
        }

        return (float) $rate;
    }

    /**
     * Default global MXN->USD (dólares por peso): inverso matemático del
     * tipo de cambio sincronizado hoy desde Aspel si existe (1 / USD->MXN),
     * si no el valor manual de respaldo en Configuración General.
     */
    public static function defaultMxnToUsd(): float
    {
        if ($synced = static::syncedUsdToMxn()) {
            return round(1 / $synced, 6);
        }

        $rate = DB::table('general_settings')->value('tipo_cambio_mxn_usd');

        if (!$rate) {
            Log::warning('CotizacionExchange: sin tipo de cambio sincronizado de Aspel ni manual en Configuración General, usando 1.0 (sin conversión).');
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
     * Tipo de cambio MXN->USD EFECTIVO para una cotización concreta: su
     * propio override (cotizacion.exchange_rate_mxn_usd) si lo tiene, si no
     * el default global — mismo patrón que effectiveUsdToMxnRate(), en la
     * dirección inversa.
     */
    public static function effectiveMxnToUsdRate(Cotizacion $cotizacion): float
    {
        return $cotizacion->exchange_rate_mxn_usd ? (float) $cotizacion->exchange_rate_mxn_usd : static::defaultMxnToUsd();
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

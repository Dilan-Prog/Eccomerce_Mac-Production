<?php

namespace App\Models;

use App\Support\CotizacionExchange;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    /**
     * A partir de 2026-08-03 los folios nuevos deben leerse como continuación
     * de la numeración que ya conocían clientes/vendedores (cotizaciones en
     * papel/Aspel), no reiniciar en 00001 — se pidió que el primer folio
     * nuevo caiga alrededor de "04500". En ese momento ya existían 13
     * cotizaciones reales (ids 1-13, folios COT-2026-00001..00013) que NO se
     * renumeran; este offset solo aplica a partir del siguiente id (14 en
     * adelante). 4486 + 14 = 4500.
     */
    private const FOLIO_NUMBER_OFFSET = 4486;

    /** Folio definitivo: COT-{AÑO}-{ID + offset, con 5 ceros}. */
    public static function buildFolio(int $id): string
    {
        $number = $id + self::FOLIO_NUMBER_OFFSET;

        return 'COT-' . now()->year . '-' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'folio',
        'user_id',
        'cotizacion_perfil_id',
        'created_by_admin_id',
        'source',
        'productos_json',
        'subtotal',
        'total',
        'currency',
        'exchange_rate',
        'exchange_rate_mxn_usd',
        'tiempo_entrega_general',
        'status',
        'pdf_path',
        'precio_minimo_autorizado_at',
        'precio_minimo_autorizado_por',
    ];

    protected $casts = [
        'productos_json'               => 'array',
        'subtotal'                     => 'decimal:2',
        'total'                        => 'decimal:2',
        'exchange_rate'                => 'decimal:4',
        'exchange_rate_mxn_usd'        => 'decimal:4',
        'precio_minimo_autorizado_at'  => 'datetime',
        'tiempo_entrega_general'       => 'date',
    ];

    /**
     * Converts a raw MXN amount (how every price is always stored — see
     * App\Support\CotizacionPricing/CotizacionExchange) into this quote's
     * chosen display currency. Deliberately isolated from Aspel's
     * monedas_aspel exchange rates: these are manual, per-quote rates
     * entered by the vendor (or defaulted from Configuración General — see
     * App\Support\CotizacionExchange).
     *
     * Uses exchange_rate_mxn_usd (dollars per peso, multiplication) when
     * present. Falls back to dividing by the older `exchange_rate` (pesos
     * per dollar) for quotes finalized before exchange_rate_mxn_usd existed,
     * so their PDFs keep producing the exact same total they always had. If
     * NEITHER is set on this quote (e.g. the vendor picked USD but never
     * touched any override input), falls back to the LIVE Configuración
     * General default via CotizacionExchange::effectiveMxnToUsdRate() —
     * without this, a quote could sit at currency='USD' with both rate
     * columns null and simply never convert anything.
     */
    public function displayAmount(float $mxnAmount): float
    {
        if ($this->currency !== 'USD') {
            return round($mxnAmount, 2);
        }

        if ($this->exchange_rate_mxn_usd) {
            return round($mxnAmount * (float) $this->exchange_rate_mxn_usd, 2);
        }

        if ($this->exchange_rate) {
            return round($mxnAmount / (float) $this->exchange_rate, 2);
        }

        return round($mxnAmount * CotizacionExchange::effectiveMxnToUsdRate($this), 2);
    }

    /**
     * Like displayAmount(), but resolves ONE line's amount with a single
     * special case: when the item's native Aspel currency EXACTLY matches
     * this quote's display currency (USD-native item, quote shown in USD),
     * it returns the original raw amount as-is — no MXN round-trip, no
     * rounding drift. Every other combination (MXN-native shown in MXN,
     * MXN-native shown in USD, USD-native shown in MXN, or missing origin
     * data because the line predates this feature / came from
     * precio_personalizado / a variant combination) delegates to
     * displayAmount(), which already handles those correctly.
     *
     * $mxnAmount and $montoOrigenEquivalente must already be aligned by the
     * caller to the same quantity (both per-unit, or both already
     * multiplied by cantidad) — this method doesn't know about quantities.
     */
    public function displayItemAmount(float $mxnAmount, ?string $monedaOrigen, ?float $montoOrigenEquivalente): float
    {
        if ($this->currency === 'USD' && $monedaOrigen === 'USD' && $montoOrigenEquivalente !== null) {
            return round($montoOrigenEquivalente, 2);
        }

        return $this->displayAmount($mxnAmount);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function perfil()
    {
        return $this->belongsTo(CotizacionPerfil::class, 'cotizacion_perfil_id');
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function autorizadorPrecioMinimo()
    {
        return $this->belongsTo(User::class, 'precio_minimo_autorizado_por');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CotizacionItem::class);
    }
}

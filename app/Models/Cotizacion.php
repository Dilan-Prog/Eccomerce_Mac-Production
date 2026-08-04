<?php

namespace App\Models;

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
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'productos_json' => 'array',
        'subtotal'       => 'decimal:2',
        'total'          => 'decimal:2',
        'exchange_rate'  => 'decimal:4',
    ];

    /**
     * Converts a raw MXN amount (how every price is always stored — see
     * App\Support\CotizacionPricing) into this quote's chosen display
     * currency. Deliberately isolated from Aspel's monedas_aspel exchange
     * rates: this is a manual, per-quote rate entered by the vendor.
     */
    public function displayAmount(float $mxnAmount): float
    {
        if ($this->currency === 'USD' && $this->exchange_rate) {
            return round($mxnAmount / (float) $this->exchange_rate, 2);
        }

        return round($mxnAmount, 2);
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

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CotizacionItem::class);
    }
}

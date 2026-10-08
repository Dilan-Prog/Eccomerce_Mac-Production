<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackConversion extends Model
{
    use HasFactory;

    /** Visita registrada una vez por sesión (no es conversión). */
    public const VISIT_TYPE = 'page_visit';

    /**
     * Interacciones intermedias: se miden pero NO cuentan como "conversión" en el admin
     * (carrito, cotización iniciada/descartada). Todo lo demás distinto de la visita
     * (whatsapp*, telefono*, correo*, quote_submit…) es conversión.
     */
    public const ENGAGEMENT_TYPES = [
        'add_to_cart',
        'add_to_cart_blocked',
        'quote_start',
        'quote_dismiss',
        'quote_abandon',
    ];

    /** Claves permitidas dentro de `meta` (lo demás se descarta al guardar). */
    public const META_KEYS = [
        'product_id', 'sku', 'product_name', 'brand', 'qty', 'value', 'currency',
        'placement', 'link_text', 'href', 'folio', 'intent', 'form_variant', 'cart_error',
    ];

    protected $fillable = [
        'gclid',
        'type',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'landing_page',
        'gbraid',
        'wbraid',
        'fbclid',
        'utm_term',
        'utm_content',
        'referrer',
        'page_url',
        'session_id',
        'event_id',
        'captured_at',
        'meta',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'meta' => 'array',
    ];

    /** Tipos que NO son conversión (visita + interacciones), para filtros SQL. */
    public static function nonConversionTypes(): array
    {
        return array_merge([self::VISIT_TYPE], self::ENGAGEMENT_TYPES);
    }
}

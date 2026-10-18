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
        // Postulación a una vacante por WhatsApp (página /empleos): no es una venta.
        'job_apply',
        'job_question',
    ];

    /** Claves permitidas dentro de `meta` (lo demás se descarta al guardar). */
    public const META_KEYS = [
        'product_id', 'sku', 'product_name', 'brand', 'qty', 'value', 'currency',
        'placement', 'link_text', 'href', 'folio', 'intent', 'form_variant', 'cart_error',
        'ref', 'puesto', 'area',
        // Atribucion por folio: tipo viejo (data-type) del boton, click ids descartados por formato
        // invalido y datos de la captura en el navegador (ver TrackConversionController::store()).
        'legacy_type', 'click_id_rejected', 'click_id_rejected_val',
        'click_id_at', 'click_channel', 'touch_conflict', 'attribution_at',
    ];

    /** Folio de contacto: "MN" + 5 caracteres del alfabeto sin vocales ni ambiguos (ver config/attribution.php). */
    public const FOLIO_REGEX = '/^MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}$/';

    /** gclid valido: 20-255 caracteres URL-safe, sin prefijo gtm y no solo digitos (descarta basura de GTM/pruebas). */
    public const GCLID_REGEX = '/^[A-Za-z0-9_-]{20,255}$/';

    /** gbraid/wbraid validos: 10-255 caracteres URL-safe. */
    public const BRAID_REGEX = '/^[A-Za-z0-9_-]{10,255}$/';

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
        'contact_folio',
        'captured_at',
        'event_at',
        'meta',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'event_at' => 'datetime',
        'meta' => 'array',
    ];

    /** Valida el formato de un gclid (ver GCLID_REGEX). Reglas compartidas por el controlador y la API. */
    public static function isValidGclid(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return preg_match(self::GCLID_REGEX, $value) === 1
            && !preg_match('/^gtm/i', $value)
            && !preg_match('/^\d+$/', $value);
    }

    /** Valida el formato de un gbraid/wbraid (ver BRAID_REGEX). */
    public static function isValidBraid(?string $value): bool
    {
        return $value !== null && $value !== '' && preg_match(self::BRAID_REGEX, $value) === 1;
    }

    /**
     * Tipo canonico de un evento de contacto, por PREFIJO: whatsapp* -> whatsapp_click,
     * telefono*|phone_click -> phone_click, correo*|email_click -> email_click. Los tipos que ya
     * son canonicos o de otra familia (page_visit, quote_*, add_to_cart*, job_*) y cualquier tipo
     * desconocido se devuelven tal cual. No reescribe datos historicos: solo sirve para leer.
     */
    public static function canonicalType(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $t = strtolower(trim($type));

        if (str_starts_with($t, 'whatsapp')) {
            return 'whatsapp_click';
        }
        if (str_starts_with($t, 'telefono') || $t === 'phone_click') {
            return 'phone_click';
        }
        if (str_starts_with($t, 'correo') || $t === 'email_click') {
            return 'email_click';
        }

        return $type;
    }

    /** Tipos que NO son conversión (visita + interacciones), para filtros SQL. */
    public static function nonConversionTypes(): array
    {
        return array_merge([self::VISIT_TYPE], self::ENGAGEMENT_TYPES);
    }
}

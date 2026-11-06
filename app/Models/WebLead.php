<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Solicitud enviada desde un formulario publico (ver migracion create_web_leads_table).
 */
class WebLead extends Model
{
    /** form_type => etiqueta para el correo y el panel. */
    public const FORM_TYPES = [
        'asociado' => 'Asociados',
        'calibracion_puesta' => 'Calibración y puesta en marcha',
        'medicion' => 'Medición',
        'cotizacion' => 'Cotización pública',
    ];

    public const STATUSES = [
        'nuevo' => 'Nuevo',
        'contactado' => 'Contactado',
        'descartado' => 'Descartado',
    ];

    protected $fillable = [
        'form_type', 'first_name', 'last_name', 'email', 'phone', 'company', 'city', 'state', 'operation', 'message',
        'source_page', 'landing_page', 'referrer', 'session_id',
        'gclid', 'gbraid', 'wbraid', 'fbclid',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'status', 'notified_at', 'notify_error', 'webhook_sent_at', 'webhook_error',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'webhook_sent_at' => 'datetime',
    ];

    public function getFormLabelAttribute(): string
    {
        return self::FORM_TYPES[$this->form_type] ?? $this->form_type;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}

<?php

namespace App\Support\AdminTable\Queries;

use App\Models\TrackConversion;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;

/**
 * Replaces TrackConversionDataTable. Read-only tracking log (no create/edit/delete
 * UI in the old view — TrackConversionController only exposes index()+store(),
 * store() is used by the public-facing tracking pixel, not this admin table),
 * so there is no "actions" column and no bulk-delete support.
 */
class TrackConversionTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return TrackConversion::query();
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'gclid',
                'label' => 'Google ID (gclid)',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'type',
                'label' => 'Tipo de Conversion',
                'searchable' => true,
            ],
            [
                'key' => 'utm_source',
                'label' => 'utm_source',
                'searchable' => true,
            ],
            [
                'key' => 'utm_medium',
                'label' => 'utm_medium',
                'searchable' => true,
            ],
            [
                'key' => 'utm_campaign',
                'label' => 'Campaña',
                'searchable' => true,
            ],
            [
                'key' => 'utm_term',
                'label' => 'utm_term',
                'searchable' => true,
            ],
            [
                'key' => 'utm_content',
                'label' => 'utm_content',
                'searchable' => true,
            ],
            [
                'key' => 'gbraid',
                'label' => 'gbraid',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'wbraid',
                'label' => 'wbraid',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'fbclid',
                'label' => 'fbclid',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'session_id',
                'label' => 'Sesion',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'referrer',
                'label' => 'Referrer',
                'searchable' => true,
            ],
            [
                'key' => 'landing_page',
                'label' => 'Url Conversion',
                'searchable' => true,
            ],
            [
                'key' => 'page_url',
                'label' => 'URL completa',
                'searchable' => true,
            ],
            [
                'key' => 'event_id',
                'label' => 'ID de evento',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'meta',
                'label' => 'Datos extra',
                'render' => function ($row) {
                    $meta = (array) $row->meta;
                    return $meta
                        ? implode(' · ', array_map(fn ($k, $v) => $k . '=' . $v, array_keys($meta), $meta))
                        : null;
                },
            ],
            [
                'key' => 'captured_at',
                'label' => 'Capturado en navegador',
                'type' => 'date',
                'sortable' => true,
            ],
            [
                'key' => 'created_at',
                'label' => 'Fecha Registrada',
                'type' => 'date',
                'sortable' => true,
            ],
        ];
    }

    /** Pestañas guardadas: visitas vs conversiones y origen del clic (Google / Meta / sin click id). */
    public function filters(): array
    {
        $hasGoogle = fn (Builder $q) => $q->where(function (Builder $w) {
            $w->whereNotNull('gclid')->where('gclid', '!=', '')
                ->orWhere(fn (Builder $b) => $b->whereNotNull('gbraid')->where('gbraid', '!=', ''))
                ->orWhere(fn (Builder $b) => $b->whereNotNull('wbraid')->where('wbraid', '!=', ''));
        });

        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'visitas', 'label' => 'Visitas', 'apply' => fn (Builder $q) => $q->where('type', TrackConversion::VISIT_TYPE)],
            ['key' => 'conversiones', 'label' => 'Conversiones', 'apply' => fn (Builder $q) => $q->whereNotIn('type', TrackConversion::nonConversionTypes())],
            ['key' => 'interacciones', 'label' => 'Carrito y cotización', 'apply' => fn (Builder $q) => $q->whereIn('type', TrackConversion::ENGAGEMENT_TYPES)],
            ['key' => 'empleos', 'label' => 'Empleos', 'apply' => fn (Builder $q) => $q->where('type', 'like', 'job\\_%')],
            ['key' => 'whatsapp', 'label' => 'WhatsApp', 'apply' => fn (Builder $q) => $q->where('type', 'like', 'whatsapp%')],
            ['key' => 'llamadas', 'label' => 'Llamadas', 'apply' => fn (Builder $q) => $q->where(fn (Builder $w) => $w->where('type', 'like', 'telefono%')->orWhere('type', 'phone_click'))],
            ['key' => 'cotizaciones', 'label' => 'Cotizaciones', 'apply' => fn (Builder $q) => $q->where('type', 'like', 'quote\\_%')],
            ['key' => 'google', 'label' => 'Google Ads (gclid/gbraid/wbraid)', 'apply' => $hasGoogle],
            ['key' => 'meta', 'label' => 'Meta (fbclid)', 'apply' => fn (Builder $q) => $q->whereNotNull('fbclid')->where('fbclid', '!=', '')],
            ['key' => 'sin_click_id', 'label' => 'Sin click id', 'apply' => fn (Builder $q) => $q
                ->where(fn (Builder $w) => $w->whereNull('gclid')->orWhere('gclid', ''))
                ->where(fn (Builder $w) => $w->whereNull('gbraid')->orWhere('gbraid', ''))
                ->where(fn (Builder $w) => $w->whereNull('wbraid')->orWhere('wbraid', ''))
                ->where(fn (Builder $w) => $w->whereNull('fbclid')->orWhere('fbclid', ''))],
        ];
    }
}

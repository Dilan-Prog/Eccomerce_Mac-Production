<?php

namespace App\Support\AdminTable\Queries;

use App\Models\WebLead;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Tabla de "Solicitudes web" (formularios publicos). Datos de visitantes anonimos: el motor de la
 * tabla escapa todo lo que pinta (AU.escapeHtml); aqui solo se arman valores de texto.
 *
 * El filtro por formulario llega como ?form_type=... y se aplica en baseQuery() para que tambien
 * afecte los contadores de las pestañas.
 */
class WebLeadTableQuery extends AdminTableQuery
{
    private const TZ_MX = 'America/Mexico_City';

    public function baseQuery(): Builder
    {
        $query = WebLead::query();

        $formType = request()->input('form_type');
        if (is_string($formType) && array_key_exists($formType, WebLead::FORM_TYPES)) {
            $query->where('form_type', $formType);
        }

        return $query;
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'created_at',
                'label' => 'Fecha',
                'sortable' => true,
                'render' => fn (Model $row) => $row->created_at
                    ? $row->created_at->copy()->timezone(self::TZ_MX)->format('d/m/Y H:i')
                    : '',
            ],
            [
                'key' => 'form_type',
                'label' => 'Formulario',
                'type' => 'badge',
                'render' => fn (Model $row) => ['label' => $row->form_label, 'tone' => 'info'],
            ],
            [
                'key' => 'first_name',
                'label' => 'Nombre',
                'searchable' => true,
                'render' => fn (Model $row) => [
                    'value' => $row->full_name,
                    'subtext' => $row->company ?: null,
                ],
            ],
            [
                'key' => 'email',
                'label' => 'Correo',
                'searchable' => true,
            ],
            [
                'key' => 'phone',
                'label' => 'Teléfono',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'city',
                'label' => 'Ciudad / Estado',
                'render' => fn (Model $row) => implode(', ', array_filter([$row->city, $row->state])) ?: '—',
            ],
            [
                'key' => 'utm_source',
                'label' => 'Origen',
                'render' => function (Model $row) {
                    $hasGclid = filled($row->gclid) || filled($row->gbraid) || filled($row->wbraid);

                    return [
                        'value' => $row->utm_source ?: '—',
                        'subtext' => 'Click de Google: ' . ($hasGclid ? 'sí' : 'no'),
                    ];
                },
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => [
                    'label' => WebLead::STATUSES[$row->status] ?? (string) $row->status,
                    'tone' => match ($row->status) {
                        'nuevo' => 'warning',
                        'contactado' => 'success',
                        default => 'info',
                    },
                ],
            ],
            [
                'key' => 'notified_at',
                'label' => 'Aviso',
                'type' => 'badge',
                'render' => function (Model $row) {
                    if (filled($row->notify_error)) {
                        return ['label' => 'Error de correo', 'tone' => 'critical'];
                    }
                    if (filled($row->webhook_error)) {
                        return ['label' => 'Error de webhook', 'tone' => 'critical'];
                    }

                    return $row->notified_at
                        ? ['label' => 'Correo enviado', 'tone' => 'success']
                        : ['label' => 'Pendiente', 'tone' => 'warning'];
                },
            ],
            [
                'key' => 'action',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    /** Nombre / apellido / correo / telefono / empresa / mensaje (y "nombre apellido" en un solo texto). */
    public function search(Builder $query, string $term): Builder
    {
        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            foreach (['first_name', 'last_name', 'email', 'phone', 'company', 'message'] as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }

            // "Juan Perez": nombre en first_name y el resto en last_name.
            $parts = preg_split('/\s+/', $term, 2);
            if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
                $q->orWhere(fn (Builder $w) => $w
                    ->where('first_name', 'like', "%{$parts[0]}%")
                    ->where('last_name', 'like', "%{$parts[1]}%"));
            }
        });
    }

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'nuevas', 'label' => 'Nuevas', 'apply' => fn (Builder $q) => $q->where('status', 'nuevo')],
            ['key' => 'contactadas', 'label' => 'Contactadas', 'apply' => fn (Builder $q) => $q->where('status', 'contactado')],
            ['key' => 'descartadas', 'label' => 'Descartadas', 'apply' => fn (Builder $q) => $q->where('status', 'descartado')],
        ];
    }

    public function rowActions(Model $row): array
    {
        $actions = [];
        $statusUrl = fn (string $status) => route('admin.web-leads.update-status', $row->id) . '?status=' . $status;

        // Primer elemento = boton principal de la fila.
        if ($row->status !== 'contactado') {
            $actions[] = ['label' => 'Marcar contactada', 'url' => $statusUrl('contactado'), 'method' => 'PATCH'];
        }
        if ($row->status !== 'descartado') {
            $actions[] = ['label' => 'Descartar', 'url' => $statusUrl('descartado'), 'method' => 'PATCH'];
        }
        if ($row->status !== 'nuevo') {
            $actions[] = ['label' => 'Marcar nueva', 'url' => $statusUrl('nuevo'), 'method' => 'PATCH'];
        }

        $actions[] = [
            'label' => 'Reenviar aviso',
            'url' => route('admin.web-leads.resend', $row->id),
            'method' => 'POST',
        ];
        $actions[] = [
            'label' => 'Borrar',
            'url' => route('admin.web-leads.destroy', $row->id),
            'method' => 'DELETE',
            'tone' => 'critical',
            'confirm' => true,
            'summary' => [
                ['label' => 'Nombre', 'value' => $row->full_name],
                ['label' => 'Formulario', 'value' => $row->form_label],
            ],
        ];

        return $actions;
    }
}

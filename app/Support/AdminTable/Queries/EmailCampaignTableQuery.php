<?php

namespace App\Support\AdminTable\Queries;

use App\Models\EmailCampaign;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Pestaña "Campañas" de Email Marketing. Estados y ciclo de vida
 * documentados en la migración create_email_campaigns_table.
 */
class EmailCampaignTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return EmailCampaign::query()->with(['template:id,name', 'list:id,name']);
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'mono',
                'sortable' => true,
                'width' => 80,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'template',
                'label' => 'Plantilla',
                'render' => fn (Model $row) => $row->template->name ?? '—',
            ],
            [
                'key' => 'list',
                'label' => 'Lista',
                'render' => fn (Model $row) => $row->list->name ?? '—',
            ],
            [
                'key' => 'scheduled_at',
                'label' => 'Programada para',
                'type' => 'date',
                'sortable' => true,
                'render' => fn (Model $row) => $row->scheduled_at
                    ? $row->scheduled_at->format('d/m/Y H:i')
                    : 'En cuanto n8n pase',
            ],
            [
                'key' => 'progress',
                'label' => 'Progreso',
                'render' => fn (Model $row) => $row->total_recipients > 0
                    ? $row->sent_count . ' / ' . $row->total_recipients . ($row->failed_count > 0 ? ' (' . $row->failed_count . ' con error)' : '')
                    : '—',
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => self::statusBadge($row->status),
            ],
            [
                'key' => 'action',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    /** @return array{label: string, tone: string} */
    public static function statusBadge(?string $status): array
    {
        return match ($status) {
            'programada' => ['label' => 'Programada', 'tone' => 'warning'],
            'enviando' => ['label' => 'Enviando', 'tone' => 'warning'],
            'enviada' => ['label' => 'Enviada', 'tone' => 'success'],
            'cancelada' => ['label' => 'Cancelada', 'tone' => 'critical'],
            default => ['label' => 'Borrador', 'tone' => 'info'],
        };
    }

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'borradores', 'label' => 'Borrador', 'apply' => fn (Builder $q) => $q->where('status', 'borrador')],
            ['key' => 'programadas', 'label' => 'Programada', 'apply' => fn (Builder $q) => $q->where('status', 'programada')],
            ['key' => 'enviando', 'label' => 'Enviando', 'apply' => fn (Builder $q) => $q->where('status', 'enviando')],
            ['key' => 'enviadas', 'label' => 'Enviada', 'apply' => fn (Builder $q) => $q->where('status', 'enviada')],
            ['key' => 'canceladas', 'label' => 'Cancelada', 'apply' => fn (Builder $q) => $q->where('status', 'cancelada')],
        ];
    }

    public function rowActions(Model $row): array
    {
        $user = auth()->user();
        $canEdit = $user?->canPerform('marketing-integracion', 'edit') ?? false;
        $canDelete = $user?->canPerform('marketing-integracion', 'delete') ?? false;

        $actions = [
            [
                'label' => 'Ver envío',
                'url' => route('admin.email-campaigns.show', $row->id),
            ],
        ];

        // Una campaña solo se edita mientras es borrador: a partir de
        // "programada" ya existe el snapshot de destinatarios y cambiar la
        // plantilla o la lista dejaría el envío en curso inconsistente.
        if ($canEdit && $row->status === 'borrador') {
            $actions[] = [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar campaña',
                    'subtitle' => $row->name,
                    'icon' => 'fas fa-paper-plane',
                    'fragmentUrl' => route('admin.email-campaigns.edit-fragment', $row->id),
                    'submitUrl' => route('admin.email-campaigns.update', $row->id),
                    'method' => 'PUT',
                ],
            ];
            $actions[] = [
                'label' => 'Programar',
                'url' => route('admin.email-campaigns.schedule', $row->id),
                'method' => 'POST',
            ];
        }

        // Cancelar solo tiene sentido antes de que n8n la reclame: una vez
        // reclamada ya puede haber correos saliendo y "cancelar" daría una
        // falsa sensación de que se detuvo algo (ver
        // EmailCampaignController::cancel, que además lo vuelve a validar).
        if ($canEdit && $row->status === 'programada' && $row->claimed_at === null) {
            $actions[] = [
                'label' => 'Cancelar envío',
                'url' => route('admin.email-campaigns.cancel', $row->id),
                'method' => 'POST',
            ];
        }

        if ($canDelete && in_array($row->status, ['borrador', 'cancelada'], true)) {
            $actions[] = [
                'label' => 'Borrar',
                'url' => route('admin.email-campaigns.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Estado', 'value' => self::statusBadge($row->status)['label']],
                ],
            ];
        }

        return $actions;
    }
}

<?php

namespace App\Support\AdminTable\Queries;

use App\Models\EmailCampaignRecipient;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Destinatarios de UNA campaña — el monitor de
 * resources/views/admin-ui/email-campaigns/show.blade.php.
 *
 * Acotada por forCampaign(), mismo criterio que
 * EmailContactListMemberTableQuery: sin id no devuelve nada.
 *
 * Sin rowActions a propósito: esto es un registro de lo que pasó, no algo
 * que el admin edite. Reintentar un envío fallido es decisión de n8n, no un
 * botón del panel.
 */
class EmailCampaignRecipientTableQuery extends AdminTableQuery
{
    private ?int $campaignId = null;

    public function forCampaign(int $campaignId): static
    {
        $this->campaignId = $campaignId;

        return $this;
    }

    public function baseQuery(): Builder
    {
        return EmailCampaignRecipient::query()
            ->where('email_campaign_id', $this->campaignId ?? 0);
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'email',
                'label' => 'Correo',
                'type' => 'mono',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre',
                'searchable' => true,
                'render' => fn (Model $row) => $row->name ?: '—',
            ],
            [
                'key' => 'company',
                'label' => 'Empresa',
                'searchable' => true,
                'render' => fn (Model $row) => $row->company ?: '—',
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => match ($row->status) {
                    'sent' => ['label' => 'Enviado', 'tone' => 'success'],
                    'failed' => ['label' => 'Error', 'tone' => 'critical'],
                    default => ['label' => 'Pendiente', 'tone' => 'warning'],
                },
            ],
            [
                'key' => 'sent_at',
                'label' => 'Enviado',
                'type' => 'date',
                'sortable' => true,
                'render' => fn (Model $row) => $row->sent_at ? $row->sent_at->format('d/m/Y H:i') : '—',
            ],
            [
                'key' => 'attempts',
                'label' => 'Intentos',
                'render' => fn (Model $row) => (string) $row->attempts,
            ],
            [
                'key' => 'error_message',
                'label' => 'Último error',
                'render' => fn (Model $row) => $row->error_message ?: '—',
            ],
        ];
    }

    public function filters(): array
    {
        return [
            ['key' => 'todos', 'label' => 'Todos', 'apply' => fn (Builder $q) => $q],
            ['key' => 'pendientes', 'label' => 'Pendiente', 'apply' => fn (Builder $q) => $q->where('status', 'pending')],
            ['key' => 'enviados', 'label' => 'Enviado', 'apply' => fn (Builder $q) => $q->where('status', 'sent')],
            ['key' => 'fallidos', 'label' => 'Error', 'apply' => fn (Builder $q) => $q->where('status', 'failed')],
        ];
    }
}

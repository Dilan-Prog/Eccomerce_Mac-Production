<?php

namespace App\Support\AdminTable\Queries;

use App\Models\EmailContactList;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Pestaña "Listas" de Email Marketing (ver
 * resources/views/admin-ui/email-marketing/index.blade.php).
 */
class EmailContactListTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return EmailContactList::query()->withCount([
            // Los dados de baja no cuentan como contactos activos — es el
            // mismo criterio con el que se arma el snapshot de una campaña
            // (ver EmailCampaignController::schedule), así el número de la
            // lista y el de destinatarios de la campaña coinciden.
            'members as members_count' => fn (Builder $q) => $q->whereNull('unsubscribed_at'),
            'members as unsubscribed_count' => fn (Builder $q) => $q->whereNotNull('unsubscribed_at'),
        ]);
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
                'key' => 'description',
                'label' => 'Descripción',
                'searchable' => true,
                'render' => fn (Model $row) => $row->description ?: '—',
            ],
            [
                'key' => 'members_count',
                'label' => 'Contactos',
                'render' => fn (Model $row) => (string) $row->members_count,
            ],
            [
                'key' => 'unsubscribed_count',
                'label' => 'Dados de baja',
                'render' => fn (Model $row) => (string) $row->unsubscribed_count,
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => $row->status
                    ? ['label' => 'Activa', 'tone' => 'success']
                    : ['label' => 'Inactiva', 'tone' => 'critical'],
            ],
            [
                'key' => 'action',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activas', 'label' => 'Activa', 'apply' => fn (Builder $q) => $q->where('status', 1)],
            ['key' => 'inactivas', 'label' => 'Inactiva', 'apply' => fn (Builder $q) => $q->where('status', 0)],
        ];
    }

    /**
     * Un rol con solo permiso de "Ver" no debe ver siquiera los botones de
     * acción — el middleware can-access-module ya bloquea las rutas, esto
     * evita ofrecer algo que terminaría en un error de permisos.
     */
    public function rowActions(Model $row): array
    {
        $user = auth()->user();
        $canEdit = $user?->canPerform('marketing-integracion', 'edit') ?? false;
        $canDelete = $user?->canPerform('marketing-integracion', 'delete') ?? false;

        $actions = [
            [
                'label' => 'Ver contactos',
                'url' => route('admin.email-lists.show', $row->id),
            ],
        ];

        if ($canEdit) {
            $actions[] = [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar lista',
                    'subtitle' => $row->name,
                    'icon' => 'fas fa-address-book',
                    'fragmentUrl' => route('admin.email-lists.edit-fragment', $row->id),
                    'submitUrl' => route('admin.email-lists.update', $row->id),
                    'method' => 'PUT',
                ],
            ];
        }

        if ($canDelete) {
            $actions[] = [
                'label' => 'Borrar',
                'url' => route('admin.email-lists.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Contactos', 'value' => (string) $row->members_count],
                ],
            ];
        }

        return $actions;
    }
}

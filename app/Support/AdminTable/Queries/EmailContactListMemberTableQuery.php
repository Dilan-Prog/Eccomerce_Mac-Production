<?php

namespace App\Support\AdminTable\Queries;

use App\Models\EmailContactListMember;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Contactos de UNA lista — alimenta la tabla de
 * resources/views/admin-ui/email-lists/show.blade.php.
 *
 * A diferencia del resto de las tablas del admin, esta siempre está acotada
 * a una lista: el controlador la resuelve del contenedor y le fija el id con
 * forList() antes de paginar. Sin ese id la consulta no devuelve nada (en
 * vez de devolver los contactos de todas las listas), así una ruta mal
 * armada nunca filtra datos de otra lista.
 */
class EmailContactListMemberTableQuery extends AdminTableQuery
{
    private ?int $listId = null;

    public function forList(int $listId): static
    {
        $this->listId = $listId;

        return $this;
    }

    public function baseQuery(): Builder
    {
        return EmailContactListMember::query()
            ->where('email_contact_list_id', $this->listId ?? 0);
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
                'sortable' => true,
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
                'key' => 'source',
                'label' => 'Origen',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => match ($row->source) {
                    'user' => ['label' => 'Cliente del sitio', 'tone' => 'info'],
                    'aspel_client' => ['label' => 'Cliente Aspel', 'tone' => 'warning'],
                    default => ['label' => 'Manual', 'tone' => 'info'],
                },
            ],
            [
                'key' => 'unsubscribed_at',
                'label' => 'Suscripción',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => $row->unsubscribed_at
                    ? ['label' => 'Dado de baja ' . $row->unsubscribed_at->format('d/m/Y'), 'tone' => 'critical']
                    : ['label' => 'Suscrito', 'tone' => 'success'],
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
            ['key' => 'todos', 'label' => 'Todos', 'apply' => fn (Builder $q) => $q],
            ['key' => 'suscritos', 'label' => 'Suscrito', 'apply' => fn (Builder $q) => $q->whereNull('unsubscribed_at')],
            ['key' => 'bajas', 'label' => 'Dado de baja', 'apply' => fn (Builder $q) => $q->whereNotNull('unsubscribed_at')],
            ['key' => 'sitio', 'label' => 'Cliente del sitio', 'apply' => fn (Builder $q) => $q->where('source', 'user')],
            ['key' => 'aspel', 'label' => 'Cliente Aspel', 'apply' => fn (Builder $q) => $q->where('source', 'aspel_client')],
            ['key' => 'manual', 'label' => 'Manual', 'apply' => fn (Builder $q) => $q->where('source', 'manual')],
        ];
    }

    public function rowActions(Model $row): array
    {
        // Quitar un contacto cuenta como acción de borrado, igual que en el
        // resto del módulo — un rol de solo lectura no ve el botón.
        if (!(auth()->user()?->canPerform('marketing-integracion', 'delete') ?? false)) {
            return [];
        }

        return [
            [
                'label' => 'Quitar',
                'url' => route('admin.email-lists.members.destroy', [$row->email_contact_list_id, $row->id]),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Correo', 'value' => $row->email],
                    ['label' => 'Nombre', 'value' => $row->name ?: '—'],
                ],
            ],
        ];
    }
}

<?php

namespace App\Support\AdminTable\Queries;

use App\Models\EmailSequence;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Pestaña "Secuencias" de Email Marketing.
 */
class EmailSequenceTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return EmailSequence::query()->withCount([
            'steps',
            'enrollments as active_enrollments_count' => fn (Builder $q) => $q->where('status', 'active'),
            'enrollments as exited_enrollments_count' => fn (Builder $q) => $q->where('status', 'exited_purchase'),
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
                'key' => 'steps_count',
                'label' => 'Pasos',
                'render' => fn (Model $row) => (string) $row->steps_count,
            ],
            [
                'key' => 'active_enrollments_count',
                'label' => 'En seguimiento',
                'render' => fn (Model $row) => (string) $row->active_enrollments_count,
            ],
            [
                'key' => 'exited_enrollments_count',
                'label' => 'Salieron por compra',
                'render' => fn (Model $row) => (string) $row->exited_enrollments_count,
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => $row->status
                    ? ['label' => 'Activa', 'tone' => 'success']
                    : ['label' => 'Pausada', 'tone' => 'critical'],
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
            ['key' => 'pausadas', 'label' => 'Pausada', 'apply' => fn (Builder $q) => $q->where('status', 0)],
        ];
    }

    public function rowActions(Model $row): array
    {
        $user = auth()->user();

        $actions = [
            [
                'label' => 'Ver seguimiento',
                'url' => route('admin.email-sequences.show', $row->id),
            ],
        ];

        if ($user?->canPerform('marketing-integracion', 'edit') ?? false) {
            $actions[] = [
                'label' => 'Editar',
                'url' => route('admin.email-sequences.edit', $row->id),
            ];
        }

        if ($user?->canPerform('marketing-integracion', 'delete') ?? false) {
            $actions[] = [
                'label' => 'Borrar',
                'url' => route('admin.email-sequences.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Pasos', 'value' => (string) $row->steps_count],
                    ['label' => 'En seguimiento', 'value' => (string) $row->active_enrollments_count],
                ],
            ];
        }

        return $actions;
    }
}

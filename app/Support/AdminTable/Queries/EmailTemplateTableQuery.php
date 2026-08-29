<?php

namespace App\Support\AdminTable\Queries;

use App\Models\EmailTemplate;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return EmailTemplate::query()->with('category:id,name');
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
                'key' => 'type',
                'label' => 'Tipo',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => self::typeBadge($row->type),
            ],
            [
                'key' => 'subject',
                'label' => 'Asunto',
                'searchable' => true,
            ],
            [
                'key' => 'category',
                'label' => 'Categoría',
                'render' => fn (Model $row) => $row->category->name ?? 'General / todas',
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => $row->status
                    ? ['label' => 'Activo', 'tone' => 'success']
                    : ['label' => 'Inactivo', 'tone' => 'critical'],
            ],
            [
                'key' => 'action',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    /**
     * Etiqueta legible del tipo de plantilla (columna `type`, ver la
     * migración add_type_to_email_templates_table). Es solo organización:
     * ningún endpoint filtra por este campo, así que una plantilla marcada
     * como "Campaña" sigue sirviendo perfectamente para cualquier otro flujo.
     *
     * @return array{label: string, tone: string}
     */
    public static function typeBadge(?string $type): array
    {
        return match ($type) {
            'campaign' => ['label' => 'Campaña', 'tone' => 'warning'],
            'sequence' => ['label' => 'Secuencia', 'tone' => 'info'],
            default => ['label' => 'Individual', 'tone' => 'info'],
        };
    }

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'individuales', 'label' => 'Individual', 'apply' => fn (Builder $q) => $q->where('type', 'individual')],
            ['key' => 'campanas', 'label' => 'Campaña', 'apply' => fn (Builder $q) => $q->where('type', 'campaign')],
            ['key' => 'secuencias', 'label' => 'Secuencia', 'apply' => fn (Builder $q) => $q->where('type', 'sequence')],
            ['key' => 'activas', 'label' => 'Activa', 'apply' => fn (Builder $q) => $q->where('status', 1)],
            ['key' => 'inactivas', 'label' => 'Inactiva', 'apply' => fn (Builder $q) => $q->where('status', 0)],
        ];
    }

    public function rowActions(Model $row): array
    {
        // Mismo criterio que el resto del módulo de Email Marketing: un rol
        // con solo permiso de "Ver" no ve los botones de acción.
        $user = auth()->user();
        $actions = [];

        if ($user?->canPerform('marketing-integracion', 'edit') ?? false) {
            $actions[] = [
                'label' => 'Editar',
                'url' => route('admin.email-templates.edit', $row->id),
            ];
        }

        if ($user?->canPerform('marketing-integracion', 'delete') ?? false) {
            $actions[] = [
                'label' => 'Borrar',
                'url' => route('admin.email-templates.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Categoría', 'value' => $row->category->name ?? 'General / todas'],
                ],
            ];
        }

        return $actions;
    }
}

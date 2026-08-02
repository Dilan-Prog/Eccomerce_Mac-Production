<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Cotizacion;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Replaces the plain (non-Yajra) paginated listing previously built by hand
 * in AdminCotizacionController::index() + resources/views/admin/cotizaciones/index.blade.php.
 */
class CotizacionTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return Cotizacion::query()->with(['user', 'perfil']);
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'folio',
                'label' => 'Folio',
                'type' => 'mono',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'user_name',
                'label' => 'Cliente',
                'type' => 'person',
                'render' => fn (Model $row) => [
                    'title' => $row->user->name ?? '—',
                    'subtitle' => $row->user->email ?? '',
                ],
            ],
            [
                'key' => 'tipo_persona',
                'label' => 'Tipo',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->perfil
                    ? ($row->perfil->tipo_persona === 'empresa'
                        ? ['label' => 'Empresa', 'tone' => 'info']
                        : ['label' => 'Física', 'tone' => 'success'])
                    : ['label' => '—', 'tone' => 'info'],
            ],
            [
                'key' => 'rfc',
                'label' => 'RFC',
                'type' => 'mono',
                'render' => fn (Model $row) => $row->perfil->rfc ?? '—',
            ],
            [
                'key' => 'created_at',
                'label' => 'Fecha',
                'type' => 'date',
                'sortable' => true,
            ],
            [
                'key' => 'total',
                'label' => 'Total',
                'type' => 'currency',
                'sortable' => true,
                'render' => fn (Model $row) => (float) $row->total,
            ],
            [
                'key' => 'actions',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    /**
     * Mirrors the old controller's search: folio, user name/email, perfil RFC.
     */
    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('folio', 'like', "%{$term}%")
                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"))
                ->orWhereHas('perfil', fn (Builder $p) => $p->where('rfc', 'like', "%{$term}%"));
        });
    }

    public function rowActions(Model $row): array
    {
        $actions = [
            [
                'label' => 'Detalles',
                'url' => route('admin.cotizaciones.show', $row->id),
            ],
        ];

        if ($row->pdf_path) {
            $actions[] = [
                'label' => 'Ver PDF',
                'url' => Storage::url($row->pdf_path),
            ];
        }

        return $actions;
    }
}

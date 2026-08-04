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
                'key' => 'source',
                'label' => 'Origen',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->source === 'cliente'
                    ? ['label' => 'Cliente', 'tone' => 'info']
                    : ['label' => 'Admin', 'tone' => 'warning'],
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'render' => fn (Model $row) => match ($row->status) {
                    'generada' => ['label' => 'Generada', 'tone' => 'info'],
                    'borrador' => ['label' => 'Borrador', 'tone' => 'warning'],
                    'finalizada' => ['label' => 'Finalizada', 'tone' => 'success'],
                    default => ['label' => ucfirst($row->status), 'tone' => 'info'],
                },
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
     * "Todas" is kept as the default tab (no filtering) to preserve the
     * listing's previous behavior — it had no tabs at all before, i.e.
     * everything was shown. "Mis borradores" is the one new tab: admin-
     * authored drafts still awaiting finalize.
     */
    public function filters(): array
    {
        return [
            [
                'key' => 'todas',
                'label' => 'Todas',
                'apply' => fn (Builder $q) => $q,
            ],
            [
                'key' => 'mis-borradores',
                'label' => 'Mis borradores',
                'apply' => fn (Builder $q) => $q->where('source', 'admin')->where('status', 'borrador'),
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

        // Only an admin-authored draft can still be edited here — a
        // customer-originated row (source = 'cliente') or an already-
        // finalized quote must never get this action.
        if ($row->source === 'admin' && $row->status === 'borrador') {
            $actions[] = [
                'label' => 'Continuar edición',
                'url' => route('admin.cotizaciones.edit', $row->id),
            ];
        }

        if ($row->pdf_path) {
            // Cache-busting query param (the file's own mtime) so a browser
            // that already cached this exact PDF URL from before a
            // regeneratePdf() call is forced to re-fetch instead of showing
            // a stale copy — Cache-Control headers alone can't invalidate
            // what a browser already has cached from a prior response.
            $version = Storage::disk('public')->exists($row->pdf_path)
                ? Storage::disk('public')->lastModified($row->pdf_path)
                : time();

            $actions[] = [
                'label' => 'Ver PDF',
                'url' => route('admin.cotizaciones.pdf', $row->id) . '?v=' . $version,
            ];
        }

        return $actions;
    }
}

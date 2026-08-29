<?php

namespace App\Support\AdminTable\Queries;

use App\Models\EmailSequenceEnrollment;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Inscripciones de UNA secuencia — el monitor de
 * resources/views/admin-ui/email-sequences/show.blade.php.
 *
 * Acotada por forSequence(), mismo criterio que las otras tablas hijas: sin
 * id no devuelve nada.
 *
 * Sin rowActions: el ciclo de vida de una inscripción lo maneja
 * App\Support\SequenceProcessor (inscribir/sacar/vencer/cerrar), no el
 * admin — esta pantalla solo mira.
 */
class EmailSequenceEnrollmentTableQuery extends AdminTableQuery
{
    private ?int $sequenceId = null;

    public function forSequence(int $sequenceId): static
    {
        $this->sequenceId = $sequenceId;

        return $this;
    }

    public function baseQuery(): Builder
    {
        return EmailSequenceEnrollment::query()
            ->where('email_sequence_id', $this->sequenceId ?? 0)
            ->with(['cotizacion:id,folio,total,currency', 'user:id,name,last_name,email'])
            ->withCount([
                'stepSends as sent_steps_count' => fn (Builder $q) => $q->where('status', 'sent'),
                'stepSends as pending_steps_count' => fn (Builder $q) => $q->whereIn('status', ['pending', 'due']),
                'stepSends as failed_steps_count' => fn (Builder $q) => $q->where('status', 'failed'),
            ]);
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'cotizacion',
                'label' => 'Cotización',
                'type' => 'mono',
                'render' => fn (Model $row) => $row->cotizacion->folio ?? ('#' . $row->cotizacion_id),
            ],
            [
                'key' => 'user',
                'label' => 'Cliente',
                'type' => 'person',
                'render' => fn (Model $row) => [
                    'title' => trim(($row->user->name ?? '') . ' ' . ($row->user->last_name ?? '')) ?: 'Cliente #' . $row->user_id,
                    'subtitle' => $row->user->email ?? '',
                ],
            ],
            [
                'key' => 'enrolled_at',
                'label' => 'Inscrita',
                'type' => 'date',
                'sortable' => true,
                'render' => fn (Model $row) => $row->enrolled_at ? $row->enrolled_at->format('d/m/Y H:i') : '—',
            ],
            [
                'key' => 'progress',
                'label' => 'Pasos',
                'render' => fn (Model $row) => $row->sent_steps_count . ' enviados · '
                    . $row->pending_steps_count . ' por enviar'
                    . ($row->failed_steps_count > 0 ? ' · ' . $row->failed_steps_count . ' con error' : ''),
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => match ($row->status) {
                    'completed' => ['label' => 'Completada', 'tone' => 'info'],
                    'exited_purchase' => ['label' => 'Salió por compra', 'tone' => 'success'],
                    default => ['label' => 'En seguimiento', 'tone' => 'warning'],
                },
            ],
            [
                'key' => 'exited_at',
                'label' => 'Cerrada',
                'type' => 'date',
                'sortable' => true,
                'render' => fn (Model $row) => $row->exited_at ? $row->exited_at->format('d/m/Y H:i') : '—',
            ],
        ];
    }

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activas', 'label' => 'En seguimiento', 'apply' => fn (Builder $q) => $q->where('status', 'active')],
            ['key' => 'completadas', 'label' => 'Completada', 'apply' => fn (Builder $q) => $q->where('status', 'completed')],
            ['key' => 'compraron', 'label' => 'Salió por compra', 'apply' => fn (Builder $q) => $q->where('status', 'exited_purchase')],
        ];
    }

    /**
     * La búsqueda por texto es sobre el folio de la cotización y el
     * nombre/correo del cliente — ninguna de las dos columnas vive en esta
     * tabla, así que el search() por defecto (que solo mira columnas
     * `searchable` propias) no sirve aquí.
     */
    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->whereHas('cotizacion', fn (Builder $c) => $c->where('folio', 'like', "%{$term}%"))
                ->orWhereHas('user', function (Builder $u) use ($term) {
                    $u->where('name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
        });
    }
}

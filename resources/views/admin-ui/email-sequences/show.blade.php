@extends('admin-ui.layouts.master')

@php
    $auCanEdit = auth()->user()?->canPerform('marketing-integracion', 'edit') ?? false;
    $auActions = ($auCanEdit ? '<a href="' . route('admin.email-sequences.edit', $emailSequence->id) . '" class="au-btn au-btn-primary">Editar pasos</a> ' : '')
        . '<a href="' . route('admin.email-marketing.index', ['tab' => 'secuencias']) . '" class="au-btn"><i class="fas fa-arrow-left"></i> Volver</a>';
@endphp

@section('title', 'Secuencia — ' . $emailSequence->name)

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => $emailSequence->name,
        'subtitle' => $emailSequence->description ?: 'Seguimiento automático de cotizaciones',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
            ['label' => 'Email Marketing', 'url' => route('admin.email-marketing.index', ['tab' => 'secuencias'])],
            ['label' => $emailSequence->name],
        ],
        'actions' => $auActions,
    ])

    <div class="au-card" style="margin-bottom:16px">
        <div class="au-card-header">
            <div class="au-card-title">
                Pasos configurados
                <span class="au-badge au-badge-{{ $emailSequence->status ? 'success' : 'critical' }}" style="margin-left:8px">
                    <span class="au-badge-dot"></span>{{ $emailSequence->status ? 'Activa' : 'Pausada' }}
                </span>
            </div>
        </div>
        <div class="au-card-body">
            @if ($emailSequence->steps->isEmpty())
                <div class="au-table-empty">Esta secuencia todavía no tiene pasos, así que no inscribe cotizaciones.</div>
            @else
                <div class="au-table-wrap">
                    <table class="au-table">
                        <thead>
                            <tr>
                                <th>Paso</th>
                                <th>Nombre</th>
                                <th>Plantilla</th>
                                <th>Se envía</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($emailSequence->steps as $step)
                                <tr>
                                    <td class="au-mono">{{ $step->step_order }}</td>
                                    <td>{{ $step->name ?: '—' }}</td>
                                    <td>{{ $step->template->name ?? '—' }}</td>
                                    <td>{{ (int) $step->wait_days === 0 ? 'De inmediato al inscribir' : $step->wait_days . ' día(s) después de la cotización' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="au-help-text" style="display:block;margin:-4px 0 16px;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
        Abajo, una fila por cotización en seguimiento. Las inscripciones nuevas, las salidas por compra y el vencimiento de los pasos se calculan cuando n8n pregunta por trabajo pendiente (<code>GET /api/marketing/sequences/due</code>) — si acabas de crear una cotización, aparecerá aquí después de la siguiente pasada de n8n.
    </div>

    <div id="ese-enrollments-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#ese-enrollments-table',
            endpoint: '{{ route('admin.email-sequences.enrollments.table-data', $emailSequence->id) }}',
            rowSelectable: false,
        });
    </script>
@endpush

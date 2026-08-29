@extends('admin-ui.layouts.master')

@php
    $statusBadge = \App\Support\AdminTable\Queries\EmailCampaignTableQuery::statusBadge($emailCampaign->status);
    $pending = max(0, $emailCampaign->total_recipients - $emailCampaign->sent_count - $emailCampaign->failed_count);
@endphp

@section('title', 'Campaña — ' . $emailCampaign->name)

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => $emailCampaign->name,
        'subtitle' => 'Plantilla: ' . ($emailCampaign->template->name ?? '—') . ' · Lista: ' . ($emailCampaign->list->name ?? '—'),
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
            ['label' => 'Email Marketing', 'url' => route('admin.email-marketing.index', ['tab' => 'campanas'])],
            ['label' => $emailCampaign->name],
        ],
        'actions' => '<a href="' . route('admin.email-marketing.index', ['tab' => 'campanas']) . '" class="au-btn"><i class="fas fa-arrow-left"></i> Volver</a>',
    ])

    <div class="au-card" style="margin-bottom:16px">
        <div class="au-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px">
                <div>
                    <div class="au-help-text">Estado</div>
                    <div><span class="au-badge au-badge-{{ $statusBadge['tone'] }}"><span class="au-badge-dot"></span>{{ $statusBadge['label'] }}</span></div>
                </div>
                <div>
                    <div class="au-help-text">Destinatarios</div>
                    <div style="font-size:20px;font-weight:600">{{ $emailCampaign->total_recipients }}</div>
                </div>
                <div>
                    <div class="au-help-text">Enviados</div>
                    <div style="font-size:20px;font-weight:600">{{ $emailCampaign->sent_count }}</div>
                </div>
                <div>
                    <div class="au-help-text">Con error</div>
                    <div style="font-size:20px;font-weight:600">{{ $emailCampaign->failed_count }}</div>
                </div>
                <div>
                    <div class="au-help-text">Pendientes</div>
                    <div style="font-size:20px;font-weight:600">{{ $pending }}</div>
                </div>
                <div>
                    <div class="au-help-text">Programada para</div>
                    <div>{{ $emailCampaign->scheduled_at ? $emailCampaign->scheduled_at->format('d/m/Y H:i') : 'En cuanto n8n pase' }}</div>
                </div>
                <div>
                    <div class="au-help-text">Tomada por n8n</div>
                    <div>{{ $emailCampaign->claimed_at ? $emailCampaign->claimed_at->format('d/m/Y H:i') : 'Todavía no' }}</div>
                </div>
                <div>
                    <div class="au-help-text">Terminada</div>
                    <div>{{ $emailCampaign->completed_at ? $emailCampaign->completed_at->format('d/m/Y H:i') : '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="au-help-text" style="display:block;margin:-4px 0 16px;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
        Este es el registro de lo que pasó con cada destinatario. Los envíos los hace n8n contra <code>/api/marketing/campaigns/{{ $emailCampaign->id }}/...</code>; los contadores de arriba se recalculan con cada resultado que reporta. Reintentar un envío fallido es decisión de n8n — por eso no hay un botón de reintento aquí.
    </div>

    <div id="ecr-recipients-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#ecr-recipients-table',
            endpoint: '{{ route('admin.email-campaigns.recipients.table-data', $emailCampaign->id) }}',
            rowSelectable: false,
        });
    </script>
@endpush

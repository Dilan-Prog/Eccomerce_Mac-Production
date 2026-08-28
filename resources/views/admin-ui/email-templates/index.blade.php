@extends('admin-ui.layouts.master')

@section('title', 'Plantillas de correo')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => 'Plantillas de correo',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
            ['label' => 'Plantillas de correo'],
        ],
        'actions' => '<a href="' . route('admin.email-templates.create') . '" class="au-btn au-btn-primary">+ Crear Nueva</a>',
    ])

    <div class="au-help-text" style="display:block;margin:-4px 0 16px;padding:10px 12px;background:var(--au-neutral-bg, #F5F7FA);border-radius:var(--au-radius-sm, 8px)">
        Estas plantillas arman el correo de ofertas personalizadas que consume <code>GET /api/marketing/email/{userId}</code> (flujo de n8n). Se elige primero la plantilla activa de la categoría dominante del cliente; si no hay una, se usa la plantilla "General / todas" (sin categoría); si tampoco existe ninguna, el sistema usa un diseño de respaldo fijo para nunca quedarse sin enviar el correo.
    </div>

    <div id="email-templates-table"></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-ui/js/table/column-types.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/bulk-actions.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/column-visibility.js') }}"></script>
    <script src="{{ asset('admin-ui/js/table/admin-table.js') }}"></script>
    <script>
        new AU.AdminTable({
            el: '#email-templates-table',
            endpoint: '{{ route('admin.email-templates.table-data') }}',
            rowSelectable: false,
        });
    </script>
@endpush

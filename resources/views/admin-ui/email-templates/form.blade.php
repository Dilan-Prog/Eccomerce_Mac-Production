@extends('admin-ui.layouts.master')

{{--
    Página completa del editor de plantillas (admin/email-templates/create|edit).

    Todo el editor vive ahora en el parcial _editor.blade.php, compartido con
    la pantalla de pestañas de Email Marketing, que lo monta por AJAX. Esta
    página se queda como el camino "de siempre": las URLs viejas siguen
    funcionando igual y el formulario se envía con un POST normal que termina
    en un redirect, sin depender de JavaScript para guardar.
--}}
@php
    $isEdit = isset($emailTemplate) && $emailTemplate;
@endphp

@section('title', $isEdit ? 'Editar plantilla de correo' : 'Nueva plantilla de correo')

@section('content')
    @include('admin-ui.layouts.page-header', [
        'title' => $isEdit ? 'Editar plantilla de correo' : 'Nueva plantilla de correo',
        'breadcrumbs' => [
            ['label' => 'Escritorio', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
            ['label' => 'Email Marketing', 'url' => route('admin.email-marketing.index')],
            ['label' => $isEdit ? $emailTemplate->name : 'Nueva'],
        ],
    ])

    @include('admin-ui.email-templates._editor')
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <link href="{{ asset('admin-ui/css/components/email-builder.css') }}?v={{ filemtime(public_path('admin-ui/css/components/email-builder.css')) }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="{{ asset('admin-ui/js/email-builder/email-builder.js') }}?v={{ filemtime(public_path('admin-ui/js/email-builder/email-builder.js')) }}"></script>
    <script>
        // La configuración viaja dentro del propio fragmento (#eb-config).
        AU.EmailBuilder.mount(AU.EmailBuilder.readConfig());

        // En la página completa, "Cancelar" simplemente regresa al listado.
        // (En la pantalla de pestañas ese mismo botón cierra el panel — cada
        // anfitrión conecta data-eb-cancel a lo que le corresponde.)
        document.querySelector('[data-eb-cancel]').addEventListener('click', function () {
            window.location.href = '{{ route('admin.email-marketing.index') }}';
        });
    </script>
@endpush

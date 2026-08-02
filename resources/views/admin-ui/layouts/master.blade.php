<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administracion') — Mac Del Norte</title>

    <link rel="stylesheet" href="{{ asset('backend/assets/modules/fontawesome/css/all.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin-ui/css/admin.css') }}">
    @stack('styles')
</head>
<body>
    <div class="admin-ui-v2">
        <div class="au-shell">
            @include('admin-ui.layouts.sidebar')

            <div class="au-main">
                @include('admin-ui.layouts.topbar')

                <main class="au-content">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <script>
        window.__auFlash = {
            errors: [
                @foreach ($errors->all() as $error)
                    @json($error),
                @endforeach
            ],
            success: @json(session('success')),
        };
    </script>
    <script src="{{ asset('admin-ui/js/core/dom.js') }}"></script>
    <script src="{{ asset('admin-ui/js/core/toast.js') }}"></script>
    <script src="{{ asset('admin-ui/js/core/modal.js') }}"></script>
    <script src="{{ asset('admin-ui/js/core/form-modal.js') }}"></script>
    <script src="{{ asset('admin-ui/js/core/toggle.js') }}"></script>
    <script src="{{ asset('admin-ui/js/core/image-picker.js') }}"></script>
    <script src="{{ asset('admin-ui/js/core/dropdown.js') }}"></script>
    <script src="{{ asset('admin-ui/js/core/sidebar.js') }}"></script>
    <script>
        // Consumed by admin.js's generic [data-au-image-slot] handler so
        // every module's fragment can wire a gallery picker without knowing
        // the Media Library route names itself.
        window.AU_MEDIA_LIBRARY = {
            dataUrl: '{{ route('admin.media-library.data') }}',
            uploadUrl: '{{ route('admin.media-library.store') }}',
        };
    </script>
    <script src="{{ asset('admin-ui/js/admin.js') }}"></script>
    @stack('scripts')
</body>
</html>

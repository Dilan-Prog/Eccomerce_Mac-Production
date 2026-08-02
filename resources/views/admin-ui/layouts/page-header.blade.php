{{-- Reusable page header: title + optional breadcrumb/subtitle + right-aligned action slot.
     Usage: @include('admin-ui.layouts.page-header', [
         'title' => 'Ordenes',
         'subtitle' => '20 órdenes · $46,989.28 MXN en el periodo seleccionado',
         'breadcrumbs' => [['label' => 'Escritorio', 'url' => route('admin.dashboard')], ['label' => 'Ordenes']],
         'actions' => '<a href="..." class="au-btn au-btn-primary">Nueva orden</a>', // trusted, pre-rendered HTML
     ]) --}}
<div class="au-page-header">
    <div>
        @if (!empty($breadcrumbs))
            <div class="au-breadcrumb">
                @foreach ($breadcrumbs as $i => $crumb)
                    @if (!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    @else
                        <span class="au-breadcrumb-current">{{ $crumb['label'] }}</span>
                    @endif
                    @if (!$loop->last) <span class="au-breadcrumb-sep">/</span> @endif
                @endforeach
            </div>
        @endif
        <h1 class="au-page-title">{{ $title }}</h1>
        @if (!empty($subtitle))
            <div class="au-page-subtitle">{{ $subtitle }}</div>
        @endif
    </div>
    @if (!empty($actions))
        <div class="au-page-header-actions">
            {!! $actions !!}
        </div>
    @endif
</div>

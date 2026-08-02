@extends('admin-ui.layouts.master')

@section('title', 'Escritorio')

@section('content')
    @include('admin-ui.layouts.page-header', ['title' => 'Escritorio'])

    <div class="au-stat-grid">
        <a href="{{ route('admin.order.index') }}" class="au-stat-card">
            <div class="au-stat-icon is-info"><i class="fas fa-cart-plus"></i></div>
            <div>
                <div class="au-stat-label">Ordenes/Pedidos De Hoy</div>
                <div class="au-stat-value">{{ $todaysOrder }}</div>
            </div>
        </a>

        <a href="{{ route('admin.order.index') }}?filter=pendiente" class="au-stat-card">
            <div class="au-stat-icon is-warning"><i class="fas fa-cart-plus"></i></div>
            <div>
                <div class="au-stat-label">Ordenes Pendientes Hoy</div>
                <div class="au-stat-value">{{ $todaysPendingOrder }}</div>
            </div>
        </a>

        <a href="{{ route('admin.order.index') }}" class="au-stat-card">
            <div class="au-stat-icon"><i class="fas fa-cart-plus"></i></div>
            <div>
                <div class="au-stat-label">Total de Ordenes</div>
                <div class="au-stat-value">{{ $totalOrders }}</div>
            </div>
        </a>

        <a href="{{ route('admin.order.index') }}?filter=pendiente" class="au-stat-card">
            <div class="au-stat-icon is-warning"><i class="fas fa-cart-plus"></i></div>
            <div>
                <div class="au-stat-label">Total Ordenes Pendientes</div>
                <div class="au-stat-value">{{ $totalPendingOrders }}</div>
            </div>
        </a>

        <a href="{{ route('admin.order.index') }}?filter=cancelado" class="au-stat-card">
            <div class="au-stat-icon is-critical"><i class="fas fa-cart-plus"></i></div>
            <div>
                <div class="au-stat-label">Total Ordenes Canceladas</div>
                <div class="au-stat-value">{{ $totalCanceledOrders }}</div>
            </div>
        </a>

        <a href="{{ route('admin.order.index') }}?filter=entregado" class="au-stat-card">
            <div class="au-stat-icon is-success"><i class="fas fa-cart-plus"></i></div>
            <div>
                <div class="au-stat-label">Total Ordenes Completadas</div>
                <div class="au-stat-value">{{ $totalCompleteOrders }}</div>
            </div>
        </a>

        <div class="au-stat-card">
            <div class="au-stat-icon is-success"><i class="fas fa-money-bill-alt"></i></div>
            <div>
                <div class="au-stat-label">Ganancias De Hoy</div>
                <div class="au-stat-value">{{ $settings->currency_icon }}{{ $todaysEarnings }}</div>
            </div>
        </div>

        <div class="au-stat-card">
            <div class="au-stat-icon is-success"><i class="fas fa-money-bill-alt"></i></div>
            <div>
                <div class="au-stat-label">Ganancias Del Mes</div>
                <div class="au-stat-value">{{ $settings->currency_icon }}{{ $monthEarnings }}</div>
            </div>
        </div>

        <div class="au-stat-card">
            <div class="au-stat-icon is-info"><i class="fas fa-money-bill-alt"></i></div>
            <div>
                <div class="au-stat-label">Ganancias Del Año</div>
                <div class="au-stat-value">{{ $settings->currency_icon }}{{ $yearEarnings }}</div>
            </div>
        </div>

        <a href="{{ route('admin.brand.index') }}" class="au-stat-card">
            <div class="au-stat-icon is-info"><i class="fas fa-copyright"></i></div>
            <div>
                <div class="au-stat-label">Total de Marcas</div>
                <div class="au-stat-value">{{ $totalBrands }}</div>
            </div>
        </a>

        <a href="{{ route('admin.category.index') }}" class="au-stat-card">
            <div class="au-stat-icon is-info"><i class="fas fa-list"></i></div>
            <div>
                <div class="au-stat-label">Total de Categorias</div>
                <div class="au-stat-value">{{ $totalCategories }}</div>
            </div>
        </a>

        <a href="{{ route('admin.customer.index') }}" class="au-stat-card">
            <div class="au-stat-icon is-warning"><i class="far fa-file"></i></div>
            <div>
                <div class="au-stat-label">Total de Usuarios</div>
                <div class="au-stat-value">{{ $totalUsers }}</div>
            </div>
        </a>
    </div>
@endsection

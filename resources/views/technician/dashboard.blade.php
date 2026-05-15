{{-- resources/views/technician/dashboard.blade.php --}}
@extends('technician.layouts.master')

@section('content')
<section class="section">
  <div class="section-header">
    <h1>Panel del Técnico</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item active">MAC DEL NORTE</div>
      <div class="breadcrumb-item">Dashboard</div>
    </div>
  </div>

  {{-- Stats --}}
  <div class="row">
    <div class="col-lg-4 col-md-6 col-sm-12">
      <div class="card card-statistic-1">
        <div class="card-icon" style="background:var(--navy)">
          <i class="fas fa-file-alt"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header"><h4>Total Reportes</h4></div>
          <div class="card-body">{{ $stats['total'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12">
      <div class="card card-statistic-1">
        <div class="card-icon" style="background:var(--amber)">
          <i class="fas fa-pencil-alt"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header"><h4>En Borrador</h4></div>
          <div class="card-body">{{ $stats['draft'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12">
      <div class="card card-statistic-1">
        <div class="card-icon" style="background:var(--green)">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header"><h4>Completados</h4></div>
          <div class="card-body">{{ $stats['completed'] }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Recent reports --}}
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4>Reportes Recientes</h4>
          <a href="{{ route('technician.reports.create') }}" class="btn btn-sm text-white" style="background:var(--orange)">
            <i class="fas fa-plus"></i> Nuevo Reporte
          </a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead>
                <tr>
                  <th>Folio</th>
                  <th>Fecha</th>
                  <th>Tipo</th>
                  <th>Cliente</th>
                  <th>Estado</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recientes as $r)
                <tr>
                  <td><strong>{{ $r->folio }}</strong></td>
                  <td>{{ $r->fecha_servicio ? $r->fecha_servicio->format('d/m/Y') : '—' }}</td>
                  <td>{{ $r->tipo_servicio ?? '—' }}</td>
                  <td>{{ $r->cliente_nombre ?? '—' }}</td>
                  <td>
                    @if($r->status === 'completed')
                      <span class="badge" style="background:var(--green);color:#fff">Completado</span>
                    @else
                      <span class="badge" style="background:var(--amber);color:#fff">Borrador</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('technician.reports.index') }}" class="btn btn-sm btn-primary">Ver</a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">No hay reportes aún.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($stats['total'] > 5)
        <div class="card-footer text-right">
          <a href="{{ route('technician.reports.index') }}" class="btn btn-sm" style="background:var(--navy);color:#fff">
            Ver todos los reportes
          </a>
        </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection

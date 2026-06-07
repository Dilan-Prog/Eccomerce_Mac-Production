@extends('admin.layouts.master')

@section('content')

<section class="section">
    <div class="section-header">
        <h1>Cotizaciones</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Inicio</a></div>
            <div class="breadcrumb-item">Cotizaciones</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Todas las Cotizaciones</h4>
                        <div class="card-header-action">
                            <form method="GET" action="{{ route('admin.cotizaciones.index') }}"
                                  style="display:flex;gap:8px;align-items:center;">
                                <input type="text" name="search"
                                       class="form-control form-control-sm"
                                       placeholder="Buscar folio, nombre o RFC…"
                                       value="{{ $search ?? '' }}"
                                       style="width:240px;">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if($search)
                                    <a href="{{ route('admin.cotizaciones.index') }}"
                                       class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>RFC</th>
                                        <th>Fecha</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cotizaciones as $cot)
                                    <tr>
                                        <td>
                                            <span class="badge badge-primary" style="font-size:12px;">
                                                {{ $cot->folio }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight:700;">{{ $cot->user->name ?? '—' }}</div>
                                            <div style="font-size:11px;color:#718096;">{{ $cot->user->email ?? '' }}</div>
                                        </td>
                                        <td>
                                            @if($cot->perfil)
                                                <span class="badge {{ $cot->perfil->tipo_persona === 'empresa' ? 'badge-info' : 'badge-success' }}">
                                                    {{ $cot->perfil->tipo_persona === 'empresa' ? 'Empresa' : 'Física' }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            <code style="font-size:12px;">{{ $cot->perfil->rfc ?? '—' }}</code>
                                        </td>
                                        <td>{{ $cot->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-right">
                                            <strong>${{ number_format($cot->total, 2, '.', ',') }}</strong>
                                            <span style="font-size:10px;color:#718096;"> MXN</span>
                                        </td>
                                        <td class="text-center">
                                            @if($cot->pdf_path)
                                            <a href="{{ Storage::url($cot->pdf_path) }}" target="_blank"
                                               class="btn btn-sm btn-warning" title="Ver PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('admin.cotizaciones.show', $cot->id) }}"
                                               class="btn btn-sm btn-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center" style="padding:40px;color:#718096;">
                                            <i class="fas fa-file-invoice" style="font-size:28px;display:block;margin-bottom:10px;opacity:0.4;"></i>
                                            No hay cotizaciones registradas.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINACIÓN --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div style="font-size:13px;color:#718096;">
                                Mostrando {{ $cotizaciones->firstItem() ?? 0 }}–{{ $cotizaciones->lastItem() ?? 0 }}
                                de {{ $cotizaciones->total() }} cotizaciones
                            </div>
                            {{ $cotizaciones->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

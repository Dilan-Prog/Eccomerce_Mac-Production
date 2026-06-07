@extends('admin.layouts.master')

@section('content')

<section class="section">
    <div class="section-header">
        <h1>Detalle de Cotización</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Inicio</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.cotizaciones.index') }}">Cotizaciones</a></div>
            <div class="breadcrumb-item">{{ $cotizacion->folio }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">

            {{-- DATOS DEL CLIENTE --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-user mr-2"></i>Datos del cliente</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width:140px;color:#718096;">Nombre</th>
                                <td><strong>{{ $cotizacion->user->name ?? '—' }}</strong></td>
                            </tr>
                            <tr>
                                <th style="color:#718096;">Correo</th>
                                <td>{{ $cotizacion->user->email ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th style="color:#718096;">Folio</th>
                                <td><span class="badge badge-primary">{{ $cotizacion->folio }}</span></td>
                            </tr>
                            <tr>
                                <th style="color:#718096;">Fecha</th>
                                <td>{{ $cotizacion->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th style="color:#718096;">Status</th>
                                <td><span class="badge badge-success">{{ ucfirst($cotizacion->status) }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- DATOS FISCALES --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-id-card mr-2"></i>Perfil fiscal</h4>
                    </div>
                    <div class="card-body">
                        @if($cotizacion->perfil)
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width:140px;color:#718096;">Tipo de persona</th>
                                <td>
                                    <span class="badge {{ $cotizacion->perfil->tipo_persona === 'empresa' ? 'badge-info' : 'badge-success' }}">
                                        {{ $cotizacion->perfil->tipo_persona === 'empresa' ? 'Persona Moral' : 'Persona Física' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th style="color:#718096;">RFC</th>
                                <td><code>{{ $cotizacion->perfil->rfc }}</code></td>
                            </tr>
                            @if($cotizacion->perfil->razon_social)
                            <tr>
                                <th style="color:#718096;">Razón Social</th>
                                <td>{{ $cotizacion->perfil->razon_social }}</td>
                            </tr>
                            @endif
                            @if($cotizacion->perfil->curp)
                            <tr>
                                <th style="color:#718096;">CURP</th>
                                <td><code>{{ $cotizacion->perfil->curp }}</code></td>
                            </tr>
                            @endif
                            <tr>
                                <th style="color:#718096;">Dirección fiscal</th>
                                <td>{{ $cotizacion->perfil->direccion_fiscal }}</td>
                            </tr>
                            <tr>
                                <th style="color:#718096;">CIF</th>
                                <td>
                                    <a href="{{ Storage::url($cotizacion->perfil->cif_path) }}"
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file"></i> Ver CIF
                                    </a>
                                </td>
                            </tr>
                        </table>
                        @else
                            <p class="text-muted">Sin perfil fiscal.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TABLA DE PRODUCTOS --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-boxes mr-2"></i>Productos cotizados</h4>
                        <div class="card-header-action">
                            @if($pdfUrl)
                            <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-warning">
                                <i class="fas fa-file-pdf"></i> Ver PDF
                            </a>
                            <a href="{{ $pdfUrl }}" download="{{ $cotizacion->folio }}.pdf"
                               class="btn btn-primary">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>SKU / Modelo</th>
                                        <th>Marca</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">P. Unitario</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cotizacion->productos_json as $i => $p)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><strong>{{ $p['nombre'] }}</strong></td>
                                        <td>
                                            @if(!empty($p['sku']))<code>{{ $p['sku'] }}</code>@endif
                                            @if(!empty($p['modelo'])) <small class="text-muted">/ {{ $p['modelo'] }}</small>@endif
                                        </td>
                                        <td>{{ $p['marca'] ?? '—' }}</td>
                                        <td class="text-right">{{ $p['cantidad'] }}</td>
                                        <td class="text-right">${{ number_format($p['precio'], 2, '.', ',') }}</td>
                                        <td class="text-right"><strong>${{ number_format($p['subtotal'], 2, '.', ',') }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td class="text-right font-weight-bold">Subtotal s/IVA:</td>
                                        <td class="text-right">${{ number_format($cotizacion->total / 1.16, 2, '.', ',') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td class="text-right font-weight-bold">IVA (16%):</td>
                                        <td class="text-right">${{ number_format($cotizacion->total - ($cotizacion->total / 1.16), 2, '.', ',') }}</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="5"></td>
                                        <td class="text-right font-weight-bold">TOTAL:</td>
                                        <td class="text-right font-weight-bold">
                                            ${{ number_format($cotizacion->total, 2, '.', ',') }} MXN
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <a href="{{ route('admin.cotizaciones.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Cotizaciones
        </a>
    </div>
</section>

@endsection

@extends('layout.app')

@section('title', 'Reporte General de Deudas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fa fa-chart-bar"></i> Reporte General de Deudas</h2>
                    @if(request('fecha_desde') || request('fecha_hasta') || request('estado'))
                        <div class="mt-2">
                            <small class="text-muted">
                                Filtros aplicados:
                                @if(request('fecha_desde'))
                                    <span class="badge badge-info">Desde: {{ \Carbon\Carbon::parse(request('fecha_desde'))->format('d/m/Y') }}</span>
                                @endif
                                @if(request('fecha_hasta'))
                                    <span class="badge badge-info">Hasta: {{ \Carbon\Carbon::parse(request('fecha_hasta'))->format('d/m/Y') }}</span>
                                @endif
                                @if(request('estado'))
                                    <span class="badge badge-info">Estado: {{ ucfirst(request('estado')) }}</span>
                                @endif
                            </small>
                        </div>
                    @endif
                </div>
                <div>
                    <a href="{{ route('deudas.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Volver a Deudas
                    </a>
                    <a href="{{ route('deudas.exportar-excel', request()->query()) }}" class="btn btn-success">
                        <i class="fa fa-file-excel"></i> Exportar Excel
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fa fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros para el reporte -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fa fa-filter"></i> Filtros del Reporte</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('deudas.reporte') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="parcial" {{ request('estado') == 'parcial' ? 'selected' : '' }}>Pago Parcial</option>
                            <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                            <option value="vencida" {{ request('estado') == 'vencida' ? 'selected' : '' }}>Vencida</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Generar Reporte
                            </button>
                            <a href="{{ route('deudas.reporte') }}" class="btn btn-secondary">
                                <i class="fa fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Totales generales -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4>{{ $totales['cantidad_deudas'] }}</h4>
                    <p class="mb-0">Total Deudas</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $totales['clientes_con_deuda'] }}</h4>
                    <p class="mb-0">Clientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>S/ {{ number_format($totales['total_pagado'], 2) }}</h4>
                    <p class="mb-0">Pagado</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4>S/ {{ number_format($totales['total_deuda'], 2) }}</h4>
                    <p class="mb-0">Adeudado</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>S/ {{ number_format($totales['total_deuda'] + $totales['total_pagado'], 2) }}</h4>
                    <p class="mb-0">Total Ventas</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    @php
                        $porcentajeCobrado = ($totales['total_deuda'] + $totales['total_pagado']) > 0 
                            ? ($totales['total_pagado'] / ($totales['total_deuda'] + $totales['total_pagado'])) * 100 
                            : 0;
                    @endphp
                    <h4>{{ number_format($porcentajeCobrado, 1) }}%</h4>
                    <p class="mb-0">Cobrado</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Reporte por cliente -->
    @if($deudasPorCliente->count() > 0)
        @foreach($deudasPorCliente as $clienteData)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fa fa-user"></i> {{ $clienteData['cliente']->nombre }}
                            </h5>
                            <small class="text-muted">{{ $clienteData['cliente']->numero_documento }}</small>
                        </div>
                        <div class="col-md-6 text-right">
                            <span class="badge badge-warning">{{ $clienteData['cantidad_deudas'] }} deuda(s)</span>
                            <span class="badge badge-danger">S/ {{ number_format($clienteData['total_deuda'], 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Comprobante</th>
                                    <th>Total</th>
                                    <th>Pagado</th>
                                    <th>Deuda</th>
                                    <th>Estado</th>
                                    <th>Vencimiento</th>
                                    <th>Días Atraso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clienteData['deudas'] as $deuda)
                                    <tr>
                                        <td>{{ $deuda->fecha_venta->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ strtoupper($deuda->tipo_documento) }}</span>
                                            {{ $deuda->numero_comprobante }}
                                        </td>
                                        <td>S/ {{ number_format($deuda->monto_total, 2) }}</td>
                                        <td>
                                            <span class="text-success">S/ {{ number_format($deuda->monto_pagado, 2) }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-danger">S/ {{ number_format($deuda->monto_deuda, 2) }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match($deuda->estado) {
                                                    'pendiente' => 'badge-warning',
                                                    'parcial' => 'badge-info',
                                                    'pagada' => 'badge-success',
                                                    'vencida' => 'badge-danger',
                                                    default => 'badge-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ ucfirst($deuda->estado) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($deuda->fecha_vencimiento)
                                                {{ $deuda->fecha_vencimiento->format('d/m/Y') }}
                                                @if($deuda->fecha_vencimiento->isPast() && $deuda->estado !== 'pagada')
                                                    <br><small class="text-danger">
                                                        <i class="fa fa-exclamation-triangle"></i> Vencida
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($deuda->fecha_vencimiento && $deuda->fecha_vencimiento->isPast() && $deuda->estado !== 'pagada')
                                                <span class="badge badge-danger">
                                                    {{ $deuda->fecha_vencimiento->diffInDays(now()) }} días
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="thead-light">
                                <tr>
                                    <th colspan="4" class="text-right">Totales del Cliente:</th>
                                    <th class="text-danger">S/ {{ number_format($clienteData['total_deuda'], 2) }}</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Resumen final -->
        <div class="card mt-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fa fa-calculator"></i> Resumen Final del Reporte</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <h3 class="text-info">{{ $totales['cantidad_deudas'] }}</h3>
                        <p class="mb-0">Deudas Totales</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h3 class="text-warning">{{ $totales['clientes_con_deuda'] }}</h3>
                        <p class="mb-0">Clientes con Deuda</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h3 class="text-success">S/ {{ number_format($totales['total_pagado'], 2) }}</h3>
                        <p class="mb-0">Total Pagado</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h3 class="text-danger">S/ {{ number_format($totales['total_deuda'], 2) }}</h3>
                        <p class="mb-0">Total Adeudado</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa fa-chart-bar fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No hay datos para mostrar</h4>
                <p class="text-muted">No se encontraron deudas que coincidan con los filtros aplicados.</p>
            </div>
        </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    @media print {
        .btn, .card-header .row .col-md-6:last-child, .no-print {
            display: none !important;
        }
        
        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }
        
        .card-header {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
        }
        
        .table {
            font-size: 12px;
        }
        
        .badge {
            color: #000 !important;
            border: 1px solid #000 !important;
        }
    }
</style>
@endpush
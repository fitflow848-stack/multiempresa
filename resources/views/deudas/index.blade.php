@extends('layout.app')

@section('title', 'Gestión de Deudas')

@section('content')
<div class="container-fluid">
    <!-- Header con estadísticas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fa fa-credit-card"></i> Gestión de Deudas</h2>
                <a href="{{ route('deudas.reporte') }}" class="btn btn-info">
                    <i class="fa fa-chart-bar"></i> Reporte General
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>S/ {{ number_format($estadisticas['total_pendiente'], 2) }}</h4>
                            <p class="mb-0">Total Pendiente</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['cantidad_pendiente'] }}</h4>
                            <p class="mb-0">Deudas Pendientes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['vencidas'] }}</h4>
                            <p class="mb-0">Deudas Vencidas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fa fa-filter"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('deudas.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Cliente</label>
                        <select name="cliente_id" class="form-control">
                            <option value="">Todos los clientes</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }} - {{ $cliente->numero_documento }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="parcial" {{ request('estado') == 'parcial' ? 'selected' : '' }}>Pago Parcial</option>
                            <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                            <option value="vencida" {{ request('estado') == 'vencida' ? 'selected' : '' }}>Vencida</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-2">
                        <label>Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('deudas.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <div class="form-check">
                            <input type="checkbox" name="mostrar_todas" class="form-check-input" {{ request('mostrar_todas') ? 'checked' : '' }}>
                            <label class="form-check-label">Mostrar todas</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de deudas -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fa fa-table"></i> Lista de Deudas</h5>
        </div>
        <div class="card-body">
            @if($deudas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Comprobante</th>
                                <th>Total</th>
                                <th>Pagado</th>
                                <th>Deuda</th>
                                <th>Estado</th>
                                <th>Vencimiento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deudas as $deuda)
                                <tr>
                                    <td>{{ $deuda->fecha_venta->format('d/m/Y') }}</td>
                                    <td>
                                        <strong>{{ $deuda->cliente->nombre ?? 'Sin cliente' }}</strong><br>
                                        <small class="text-muted">{{ $deuda->cliente->numero_documento ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ strtoupper($deuda->tipo_documento) }}</span>
                                        {{ $deuda->numero_comprobante }}
                                    </td>
                                    <td>
                                        <strong>S/ {{ number_format($deuda->monto_total, 2) }}</strong>
                                    </td>
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
                                                    <i class="bx bx-time"></i> Vencida
                                                </small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('deudas.show', $deuda->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @if($deuda->estado !== 'pagada')
                                                <button type="button" class="btn btn-sm btn-success" onclick="aplicarPago({{ $deuda->id }})" title="Aplicar pago">
                                                    <i class="bx bx-dollar-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="marcarComoPagada({{ $deuda->id }})" title="Marcar como pagada">
                                                    <i class="bx bx-check"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <div class="d-flex justify-content-center">
                    {{ $deudas->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fa fa-credit-card fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron deudas</h5>
                    <p class="text-muted">No hay deudas que coincidan con los filtros aplicados.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para aplicar pago -->
<div class="modal fade" id="modalAplicarPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aplicar Pago a Deuda</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formAplicarPago">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Monto del Pago</label>
                        <input type="number" step="0.01" min="0.01" id="montoPago" class="form-control" required>
                        <small class="form-text text-muted">Deuda pendiente: S/ <span id="deudaPendiente">0.00</span></small>
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea id="observaciones" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Aplicar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let deudaActual = null;

function aplicarPago(deudaId) {
    // Encontrar la deuda en la tabla
    const fila = event.target.closest('tr');
    const deudaPendiente = fila.querySelector('td:nth-child(6)').textContent.replace('S/ ', '').replace(',', '');
    
    deudaActual = deudaId;
    document.getElementById('deudaPendiente').textContent = deudaPendiente;
    document.getElementById('montoPago').max = parseFloat(deudaPendiente.replace(',', ''));
    document.getElementById('montoPago').value = deudaPendiente;
    
    // Show modal using vanilla JavaScript
    const modal = new bootstrap.Modal(document.getElementById('modalAplicarPago'));
    modal.show();
}

function marcarComoPagada(deudaId) {
    if (confirm('¿Está seguro que desea marcar esta deuda como pagada completamente?')) {
        fetch(`{{ url('deudas') }}/${deudaId}/marcar-pagada`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Deuda marcada como pagada correctamente');
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al marcar la deuda como pagada');
        });
    }
}

document.getElementById('formAplicarPago').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const montoPago = document.getElementById('montoPago').value;
    const observaciones = document.getElementById('observaciones').value;
    
    if (!deudaActual || !montoPago || montoPago <= 0) {
        alert('Ingrese un monto válido');
        return;
    }
    
    fetch(`{{ url('deudas') }}/${deudaActual}/aplicar-pago`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            monto_pago: montoPago,
            observaciones: observaciones
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pago aplicado correctamente');
            // Hide modal using vanilla JavaScript
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAplicarPago'));
            if (modal) modal.hide();
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al aplicar el pago');
    });
});
</script>
@endpush
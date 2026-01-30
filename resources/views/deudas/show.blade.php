@extends('layout.app')

@section('title', 'Detalles de Deuda')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>
                        <i class="fa fa-eye"></i> Detalles de Deuda #{{ $deuda->id }}
                    </h2>
                    <div>
                        <button type="button" class="btn btn-info" onclick="verHistorial()">
                            <i class="fa fa-history"></i> Ver Historial
                        </button>
                        <a href="{{ route('deudas.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Volver a Lista
                        </a>
                        @if ($deuda->estado !== 'pagada')
                            <button type="button" class="btn btn-success" onclick="aplicarPago({{ $deuda->id }})">
                                <i class="fa fa-dollar-sign"></i> Aplicar Pago
                            </button>
                            <button type="button" class="btn btn-warning" onclick="marcarComoPagada({{ $deuda->id }})">
                                <i class="fa fa-check"></i> Marcar como Pagada
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Información de la Deuda -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa fa-info-circle"></i> Información de la Deuda</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Fecha de Venta:</th>
                                        <td>{{ $deuda->fecha_venta->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo de Documento:</th>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ strtoupper($deuda->tipo_documento) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Número de Comprobante:</th>
                                        <td><strong>{{ $deuda->numero_comprobante }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Monto Total:</th>
                                        <td><strong class="text-primary">S/
                                                {{ number_format($deuda->monto_total, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Monto Pagado:</th>
                                        <td><strong class="text-success">S/
                                                {{ number_format($deuda->monto_pagado, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Monto Deuda:</th>
                                        <td><strong class="text-danger">S/
                                                {{ number_format($deuda->monto_deuda, 2) }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Estado:</th>
                                        <td>
                                            @php
                                                $badgeClass = match ($deuda->estado) {
                                                    'pendiente' => 'badge-warning',
                                                    'parcial' => 'badge-info',
                                                    'pagada' => 'badge-success',
                                                    'vencida' => 'badge-danger',
                                                    default => 'badge-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ ucfirst($deuda->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de Vencimiento:</th>
                                        <td>
                                            @if ($deuda->fecha_vencimiento)
                                                {{ $deuda->fecha_vencimiento->format('d/m/Y') }}
                                                @if ($deuda->fecha_vencimiento->isPast() && $deuda->estado !== 'pagada')
                                                    <br><small class="text-danger">
                                                        <i class="fa fa-exclamation-triangle"></i> Vencida
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-muted">No definida</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Creada por:</th>
                                        <td>{{ $deuda->user->name ?? 'Sistema' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de Creación:</th>
                                        <td>{{ $deuda->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Última Actualización:</th>
                                        <td>{{ $deuda->updated_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @if ($deuda->observaciones)
                                        <tr>
                                            <th>Observaciones:</th>
                                            <td>{{ $deuda->observaciones }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de la Venta Original (si existe) -->
                @if ($deuda->venta)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><i class="fa fa-receipt"></i> Detalles de la Venta Original</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>ID Venta:</strong> {{ $deuda->venta->id }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Serie-Número:</strong> {{ $deuda->venta->serie }}-{{ $deuda->venta->numero }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Subtotal:</strong> S/ {{ number_format($deuda->venta->subtotal, 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>IGV:</strong> S/ {{ number_format($deuda->venta->igv, 2) }}
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('comprobantes.show', $deuda->venta->id) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-file-invoice"></i> Ver Comprobante Completo
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>


            <!-- Información del Cliente -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa fa-user"></i> Información del Cliente</h5>
                    </div>
                    <div class="card-body">
                        @if ($deuda->cliente)
                            <div class="text-center mb-3">
                                <i class="fa fa-user-circle fa-3x text-muted"></i>
                            </div>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Nombre:</th>
                                    <td><strong>{{ $deuda->cliente->nombre }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ $deuda->cliente->tipo_documento }}:</th>
                                    <td>{{ $deuda->cliente->numero_documento }}</td>
                                </tr>
                                @if ($deuda->cliente->direccion)
                                    <tr>
                                        <th>Dirección:</th>
                                        <td>{{ $deuda->cliente->direccion }}</td>
                                    </tr>
                                @endif
                                @if ($deuda->cliente->telefono)
                                    <tr>
                                        <th>Teléfono:</th>
                                        <td>{{ $deuda->cliente->telefono }}</td>
                                    </tr>
                                @endif
                                @if ($deuda->cliente->email)
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $deuda->cliente->email }}</td>
                                    </tr>
                                @endif
                            </table>
                            <div class="mt-3">
                                <a href="{{ route('clientes.show', $deuda->cliente->id) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-external-link-alt"></i> Ver Perfil Completo
                                </a>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fa fa-user-slash fa-3x mb-3"></i>
                                <p>No hay información del cliente disponible</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Resumen de Deudas del Cliente -->
                @if ($deuda->cliente)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6><i class="fa fa-chart-pie"></i> Resumen de Deudas del Cliente</h6>
                        </div>
                        <div class="card-body">
                            @php
                                $totalDeudas = $deuda->cliente->deudas()->count();
                                $deudasPendientes = $deuda->cliente->deudas()->pendientes()->count();
                                $totalMonto = $deuda->cliente->deudas()->sum('monto_deuda');
                            @endphp
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-warning">{{ $totalDeudas }}</h4>
                                    <small>Total Deudas</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-danger">{{ $deudasPendientes }}</h4>
                                    <small>Pendientes</small>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <h5 class="text-danger">S/ {{ number_format($totalMonto, 2) }}</h5>
                                <small>Total Adeudado</small>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('deudas.index', ['cliente_id' => $deuda->cliente->id]) }}"
                                    class="btn btn-outline-info btn-sm btn-block">
                                    <i class="fa fa-list"></i> Ver Todas las Deudas
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>


    </div>



    <!-- Modal para aplicar pago (reutilizando el del index) -->
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
                            <input type="number" step="0.01" min="0.01" id="montoPago" class="form-control"
                                required>
                            <small class="form-text text-muted">Deuda pendiente: S/ <span
                                    id="deudaPendiente">{{ number_format($deuda->monto_deuda, 2) }}</span></small>
                        </div>
                        <div class="form-group">
                            <label>Método de Pago</label>
                            <select id="metodoPago" class="form-control">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Yape">Yape</option>
                                <option value="Plin">Plin</option>
                                <option value="Tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Referencia</label>
                            <input type="text" id="referenciaPago" class="form-control"
                                placeholder="Nro Operación / Referencia">
                        </div>
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea id="observaciones" class="form-control" rows="3" placeholder="Observaciones del pago (opcional)"></textarea>
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

    <!-- Modal Historial de Pagos -->
    <div class="modal fade" id="modalHistorialPagos" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-history"></i> Historial de Pagos</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Usuario</th>
                                    <th>Comprobante</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deuda->pagos as $pago)
                                    <tr>
                                        <td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                                        <td>S/ {{ number_format($pago->monto, 2) }}</td>
                                        <td>{{ $pago->metodo_pago }}</td>
                                        <td>{{ $pago->user->name ?? 'Sistema' }}</td>
                                        <td>{{ $pago->codigo_comprobante }}</td>
                                        <td>
                                            <a href="{{ route('deudas.comprobante-pago', $pago->id) }}" target="_blank"
                                                class="btn btn-sm btn-info">
                                                <i class="bx bx-file"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay pagos registrados para
                                            esta deuda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let deudaActual = {{ $deuda->id }};

        function aplicarPago(deudaId) {
            deudaActual = deudaId;
            document.getElementById('montoPago').max = {{ $deuda->monto_deuda }};
            document.getElementById('montoPago').value = {{ $deuda->monto_deuda }}; // Sugerir pago completo

            const modal = new bootstrap.Modal(document.getElementById('modalAplicarPago'));
            modal.show();
        }

        function verHistorial() {
            const modal = new bootstrap.Modal(document.getElementById('modalHistorialPagos'));
            modal.show();
        }

        function marcarComoPagada(deudaId) {
            if (confirm('¿Está seguro que desea marcar esta deuda como pagada completamente de forma manual?')) {
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
            const metodoPago = document.getElementById('metodoPago').value;
            const referencia = document.getElementById('referenciaPago').value;
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
                        metodo_pago: metodoPago,
                        referencia: referencia,
                        observaciones: observaciones
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide modal using vanilla JavaScript
                        const modalEl = document.getElementById('modalAplicarPago');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        // Preguntar si quiere imprimir recibo
                        if (confirm('Pago registrado correctamente. ¿Desea imprimir el comprobante?')) {
                            // Abrir PDF en nueva pestaña
                            if (data.pago_id) {
                                window.open(`{{ url('deudas/pago') }}/${data.pago_id}/comprobante`, '_blank');
                            }
                            // Recargar página después de abrir PDF (dar un pequeño delay)
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            window.location.reload();
                        }
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

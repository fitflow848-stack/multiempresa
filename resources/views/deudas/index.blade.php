@extends('layout.app')

@section('title', 'Gestión de Deudas')

@section('content')
    <div class="container-fluid">
        <!-- Header con estadísticas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="fa fa-credit-card"></i> Gestión de Deudas</h2>
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
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}"
                                        {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre }} - {{ $cliente->numero_documento }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Estado</label>
                            <select name="estado" class="form-control">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente
                                </option>
                                <option value="parcial" {{ request('estado') == 'parcial' ? 'selected' : '' }}>Pago Parcial
                                </option>
                                <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada
                                </option>
                                <option value="vencida" {{ request('estado') == 'vencida' ? 'selected' : '' }}>Vencida
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Desde</label>
                            <input type="date" name="fecha_desde" class="form-control"
                                value="{{ request('fecha_desde') }}">
                        </div>
                        <div class="col-md-2">
                            <label>Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control"
                                value="{{ request('fecha_hasta') }}">
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
                                <input type="checkbox" name="mostrar_todas" class="form-check-input"
                                    {{ request('mostrar_todas') ? 'checked' : '' }}>
                                <label class="form-check-label">Mostrar todas</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Clientes con Deuda -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fa fa-users"></i> Clientes con Deudas Pendientes</h5>
            </div>
            <div class="card-body">
                @if ($clientes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th class="text-center">Cant. Deudas</th>
                                    <th class="text-end">Total Deuda Acumulada</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clientes as $cliente)
                                    <tr class="cliente-row" data-cliente-id="{{ $cliente->id }}">
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary toggle-deudas" type="button">
                                                <i class="bx bx-plus"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <strong>{{ $cliente->nombre }}</strong>
                                        </td>
                                        <td>
                                            {{ $cliente->numero_documento }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info px-3">{{ $cliente->deudas_count }}</span>
                                        </td>
                                        <td class="text-end">
                                            <h5 class="text-danger font-weight-bold mb-0">S/
                                                {{ number_format($cliente->deudas_sum_monto_deuda, 2) }}</h5>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success btn-sm"
                                                    onclick="abrirModalPagoAcumulado({{ $cliente->id }}, '{{ $cliente->nombre }}', {{ $cliente->deudas_sum_monto_deuda }})">
                                                    <i class="fa fa-money-bill-wave"></i> Pagar Acumulado
                                                </button>
                                                <a href="{{ route('deudas.cliente', $cliente->id) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fa fa-external-link-alt"></i> Detalle Completo
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Fila expandible con deudas -->
                                    <tr class="deudas-expand-row d-none" id="deudas-cliente-{{ $cliente->id }}">
                                        <td colspan="6" class="p-0 border-top-0">
                                            <div class="bg-light p-3 border-bottom shadow-sm">
                                                <h6 class="font-weight-bold text-primary mb-3"><i
                                                        class="fa fa-file-invoice"></i> Documentos Pendientes de
                                                    {{ $cliente->nombre }}</h6>
                                                <div class="table-responsive bg-white rounded">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="bg-dark text-white">
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Comprobante</th>
                                                                <th class="text-end">Total</th>
                                                                <th class="text-end">Pagado</th>
                                                                <th class="text-end text-danger">Saldo</th>
                                                                <th class="text-center">Estado</th>
                                                                <th class="text-center">Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($cliente->deudas as $deuda)
                                                                <tr>
                                                                    <td>{{ $deuda->fecha_venta->format('d/m/Y') }}</td>
                                                                    <td><strong>{{ $deuda->numero_comprobante }}</strong>
                                                                    </td>
                                                                    <td class="text-end">S/
                                                                        {{ number_format($deuda->monto_total, 2) }}</td>
                                                                    <td class="text-end text-success">S/
                                                                        {{ number_format($deuda->monto_pagado, 2) }}</td>
                                                                    <td class="text-end font-weight-bold text-danger">S/
                                                                        {{ number_format($deuda->monto_deuda, 2) }}</td>
                                                                    <td class="text-center">
                                                                        <span
                                                                            class="badge badge-{{ $deuda->estado === 'vencida' ? 'danger' : ($deuda->estado === 'parcial' ? 'info' : 'warning') }}">
                                                                            {{ ucfirst($deuda->estado) }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <button class="btn btn-xs btn-primary py-0"
                                                                            onclick="abrirModalPagoIndividual({{ $deuda->id }}, '{{ $deuda->numero_comprobante }}', {{ $deuda->monto_deuda }})">
                                                                            <i class="fa fa-dollar-sign"></i> Pagar
                                                                        </button>
                                                                        <button class="btn btn-xs btn-info py-0"
                                                                            onclick="verHistorialPagos({{ $deuda->id }}, '{{ $deuda->numero_comprobante }}')">
                                                                            <i class="fa fa-history"></i> Historial
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center">
                        {{ $clientes->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-muted">No se encontraron clientes con deudas pendientes</h5>
                        <p class="text-muted">¡Excelente! Todos los clientes están al día con los filtros aplicados.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para aplicar pago -->
    <div class="modal fade" id="modalAplicarPago" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalTitle">Aplicar Pago</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formAplicarPago">
                    <div class="modal-body">
                        <div class="alert alert-info" id="infoPago">
                            Se aplicará el pago a las deudas pendientes.
                        </div>
                        <input type="hidden" id="tipoPago" value="individual">
                        <input type="hidden" id="targetId">

                        <div class="form-group">
                            <label class="font-weight-bold">Monto del Pago (S/)</label>
                            <input type="number" step="0.01" min="0.01" id="montoPago"
                                class="form-control form-control-lg text-primary font-weight-bold" required>
                            <small class="form-text text-muted">Deuda total pendiente: S/ <span
                                    id="deudaPendienteLabel">0.00</span></small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Método de Pago</label>
                            <select id="metodo_pago" class="form-control">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Yape">Yape</option>
                                <option value="Plin">Plin</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Tarjeta">Tarjeta</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Observaciones</label>
                            <textarea id="observaciones" class="form-control" rows="3" placeholder="Ej: Pago parcial del mes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fa fa-check-circle"></i> Confirmar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Pagos -->
    <div class="modal fade" id="modalHistorialPagos" tabindex="-1">
        <div class="modal-dialog modal-lg border-0 shadow">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fa fa-history"></i> Historial de Pagos: <span
                            id="historialComprobante"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="loadingHistorial" class="text-center py-5 d-none">
                        <i class="fa fa-spinner fa-spin fa-3x text-info mb-3"></i>
                        <p>Cargando historial de pagos...</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tablaHistorial">
                            <thead class="bg-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="historialBody">
                                <!-- Se llena por JS -->
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
        // Toggle de deudas hiddens
        document.querySelectorAll('.toggle-deudas').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const targetId = row.dataset.clienteId;
                const targetRow = document.getElementById(`deudas-cliente-${targetId}`);
                const icon = this.querySelector('i');

                if (targetRow.classList.contains('d-none')) {
                    targetRow.classList.remove('d-none');
                    icon.classList.replace('fa-plus', 'fa-minus');
                    this.classList.replace('btn-outline-secondary', 'btn-secondary');
                } else {
                    targetRow.classList.add('d-none');
                    icon.classList.replace('fa-minus', 'fa-plus');
                    this.classList.replace('btn-secondary', 'btn-outline-secondary');
                }
            });
        });

        function abrirModalPagoAcumulado(clienteId, nombreCliente, montoTotal) {
            document.getElementById('modalTitle').textContent = 'Pago Acumulado: ' + nombreCliente;
            document.getElementById('infoPago').innerHTML =
                `<i class="fa fa-info-circle"></i> Se distribuirá el pago entre <strong>todas</strong> las deudas pendientes de este cliente.`;
            document.getElementById('tipoPago').value = 'acumulado';
            document.getElementById('targetId').value = clienteId;
            document.getElementById('deudaPendienteLabel').textContent = parseFloat(montoTotal).toFixed(2);
            document.getElementById('montoPago').value = parseFloat(montoTotal).toFixed(2);
            document.getElementById('montoPago').max = parseFloat(montoTotal).toFixed(2);

            $('#modalAplicarPago').modal('show');
        }

        function abrirModalPagoIndividual(deudaId, comprobante, montoDeuda) {
            document.getElementById('modalTitle').textContent = 'Pago Individual: ' + comprobante;
            document.getElementById('infoPago').innerHTML =
                `<i class="fa fa-file-invoice"></i> Aplicando pago específicamente al documento <strong>${comprobante}</strong>.`;
            document.getElementById('tipoPago').value = 'individual';
            document.getElementById('targetId').value = deudaId;
            document.getElementById('deudaPendienteLabel').textContent = parseFloat(montoDeuda).toFixed(2);
            document.getElementById('montoPago').value = parseFloat(montoDeuda).toFixed(2);
            document.getElementById('montoPago').max = parseFloat(montoDeuda).toFixed(2);

            $('#modalAplicarPago').modal('show');
        }

        async function verHistorialPagos(deudaId, comprobante) {
            document.getElementById('historialComprobante').textContent = comprobante;
            document.getElementById('historialBody').innerHTML = '';
            document.getElementById('loadingHistorial').classList.remove('d-none');
            $('#modalHistorialPagos').modal('show');

            try {
                const response = await fetch(`{{ url('deudas') }}/${deudaId}/historial`);
                const data = await response.json();

                document.getElementById('loadingHistorial').classList.add('d-none');

                if (data.pagos && data.pagos.length > 0) {
                    data.pagos.forEach(pago => {
                        document.getElementById('historialBody').innerHTML += `
                            <tr>
                                <td>${new Date(pago.fecha_pago).toLocaleDateString()} ${new Date(pago.fecha_pago).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</td>
                                <td class="font-weight-bold text-success">S/ ${parseFloat(pago.monto).toFixed(2)}</td>
                                <td>${pago.metodo_pago}</td>
                                <td>${pago.user ? pago.user.name : 'Sistema'}</td>
                                <td class="text-center">
                                    <a href="{{ url('deudas/pago') }}/${pago.id}/comprobante" target="_blank" class="btn btn-xs btn-info">
                                        <i class="fa fa-print"></i> Recibo
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    document.getElementById('historialBody').innerHTML =
                        `<tr><td colspan="5" class="text-center py-4">No hay pagos registrados</td></tr>`;
                }
            } catch (error) {
                console.error(error);
                document.getElementById('historialBody').innerHTML =
                    `<tr><td colspan="5" class="text-center text-danger py-4">Error al cargar el historial</td></tr>`;
            }
        }

        document.getElementById('formAplicarPago').addEventListener('submit', function(e) {
            e.preventDefault();

            const tipo = document.getElementById('tipoPago').value;
            const targetId = document.getElementById('targetId').value;
            const montoPago = document.getElementById('montoPago').value;
            const metodo_pago = document.getElementById('metodo_pago').value;
            const observaciones = document.getElementById('observaciones').value;

            if (!montoPago || montoPago <= 0) {
                alert('Ingrese un monto válido');
                return;
            }

            const url = tipo === 'acumulado' ?
                `{{ route('deudas.pagar-acumulado') }}` :
                `{{ url('deudas') }}/${targetId}/aplicar-pago`;

            const body = tipo === 'acumulado' ?
                {
                    cliente_id: targetId,
                    monto_pago: montoPago,
                    metodo_pago,
                    observaciones
                } :
                {
                    monto_pago: montoPago,
                    metodo_pago,
                    observaciones
                };

            if (!confirm(`¿Está seguro de aplicar un pago de S/ ${montoPago} vía ${metodo_pago}?`)) return;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(body)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#modalAplicarPago').modal('hide');
                        let msg = data.message || 'Pago registrado correctamente';
                        if (confirm(msg + '. ¿Desea imprimir el comprobante de pago ahora?')) {
                            if (data.pago_id) {
                                window.open(`{{ url('deudas/pago') }}/${data.pago_id}/comprobante`, '_blank');
                            } else if (data.pago_ids && data.pago_ids.length > 0) {
                                data.pago_ids.forEach((id, index) => {
                                    setTimeout(() => {
                                        window.open(
                                            `{{ url('deudas/pago') }}/${id}/comprobante`,
                                            '_blank');
                                    }, index * 500);
                                });
                            }
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

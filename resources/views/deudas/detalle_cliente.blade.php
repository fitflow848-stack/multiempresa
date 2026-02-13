@extends('layout.app')

@section('title', 'Detalle de Deudas - ' . $cliente->nombre)

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Deudas de: {{ $cliente->nombre }}</h1>
                <p class="mb-0 text-muted">Documento: {{ $cliente->numero_documento }} &nbsp;|&nbsp;
                    Total Acumulado: <strong class="text-danger">S/ {{ number_format($totalPendienteCliente, 2) }}</strong>
                </p>
            </div>
            <div>
                <a href="{{ route('deudas.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Volver a Lista Clientes
                </a>
                <a href="#" class="btn btn-info" onclick="window.print()">
                    <i class="fa fa-print"></i> Imprimir Estado
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4 d-print-none">
            <div class="card-body">
                <form method="GET" action="{{ route('deudas.cliente', $cliente->id) }}" class="form-inline">
                    <label class="mr-2">Estado:</label>
                    <select name="estado" class="form-control mr-3" onchange="this.form.submit()">
                        <option value="">Todos (Pendientes primero)</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente
                        </option>
                        <option value="parcial" {{ request('estado') == 'parcial' ? 'selected' : '' }}>Pago Parcial</option>
                        <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                        <option value="vencida" {{ request('estado') == 'vencida' ? 'selected' : '' }}>Vencida</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Tabla de deudas -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Comprobantes por Cobrar</h6>
            </div>
            <div class="card-body">
                @if ($deudas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha Venta</th>
                                    <th>Comprobante</th>
                                    <th>Total Venta</th>
                                    <th>Pagado</th>
                                    <th>Saldo Deuda</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                    <th class="d-print-none">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deudas as $deuda)
                                    <tr class="{{ $deuda->estado === 'pagada' ? 'table-success' : '' }}">
                                        <td>{{ $deuda->fecha_venta->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge badge-primary bg-primary text-white" style="font-size: 0.85em;">
                                                {{ !empty($deuda->tipo_documento) ? strtoupper($deuda->tipo_documento) : 'DOC' }}
                                            </span>
                                            <a href="{{ route('deudas.show', $deuda->id) }}" class="font-weight-bold ml-1">
                                                {{ $deuda->numero_comprobante }}
                                            </a>
                                            @if ($deuda->observaciones)
                                                <br><small class="text-muted">{{ Str::limit($deuda->observaciones, 30) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-right">S/ {{ number_format($deuda->monto_total, 2) }}</td>
                                        <td class="text-right text-success">S/ {{ number_format($deuda->monto_pagado, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold text-danger">S/
                                            {{ number_format($deuda->monto_deuda, 2) }}
                                        </td>
                                        <td>
                                            @if ($deuda->fecha_vencimiento)
                                                {{ $deuda->fecha_vencimiento->format('d/m/Y') }}
                                                @if ($deuda->fecha_vencimiento->isPast() && $deuda->estado !== 'pagada')
                                                    <span class="badge badge-danger bg-danger text-white ml-1">Vencido</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $estado = !empty($deuda->estado) ? $deuda->estado : 'pendiente';
                                                $style = match ($estado) {
                                                    'pendiente' => 'background-color: #ffc107; color: #212529;',
                                                    'parcial' => 'background-color: #17a2b8; color: white;',
                                                    'pagada' => 'background-color: #28a745; color: white;',
                                                    'vencida' => 'background-color: #dc3545; color: white;',
                                                    default => 'background-color: #6c757d; color: white;',
                                                };
                                            @endphp
                                            <span class="badge p-2" style="{{ $style }}">{{ ucfirst($estado) }}</span>
                                        </td>
                                        <td class="d-print-none text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info"
                                                    onclick="verHistorialPagos({{ $deuda->id }}, '{{ $deuda->numero_comprobante }}')"
                                                    title="Ver historial de pagos">
                                                    <i class="bx bx-history"></i>
                                                </button>
                                                @if($deuda->estado !== 'pagada')
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        onclick="abrirModalPagoIndividual({{ $deuda->id }}, '{{ $deuda->numero_comprobante }}', {{ $deuda->monto_deuda }})"
                                                        title="Aplicar pago a este documento">
                                                        <i class="bx bx-money"></i>
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
                    <div class="d-flex justify-content-center mt-3 d-print-none">
                        {{ $deudas->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        No se encontraron deudas para este cliente con los filtros seleccionados.
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
                    <h5 class="modal-title" id="modalTitle">Registrar Pago</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAplicarPago" onsubmit="submitPago(event)">
                    <div class="modal-body">
                        <input type="hidden" id="pagoTipo" value="individual">
                        <input type="hidden" id="pagoTargetId">

                        <div class="alert alert-info py-2" id="infoPago">
                            <small>Pagando comprobante: <strong id="pagoComprobante"></strong></small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Monto a Pagar (S/)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light font-weight-bold">S/</span>
                                </div>
                                <input type="number" step="0.01" min="0.01" id="montoPago"
                                    class="form-control form-control-lg font-weight-bold text-primary" required>
                            </div>
                            <small class="form-text text-danger font-weight-bold text-right"
                                id="textoDeudaPendiente"></small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Método de Pago</label>
                            <select class="form-control" id="metodoPago" name="metodo_pago">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Yape/Plin">Yape / Plin</option>
                                <option value="Tarjeta">Tarjeta</option>
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Observaciones / Referencia</label>
                            <textarea id="observaciones" class="form-control" rows="2"
                                placeholder="Nro Operación, banco, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success font-weight-bold px-4" id="btnConfirmarPago">
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
                    <h5 class="modal-title"><i class="fa fa-history"></i> Historial de Pagos: <span id="historialComprobante"></span></h5>
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
        let deudaActualMax = 0;

        function abrirModalPagoIndividual(id, comprobante, montoPendiente) {
            document.getElementById('modalTitle').textContent = 'Registrar Pago Individual';
            document.getElementById('pagoTipo').value = 'individual';
            document.getElementById('pagoTargetId').value = id;
            document.getElementById('pagoComprobante').innerText = comprobante;
            document.getElementById('infoPago').style.display = 'block';
            document.getElementById('infoPago').innerHTML = `<small>Pagando comprobante: <strong>${comprobante}</strong></small>`;
            document.getElementById('montoPago').value = parseFloat(montoPendiente).toFixed(2);
            document.getElementById('montoPago').max = parseFloat(montoPendiente).toFixed(2);
            deudaActualMax = parseFloat(montoPendiente);
            document.getElementById('textoDeudaPendiente').innerText = 'Deuda Pendiente: S/ ' + deudaActualMax.toFixed(2);
            
            mostrarModal();
        }

        async function verHistorialPagos(deudaId, comprobante) {
            document.getElementById('historialComprobante').textContent = comprobante;
            document.getElementById('historialBody').innerHTML = '';
            document.getElementById('loadingHistorial').classList.remove('d-none');
            
            var modalEl = document.getElementById('modalHistorialPagos');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

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
                    document.getElementById('historialBody').innerHTML = `<tr><td colspan="5" class="text-center py-4">No hay pagos registrados</td></tr>`;
                }
            } catch (error) {
                console.error(error);
                document.getElementById('historialBody').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Error al cargar el historial</td></tr>`;
            }
        }

        function abrirModalPagoAcumulado() {
            const montoPnd = {{ $totalPendienteCliente }};
            document.getElementById('modalTitle').textContent = 'Registrar Pago de Deuda Total';
            document.getElementById('pagoTipo').value = 'acumulado';
            document.getElementById('pagoTargetId').value = {{ $cliente->id }};
            document.getElementById('infoPago').style.display = 'block';
            document.getElementById('pagoComprobante').innerText = 'Todos los pendientes (Acumulado)';
            document.getElementById('montoPago').value = parseFloat(montoPnd).toFixed(2);
            document.getElementById('montoPago').max = parseFloat(montoPnd).toFixed(2);
            deudaActualMax = parseFloat(montoPnd);
            document.getElementById('textoDeudaPendiente').innerText = 'Total Acumulado: S/ ' + deudaActualMax.toFixed(2);

            mostrarModal();
        }

        function mostrarModal() {
            var modalEl = document.getElementById('modalAplicarPago');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            setTimeout(() => { document.getElementById('montoPago').select(); }, 400);
        }

        function submitPago(e) {
            e.preventDefault();

            const tipo = document.getElementById('pagoTipo').value;
            const targetId = document.getElementById('pagoTargetId').value;
            const monto = parseFloat(document.getElementById('montoPago').value);
            const metodo = document.getElementById('metodoPago').value;
            const obs = document.getElementById('observaciones').value;
            const btn = document.getElementById('btnConfirmarPago');

            if (monto > deudaActualMax + 0.01) { // Allow for minor floating point inaccuracies
                alert('El monto no puede ser mayor a la deuda pendiente (S/ ' + deudaActualMax.toFixed(2) + ')');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';

            const url = tipo === 'acumulado'
                ? `{{ route('deudas.pagar-acumulado') }}`
                : `{{ url('deudas') }}/${targetId}/aplicar-pago`;

            const body = tipo === 'acumulado'
                ? { cliente_id: targetId, monto_pago: monto, metodo_pago: metodo, observaciones: obs }
                : { monto_pago: monto, metodo_pago: metodo, observaciones: obs };

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
                        // Éxito
                        // $('#modalAplicarPago').modal('hide'); // No longer needed with Bootstrap 5
                        var modalEl = document.getElementById('modalAplicarPago');
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        // Usar SweetAlert2 si está disponible, o alert
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pago Registrado',
                                text: data.message || 'El pago se aplicó correctamente y se actualizó la caja.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert(data.message || 'Pago registrado correctamente');
                            location.reload();
                        }
                    } else {
                        alert('Error: ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-check-circle"></i> Confirmar Pago';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error de conexión al procesar el pago');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-check-circle"></i> Confirmar Pago';
                });
        }
    </script>
@endpush
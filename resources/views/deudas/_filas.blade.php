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

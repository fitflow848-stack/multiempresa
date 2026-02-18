@extends('layout.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Finanzas /</span> Registro de Operaciones</h4>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Historial de Operaciones</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                        <i class="bx bx-plus me-1"></i> Registrar Operación
                    </button>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo de Operación</th>
                                <th>Descripción</th>
                                <th>Documento</th>
                                <th>Monto</th>
                                <th>Pagado</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($operaciones as $op)
                                <tr>
                                    <td>{{ $op->fecha_registro->format('d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $badgeColor = match ($op->tipo->nombre) {
                                                'Compras a credito' => 'bg-label-primary',
                                                'Adelanto clientes' => 'bg-label-success',
                                                'Adelantos personal' => 'bg-label-info',
                                                default => 'bg-label-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeColor }} me-1">{{ $op->tipo->nombre }}</span>
                                    </td>
                                    <td>{{ $op->nombre }}</td>
                                    <td>{{ $op->documento ?? '-' }}</td>
                                    <td class="fw-bold">S/ {{ number_format($op->monto, 2) }}</td>
                                    <td>S/ {{ number_format($op->monto_pagado, 2) }}</td>
                                    <td class="text-danger fw-bold">S/ {{ number_format($op->saldo, 2) }}</td>
                                    <td>
                                        @if($op->estado == 'pendiente')
                                            <span class="badge bg-label-warning">Pendiente</span>
                                        @elseif($op->estado == 'parcial')
                                            <span class="badge bg-label-info">Parcial</span>
                                        @elseif($op->estado == 'pagado')
                                            <span class="badge bg-label-success">Pagado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($op->tipo->nombre === 'Compras a credito' && $op->saldo > 0)
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#modalPagar{{ $op->id }}">
                                                <i class="bx bx-dollar-circle"></i> Pagar
                                            </button>

                                            <!-- Modal Pagar -->
                                            <div class="modal fade" id="modalPagar{{ $op->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Registrar Pago</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('pasivos.pagar', $op->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col mb-3">
                                                                        <label for="monto" class="form-label">Monto a Pagar (Saldo:
                                                                            {{ $op->saldo }})</label>
                                                                        <input type="number" step="0.01" name="monto"
                                                                            class="form-control" value="{{ $op->saldo }}"
                                                                            max="{{ $op->saldo }}" requried>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col mb-3">
                                                                        <label for="fecha_pago" class="form-label">Fecha
                                                                            Pago</label>
                                                                        <input type="date" name="fecha_pago" class="form-control"
                                                                            value="{{ date('Y-m-d') }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col mb-3">
                                                                        <label for="metodo_pago" class="form-label">Método de
                                                                            Pago</label>
                                                                        <select name="metodo_pago" class="form-select" required>
                                                                            <option value="Efectivo">Efectivo</option>
                                                                            <option value="Transferencia">Transferencia</option>
                                                                            <option value="Yape/Plin">Yape/Plin</option>
                                                                            <option value="Tarjeta">Tarjeta</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col mb-3">
                                                                        <label for="observaciones"
                                                                            class="form-label">Observaciones</label>
                                                                        <textarea name="observaciones" class="form-control"
                                                                            rows="2"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-outline-secondary"
                                                                    data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary">Guardar Pago</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($op->monto_pagado > 0)
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @foreach($op->pagos as $pago)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('pasivos.ticket', $pago->id) }}"
                                                                target="_blank">
                                                                <i class="bx bx-printer me-1"></i> Ticket
                                                                ({{ $pago->fecha_pago->format('d/m') }} - {{ $pago->monto }})
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No hay operaciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer py-3">
                    {{ $operaciones->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registro -->
    <div class="modal fade" id="modalRegistro" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nueva Operación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('finanzas_vendedor.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <label for="tipo_operacion" class="col-sm-2 col-form-label">Tipo</label>
                            <div class="col-sm-10">
                                <select class="form-select" id="tipo_operacion" name="tipo_operacion" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option value="compras_credito">Compras a Crédito</option>
                                    <option value="adelanto_clientes">Adelanto Clientes</option>
                                    <option value="adelanto_personal">Adelantos Personal</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="nombre" class="col-sm-2 col-form-label">Descripción</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                    placeholder="Concepto o Descripción" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="monto" class="col-sm-2 col-form-label">Monto</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" class="form-control" id="monto" name="monto"
                                        placeholder="0.00" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="fecha_registro" class="col-sm-2 col-form-label">Fecha</label>
                            <div class="col-sm-10">
                                <input type="date" class="form-control" id="fecha_registro" name="fecha_registro"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="documento" class="col-sm-2 col-form-label">Documento</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="documento" name="documento"
                                    placeholder="N° Comprobante o Referencia (Opcional)">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="observaciones" class="col-sm-2 col-form-label">Observaciones</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Operación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
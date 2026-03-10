@extends('layout.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Finanzas /</span> {{ $tituloSeccion }}
        </h4>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filtros Avanzados --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('finanzas_vendedor.index') }}" class="row g-3">
                    <input type="hidden" name="tipo" value="{{ $tipoActivo }}">
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="search" class="form-control" placeholder="Empresa, descripción..."
                            value="{{ $search }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Vista</label>
                        <select name="agrupar" class="form-select">
                            <option value="0" {{ !$agrupar ? 'selected' : '' }}>Detallado</option>
                            <option value="1" {{ $agrupar ? 'selected' : '' }}>Agrupado por Persona</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-filter-alt"></i> Filtrar
                        </button>
                        <a href="{{ route('finanzas_vendedor.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabs de filtro rápido --}}
        <div class="mb-3">
            <div class="btn-group" role="group">
                <a href="{{ route('finanzas_vendedor.index', ['agrupar' => $agrupar, 'search' => $search, 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta]) }}"
                    class="btn btn-sm {{ !$tipoActivo ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bx bx-list-ul me-1"></i> Todos
                </a>
                <a href="{{ route('finanzas_vendedor.index', ['tipo' => 'adelanto_personal', 'agrupar' => $agrupar, 'search' => $search, 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta]) }}"
                    class="btn btn-sm {{ $tipoActivo == 'adelanto_personal' ? 'btn-info' : 'btn-outline-secondary' }}">
                    <i class="bx bx-user me-1"></i> Adelantos Personal
                </a>
                <a href="{{ route('finanzas_vendedor.index', ['tipo' => 'compras_credito', 'agrupar' => $agrupar, 'search' => $search, 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta]) }}"
                    class="btn btn-sm {{ $tipoActivo == 'compras_credito' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bx bx-cart me-1"></i> Compras a Crédito
                </a>
                <a href="{{ route('finanzas_vendedor.index', ['tipo' => 'adelanto_clientes', 'agrupar' => $agrupar, 'search' => $search, 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta]) }}"
                    class="btn btn-sm {{ $tipoActivo == 'adelanto_clientes' ? 'btn-success' : 'btn-outline-secondary' }}">
                    <i class="bx bx-money me-1"></i> Adelanto Clientes
                </a>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $agrupar ? 'Resumen Agrupado por Empresa/Persona' : 'Historial de Operaciones' }}
                    </h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                        <i class="bx bx-plus me-1"></i> Registrar Operación
                    </button>
                </div>

                @if (!$agrupar)
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo de Operación</th>
                                    <th>Empresa / Persona</th>
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
                                                $tipoNombreLower = strtolower($op->tipo->nombre);
                                                if (str_contains($tipoNombreLower, 'compra')) {
                                                    $badgeColor = 'bg-label-primary';
                                                } elseif (str_contains($tipoNombreLower, 'adelanto') && str_contains($tipoNombreLower, 'cliente')) {
                                                    $badgeColor = 'bg-label-success';
                                                } elseif (str_contains($tipoNombreLower, 'adelanto') && str_contains($tipoNombreLower, 'personal')) {
                                                    $badgeColor = 'bg-label-info';
                                                } else {
                                                    $badgeColor = 'bg-label-secondary';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeColor }} me-1">{{ $op->tipo->nombre }}</span>
                                        </td>
                                        <td class="fw-semibold">
                                            <i class="bx bx-building-house me-1 text-muted"></i>
                                            {{ $op->empresa_persona ?? '-' }}
                                        </td>
                                        <td>{{ $op->nombre }}</td>
                                        <td>{{ $op->documento ?? '-' }}</td>
                                        <td class="fw-bold">S/ {{ number_format($op->monto, 2) }}</td>
                                        <td>S/ {{ number_format($op->monto_pagado, 2) }}</td>
                                        <td class="text-danger fw-bold">S/ {{ number_format($op->saldo, 2) }}</td>
                                        <td>
                                            @if ($op->estado == 'pendiente')
                                                <span class="badge bg-label-warning">Pendiente</span>
                                            @elseif($op->estado == 'aprobado')
                                                <span class="badge bg-label-primary">Aprobado</span>
                                            @elseif($op->estado == 'parcial')
                                                <span class="badge bg-label-info">Parcial</span>
                                            @elseif($op->estado == 'pagado')
                                                <span class="badge bg-label-success">Pagado</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (!isset($op->_es_activo))
                                                {{-- Acciones para registros Pasivo --}}
                                                @php
                                                    $tn = strtolower($op->tipo->nombre);
                                                    $esAdelanto = (str_contains($tn, 'adelanto') && str_contains($tn, 'personal')) ||
                                                                  (str_contains($tn, 'adelanto') && str_contains($tn, 'cliente'));
                                                    $esCompra = str_contains($tn, 'compra');
                                                    $mostrarBoton = ($esAdelanto || $esCompra) && $op->saldo > 0;
                                                @endphp
                                                @if ($mostrarBoton)
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#modalPagar{{ $op->id }}">
                                                        <i class="bx bx-dollar-circle"></i>
                                                        {{ $esAdelanto ? 'Saldar' : 'Pagar' }}
                                                    </button>

                                                    {{-- Modal Pagar --}}
                                                    <div class="modal fade" id="modalPagar{{ $op->id }}" tabindex="-1"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Registrar Pago</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form action="{{ route('pasivos.pagar', $op->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <div class="row">
                                                                            <div class="col mb-3">
                                                                                <label for="monto" class="form-label">Monto
                                                                                    a Pagar (Saldo:
                                                                                    {{ $op->saldo }})</label>
                                                                                <input type="number" step="0.01"
                                                                                    name="monto" class="form-control"
                                                                                    value="{{ $op->saldo }}"
                                                                                    max="{{ $op->saldo }}" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col mb-3">
                                                                                <label for="fecha_pago"
                                                                                    class="form-label">Fecha Pago</label>
                                                                                <input type="date" name="fecha_pago"
                                                                                    class="form-control"
                                                                                    value="{{ date('Y-m-d') }}" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col mb-3">
                                                                                <label for="metodo_pago"
                                                                                    class="form-label">Método de Pago</label>
                                                                                <select name="metodo_pago" class="form-select"
                                                                                    required>
                                                                                    <option value="Efectivo">Efectivo</option>
                                                                                    <option value="Transferencia">Transferencia
                                                                                    </option>
                                                                                    <option value="Yape/Plin">Yape/Plin
                                                                                    </option>
                                                                                    <option value="Tarjeta">Tarjeta</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col mb-3">
                                                                                <label for="observaciones"
                                                                                    class="form-label">Observaciones</label>
                                                                                <textarea name="observaciones" class="form-control" rows="2"></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button"
                                                                            class="btn btn-outline-secondary"
                                                                            data-bs-dismiss="modal">Cancelar</button>
                                                                        <button type="submit" class="btn btn-primary">Guardar
                                                                            Pago</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($op->monto_pagado > 0)
                                                    <div class="dropdown d-inline-block">
                                                        <button class="btn btn-sm btn-icon" type="button"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @foreach ($op->pagos as $pago)
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('pasivos.ticket', $pago->id) }}"
                                                                        target="_blank">
                                                                        <i class="bx bx-printer me-1"></i> Ticket
                                                                        ({{ $pago->fecha_pago->format('d/m') }} -
                                                                        {{ $pago->monto }})
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                                <button type="button"
                                                    class="btn btn-sm btn-icon btn-edit-operacion shadow-none"
                                                    data-id="{{ $op->id }}" title="Editar Operación">
                                                    <i class="bx bx-edit text-warning fs-4"></i>
                                                </button>

                                                <a href="{{ route('pasivos.ticket_registro', $op->id) }}" target="_blank"
                                                    class="btn btn-sm btn-icon shadow-none"
                                                    title="Imprimir Comprobante de Registro">
                                                    <i class="bx bx-printer text-primary fs-4"></i>
                                                </a>
                                            @else
                                                {{-- Acciones para adelantos de personal registrados desde Caja --}}
                                                @if ($op->saldo > 0)
                                                    <form action="{{ route('finanzas.saldar-adelanto-personal', $op->_activo_id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning"
                                                            onclick="return confirm('¿Marcar este adelanto como saldado?')">
                                                            <i class="bx bx-check-circle"></i> Saldar
                                                        </button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('finanzas.ticket-personal', $op->_activo_id) }}" target="_blank"
                                                    class="btn btn-sm btn-icon shadow-none"
                                                    title="Imprimir Ticket">
                                                    <i class="bx bx-printer text-primary fs-4"></i>
                                                </a>

                                                <small class="text-muted d-block mt-1"
                                                    title="Registrado desde Caja"><i class="bx bx-store-alt"></i> Caja</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No hay operaciones registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer py-3">
                        {{ $operaciones->links() }}
                    </div>
                @else
                    {{-- VISTA AGRUPADA --}}
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Empresa / Persona</th>
                                    <th>Operaciones</th>
                                    <th>Monto Total</th>
                                    <th>Pagado Total</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($operaciones as $group)
                                    <tr>
                                        <td class="fw-bold">
                                            <i class="bx bx-building-house me-1"></i> {{ $group->empresa_persona }}
                                        </td>
                                        <td><span class="badge bg-label-secondary">{{ $group->cantidad_operaciones }}
                                                registros</span></td>
                                        <td class="fw-bold">S/ {{ number_format($group->total_monto, 2) }}</td>
                                        <td>S/ {{ number_format($group->total_pagado, 2) }}</td>
                                        <td class="text-danger fw-bold">S/ {{ number_format($group->saldo, 2) }}</td>
                                        <td>
                                            @if ($group->saldo > 0)
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalPagoAcumulado{{ $loop->index }}">
                                                    <i class="bx bx-dollar me-1"></i> Pagar Todo
                                                </button>

                                                {{-- Modal Pago Acumulado --}}
                                                <div class="modal fade" id="modalPagoAcumulado{{ $loop->index }}"
                                                    tabindex="-1" aria-hidden="true">

                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Pago Acumulado:
                                                                    {{ $group->empresa_persona }}</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form
                                                                action="{{ route('finanzas_vendedor.pagar_acumulado') }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="empresa_persona"
                                                                    value="{{ $group->empresa_persona }}">
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Monto a Distribuir
                                                                            (Saldo:
                                                                            {{ number_format($group->saldo, 2) }})
                                                                        </label>
                                                                        <input type="number" step="0.01"
                                                                            name="monto" class="form-control"
                                                                            value="{{ $group->saldo }}"
                                                                            max="{{ $group->saldo }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Método de Pago</label>
                                                                        <select name="metodo_pago" class="form-select"
                                                                            required>
                                                                            <option value="Efectivo">Efectivo</option>
                                                                            <option value="Transferencia">Transferencia
                                                                            </option>
                                                                            <option value="Yape/Plin">Yape/Plin</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Fecha Pago</label>
                                                                        <input type="date" name="fecha_pago"
                                                                            class="form-control"
                                                                            value="{{ date('Y-m-d') }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Observaciones</label>
                                                                        <textarea name="observaciones" class="form-control" rows="2"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button"
                                                                        class="btn btn-outline-secondary"
                                                                        data-bs-dismiss="modal">Cerrar</button>
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Registrar Pago</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-success"><i class="bx bx-check-double"></i> Al
                                                    día</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay resumen disponible.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
    </div>
    </div>

    {{-- Modal Registro --}}
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

                        {{-- Tipo de operación --}}
                        <div class="row mb-3">
                            <label for="tipo_operacion" class="col-sm-3 col-form-label fw-semibold">Tipo</label>
                            <div class="col-sm-9">
                                <select class="form-select" id="tipo_operacion" name="tipo_operacion" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option value="compras_credito"
                                        {{ $tipoActivo == 'compras_credito' ? 'selected' : '' }}>
                                        Compras a Crédito
                                    </option>
                                    <option value="adelanto_clientes"
                                        {{ $tipoActivo == 'adelanto_clientes' ? 'selected' : '' }}>
                                        Adelanto Clientes
                                    </option>
                                    <option value="adelanto_personal"
                                        {{ $tipoActivo == 'adelanto_personal' ? 'selected' : '' }}>
                                        Adelantos Personal
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- Empresa / Persona --}}
                        <div class="row mb-3">
                            <label for="empresa_persona" class="col-sm-3 col-form-label fw-semibold">
                                <i class="bx bx-building-house me-1"></i>Empresa / Persona
                            </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="empresa_persona" name="empresa_persona"
                                    placeholder="Nombre de empresa o persona" required>
                                <div class="form-text">Ej: Proveedor ABC, Juan Pérez, etc.</div>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="row mb-3">
                            <label for="nombre" class="col-sm-3 col-form-label fw-semibold">Descripción</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                    placeholder="Concepto o descripción del registro" required>
                            </div>
                        </div>

                        {{-- Monto --}}
                        <div class="row mb-3">
                            <label for="monto" class="col-sm-3 col-form-label fw-semibold">Monto</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" class="form-control" id="monto"
                                        name="monto" placeholder="0.00" required>
                                </div>
                            </div>
                        </div>

                        {{-- Fecha --}}
                        <div class="row mb-3">
                            <label for="fecha_registro" class="col-sm-3 col-form-label fw-semibold">Fecha</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="fecha_registro" name="fecha_registro"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        {{-- Documento --}}
                        <div class="row mb-3">
                            <label for="documento" class="col-sm-3 col-form-label fw-semibold">Documento</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="documento" name="documento"
                                    placeholder="N° Comprobante o Referencia (Opcional)">
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="row mb-3">
                            <label for="observaciones" class="col-sm-3 col-form-label fw-semibold">Observaciones</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Registrar Operación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar --}}
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Operación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditar" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <label for="edit_tipo_operacion" class="col-sm-3 col-form-label fw-semibold">Tipo</label>
                            <div class="col-sm-9">
                                <select class="form-select" id="edit_tipo_operacion" name="tipo_operacion" required>
                                    <option value="compras_credito">Compras a Crédito</option>
                                    <option value="adelanto_clientes">Adelanto Clientes</option>
                                    <option value="adelanto_personal">Adelantos Personal</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="edit_empresa_persona" class="col-sm-3 col-form-label fw-semibold">Empresa /
                                Persona</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="edit_empresa_persona"
                                    name="empresa_persona" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="edit_nombre" class="col-sm-3 col-form-label fw-semibold">Descripción</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="edit_monto" class="col-sm-3 col-form-label fw-semibold">Monto</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" class="form-control" id="edit_monto"
                                        name="monto" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="edit_fecha_registro" class="col-sm-3 col-form-label fw-semibold">Fecha</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="edit_fecha_registro"
                                    name="fecha_registro" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="edit_documento" class="col-sm-3 col-form-label fw-semibold">Documento</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="edit_documento" name="documento">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="edit_observaciones"
                                class="col-sm-3 col-form-label fw-semibold">Observaciones</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Actualizar Operación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if (session('imprimir_pasivo_id'))
                    window.open("{{ route('pasivos.ticket_registro', session('imprimir_pasivo_id')) }}", "_blank");
                @endif

                const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
                const formEditar = document.getElementById('formEditar');

                document.querySelectorAll('.btn-edit-operacion').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');

                        fetch(`{{ url('finanzas-vendedor') }}/${id}/edit`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const op = data.operacion;
                                    formEditar.action =
                                        `{{ url('finanzas-vendedor') }}/${id}/update`;

                                    document.getElementById('edit_tipo_operacion').value = data
                                        .tipo_operacion;
                                    document.getElementById('edit_empresa_persona').value = op
                                        .empresa_persona;
                                    document.getElementById('edit_nombre').value = op.nombre;
                                    document.getElementById('edit_monto').value = op.monto;
                                    document.getElementById('edit_fecha_registro').value = op
                                        .fecha_registro.substring(0, 10);
                                    document.getElementById('edit_documento').value = op
                                        .documento || '';
                                    document.getElementById('edit_observaciones').value = op
                                        .observaciones || '';

                                    modalEditar.show();
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error al cargar datos de la operación');
                            });
                    });
                });
            });
        </script>
    @endpush

@endsection

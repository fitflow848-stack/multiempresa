@extends('layout.app')

@section('content')
    <style>
        .table-compressed th {
            padding: 0.5rem 0.4rem !important;
            font-size: 0.75rem !important;
            text-transform: uppercase;
        }
        .table-compressed td {
            padding: 0.4rem 0.4rem !important;
            vertical-align: middle;
        }
        .btn-xs {
            padding: 0.25rem 0.4rem;
            font-size: 0.7rem;
            line-height: 1;
        }
        .badge {
            text-transform: uppercase;
            font-weight: 600;
        }
        /* Evitar que la tabla se estire innecesariamente */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
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
                    <h5 class="mb-0">{{ $agrupar ? 'Resumen Agrupado por Persona' : 'Historial de Operaciones' }}
                    </h5>
                    <div>
                        <a href="{{ route('finanzas_vendedor.export', ['tipo' => $tipoActivo, 'search' => $search, 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta]) }}"
                            class="btn btn-success me-1">
                            <i class="bx bx-export me-1"></i> Excel
                        </a>
                        @can('finanzas.crear')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                                <i class="bx bx-plus me-1"></i> Registrar
                            </button>
                        @endcan
                    </div>
                </div>

                @cannot('finanzas.crear')
                    <div class="alert alert-warning mb-3">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Modo Solo Lectura:</strong> Solo los usuarios autorizados pueden crear, editar o eliminar registros de finanzas.
                    </div>
                @endcannot

                @if (!$agrupar)
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover table-compressed">
                            <thead>
                                <tr class="text-xs">
                                    <th style="width: 80px">Fecha</th>
                                    <th style="width: 100px">Tipo</th>
                                    <th style="width: 150px">Entidad</th>
                                    <th>Concepto</th>
                                    <th style="width: 90px">Doc.</th>
                                    <th style="width: 90px">Monto</th>
                                    <th style="width: 90px">Pagado</th>
                                    <th style="width: 90px">Saldo</th>
                                    <th style="width: 80px">Estado</th>
                                    <th style="width: 120px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($operaciones as $op)
                                    <tr style="font-size: 0.85rem;">
                                        <td>{{ $op->created_at->format('d/m/y H:i') }}</td>
                                        <td>
                                            @php
                                                $tipoNombreLower = strtolower($op->tipo->nombre);
                                                $tipoDisplay = $op->tipo->nombre;
                                                // Acortar nombres largos
                                                $tipoDisplay = str_replace('Adelantos a Personal', 'Adel. Pers.', $tipoDisplay);
                                                $tipoDisplay = str_replace('Adelantos Personal', 'Adel. Pers.', $tipoDisplay);
                                                $tipoDisplay = str_replace('Compras a Crédito', 'Compras Cred.', $tipoDisplay);
                                                $tipoDisplay = str_replace('Adelanto Clientes', 'Adel. Cli.', $tipoDisplay);
                                                
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
                                            <span class="badge {{ $badgeColor }}" style="font-size: 0.75rem; padding: 0.35em 0.5em;">{{ $tipoDisplay }}</span>
                                            @if(isset($op->metodo_pago) && $op->metodo_pago)
                                                <div style="font-size: 0.7rem;" class="text-muted mt-1">
                                                    <i class="bx bx-credit-card me-1"></i>{{ $op->metodo_pago }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">
                                            {{ \Illuminate\Support\Str::limit($op->empresa_persona ?? '-', 20) }}
                                        </td>
                                        <td class="text-truncate" style="max-width: 120px;" title="{{ $op->nombre }}">{{ $op->nombre }}</td>
                                        <td>{{ $op->documento ?? '-' }}</td>
                                        <td class="fw-bold text-end">S/ {{ number_format($op->monto, 1) }}</td>
                                        <td class="text-end">S/ {{ number_format($op->monto_pagado, 1) }}</td>
                                        <td class="text-danger fw-bold text-end">S/ {{ number_format($op->saldo, 1) }}</td>
                                        <td>
                                            @php
                                                $estadoColor = 'bg-label-secondary';
                                                $estadoName = ucfirst($op->estado);
                                                if ($op->estado == 'pendiente') $estadoColor = 'bg-label-warning';
                                                elseif($op->estado == 'aprobado') $estadoColor = 'bg-label-primary';
                                                elseif($op->estado == 'parcial') $estadoColor = 'bg-label-info';
                                                elseif($op->estado == 'pagado') $estadoColor = 'bg-label-success';
                                            @endphp
                                            <span class="badge {{ $estadoColor }}" style="font-size: 0.7rem; padding: 0.3em 0.4em;">{{ $estadoName }}</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                @if (!isset($op->_es_activo))
                                                    @php
                                                        $tn = strtolower($op->tipo->nombre);
                                                        $esAdelanto = (str_contains($tn, 'adelanto') && str_contains($tn, 'personal')) ||
                                                                      (str_contains($tn, 'adelanto') && str_contains($tn, 'cliente'));
                                                        $esCompra = str_contains($tn, 'compra');
                                                        $mostrarBoton = ($esAdelanto || $esCompra) && $op->saldo > 0;
                                                    @endphp
                                                    @if ($mostrarBoton)
                                                        <button type="button" class="btn btn-xs btn-primary p-1"
                                                            data-bs-toggle="modal" data-bs-target="#modalPagar{{ $op->id }}" title="Registrar Pago">
                                                            <i class="bx bx-dollar-circle"></i>
                                                        </button>
                                                        {{-- ... modal code stays same in terms of logic ... --}}
    @include('finanzas_vendedor.partials.modal_pagar', ['op' => $op, 'esAdelanto' => $esAdelanto])
                                                    @endif

                                                    @can('finanzas.editar')
                                                        <button type="button"
                                                            class="btn btn-xs btn-icon btn-edit-operacion text-warning"
                                                            data-id="{{ $op->id }}" title="Editar">
                                                            <i class="bx bx-edit fs-5"></i>
                                                        </button>

                                                        <form action="{{ route('finanzas_vendedor.destroy', $op->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-icon text-danger"
                                                                title="Eliminar"
                                                                onclick="return confirm('¿Seguro?')">
                                                                <i class="bx bx-trash fs-5"></i>
                                                            </button>
                                                        </form>
                                                    @endcan

                                                    @php
                                                        $printRoute = route('pasivos.ticket_registro', $op->id);
                                                    @endphp
                                                    <a href="{{ $printRoute }}" target="_blank"
                                                        class="btn btn-xs btn-icon text-primary"
                                                        title="Imprimir">
                                                        <i class="bx bx-printer fs-5"></i>
                                                    </a>
                                                    
                                                    @if ($op->monto_pagado > 0)
                                                        <div class="dropdown d-inline-block">
                                                            <button class="btn btn-xs btn-icon p-0" type="button"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-history"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.8rem;">
                                                                @foreach ($op->pagos as $pago)
                                                                    <li>
                                                                        <a class="dropdown-item py-1"
                                                                            href="{{ route('pasivos.ticket', $pago->id) }}"
                                                                            target="_blank">
                                                                            <i class="bx bx-printer me-1"></i> Tkt {{ $pago->fecha_pago->format('d/m') }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @else
                                                    @if ($op->saldo > 0)
                                                        <form
                                                            action="{{ route('finanzas.saldar-adelanto-personal', $op->_activo_id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-warning p-1"
                                                                onclick="return confirm('¿Saldar?')">
                                                                <i class="bx bx-check-circle"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @can('finanzas.editar')
                                                        <button type="button"
                                                            class="btn btn-xs btn-icon btn-edit-operacion text-warning"
                                                            data-id="{{ $op->id }}" title="Editar">
                                                            <i class="bx bx-edit fs-5"></i>
                                                        </button>

                                                        <form action="{{ route('finanzas_vendedor.destroy', $op->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-icon text-danger"
                                                                onclick="return confirm('¿Seguro?')">
                                                                <i class="bx bx-trash fs-5"></i>
                                                            </button>
                                                        </form>
                                                    @endcan

                                                    <a href="{{ route('finanzas.ticket-personal', $op->_activo_id) }}"
                                                        target="_blank" class="btn btn-xs btn-icon text-primary">
                                                        <i class="bx bx-printer fs-5"></i>
                                                    </a>
                                                    <i class="bx bx-store-alt text-muted small" title="Caja"></i>
                                                @endif
                                            </div>
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
                        <table class="table table-hover table-compressed">
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
                                                                    <input type="hidden" name="metodo_pago" value="Efectivo">
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

                        {{-- Método de Pago (Oculto y siempre Efectivo por requerimiento) --}}
                        <input type="hidden" name="metodo_pago" value="Efectivo">

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

                        {{-- Método de Pago (Oculto y siempre Efectivo por requerimiento) --}}
                        <input type="hidden" id="edit_metodo_pago" name="metodo_pago" value="Efectivo">

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
                @if (session('imprimir_adelanto_id'))
                    window.open("{{ route('finanzas.ticket-personal', session('imprimir_adelanto_id')) }}", "_blank");
                @endif

                const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
                const formEditar = document.getElementById('formEditar');

                // Eliminado el manejo de visibilidad de método de pago por requerimiento (siempre Efectivo)

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

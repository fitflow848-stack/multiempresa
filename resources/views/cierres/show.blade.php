@extends('layout.app')

@section('title', 'Detalle de Cierre de Caja')

@section('content')
    <style>
        /* Contenedor de tabla con scroll interno */
        .table-responsive-scroll {
            max-height: 500px;
            /* Altura fija para activar el scroll */
            overflow-y: auto;
            border-bottom: 1px solid #dee2e6;
        }

        /* Encabezado fijo para el scroll */
        .table-responsive-scroll thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            box-shadow: inset 0 -1px 0 #dee2e6;
        }

        .label-custom {
            font-size: 0.72rem;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .display-value {
            font-size: 1.15rem;
            font-weight: 700;
        }

        #fab-add-operacion {
            position: fixed;
            right: 25px;
            bottom: 25px;
            z-index: 1050;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .bg-summary {
            background-color: #f1f4f8;
            border-radius: 8px;
        }

        .form-control-sm {
            font-size: 0.85rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .arqueo-modal-content {
                width: 95% !important;
                max-height: 90vh;
                overflow-y: auto;
            }
            .arqueo-flex {
                flex-direction: column !important;
            }
            .arqueo-column {
                width: 100% !important;
                margin-bottom: 15px;
            }
            .display-value {
                font-size: 1rem;
            }
            .header-info-mobile {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
        }
    </style>

    <div id="cierre-container" class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3 header-info-mobile">
            <div>
                <h1 class="h4 mb-0 text-dark">{{ isset($isTesoreria) && $isTesoreria ? 'Arqueo de Tesorería' : 'Cierre de Caja' }} #{{ $cierre->id }}</h1>
                <p class="text-muted small mb-0">Usuario: {{ auth()->user()->name ?? 'Administrador' }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-primary px-3">
                    <i class="fas fa-cash-register me-1"></i> Volver TPV
                </a>
                <a href="{{ route('cierre-caja.index', isset($isTesoreria) && $isTesoreria ? ['tipo' => 'tesoreria'] : []) }}" class="btn btn-sm btn-outline-secondary px-3">
                    <i class="fas fa-chevron-left me-1"></i> Volver
                </a>
                @if ($cierre->fecha_cierre)
                    <span class="badge bg-dark d-flex align-items-center px-3">
                        <i class="fas fa-lock me-2"></i> CERRADA:
                        {{ \Carbon\Carbon::parse($cierre->fecha_cierre)->format('d/m/Y H:i') }}
                    </span>
                @else
                    <button id="btn-cerrar-caja" class="btn btn-sm btn-success px-3 shadow-sm">
                        <i class="fas fa-save me-1"></i> Finalizar Cierre
                    </button>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Balances de Efectivo</h6>

                        <div class="mb-3">
                            <label class="label-custom">Saldo Inicial Apertura</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">S/</span>
                                <input type="number" step="0.01" id="saldo_inicial"
                                    class="form-control border-start-0 fw-bold" value="{{ $cierre->monto_apertura }}">
                            </div>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="label-custom text-success">(+) Ingresos</label>
                                <input type="number" id="ingresos"
                                    class="form-control form-control-sm border-success bg-light"
                                    value="{{ $cierre->ingresos }}" readonly>
                                <input type="hidden" id="ingresos_digital" value="{{ $cierre->ingresos_digital ?? 0 }}">
                            </div>
                            <div class="col-6">
                                <label class="label-custom text-danger">(-) Gastos</label>
                                <input type="number" id="gastos"
                                    class="form-control form-control-sm border-danger bg-light"
                                    value="{{ $cierre->egresos }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="label-custom text-primary">(+) Aportes</label>
                                <input type="number" id="aportaciones"
                                    class="form-control form-control-sm border-primary bg-light"
                                    value="{{ $cierre->aportaciones }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="label-custom text-warning">(-) Retiros</label>
                                <input type="number" id="sustracciones"
                                    class="form-control form-control-sm border-warning bg-light"
                                    value="{{ $cierre->sustracciones }}" readonly>
                            </div>
                            <div class="col-12 mt-2">
                                <label class="label-custom text-dark" style="color: #6b2e51 !important;">RECAUDADO POR COBRAR (CRÉDITO)</label>
                                <input type="text" class="form-control form-control-sm border-dark bg-light fw-bold"
                                    value="S/ {{ number_format($porCobrar ?? 0, 2) }}" readonly style="border-color: #6b2e51 !important; color: #6b2e51 !important;">
                            </div>
                        </div>

                        <div class="bg-summary p-3 mb-4 shadow-sm border border-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="label-custom">Total Teórico:</span>
                                <span id="display_teorico" class="display-value text-dark">S/ 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="label-custom">Efectivo en Caja (Real):</span>
                                <span id="display_real" class="display-value text-dark">S/ 0.00</span>
                            </div>
                            <div class="border-top pt-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span id="label_descuadre" class="label-custom">Diferencia:</span>
                                    <span id="display_descuadre" class="display-value">S/ 0.00</span>
                                </div>
                                <div id="warning_descuadre" class="alert alert-danger py-1 px-2 small mt-2 mb-0"
                                    style="display: none; font-size: 0.75rem;">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Descuadre detectado
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="label-custom fw-bold text-primary">Conteo Real en Caja (Efectivo)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white border-primary">S/</span>
                                <input type="number" step="0.01" id="cierre_caja"
                                    class="form-control form-control-lg border-primary fw-bold"
                                    value="{{ $cierre->monto_cierre }}" placeholder="0.00">
                                @if(!$cierre->fecha_cierre)
                                <button class="btn btn-outline-primary" type="button" onclick="cuadrarConTeorico()" title="Cuadrar con monto teórico">
                                    <i class="fas fa-magic"></i>
                                </button>
                                @endif
                            </div>
                        </div>

                        @if (!empty($ingresosPorMetodo))
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-secondary">Otros Métodos de Pago (No Efectivo)</h6>
                                <ul class="list-group list-group-flush border rounded shadow-sm">
                                    @foreach ($ingresosPorMetodo as $metodo => $totalMetodo)
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-light">
                                            <span class="label-custom fw-bold text-dark"><i
                                                    class="fas fa-wallet me-1 text-secondary"></i>
                                                {{ $metodo }}</span>
                                            <span class="fw-bold text-success">S/
                                                {{ number_format($totalMetodo, 2) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-0">
                            <label class="label-custom">Observaciones Finales</label>
                            <textarea name="observaciones" class="form-control form-control-sm" rows="3"
                                placeholder="Notas adicionales sobre el cierre...">{{ $cierre->observaciones }}</textarea>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-star me-1"></i> Operaciones Especiales</h6>
                        <div class="d-grid gap-2">
                             <button type="button" class="btn btn-outline-info btn-sm text-start" onclick="openModalAdelanto('personal')">
                                <i class="fas fa-user-tag me-2"></i> Adelanto a Personal
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm text-start" onclick="openModalAdelanto('cliente')">
                                <i class="fas fa-user-clock me-2"></i> Adelanto de Cliente
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="openModalAdelanto('compra_credito')">
                                <i class="fas fa-file-invoice-dollar me-2"></i> Compra a Crédito
                            </button>

                            @if(!$cierre->fecha_cierre)
                            <hr class="my-1">
                            {{-- Desde CAJA normal: el cajero puede enviar efectivo a la bóveda o solicitar desde bóveda --}}
                            @if(!$isTesoreria)
                            <button type="button" class="btn btn-warning btn-sm text-start fw-bold"
                                    onclick="abrirModalCajaABoveda()">
                                <i class="fas fa-vault me-2"></i> Pase a Bóveda
                            </button>
                            <button type="button" class="btn btn-info btn-sm text-start fw-bold text-white mt-1"
                                    onclick="abrirModalCajaDesdeBoveda()">
                                <i class="fas fa-hand-holding-dollar me-2"></i> Recibir de Bóveda
                            </button>
                            @endif

                            {{-- Desde BÓVEDA: el admin envía efectivo a una caja específica --}}
                            @if($isTesoreria)
                            <button type="button" class="btn btn-dark btn-sm text-start fw-bold"
                                    onclick="abrirModalBovedaACaja()">
                                <i class="fas fa-money-bill-transfer me-2"></i> Pase a Caja
                            </button>
                            @endif
                            @endif
                        </div>

                        <input type="hidden" id="teorico_cierre" value="{{ $cierre->teorico_cierre }}">
                        <input type="hidden" id="descuadre" value="{{ $cierre->descuadre }}">
                    </div>

                    <div class="card-footer bg-white border-top-0 d-flex gap-2 pb-3">
                        <button id="btn-registrar-arqueo" class="btn btn-sm btn-success flex-grow-1">
                            <i class="fas fa-calculator me-1"></i> Arqueo Actual
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">Movimientos de la Jornada</h6>
                        <span id="movimientos-count" class="badge bg-secondary rounded-pill">{{ count($movimientos) }}
                            registros</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive-scroll">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-secondary">
                                        <th class="ps-3 py-3" style="width: 100px;">Fecha/Hora</th>
                                        <th style="width: 150px;">Operación</th>
                                        <th>Concepto / Referencia</th>
                                        <th style="width: 120px;">Método Pago</th>
                                        <th class="text-end" style="width: 100px;">Importe</th>
                                        @if (!$cierre->fecha_cierre)
                                            <th class="text-center" style="width: 80px;">Acciones</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="movimientos-tbody">
                                    @forelse ($movimientos as $movimiento)
                                        <tr data-id="{{ $movimiento->id_movimiento }}"
                                            data-origen="{{ $movimiento->origen_movimiento ?? '' }}">
                                            <td class="ps-3 py-3 small text-muted">
                                                {{ \Carbon\Carbon::parse($movimiento->fecha_emision)->format('d/m H:i') }}
                                            </td>
                                            <td>
                                                @php
                                                    $tipoOp = strtolower($movimiento->operacion);
                                                    $badgeClass = match (true) {
                                                        str_contains($tipoOp, 'ingreso') ||
                                                            str_contains($tipoOp, 'venta')
                                                            => 'bg-success',
                                                        str_contains($tipoOp, 'gasto') ||
                                                            str_contains($tipoOp, 'compra')
                                                            => 'bg-danger',
                                                        str_contains($tipoOp, 'aporte') => 'bg-info',
                                                        str_contains($tipoOp, 'sustraccion') ||
                                                            str_contains($tipoOp, 'retiro')
                                                            => 'bg-warning text-dark',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} font-monospace"
                                                    style="font-size: 0.65rem;">
                                                    {{ strtoupper($movimiento->operacion) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold mb-0" style="font-size: 0.85rem;">
                                                    {{ $movimiento->concepto }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    {{ $movimiento->cliente_nombre ?? '---' }} | <i
                                                        class="fas fa-user-circle"></i> {{ $movimiento->usuario }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-light text-dark border">{{ $movimiento->metodo_pago ?? '---' }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-dark">
                                                S/ {{ number_format($movimiento->importe, 2) }}
                                            </td>
                                            @if (!$cierre->fecha_cierre)
                                                <td class="text-center">
                                                    @if (isset($movimiento->origen_movimiento) &&
                                                            $movimiento->origen_movimiento === 'operacion' &&
                                                            ($movimiento->operacion ?? '') !== 'Cobro Deuda')
                                                        @can('cajas.ajustar')
                                                            <button
                                                                class="btn btn-sm btn-outline-primary px-2 py-0 border-0 fs-6 edit-operacion"
                                                                data-id="{{ $movimiento->id_movimiento }}"
                                                                data-tipo="{{ $movimiento->tipo_movimiento }}"
                                                                data-partida="{{ $movimiento->operacion }}"
                                                                data-concepto="{{ $movimiento->concepto }}"
                                                                data-metodo="{{ $movimiento->metodo_pago }}"
                                                                data-importe="{{ $movimiento->importe }}" title="Editar">
                                                                <i class="bx bx-edit"></i>
                                                            </button>
                                                        @endcan
                                                        @can('operaciones_caja.eliminar')
                                                            <button
                                                                class="btn btn-sm btn-outline-danger px-2 py-0 border-0 fs-6 delete-operacion"
                                                                data-id="{{ $movimiento->id_movimiento }}" title="Eliminar">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        @endcan
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <img src="https://cdn-icons-png.flaticon.com/512/4076/4076403.png"
                                                    width="50" class="opacity-25 mb-3"><br>
                                                <span class="text-muted">No se encontraron movimientos el día de
                                                    hoy.</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!$cierre->fecha_cierre)
        <button id="fab-add-operacion" class="btn btn-primary shadow-lg d-flex align-items-center justify-content-center"
            title="Nueva Operación">
            <i class="bx bx-plus fa-lg"></i>
        </button>
    @endif

    <!-- Modal Adelantos / Finanzas Especiales -->
    <div id="modal-adelanto" class="modal" tabindex="-1" style="background: rgba(0,0,0,0.5); display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white" id="adelanto-header">
                    <h6 class="modal-title" id="adelanto-title">Registrar Adelanto</h6>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModals()"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="adelanto_type">
                    <div class="row g-3">
                        <div class="col-12" id="div-person">
                            <label class="label-custom" id="label-person">Nombre del Personal</label>
                            <input id="ad_nombre" class="form-control form-control-sm" placeholder="Ingrese nombre...">
                        </div>
                        <div class="col-12" id="div-empresa" style="display:none;">
                            <label class="label-custom">Empresa / Proveedor</label>
                            <input id="ad_empresa_persona" class="form-control form-control-sm" placeholder="Nombre de la empresa...">
                        </div>
                        <div class="col-md-6">
                            <label class="label-custom">Monto (S/)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">S/</span>
                                <input id="ad_monto" type="number" step="0.01" class="form-control fw-bold" value="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="label-custom">Fecha</label>
                            <input id="ad_fecha" type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12" id="div-doc" style="display:none;">
                            <label class="label-custom">Nro Documento / Referencia</label>
                            <input id="ad_documento" class="form-control form-control-sm" placeholder="Ej: Recibo-001">
                        </div>
                        <div class="col-12">
                            <label class="label-custom">Observaciones</label>
                            <textarea id="ad_observaciones" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-sm btn-light px-4" onclick="closeModals()">Cancelar</button>
                    <button id="btn-save-adelanto" class="btn btn-sm btn-primary px-4">Registrar y Ver Ticket</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-operacion" class="modal" tabindex="-1" style="background: rgba(0,0,0,0.5); display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-dark text-white">
                    <h6 class="modal-title">Registrar Operación Manual</h6>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModals()"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="op_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="label-custom">Tipo de Movimiento</label>
                            <select id="op_tipo" class="form-select form-select-sm">
                                <option value="ingreso">Ingreso (+)</option>
                                <option value="gasto">Gasto (-)</option>
                                <option value="aportacion">Aportación (+)</option>
                                <option value="sustraccion">Sustracción (-)</option>
                                <option value="transferencia_boveda">🏦 Transferir a Tesorería (-)</option>
                                <option value="pase_banco">🏦 Pase a Banco (-)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="label-custom">Partida</label>
                            <div class="input-group input-group-sm">
                                <select id="op_partida" class="form-select"></select>
                                <button class="btn btn-outline-secondary"
                                    onclick="document.getElementById('modal-add-partida').style.display='block'">+</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="label-custom">Concepto</label>
                            <input id="op_concepto" class="form-control form-control-sm"
                                placeholder="Describa el motivo...">
                        </div>
                        <div class="col-md-12">
                            <label class="label-custom">Importe (S/)</label>
                            <input id="op_importe" type="number" step="0.01"
                                class="form-control form-control-sm text-center fw-bold" value="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-sm btn-light px-4" onclick="closeModals()">Cancelar</button>
                    <button id="op_save" class="btn btn-sm btn-primary px-4">Guardar Registro</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-add-partida" class="modal" tabindex="-1"
        style="background: rgba(0,0,0,0.6); display: none; z-index: 1100;">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body p-4 text-center">
                    <h6 class="fw-bold mb-3">Nueva Partida</h6>
                    <input id="pa_nombre" class="form-control form-control-sm mb-3" placeholder="Nombre de partida">
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn btn-sm btn-link text-decoration-none"
                            onclick="document.getElementById('modal-add-partida').style.display='none'">Cerrar</button>
                        <button id="pa_save" class="btn btn-sm btn-dark px-3">Crear</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Arqueo: conteo de monedas y billetes -->
    <div id="modal-arqueo"
        style="display:none; position: fixed; inset:0; background: rgba(0,0,0,0.5); z-index:3000; align-items:center; justify-content:center;">
        <div class="arqueo-modal-content" style="background:#fff; padding:18px; width:760px; border-radius:6px; max-width: 95%;">
            <h5 class="mb-3 text-center">Registrar Arqueo de Caja</h5>
            <div class="arqueo-flex" style="display:flex; gap:20px;">
                <div class="arqueo-column" style="flex:1;">
                    <h6>Monedas</h6>
                    <div>
                        <label> S/5.00: <input type="number" class="m-count form-control form-control-sm" data-value="5"
                                value="0" min="0"></label>
                        <label> S/2.00: <input type="number" class="m-count form-control form-control-sm" data-value="2"
                                value="0" min="0"></label>
                        <label> S/1.00: <input type="number" class="m-count form-control form-control-sm" data-value="1"
                                value="0" min="0"></label>
                        <label> S/0.50: <input type="number" class="m-count form-control form-control-sm"
                                data-value="0.5" value="0" min="0"></label>
                        <label> S/0.20: <input type="number" class="m-count form-control form-control-sm"
                                data-value="0.2" value="0" min="0"></label>
                        <label> S/0.10: <input type="number" class="m-count form-control form-control-sm"
                                data-value="0.1" value="0" min="0"></label>
                    </div>
                </div>
                <div class="arqueo-column" style="flex:1;">
                    <h6>Billetes</h6>
                    <div>
                        <label> S/200: <input type="number" class="b-count form-control form-control-sm"
                                data-value="200" value="0" min="0"></label>
                        <label> S/100: <input type="number" class="b-count form-control form-control-sm"
                                data-value="100" value="0" min="0"></label>
                        <label> S/50: <input type="number" class="b-count form-control form-control-sm" data-value="50"
                                value="0" min="0"></label>
                        <label> S/20: <input type="number" class="b-count form-control form-control-sm" data-value="20"
                                value="0" min="0"></label>
                        <label> S/10: <input type="number" class="b-count form-control form-control-sm" data-value="10"
                                value="0" min="0"></label>

                    </div>
                </div>
                <div class="arqueo-column" style="width:220px;">
                    <h6>Totales</h6>
                    <div>Monedas: S/ <span id="total-monedas">0.00</span></div>
                    <div>Billetes: S/ <span id="total-billetes">0.00</span></div>
                    <div class="border-top mt-2 pt-2">
                        <div style="font-weight:bold; font-size:1.1rem;">TOTAL CAJA: S/ <span id="total-caja">0.00</span>
                        </div>
                        <div class="small text-muted mt-1">Total Teórico: S/ <span id="modal-arqueo-teorico">0.00</span>
                        </div>
                        <div id="modal-arqueo-diff-container" class="small mt-1" style="font-weight:bold;">
                            Diferencia: S/ <span id="modal-arqueo-diferencia">0.00</span>
                        </div>
                    </div>
                    <div style="margin-top:12px;">
                        <label>Notas:<br>
                            <textarea id="arqueo-notas" class="form-control form-control-sm" style="width:100%; height:80px;"></textarea>
                        </label>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button id="btn-close-arqueo" class="btn btn-sm btn-light">Cancelar</button>
                <button id="btn-save-arqueo" class="btn btn-sm btn-primary">Registrar Arqueo</button>
            </div>
        </div>
    </div>

    <script>
        function openModalAdelanto(type) {
            document.getElementById('adelanto_type').value = type;
            const header = document.getElementById('adelanto-header');
            const title = document.getElementById('adelanto-title');
            const divEmpresa = document.getElementById('div-empresa');
            const divDoc = document.getElementById('div-doc');
            const labelPerson = document.getElementById('label-person');

            // Reset
            divEmpresa.style.display = 'none';
            divDoc.style.display = 'none';
            header.className = 'modal-header text-white';

            if (type === 'personal') {
                title.innerText = 'Registrar Adelanto a Personal';
                labelPerson.innerText = 'Nombre del Personal';
                header.classList.add('bg-info');
            } else if (type === 'cliente') {
                title.innerText = 'Registrar Adelanto de Cliente';
                labelPerson.innerText = 'Nombre del Cliente';
                header.classList.add('bg-success');
            } else if (type === 'compra_credito') {
                title.innerText = 'Registrar Compra a Crédito';
                labelPerson.innerText = 'Concepto / Referencia';
                divEmpresa.style.display = 'block';
                divDoc.style.display = 'block';
                header.classList.add('bg-secondary');
            }

            document.getElementById('modal-adelanto').style.display = 'block';
        }

        // Handler para guardar adelantos
        document.getElementById('btn-save-adelanto')?.addEventListener('click', async () => {
            const type = document.getElementById('adelanto_type').value;
            const payload = {
                nombre: document.getElementById('ad_nombre').value,
                monto: parseFloat(document.getElementById('ad_monto').value) || 0,
                fecha_registro: document.getElementById('ad_fecha').value,
                observaciones: document.getElementById('ad_observaciones').value,
                cierre_caja_id: cierreId
            };

            if (type === 'compra_credito') {
                payload.empresa_persona = document.getElementById('ad_empresa_persona').value;
                payload.documento = document.getElementById('ad_documento').value;
            }

            if (!payload.nombre || payload.monto <= 0) {
                alert('Por favor complete los campos obligatorios.');
                return;
            }

            let url = "";
            let ticketUrl = "";
            if (type === 'personal') {
                url = "{{ route('finanzas.adelanto-personal') }}";
                ticketUrl = "/finanzas-especiales/ticket-personal/";
            } else if (type === 'cliente') {
                url = "{{ route('finanzas.adelanto-cliente') }}";
                ticketUrl = "/finanzas-especiales/ticket-pasivo/";
            } else if (type === 'compra_credito') {
                url = "{{ route('finanzas.compra-credito') }}";
                ticketUrl = "/finanzas-especiales/ticket-pasivo/";
            }

            const btn = document.getElementById('btn-save-adelanto');
            btn.disabled = true;
            btn.innerText = 'Procesando...';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    window.open(ticketUrl + data.id, '_blank');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo registrar la operación.'));
                }
            } catch (err) {
                alert('Ocurrió un error de red.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Registrar y Ver Ticket';
            }
        });

        const isCajaClosed = {{ $cierre->fecha_cierre ? 'true' : 'false' }};
        const cierreId = {{ $cierre->id }};

        function closeModals() {
            document.getElementById('modal-operacion').style.display = 'none';
            document.getElementById('modal-add-partida').style.display = 'none';
            document.getElementById('modal-adelanto').style.display = 'none';
            if (document.getElementById('modal-arqueo')) document.getElementById('modal-arqueo').style.display = 'none';
        }

        function recalcular() {
            const getVal = (id) => parseFloat(document.getElementById(id).value) || 0;

            const saldoIni = getVal('saldo_inicial');
            const ingresos = getVal('ingresos');
            const ingresosDigital = getVal('ingresos_digital');
            const gastos = getVal('gastos');
            const aportes = getVal('aportaciones');
            const sustrac = getVal('sustracciones');
            const cierreReal = getVal('cierre_caja');

            const teorico = saldoIni + (ingresos - ingresosDigital) - gastos + aportes - sustrac;
            const diferencia = cierreReal - teorico;

            // Actualizar displays
            document.getElementById('teorico_cierre').value = teorico.toFixed(2);
            document.getElementById('display_teorico').innerText = 'S/ ' + teorico.toLocaleString('en-US', {
                minimumFractionDigits: 2
            });
            document.getElementById('display_real').innerText = 'S/ ' + cierreReal.toLocaleString('en-US', {
                minimumFractionDigits: 2
            });

            const descText = document.getElementById('display_descuadre');
            const descLabel = document.getElementById('label_descuadre');
            const warningDiv = document.getElementById('warning_descuadre');

            descText.innerText = 'S/ ' + Math.abs(diferencia).toLocaleString('en-US', {
                minimumFractionDigits: 2
            });

            if (Math.abs(diferencia) <= 0.01) {
                descLabel.innerText = 'DIFERENCIA:';
                descText.className = 'display-value text-success';
                warningDiv.style.display = 'none';
            } else if (diferencia < 0) {
                descLabel.innerText = 'FALTANTE:';
                descText.className = 'display-value text-danger';
                warningDiv.style.display = 'block';
                warningDiv.className = 'alert alert-danger py-1 px-2 small mt-2 mb-0';
                warningDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> ¡Atención! Hay un faltante en caja.';
            } else if (diferencia > 0) {
                descLabel.innerText = 'SOBRANTE:';
                descText.className = 'display-value text-warning';
                warningDiv.style.display = 'block';
                warningDiv.className = 'alert alert-warning py-1 px-2 small mt-2 mb-0';
                warningDiv.innerHTML =
                    '<i class="fas fa-exclamation-triangle me-1"></i> ¡Atención! Hay un sobrante en caja.';
            }
        }

        // Event Listeners
        document.getElementById('op_tipo').addEventListener('change', function(e) {
            const partidaEl = document.getElementById('op_partida');
            const conceptoEl = document.getElementById('op_concepto');

            if (e.target.value === 'transferencia_boveda') {
                partidaEl.value = '';
                partidaEl.disabled = true;
                if (!conceptoEl.value) conceptoEl.value = 'Exceso de caja transferido';
            } else if (e.target.value === 'pase_banco') {
                partidaEl.value = '';
                partidaEl.disabled = true;
                if (!conceptoEl.value) conceptoEl.value = 'Dinero digital transferido a banco';
            } else {
                partidaEl.disabled = false;
            }
        });

        document.getElementById('fab-add-operacion')?.addEventListener('click', () => {
            document.getElementById('op_id').value = '';
            document.getElementById('op_tipo').value = 'ingreso';
            document.getElementById('op_partida').value = '';
            document.getElementById('op_concepto').value = '';
            document.getElementById('op_importe').value = '0.00';
            document.querySelector('#modal-operacion .modal-title').innerText = 'Registrar Operación Manual';
            document.getElementById('modal-operacion').style.display = 'block';
        });

        document.querySelectorAll('.edit-operacion').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const btnEl = e.currentTarget;
                document.getElementById('op_id').value = btnEl.getAttribute('data-id');
                document.getElementById('op_tipo').value = btnEl.getAttribute('data-tipo');
                document.getElementById('op_concepto').value = btnEl.getAttribute('data-concepto');

                document.getElementById('op_importe').value = parseFloat(btnEl.getAttribute('data-importe'))
                    .toFixed(2);

                // Need to correctly set 'op_partida' if it is in the select options or fetch them.
                const partida = btnEl.getAttribute('data-partida');
                const partidaSelect = document.getElementById('op_partida');
                let found = false;
                for (let i = 0; i < partidaSelect.options.length; i++) {
                    if (partidaSelect.options[i].value === partida) {
                        partidaSelect.selectedIndex = i;
                        found = true;
                        break;
                    }
                }
                if (!found && partida) {
                    const opt = document.createElement('option');
                    opt.value = partida;
                    opt.textContent = partida;
                    partidaSelect.appendChild(opt);
                    partidaSelect.value = partida;
                }

                document.querySelector('#modal-operacion .modal-title').innerText =
                    'Editar Operación Manual';
                document.getElementById('modal-operacion').style.display = 'block';
            });
        });

        document.querySelectorAll('.delete-operacion').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                if (!confirm(
                        '¿Desea eliminar esta operación? Esta acción alterará los totales de la caja.'))
                    return;
                const id = e.currentTarget.getAttribute('data-id');
                try {
                    const res = await fetch(`/operaciones-caja/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    if (res.ok) location.reload();
                    else {
                        const errorData = await res.json().catch(() => ({}));
                        alert('Error al eliminar: ' + (errorData.message || 'No se pudo eliminar la operación (Error ' + res.status + ')'));
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error de red al intentar eliminar.');
                }
            });
        });

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', recalcular);
        });

        // Guardar Operación
        document.getElementById('op_save').addEventListener('click', async () => {
            const opId = document.getElementById('op_id').value;
            const payload = {
                tipo: document.getElementById('op_tipo').value,
                partida: document.getElementById('op_partida').value,
                concepto: document.getElementById('op_concepto').value,
                metodo_pago: 'Efectivo',
                importe: parseFloat(document.getElementById('op_importe').value) || 0,
                cierre_caja_id: cierreId
            };

            if (!payload.concepto || payload.importe <= 0) {
                alert('Por favor ingrese un concepto e importe válido.');
                return;
            }

            const btn = document.getElementById('op_save');
            btn.disabled = true;
            btn.innerText = 'Guardando...';

            const url = opId ? `/operaciones-caja/${opId}` : '/operaciones-caja';
            const method = opId ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    // Si es pase a banco, registrar también en módulo de bancos
                    if (payload.tipo === 'pase_banco') {
                        try {
                            const bancoRes = await fetch('{{ route('bancos.pase-caja-banco') }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({
                                    monto: payload.importe,
                                    concepto: payload.concepto,
                                    cierre_caja_id: cierreId
                                })
                            });
                            const bancoData = await bancoRes.json();
                            if (!bancoRes.ok || !bancoData.success) {
                                alert('⚠️ La operación de caja se registró, pero el pase a banco falló: ' + (bancoData.message || 'Error desconocido'));
                            }
                        } catch (e) {
                            console.error('Error registrando en banco:', e);
                            alert('⚠️ La operación de caja se registró, pero hubo un error al registrar en banco.');
                        }
                    }
                    location.reload();
                } else {
                    const errorData = await res.json().catch(() => ({}));
                    alert('Error al guardar: ' + (errorData.message || 'No se pudo guardar la operación.'));
                    btn.disabled = false;
                    btn.innerText = 'Guardar Registro';
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al intentar guardar.');
                btn.disabled = false;
                btn.innerText = 'Guardar Registro';
            }
        });

        // Cerrar Caja
        document.getElementById('btn-cerrar-caja')?.addEventListener('click', async () => {
            if (!confirm('¿Desea finalizar el cierre de caja? Esta acción no se puede deshacer.')) return;

            const payload = {
                monto_apertura: parseFloat(document.getElementById('saldo_inicial').value),
                monto_cierre: parseFloat(document.getElementById('cierre_caja').value),
                ingresos: parseFloat(document.getElementById('ingresos').value),
                egresos: parseFloat(document.getElementById('gastos').value),
                aportaciones: parseFloat(document.getElementById('aportaciones').value),
                sustracciones: parseFloat(document.getElementById('sustracciones').value),
                observaciones: document.querySelector('textarea[name="observaciones"]').value
            };

            const btn = document.getElementById('btn-cerrar-caja');
            btn.disabled = true;
            btn.innerText = 'Cerrando...';

            try {
                const res = await fetch("{{ route('cierre-caja.close', $cierre->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    alert('Caja cerrada exitosamente.');
                    location.reload();
                } else {
                    const errorData = await res.json().catch(() => ({}));
                    alert('Error al cerrar caja: ' + (errorData.message || 'No se pudo cerrar la caja.'));
                    btn.disabled = false;
                    btn.innerText = 'Finalizar Cierre de Caja';
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al intentar cerrar caja.');
                btn.disabled = false;
                btn.innerText = 'Finalizar Cierre de Caja';
            }
        });

        // Cargar partidas al inicio
        async function loadPartidas() {
            try {
                const res = await fetch("{{ route('partidas.index') }}");
                const data = await res.json();
                const select = document.getElementById('op_partida');
                select.innerHTML = '<option value="">Elegir...</option>';
                data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.nombre;
                    opt.textContent = p.nombre;
                    select.appendChild(opt);
                });
            } catch (e) {}
        }

        function cuadrarConTeorico() {
            const teorico = parseFloat(document.getElementById('teorico_cierre').value) || 0;
            document.getElementById('cierre_caja').value = teorico.toFixed(2);
            recalcular();
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadPartidas();
            recalcular();

            // Si es una sesión abierta y el cierre está en 0, sugerir el teórico
            if (!isCajaClosed && (parseFloat(document.getElementById('cierre_caja').value) || 0) === 0) {
                // Solo si el teórico es > 0
                const t = parseFloat(document.getElementById('teorico_cierre').value) || 0;
                if (t > 0) {
                    document.getElementById('cierre_caja').value = t.toFixed(2);
                    recalcular();
                }
            }

            // Guardar nueva partida
            document.getElementById('pa_save').addEventListener('click', async () => {
                const nombre = document.getElementById('pa_nombre').value.trim();

                if (!nombre) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Por favor ingrese un nombre para la partida.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }

                const btn = document.getElementById('pa_save');
                btn.disabled = true;
                btn.innerText = 'Creando...';

                try {
                    const res = await fetch("{{ route('partidas.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            nombre: nombre
                        })
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        Swal.fire({
                            title: '¡Partida creada!',
                            text: `La partida "${nombre}" ha sido creada exitosamente.`,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Cerrar modal y limpiar
                        document.getElementById('modal-add-partida').style.display = 'none';
                        document.getElementById('pa_nombre').value = '';

                        // Recargar partidas y seleccionar la nueva
                        await loadPartidas();
                        document.getElementById('op_partida').value = nombre;
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'No se pudo crear la partida.',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor.',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                } finally {
                    btn.disabled = false;
                    btn.innerText = 'Crear';
                }
            });

            // Inicializar handlers de Arqueo
            (function() {
                const modal = document.getElementById('modal-arqueo');
                const btnOpen = document.getElementById('btn-registrar-arqueo');
                const btnClose = document.getElementById('btn-close-arqueo');
                const btnSave = document.getElementById('btn-save-arqueo');

                function calcTotals() {
                    let totalM = 0;
                    document.querySelectorAll('.m-count').forEach(inp => {
                        const val = parseFloat(inp.getAttribute('data-value')) || 0;
                        const cnt = parseFloat(inp.value) || 0;
                        totalM += val * cnt;
                    });
                    let totalB = 0;
                    document.querySelectorAll('.b-count').forEach(inp => {
                        const val = parseFloat(inp.getAttribute('data-value')) || 0;
                        const cnt = parseFloat(inp.value) || 0;
                        totalB += val * cnt;
                    });
                    const totalCaja = totalM + totalB;
                    document.getElementById('total-monedas').innerText = totalM.toFixed(2);
                    document.getElementById('total-billetes').innerText = totalB.toFixed(2);
                    document.getElementById('total-caja').innerText = totalCaja.toFixed(2);

                    // Diferencia con el teórico
                    const teorico = parseFloat(document.getElementById('teorico_cierre').value) || 0;
                    const diff = totalCaja - teorico;

                    document.getElementById('modal-arqueo-teorico').innerText = teorico.toFixed(2);
                    const diffEl = document.getElementById('modal-arqueo-diferencia');
                    const diffContainer = document.getElementById('modal-arqueo-diff-container');

                    diffEl.innerText = Math.abs(diff).toFixed(2);
                    
                    if (Math.abs(diff) <= 0.01) {
                        diffContainer.style.color = 'green';
                        diffContainer.innerHTML = 'Caja Cuadrada <i class="fas fa-check-circle"></i> <span id="modal-arqueo-diferencia" style="display:none">0.00</span>';
                    } else if (diff < 0) {
                        diffContainer.style.color = 'red';
                        diffContainer.innerHTML = 'Faltante: S/ <span id="modal-arqueo-diferencia">' + Math.abs(diff).toFixed(2) + '</span>';
                    } else {
                        diffContainer.style.color = 'blue';
                        diffContainer.innerHTML = 'Sobrante: S/ <span id="modal-arqueo-diferencia">' + diff.toFixed(2) + '</span>';
                    }
                }

                document.querySelectorAll('.m-count, .b-count').forEach(i => i.addEventListener('input',
                    calcTotals));

                btnOpen?.addEventListener('click', async () => {
                    // Abrir modal y precargar datos si existe arqueo
                    modal.style.display = 'flex';
                    calcTotals();
                    window.currentArqueoId = null;
                    try {
                        const resp = await fetch(`{{ url('/arqueo') }}?cierre_id=${cierreId}`);
                        if (!resp.ok) throw new Error('no ok');
                        const data = await resp.json();
                        if (data.found && data.arqueo) {
                            const a = data.arqueo;
                            // guardar id para que el save haga update
                            window.currentArqueoId = a.id;
                            // llenar inputs
                            Object.keys(a.monedas || {}).forEach(k => {
                                const inp = document.querySelector('.m-count[data-value="' +
                                    k + '"]');
                                if (inp) inp.value = a.monedas[k];
                            });
                            Object.keys(a.billetes || {}).forEach(k => {
                                const inp = document.querySelector('.b-count[data-value="' +
                                    k + '"]');
                                if (inp) inp.value = a.billetes[k];
                            });
                            document.getElementById('total-caja').innerText = parseFloat(a.total ||
                                0).toFixed(2);
                            document.getElementById('total-monedas').innerText = parseFloat(a
                                    .monedas ? Object.keys(a.monedas).reduce((s, k) => s + (
                                        parseFloat(k) * (parseFloat(a.monedas[k]) || 0)), 0) : 0)
                                .toFixed(2);
                            document.getElementById('total-billetes').innerText = parseFloat(a
                                    .billetes ? Object.keys(a.billetes).reduce((s, k) => s + (
                                        parseFloat(k) * (parseFloat(a.billetes[k]) || 0)), 0) : 0)
                                .toFixed(2);
                            document.getElementById('arqueo-notas').value = a.notas || '';
                        }
                    } catch (e) {
                        console.warn('No existe arqueo previo o error al cargar:', e);
                    }
                });
                btnClose?.addEventListener('click', () => {
                    modal.style.display = 'none';
                });

                btnSave?.addEventListener('click', async () => {
                    const monedas = {};
                    document.querySelectorAll('.m-count').forEach(inp => {
                        monedas[inp.getAttribute('data-value')] = parseFloat(inp.value) ||
                            0;
                    });
                    const billetes = {};
                    document.querySelectorAll('.b-count').forEach(inp => {
                        billetes[inp.getAttribute('data-value')] = parseFloat(inp.value) ||
                            0;
                    });
                    const total = parseFloat(document.getElementById('total-caja').innerText) || 0;
                    const notas = document.getElementById('arqueo-notas').value || null;

                    const btn = document.getElementById('btn-save-arqueo');
                    btn.disabled = true;
                    btn.innerText = 'Registrando...';

                    try {
                        const token = '{{ csrf_token() }}';
                        const resp = await fetch('{{ route('arqueo.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                monedas,
                                billetes,
                                total,
                                notas,
                                cierre_id: cierreId
                            })
                        });
                        if (!resp.ok) throw new Error('Error en servidor');
                        const data = await resp.json();
                        const tbody = document.getElementById('movimientos-tbody');
                        const usuario = '{{ auth()->user()->name ?? '' }}';
                        const arqueo = data.arqueo || {};
                        const createdAt = arqueo.created_at ? new Date(arqueo.created_at) :
                            new Date();
                        const hora = createdAt.toLocaleTimeString('en-GB', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        const importe = parseFloat(arqueo.total || 0).toFixed(2);
                        const concepto = (arqueo.notas && arqueo.notas.length) ? arqueo.notas :
                            'Arqueo de caja';

                        // Si ya teníamos un arqueo cargado (update), actualizar la fila existente
                        if (window.currentArqueoId) {
                            // buscar fila existente por atributo data-arqueo-id
                            let row = document.querySelector(
                                `tr[data-arqueo-id="${window.currentArqueoId}"]`);
                            if (!row) {
                                // intentar buscar por importe/concepto
                                row = Array.from(document.querySelectorAll('#movimientos-tbody tr'))
                                    .find(r => r.innerText.includes('ARQUEO')) || null;
                            }
                            if (row) {
                                row.setAttribute('data-arqueo-id', data.id || arqueo.id || window
                                    .currentArqueoId);
                                row.children[0].textContent = hora;
                                row.children[2].querySelector('.fw-bold').textContent = concepto;
                                row.children[2].querySelector('.text-muted').textContent = usuario;
                                row.children[3].textContent = 'S/ ' + parseFloat(importe).toFixed(
                                    2);
                            }
                        } else {
                            // insertar nueva fila
                            if (tbody) {
                                const tr = document.createElement('tr');
                                tr.setAttribute('data-arqueo-id', data.id || arqueo.id || '');
                                tr.innerHTML = `
                                            <td class="ps-3 py-3 small text-muted">${hora}</td>
                                            <td>
                                                <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;">ARQUEO</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold mb-0" style="font-size: 0.85rem;">${concepto}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">${usuario}</div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-dark">S/ ${parseFloat(importe).toFixed(2)}</td>
                                        `;
                                if (tbody.firstChild) tbody.insertBefore(tr, tbody.firstChild);
                                else tbody.appendChild(tr);

                                const cnt = document.getElementById('movimientos-count');
                                if (cnt) {
                                    const current = parseInt(cnt.textContent) || 0;
                                    cnt.textContent = (current + 1) + ' registros';
                                }
                            }
                        }

                        // guardar id actual (por si hubo creación)
                        window.currentArqueoId = data.id || arqueo.id || window.currentArqueoId;

                        // Actualizar campo "Conteo Real en Caja" con el total del arqueo
                        const inputCierre = document.getElementById('cierre_caja');
                        if (inputCierre) {
                            inputCierre.value = total.toFixed(2);
                            recalcular(); // Recalcular diferencias
                        }

                        alert('Arqueo registrado (ID: ' + (data.id || arqueo.id || '') + ')');
                        modal.style.display = 'none';
                    } catch (e) {
                        console.error(e);
                        alert('Error al registrar arqueo');
                    } finally {
                        btn.disabled = false;
                        btn.innerText = 'Registrar Arqueo';
                    }
                });
            })();
        });
    </script>

    {{-- ══════════ MODAL PASE CAJA → BÓVEDA ══════════ --}}
    <div id="modal-caja-boveda" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:3100; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:10px; width:420px; max-width:95vw; overflow:hidden; box-shadow:0 8px 40px rgba(0,0,0,0.3);">
            <div style="background:#f59e0b; color:#fff; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:700; font-size:15px;">🏦 Pase a Bóveda</span>
                <button onclick="cerrarModalCajaABoveda()" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer;">×</button>
            </div>
            <div style="padding:20px;">
                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:10px; margin-bottom:14px; font-size:12px; color:#92400e;">
                    <i class="fas fa-info-circle me-1"></i> Transfiere el exceso de efectivo a la Bóveda. El administrador lo recibirá y firmará.
                </div>
                <div class="mb-3">
                    <label class="label-custom">Importe a transferir (S/)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-warning-subtle">S/</span>
                        <input type="number" id="cb_importe" step="0.01" min="0.01" class="form-control fw-bold" placeholder="0.00" value="">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="label-custom">Concepto / Motivo</label>
                    <input type="text" id="cb_concepto" class="form-control form-control-sm" placeholder="Ej: Exceso de caja turno mañana">
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button onclick="cerrarModalCajaABoveda()" class="btn btn-sm btn-light px-4">Cancelar</button>
                    <button id="btn-confirmar-caja-boveda" onclick="ejecutarCajaABoveda()" class="btn btn-sm btn-warning px-4 fw-bold">Confirmar Pase</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ MODAL CAJA DESDE BÓVEDA (Pedir a Bóveda) ══════════ --}}
    <div id="modal-caja-desde-boveda" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:3100; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:10px; width:420px; max-width:95vw; overflow:hidden; box-shadow:0 8px 40px rgba(0,0,0,0.3);">
            <div style="background:#0ea5e9; color:#fff; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:700; font-size:15px;">🏦 Recibir de Bóveda</span>
                <button onclick="cerrarModalCajaDesdeBoveda()" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer;">×</button>
            </div>
            <div style="padding:20px;">
                <div style="background:#e0f2fe; border:1px solid #bae6fd; border-radius:6px; padding:10px; margin-bottom:14px; font-size:12px; color:#0369a1;">
                    <i class="fas fa-info-circle me-1"></i> Registra un ingreso a tu caja proveniente de la Bóveda General (Ej. para dar vuelto).
                </div>
                <div class="mb-3">
                    <label class="label-custom">Importe a recibir (S/)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-info-subtle">S/</span>
                        <input type="number" id="cd_importe" step="0.01" min="0.01" class="form-control fw-bold" placeholder="0.00" value="">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="label-custom">Concepto / Motivo</label>
                    <input type="text" id="cd_concepto" class="form-control form-control-sm" placeholder="Ej: Cambio en sencillo">
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button onclick="cerrarModalCajaDesdeBoveda()" class="btn btn-sm btn-light px-4">Cancelar</button>
                    <button id="btn-confirmar-caja-desde-boveda" onclick="ejecutarCajaDesdeBoveda()" class="btn btn-sm btn-info px-4 fw-bold text-white">Confirmar Ingreso</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ MODAL PASE BÓVEDA → CAJA ══════════ --}}
    <div id="modal-boveda-caja" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:3100; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:10px; width:440px; max-width:95vw; overflow:hidden; box-shadow:0 8px 40px rgba(0,0,0,0.3);">
            <div style="background:#1a1a2e; color:#fff; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:700; font-size:15px;">💰 Pase Bóveda → Caja</span>
                <button onclick="cerrarModalBovedaACaja()" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer;">×</button>
            </div>
            <div style="padding:20px;">
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:10px; margin-bottom:14px; font-size:12px; color:#1e3a8a;">
                    <i class="fas fa-info-circle me-1"></i> Solo administradores/supervisores. Seleccione la caja destino con sesión activa.
                </div>
                <div class="mb-3">
                    <label class="label-custom">Caja Destino</label>
                    <select id="bc_caja_destino" class="form-select form-select-sm">
                        <option value="">Cargando cajas abiertas...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="label-custom">Importe a transferir (S/)</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" id="bc_importe" step="0.01" min="0.01" class="form-control fw-bold" placeholder="0.00">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="label-custom">Concepto / Motivo</label>
                    <input type="text" id="bc_concepto" class="form-control form-control-sm" placeholder="Ej: Reposición de caja 2">
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button onclick="cerrarModalBovedaACaja()" class="btn btn-sm btn-light px-4">Cancelar</button>
                    <button id="btn-confirmar-boveda-caja" onclick="ejecutarBovedaACaja()" class="btn btn-sm btn-dark px-4 fw-bold">Confirmar Pase</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const cierreIdBoveda = {{ $cierre->id }};
        const isTesoreria    = {{ $isTesoreria ? 'true' : 'false' }};
        const csrfBoveda     = '{{ csrf_token() }}';
        const ticketBaseUrl  = '{{ route("boveda.ticket") }}';

        // ─── PASE CAJA → BÓVEDA ─────────────────────────────────────
        function abrirModalCajaABoveda() {
            document.getElementById('cb_importe').value = '';
            document.getElementById('cb_concepto').value = '';
            document.getElementById('modal-caja-boveda').style.display = 'flex';
        }
        function cerrarModalCajaABoveda() {
            document.getElementById('modal-caja-boveda').style.display = 'none';
        }

        async function ejecutarCajaABoveda() {
            const importe = parseFloat(document.getElementById('cb_importe').value);
            const concepto = document.getElementById('cb_concepto').value.trim();

            if (!importe || importe <= 0) { alert('Ingrese un importe válido.'); return; }

            const btn = document.getElementById('btn-confirmar-caja-boveda');
            btn.disabled = true; btn.innerText = 'Procesando...';

            try {
                const res = await fetch('{{ route("boveda.caja-a-boveda") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfBoveda },
                    body: JSON.stringify({ cierre_caja_id: cierreIdBoveda, importe, concepto })
                });
                const data = await res.json();
                if (data.success) {
                    cerrarModalCajaABoveda();
                    // Abrir ticket en nueva pestaña
                    const t = data.ticket_data;
                    const url = ticketBaseUrl + '?tipo=' + encodeURIComponent(t.tipo)
                        + '&origen=' + encodeURIComponent(t.origen)
                        + '&destino=' + encodeURIComponent(t.destino)
                        + '&importe=' + encodeURIComponent(t.importe)
                        + '&concepto=' + encodeURIComponent(t.concepto)
                        + '&usuario=' + encodeURIComponent(t.usuario)
                        + '&fecha=' + encodeURIComponent(t.fecha)
                        + '&id_origen=' + encodeURIComponent(t.id_origen)
                        + '&id_destino=' + encodeURIComponent(t.id_destino);
                    window.open(url, '_blank');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo completar el pase.'));
                }
            } catch(e) {
                alert('Error de red.');
                console.error(e);
            } finally {
                btn.disabled = false; btn.innerText = 'Confirmar Pase';
            }
        }

        // ─── CAJA RECIBE DE BÓVEDA (Petición) ───────────────────────
        function abrirModalCajaDesdeBoveda() {
            document.getElementById('cd_importe').value = '';
            document.getElementById('cd_concepto').value = '';
            document.getElementById('modal-caja-desde-boveda').style.display = 'flex';
        }
        function cerrarModalCajaDesdeBoveda() {
            document.getElementById('modal-caja-desde-boveda').style.display = 'none';
        }

        async function ejecutarCajaDesdeBoveda() {
            const importe = parseFloat(document.getElementById('cd_importe').value);
            const concepto = document.getElementById('cd_concepto').value.trim();

            if (!importe || importe <= 0) { alert('Ingrese un importe válido.'); return; }

            const btn = document.getElementById('btn-confirmar-caja-desde-boveda');
            btn.disabled = true; btn.innerText = 'Procesando...';

            try {
                const res = await fetch('{{ route("boveda.caja-desde-boveda") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfBoveda },
                    body: JSON.stringify({ cierre_caja_id: cierreIdBoveda, importe, concepto })
                });
                const data = await res.json();
                if (data.success) {
                    cerrarModalCajaDesdeBoveda();
                    const t = data.ticket_data;
                    const url = ticketBaseUrl + '?tipo=' + encodeURIComponent(t.tipo)
                        + '&origen=' + encodeURIComponent(t.origen)
                        + '&destino=' + encodeURIComponent(t.destino)
                        + '&importe=' + encodeURIComponent(t.importe)
                        + '&concepto=' + encodeURIComponent(t.concepto)
                        + '&usuario=' + encodeURIComponent(t.usuario)
                        + '&fecha=' + encodeURIComponent(t.fecha)
                        + '&id_origen=' + encodeURIComponent(t.id_origen)
                        + '&id_destino=' + encodeURIComponent(t.id_destino);
                    window.open(url, '_blank');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo completar el pase.'));
                }
            } catch(e) {
                alert('Error de red.');
                console.error(e);
            } finally {
                btn.disabled = false; btn.innerText = 'Confirmar Ingreso';
            }
        }

        // ─── PASE BÓVEDA → CAJA ─────────────────────────────────────
        function abrirModalBovedaACaja() {
            document.getElementById('bc_importe').value = '';
            document.getElementById('bc_concepto').value = '';
            document.getElementById('modal-boveda-caja').style.display = 'flex';
            cargarCajasAbiertas();
        }
        function cerrarModalBovedaACaja() {
            document.getElementById('modal-boveda-caja').style.display = 'none';
        }

        async function cargarCajasAbiertas() {
            const sel = document.getElementById('bc_caja_destino');
            sel.innerHTML = '<option value="">Cargando...</option>';
            try {
                const res = await fetch('{{ route("boveda.cajas-abiertas") }}');
                const data = await res.json();
                if (data.length === 0) {
                    sel.innerHTML = '<option value="">No hay cajas con sesión abierta</option>';
                } else {
                    sel.innerHTML = '<option value="">Seleccione caja destino...</option>';
                    data.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.nombre;
                        sel.appendChild(opt);
                    });
                }
            } catch(e) {
                sel.innerHTML = '<option value="">Error al cargar cajas</option>';
            }
        }

        async function ejecutarBovedaACaja() {
            const cajaId  = document.getElementById('bc_caja_destino').value;
            const importe = parseFloat(document.getElementById('bc_importe').value);
            const concepto = document.getElementById('bc_concepto').value.trim();

            if (!cajaId)  { alert('Seleccione la caja destino.'); return; }
            if (!importe || importe <= 0) { alert('Ingrese un importe válido.'); return; }

            const btn = document.getElementById('btn-confirmar-boveda-caja');
            btn.disabled = true; btn.innerText = 'Procesando...';

            try {
                const res = await fetch('{{ route("boveda.boveda-a-caja") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfBoveda },
                    body: JSON.stringify({
                        cierre_boveda_id: cierreIdBoveda,
                        caja_destino_id: cajaId,
                        importe,
                        concepto
                    })
                });
                const data = await res.json();
                if (data.success) {
                    cerrarModalBovedaACaja();
                    const t = data.ticket_data;
                    const url = ticketBaseUrl + '?tipo=' + encodeURIComponent(t.tipo)
                        + '&origen=' + encodeURIComponent(t.origen)
                        + '&destino=' + encodeURIComponent(t.destino)
                        + '&importe=' + encodeURIComponent(t.importe)
                        + '&concepto=' + encodeURIComponent(t.concepto)
                        + '&usuario=' + encodeURIComponent(t.usuario)
                        + '&fecha=' + encodeURIComponent(t.fecha)
                        + '&id_origen=' + encodeURIComponent(t.id_origen)
                        + '&id_destino=' + encodeURIComponent(t.id_destino);
                    window.open(url, '_blank');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo completar el pase.'));
                }
            } catch(e) {
                alert('Error de red.');
                console.error(e);
            } finally {
                btn.disabled = false; btn.innerText = 'Confirmar Pase';
            }
        }
    </script>
@endsection

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
    </style>

    <div id="cierre-container" class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-0 text-dark">Cierre de Caja #{{ $cierre->id }}</h1>
                <p class="text-muted small mb-0">Usuario: {{ auth()->user()->name ?? 'Administrador' }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('cierre-caja.index') }}" class="btn btn-sm btn-outline-secondary px-3">
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
                        </div>

                        <div class="bg-summary p-3 mb-4 shadow-sm border border-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="label-custom">Total Teórico:</span>
                                <span id="display_teorico" class="display-value text-dark">S/ 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span id="label_descuadre" class="label-custom">Diferencia:</span>
                                <span id="display_descuadre" class="display-value">S/ 0.00</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="label-custom fw-bold text-primary">Conteo Real en Caja (Efectivo)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white border-primary">S/</span>
                                <input type="number" step="0.01" id="cierre_caja"
                                    class="form-control form-control-lg border-primary fw-bold"
                                    value="{{ $cierre->monto_cierre }}" placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="label-custom">Observaciones Finales</label>
                            <textarea name="observaciones" class="form-control form-control-sm" rows="3"
                                placeholder="Notas adicionales sobre el cierre...">{{ $cierre->observaciones }}</textarea>
                        </div>

                        <input type="hidden" id="teorico_cierre" value="{{ $cierre->teorico_cierre }}">
                        <input type="hidden" id="descuadre" value="{{ $cierre->descuadre }}">
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button id="btn-arqueo-actual" class="btn btn-sm btn-success">Arqueo Actual</button>
                        <button id="btn-registrar-arqueo" class="btn btn-sm btn-primary">Registrar Arqueo</button>
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
                                        <th class="ps-3 py-3" style="width: 80px;">Hora</th>
                                        <th style="width: 150px;">Operación</th>
                                        <th>Concepto / Referencia</th>
                                        <th class="text-end pe-3">Importe</th>
                                    </tr>
                                </thead>
                                <tbody id="movimientos-tbody">
                                    @forelse ($movimientos as $movimiento)
                                        <tr>
                                            <td class="ps-3 py-3 small text-muted">
                                                {{ \Carbon\Carbon::parse($movimiento->fecha_emision)->format('H:i') }}
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
                                                    {{ $movimiento->concepto }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    {{ $movimiento->cliente_nombre ?? '---' }} | <i
                                                        class="fas fa-user-circle"></i> {{ $movimiento->usuario }}
                                                </div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-dark">
                                                S/ {{ number_format($movimiento->importe, 2) }}
                                            </td>
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

    <div id="modal-operacion" class="modal" tabindex="-1" style="background: rgba(0,0,0,0.5); display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-dark text-white">
                    <h6 class="modal-title">Registrar Operación Manual</h6>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModals()"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="label-custom">Tipo de Movimiento</label>
                            <select id="op_tipo" class="form-select form-select-sm">
                                <option value="ingreso">Ingreso (+)</option>
                                <option value="gasto">Gasto (-)</option>
                                <option value="aportacion">Aportación (+)</option>
                                <option value="sustraccion">Sustracción (-)</option>
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
                        <div class="col-12">
                            <label class="label-custom">Importe (S/)</label>
                            <input id="op_importe" type="number" step="0.01"
                                class="form-control form-control-lg text-center" value="0.00">
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
        <div style="background:#fff; padding:18px; width:760px; border-radius:6px;">
            <h5 class="mb-3">Registrar Arqueo de Caja</h5>
            <div style="display:flex; gap:20px;">
                <div style="flex:1;">
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
                <div style="flex:1;">
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
                <div style="width:220px;">
                    <h6>Totales</h6>
                    <div>Monedas: S/ <span id="total-monedas">0.00</span></div>
                    <div>Billetes: S/ <span id="total-billetes">0.00</span></div>
                    <div style="font-weight:bold; margin-top:8px;">TOTAL CAJA: S/ <span id="total-caja">0.00</span></div>
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
        const isCajaClosed = {{ $cierre->fecha_cierre ? 'true' : 'false' }};
        const cierreId = {{ $cierre->id }};

        function closeModals() {
            document.getElementById('modal-operacion').style.display = 'none';
            document.getElementById('modal-add-partida').style.display = 'none';
        }

        function recalcular() {
            const getVal = (id) => parseFloat(document.getElementById(id).value) || 0;

            const saldoIni = getVal('saldo_inicial');
            const ingresos = getVal('ingresos');
            const gastos = getVal('gastos');
            const aportes = getVal('aportaciones');
            const sustrac = getVal('sustracciones');
            const cierreReal = getVal('cierre_caja');

            const teorico = saldoIni + ingresos - gastos + aportes - sustrac;
            const diferencia = cierreReal - teorico;

            // Actualizar displays
            document.getElementById('teorico_cierre').value = teorico.toFixed(2);
            document.getElementById('display_teorico').innerText = 'S/ ' + teorico.toLocaleString('en-US', {
                minimumFractionDigits: 2
            });

            const descText = document.getElementById('display_descuadre');
            const descLabel = document.getElementById('label_descuadre');

            descText.innerText = 'S/ ' + Math.abs(diferencia).toLocaleString('en-US', {
                minimumFractionDigits: 2
            });

            if (diferencia < 0) {
                descLabel.innerText = 'FALTANTE:';
                descText.className = 'display-value text-danger';
            } else if (diferencia > 0) {
                descLabel.innerText = 'SOBRANTE:';
                descText.className = 'display-value text-primary';
            } else {
                descLabel.innerText = 'DIFERENCIA:';
                descText.className = 'display-value text-success';
            }
        }

        // Event Listeners
        document.getElementById('fab-add-operacion')?.addEventListener('click', () => {
            document.getElementById('modal-operacion').style.display = 'block';
        });

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', recalcular);
        });

        // Guardar Operación
        document.getElementById('op_save').addEventListener('click', async () => {
            const payload = {
                tipo: document.getElementById('op_tipo').value,
                partida: document.getElementById('op_partida').value,
                concepto: document.getElementById('op_concepto').value,
                importe: parseFloat(document.getElementById('op_importe').value) || 0,
                cierre_caja_id: cierreId
            };

            if (!payload.concepto || payload.importe <= 0) {
                alert('Por favor ingrese un concepto e importe válido.');
                return;
            }

            const res = await fetch("{{ route('operaciones-caja.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) location.reload();
            else alert('Error al guardar la operación.');
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
                alert('Error al cerrar caja.');
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

        document.addEventListener('DOMContentLoaded', () => {
            loadPartidas();
            recalcular();
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
                    document.getElementById('total-monedas').innerText = totalM.toFixed(2);
                    document.getElementById('total-billetes').innerText = totalB.toFixed(2);
                    document.getElementById('total-caja').innerText = (totalM + totalB).toFixed(2);
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
                    }
                });
            })();
        });
    </script>
@endsection

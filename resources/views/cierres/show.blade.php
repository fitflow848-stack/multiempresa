@extends('layout.app')

@section('title', 'Detalle Cierre')

@section('content')
    <div id="cierre-container" class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Detalle Cierre #{{ $cierre->id }}</h1>
                <small class="text-muted">Información completa del cierre de caja</small>
            </div>
            <div>
                <a href="{{ route('cierre-caja.index') }}" class="btn btn-outline-secondary">Volver</a>
                @if($cierre->fecha_cierre)
                    <span class="badge bg-secondary ms-2">CERRADA - {{ \Carbon\Carbon::parse($cierre->fecha_cierre)->format('d/m/Y H:i') }}</span>
                @else
                    <button id="btn-cerrar-caja" class="btn btn-danger ms-2">Cerrar Caja</button>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Saldo Inicial</label>
                            <input type="number" step="0.01" name="monto_apertura" id="saldo_inicial"
                                class="form-control form-control-sm" value="{{ $cierre->monto_apertura }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Ingresos</label>
                            <input type="number" step="0.01" name="ingresos" id="ingresos"
                                class="form-control form-control-sm" value="{{ $cierre->ingresos }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Gastos</label>
                            <input type="number" step="0.01" name="egresos" id="gastos"
                                class="form-control form-control-sm" value="{{ $cierre->egresos }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Aportaciones</label>
                            <input type="number" step="0.01" name="aportaciones" id="aportaciones"
                                class="form-control form-control-sm" value="{{ $cierre->aportaciones }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Sustracciones</label>
                            <input type="number" step="0.01" name="sustracciones" id="sustracciones"
                                class="form-control form-control-sm" value="{{ $cierre->sustracciones }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Teórico Cierre</label>
                            <input type="text" id="teorico_cierre" readonly class="form-control form-control-sm"
                                value="{{ $cierre->teorico_cierre }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Cierre Caja (Efectivo)</label>
                            <input type="number" step="0.01" name="monto_cierre" id="cierre_caja"
                                class="form-control form-control-sm" value="{{ $cierre->monto_cierre }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold" id="label_descuadre">Descuadre Caja</label>
                            <input type="text" id="descuadre" readonly class="form-control form-control-sm"
                                value="{{ $cierre->descuadre }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Observaciones</label>
                            <textarea name="observaciones" class="form-control form-control-sm" rows="4">
                                {{ $cierre->observaciones }}
                            </textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row gy-3 table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="border px-3 py-2">Fecha</th>
                                <th class="border px-3 py-2">Operación</th>
                                <th class="border px-3 py-2">Cliente</th>
                                <th class="border px-3 py-2">Concepto</th>
                                <th class="border px-3 py-2">Importe</th>
                                <th class="border px-3 py-2">Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movimientos as $movimiento)
                                <tr>
                                    <td class="border px-3 py-2">
                                        {{ \Carbon\Carbon::parse($movimiento->fecha_emision)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="border px-3 py-2">{{ $movimiento->operacion }}</td>
                                    <td class="border px-3 py-2">{{ $movimiento->cliente_nombre ?? '---' }}</td>
                                    <td class="border px-3 py-2">{{ $movimiento->concepto }}</td>
                                    <td class="border px-3 py-2">S/ {{ number_format($movimiento->importe, 2) }}</td>
                                    <td class="border px-3 py-2">{{ $movimiento->usuario }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Floating action button to add operaciones -->
    <button id="fab-add-operacion" title="Agregar operación"
        style="position: fixed; right: 18px; bottom: 18px; z-index: 1050; width:56px; height:56px; border-radius:50%; border:none; background:#0d6efd; color:#fff; box-shadow:0 6px 18px rgba(13,110,253,0.28); font-size:22px;">+</button>

    <!-- Modal (simple) -->
    <div id="modal-operacion"
        style="display:none; position:fixed; left:0; right:0; top:0; bottom:0; background:rgba(0,0,0,0.4); z-index:1100;">
        <div style="max-width:640px; margin:60px auto; background:#fff; padding:16px; border-radius:6px;">
            <h6>Registrar Operación</h6>
            <div class="mb-2">
                <label class="form-label small">Tipo</label>
                <select id="op_tipo" class="form-control form-control-sm">
                    <option value="aportacion">Aportación</option>
                    <option value="sustraccion">Sustracción</option>
                    <option value="ingreso">Ingreso</option>
                    <option value="gasto">Gasto</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label small">Partida</label>
                <div style="display:flex; gap:6px; align-items:center;">
                    <select id="op_partida" class="form-control form-control-sm" style="flex:1">
                        <option value="">Elige partida...</option>
                    </select>
                    <button id="btn-add-partida" class="btn btn-sm btn-outline-secondary" title="Agregar partida" style="padding:6px 8px;">+</button>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label small">Concepto</label>
                <input id="op_concepto" class="form-control form-control-sm">
            </div>
            <div class="mb-2">
                <label class="form-label small">Importe</label>
                <input id="op_importe" type="number" step="0.01" class="form-control form-control-sm"
                    value="0.00">
            </div>

            <div class="d-flex justify-content-end" style="gap:8px; margin-top:8px;">
                <button id="op_cancel" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                <button id="op_save" class="btn btn-sm btn-primary">Registrar</button>
            </div>
        </div>
    </div>

    <script>
        // indicate from server if caja is closed
        const isCajaClosed = {{ $cierre->fecha_cierre ? 'true' : 'false' }};

        function disableCierreUI(){
            // Disable form controls inside the cierre container
            const container = document.getElementById('cierre-container');
            if (!container) return;
            container.querySelectorAll('input, textarea, select').forEach(el => el.setAttribute('disabled', 'disabled'));
            // Hide floating action and modal triggers
            const fab = document.getElementById('fab-add-operacion'); if (fab) fab.style.display = 'none';
            const btnAddPartida = document.getElementById('btn-add-partida'); if (btnAddPartida) btnAddPartida.style.display = 'none';
            // Disable modal buttons
            const opSaveBtn = document.getElementById('op_save'); if (opSaveBtn) opSaveBtn.setAttribute('disabled','disabled');
            const paSaveBtn = document.getElementById('pa_save'); if (paSaveBtn) paSaveBtn.setAttribute('disabled','disabled');
            // Disable close button if exists
            const btnClose = document.getElementById('btn-cerrar-caja'); if (btnClose) btnClose.setAttribute('disabled','disabled');
        }

        // Modal controls
        const fab = document.getElementById('fab-add-operacion');
        const modal = document.getElementById('modal-operacion');
        const opCancel = document.getElementById('op_cancel');
        const opSave = document.getElementById('op_save');

        fab.addEventListener('click', () => {
            if (isCajaClosed) return; // prevent opening when closed
            modal.style.display = 'block';
        });
        opCancel.addEventListener('click', (e) => {
            e.preventDefault();
            modal.style.display = 'none';
        });

        async function postOperacion(payload) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch("{{ route('operaciones-caja.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload)
            });
            return res.json();
        }

        opSave.addEventListener('click', async (e) => {
            e.preventDefault();
            if (isCajaClosed) { alert('La caja está cerrada. No puede registrar operaciones.'); return; }
            const payload = {
                tipo: document.getElementById('op_tipo').value,
                partida: document.getElementById('op_partida').value,
                concepto: document.getElementById('op_concepto').value,
                importe: parseFloat(document.getElementById('op_importe').value) || 0,
                cierre_caja_id: {{ $cierre->id }}
            };

            // if current cierre is open, try to attach (optional): find via server side or embed current cierre id if available
            const result = await postOperacion(payload);
            if (result && result.success) {
                // refresh the whole page to reflect the new operation and totals
                location.reload();
            } else {
                alert('Ocurrió un error al registrar la operación');
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === modal) modal.style.display = 'none';
        });
    </script>

        <!-- Modal para agregar Partida -->
        <div id="modal-add-partida" style="display:none; position:fixed; left:0; right:0; top:0; bottom:0; background:rgba(0,0,0,0.4); z-index:1200;">
            <div style="max-width:520px; margin:80px auto; background:#fff; padding:16px; border-radius:6px;">
                <h6>Agregar Partida</h6>
                <div class="mb-2">
                    <label class="form-label small">Nombre</label>
                    <input id="pa_nombre" class="form-control form-control-sm">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Descripción</label>
                    <input id="pa_descripcion" class="form-control form-control-sm">
                </div>
                <div class="d-flex justify-content-end" style="gap:8px; margin-top:8px;">
                    <button id="pa_cancel" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                    <button id="pa_save" class="btn btn-sm btn-primary">Crear</button>
                </div>
            </div>
        </div>

        <script>
            // Partidas: cargar y crear
            const partidasSelect = document.getElementById('op_partida');
            const btnAddPartida = document.getElementById('btn-add-partida');
            const modalAdd = document.getElementById('modal-add-partida');
            const paCancel = document.getElementById('pa_cancel');
            const paSave = document.getElementById('pa_save');

            btnAddPartida.addEventListener('click', (e) => {
                e.preventDefault();
                if (isCajaClosed) { alert('La caja está cerrada. No puede agregar partidas.'); return; }
                modalAdd.style.display = 'block';
                document.getElementById('pa_nombre').focus();
            });

            paCancel.addEventListener('click', (e) => { e.preventDefault(); modalAdd.style.display = 'none'; });

            async function loadPartidas(){
                try{
                    const res = await fetch("{{ route('partidas.index') }}");
                    const data = await res.json();
                    // clear options except first
                    partidasSelect.innerHTML = '<option value="">Elige partida...</option>';
                    data.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.nombre; // store nombre as value to be compatible with operaciones
                        opt.textContent = p.nombre;
                        partidasSelect.appendChild(opt);
                    });
                } catch(err){ console.error('Error loading partidas', err); }
            }

            async function createPartida(payload){
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch("{{ route('partidas.store') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify(payload)
                });
                return res.json();
            }

            paSave.addEventListener('click', async (e) => {
                e.preventDefault();
                if (isCajaClosed) { alert('La caja está cerrada. No puede crear partidas.'); return; }
                const payload = { nombre: document.getElementById('pa_nombre').value.trim(), descripcion: document.getElementById('pa_descripcion').value.trim() };
                if (!payload.nombre) { alert('Ingrese un nombre para la partida'); return; }
                const r = await createPartida(payload);
                if (r && r.success){
                    // add to select and select it
                    const opt = document.createElement('option');
                    opt.value = r.partida.nombre;
                    opt.textContent = r.partida.nombre;
                    partidasSelect.appendChild(opt);
                    partidasSelect.value = r.partida.nombre;
                    modalAdd.style.display = 'none';
                    document.getElementById('pa_nombre').value = '';
                    document.getElementById('pa_descripcion').value = '';
                } else {
                    alert('Error creando partida');
                }
            });

            window.addEventListener('click', function(e){ if (e.target === modalAdd) modalAdd.style.display = 'none'; });

            // cargar partidas al abrir la vista
            document.addEventListener('DOMContentLoaded', function(){ loadPartidas(); if (isCajaClosed) disableCierreUI(); });
        </script>

        <script>
            // Cerrar Caja button handler
            const btnCerrar = document.getElementById('btn-cerrar-caja');
            btnCerrar.addEventListener('click', async function(e){
                e.preventDefault();
                if (!confirm('¿Desea cerrar la caja y actualizar los montos?')) return;

                const payload = {
                    monto_apertura: parseFloat(document.getElementById('saldo_inicial').value) || 0,
                    monto_cierre: parseFloat(document.getElementById('cierre_caja').value) || 0,
                    ingresos: parseFloat(document.getElementById('ingresos').value) || 0,
                    egresos: parseFloat(document.getElementById('gastos').value) || 0,
                    aportaciones: parseFloat(document.getElementById('aportaciones').value) || 0,
                    sustracciones: parseFloat(document.getElementById('sustracciones').value) || 0,
                    observaciones: (document.querySelector('textarea[name="observaciones"]')||{}).value || ''
                };

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch("{{ route('cierre-caja.close', $cierre->id) }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data && data.success) {
                    alert(data.message || 'Caja cerrada');
                    location.reload();
                } else {
                    alert('Error al cerrar la caja');
                }
            });
        </script>

    <script>
        function val(id) {
            return parseFloat(document.getElementById(id).value) || 0;
        }

        function recalcular() {
            const saldoIni = val('saldo_inicial');
            const ingresos = val('ingresos');
            const gastos = val('gastos');
            const aportes = val('aportaciones');
            const sustrac = val('sustracciones');
            const cierreReal = val('cierre_caja');

            const teorico = saldoIni + ingresos - gastos + aportes - sustrac;
            document.getElementById('teorico_cierre').value = teorico.toFixed(2);

            const diferencia = cierreReal - teorico;
            const desc = document.getElementById('descuadre');
            const label = document.getElementById('label_descuadre');
            desc.value = Math.abs(diferencia).toFixed(2);

            if (diferencia < 0) {
                label.innerText = 'DESCUADRE CAJA: FALTANTE';
                desc.style.color = 'red';
            } else if (diferencia > 0) {
                label.innerText = 'DESCUADRE CAJA: SOBRANTE';
                desc.style.color = 'blue';
            } else {
                label.innerText = 'DESCUADRE CAJA';
                desc.style.color = 'green';
            }
        }

        document.addEventListener('input', function(e) {
            const inputs = ['saldo_inicial', 'ingresos', 'gastos', 'aportaciones', 'sustracciones', 'cierre_caja'];
            if (inputs.includes(e.target.id)) recalcular();
        });

        // Inicializa cálculo al cargar
        document.addEventListener('DOMContentLoaded', function() {
            recalcular();
        });
    </script>
@endsection

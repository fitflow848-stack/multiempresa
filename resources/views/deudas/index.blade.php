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
                            <label>Buscar Cliente</label>
                            <input type="text" name="search" class="form-control" 
                                placeholder="Nombre o Nro. Documento..." 
                                value="{{ request('search') }}">
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
                            <label>Local / Sucursal</label>
                            <select name="sucursal_id" class="form-control">
                                <option value="">Todas (Consolidado)</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>
                                        {{ $suc->nombre }}
                                    </option>
                                @endforeach
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
                                @include('deudas._filas', ['clientes' => $clientes])
                            </tbody>
                        </table>
                    </div>

                    <!-- Carga infinita: al llegar al final se piden los siguientes clientes -->
                    <div id="deudas-scroll-sentinel" class="text-center py-3 text-muted"
                        data-next-url="{{ $clientes->appends(request()->query())->nextPageUrl() }}">
                        <span id="deudas-scroll-loader" class="d-none">
                            <i class="fa fa-spinner fa-spin"></i> Cargando más clientes...
                        </span>
                        <span id="deudas-scroll-fin" class="{{ $clientes->hasMorePages() ? 'd-none' : '' }} small">
                            Mostrando todos los clientes ({{ $clientes->total() }})
                        </span>
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
        // Toggle de deudas (delegado: funciona también con las filas que se cargan al hacer scroll)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.toggle-deudas');
            if (!btn) return;
            const row = btn.closest('tr');
            const targetRow = document.getElementById(`deudas-cliente-${row.dataset.clienteId}`);
            const icon = btn.querySelector('i');
            if (!targetRow) return;

            if (targetRow.classList.contains('d-none')) {
                targetRow.classList.remove('d-none');
                icon.classList.replace('fa-plus', 'fa-minus');
                icon.classList.replace('bx-plus', 'bx-minus');
                btn.classList.replace('btn-outline-secondary', 'btn-secondary');
            } else {
                targetRow.classList.add('d-none');
                icon.classList.replace('fa-minus', 'fa-plus');
                icon.classList.replace('bx-minus', 'bx-plus');
                btn.classList.replace('btn-secondary', 'btn-outline-secondary');
            }
        });

        // Scroll infinito de clientes con deudas
        (function() {
            const sentinel = document.getElementById('deudas-scroll-sentinel');
            if (!sentinel) return;
            const loader = document.getElementById('deudas-scroll-loader');
            const fin = document.getElementById('deudas-scroll-fin');
            const tbody = document.querySelector('table.table-hover tbody');
            let nextUrl = sentinel.dataset.nextUrl || '';
            let cargando = false;

            async function cargarMas() {
                if (cargando || !nextUrl) return;
                cargando = true;
                loader.classList.remove('d-none');
                try {
                    const url = new URL(nextUrl, window.location.origin);
                    url.searchParams.set('partial', '1');
                    // Solo ruta + parámetros: evita contenido mixto si la URL llega con otro esquema (http/https)
                    const resp = await fetch(url.pathname + url.search, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const data = await resp.json();
                    tbody.insertAdjacentHTML('beforeend', data.html);
                    nextUrl = data.next || '';
                } catch (e) {
                    console.error('No se pudo cargar más clientes:', e);
                    loader.innerHTML = 'No se pudo cargar más. <a href="#" id="deudas-reintentar">Reintentar</a>';
                    document.getElementById('deudas-reintentar').addEventListener('click', function(ev) {
                        ev.preventDefault();
                        loader.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando más clientes...';
                        cargando = false;
                        cargarMas();
                    });
                    return;
                } finally {
                    if (!loader.querySelector('#deudas-reintentar')) {
                        loader.classList.add('d-none');
                        cargando = false;
                    }
                }
                if (!nextUrl) {
                    observer.disconnect();
                    fin.classList.remove('d-none');
                }
            }

            const observer = new IntersectionObserver(function(entries) {
                if (entries[0].isIntersecting) cargarMas();
            }, { rootMargin: '300px' });

            if (nextUrl) observer.observe(sentinel);
        })();

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

            const body = tipo === 'acumulado' ? {
                cliente_id: targetId,
                monto_pago: montoPago,
                metodo_pago,
                observaciones
            } : {
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

                        Swal.fire({
                            icon: 'success',
                            title: '¡Pago Correcto!',
                            text: data.message || 'El pago se ha registrado correctamente.',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fa fa-print"></i> Imprimir Comprobante',
                            cancelButtonText: 'Cerrar',
                            confirmButtonColor: '#696cff',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                if (data.pago_id) {
                                    window.open(`{{ url('deudas/pago') }}/${data.pago_id}/comprobante`,
                                        '_blank');
                                } else if (data.pago_ids && data.pago_ids.length > 0) {
                                    // Abrir el primero inmediatamente (acción directa del usuario)
                                    window.open(
                                        `{{ url('deudas/pago') }}/${data.pago_ids[0]}/comprobante`,
                                        '_blank');

                                    // Si hay más, avisar al usuario o abrirlos con delay (pero Chrome los bloqueará igual si son muchos)
                                    if (data.pago_ids.length > 1) {
                                        for (let i = 1; i < data.pago_ids.length; i++) {
                                            setTimeout(() => {
                                                window.open(
                                                    `{{ url('deudas/pago') }}/${data.pago_ids[i]}/comprobante`,
                                                    '_blank');
                                            }, i * 800);
                                        }
                                    }
                                }

                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se pudo aplicar el pago'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al aplicar el pago');
                });
        });
    </script>
@endpush

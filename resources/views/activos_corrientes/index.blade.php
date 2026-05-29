@extends('layout.app')

@section('title', 'Activos Corrientes')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Gestión de Activos Corrientes</h1>
        </div>

        <div class="row">
            <!-- Card Principal -->
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Listado de Activos Corrientes</h6>
                        <div>
                            <button class="btn btn-sm btn-info shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#modalNuevoTipo">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Tipo
                            </button>
                            <button class="btn btn-sm btn-primary shadow-sm ml-2" data-bs-toggle="modal"
                                data-bs-target="#modalNuevoActivo">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Activo
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <form action="{{ route('activos_corrientes.index') }}" method="GET"
                            class="row g-3 mb-4 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tipo</label>
                                <select name="tipo_id" class="form-control form-select">
                                    <option value="">Todos</option>
                                    @foreach ($tipos as $tipo)
                                        <option value="{{ $tipo->id }}"
                                            {{ request('tipo_id') == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase text-muted">Nombre</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Buscar..."
                                    value="{{ request('nombre') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase text-muted">Desde</label>
                                <input type="date" name="fecha_inicio" class="form-control"
                                    value="{{ request('fecha_inicio') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase text-muted">Hasta</label>
                                <input type="date" name="fecha_fin" class="form-control"
                                    value="{{ request('fecha_fin') }}">
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="bx bx-search"></i> Filtrar
                                    </button>
                                    <a href="{{ route('activos_corrientes.index', array_merge(request()->query(), ['export' => 1])) }}" class="btn btn-success" title="Exportar Excel">
                                        <i class="bx bx-download"></i>
                                    </a>
                                    <a href="{{ route('activos_corrientes.index') }}" class="btn btn-outline-secondary"
                                        title="Limpiar filtros">
                                        <i class="bx bx-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                        <hr class="sidebar-divider my-4">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Fecha Registro</th>
                                        <th>Tipo</th>
                                        <th>Nombre</th>
                                        <th>Método Pago</th>
                                        <th>Documento</th>
                                        <th class="text-right">Monto (S/)</th>
                                        <th class="text-right">Cobrado (S/)</th>
                                        <th class="text-right">Pendiente (S/)</th>
                                        <th class="text-center">Fecha Cobro</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activos as $activo)
                                        <tr class="{{ $activo->is_settled ? 'table-success' : '' }}">
                                            <td>{{ $activo->fecha_registro->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $activo->tipo->nombre }}</span>
                                                @if($activo->is_settled)<br><span class="badge bg-success mt-1">SALDADO</span>@endif
                                            </td>
                                            <td>{{ $activo->nombre }}</td>
                                            <td>
                                                @if($activo->metodo_pago)
                                                    <span class="badge {{ strtolower($activo->metodo_pago) === 'efectivo' ? 'bg-warning text-dark' : 'bg-info text-white' }}">
                                                        {{ strtoupper($activo->metodo_pago) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $activo->documento ?? '-' }}</td>
                                            <td class="text-right font-weight-bold">S/ {{ number_format($activo->monto, 2) }}</td>
                                            <td class="text-right text-success">S/ {{ number_format($activo->monto_cobrado ?? 0, 2) }}</td>
                                            <td class="text-right {{ $activo->monto_pendiente > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                                                S/ {{ number_format($activo->monto_pendiente, 2) }}
                                            </td>
                                            <td class="text-center small">
                                                @if($activo->is_settled && $activo->updated_at)
                                                    {{ $activo->updated_at->format('d/m/Y') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                 @if(str_contains(strtolower($activo->tipo->nombre), 'adelanto') && !$activo->is_settled)
                                                     <form action="{{ route('finanzas.saldar-adelanto-personal', $activo->id) }}"
                                                         method="POST" class="d-inline confirm-form" data-msg="¿Desea marcar este adelanto como SALDADO?">
                                                         @csrf
                                                         <button type="submit" class="btn btn-success btn-circle btn-sm" title="Saldar">
                                                             <i class="fas fa-check"></i>
                                                         </button>
                                                     </form>
                                                 @endif

                                                 @if(!$activo->tipo->afecta_caja && !$activo->is_settled)
                                                     <button type="button"
                                                         class="btn btn-primary btn-circle btn-sm btn-cobrar"
                                                         title="Cobrar"
                                                         data-id="{{ $activo->id }}"
                                                         data-nombre="{{ $activo->nombre }}"
                                                         data-pendiente="{{ $activo->monto_pendiente }}">
                                                         <i class="fas fa-hand-holding-usd"></i>
                                                     </button>
                                                 @endif

                                                 {{-- Historial de cobros --}}
                                                 @if(($activo->monto_cobrado ?? 0) > 0 || $activo->is_settled)
                                                     <button type="button"
                                                         class="btn btn-secondary btn-circle btn-sm btn-historial"
                                                         title="Historial de cobros"
                                                         data-id="{{ $activo->id }}">
                                                         <i class="bx bx-history"></i>
                                                     </button>
                                                 @endif

                                                 @if(str_contains(strtolower($activo->tipo->nombre), 'adelanto'))
                                                     <a href="{{ route('finanzas.ticket-personal', $activo->id) }}" target="_blank"
                                                        class="btn btn-info btn-circle btn-sm" title="Ver Ticket">
                                                         <i class="bx bx-printer"></i>
                                                     </a>
                                                 @endif

                                                 <form action="{{ route('activos_corrientes.destroy', $activo->id) }}"
                                                     method="POST" class="d-inline delete-form">
                                                     @csrf
                                                     @method('DELETE')
                                                     <button type="submit" class="btn btn-danger btn-circle btn-sm"
                                                         title="Eliminar">
                                                         <i class="bx bx-trash"></i>
                                                     </button>
                                                 </form>
                                                 <button type="button" class="btn btn-warning btn-circle btn-sm btn-edit-activo"
                                                     data-id="{{ $activo->id }}" title="Editar">
                                                     <i class="bx bx-edit"></i>
                                                 </button>
                                             </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No hay registros.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-3">
                                {{ $activos->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Activo -->
    <div class="modal fade" id="modalNuevoActivo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('activos_corrientes.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Registrar Nuevo Activo Corriente</h5>
                        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tipo <span class="text-danger">*</span></label>
                            <select name="tipo_activo_corriente_id" id="selectTipo" class="form-control" required>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->id }}" data-nombre="{{ strtolower($tipo->nombre) }}" data-afecta-caja="{{ $tipo->afecta_caja ? '1' : '0' }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group d-none" id="seccion-proveedor">
                            <label>Proveedor <span class="text-danger">*</span></label>
                            <select name="proveedor_id" id="selectProveedor" class="form-control">
                                <option value="">-- Seleccionar proveedor --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nombre / Descripción Corta <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                placeholder="Ej. Depósito BCP, Adelanto a Juan" required>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label>Monto (Valor) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">S/</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="monto"
                                        class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Fecha Registro <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_registro" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label>Método de Pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago" id="selectMetodoPago" class="form-control" required>
                                <option value="Efectivo">Efectivo (Caja)</option>
                                <option value="Transferencia">Transferencia (Banco)</option>
                                <option value="No aplica">No aplica (Solo Balance)</option>
                            </select>
                            <small class="form-text text-muted d-none" id="infoNoAfectaCaja">
                                Este tipo no afecta la caja. Solo se registra en el balance.
                            </small>
                        </div>
                        <div class="form-group">
                            <label>Documento Referencia</label>
                            <input type="text" name="documento" class="form-control" placeholder="Ej. Voucher 123">
                        </div>
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Cobrar Activo -->
    <div class="modal fade" id="modalCobrar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Registrar Cobro</h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-2 text-muted small" id="cobrar-nombre-label"></p>
                    <div class="form-group mb-3">
                        <label class="fw-bold">Monto a cobrar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">S/</span></div>
                            <input type="number" step="0.01" min="0.01" id="cobrar-monto" class="form-control" placeholder="0.00">
                        </div>
                        <small class="text-muted">Pendiente: <span id="cobrar-pendiente-label" class="font-weight-bold text-danger"></span></small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">Método de Pago <span class="text-danger">*</span></label>
                        <select id="cobrar-metodo" class="form-control">
                            <option value="Efectivo">Efectivo (Caja)</option>
                            <option value="Transferencia">Transferencia Bancaria</option>
                            <option value="Yape">Yape</option>
                            <option value="Plin">Plin</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Referencia / Código</label>
                        <input type="text" id="cobrar-referencia" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="form-group mb-2">
                        <label>Observaciones</label>
                        <textarea id="cobrar-observaciones" class="form-control" rows="2" placeholder="Opcional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-confirmar-cobro" class="btn btn-primary">
                        <i class="fas fa-hand-holding-usd me-1"></i> Cobrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Cobros -->
    <div class="modal fade" id="modalHistorial" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="bx bx-history me-1"></i> Historial de Cobros</h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="historial-loading" class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>
                    <div id="historial-content" class="d-none">
                        <div class="row mb-3">
                            <div class="col-4 text-center">
                                <div class="small text-muted">Total Activo</div>
                                <div class="font-weight-bold" id="hist-monto-total"></div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="small text-muted">Cobrado</div>
                                <div class="font-weight-bold text-success" id="hist-monto-cobrado"></div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="small text-muted">Pendiente</div>
                                <div class="font-weight-bold text-danger" id="hist-monto-pendiente"></div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th class="text-right">Monto</th>
                                        <th>Método</th>
                                        <th>Recibo N°</th>
                                        <th>Referencia</th>
                                        <th>Atendió</th>
                                        <th class="text-center">PDF</th>
                                    </tr>
                                </thead>
                                <tbody id="historial-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Tipo -->
    <div class="modal fade" id="modalNuevoTipo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="formNuevoTipo">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Nuevo Tipo</h5>
                        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre del Tipo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombreTipo" class="form-control"
                                placeholder="Ej. Cuenta BCP" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" id="descTipo" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info">Guardar Tipo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ---- COBRAR ----
        let cobrarActivoId = null;

        document.querySelectorAll('.btn-cobrar').forEach(btn => {
            btn.addEventListener('click', function () {
                cobrarActivoId = this.dataset.id;
                const pendiente = parseFloat(this.dataset.pendiente);
                document.getElementById('cobrar-nombre-label').textContent = this.dataset.nombre;
                document.getElementById('cobrar-monto').value = pendiente.toFixed(2);
                document.getElementById('cobrar-monto').max = pendiente;
                document.getElementById('cobrar-pendiente-label').textContent = 'S/ ' + pendiente.toFixed(2);
                document.getElementById('cobrar-referencia').value = '';
                document.getElementById('cobrar-observaciones').value = '';
                new bootstrap.Modal(document.getElementById('modalCobrar')).show();
            });
        });

        document.getElementById('btn-confirmar-cobro').addEventListener('click', function () {
            const monto = document.getElementById('cobrar-monto').value;
            const metodo = document.getElementById('cobrar-metodo').value;
            const referencia = document.getElementById('cobrar-referencia').value;
            const observaciones = document.getElementById('cobrar-observaciones').value;

            if (!monto || parseFloat(monto) <= 0) {
                alert('Ingrese un monto válido.');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

            fetch(`{{ url('activos-corrientes') }}/${cobrarActivoId}/cobrar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ monto_pago: monto, metodo_pago: metodo, referencia, observaciones })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Abrir recibo en nueva pestaña
                    if (data.pago_id) {
                        window.open(`{{ url('activos-corrientes/pago') }}/${data.pago_id}/comprobante`, '_blank');
                    }
                    bootstrap.Modal.getInstance(document.getElementById('modalCobrar')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(() => alert('Error de conexión al procesar el cobro.'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-hand-holding-usd me-1"></i> Cobrar';
            });
        });

        // ---- HISTORIAL ----
        document.querySelectorAll('.btn-historial').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                document.getElementById('historial-loading').classList.remove('d-none');
                document.getElementById('historial-content').classList.add('d-none');
                new bootstrap.Modal(document.getElementById('modalHistorial')).show();

                fetch(`{{ url('activos-corrientes') }}/${id}/historial`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const a = data.activo;
                        document.getElementById('hist-monto-total').textContent = 'S/ ' + parseFloat(a.monto).toFixed(2);
                        document.getElementById('hist-monto-cobrado').textContent = 'S/ ' + parseFloat(a.monto_cobrado).toFixed(2);
                        document.getElementById('hist-monto-pendiente').textContent = 'S/ ' + parseFloat(a.monto_pendiente).toFixed(2);

                        const tbody = document.getElementById('historial-tbody');
                        tbody.innerHTML = '';
                        if (data.pagos.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Sin cobros registrados.</td></tr>';
                        } else {
                            data.pagos.forEach(p => {
                                tbody.innerHTML += `<tr>
                                    <td>${p.fecha_pago}</td>
                                    <td class="text-right font-weight-bold">S/ ${parseFloat(p.monto).toFixed(2)}</td>
                                    <td><span class="badge bg-info">${p.metodo_pago}</span></td>
                                    <td><small>${p.codigo_comprobante}</small></td>
                                    <td>${p.referencia ?? '-'}</td>
                                    <td>${p.user}</td>
                                    <td class="text-center">
                                        <a href="{{ url('activos-corrientes/pago') }}/${p.id}/comprobante" target="_blank"
                                           class="btn btn-sm btn-outline-danger" title="Ver recibo PDF">
                                            <i class="bx bx-printer"></i>
                                        </a>
                                    </td>
                                </tr>`;
                            });
                        }

                        document.getElementById('historial-loading').classList.add('d-none');
                        document.getElementById('historial-content').classList.remove('d-none');
                    }
                })
                .catch(() => {
                    alert('Error al cargar historial.');
                    bootstrap.Modal.getInstance(document.getElementById('modalHistorial')).hide();
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar/ocultar select de proveedor según tipo seleccionado
            const selectTipo = document.getElementById('selectTipo');
            const seccionProveedor = document.getElementById('seccion-proveedor');
            const selectProveedor = document.getElementById('selectProveedor');
            let proveedoresCargados = false;

            function toggleProveedorSelect() {
                const selected = selectTipo.options[selectTipo.selectedIndex];
                const nombre = (selected.getAttribute('data-nombre') || '').toLowerCase();
                const esAnticipo = nombre.includes('anticipo') && nombre.includes('proveedor');
                const afectaCaja = selected.getAttribute('data-afecta-caja') === '1';
                
                seccionProveedor.classList.toggle('d-none', !esAnticipo);
                
                if (esAnticipo && !proveedoresCargados) {
                    cargarProveedores();
                }

                // Mostrar/ocultar método de pago según si afecta caja
                const selectMetodo = document.getElementById('selectMetodoPago');
                const infoNoAfecta = document.getElementById('infoNoAfectaCaja');
                
                if (!afectaCaja) {
                    selectMetodo.value = 'No aplica';
                    selectMetodo.setAttribute('readonly', true);
                    selectMetodo.style.pointerEvents = 'none';
                    selectMetodo.style.backgroundColor = '#e9ecef';
                    infoNoAfecta.classList.remove('d-none');
                } else {
                    selectMetodo.removeAttribute('readonly');
                    selectMetodo.style.pointerEvents = '';
                    selectMetodo.style.backgroundColor = '';
                    if (selectMetodo.value === 'No aplica') {
                        selectMetodo.value = 'Efectivo';
                    }
                    infoNoAfecta.classList.add('d-none');
                }
            }

            function cargarProveedores() {
                fetch("{{ route('proveedores.select') }}")
                    .then(r => r.json())
                    .then(data => {
                        let html = '<option value="">-- Seleccionar proveedor --</option>';
                        data.forEach(p => {
                            html += `<option value="${p.id}">${p.nombre_comercial || p.nombre_legal || p.ruc}</option>`;
                        });
                        selectProveedor.innerHTML = html;
                        proveedoresCargados = true;
                    })
                    .catch(() => {
                        selectProveedor.innerHTML = '<option value="">-- Error al cargar --</option>';
                    });
            }

            selectTipo.addEventListener('change', toggleProveedorSelect);
            toggleProveedorSelect();

            // Auto-llenar nombre cuando se selecciona un proveedor
            selectProveedor.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                if (selected && selected.value) {
                    const nombreInput = document.querySelector('#modalNuevoActivo input[name="nombre"]');
                    if (nombreInput && (!nombreInput.value || nombreInput.value.trim() === '')) {
                        nombreInput.value = 'Anticipo - ' + selected.text;
                    }
                }
            });

            // Script para guardar tipo via AJAX y actualizar el select
            const formTipo = document.getElementById('formNuevoTipo');
            if (formTipo) {
                formTipo.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const btn = formTipo.querySelector('button[type="submit"]');
                    const originalText = btn.textContent;
                    btn.disabled = true;
                    btn.textContent = 'Guardando...';

                    const formData = new FormData(formTipo);

                    fetch("{{ route('activos_corrientes.storeTipo') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Agregar al select
                                const select = document.getElementById('selectTipo');
                                if (select) {
                                    const option = new Option(data.tipo.nombre, data.tipo.id, true,
                                        true);
                                    option.setAttribute('data-nombre', data.tipo.nombre.toLowerCase());
                                    option.setAttribute('data-afecta-caja', data.tipo.afecta_caja ? '1' : '0');
                                    select.add(option);
                                    select.dispatchEvent(new Event('change'));
                                }

                                // Cerrar modal simulando click en dismiss
                                const closeBtn = document.querySelector(
                                    '#modalNuevoTipo [data-bs-dismiss="modal"]');
                                if (closeBtn) closeBtn.click();

                                formTipo.reset();
                                alert('Tipo creado correctamente');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error al crear tipo. Ver consola para detalles.');
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.textContent = originalText;
                        });
                });
            }

            // Confirmación de eliminación
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm(
                            '¿Estás seguro de eliminar este registro? esta acción afectará al balance general.'
                        )) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
    <!-- Modal Editar Activo -->
    <div class="modal fade" id="modalEditarActivo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formEditarActivo" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">Editar Activo Corriente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Tipo <span class="text-danger">*</span></label>
                            <select name="tipo_activo_corriente_id" id="edit_tipo_activo_corriente_id" class="form-control" required>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Nombre / Descripción Corta <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label>Monto (Valor) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">S/</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="monto" id="edit_monto" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Fecha Registro <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_registro" id="edit_fecha_registro" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label>Documento Referencia</label>
                            <input type="text" name="documento" id="edit_documento" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" id="edit_observaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Actualizar Registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEditarActivo = new bootstrap.Modal(document.getElementById('modalEditarActivo'));
            const formEditarActivo = document.getElementById('formEditarActivo');

            document.querySelectorAll('.btn-edit-activo').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    
                    fetch(`{{ url('activos-corrientes') }}/${id}/edit`)
                        .then(response => response.json())
                        .then(data => {
                            formEditarActivo.action = `{{ url('activos-corrientes') }}/${id}/update`;
                            
                            document.getElementById('edit_tipo_activo_corriente_id').value = data.tipo_activo_corriente_id;
                            document.getElementById('edit_nombre').value = data.nombre;
                            document.getElementById('edit_monto').value = data.monto;
                            document.getElementById('edit_fecha_registro').value = data.fecha_registro.substring(0, 10);
                            document.getElementById('edit_documento').value = data.documento || '';
                            document.getElementById('edit_observaciones').value = data.observaciones || '';
                            
                            modalEditarActivo.show();
                        })
                        .catch(error => alert('Error al cargar datos'));
                });
            });

            // Confirmación genérica con mensaje personalizado
            document.querySelectorAll('.confirm-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const msg = this.getAttribute('data-msg') || '¿Está seguro de realizar esta acción?';
                    if (!confirm(msg)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush

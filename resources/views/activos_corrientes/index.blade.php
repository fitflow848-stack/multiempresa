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
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Desde</label>
                                <input type="date" name="fecha_inicio" class="form-control"
                                    value="{{ request('fecha_inicio') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Hasta</label>
                                <input type="date" name="fecha_fin" class="form-control"
                                    value="{{ request('fecha_fin') }}">
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="bx bx-search"></i> Filtrar
                                    </button>
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
                                            <td class="text-right font-weight-bold">S/
                                                {{ number_format($activo->monto, 2) }}</td>
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
                                                     <form action="{{ route('activos_corrientes.cobrar', $activo->id) }}"
                                                         method="POST" class="d-inline confirm-form" data-msg="¿Desea COBRAR este activo? Se registrará un ingreso en caja.">
                                                         @csrf
                                                         <button type="submit" class="btn btn-primary btn-circle btn-sm" title="Cobrar">
                                                             <i class="fas fa-hand-holding-usd"></i>
                                                         </button>
                                                     </form>
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
                                            <td colspan="6" class="text-center text-muted">No hay registros.
                                            </td>
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

@extends('layout.app')

@section('title', 'Pasivos Corrientes')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Gestión de Pasivos Corrientes</h1>
        </div>

        <div class="row">
            <!-- Card Principal -->
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Listado de Pasivos Registrados</h6>
                        <div>
                            <button class="btn btn-sm btn-info shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#modalNuevoTipo">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Tipo
                            </button>
                            <button class="btn btn-sm btn-primary shadow-sm ml-2" data-bs-toggle="modal"
                                data-bs-target="#modalNuevoPasivo">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Pasivo
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <form action="{{ route('pasivos.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tipo de Pasivo</label>
                                <select name="tipo_id" id="selectTipoPasivo" class="form-control form-select">
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
                                    <a href="{{ route('pasivos.index', array_merge(request()->query(), ['export' => 1])) }}" class="btn btn-success" title="Exportar Excel">
                                        <i class="bx bx-download"></i>
                                    </a>
                                    <a href="{{ route('pasivos.index') }}" class="btn btn-outline-secondary"
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
                                        <th>Documento</th>
                                        <th class="text-right">Monto (S/)</th>
                                        <th class="text-right">Saldo (S/)</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Último Pago</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pasivos as $pasivo)
                                        <tr>
                                            <td>{{ $pasivo->fecha_registro->format('d/m/Y') }}</td>
                                            <td><span class="badge bg-primary">{{ $pasivo->tipo->nombre }}</span></td>
                                            <td>{{ $pasivo->nombre }}</td>
                                            <td>{{ $pasivo->documento ?? '-' }}</td>
                                            <td class="text-right font-weight-bold">S/
                                                {{ number_format($pasivo->monto, 2) }}</td>
                                            <td class="text-right text-danger font-weight-bold">S/
                                                {{ number_format($pasivo->saldo, 2) }}</td>
                                            <td class="text-center">
                                                @if($pasivo->estado == 'pagado')
                                                    <span class="badge bg-success">PAGADO</span>
                                                @elseif($pasivo->estado == 'parcial')
                                                    <span class="badge bg-warning">PARCIAL</span>
                                                @else
                                                    <span class="badge bg-secondary">PENDIENTE</span>
                                                @endif
                                                @if($pasivo->is_settled || $pasivo->saldo == 0)
                                                    <span class="badge bg-success">SALDADO</span>
                                                @endif
                                            </td>
                                            <td class="text-center small">
                                                @php
                                                    $ultimoPago = $pasivo->pagos->sortByDesc('fecha_pago')->first();
                                                @endphp
                                                @if($ultimoPago)
                                                    {{ $ultimoPago->fecha_pago->format('d/m/Y') }}
                                                    <br><span class="text-muted">{{ $ultimoPago->metodo_pago ?? '' }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                             <td class="text-center">
                                                @if(str_contains(strtolower($pasivo->tipo->nombre), 'compra') && $pasivo->saldo > 0)
                                                    <form action="{{ route('finanzas.pagar-compra-credito', $pasivo->id) }}"
                                                        method="POST" class="d-inline confirm-form" data-msg="¿Desea marcar esta compra como PAGADA?">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-circle btn-sm" title="Pagar Compra">
                                                            <i class="bx bx-money"></i>
                                                        </button>
                                                    </form>
                                                @elseif(str_contains(strtolower($pasivo->tipo->nombre), 'adelanto') && !$pasivo->is_settled)
                                                     <form action="{{ route('finanzas.saldar-adelanto-cliente', $pasivo->id) }}"
                                                        method="POST" class="d-inline confirm-form" data-msg="¿Desea marcar este adelanto como UTILIZADO/SALDADO? (Esto generará un egreso de caja neutro)">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-circle btn-sm" title="Saldar Adelanto">
                                                            <i class="bx bx-user-check"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('finanzas.ticket-pasivo', $pasivo->id) }}" target="_blank"
                                                   class="btn btn-info btn-circle btn-sm" title="Ver Ticket">
                                                    <i class="bx bx-printer"></i>
                                                </a>

                                                @if($pasivo->pagos->count() > 0)
                                                    <button type="button"
                                                        class="btn btn-secondary btn-circle btn-sm btn-historial-pasivo"
                                                        title="Historial de pagos"
                                                        data-id="{{ $pasivo->id }}">
                                                        <i class="bx bx-history"></i>
                                                    </button>
                                                @endif

                                                @if($pasivo->compra_id)
                                                    <a href="{{ route('compras.show', $pasivo->compra_id) }}"
                                                       class="btn btn-secondary btn-circle btn-sm" title="Ver Compra Original">
                                                        <i class="bx bx-receipt"></i>
                                                    </a>
                                                @endif

                                                @if($pasivo->saldo > 0 && !str_contains(strtolower($pasivo->tipo->nombre), 'compra'))
                                                    <button type="button" class="btn btn-primary btn-circle btn-sm"
                                                        onclick="abrirModalPagoPasivo({{ $pasivo->id }}, '{{ $pasivo->nombre }}', {{ $pasivo->saldo }})"
                                                        title="Registrar Pago (Afecta Caja)">
                                                        <i class="bx bx-money"></i>
                                                    </button>
                                                @endif

                                                <button type="button" class="btn btn-warning btn-circle btn-sm btn-edit-pasivo"
                                                    data-id="{{ $pasivo->id }}" title="Editar">
                                                    <i class="bx bx-edit"></i>
                                                </button>

                                                @if (strtolower($pasivo->tipo->nombre) === 'beneficio')
                                                    <form action="{{ route('pasivos.convertir-aporte', $pasivo->id) }}"
                                                        method="POST" class="d-inline convert-form">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-circle btn-sm"
                                                            title="Convertir a Aporte">
                                                            <i class="bx bx-repost"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('pasivos.destroy', $pasivo->id) }}" method="POST"
                                                    class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-circle btn-sm"
                                                        title="Eliminar">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No hay pasivos registrados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-3">
                                {{ $pasivos->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Pasivo -->
    <div class="modal fade" id="modalNuevoPasivo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('pasivos.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Registrar Nuevo Pasivo</h5>
                        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tipo de Pasivo <span class="text-danger">*</span></label>
                            <select name="tipo_pasivo_id" id="selectTipoPasivo" class="form-control" required>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nombre / Descripción Corta <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                placeholder="Ej. Préstamo BCP, Aporte Socio" required>
                        </div>
                        <div class="form-row">
                            <div class="col-md-4 mb-3">
                                <label>Monto (Valor) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">S/</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="monto"
                                        class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Método de Pago</label>
                                <select name="metodo_pago" class="form-select">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                    <option value="Yape/Plin">Yape/Plin</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Fecha Registro <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_registro" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Documento Referencia</label>
                            <input type="text" name="documento" class="form-control" placeholder="Ej. Pagaré 001">
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
                        <h5 class="modal-title">Nuevo Tipo Pasivo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre del Tipo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombreTipo" class="form-control"
                                placeholder="Ej. Prestamos Corto Plazo" required>
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

    <!-- Modal Registrar Pago Pasivo -->
    <div class="modal fade" id="modalPagoPasivo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formPagoPasivo" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Registrar Pago de Pasivo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3"><strong>Concepto:</strong> <span id="pagoConcepto"></span></p>
                        <div class="alert alert-info py-2">
                            Saldo pendiente: <strong>S/ <span id="pagoSaldoMax"></span></strong>
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-bold small">Monto a Pagar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" step="0.01" name="monto" id="pagoMontoInput" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold small">Fecha Pago <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_pago" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold small">Método de Pago <span class="text-danger">*</span></label>
                                <select name="metodo_pago" class="form-select" required>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                    <option value="Yape/Plin">Yape/Plin</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-bold small">Nro. Operación / Documento</label>
                            <input type="text" name="documento_pago" class="form-control" placeholder="Ej. OP-12345">
                        </div>

                        <div class="form-group">
                            <label class="fw-bold small">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Procesar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Pasivo -->
    <div class="modal fade" id="modalEditarPasivo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formEditarPasivo" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">Editar Pasivo</h5>
                        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Tipo de Pasivo <span class="text-danger">*</span></label>
                            <select name="tipo_pasivo_id" id="edit_tipo_pasivo_id" class="form-control" required>
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
                                    <input type="number" step="0.01" min="0" name="monto" id="edit_monto"
                                        class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Fecha Registro <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_registro" id="edit_fecha_registro" class="form-control"
                                    required>
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

    <!-- Modal Historial de Pagos (Pasivo) -->
    <div class="modal fade" id="modalHistorialPasivo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="bx bx-history me-1"></i> Historial de Pagos</h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="historial-pasivo-loading" class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>
                    <div id="historial-pasivo-content" class="d-none">
                        <div class="row mb-3">
                            <div class="col-4 text-center">
                                <div class="small text-muted">Monto Total</div>
                                <div class="font-weight-bold" id="hist-pasivo-monto-total"></div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="small text-muted">Pagado</div>
                                <div class="font-weight-bold text-success" id="hist-pasivo-monto-pagado"></div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="small text-muted">Saldo</div>
                                <div class="font-weight-bold text-danger" id="hist-pasivo-saldo"></div>
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
                                <tbody id="historial-pasivo-tbody"></tbody>
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
@endsection

@push('scripts')
    <script>
        function abrirModalPagoPasivo(id, nombre, saldo) {
            const form = document.getElementById('formPagoPasivo');
            form.action = `{{ url('pasivos/pagar') }}/${id}`;
            document.getElementById('pagoConcepto').textContent = nombre;
            document.getElementById('pagoSaldoMax').textContent = saldo.toFixed(2);
            document.getElementById('pagoMontoInput').value = saldo.toFixed(2);
            document.getElementById('pagoMontoInput').max = saldo;
            
            new bootstrap.Modal(document.getElementById('modalPagoPasivo')).show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Imprimir ticket si existe en sesión
            @if(session('pago_id'))
                const url = "{{ route('pasivos.ticket', session('pago_id')) }}";
                window.open(url, '_blank', 'width=400,height=600');
            @endif

            // Script para guardar tipo via AJAX
            const formTipo = document.getElementById('formNuevoTipo');
            if (formTipo) {
                formTipo.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = formTipo.querySelector('button[type="submit"]');
                    const originalText = btn.textContent;
                    btn.disabled = true;
                    btn.textContent = 'Guardando...';

                    fetch("{{ route('pasivos.storeTipo') }}", {
                            method: 'POST',
                            body: new FormData(formTipo),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const select = document.getElementById('selectTipoPasivo');
                                if (select) {
                                    const option = new Option(data.tipo.nombre, data.tipo.id, true, true);
                                    select.add(option);
                                    select.dispatchEvent(new Event('change'));
                                }
                                bootstrap.Modal.getInstance(document.getElementById('modalNuevoTipo')).hide();
                                formTipo.reset();
                                alert('Tipo creado correctamente');
                            }
                        })
                        .catch(error => alert('Error al crear tipo'))
                        .finally(() => {
                            btn.disabled = false;
                            btn.textContent = originalText;
                        });
                });
            }

            // Confirmación de conversión
            document.querySelectorAll('.convert-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('¿Estás seguro de pasar este pasivo a APORTE? El monto dejará de ser una deuda y pasará a formar parte del capital de la empresa.')) {
                        e.preventDefault();
                    }
                });
            });

            // Confirmación de eliminación
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('¿Estás seguro de eliminar este pasivo? Esta acción afectará al balance general.')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Logic for Edit Pasivo
            const modalEditarPasivo = new bootstrap.Modal(document.getElementById('modalEditarPasivo'));
            const formEditarPasivo = document.getElementById('formEditarPasivo');

            document.querySelectorAll('.btn-edit-pasivo').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    
                    fetch(`{{ url('pasivos') }}/${id}/edit`)
                        .then(response => response.json())
                        .then(data => {
                            formEditarPasivo.action = `{{ url('pasivos') }}/${id}/update`;
                            
                            document.getElementById('edit_tipo_pasivo_id').value = data.tipo_pasivo_id;
                            document.getElementById('edit_nombre').value = data.nombre;
                            document.getElementById('edit_monto').value = data.monto;
                            document.getElementById('edit_fecha_registro').value = data.fecha_registro.substring(0, 10);
                            document.getElementById('edit_documento').value = data.documento || '';
                            document.getElementById('edit_observaciones').value = data.observaciones || '';
                            
                            modalEditarPasivo.show();
                        })
                        .catch(error => alert('Error al cargar datos'));
                });
            });

            // ---- HISTORIAL DE PAGOS ----
            document.querySelectorAll('.btn-historial-pasivo').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    document.getElementById('historial-pasivo-loading').classList.remove('d-none');
                    document.getElementById('historial-pasivo-content').classList.add('d-none');
                    new bootstrap.Modal(document.getElementById('modalHistorialPasivo')).show();

                    fetch(`{{ url('pasivos') }}/${id}/historial`, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const p = data.pasivo;
                            document.getElementById('hist-pasivo-monto-total').textContent = 'S/ ' + parseFloat(p.monto).toFixed(2);
                            document.getElementById('hist-pasivo-monto-pagado').textContent = 'S/ ' + parseFloat(p.monto_pagado).toFixed(2);
                            document.getElementById('hist-pasivo-saldo').textContent = 'S/ ' + parseFloat(p.saldo).toFixed(2);

                            const tbody = document.getElementById('historial-pasivo-tbody');
                            tbody.innerHTML = '';
                            if (data.pagos.length === 0) {
                                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Sin pagos registrados.</td></tr>';
                            } else {
                                data.pagos.forEach(pago => {
                                    tbody.innerHTML += `<tr>
                                        <td>${pago.fecha_pago}</td>
                                        <td class="text-right font-weight-bold">S/ ${parseFloat(pago.monto).toFixed(2)}</td>
                                        <td><span class="badge bg-info">${pago.metodo_pago}</span></td>
                                        <td><small>${pago.recibo}</small></td>
                                        <td>${pago.referencia ?? '-'}</td>
                                        <td>${pago.user}</td>
                                        <td class="text-center">
                                            <a href="{{ url('pasivos/ticket') }}/${pago.id}" target="_blank"
                                               class="btn btn-sm btn-outline-danger" title="Ver recibo PDF">
                                                <i class="bx bx-printer"></i>
                                            </a>
                                        </td>
                                    </tr>`;
                                });
                            }

                            document.getElementById('historial-pasivo-loading').classList.add('d-none');
                            document.getElementById('historial-pasivo-content').classList.remove('d-none');
                        }
                    })
                    .catch(() => {
                        alert('Error al cargar historial.');
                        bootstrap.Modal.getInstance(document.getElementById('modalHistorialPasivo')).hide();
                    });
                });
            });

            // Confirmación genérica
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

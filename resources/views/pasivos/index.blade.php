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
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tipo de Pasivo</label>
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
                                            <td class="text-center">
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
                        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

                    fetch("{{ route('pasivos.storeTipo') }}", {
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
                                const select = document.getElementById('selectTipoPasivo');
                                if (select) {
                                    const option = new Option(data.tipo.nombre, data.tipo.id, true,
                                        true);
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
                            '¿Estás seguro de eliminar este pasivo? esta acción afectará al balance general.'
                        )) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush

@extends('layout.app')

@section('title', 'Aportes de Capital')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Gestión de Aportes / Capital</h1>
        </div>

        <div class="row">
            <!-- Card Principal -->
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-success">Listado de Aportes Registrados</h6>
                        <div>
                            <button class="btn btn-sm btn-info shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#modalNuevoTipo">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Tipo
                            </button>
                            <button class="btn btn-sm btn-success shadow-sm ml-2" data-bs-toggle="modal"
                                data-bs-target="#modalNuevoAporte">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Aporte
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <form action="{{ route('aportes.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tipo de Aporte</label>
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
                                    <button type="submit" class="btn btn-success flex-grow-1">
                                        <i class="bx bx-search"></i> Filtrar
                                    </button>
                                    <a href="{{ route('aportes.index') }}" class="btn btn-outline-secondary"
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
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                                <thead class="bg-success text-white">
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
                                    @forelse($aportes as $aporte)
                                        <tr>
                                            <td>{{ $aporte->fecha_registro->format('d/m/Y') }}</td>
                                            <td><span class="badge bg-success">{{ $aporte->tipo->nombre }}</span></td>
                                            <td>{{ $aporte->nombre }}</td>
                                            <td>{{ $aporte->documento ?? '-' }}</td>
                                            <td class="text-right font-weight-bold">S/
                                                {{ number_format($aporte->monto, 2) }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('aportes.destroy', $aporte->id) }}" method="POST"
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
                                            <td colspan="6" class="text-center text-muted">No hay aportes registrados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-3">
                                {{ $aportes->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Aporte -->
    <div class="modal fade" id="modalNuevoAporte" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('aportes.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Registrar Nuevo Aporte</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="fw-bold small">Tipo de Aporte <span class="text-danger">*</span></label>
                            <select name="tipo_aporte_id" id="selectTipoAporte" class="form-control" required>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold small">Nombre / Descripción Corta <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                placeholder="Ej. Aporte Socio Juan, Reinversión" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold small">Monto (Valor) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" min="0" name="monto"
                                        class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold small">Fecha Registro <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_registro" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold small">Documento Referencia</label>
                            <input type="text" name="documento" class="form-control" placeholder="Ej. Transferencia BCP">
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold small">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success px-4">Guardar Registro</button>
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
                        <h5 class="modal-title">Nuevo Tipo Aporte</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="fw-bold small">Nombre del Tipo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombreTipo" class="form-control"
                                placeholder="Ej. Aporte Efectivo" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold small">Descripción</label>
                            <textarea name="descripcion" id="descTipo" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
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

                    fetch("{{ route('aportes.storeTipo') }}", {
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
                                const select = document.getElementById('selectTipoAporte');
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
                                alert('Tipo de aporte creado correctamente');
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
                            '¿Estás seguro de eliminar este aporte? Esta acción afectará al balance general.'
                        )) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush

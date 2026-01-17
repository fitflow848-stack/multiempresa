@extends('layout.app')

@section('title', 'Gestión de Compras')
@section('page-title', 'Gestión de Compras')
@section('breadcrumb')
    <li class="breadcrumb-item">Área Compras</li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

<link rel="stylesheet" href="{{ asset('css/modules-common.css') }}">

@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Compras</h1>
            <small class="text-muted">Gestión y seguimiento de compras</small>
        </div>
        <a href="{{ route('compras.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Compra
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('compras.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" id="fecha-desde" class="form-control" value="{{ request('fecha_desde') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha-hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Proveedor</label>
                    <input type="text" name="proveedor" id="proveedor-filter" class="form-control" placeholder="Buscar proveedor..." value="{{ request('proveedor') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" id="estado-filter" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="compras-table" class="table table-hover" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">N°</th>
                            <th class="text-center">Fecha</th>
                            <th>Proveedor</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTable carga por AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Total Compras</small>
                    <div class="h5" id="total-compras">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Pendientes</small>
                    <div class="h5" id="compras-pendientes">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Completadas</small>
                    <div class="h5" id="compras-completadas">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Valor Total</small>
                    <div class="h5" id="valor-total">S/. 0.00</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== SCRIPTS ===================== --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(function() {
            // Inicializar DataTable con configuración mejorada
            const table = $('#compras-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('/compras/data') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: function(d) {
                        // Agregar filtros personalizados
                        d.fecha_desde = $('#fecha-desde').val();
                        d.fecha_hasta = $('#fecha-hasta').val();
                        d.proveedor_filter = $('#proveedor-filter').val();
                        d.estado_filter = $('#estado-filter').val();
                    }
                },
                responsive: true,
                columns: [{
                        data: 'id',
                        name: 'id',
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return '<strong>' + data + '</strong>';
                        }
                    },
                    {
                        data: 'fecha',
                        name: 'fecha',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                const fecha = new Date(data);
                                return '<small>' + fecha.toLocaleDateString('es-ES') + '</small>';
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'proveedor',
                        name: 'proveedor',
                        render: function(data) {
                            return '<div class="product-info"><span class="product-name">' + (
                                data || 'Sin proveedor') + '</span></div>';
                        }
                    },
                    {
                        data: 'total',
                        name: 'total',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return '<span class="text-success fw-bold">S/. ' + parseFloat(data)
                                    .toLocaleString('es-ES', {
                                        minimumFractionDigits: 2
                                    }) + '</span>';
                            }
                            return '<span class="text-muted">S/. 0.00</span>';
                        }
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                        className: 'text-center',
                        render: function(data) {
                            let badgeClass = 'status-pendiente';
                            let text = 'Pendiente';

                            if (data === 'Recibida') {
                                badgeClass = 'status-completado';
                                text = 'Completado';
                            } else if (data === 'cancelado') {
                                badgeClass = 'status-cancelado';
                                text = 'Cancelado';
                            }

                            return '<span class="status-badge ' + badgeClass + '">' + text +
                                '</span>';
                        }
                    },
                    {
                        data: 'acciones',
                        name: 'acciones',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [1, 'desc']
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                drawCallback: function(settings) {
                    // Actualizar estadísticas después de cada recarga
                    updateStats();
                }
            });

            // Aplicar filtros
            $('#aplicar-filtros').click(function() {
                table.ajax.reload();
            });

            // Limpiar filtros
            $('#limpiar-filtros').click(function() {
                $('#fecha-desde, #fecha-hasta, #proveedor-filter').val('');
                $('#estado-filter').val('');
                table.ajax.reload();
            });

            // Auto-aplicar filtros cuando cambian los inputs
            $('#fecha-desde, #fecha-hasta, #proveedor-filter, #estado-filter').on('change', function() {
                table.ajax.reload();
            });

            // Función para actualizar estadísticas (simulada)
            function updateStats() {
                // En una implementación real, estos valores vendrían del servidor
                // Por ahora usamos valores de ejemplo
                $('#total-compras').text(table.page.info().recordsTotal || 0);
                $('#compras-pendientes').text(Math.floor(Math.random() * 10));
                $('#compras-completadas').text(Math.floor(Math.random() * 20));
                $('#valor-total').text('S/. ' + (Math.random() * 10000).toLocaleString('es-ES', {
                    minimumFractionDigits: 2
                }));
            }

            // Configurar fechas por defecto (último mes)
            const hoy = new Date();
            const haceUnMes = new Date();
            haceUnMes.setMonth(hoy.getMonth() - 1);

            $('#fecha-hasta').val(hoy.toISOString().split('T')[0]);
            $('#fecha-desde').val(haceUnMes.toISOString().split('T')[0]);
        });
    </script>
@endsection

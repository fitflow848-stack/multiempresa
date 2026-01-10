@extends('layout.app')

@section('title', 'Gestión de Compras')
@section('page-title', 'Gestión de Compras')
@section('breadcrumb')
    <li class="breadcrumb-item">Área Compras</li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

<link rel="stylesheet" href="{{ asset('css/modules-common.css') }}">

@section('content')

    <div class="module-container">
        <!-- Sección de filtros de búsqueda -->
        <div class="search-section">
            <h6 class="mb-3">
                <i class="fas fa-filter me-2"></i>
                Filtros de Búsqueda
            </h6>
            <div class="search-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-calendar me-1"></i>
                        Desde:
                    </label>
                    <input type="date" class="form-control" id="fecha-desde">
                </div>
                <div class="form-group">
                    <label>
                        <i class="fas fa-calendar me-1"></i>
                        Hasta:
                    </label>
                    <input type="date" class="form-control" id="fecha-hasta">
                </div>
                <div class="form-group">
                    <label>
                        <i class="fas fa-truck me-1"></i>
                        Proveedor:
                    </label>
                    <input type="text" class="form-control" id="proveedor-filter" placeholder="Buscar proveedor...">
                </div>
                <div class="form-group">
                    <label>
                        <i class="fas fa-info-circle me-1"></i>
                        Estado:
                    </label>
                    <select class="form-control" id="estado-filter">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="completado">Completado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="limpiar-filtros"
                            title="Limpiar filtros">
                            <i class="fas fa-eraser"></i>
                        </button>
                        <button type="button" class="btn-search" id="aplicar-filtros">
                            <i class="fas fa-search me-1"></i>
                            Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de compras mejorada -->
        <div class="inventory-section">
            <div class="section-header">
                <i class="fas fa-list me-2"></i>
                Listado de Compras
            </div>
            <div class="table-responsive">
                <table id="compras-table" class="table table-hover" style="width:100%">
                    <thead>
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
                        {{-- Los datos se cargan por AJAX desde DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resumen de compras -->
        <div class="summary-section">
            <h6 class="mb-3">
                <i class="fas fa-chart-bar me-2"></i>
                Resumen de Compras
            </h6>
            <div class="summary-stats">
                <div class="stat-item">
                    <i class="fas fa-shopping-cart text-success"></i>
                    <small class="d-block text-muted">Total Compras</small>
                    <span class="stat-value text-success" id="total-compras">0</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock text-warning"></i>
                    <small class="d-block text-muted">Pendientes</small>
                    <span class="stat-value text-warning" id="compras-pendientes">0</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-check-circle text-primary"></i>
                    <small class="d-block text-muted">Completadas</small>
                    <span class="stat-value text-primary" id="compras-completadas">0</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-dollar-sign text-info"></i>
                    <small class="d-block text-muted">Valor Total</small>
                    <span class="stat-value text-info" id="valor-total">S/. 0.00</span>
                </div>
            </div>
        </div>

        <!-- Botones de acción flotantes -->
        <div class="action-buttons">
            <a href="{{ route('compras.create') }}" class="btn-action" title="Crear nueva compra">
                <i class="fas fa-plus me-2"></i>
                Nueva Compra
            </a>
            <a href="#" class="btn-action alta-rapida" title="Ver reportes">
                <i class="fas fa-chart-line me-2"></i>
                Ver Reportes
            </a>
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

                            if (data === 'completado') {
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

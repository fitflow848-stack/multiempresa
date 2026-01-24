@extends('layout.app')

@section('title', 'Gestión de Compras')
@section('page-title', 'Gestión de Compras')

@section('content')
    <style>
        :root {
            --primary-soft: #eef2ff;
            --accent-color: #4f46e5;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        }

        .stat-card {
            transition: transform 0.2s;
            border: none;
            border-radius: 12px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.02);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-completado {
            background: #dcfce7;
            color: #166534;
        }

        .status-pendiente {
            background: #fef9c3;
            color: #854d0e;
        }

        .status-cancelado {
            background: #fee2e2;
            color: #991b1b;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            border-color: var(--accent-color);
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-1">Panel de Compras</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Área Compras</a></li>
                        <li class="breadcrumb-item active">Listado</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="{{ route('compras.create') }}"
                    class="btn btn-primary px-4 py-2 shadow-sm bg-gradient-quote border-0">
                    <i class="fas fa-plus-circle me-2"></i>Nueva Compra
                </a>
            </div>
        </div>

        <div class="table-container shadow-sm">
            <div class="row g-3 mb-4 pb-3 border-bottom">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i
                                class="fas fa-search"></i></span>
                        <input type="text" id="proveedor-filter" class="form-control border-start-0"
                            placeholder="Buscar proveedor...">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" id="fecha-desde" class="form-control" title="Fecha inicial">
                </div>
                <div class="col-md-2">
                    <input type="date" id="fecha-hasta" class="form-control" title="Fecha final">
                </div>
                <div class="col-md-2">
                    <select id="estado-filter" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="completado">Completado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button id="limpiar-filtros" class="btn btn-light px-3 me-2">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button id="aplicar-filtros" class="btn btn-dark px-4">
                        Filtrar Resultados
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="compras-table" class="table table-hover align-middle" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">ID</th>
                            <th class="border-0">FECHA EMISIÓN</th>
                            <th class="border-0">PROVEEDOR</th>
                            <th class="border-0 text-center">TOTAL</th>
                            <th class="border-0 text-center">ESTADO</th>
                            <th class="border-0 text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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

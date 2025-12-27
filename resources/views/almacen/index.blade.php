@extends('layout.app')

@section('content')
    {{-- ===================== STYLES ===================== --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/principal.css') }}">

    <div class="almacen-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            Área Almacén > Inventarios
            <div style="float: right;">
                Local: <strong>{{ $company->nombre_comercial }}</strong> | Operador: <strong>{{ $user->name }}</strong>
            </div>
        </div>

        <!-- Título -->
        <div class="page-title">
            Inventario Stock Almacén
        </div>

        <!-- Sección de búsqueda -->
        <div class="search-section">
            <form id="search-form">
                <div class="search-row">
                    <div class="form-group">
                        <label>Local:</label>
                        <select class="form-control" name="sucursal" id="sucursal-select">
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Producto:</label>
                        <input type="text" class="form-control" name="producto" value="">
                    </div>

                    <div class="form-group">
                        <label>Existencias:</label>
                        <select class="form-control" name="existencias">
                            <option value="Todos">Todos</option>
                            <option value="Con Stock">Con Stock</option>
                            <option value="Sin Stock">Sin Stock</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>CB:</label>
                        <input type="text" class="form-control" name="codigo_barras" placeholder="">
                    </div>

                    <div style="display: flex; gap: 5px;">
                        <button type="button"
                            style="padding: 6px 8px; border: 1px solid #ccc; background: white; border-radius: 3px; font-size: 10px;">📋</button>
                        <button type="submit" class="btn-search">Buscar</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de inventario -->
        <div class="inventory-table">
            <div class="table-header">
                Stock Almacén
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th style="width: 80px;">Almacén</th>
                        <th style="width: 100px;">Inventariado</th>
                        <th style="width: 100px;">Último Movimiento</th>
                        <th style="width: 300px;">Producto</th>
                        <th style="width: 80px;">Existencias</th>
                        <th style="width: 60px;">Costo</th>
                        <th style="width: 60px;">PVP</th>
                        <th style="width: 60px;">PVPD</th>
                        <th style="width: 60px;">PVC</th>
                        <th style="width: 60px;">PVCD</th>
                        <th style="width: 80px;">PV/Emp</th>
                        <th style="width: 80px;">PV/Doc</th>
                        <th style="width: 50px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($productos) && count($productos) > 0)
                        @foreach ($productos as $index => $p)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>Principal</td>
                                <td>-</td>
                                <td>-</td>
                                <td>{{ $p->producto ?? '-' }}</td>
                                <td>{{ number_format($p->existencias, 2) }}</td>
                                <td>{{ isset($p->costo) ? number_format($p->costo, 2) : '-' }}</td>
                                <td>{{ isset($p->pvp) ? number_format($p->pvp, 2) : '-' }}</td>
                                <td>{{ isset($p->pvpd) ? number_format($p->pvpd, 2) : '-' }}</td>
                                <td>{{ isset($p->pvc) ? number_format($p->pvc, 2) : '-' }}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>
                                    <a href="{{ route('almacen.ajustar-existencias', $p->producto_id) }}"
                                        class="btn btn-sm btn-primary">Ajustar</a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="14">No se encontraron productos en almacén.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Resumen -->
        <div class="summary-section">
            <div class="summary-title">Resumen</div>
            <div class="summary-stats">
                <div class="stat-item">
                    Total Productos: <span class="stat-value">33</span>
                </div>
                <div class="stat-item">
                    Con Stock: <span class="stat-value">8</span>
                </div>
                <div class="stat-item">
                    Sin Stock: <span class="stat-value">25</span>
                </div>
                <div class="stat-item">
                    Valor Total: <span class="stat-value">S/ 3,221.25</span>
                </div>
            </div>
        </div>

        <!-- Botones de acción flotantes -->
        <div class="action-buttons">
            <button class="btn-action" onclick="window.location='#'">
                📊 Ver Inventarios
            </button>
            <button class="btn-action alta-rapida" onclick="window.location='{{ route('almacen.alta-rapida') }}'">
                🚀 Alta Rápida...
            </button>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

@endsection

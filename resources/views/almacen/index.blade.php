@extends('layout.app')

@section('title', 'Inventario Stock Almacén')
@section('page-title', 'Inventario Stock Almacén')
@section('breadcrumb')
    <li class="breadcrumb-item">Área Almacén</li>
    <li class="breadcrumb-item active">Inventarios</li>
@endsection

<link rel="stylesheet" href="{{ asset('css/modules-common.css') }}">

@section('content')
    <div class="module-container">
        <!-- Sección de búsqueda mejorada -->
        <div class="search-section">
            <h6 class="mb-3">
                <i class="fas fa-search me-2"></i>
                Filtros de Búsqueda
            </h6>
            <form id="search-form">
                <div class="search-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-store me-1"></i>
                            Local:
                        </label>
                        <select class="form-control" name="sucursal" id="sucursal-select">
                            @if (isset($sucursales) && count($sucursales) > 0)
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            @else
                                <option value="">No hay sucursales disponibles</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-box me-1"></i>
                            Producto:
                        </label>
                        <input type="text" class="form-control" name="producto" placeholder="Buscar por nombre...">
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-layer-group me-1"></i>
                            Existencias:
                        </label>
                        <select class="form-control" name="existencias">
                            <option value="Todos">Todos</option>
                            <option value="Con Stock">Con Stock</option>
                            <option value="Sin Stock">Sin Stock</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-barcode me-1"></i>
                            Código de Barras:
                        </label>
                        <input type="text" class="form-control" name="codigo_barras"
                            placeholder="Escanear o escribir...">
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" title="Escanear código">
                                <i class="fas fa-qrcode"></i>
                            </button>
                            <button type="submit" class="btn-search">
                                <i class="fas fa-search me-1"></i>
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de inventario mejorada -->
        <div class="inventory-section">
            <div class="section-header">
                <i class="fas fa-table me-2"></i>
                Stock Almacén - Inventario
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 30px;" class="text-center">#</th>
                            <th style="width: 80px;" class="text-center">Almacén</th>
                            <th style="width: 100px;" class="text-center">Inventariado</th>
                            <th style="width: 100px;" class="text-center">Último Movimiento</th>
                            <th style="width: 300px;">Producto</th>
                            <th style="width: 80px;" class="text-center">Stock</th>
                            <th style="width: 60px;" class="text-center">Costo</th>
                            <th style="width: 60px;" class="text-center">PVP</th>
                            <th style="width: 60px;" class="text-center">PVPD</th>
                            <th style="width: 60px;" class="text-center">PVC</th>
                            <th style="width: 60px;" class="text-center">PVCD</th>
                            <th style="width: 80px;" class="text-center">PV/Emp</th>
                            <th style="width: 80px;" class="text-center">PV/Doc</th>
                            <th style="width: 120px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($productos) && count($productos) > 0)
                            @foreach ($productos as $index => $p)
                                <tr>
                                    <td class="text-center">
                                        <strong>{{ $index + 1 }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">Principal</span>
                                    </td>
                                    <td class="text-center text-muted">
                                        <small>-</small>
                                    </td>
                                    <td class="text-center text-muted">
                                        <small>-</small>
                                    </td>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-name">
                                                {{ $p->producto ?? '-' }}
                                            </div>
                                            @if (isset($p->codigo))
                                                <small class="text-muted">Código: {{ $p->codigo }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $stock = $p->existencias ?? 0;
                                            $stockClass = $stock > 0 ? 'text-success' : 'text-danger';
                                        @endphp
                                        <span class="{{ $stockClass }} fw-bold">
                                            {{ number_format($stock, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if (isset($p->costo))
                                            <small>S/. {{ number_format($p->costo, 2) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if (isset($p->pvp))
                                            <small class="text-success fw-bold">S/. {{ number_format($p->pvp, 2) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if (isset($p->pvpd))
                                            <small>S/. {{ number_format($p->pvpd, 2) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if (isset($p->pvc))
                                            <small>S/. {{ number_format($p->pvc, 2) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted">
                                        <small>-</small>
                                    </td>
                                    <td class="text-center text-muted">
                                        <small>-</small>
                                    </td>
                                    <td class="text-center text-muted">
                                        <small>-</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('almacen.ajustar-existencias', $p->producto_id) }}"
                                            class="btn-ajustar" title="Ajustar existencias">
                                            <i class="fas fa-balance-scale me-1"></i>
                                            Ajustar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="14" class="text-center-table">
                                    <div class="py-4">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <p class="mb-0">No se encontraron productos en almacén.</p>
                                        <small class="text-muted">Intente ajustar los filtros de búsqueda.</small>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resumen mejorado -->
        <div class="summary-section">
            <h6 class="mb-3">
                <i class="fas fa-chart-bar me-2"></i>
                Resumen del Inventario
            </h6>
            <div class="summary-stats">
                <div class="stat-item">
                    <i class="fas fa-boxes text-primary"></i>
                    <small class="d-block text-muted">Total Productos</small>
                    <span class="stat-value text-primary">
                        {{ isset($productos) ? count($productos) : 0 }}
                    </span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <small class="d-block text-muted">Con Stock</small>
                    <span class="stat-value text-success">
                        @php
                            $conStock = isset($productos)
                                ? $productos
                                    ->filter(function ($p) {
                                        return ($p->existencias ?? 0) > 0;
                                    })
                                    ->count()
                                : 0;
                        @endphp
                        {{ $conStock }}
                    </span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    <small class="d-block text-muted">Sin Stock</small>
                    <span class="stat-value text-warning">
                        @php
                            $sinStock = isset($productos)
                                ? $productos
                                    ->filter(function ($p) {
                                        return ($p->existencias ?? 0) <= 0;
                                    })
                                    ->count()
                                : 0;
                        @endphp
                        {{ $sinStock }}
                    </span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-dollar-sign text-info"></i>
                    <small class="d-block text-muted">Valor Total</small>
                    <span class="stat-value text-info">
                        @php
                            $valorTotal = isset($productos)
                                ? $productos->sum(function ($p) {
                                    return ($p->existencias ?? 0) * ($p->costo ?? 0);
                                })
                                : 0;
                        @endphp
                        S/. {{ number_format($valorTotal, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Botones de acción flotantes mejorados -->
        <div class="action-buttons">
            <a href="#" class="btn-action" title="Ver reportes de inventario">
                <i class="fas fa-chart-line me-2"></i>
                Ver Inventarios
            </a>
            <a href="{{ route('almacen.alta-rapida') }}" class="btn-action alta-rapida"
                title="Registro rápido de productos">
                <i class="fas fa-plus-circle me-2"></i>
                Alta Rápida
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

@endsection

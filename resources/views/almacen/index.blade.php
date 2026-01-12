@extends('layout.app')

@section('title', 'Inventario Stock Almacén')
@section('page-title', 'Inventario Stock Almacén')

<link rel="stylesheet" href="{{ asset('css/modules-common.css') }}">

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Inventario Stock Almacén</h1>
            <small class="text-muted">Gestión de existencias y ajustes</small>
        </div>
        <div>
            <a href="{{ route('almacen.alta-rapida') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Alta Rápida
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('almacen.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Local</label>
                    <select name="sucursal" class="form-select">
                        <option value="">Todos</option>
                        @if (isset($sucursales))
                            @foreach ($sucursales as $s)
                                <option value="{{ $s->id }}" {{ request('sucursal') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Producto</label>
                    <input type="text" name="producto" class="form-control" placeholder="Nombre o código" value="{{ request('producto') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Existencias</label>
                    <select name="existencias" class="form-select">
                        <option value="">Todos</option>
                        <option value="con" {{ request('existencias')=='con' ? 'selected' : '' }}>Con Stock</option>
                        <option value="sin" {{ request('existencias')=='sin' ? 'selected' : '' }}>Sin Stock</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Código</label>
                    <input type="text" name="codigo" class="form-control" placeholder="Código de barras" value="{{ request('codigo') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if(isset($productos) && $productos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Almacén</th>
                                <th>Producto</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Costo</th>
                                <th class="text-center">PVP</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $i => $p)
                                <tr>
                                    <td class="fw-bold">{{ $i + 1 }}</td>
                                    <td>{{ $p->almacen_nombre ?? 'Principal' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $p->producto ?? '-' }}</div>
                                        @if(isset($p->codigo))
                                            <small class="text-muted">Código: {{ $p->codigo }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center {{ ($p->existencias ?? 0) > 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ number_format($p->existencias ?? 0, 2) }}</td>
                                    <td class="text-center">{{ isset($p->costo) ? 'S/. '.number_format($p->costo,2) : '-' }}</td>
                                    <td class="text-center text-success fw-bold">{{ isset($p->pvp) ? 'S/. '.number_format($p->pvp,2) : '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('almacen.ajustar-existencias', $p->producto_id ?? $p->id) }}" class="btn btn-sm btn-outline-secondary">Ajustar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $productos->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron productos</h5>
                    <p class="text-muted">Ajusta los filtros o registra productos nuevos.</p>
                    <a href="{{ route('almacen.alta-rapida') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Alta Rápida
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Resumen simple -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Total Productos</small>
                    <div class="h5">{{ isset($productos) ? $productos->total() : 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Con Stock</small>
                    <div class="h5">{{ isset($conStock) ? $conStock : (isset($productos) ? $productos->where('existencias', '>', 0)->count() : 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Sin Stock</small>
                    <div class="h5">{{ isset($sinStock) ? $sinStock : (isset($productos) ? $productos->where('existencias', '<=', 0)->count() : 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Valor Total</small>
                    <div class="h5">S/. {{ isset($valorTotal) ? number_format($valorTotal,2) : (isset($productos) ? number_format($productos->sum(function($p){ return ($p->existencias ?? 0) * ($p->costo ?? 0); }),2) : '0.00') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

@endsection

@extends('layout.app')

@section('content')
<style>
    .almacen-container {
        padding: 20px;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 20px;
        font-size: 12px;
        color: #666;
    }

    .page-title {
        color: #d32f2f;
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
    }

    .search-section {
        background: white;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
    }

    .search-row {
        display: flex;
        gap: 10px;
        align-items: end;
        flex-wrap: wrap;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        min-width: 120px;
    }

    .form-group label {
        font-size: 11px;
        color: #666;
        margin-bottom: 3px;
    }

    .form-control {
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 11px;
    }

    .btn-search {
        background: #17a2b8;
        color: white;
        padding: 6px 15px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        font-size: 11px;
        height: fit-content;
    }

    .btn-search:hover {
        background: #138496;
    }

    .inventory-table {
        background: white;
        border-radius: 5px;
        overflow: hidden;
        border: 1px solid #ddd;
    }

    .table-header {
        background: #f8f9fa;
        padding: 10px 15px;
        font-weight: bold;
        font-size: 12px;
        color: #333;
        border-bottom: 1px solid #ddd;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }

    .table th {
        background: #f8f9fa;
        padding: 8px;
        text-align: center;
        border: 1px solid #ddd;
        font-size: 10px;
        font-weight: bold;
        color: #666;
    }

    .table td {
        padding: 6px 8px;
        border: 1px solid #e0e0e0;
        text-align: center;
        font-size: 10px;
    }

    .table tbody tr:hover {
        background: #f5f5f5;
    }

    .actions-cell {
        position: relative;
    }

    .action-btn {
        background: none;
        border: none;
        padding: 5px;
        cursor: pointer;
        font-size: 14px;
        color: #666;
    }

    .action-btn:hover {
        color: #333;
    }

    .product-link {
        color: #333;
        text-decoration: none;
        cursor: pointer;
    }

    .product-link:hover {
        color: #0066cc;
        text-decoration: underline;
    }

    .summary-section {
        background: white;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
        border: 1px solid #ddd;
    }

    .summary-title {
        font-weight: bold;
        color: #d32f2f;
        margin-bottom: 10px;
        font-size: 12px;
    }

    .summary-stats {
        display: flex;
        gap: 20px;
        font-size: 11px;
    }

    .stat-item {
        color: #666;
    }

    .stat-value {
        color: #17a2b8;
        font-weight: bold;
    }

    .action-buttons {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        gap: 10px;
    }

    .btn-action {
        background: #17a2b8;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .btn-action.alta-rapida {
        background: #28a745;
    }

    .btn-action:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
</style>

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
        Inventario Inicial mayo 2022 - Pendiente > Stock Almacén
    </div>

    <!-- Sección de búsqueda -->
    <div class="search-section">
        <form id="search-form">
            <div class="search-row">
                <div class="form-group">
                    <label>Local:</label>
                    <select class="form-control" name="local">
                        <option value="PURINA">PURINA</option>
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
                    <button type="button" style="padding: 6px 8px; border: 1px solid #ccc; background: white; border-radius: 3px; font-size: 10px;">📋</button>
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
                @foreach($productos as $index => $producto)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>PURINA</td>
                    <td>{{ $producto['almacen'] }}</td>
                    <td>{{ $producto['fecha'] }}</td>
                    <td class="text-left">
                        <a href="{{ route('almacen.ajustar-existencias', $producto['id']) }}" class="product-link">
                            <strong>{{ $producto['codigo'] }}</strong> {{ $producto['producto'] }}
                        </a>
                    </td>
                    <td>{{ $producto['existencias'] }}</td>
                    <td>{{ $producto['costo'] }}</td>
                    <td>{{ $producto['pvp'] }}</td>
                    <td>{{ $producto['pvpd'] }}</td>
                    <td>{{ $producto['pvc'] }}</td>
                    <td>{{ $producto['pvcd'] }}</td>
                    <td>{{ $producto['pv_emp'] }}</td>
                    <td>{{ $producto['pv_doc'] }}</td>
                    <td class="actions-cell">
                        <div style="display: flex; gap: 5px;">
                            <button class="action-btn" onclick="editProduct({{ $producto['id'] }})" title="Editar">
                                ⚙️
                            </button>
                            <button class="action-btn" onclick="showActions({{ $producto['id'] }})" title="Más acciones">
                                ⚫
                            </button>
                            <button class="action-btn" onclick="showMenu({{ $producto['id'] }})" title="Menú">
                                ⋮
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
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

@push('scripts')
<script>
document.getElementById('search-form').addEventListener('submit', function(e) {
    e.preventDefault();
    // TODO: Implementar búsqueda AJAX
    console.log('Buscando productos...');
});

function editProduct(id) {
    window.location.href = `{{ url('almacen/ajustar-existencias') }}/${id}`;
}

function showActions(id) {
    console.log('Mostrar acciones para producto:', id);
    // TODO: Implementar menú de acciones
}

function showMenu(id) {
    console.log('Mostrar menú para producto:', id);
    // TODO: Implementar menú contextual
}
</script>
@endpush
@endsection
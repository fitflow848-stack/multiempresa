@extends('layout.app')

@section('content')
@push('styles')
<style>
    .ajuste-container {
        padding: 20px;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .ajuste-form {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        max-width: 500px;
        width: 100%;
    }

    .form-title {
        color: #d32f2f;
        text-align: center;
        margin-bottom: 10px;
        font-size: 16px;
        font-weight: bold;
    }

    .form-subtitle {
        color: #d32f2f;
        text-align: center;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .product-name {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: bold;
        color: #333;
        font-size: 12px;
    }

    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        align-items: center;
    }

    .form-label {
        min-width: 140px;
        text-align: right;
        font-size: 12px;
        color: #555;
        font-weight: 500;
    }

    .form-input {
        flex: 1;
        padding: 8px 10px;
        border: 2px solid #ddd;
        border-radius: 5px;
        font-size: 12px;
        text-align: center;
    }

    .form-input:focus {
        outline: none;
        border-color: #17a2b8;
    }

    .form-input.highlight {
        border-color: #17a2b8;
        background: #f0f9ff;
    }

    .stock-info {
        background: #e3f2fd;
        padding: 8px 10px;
        border-radius: 5px;
        font-size: 12px;
        text-align: center;
        font-weight: bold;
        color: #1976d2;
        min-width: 80px;
    }

    .section-title {
        margin: 25px 0 15px 0;
        font-size: 13px;
        font-weight: bold;
        color: #333;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 30px;
    }

    .btn {
        padding: 10px 25px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
        font-weight: bold;
        min-width: 120px;
    }

    .btn-primary {
        background: #17a2b8;
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .back-link {
        color: #17a2b8;
        text-decoration: none;
        font-size: 11px;
        margin-bottom: 20px;
        display: inline-block;
    }

    .back-link:hover {
        text-decoration: underline;
    }
</style>
@endpush

<div class="ajuste-container">
    <div class="ajuste-form">
        <a href="{{ route('almacen.index') }}" class="back-link">← Volver al inventario</a>
        
        <div class="form-title">Inventario Inicial mayo 2022 - Pendiente > Stock Almacén</div>
        <div class="form-subtitle">Ajustar Existencias</div>
        
        <div class="product-name">
            {{ $producto['nombre'] }}
        </div>

        <form action="{{ route('almacen.guardar-ajuste', $producto['id']) }}" method="POST">
            @csrf
            
            <div class="form-row">
                <label class="form-label">Existencias kardex</label>
                <div class="stock-info">{{ $producto['existencias_kardex'] }}</div>
            </div>

            <div class="form-row">
                <label class="form-label">Ajuste Existencias</label>
                <div class="stock-info">{{ $producto['ajuste_existencias'] }}</div>
            </div>

            <div class="form-row">
                <label class="form-label">Existencias Físico</label>
                <input type="number" class="form-input highlight" name="existencias_fisico" 
                       value="{{ $producto['existencias_fisico'] }}" step="0.01" required>
            </div>

            <div class="form-row">
                <label class="form-label">Precio Compra</label>
                <input type="number" class="form-input highlight" name="precio_compra" 
                       value="{{ $producto['precio_compra'] }}" step="0.01" required>
            </div>

            <div class="form-row">
                <label class="form-label">Costo Operativo</label>
                <input type="number" class="form-input highlight" name="costo_operativo" 
                       value="{{ $producto['costo_operativo'] }}" step="0.01">
            </div>

            <div class="form-row">
                <label class="form-label">Peso (KGM)</label>
                <input type="number" class="form-input" name="peso" 
                       value="{{ $producto['peso'] }}" step="0.01">
            </div>

            <div class="section-title">Precios de Venta</div>

            <div class="form-row">
                <label class="form-label">PVP</label>
                <input type="number" class="form-input highlight" name="pvp" 
                       value="{{ $producto['pvp'] }}" step="0.01" required>
            </div>

            <div class="form-row">
                <label class="form-label">PVP/Dcto.</label>
                <input type="number" class="form-input highlight" name="pvp_dcto" 
                       value="{{ $producto['pvp_dcto'] }}" step="0.01">
            </div>

            <div class="form-row">
                <label class="form-label">PVC</label>
                <input type="number" class="form-input highlight" name="pvc" 
                       value="{{ $producto['pvc'] }}" step="0.01">
            </div>

            <div class="form-row">
                <label class="form-label">PVC/Dcto.</label>
                <input type="number" class="form-input highlight" name="pvc_dcto" 
                       value="{{ $producto['pvc_dcto'] }}" step="0.01">
            </div>

            <div class="form-row">
                <label class="form-label">PV/Docena</label>
                <input type="number" class="form-input highlight" name="pv_docena" 
                       value="{{ $producto['pv_docena'] }}" step="0.01">
            </div>

            <div class="action-buttons">
                <button type="button" class="btn btn-primary" onclick="modificar()">
                    ✏️ Modificar...
                </button>
                <button type="submit" class="btn btn-secondary">
                    📦 Volver Stock
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function modificar() {
    if (confirm('¿Estás seguro que deseas guardar los cambios?')) {
        document.querySelector('form').submit();
    }
}

// Auto-focus en el primer campo destacado
document.addEventListener('DOMContentLoaded', function() {
    const firstHighlight = document.querySelector('.form-input.highlight');
    if (firstHighlight) {
        firstHighlight.focus();
        firstHighlight.select();
    }
});
</script>
@endpush
@endsection
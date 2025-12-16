@extends('layout.app')

@section('content')
<style>
    .alta-container {
        padding: 20px;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 40px;
    }

    .alta-form {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        max-width: 600px;
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
        margin-bottom: 30px;
        font-size: 14px;
    }

    .form-row {
        display: flex;
        gap: 15px;
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

    .required {
        color: #d32f2f;
        margin-left: 5px;
    }

    .form-input, .form-select {
        flex: 1;
        padding: 8px 10px;
        border: 2px solid #ddd;
        border-radius: 5px;
        font-size: 12px;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: #17a2b8;
    }

    .form-input.highlight, .form-select.highlight {
        border-color: #17a2b8;
        background: #f0f9ff;
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #555;
    }

    .checkbox-item input[type="checkbox"] {
        transform: scale(1.2);
    }

    .section-title {
        margin: 25px 0 15px 0;
        font-size: 13px;
        font-weight: bold;
        color: #d32f2f;
        text-align: center;
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
        min-width: 140px;
    }

    .btn-back {
        background: #17a2b8;
        color: white;
    }

    .btn-next {
        background: #d32f2f;
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

    .input-group {
        display: flex;
        gap: 10px;
        flex: 1;
    }

    .input-prefix {
        background: #f8f9fa;
        border: 2px solid #ddd;
        border-right: none;
        border-radius: 5px 0 0 5px;
        padding: 8px 10px;
        font-size: 12px;
        color: #666;
        min-width: 30px;
        text-align: center;
    }

    .input-with-prefix {
        border-radius: 0 5px 5px 0;
        border-left: none;
    }
</style>

<div class="alta-container">
    <div class="alta-form">
        <a href="{{ route('almacen.index') }}" class="back-link">← Volver al inventario</a>
        
        <div class="form-title">Alta Rápida</div>
        <div class="form-subtitle">Nuevo Producto</div>

        <form action="{{ route('almacen.guardar-producto') }}" method="POST">
            @csrf
            
            <div class="form-row">
                <label class="form-label">Lab. hab<span class="required">*</span></label>
                <input type="text" class="form-input" name="laboratorio" required>
            </div>

            <div class="form-row">
                <label class="form-label">Fam/Subfam<span class="required">*</span></label>
                <div class="input-group">
                    <div class="input-prefix">1</div>
                    <input type="text" class="form-input input-with-prefix highlight" 
                           name="familia_subfamilia" value="NUTRIENTES -..." required>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-input" name="nombre">
            </div>

            <div class="form-row">
                <label class="form-label">Marca<span class="required">*</span></label>
                <input type="text" class="form-input" name="marca" value="Generico" required>
            </div>

            <div class="form-row">
                <label class="form-label">Unidad Medida<span class="required">*</span></label>
                <select class="form-select highlight" name="unidad_medida" required>
                    <option value="NIU - UNIDAD (BIENES)" selected>NIU - UNIDAD (BIENES)</option>
                    <option value="KGM - KILOGRAMO">KGM - KILOGRAMO</option>
                    <option value="LTR - LITRO">LTR - LITRO</option>
                </select>
            </div>

            <div class="form-row">
                <label class="form-label">Tipo Impuesto</label>
                <select class="form-select" name="tipo_impuesto">
                    <option value="Gravado - Operacion Onerosa" selected>Gravado - Operacion Onerosa</option>
                    <option value="Exonerado">Exonerado</option>
                    <option value="Inafecto">Inafecto</option>
                </select>
            </div>

            <div class="form-row">
                <label class="form-label">Opciones Avanzadas</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="opciones_avanzadas" name="opciones_avanzadas">
                        <label for="opciones_avanzadas"></label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">Condición de venta</label>
                <select class="form-select highlight" name="condicion_venta">
                    <option value="Sin Receta Medica" selected>Sin Receta Medica</option>
                    <option value="Con Receta Medica">Con Receta Medica</option>
                </select>
            </div>

            <div class="section-title">Atributos Stock</div>

            <div class="form-row">
                <label class="form-label">Número Serie</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="numero_serie" name="numero_serie">
                        <label for="numero_serie"></label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">Fecha Vencimiento</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="fecha_vencimiento" name="fecha_vencimiento">
                        <label for="fecha_vencimiento"></label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">Lote Producción</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="lote_produccion" name="lote_produccion">
                        <label for="lote_produccion"></label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label">Venta Menudeo</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="venta_menudeo" name="venta_menudeo">
                        <label for="venta_menudeo"></label>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn btn-back" onclick="window.history.back()">
                    ← Volver Presupuestos
                </button>
                <button type="submit" class="btn btn-next">
                    🚀 Siguiente
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus en el primer campo requerido
    const firstRequired = document.querySelector('input[required]');
    if (firstRequired) {
        firstRequired.focus();
    }
});

// Validación del formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = document.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#d32f2f';
            isValid = false;
        } else {
            field.style.borderColor = '#ddd';
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Por favor, completa todos los campos requeridos (marcados con *)');
    }
});
</script>
@endpush
@endsection
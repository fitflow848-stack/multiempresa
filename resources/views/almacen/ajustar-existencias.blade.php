@extends('layout.app')

@section('title', 'Ajustar Existencias')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Ajuste de Existencias</h1>
                <small class="text-muted">Ajusta cantidades y precios del producto</small>
            </div>
            <div>
                <a href="{{ route('almacen.index') }}" class="btn btn-outline-secondary">← Volver al inventario</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h5 class="mb-1">{{ $producto['nombre'] ?? ($producto['nombre'] ?? '-') }}</h5>
                        <small class="text-muted">Código: {{ $producto['codigo'] ?? '-' }}</small>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="badge bg-info text-dark">Existencias Kardex:
                            {{ $producto['existencias_kardex'] ?? '-' }}</div>
                    </div>
                </div>

                <form action="{{ route('almacen.guardar-ajuste', $producto['id'] ?? $producto['id']) }}" method="POST"
                    class="row g-3">
                    @csrf

                    <div class="col-md-4">
                        <label class="form-label">Existencias Físico</label>
                        <input type="number" step="0.01" name="existencias_fisico" class="form-control"
                            value="{{ old('existencias_fisico', $producto['existencias_fisico'] ?? '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Ajuste Existencias</label>
                        <input type="text" class="form-control" value="{{ $producto['ajuste_existencias'] ?? '-' }}"
                            disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Peso (KGM)</label>
                        <input type="number" step="0.01" name="peso" class="form-control"
                            value="{{ old('peso', $producto['peso'] ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Precio Compra</label>
                        <input type="number" step="0.01" name="precio_compra" class="form-control"
                            value="{{ old('precio_compra', $producto['precio_compra'] ?? '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Costo Operativo</label>
                        <input type="number" step="0.01" name="costo_operativo" class="form-control"
                            value="{{ old('costo_operativo', $producto['costo_operativo'] ?? '') }}">
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-2">Precios de Venta</h6>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">PVP</label>
                        <input type="number" step="0.01" name="pvp" class="form-control"
                            value="{{ old('pvp', $producto['pvp'] ?? '') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">PVP/Dcto.</label>
                        <input type="number" step="0.01" name="pvp_dcto" class="form-control"
                            value="{{ old('pvp_dcto', $producto['pvp_dcto'] ?? '') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">PVC</label>
                        <input type="number" step="0.01" name="pvc" class="form-control"
                            value="{{ old('pvc', $producto['pvc'] ?? '') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">PVC/Dcto.</label>
                        <input type="number" step="0.01" name="pvc_dcto" class="form-control"
                            value="{{ old('pvc_dcto', $producto['pvc_dcto'] ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">PV/Docena</label>
                        <input type="number" step="0.01" name="pv_docena" class="form-control"
                            value="{{ old('pv_docena', $producto['pv_docena'] ?? '') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observación</label>
                        <textarea name="observacion" class="form-control" rows="2" placeholder="Ej: Ajuste por rotura, ingreso inicial, etc.">{{ old('observacion') }}</textarea>
                    </div>

                    <div class="col-12 d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Guardar Ajuste</button>
                        <a href="{{ route('almacen.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>


        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const inputFisico = document.querySelector('input[name="existencias_fisico"]');
                const inputAjuste = document.querySelector('input[disabled]'); // El campo de ajuste
                const kardexOriginal = {{ $producto['existencias_kardex'] ?? 0 }};

                inputFisico.addEventListener('input', function() {
                    const fisico = parseFloat(this.value) || 0;
                    const diferencia = fisico - kardexOriginal;

                    // Formatear con color si es positivo o negativo
                    inputAjuste.value = diferencia.toFixed(2);
                    inputAjuste.style.color = diferencia >= 0 ? 'green' : 'red';
                });
            });
        </script>

    @endsection

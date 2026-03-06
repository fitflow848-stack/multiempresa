@extends('layout.app')

@section('title', 'Editar Cotización')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Editar Cotización {{ $cotizacion->numero }}</h1>
            <small class="text-muted">Modificar cotización de ventas</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cotizaciones.show', $cotizacion->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-eye me-2"></i>Ver
            </a>
            <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Panel de productos -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-search me-2"></i>Búsqueda de productos
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Búsqueda de productos -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <input type="text" id="search-product" class="form-control" 
                                   placeholder="Buscar por nombre, código de barras o referencia...">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary w-100" id="search-btn">
                                <i class="bx bx-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>

                    <!-- Resultados de búsqueda -->
                    <div id="search-results" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-search fa-2x mb-3"></i>
                            <p>Busca productos para agregarlos a la cotización</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de cotización -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Cotización
                    </h5>
                    <small class="text-muted">{{ $cotizacion->numero }}</small>
                </div>
                <div class="card-body">
                    <form id="cotizacion-form">
                        @csrf
                        @method('PUT')
                        
                        <!-- Datos del cliente -->
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <div class="input-group">
                                <input type="text" id="cliente-search" class="form-control" 
                                       placeholder="Buscar cliente..." autocomplete="off"
                                       value="{{ $cotizacion->cliente->nombre }}">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#clienteModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <input type="hidden" id="cliente_id" name="cliente_id" value="{{ $cotizacion->cliente_id }}" required>
                            <div id="cliente-info" class="mt-2">
                                <small class="text-muted">
                                    <strong>Cliente seleccionado:</strong> <span id="cliente-nombre">{{ $cotizacion->cliente->nombre }}</span><br>
                                    <strong>Documento:</strong> <span id="cliente-documento">{{ $cotizacion->cliente->tipo_doc }} {{ $cotizacion->cliente->documento }}</span>
                                </small>
                            </div>
                        </div>

                        <!-- Datos adicionales -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Vigencia (días)</label>
                                <input type="number" name="vigencia_dias" class="form-control" 
                                       value="{{ $cotizacion->vigencia->diffInDays($cotizacion->fecha) }}" min="1" max="365">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Descuento general</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" id="descuento-general" class="form-control" 
                                           value="{{ $cotizacion->descuento_total }}">
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3" 
                                      placeholder="Observaciones adicionales...">{{ $cotizacion->observaciones }}</textarea>
                        </div>
                    </form>

                    <!-- Lista de productos -->
                    <div class="border rounded mb-3" style="min-height: 200px; max-height: 300px; overflow-y: auto;">
                        <div class="p-3">
                            <h6 class="mb-3">Productos seleccionados</h6>
                            <div id="selected-products">
                                <!-- Los productos se cargan aquí -->
                            </div>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="bg-light p-3 rounded">
                        <div class="row mb-2">
                            <div class="col">Subtotal:</div>
                            <div class="col text-end fw-bold" id="subtotal-display">S/ {{ number_format($cotizacion->subtotal, 2) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">Descuento:</div>
                            <div class="col text-end text-danger" id="descuento-display">-S/ {{ number_format($cotizacion->descuento_total, 2) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">IGV (18%):</div>
                            <div class="col text-end" id="igv-display">S/ {{ number_format($cotizacion->igv, 2) }}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col"><strong>Total:</strong></div>
                            <div class="col text-end"><strong class="fs-5 text-primary" id="total-display">S/ {{ number_format($cotizacion->total, 2) }}</strong></div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-grid gap-2 mt-3">
                        <button type="button" id="update-cotizacion" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Actualizar Cotización
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarCotizacion()">
                            <i class="fas fa-broom me-2"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('cotizaciones.partials.modals')
@endsection

@push('scripts')
<script src="{{ asset('js/cotizaciones.js') }}"></script>
<script>
    // Cargar datos existentes de la cotización
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar productos existentes
        const productosExistentes = @json($cotizacion->detalles->map(function($detalle) {
            return [
                'id' => $detalle->id,
                'producto_id' => $detalle->producto_id,
                'descripcion' => $detalle->descripcion,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'descuento' => $detalle->descuento,
                'subtotal' => $detalle->subtotal,
                'lote' => $detalle->lote,
                'fecha_vencimiento' => $detalle->fecha_vencimiento ? $detalle->fecha_vencimiento->format('Y-m-d') : null
            ];
        }));
        
        // Convertir a formato compatible con el JavaScript
        selectedProducts = productosExistentes.map(product => ({
            id: product.id,
            producto_id: product.producto_id,
            descripcion: product.descripcion,
            cantidad: parseFloat(product.cantidad),
            precio_unitario: parseFloat(product.precio_unitario),
            descuento: parseFloat(product.descuento || 0),
            subtotal: parseFloat(product.subtotal),
            lote: product.lote || '',
            fecha_vencimiento: product.fecha_vencimiento || ''
        }));
        
        // Actualizar la vista
        updateProductsList();
        updateTotals();
        checkSaveButtonState();
        
        // Cambiar el comportamiento del botón de guardar para editar
        document.getElementById('update-cotizacion').addEventListener('click', updateCotizacion);
    });
    
    function updateCotizacion() {
        const clienteId = document.getElementById('cliente_id').value;
        const vigenciaDias = document.querySelector('input[name="vigencia_dias"]').value || 30;
        const observaciones = document.querySelector('textarea[name="observaciones"]').value;
        const descuentoGeneral = parseFloat(document.getElementById('descuento-general').value) || 0;
        
        if (!clienteId) {
            alert('Debe seleccionar un cliente');
            return;
        }
        
        if (selectedProducts.length === 0) {
            alert('Debe agregar al menos un producto');
            return;
        }
        
        // Calcular totales
        const subtotal = selectedProducts.reduce((sum, product) => sum + product.subtotal, 0);
        const subtotalConDescuento = subtotal - descuentoGeneral;
        const igv = subtotalConDescuento * 0.18;
        const total = subtotalConDescuento + igv;
        
        const data = {
            cliente_id: clienteId,
            productos: selectedProducts,
            subtotal: subtotal,
            descuento_total: descuentoGeneral,
            igv: igv,
            total: total,
            observaciones: observaciones,
            vigencia_dias: vigenciaDias
        };
        
        // Deshabilitar botón
        const updateButton = document.getElementById('update-cotizacion');
        updateButton.disabled = true;
        updateButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...';
        
        fetch(`/cotizaciones/{{ $cotizacion->id }}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message);
                    window.location.href = '{{ route("cotizaciones.show", $cotizacion->id) }}';
                }
            } else {
                alert(data.message || 'Error al actualizar la cotización');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar la cotización');
        })
        .finally(() => {
            updateButton.disabled = false;
            updateButton.innerHTML = '<i class="fas fa-save me-2"></i>Actualizar Cotización';
        });
    }
</script>
@endpush
@extends('layout.app')

@section('title', 'Detalle del Producto')
@section('page-title', 'Registrar Producto - Detalle')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Inventario</a></li>
    <li class="breadcrumb-item"><a href="#">Productos</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

<style>
    .card-primary.card-outline {
        border-top: 3px solid #007bff;
    }
    
    .card-success.card-outline {
        border-top: 3px solid #28a745;
    }
    
    .card-info.card-outline {
        border-top: 3px solid #17a2b8;
    }
    
    .card-warning.card-outline {
        border-top: 3px solid #ffc107;
    }
    
    .compact-form .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .compact-form .form-control,
    .compact-form .form-select {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .compact-card .card-header {
        padding: 0.5rem 1rem;
    }
    
    .compact-card .card-body {
        padding: 0.75rem 1rem;
    }
    
    .add-product-header {
        background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        margin-bottom: 0.75rem;
    }
    
    .product-table {
        font-size: 0.8rem;
    }
    
    .product-table th,
    .product-table td {
        padding: 0.5rem 0.75rem;
        vertical-align: middle;
    }
    
    .product-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-color: #dee2e6;
        font-size: 0.75rem;
    }
    
    .btn-action {
        padding: 0.5rem 1rem;
        font-weight: 600;
        border-radius: 0.375rem;
    }
</style>

@section('content')

    <div class="container-fluid py-2">
        <!-- Encabezado compacto -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h5 mb-1">
                    <i class="fas fa-cube text-primary me-2"></i>
                    Registrar Producto — Detalle - {{ $producto_data['nombre'] }}
                </h1>
                <p class="text-muted mb-0 small">Configure las especificaciones detalladas y precios del producto</p>
            </div>
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back()">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('productos.store') }}" id="product-detailed-form" enctype="multipart/form-data">
            @csrf
            @method('POST')

            <!-- Hidden fields con TODOS los datos del step1 -->
            <input type="hidden" name="laboratorio_id" value="{{ old('laboratorio_id', $producto_data['laboratorio_id'] ?? '') }}">
            <input type="hidden" name="familia_id" value="{{ old('familia_id', $producto_data['familia_id'] ?? '') }}">
            <input type="hidden" name="subfamilia_id" value="{{ old('subfamilia_id', $producto_data['subfamilia_id'] ?? '') }}">
            <input type="hidden" name="marca_id" value="{{ old('marca_id', $producto_data['marca_id'] ?? '') }}">
            <input type="hidden" name="unidad_medida_id" value="{{ old('unidad_medida_id', $producto_data['unidad_medida_id'] ?? '') }}">
            <input type="hidden" name="presentacion_id" value="{{ old('presentacion_id', $producto_data['presentacion_id'] ?? '') }}">
            <input type="hidden" name="concentracion_id" value="{{ old('concentracion_id', $producto_data['concentracion_id'] ?? '') }}">
            <input type="hidden" name="tipo_impuesto" value="{{ old('tipo_impuesto', $producto_data['tipo_impuesto'] ?? '') }}">
            <input type="hidden" name="condicion_venta" value="{{ old('condicion_venta', $producto_data['condicion_venta'] ?? '') }}">
            <input type="hidden" name="codigo_personalizado" value="{{ old('codigo_personalizado', $producto_data['codigo_personalizado'] ?? '') }}">
            <input type="hidden" name="notas" value="{{ old('notas', $producto_data['notas'] ?? '') }}">
            <input type="hidden" name="opciones_avanzadas" value="{{ old('opciones_avanzadas', $producto_data['opciones_avanzadas'] ?? 0) }}">
            <input type="hidden" name="attr_numero_serie" value="{{ old('attr_numero_serie', $producto_data['attr_numero_serie'] ?? 0) }}">
            <input type="hidden" name="attr_fecha_vencimiento" value="{{ old('attr_fecha_vencimiento', $producto_data['attr_fecha_vencimiento'] ?? 0) }}">
            <input type="hidden" name="attr_lote_produccion" value="{{ old('attr_lote_produccion', $producto_data['attr_lote_produccion'] ?? 0) }}">
            <input type="hidden" name="attr_venta_menudeo" value="{{ old('attr_venta_menudeo', $producto_data['attr_venta_menudeo'] ?? 0) }}">
            <input type="hidden" name="nombre" value="{{ old('nombre', $producto_data['nombre'] ?? '') }}">
            
            <!-- Campos de características como JSON -->
            @if(isset($producto_data['propiedades']) && is_array($producto_data['propiedades']))
                @foreach($producto_data['propiedades'] as $key => $value)
                    <input type="hidden" name="propiedades[{{ $key }}]" value="{{ $value }}">
                @endforeach
            @endif
            
            @if(isset($producto_data['almacenamiento']) && is_array($producto_data['almacenamiento']))
                @foreach($producto_data['almacenamiento'] as $key => $value)
                    <input type="hidden" name="almacenamiento[{{ $key }}]" value="{{ $value }}">
                @endforeach
            @endif
            
            @if(isset($producto_data['seguridad']) && is_array($producto_data['seguridad']))
                @foreach($producto_data['seguridad'] as $key => $value)
                    <input type="hidden" name="seguridad[{{ $key }}]" value="{{ $value }}">
                @endforeach
            @endif
            
            @if(isset($producto_data['ficha_tecnica']) && is_array($producto_data['ficha_tecnica']))
                @foreach($producto_data['ficha_tecnica'] as $key => $value)
                    <input type="hidden" name="ficha_tecnica[{{ $key }}]" value="{{ $value }}">
                @endforeach
            @endif
            
            <!-- Campos de imágenes -->
            <input type="hidden" name="imagen_alt" value="{{ old('imagen_alt', $producto_data['imagen_alt'] ?? '') }}">
            <input type="hidden" name="imagen_titulo" value="{{ old('imagen_titulo', $producto_data['imagen_titulo'] ?? '') }}">
            <input type="hidden" name="imagen_fuente" value="{{ old('imagen_fuente', $producto_data['imagen_fuente'] ?? '') }}">
            
            <!-- Información de imágenes subidas (no los archivos, solo la info) -->
            @if(isset($producto_data['uploaded_images']) && is_array($producto_data['uploaded_images']))
                @foreach($producto_data['uploaded_images'] as $index => $imageInfo)
                    <input type="hidden" name="uploaded_images[{{ $index }}][name]" value="{{ $imageInfo['name'] ?? '' }}">
                    <input type="hidden" name="uploaded_images[{{ $index }}][path]" value="{{ $imageInfo['path'] ?? '' }}">
                    <input type="hidden" name="uploaded_images[{{ $index }}][temp_path]" value="{{ $imageInfo['temp_path'] ?? '' }}">
                @endforeach
            @endif

            <!-- Campos temporales de imágenes desde step1 -->
            @if(isset($producto_data['imagen_principal_temp']))
                <input type="hidden" name="imagen_principal_temp" value="{{ $producto_data['imagen_principal_temp'] }}">
            @endif
            
            @if(isset($producto_data['imagenes_adicionales_temp']) && is_array($producto_data['imagenes_adicionales_temp']))
                @foreach($producto_data['imagenes_adicionales_temp'] as $index => $filename)
                    <input type="hidden" name="imagenes_adicionales_temp[{{ $index }}]" value="{{ $filename }}">
                @endforeach
            @endif

            <!-- Campo para líneas de producto (disgregados) -->
            <input type="hidden" name="product_lines" id="product_lines" value="[]">

            <div class="row g-3">
                <!-- Formulario principal -->
                <div class="col-lg-8">
                    <!-- Información del Producto -->
                    <div class="card card-primary card-outline compact-card">
                        <div class="card-header">
                            <h3 class="card-title h6 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Especificaciones del Producto
                            </h3>
                        </div>
                        <div class="card-body compact-form">
                            <div class="row g-2">
                                <!-- Fila 1 -->
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-barcode text-primary me-1"></i>
                                        Código de Barras
                                    </label>
                                    <input type="text" id="cb" class="form-control" placeholder="Código">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-hashtag text-success me-1"></i>
                                        Cantidad
                                    </label>
                                    <input type="number" id="cantidad" class="form-control" value="1" min="0" placeholder="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-certificate text-warning me-1"></i>
                                        Registro Sanitario
                                    </label>
                                    <input type="text" id="registro" class="form-control" placeholder="Registro">
                                </div>

                                <!-- Fila 2 -->
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-tags text-info me-1"></i>
                                        Lote de Producción
                                    </label>
                                    <input type="text" id="lote" class="form-control" placeholder="Lote">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-flask text-danger me-1"></i>
                                        Principio Activo 1
                                    </label>
                                    <input type="text" id="pa1" class="form-control" placeholder="P. Activo 1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-times text-warning me-1"></i>
                                        Fecha Vencimiento
                                    </label>
                                    <input type="date" id="fecha_venc" class="form-control">
                                </div>

                                <!-- Fila 3 -->
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-flask text-secondary me-1"></i>
                                        Principio Activo 2
                                    </label>
                                    <input type="text" id="pa2" class="form-control" placeholder="P. Activo 2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-dollar-sign text-success me-1"></i>
                                        Precio Compra S/
                                    </label>
                                    <input type="number" step="0.01" id="precio_compra" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-calculator text-info me-1"></i>
                                        Costo Operativo S/
                                    </label>
                                    <input type="number" step="0.01" id="costo_operativo" class="form-control" value="0.00">
                                </div>

                                <!-- Fila 4 -->
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-box text-primary me-1"></i>
                                        Presentación
                                    </label>
                                    <div class="input-group">
                                        <select id="presentacion" class="form-select">
                                            <option value="">-- Seleccionar --</option>
                                            @foreach($presentaciones as $presentacion)
                                                <option value="{{ $presentacion->nombre }}">{{ $presentacion->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-success" id="btn-add-presentacion" title="Agregar nueva presentación">
                                           <i class='bx  bx-plus'></i> 
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-vial text-warning me-1"></i>
                                        Concentración
                                    </label>
                                    <div class="input-group">
                                        <select id="concentracion" class="form-select">
                                            <option value="">-- Seleccionar --</option>
                                            @foreach($concentraciones as $concentracion)
                                                <option value="{{ $concentracion->nombre }}">{{ $concentracion->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-success" id="btn-add-concentracion" title="Agregar nueva concentración">
                                           <i class='bx  bx-plus'></i> 
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-weight text-secondary me-1"></i>
                                        Peso (KG)
                                    </label>
                                    <input type="number" step="0.01" id="peso" class="form-control" value="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel lateral de precios más compacto -->
                <div class="col-lg-4">
                    <!-- Precios de Venta -->
                    <div class="card card-success card-outline compact-card">
                        <div class="card-header">
                            <h3 class="card-title h6 mb-0">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Precios de Venta
                            </h3>
                        </div>
                        <div class="card-body compact-form">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">
                                        <i class="fas fa-tag text-success me-1"></i>
                                        PVP S/
                                    </label>
                                    <input type="number" step="0.01" id="pvp" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">
                                        <i class="fas fa-tags text-info me-1"></i>
                                        PV/Docena S/
                                    </label>
                                    <input type="number" step="0.01" id="pv_docena" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">
                                        <i class="fas fa-percentage text-warning me-1"></i>
                                        PVP/Dcto S/
                                    </label>
                                    <input type="number" step="0.01" id="pvp_dto" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">
                                        <i class="fas fa-coins text-primary me-1"></i>
                                        PVC S/
                                    </label>
                                    <input type="number" step="0.01" id="pvc" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">
                                        <i class="fas fa-percentage text-secondary me-1"></i>
                                        PVC/Dcto S/
                                    </label>
                                    <input type="number" step="0.01" id="pvc_dto" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">
                                        <i class="fas fa-plus-circle text-success me-1"></i>
                                        PVP (Otro)
                                    </label>
                                    <input type="number" step="0.01" id="pvp2" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección para agregar productos -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="add-product-header d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fas fa-plus-circle me-2"></i>
                            <strong>Agregar Producto a la Lista</strong>
                        </div>
                        <button type="button" class="btn btn-light btn-sm" id="btn-add-line">
                            <i class="fas fa-plus me-1"></i>
                            Agregar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de productos compacta -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline compact-card">
                        <div class="card-header">
                            <h3 class="card-title h6 mb-0">
                                <i class="fas fa-list me-2"></i>
                                Lista de Productos
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover product-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:60px">
                                                <i class="fas fa-barcode me-1"></i>
                                                CB
                                            </th>
                                            <th style="width:80px">
                                                <i class="fas fa-code me-1"></i>
                                                Ref
                                            </th>
                                            <th>
                                                <i class="fas fa-box me-1"></i>
                                                Presentación
                                            </th>
                                            <th>
                                                <i class="fas fa-vial me-1"></i>
                                                Concentración
                                            </th>
                                            <th style="width:50px" class="text-center">
                                                <i class="fas fa-hashtag me-1"></i>
                                                Cant.
                                            </th>
                                            <th style="width:70px" class="text-end">
                                                <i class="fas fa-dollar-sign me-1"></i>
                                                PC
                                            </th>
                                            <th style="width:70px" class="text-end">
                                                <i class="fas fa-tag me-1"></i>
                                                PVP
                                            </th>
                                            <th style="width:70px" class="text-end">
                                                <i class="fas fa-percentage me-1"></i>
                                                PVPD
                                            </th>
                                            <th style="width:60px" class="text-end">
                                                <i class="fas fa-weight me-1"></i>
                                                Peso
                                            </th>
                                            <th style="width:40px" class="text-center">-</th>
                                        </tr>
                                    </thead>
                                    <tbody id="product-lines-body">
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-3">
                                                <i class="fas fa-inbox fa-lg mb-1 d-block"></i>
                                                <small>No hay productos agregados</small>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones finales compactas -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card card-warning card-outline compact-card">
                        <div class="card-body">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <button type="button" id="btn-comprar" class="btn btn-success btn-action w-100">
                                        <i class="fas fa-shopping-cart me-1"></i>
                                        Completar
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('productos.step1') }}" class="btn btn-outline-secondary btn-action w-100">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        Volver
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-info border-0 mb-0 py-2 px-3" role="alert">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            Complete los campos y agregue productos
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>

        <!-- Modal para agregar presentación -->
        <div class="modal fade" id="modalPresentacion" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-box me-2"></i>
                            Nueva Presentación
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formPresentacion">
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" id="nombrePresentacion" class="form-control" placeholder="Ej: TABLETAS, CAPSULAS" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea id="descripcionPresentacion" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btnGuardarPresentacion">
                            <i class="fas fa-save me-1"></i>
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para agregar concentración -->
        <div class="modal fade" id="modalConcentracion" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-vial me-2"></i>
                            Nueva Concentración
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formConcentracion">
                            <div class="row g-3">
                                <div class="col-8">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" id="nombreConcentracion" class="form-control" placeholder="Ej: 500, 250" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Unidad</label>
                                    <select id="unidadConcentracion" class="form-select">
                                        <option value="">-</option>
                                        <option value="MG">MG</option>
                                        <option value="ML">ML</option>
                                        <option value="KG">KG</option>
                                        <option value="GR">GR</option>
                                        <option value="LT">LT</option>
                                        <option value="UI">UI</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea id="descripcionConcentracion" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btnGuardarConcentracion">
                            <i class="fas fa-save me-1"></i>
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dependencias JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script>
        (function($){
            'use strict';

            // Mantener un array local de líneas
            let lines = [];

            function renderLines() {
                const $body = $('#product-lines-body');
                
                if (lines.length === 0) {
                    $body.html(`
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">
                                <i class="fas fa-inbox fa-lg mb-1 d-block"></i>
                                <small>No hay productos agregados</small>
                            </td>
                        </tr>
                    `);
                } else {
                    $body.empty();
                    lines.forEach((ln, idx) => {
                        const $tr = $('<tr>');
                        $tr.append(`<td><small class="badge bg-secondary">${(ln.cb || '').substring(0, 8) || 'N/A'}</small></td>`);
                        $tr.append(`<td><small class="text-muted">${ln.codigo_ref ? ln.codigo_ref.substring(0, 8) + '...' : 'AUTO'}</small></td>`);
                        $tr.append(`<td><span class="fw-bold" style="font-size:0.8rem">${(ln.presentacion || 'Sin esp.').substring(0, 15)}</span></td>`);
                        $tr.append(`<td style="font-size:0.75rem">${(ln.concentracion || 'Sin esp.').substring(0, 15)}</td>`);
                        $tr.append(`<td class="text-center"><span class="badge bg-primary">${ln.cantidad}</span></td>`);
                        $tr.append(`<td class="text-end fw-bold text-success" style="font-size:0.8rem">${ln.precio_compra || '0.00'}</td>`);
                        $tr.append(`<td class="text-end fw-bold text-info" style="font-size:0.8rem">${ln.pvp || '0.00'}</td>`);
                        $tr.append(`<td class="text-end fw-bold text-warning" style="font-size:0.8rem">${ln.pvp_dto || '0.00'}</td>`);
                        $tr.append(`<td class="text-end" style="font-size:0.75rem">${ln.peso || '0'}</td>`);
                        $tr.append(`<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" data-idx="${idx}" title="Eliminar"><i class="fas fa-times"></i></button></td>`);
                        $body.append($tr);
                    });
                }

                // actualizar hidden input con JSON
                $('#product_lines').val(JSON.stringify(lines));
            }

            // Función para limpiar formulario
            function clearForm() {
                $('#cb').val('');
                $('#cantidad').val(1);
                $('#registro').val('');
                $('#lote').val('');
                $('#pa1').val('');
                $('#pa2').val('');
                $('#precio_compra').val('');
                $('#pvp').val('');
                $('#pvp_dto').val('');
                $('#peso').val(0);
                $('#presentacion').val('');
                $('#concentracion').val('');
            }

            // Añadir línea cuando se presiona el botón
            $('#btn-add-line').on('click', function(){
                const ln = {
                    cb: $('#cb').val().trim(),
                    codigo_ref: `PROD-${Date.now()}`, // auto-generar código
                    presentacion: $('#presentacion').val() || $('#presentacion option:selected').text(),
                    concentracion: $('#concentracion').val() || $('#concentracion option:selected').text(),
                    cantidad: Number($('#cantidad').val()) || 0,
                    precio_compra: parseFloat($('#precio_compra').val()) || 0,
                    pvp: parseFloat($('#pvp').val()) || 0,
                    pvp_dto: parseFloat($('#pvp_dto').val()) || 0,
                    peso: parseFloat($('#peso').val()) || 0,
                    pa1: $('#pa1').val().trim(),
                    pa2: $('#pa2').val().trim(),
                    lote: $('#lote').val().trim(),
                    fecha_venc: $('#fecha_venc').val(),
                    registro: $('#registro').val().trim(),
                    costo_operativo: parseFloat($('#costo_operativo').val()) || 0,
                    pv_docena: parseFloat($('#pv_docena').val()) || 0,
                    pvc: parseFloat($('#pvc').val()) || 0,
                    pvc_dto: parseFloat($('#pvc_dto').val()) || 0,
                    pvp2: parseFloat($('#pvp2').val()) || 0
                };

                // Validación mejorada
                if (!ln.presentacion && !ln.concentracion && ln.cantidad <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Datos incompletos',
                        text: 'Ingrese al menos Presentación, Concentración y una Cantidad válida.',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }

                lines.push(ln);
                renderLines();
                clearForm();

                // Toast de éxito
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Producto agregado',
                    showConfirmButton: false,
                    timer: 1500
                });
            });

            // Eliminar línea
            $(document).on('click', '.btn-remove-line', function(){
                const idx = Number($(this).data('idx'));
                if (!isNaN(idx)) {
                    Swal.fire({
                        title: '¿Eliminar producto?',
                        text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            lines.splice(idx,1);
                            renderLines();
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Producto eliminado',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    });
                }
            });

            // Completar registro - versión AJAX
            $('#btn-comprar').on('click', function(e){
                e.preventDefault();
                
                const $btn = $(this);
                const originalText = $btn.html();
                
                // Confirmación con SweetAlert
                Swal.fire({
                    title: '¿Completar registro?',
                    text: lines.length > 0 
                        ? `Se registrarán ${lines.length} línea(s) de producto` 
                        : 'Se registrará el producto sin líneas adicionales',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, completar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    
                    // Mostrar loading en botón
                    $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);
                    
                    // Obtener datos del formulario
                    const $form = $('#product-detailed-form');
                    const formData = new FormData($form[0]);
                    
                    // Envío AJAX
                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            // Éxito
                            Swal.fire({
                                icon: 'success',
                                title: '¡Producto guardado!',
                                text: 'El producto se ha registrado correctamente.',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            }).then(() => {
                                // El backend nos dice a dónde ir
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                }
                            });
                        },
                        error: function(jqXHR) {
                            // Restaurar botón
                            $btn.html(originalText).prop('disabled', false);
                            
                            let errorMsg = 'Error al guardar el producto';
                            let errorDetails = '';
                            
                            if (jqXHR.status === 422) {
                                // Errores de validación
                                const errors = jqXHR.responseJSON?.errors;
                                if (errors) {
                                    const errorList = [];
                                    Object.keys(errors).forEach(field => {
                                        errors[field].forEach(msg => errorList.push(msg));
                                    });
                                    errorDetails = '<ul class="text-left mt-2">' + 
                                        errorList.map(msg => `<li>${msg}</li>`).join('') + 
                                        '</ul>';
                                    errorMsg = 'Errores de validación';
                                }
                            } else if (jqXHR.responseJSON?.message) {
                                errorMsg = jqXHR.responseJSON.message;
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: errorMsg,
                                html: errorDetails || 'Revise los datos e intente nuevamente.',
                                confirmButtonText: 'Entendido'
                            });
                        },
                        timeout: 30000 // 30 segundos timeout
                    });
                });
            });

            // Cargar líneas preexistentes si las hay
            $(function(){
                try {
                    const pre = $('#product_lines').val();
                    if (pre && pre !== '[]') {
                        const parsed = JSON.parse(pre);
                        if (Array.isArray(parsed) && parsed.length) {
                            lines = parsed;
                            renderLines();
                        }
                    }
                } catch(e){ 
                    console.warn('Error cargando líneas preexistentes:', e); 
                }
            });

            // Render inicial
            renderLines();

            // Handlers para agregar nueva presentación
            $('#btn-add-presentacion').on('click', function() {
                $('#modalPresentacion').modal('show');
            });

            $('#btnGuardarPresentacion').on('click', function() {
                const nombre = $('#nombrePresentacion').val().trim();
                const descripcion = $('#descripcionPresentacion').val().trim();

                if (!nombre) {
                    Swal.fire('Error', 'El nombre es requerido', 'error');
                    return;
                }

                $.ajax({
                    url: '{{ route('api.presentaciones.store') }}',
                    method: 'POST',
                    data: {
                        nombre: nombre,
                        descripcion: descripcion,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Agregar al select
                            $('#presentacion').append(new Option(response.data.nombre, response.data.nombre));
                            // Seleccionar el nuevo elemento
                            $('#presentacion').val(response.data.nombre);
                            // Cerrar modal
                            $('#modalPresentacion').modal('hide');
                            // Limpiar formulario
                            $('#formPresentacion')[0].reset();
                            // Mostrar mensaje
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.message || 'Error al crear presentación';
                        Swal.fire('Error', error, 'error');
                    }
                });
            });

            // Handlers para agregar nueva concentración
            $('#btn-add-concentracion').on('click', function() {
                $('#modalConcentracion').modal('show');
            });

            $('#btnGuardarConcentracion').on('click', function() {
                const nombre = $('#nombreConcentracion').val().trim();
                const unidad = $('#unidadConcentracion').val().trim();
                const descripcion = $('#descripcionConcentracion').val().trim();

                if (!nombre) {
                    Swal.fire('Error', 'El nombre es requerido', 'error');
                    return;
                }

                // Formatear el nombre con la unidad si existe
                const nombreCompleto = unidad ? `${nombre} ${unidad}` : nombre;

                $.ajax({
                    url: '{{ route('api.concentraciones.store') }}',
                    method: 'POST',
                    data: {
                        nombre: nombreCompleto,
                        descripcion: descripcion,
                        unidad: unidad,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Agregar al select
                            $('#concentracion').append(new Option(response.data.nombre, response.data.nombre));
                            // Seleccionar el nuevo elemento
                            $('#concentracion').val(response.data.nombre);
                            // Cerrar modal
                            $('#modalConcentracion').modal('hide');
                            // Limpiar formulario
                            $('#formConcentracion')[0].reset();
                            // Mostrar mensaje
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.message || 'Error al crear concentración';
                        Swal.fire('Error', error, 'error');
                    }
                });
            });

        })(jQuery);
    </script>
@endsection
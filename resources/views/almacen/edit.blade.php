@extends('layout.app')

@section('title', 'Editar Registro de Almacén')
@section('page-title', 'Editar Registro de Almacén')

@push('styles')
<style>
    .nav-tabs { border-bottom: 2px solid #dee2e6; }
    .nav-link { border: none; font-weight: 500; color: #6c757d; }
    .nav-link.active { color: #007bff; border-bottom: 2px solid #007bff; background: transparent !important; }
    .card-outline { border-top-width: 3px !important; }
    .tab-pane { padding-top: 20px; }
</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('almacen.index') }}">Almacén</a></li>
    <li class="breadcrumb-item active">Editar Producto</li>
@endsection

@section('content')
<div class="container-fluid py-2">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">
                <i class="fas fa-edit text-primary me-2"></i>
                Editar Producto: {{ $producto->nombre }}
            </h1>
            <p class="text-muted mb-0 small">Modifique la información del producto en las diferentes secciones</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back()">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </button>
        </div>
    </div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#datos-basicos">
                <i class="fas fa-cube me-1"></i> Datos Básicos
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#caracteristicas">
                <i class="fas fa-cogs me-1"></i> Características
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ficha-tecnica">
                <i class="fas fa-file-alt me-1"></i> Ficha Técnica
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#imagenes">
                <i class="fas fa-images me-1"></i> Imágenes
            </button>
        </li>
    </ul>

    <form action="{{ route('almacen.edit-detailed', $detalle->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="tab-content">
            <!-- TAB 1: Datos Básicos -->
            <div class="tab-pane fade show active" id="datos-basicos">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-cube me-2"></i>Datos del Producto</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Laboratorio</label>
                                                <select name="laboratorio_id" class="form-select">
                                                    <option value="">-- Seleccionar --</option>
                                                    @foreach($laboratorios as $lab)
                                                        <option value="{{ $lab->id }}" {{ $producto->laboratorio == $lab->id ? 'selected' : '' }}>{{ $lab->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Marca</label>
                                                <select name="marca_id" class="form-select">
                                                    <option value="">-- Seleccionar --</option>
                                                    @foreach($marcas as $mar)
                                                        <option value="{{ $mar->id }}" {{ $producto->marca_id == $mar->id ? 'selected' : '' }}>{{ $mar->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                            <textarea name="nombre" class="form-control" rows="2" required>{{ $producto->nombre }}</textarea>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Unidad de Medida</label>
                                                <select name="unidades_medida_id" class="form-select">
                                                    <option value="">-- Seleccionar --</option>
                                                    @foreach($unidades as $un)
                                                        <option value="{{ $un->id }}" {{ $producto->unidad_medida_id == $un->id ? 'selected' : '' }}>{{ $un->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tipo de Impuesto</label>
                                                <select name="tipo_impuesto" class="form-select">
                                                    <option value="gravado" {{ $producto->tipo_impuesto == 'gravado' ? 'selected' : '' }}>Gravado</option>
                                                    <option value="exonerado" {{ $producto->tipo_impuesto == 'exonerado' ? 'selected' : '' }}>Exonerado</option>
                                                    <option value="inafecto" {{ $producto->tipo_impuesto == 'inafecto' ? 'selected' : '' }}>Inafecto</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card card-info card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="bx bx-cog me-2"></i>Opciones</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="attr_numero_serie" id="check-serie" {{ $producto->attr_numero_serie ? 'checked' : '' }}>
                                            <label class="form-check-label" for="check-serie">Número Serie</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="attr_fecha_vencimiento" id="check-venc" {{ $producto->attr_fecha_vencimiento ? 'checked' : '' }}>
                                            <label class="form-check-label" for="check-venc">Fecha Vencimiento</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="attr_lote_produccion" id="check-lote" {{ $producto->attr_lote_produccion ? 'checked' : '' }}>
                                            <label class="form-check-label" for="check-lote">Lote Producción</label>
                                        </div>
                                        <hr>
                                        <button type="submit" class="btn btn-success w-100">
                                            Siguiente <i class="fas fa-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Características -->
            <div class="tab-pane fade" id="caracteristicas">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">Propiedades Físicas</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            @php 
                                                $props = $producto->caracteristicas['propiedades'] ?? [];
                                                $almacenamiento = $producto->caracteristicas['almacenamiento'] ?? [];
                                                $seguridad = $producto->caracteristicas['seguridad'] ?? [];
                                            @endphp
                                            <div class="col-md-4">
                                                <label class="form-label small">Color</label>
                                                <input type="text" class="form-control" name="caracteristicas[color]" value="{{ $props['color'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Textura</label>
                                                <input type="text" class="form-control" name="caracteristicas[textura]" value="{{ $props['textura'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">pH</label>
                                                <input type="number" step="0.1" class="form-control" name="caracteristicas[ph]" value="{{ $props['ph'] ?? '' }}">
                                            </div>
                                        </div>
                                        <hr>
                                        <h5>Condiciones de Almacenamiento</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label small">Temp. Mín. (°C)</label>
                                                <input type="number" class="form-control" name="almacenamiento[temp_min]" value="{{ $almacenamiento['temp_min'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Temp. Máx. (°C)</label>
                                                <input type="number" class="form-control" name="almacenamiento[temp_max]" value="{{ $almacenamiento['temp_max'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Humedad (%)</label>
                                                <input type="number" class="form-control" name="almacenamiento[humedad]" value="{{ $almacenamiento['humedad'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Ficha Técnica -->
            <div class="tab-pane fade" id="ficha-tecnica">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Información Técnica Detallada</h3>
                            </div>
                            <div class="card-body">
                                @php $ficha = $producto->ficha_tecnica ?? []; @endphp
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">Composición</label>
                                        <textarea class="form-control" name="ficha_tecnica[composicion]" rows="4">{{ $ficha['composicion'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Mecanismo de Acción</label>
                                        <textarea class="form-control" name="ficha_tecnica[mecanismo_accion]" rows="4">{{ $ficha['mecanismo_accion'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Indicaciones de Uso</label>
                                        <textarea class="form-control" name="ficha_tecnica[indicaciones]" rows="4">{{ $ficha['indicaciones'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Contraindicaciones</label>
                                        <textarea class="form-control" name="ficha_tecnica[contraindicaciones]" rows="4">{{ $ficha['contraindicaciones'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Imágenes -->
            <div class="tab-pane fade" id="imagenes">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Galería de Imágenes</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Imagen Principal</label>
                                        @if($producto->imagen_principal)
                                            <div class="mb-2">
                                                <img src="{{ Storage::url($producto->imagen_principal) }}" style="max-width: 200px;" class="img-thumbnail">
                                            </div>
                                        @endif
                                        <input type="file" name="imagen_principal" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Imágenes Adicionales</label>
                                        <input type="file" name="imagenes_adicionales[]" class="form-control" multiple accept="image/*">
                                        <small class="text-muted">Puede seleccionar varias imágenes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

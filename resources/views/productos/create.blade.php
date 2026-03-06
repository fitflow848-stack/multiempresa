@extends('layout.app')

@section('title', 'Nuevo Producto')
@section('page-title', 'Registrar Producto')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Inventario</a></li>
    <li class="breadcrumb-item"><a href="#">Productos</a></li>
    <li class="breadcrumb-item active">Nuevo Producto</li>
@endsection

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet">
<style>
    .options-card {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        margin-top: 20px;
        padding: 1rem;
        border-radius: 0.375rem;
    }

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

    .select-overlay {
        background: transparent;
    }

    /* Estilos para las pestañas */
    .nav-tabs-custom {
        background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 0.5rem 0.5rem 0 0;
        border-bottom: 1px solid #dee2e6;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        border-radius: 0.5rem 0.5rem 0 0;
        color: #6c757d;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        transition: all 0.2s ease;
    }

    .image-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: center;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .image-upload-area:hover {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.05);
    }

    .image-preview {
        max-width: 150px;
        max-height: 150px;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .ficha-editor {
        min-height: 120px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.75rem;
    }

    .compact-card {
        margin-bottom: 1rem;
    }

    .compact-card .card-body {
        padding: 1rem;
    }

    .compact-card .card-header {
        padding: 0.75rem 1rem;
        background-color: rgba(0, 0, 0, 0.03);
    }

    .compact-card .card-title {
        margin-bottom: 0;
        font-size: 0.95rem;
        font-weight: 600;
    }
</style>

@section('content')
    <div class="container-fluid py-2">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">
                    <i class="fas fa-cube text-primary me-2"></i>
                    Registrar Nuevo Producto
                </h1>
                <p class="text-muted mb-0 small">Complete la información del producto en las diferentes secciones</p>
            </div>
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back()">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </button>
            </div>
        </div>

        <!-- Nav tabs estándar de Bootstrap 5 -->
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

        <!-- Formulario para Step 1 -->
        <form id="producto-step1-form" method="POST" action="{{ route('productos.step2') }}" enctype="multipart/form-data">
            @csrf
            <!-- Campo hidden para subfamilia_id -->
            <input type="hidden" id="np-subfamilia" name="subfamilia_id" value="">
            <!-- Campos hidden para presentación y concentración (se manejan en step2) -->
            <input type="hidden" id="np-presentacion" name="presentacion_id" value="">
            <input type="hidden" id="np-concentracion" name="concentracion_id" value="">
            <div class="tab-content">

                <!-- TAB 1 -->
                <div class="tab-pane fade show active" id="datos-basicos">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <!-- Main Form -->
                                <div class="col-lg-8">
                                    <!-- Datos del Producto -->
                                    <div class="card card-primary card-outline">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-cube me-2"></i>
                                                Datos del Producto
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="np-laboratorio" class="form-label">
                                                        <i class="fas fa-flask me-1"></i>
                                                        Laboratorio
                                                    </label>
                                                    <div class="input-group">
                                                        <select id="np-laboratorio" name="laboratorio_id"
                                                            class="form-select">
                                                            <option value="">-- Seleccionar laboratorio --
                                                            </option>
                                                            @foreach ($laboratorios ?? [] as $laboratorio)
                                                                <option value="{{ $laboratorio->id }}">
                                                                    {{ $laboratorio->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-success" id="np-lab-add"
                                                            title="Nuevo Laboratorio">
                                                            <i class='bx bx-plus'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <i class="fas fa-sitemap me-1"></i>
                                                        Familia / Subfamilia
                                                    </label>
                                                    <div class="input-group position-relative">
                                                        <select name="familia_id" id="np-familia" class="form-select" readonly
                                                            style="pointer-events: none;">
                                                            <option value="">-- Seleccionar --</option>
                                                            <option value="nutrientes">NUTRIENTES</option>
                                                            <option value="fertilizantes">FERTILIZANTES</option>
                                                            <option value="varios">VARIOS</option>
                                                        </select>
                                                        <div class="select-overlay"
                                                            style="position: absolute; top: 0; left: 0; right: 42px; bottom: 0; z-index: 10; cursor: pointer;">
                                                        </div>
                                                        <button type="button" class="btn btn-info" id="np-fam-add"
                                                            title="Seleccionar Familia">
                                                            <i class='bx bx-search'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">
                                                        <i class="bx bx-edit me-1"></i>
                                                        Nombre del Producto <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea name="nombre" id="np-nombre" class="form-control" rows="2" required
                                                        placeholder="Ingrese el nombre completo del producto, marca o modelo"></textarea>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <i class="bx bx-label me-1"></i>
                                                        Marca
                                                    </label>
                                                    <div class="input-group">
                                                        <select id="np-marca" name="marca_id" class="form-select">
                                                            <option value="">--Elegir--</option>
                                                            @foreach ($marcas as $marca)
                                                                <option value="{{ $marca->id }}">
                                                                    {{ $marca->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-success" id="np-marca-add"
                                                            title="Nueva Marca">
                                                            <i class='bx bx-plus'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <i class="bx bx-package me-1"></i>
                                                        Unidad de Medida
                                                    </label>
                                                    <div class="input-group">
                                                        <select id="np-unidad" name="unidad_medida_id" class="form-select">
                                                            <option value="">--Elegir--</option>
                                                            @foreach ($unidades as $unidad)
                                                                <option value="{{ $unidad->id }}">
                                                                    {{ $unidad->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-success" id="np-unidad-add"
                                                            title="Nueva Unidad">
                                                            <i class='bx bx-plus'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Opciones del Producto -->
                                            <div class="options-card">
                                                <h6 class="mb-3">
                                                    <i class='bx bx-cog me-2'></i>
                                                    Opciones del Producto
                                                </h6>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tipo de Impuesto</label>
                                                        <select name="tipo_impuesto" id="np-tipo-impuesto"
                                                            class="form-select">
                                                            <option value="gravado">Gravado - Operación Onerosa
                                                            </option>
                                                            <option value="exonerado">Exonerado</option>
                                                            <option value="inafecto">Inafecto</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Condición de Venta</label>
                                                        <select name="condicion_venta" id="np-condicion-venta"
                                                            class="form-select">
                                                            <option value="sin_receta">Sin Receta Médica</option>
                                                            <option value="con_receta">Con Receta Médica</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="np-op-avanzadas" name="opciones_avanzadas" />
                                                            <label class="form-check-label" for="np-op-avanzadas">
                                                                <i class="bx bx-cog me-1"></i> Mostrar Opciones
                                                                Avanzadas
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <!-- Atributos de Stock -->
                                    <div class="card card-info card-outline mt-3">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="bx bx-warehouse me-2"></i>
                                                Atributos de Stock
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="np-attr-serie"
                                                    name="attr_numero_serie">
                                                <label class="form-check-label" for="np-attr-serie">
                                                    <i class="bx bx-barcode me-1"></i>
                                                    Número Serie
                                                </label>
                                                <small class="form-text text-muted d-block">Control de productos
                                                    individualizados</small>
                                            </div>

                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="np-attr-fecha-venc"
                                                    name="attr_fecha_vencimiento">
                                                <label class="form-check-label" for="np-attr-fecha-venc">
                                                    <i class="bx bx-calendar me-1"></i>
                                                    Fecha Vencimiento
                                                </label>
                                                <small class="form-text text-muted d-block">Control de fechas de
                                                    caducidad</small>
                                            </div>

                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="np-attr-lote"
                                                    name="attr_lote_produccion">
                                                <label class="form-check-label" for="np-attr-lote">
                                                    <i class="bx bx-box me-1"></i>
                                                    Lote Producción
                                                </label>
                                                <small class="form-text text-muted d-block">Control por lotes de
                                                    fabricación</small>
                                            </div>

                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox"
                                                    id="np-attr-venta-menudeo" name="attr_venta_menudeo">
                                                <label class="form-check-label" for="np-attr-venta-menudeo">
                                                    <i class="bx bx-shopping-bag me-1"></i>
                                                    Venta Menudeo
                                                </label>
                                                <small class="form-text text-muted d-block">Permite venta por
                                                    unidades</small>
                                            </div>
                                            <hr>
                                            <div class="d-grid gap-2">
                                                <button id="np-next" class="btn btn-success" id="guardar-producto">
                                                    <i class="bx bx-save me-2"></i>
                                                    Siguente
                                                </button>
                                                <a href="{{ route('compras.create') }}" class="btn btn-outline-secondary">
                                                    <i class="bx bx-arrow-back me-2"></i>
                                                    Volver al Listado
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2 -->
                <div class="tab-pane fade" id="caracteristicas">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <!-- Main Form -->
                                <div class="col-lg-12">
                                    <!-- Datos del Producto -->
                                    <div class="card card-primary card-outline">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class='bx  bx-box'></i>
                                                Propiedades Físicas
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="form-label small"><i class="bx bx-palette me-1"></i>
                                                        Color</label>
                                                    <input type="text" class="form-control"
                                                        name="caracteristicas[color]" placeholder="Ej: Blanco, Azul">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label small"><i class="bx bx-text me-1"></i>
                                                        Textura</label>
                                                    <input type="text" class="form-control"
                                                        name="caracteristicas[textura]" placeholder="Ej: Polvo, Líquido">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label small"><i class="bx bx-water me-1"></i>
                                                        pH</label>
                                                    <input type="number" step="0.1" class="form-control"
                                                        name="caracteristicas[ph]" placeholder="7.0">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="form-label small"><i class="bx bx-edit me-1"></i>
                                                        Densidad</label>
                                                    <input type="text" class="form-control"
                                                        name="caracteristicas[densidad]" placeholder="g/cm³">
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label">
                                                        <i class="bx bx-edit me-1"></i>
                                                        Solubilidad <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea name="caracteristicas[solubilidad]" id="caracteristicas[solubilidad]" class="form-control" rows="1"
                                                        required placeholder="Describa la solubilidad"></textarea>
                                                </div>
                                            </div>
                                            <hr>
                                            <h3 class="card-title">
                                                <i class='bx  bx-box'></i>
                                                Condiciones de Almacenamiento
                                            </h3>
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="form-label small">Temp. Mín. (°C)</label>
                                                    <input type="number" class="form-control"
                                                        name="almacenamiento[temp_min]" placeholder="15">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label small">Temp. Máx. (°C)</label>
                                                    <input type="number" class="form-control"
                                                        name="almacenamiento[temp_max]" placeholder="25">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label small">Humedad (%)</label>
                                                    <input type="number" class="form-control"
                                                        name="almacenamiento[humedad]" placeholder="60">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="form-label small">Proteger de</label>
                                                    <select class="form-select" name="almacenamiento[proteger_de]">
                                                        <option value="">Seleccionar</option>
                                                        <option value="luz">Luz</option>
                                                        <option value="humedad">Humedad</option>
                                                        <option value="calor">Calor</option>
                                                        <option value="aire">Aire</option>
                                                    </select>
                                                </div>
                                                <div class="col-8">
                                                    <label class="form-label small">Instrucciones
                                                        Especiales</label>
                                                    <textarea class="form-control" name="almacenamiento[instrucciones]" rows="1"
                                                        placeholder="Instrucciones adicionales"></textarea>
                                                </div>
                                            </div>
                                            <hr>
                                            <h3 class="card-title">
                                                <i class="fas fa-shield-alt me-2"></i>
                                                Seguridad y Precauciones
                                            </h3>
                                            <div class="row mb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label small">Nivel de Riesgo</label>
                                                    <select class="form-select" name="seguridad[nivel_riesgo]">
                                                        <option value="">Seleccionar</option>
                                                        <option value="bajo">Bajo</option>
                                                        <option value="medio">Medio</option>
                                                        <option value="alto">Alto</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">Equipo de
                                                        Protección</label>
                                                    <input type="text" class="form-control" name="seguridad[epp]"
                                                        placeholder="Guantes, mascarilla">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">Clasificación</label>
                                                    <input type="text" class="form-control"
                                                        name="seguridad[clasificacion]" placeholder="Irritante, Tóxico">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">Primeros Auxilios</label>
                                                    <textarea class="form-control" name="seguridad[primeros_auxilios]" rows="2"
                                                        placeholder="Procedimientos básicos"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3 -->
                <div class="tab-pane fade" id="ficha-tecnica">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <!-- Main Form -->
                                <div class="col-lg-12">
                                    <!-- Datos del Producto -->
                                    <div class="card card-primary card-outline">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class='bx  bx-box'></i>
                                                Información Técnica Detallada
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small">Composición</label>
                                                    <textarea class="form-control form-control-sm ficha-editor" name="ficha_tecnica[composicion]" rows="4"
                                                        placeholder="Describa la composición química del producto"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Mecanismo de Acción</label>
                                                    <textarea class="form-control form-control-sm ficha-editor" name="ficha_tecnica[mecanismo_accion]" rows="4"
                                                        placeholder="Explique cómo funciona el producto"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Indicaciones de Uso</label>
                                                    <textarea class="form-control form-control-sm ficha-editor" name="ficha_tecnica[indicaciones]" rows="4"
                                                        placeholder="¿Cuándo y cómo usar el producto?"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Contraindicaciones</label>
                                                    <textarea class="form-control form-control-sm ficha-editor" name="ficha_tecnica[contraindicaciones]" rows="4"
                                                        placeholder="¿Cuándo NO usar el producto?"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Dosificación y Administración</label>
                                                    <textarea class="form-control form-control-sm" name="ficha_tecnica[dosificacion]" rows="3"
                                                        placeholder="Instrucciones detalladas de dosificación"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Efectos Secundarios</label>
                                                    <textarea class="form-control form-control-sm" name="ficha_tecnica[efectos_secundarios]" rows="3"
                                                        placeholder="Posibles efectos secundarios"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Interacciones</label>
                                                    <textarea class="form-control form-control-sm" name="ficha_tecnica[interacciones]" rows="2"
                                                        placeholder="Interacciones con otros productos"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Sobredosis</label>
                                                    <textarea class="form-control form-control-sm" name="ficha_tecnica[sobredosis]" rows="2"
                                                        placeholder="Qué hacer en caso de sobredosis"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4 -->
                <div class="tab-pane fade" id="imagenes">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <!-- Main Form -->
                                <div class="col-lg-12">
                                    <!-- Datos del Producto -->
                                    <div class="card card-primary card-outline">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class='bx  bx-images'></i>
                                                Galería de Imágenes
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="card-body">
                                                <!-- Área de subida principal -->
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="image-upload-area mb-3" id="main-upload-area">
                                                            <input type="file" id="imagen-principal"
                                                                name="imagen_principal" accept="image/*"
                                                                style="display: none;">
                                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                            <h6 class="text-muted mb-1">Imagen Principal</h6>
                                                            <p class="text-muted mb-0 small">Clic o arrastre aquí</p>
                                                        </div>

                                                        <!-- Preview de imagen principal -->
                                                        <div id="main-image-preview" class="text-center mb-3"
                                                            style="display: none;">
                                                            <img id="main-preview-img" src="" alt="Vista previa"
                                                                class="image-preview">
                                                            <div class="mt-2">
                                                                <button type="button" class="btn btn-sm btn-danger"
                                                                    id="remove-main-image">
                                                                    <i class="fas fa-trash me-1"></i>
                                                                    Eliminar
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Galería adicional -->
                                                        <hr class="my-2">
                                                        <h6 class="mb-2">Imágenes Adicionales</h6>
                                                        <div class="row g-2" id="gallery-container">
                                                            <div class="col-md-4">
                                                                <div class="image-upload-area" data-gallery-item>
                                                                    <input type="file" name="imagenes_adicionales[]"
                                                                        accept="image/*" style="display: none;">
                                                                    <i class="fas fa-plus fa-lg text-muted mb-1"></i>
                                                                    <p class="text-muted mb-0 small">Agregar</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="image-upload-area" data-gallery-item>
                                                                    <input type="file" name="imagenes_adicionales[]"
                                                                        accept="image/*" style="display: none;">
                                                                    <i class="fas fa-plus fa-lg text-muted mb-1"></i>
                                                                    <p class="text-muted mb-0 small">Agregar</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="image-upload-area" data-gallery-item>
                                                                    <input type="file" name="imagenes_adicionales[]"
                                                                        accept="image/*" style="display: none;">
                                                                    <i class="fas fa-plus fa-lg text-muted mb-1"></i>
                                                                    <p class="text-muted mb-0 small">Agregar</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="card card-info card-outline compact-card">
                                                            <div class="card-header">
                                                                <h3 class="card-title">
                                                                    <i class="fas fa-info-circle me-2"></i>
                                                                    Información de Imágenes
                                                                </h3>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="alert alert-info py-2 px-3 mb-3">
                                                                    <h6 class="mb-2"><i
                                                                            class="fas fa-lightbulb me-1"></i>
                                                                        Consejos:</h6>
                                                                    <ul class="mb-0 small">
                                                                        <li>Use imágenes de alta calidad</li>
                                                                        <li>Recomendamos fondo blanco</li>
                                                                        <li>Incluya diferentes ángulos</li>
                                                                        <li>Formato: JPG, PNG (max 5MB)</li>
                                                                    </ul>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label small">Texto
                                                                        alternativo</label>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        name="imagen_alt"
                                                                        placeholder="Descripción de la imagen">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label small">Título de la
                                                                        imagen</label>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        name="imagen_titulo"
                                                                        placeholder="Título descriptivo">
                                                                </div>

                                                                <div class="mb-0">
                                                                    <label class="form-label small">Licencia/Fuente</label>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        name="imagen_fuente" placeholder="Fuente o autor">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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

    <!-- Modal para escoger/crear Familia y Subfamilia -->
    <div class="modal fade" id="familiaSubfamiliaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-sitemap me-2"></i>
                        Seleccionar Familia y Subfamilia
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-folder text-primary me-2"></i>
                                        Familias
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bx bx-search"></i>
                                            </span>
                                            <input type="text" id="fam-filter" class="form-control"
                                                placeholder="Buscar familia...">
                                        </div>
                                        <button class="btn btn-primary ms-2" id="fam-create-btn"
                                            title="Crear nueva familia">
                                            <i class='bx  bx-plus'></i>
                                        </button>
                                    </div>
                                    <div class="list-group" id="fam-list" style="max-height:320px; overflow:auto;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-folder-open text-success me-2"></i>
                                        Subfamilias
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bx bx-search"></i>
                                            </span>
                                            <input type="text" id="subfam-filter" class="form-control"
                                                placeholder="Buscar subfamilia...">
                                        </div>
                                        <button class="btn btn-success ms-2" id="subfam-create-btn"
                                            title="Crear nueva subfamilia">
                                            <i class='bx  bx-plus'></i>
                                        </button>
                                    </div>
                                    <div class="list-group" id="subfam-list" style="max-height:320px; overflow:auto;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-info border-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-info me-2"></i>
                                <div>
                                    <strong>Selección actual:</strong>
                                    <span id="familia-subfam-selected" class="fw-bold">— Ninguna selección —</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="fam-subfam-apply">
                        <i class="fas fa-check me-2"></i>
                        Aplicar Selección
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        // Public JS: public/js/product-quick-create.js
        // Requiere jQuery y SweetAlert2 (ya los cargas en la vista que mostraste)

        (function($) {
            'use strict';

            // Función para mostrar alert de éxito pequeño
            function toastSuccess(message) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }

            // Función para mostrar errores de validación
            function showValidationErrors(errors) {
                let html = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                for (const key in errors) {
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        errors[key].forEach(msg => {
                            html += `<li>${msg}</li>`;
                        });
                    }
                }
                html += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: 'Errores',
                    html: html
                });
            }

            $(function() {
                $('#np-marca-add').on('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Nueva marca',
                        input: 'text',
                        inputLabel: 'Nombre de la marca',
                        inputPlaceholder: 'Ej. ACME',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        preConfirm: (value) => {
                            if (!value || !value.trim()) {
                                Swal.showValidationMessage('El nombre es requerido');
                                return false;
                            }
                            return value.trim();
                        }
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        const nombre = result.value;

                        $.ajax({
                            url: '{{ env('APP_URL') }}/marcas',
                            method: 'POST',
                            data: {
                                nombre: nombre
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            success: function(data, textStatus, jqXHR) {
                                // data debe contener { id, nombre } según tu controller
                                const status = jqXHR
                                    .status; // 200 = existente, 201 = creado
                                const id = data.id;
                                const nombreResp = data.nombre ?? data.nombre;

                                // Verificar si ya existe la opción (por id)
                                let $select = $('#np-marca');
                                if ($select.find('option[value="' + id + '"]')
                                    .length === 0) {
                                    // agregar nueva opción
                                    const option = new Option(nombreResp, id, true,
                                        true);
                                    $select.append(option);
                                } else {
                                    // si existe, seleccionarla y actualizar texto por si cambió
                                    $select.find('option[value="' + id + '"]').text(
                                        nombreResp).prop('selected', true);
                                }

                                // Si usas select2, disparar evento para refrescar
                                if ($select.hasClass('select2-hidden-accessible')) {
                                    $select.trigger('change.select2');
                                }

                                if (status === 201) {
                                    toastSuccess('Marca creada');
                                } else {
                                    toastSuccess('Marca disponible');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                if (jqXHR.status === 422) {
                                    // errores de validación Laravel
                                    const json = jqXHR.responseJSON;
                                    if (json && json.errors) {
                                        showValidationErrors(json.errors);
                                        return;
                                    }
                                }

                                // otro error
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: jqXHR.responseJSON && jqXHR
                                        .responseJSON.message ?
                                        jqXHR.responseJSON.message :
                                        'Hubo un error al guardar la marca'
                                });
                            }
                        });
                    });
                });
            });
        })(jQuery);

        (function($) {
            'use strict';

            // Reusar funciones definidas ya para marcas: toastSuccess y showValidationErrors
            function toastSuccess(message) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }

            function showValidationErrors(errors) {
                let html = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                for (const key in errors) {
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        errors[key].forEach(msg => {
                            html += `<li>${msg}</li>`;
                        });
                    }
                }
                html += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: 'Errores',
                    html: html
                });
            }

            $(function() {
                // Handler para crear Unidad de Medida
                $('#np-unidad-add').on('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Nueva Unidad de Medida',
                        html: '<input id="swal-unidad-codigo" class="swal2-input" placeholder="Código (ej. KG, L)">' +
                            '<input id="swal-unidad-nombre" class="swal2-input" placeholder="Nombre (ej. Kilogramo, Litro)">',
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        preConfirm: () => {
                            const codigo = document.getElementById('swal-unidad-codigo')
                                .value;
                            const nombre = document.getElementById('swal-unidad-nombre')
                                .value;
                            if (!codigo || !codigo.trim()) {
                                Swal.showValidationMessage('El código es requerido');
                                return false;
                            }
                            if (!nombre || !nombre.trim()) {
                                Swal.showValidationMessage('El nombre es requerido');
                                return false;
                            }
                            return {
                                codigo: codigo.trim(),
                                nombre: nombre.trim()
                            };
                        }
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        const payload = result.value;

                        $.ajax({
                            url: '{{ env('APP_URL') }}/unidades',
                            method: 'POST',
                            data: payload,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            success: function(data, textStatus, jqXHR) {
                                const status = jqXHR
                                    .status; // 200 = existente, 201 = creado
                                const id = data.id;
                                const nombreResp = data.nombre;

                                let $select = $('#np-unidad');
                                if ($select.find('option[value="' + id + '"]')
                                    .length === 0) {
                                    const option = new Option(nombreResp, id, true,
                                        true);
                                    $select.append(option);
                                } else {
                                    $select.find('option[value="' + id + '"]').text(
                                        nombreResp).prop('selected', true);
                                }

                                if ($select.hasClass('select2-hidden-accessible')) {
                                    $select.trigger('change.select2');
                                }

                                if (status === 201) {
                                    toastSuccess('Unidad creada');
                                } else {
                                    toastSuccess('Unidad disponible');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                if (jqXHR.status === 422) {
                                    const json = jqXHR.responseJSON;
                                    if (json && json.errors) {
                                        showValidationErrors(json.errors);
                                        return;
                                    }
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: jqXHR.responseJSON && jqXHR
                                        .responseJSON.message ?
                                        jqXHR.responseJSON.message :
                                        'Hubo un error al guardar la unidad'
                                });
                            }
                        });
                    });
                });
            });

        })(jQuery);

        // Funcionalidad para Laboratorio
        (function($) {
            'use strict';

            // Reusar funciones definidas para otros modales
            function toastSuccess(message) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }

            function showValidationErrors(errors) {
                let html = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                for (const key in errors) {
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        errors[key].forEach(msg => {
                            html += `<li>${msg}</li>`;
                        });
                    }
                }
                html += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: 'Errores',
                    html: html
                });
            }

            $(function() {
                $('#np-lab-add').on('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Nuevo laboratorio',
                        input: 'text',
                        inputLabel: 'Nombre del laboratorio',
                        inputPlaceholder: 'Ej. LABORATORIO XYZ',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        preConfirm: (value) => {
                            if (!value || !value.trim()) {
                                Swal.showValidationMessage('El nombre es requerido');
                                return false;
                            }
                            return value.trim();
                        }
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        const nombre = result.value;

                        $.ajax({
                            url: '{{ env('APP_URL') }}/laboratorios',
                            method: 'POST',
                            data: {
                                nombre: nombre
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            success: function(data, textStatus, jqXHR) {
                                const status = jqXHR
                                    .status; // 200 = existente, 201 = creado
                                const id = data.id;
                                const nombreResp = data.nombre;

                                // Verificar si ya existe la opción (por id)
                                let $select = $('#np-laboratorio');
                                if ($select.find('option[value="' + id + '"]')
                                    .length === 0) {
                                    // agregar nueva opción
                                    const option = new Option(nombreResp, id, true,
                                        true);
                                    $select.append(option);
                                } else {
                                    // si existe, seleccionarla y actualizar texto por si cambió
                                    $select.find('option[value="' + id + '"]').text(
                                        nombreResp).prop('selected', true);
                                }

                                // Si usas select2, disparar evento para refrescar
                                if ($select.hasClass('select2-hidden-accessible')) {
                                    $select.trigger('change.select2');
                                }

                                if (status === 201) {
                                    toastSuccess('Laboratorio creado');
                                } else {
                                    toastSuccess('Laboratorio disponible');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                if (jqXHR.status === 422) {
                                    // errores de validación Laravel
                                    const json = jqXHR.responseJSON;
                                    if (json && json.errors) {
                                        showValidationErrors(json.errors);
                                        return;
                                    }
                                }

                                // otro error
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: jqXHR.responseJSON && jqXHR
                                        .responseJSON.message ?
                                        jqXHR.responseJSON.message :
                                        'Hubo un error al guardar el laboratorio'
                                });
                            }
                        });
                    });
                });
            });
        })(jQuery);


        // Requiere jQuery, Bootstrap JS y SweetAlert2 (ya los cargas en la vista)
        (function($) {
            'use strict';

            // URLs (si prefieres rutas nombradas, asigna window.routes desde Blade)
            const URLS = {
                // Rutas simples
                familiasIndex: '{{ route('familias.index') }}',
                familiasStore: '{{ route('familias.store') }}',
                subfamiliasIndex: '{{ route('subfamilias.index') }}',
                subfamiliasStore: '{{ route('subfamilias.store') }}',

                // Ruta con parámetro dinámico
                familiaSubfamilias: (id) => {
                    // Generamos la ruta en Blade usando un placeholder 'ID_PLACEHOLDER'
                    let url = '{{ route('familias.subfamilias', ['familia' => 'ID_PLACEHOLDER']) }}';
                    // Reemplazamos el placeholder con el ID real de JS
                    return url.replace('ID_PLACEHOLDER', id);
                }
            };

            // Estado local
            let selectedFamilia = null;
            let selectedSubfamilia = null;

            // Helpers
            function csrfHeader() {
                return {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                };
            }

            function toastSuccess(msg) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: msg,
                    showConfirmButton: false,
                    timer: 1500
                });
            }

            function showValidationErrors(errors) {
                let html = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                for (const key in errors) {
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        errors[key].forEach(msg => html += `<li>${msg}</li>`);
                    }
                }
                html += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: 'Errores',
                    html: html
                });
            }

            // Render listas
            function renderFamilias(items) {
                const $list = $('#fam-list').empty();
                items.forEach(f => {
                    const $el = $(
                        `<button type="button" class="list-group-item list-group-item-action">${f.nombre}</button>`
                    );
                    $el.data('item', f);
                    $el.on('click', function() {
                        selectedFamilia = $(this).data('item');
                        selectedSubfamilia = null;
                        highlightSelected();
                        fetchSubfamilias(selectedFamilia.id);
                        updateSelectedLabel();
                    });
                    $list.append($el);
                });
            }

            function renderSubfamilias(items) {
                const $list = $('#subfam-list').empty();
                // Si no hay familia seleccionada, mostrar placeholder
                if (!selectedFamilia) {
                    $list.html('<div class="text-muted small p-2">Seleccione primero una familia</div>');
                    return;
                }

                // opción "—" vacía
                const empty = $(
                    `<button type="button" class="list-group-item list-group-item-action text-muted">—</button>`);
                empty.data('item', null);
                empty.on('click', function() {
                    selectedSubfamilia = null;
                    highlightSelected();
                    updateSelectedLabel();
                });
                $list.append(empty);

                items.forEach(s => {
                    const $el = $(
                        `<button type="button" class="list-group-item list-group-item-action">${s.nombre}</button>`
                    );
                    $el.data('item', s);
                    $el.on('click', function() {
                        selectedSubfamilia = $(this).data('item');
                        highlightSelected();
                        updateSelectedLabel();
                    });
                    $list.append($el);
                });
            }

            function highlightSelected() {
                $('#fam-list .list-group-item').removeClass('active');
                $('#subfam-list .list-group-item').removeClass('active');

                if (selectedFamilia) {
                    $('#fam-list .list-group-item').filter(function() {
                        const it = $(this).data('item');
                        return it && it.id === selectedFamilia.id;
                    }).addClass('active');
                }
                if (selectedSubfamilia) {
                    $('#subfam-list .list-group-item').filter(function() {
                        const it = $(this).data('item');
                        return it && it.id === selectedSubfamilia.id;
                    }).addClass('active');
                }
            }

            function updateSelectedLabel() {
                const famText = selectedFamilia ? selectedFamilia.nombre : '—';
                const subText = selectedSubfamilia ? selectedSubfamilia.nombre : '—';
                $('#familia-subfam-selected').text(`${famText}  —  ${subText}`);
            }

            // Fetch data
            function fetchFamilias(q = '') {
                $.get(URLS.familiasIndex, {
                    q: q
                }).done(data => {
                    renderFamilias(data);
                });
            }

            function fetchSubfamilias(familiaId, q = '') {
                if (!familiaId) return renderSubfamilias([]);
                $.get(URLS.familiaSubfamilias(familiaId), {
                    q: q
                }).done(data => {
                    renderSubfamilias(data);
                });
            }

            // Crear familia
            function createFamilia(nombre) {
                return $.ajax({
                    url: URLS.familiasStore,
                    method: 'POST',
                    data: {
                        nombre: nombre
                    },
                    headers: csrfHeader()
                });
            }

            // Crear subfamilia
            function createSubfamilia(familia_id, nombre) {
                return $.ajax({
                    url: URLS.subfamiliasStore,
                    method: 'POST',
                    data: {
                        familia_id: familia_id,
                        nombre: nombre
                    },
                    headers: csrfHeader()
                });
            }

            // Aplicar selección al formulario principal
            function applySelectionToForm() {
                if (!selectedFamilia) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Debe seleccionar una familia'
                    });
                    return;
                }

                // Asegurar que el select #np-familia tiene la opción
                const $selectFam = $('#np-familia');
                if ($selectFam.find(`option[value="${selectedFamilia.id}"]`).length === 0) {
                    $selectFam.append(new Option(selectedFamilia.nombre, selectedFamilia.id, true, true));
                } else {
                    $selectFam.val(selectedFamilia.id);
                    $selectFam.find(`option[value="${selectedFamilia.id}"]`).text(selectedFamilia.nombre);
                }

                // Asegurar que hay un input hidden para subfamilia
                if ($('#np-subfamilia').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'np-subfamilia',
                        name: 'subfamilia_id',
                        value: selectedSubfamilia ? selectedSubfamilia.id : ''
                    }).appendTo('form'); // agrega al primer form en la página
                } else {
                    $('#np-subfamilia').val(selectedSubfamilia ? selectedSubfamilia.id : '');
                }

                // Si usas select2, disparar cambio
                if ($selectFam.hasClass('select2-hidden-accessible')) {
                    $selectFam.trigger('change.select2');
                }

                toastSuccess('Familia y subfamilia aplicadas');
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('familiaSubfamiliaModal'));
                modal.hide();
            }

            // Initialize
            $(function() {
                // Prevenir que el select se abra
                $('#np-familia').on('mousedown keydown', function(e) {
                    e.preventDefault();
                    return false;
                });

                // Abrir modal desde el botón existente #np-fam-add o el overlay del select
                $('#np-fam-add, .select-overlay').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Reset estado
                    selectedFamilia = null;
                    selectedSubfamilia = null;
                    $('#fam-filter').val('');
                    $('#subfam-filter').val('');
                    $('#familia-subfam-selected').text('—');
                    // Cargar familias
                    fetchFamilias();
                    renderSubfamilias([]);
                    const modalEl = document.getElementById('familiaSubfamiliaModal');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                });

                // Filtros
                let famTimeout, subfamTimeout;
                $('#fam-filter').on('input', function() {
                    clearTimeout(famTimeout);
                    const q = $(this).val();
                    famTimeout = setTimeout(() => fetchFamilias(q), 250);
                });

                $('#subfam-filter').on('input', function() {
                    clearTimeout(subfamTimeout);
                    const q = $(this).val();
                    if (selectedFamilia) {
                        subfamTimeout = setTimeout(() => fetchSubfamilias(selectedFamilia.id, q), 250);
                    }
                });

                // Crear familia desde modal
                $('#fam-create-btn').on('click', function() {
                    const modalEl = document.getElementById('familiaSubfamiliaModal');
                    // obtener instancia de bootstrap Modal (si no existe, crear una temporal)
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(
                        modalEl);
                    const wasShown = modalEl.classList.contains('show');

                    // ocultar modal para que Swal pueda recibir foco
                    if (wasShown) {
                        modalInstance.hide();
                    }

                    Swal.fire({
                        title: 'Crear nueva familia',
                        input: 'text',
                        inputLabel: 'Nombre de la familia',
                        inputPlaceholder: 'Ej. NUTRIENTES',
                        showCancelButton: true,
                        confirmButtonText: 'Crear',
                        // enfocar input cuando Swal abre (ayuda en algunos casos)
                        didOpen: () => {
                            const input = Swal.getInput();
                            if (input) input.focus();
                        },
                        preConfirm: async (val) => {
                            if (!val || !val.trim()) {
                                Swal.showValidationMessage('El nombre es obligatorio');
                                return false;
                            }

                            try {
                                const response = await $.ajax({
                                    url: URLS
                                        .familiasStore, // asegúrate que URLS.familiasStore === '/familias'
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                            .attr('content')
                                    },
                                    data: {
                                        nombre: val.trim()
                                    }
                                });
                                // devolver respuesta para then()
                                return response;
                            } catch (xhr) {
                                // Si Laravel devuelve 422 con errores, mostrarlos en Swal
                                if (xhr && xhr.status === 422 && xhr.responseJSON && xhr
                                    .responseJSON.errors) {
                                    // concatenar mensajes o mostrar el primero
                                    const errs = xhr.responseJSON.errors;
                                    let msgs = [];
                                    Object.keys(errs).forEach(k => msgs = msgs.concat(errs[
                                        k]));
                                    Swal.showValidationMessage(msgs.join('<br>'));
                                    return false; // evita cerrar el modal si hay validación
                                }
                                // otro error
                                Swal.showValidationMessage('Error al crear la familia');
                                return false;
                            }
                        },
                        // al cerrar Swal, si el modal estaba abierto, reabrirlo
                        willClose: () => {
                            if (wasShown) {
                                // esperar un tick para evitar conflictos de z-index
                                setTimeout(() => modalInstance.show(), 50);
                            }
                        }
                    }).then(result => {
                        if (result.isConfirmed && result.value) {
                            toastSuccess('Familia creada');
                            // recargar lista de familias (asegúrate que cargarFamilias o fetchFamilias existe)
                            if (typeof cargarFamilias === 'function') {
                                cargarFamilias();
                            } else if (typeof fetchFamilias === 'function') {
                                fetchFamilias();
                            } else {
                                // alternativa: emitir evento o recargar manualmente
                                console.warn(
                                    'Función cargarFamilias o fetchFamilias no encontrada.');
                            }
                        }
                    });
                });
                // Crear subfamilia desde modal (requiere familia seleccionada)
                $('#subfam-create-btn').on('click', function() {
                    if (!selectedFamilia) {
                        Swal.fire('Atención', 'Seleccione primero una familia', 'warning');
                        return;
                    }

                    const modalEl = document.getElementById('familiaSubfamiliaModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(
                        modalEl);
                    const wasShown = modalEl.classList.contains('show');

                    // ocultar modal para que Swal pueda recibir foco
                    if (wasShown) modalInstance.hide();

                    Swal.fire({
                        title: `Crear subfamilia para "${selectedFamilia.nombre}"`,
                        input: 'text',
                        inputLabel: 'Nombre de la subfamilia',
                        inputPlaceholder: 'Ej. MAIZ',
                        showCancelButton: true,
                        confirmButtonText: 'Crear',
                        didOpen: () => {
                            const input = Swal.getInput();
                            if (input) input.focus();
                        },
                        preConfirm: async (value) => {
                            if (!value || !value.trim()) {
                                Swal.showValidationMessage('El nombre es requerido');
                                return false;
                            }

                            try {
                                // Si tienes la función createSubfamilia que hace $.ajax, úsala:
                                // const resp = await createSubfamilia(selectedFamilia.id, value.trim());

                                // O hacer la llamada AJAX directa aquí:
                                const resp = await $.ajax({
                                    url: URLS
                                        .subfamiliasStore, // asegúrate que URLS.subfamiliasStore === '/subfamilias'
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                            .attr('content')
                                    },
                                    data: {
                                        familia_id: selectedFamilia.id,
                                        nombre: value.trim()
                                    }
                                });

                                return resp; // lo recibirá then()
                            } catch (xhr) {
                                // Mostrar errores de validación (Laravel 422)
                                if (xhr && xhr.status === 422 && xhr.responseJSON && xhr
                                    .responseJSON.errors) {
                                    const errs = xhr.responseJSON.errors;
                                    let msgs = [];
                                    Object.keys(errs).forEach(k => msgs = msgs.concat(errs[
                                        k]));
                                    Swal.showValidationMessage(msgs.join('<br>'));
                                    return false;
                                }

                                Swal.showValidationMessage('Error al crear la subfamilia');
                                return false;
                            }
                        },
                        willClose: () => {
                            // reabrir modal si estaba abierto antes
                            if (wasShown) {
                                setTimeout(() => modalInstance.show(), 50);
                            }
                        }
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        const data = result.value;
                        if (!data) return;

                        // data = respuesta del servidor con la subfamilia creada o existente
                        selectedSubfamilia = data;
                        // refrescar lista de subfamilias para la familia actual
                        if (typeof fetchSubfamilias === 'function') {
                            fetchSubfamilias(selectedFamilia.id);
                        } else {
                            // si tienes otra función para recargar, úsala
                            console.warn(
                                'fetchSubfamilias no definida: actualiza manualmente la lista.'
                            );
                        }
                        updateSelectedLabel();
                        highlightSelected();
                        toastSuccess('Subfamilia creada');
                    });
                });
                // Aplicar selección al formulario
                $('#fam-subfam-apply').on('click', function() {
                    applySelectionToForm();
                });
            });

        })(jQuery);



        ///step1

        // Funcionalidad del botón "Siguiente" para ir al Step 2
        (function($) {
            'use strict';

            $('#np-next').on('click', function(e) {
                e.preventDefault();

                // Validación cliente mínima
                const nombre = $('#np-nombre').val();
                if (!nombre || !nombre.trim()) {
                    Swal.fire('Atención', 'El nombre del producto es obligatorio', 'warning');
                    return;
                }

                // Mostrar loading en el botón
                const $btn = $(this);
                const originalText = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Procesando...').prop('disabled', true);

                // Usar el formulario existente para enviar todos los datos
                const $form = $('#producto-step1-form');
                const formData = new FormData($form[0]);

                $.ajax({
                    url: '{{ route('productos.step2') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Enviar el formulario normalmente para ir al step2
                        $form[0].submit();
                    },
                    error: function(jqXHR) {
                        // Restaurar botón
                        $btn.html(originalText).prop('disabled', false);

                        if (jqXHR.status === 422) {
                            // Errores de validación
                            const errors = jqXHR.responseJSON.errors;
                            let errorMessages = [];

                            for (const field in errors) {
                                errorMessages = errorMessages.concat(errors[field]);
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Errores de Validación',
                                html: '<ul class="text-left">' + errorMessages.map(msg =>
                                    `<li>${msg}</li>`).join('') + '</ul>'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Hubo un problema al procesar la información. Inténtalo de nuevo.'
                            });
                        }
                    }
                });
            });

            // ===== FUNCIONALIDAD DE PESTAÑAS =====
            const tabs = ['datos-basicos', 'caracteristicas', 'ficha-tecnica', 'imagenes'];
            let currentTab = 0;

            function updateTabNavigation() {
                const prevBtn = $('#prev-tab-btn');
                const nextBtn = $('#next-tab-btn');
                const submitBtn = $('#submit-btn');

                // Mostrar/ocultar botón anterior
                if (currentTab === 0) {
                    prevBtn.hide();
                } else {
                    prevBtn.show();
                }

                // Mostrar/ocultar botón siguiente o submit
                if (currentTab === tabs.length - 1) {
                    nextBtn.hide();
                    submitBtn.show();
                } else {
                    nextBtn.show();
                    submitBtn.hide();
                }
            }

            function scrollToTop() {
                // Scroll suave hacia las pestañas
                $('html, body').animate({
                    scrollTop: $('#productTabs').offset().top - 20
                }, 300);
            }

            function toastError(message) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: message,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }

            $('#next-tab-btn').on('click', function() {
                if (currentTab < tabs.length - 1) {
                    currentTab++;
                    const targetTab = tabs[currentTab];
                    $(`#${targetTab}-tab`).tab('show');
                    updateTabNavigation();
                    scrollToTop();
                }
            });

            $('#prev-tab-btn').on('click', function() {
                if (currentTab > 0) {
                    currentTab--;
                    const targetTab = tabs[currentTab];
                    $(`#${targetTab}-tab`).tab('show');
                    updateTabNavigation();
                    scrollToTop();
                }
            });

            // Detectar cambio manual de pestañas
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const targetId = e.target.getAttribute('data-bs-target').substring(1);
                currentTab = tabs.indexOf(targetId);
                updateTabNavigation();
                scrollToTop();
            });

            // Manejar envío del formulario
            $('#submit-btn').on('click', function(e) {
                e.preventDefault();

                // Validar campos requeridos
                const nombre = $('#np-nombre').val().trim();
                if (!nombre) {
                    toastError('El nombre del producto es requerido');
                    // Ir a la primera pestaña
                    currentTab = 0;
                    $('#datos-basicos-tab').tab('show');
                    updateTabNavigation();
                    scrollToTop();
                    $('#np-nombre').focus();
                    return;
                }

                // Mostrar loading
                const originalText = $(this).html();
                $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);

                // Enviar el formulario
                $('#producto-form').submit();
            });

            // Inicializar navegación
            updateTabNavigation();

            // ===== FUNCIONALIDAD DE IMÁGENES =====

            // Imagen principal
            $('#main-upload-area').on('click', function() {
                $('#imagen-principal').click();
            });

            $('#imagen-principal').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#main-preview-img').attr('src', e.target.result);
                        $('#main-image-preview').show();
                        $('#main-upload-area').hide();
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#remove-main-image').on('click', function() {
                $('#imagen-principal').val('');
                $('#main-image-preview').hide();
                $('#main-upload-area').show();
            });

            // Imágenes adicionales
            $(document).on('click', '[data-gallery-item]', function() {
                const $this = $(this);
                const $input = $this.find('input[type="file"]');
                $input.click();
            });

            $(document).on('change', '[data-gallery-item] input[type="file"]', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const $container = $(this).closest('[data-gallery-item]');
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $container.html(`
                            <div class="position-relative">
                                <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 120px; width: 100%; object-fit: cover;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-gallery-image">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `);

                        // Agregar nuevo slot si es necesario
                        if ($('#gallery-container [data-gallery-item]').length === $(
                                '#gallery-container [data-gallery-item] img').length) {
                            $('#gallery-container').append(`
                                <div class="col-md-4">
                                    <div class="image-upload-area" data-gallery-item style="border: 2px dashed #dee2e6; border-radius: 8px; padding: 1rem; text-align: center; cursor: pointer; transition: all 0.3s ease; height: 120px; display: flex; flex-direction: column; justify-content: center;">
                                        <input type="file" name="imagenes_adicionales[]" accept="image/*" style="display: none;">
                                        <i class="fas fa-plus fa-lg text-muted mb-1"></i>
                                        <p class="text-muted mb-0 small">Agregar</p>
                                    </div>
                                </div>
                            `);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Remover imagen de galería
            $(document).on('click', '.remove-gallery-image', function(e) {
                e.stopPropagation();
                const $container = $(this).closest('.col-md-4');
                $container.html(`
                    <div class="image-upload-area" data-gallery-item style="border: 2px dashed #dee2e6; border-radius: 8px; padding: 1rem; text-align: center; cursor: pointer; transition: all 0.3s ease; height: 120px; display: flex; flex-direction: column; justify-content: center;">
                        <input type="file" name="imagenes_adicionales[]" accept="image/*" style="display: none;">
                        <i class="fas fa-plus fa-lg text-muted mb-1"></i>
                        <p class="text-muted mb-0 small">Agregar</p>
                    </div>
                `);
            });

            // Drag & Drop para imagen principal
            $('#main-upload-area').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('border-primary');
            });

            $('#main-upload-area').on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('border-primary');
            });

            $('#main-upload-area').on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('border-primary');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    if (file.type.startsWith('image/')) {
                        const $input = $('#imagen-principal')[0];
                        $input.files = files;
                        $input.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                }
            });

        })(jQuery);
    </script>
@endsection

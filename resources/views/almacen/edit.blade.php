@extends('layout.app')

@section('title', 'Editar Inventario')
@section('page-title', 'Editar Registro de Inventario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('almacen.index') }}">Almacén</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0 text-gray-800">
                <span class="badge bg-primary me-2">{{ $producto->nombre }}</span>
                Editar Registro
            </h1>
        </div>
        <button type="button" class="btn btn-light btn-sm shadow-sm" onclick="history.back()">
            <i class="fas fa-arrow-left me-1"></i> Regresar
        </button>
    </div>

    <form method="POST" action="{{ route('almacen.update', $detalle->id) }}" id="edit-inventory-form">
        @csrf
        
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="card card-primary card-outline shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cube me-2"></i>Datos Básicos del Producto
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-muted">Nombre del Producto</label>
                                <textarea name="nombre" class="form-control" rows="2" required>{{ $producto->nombre }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Laboratorio</label>
                                <select name="laboratorio_id" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($laboratorios as $lab)
                                        <option value="{{ $lab->id }}" {{ $producto->laboratorio == $lab->id ? 'selected' : '' }}>{{ $lab->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Marca</label>
                                <select name="marca_id" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($marcas as $mar)
                                        <option value="{{ $mar->id }}" {{ $producto->marca_id == $mar->id ? 'selected' : '' }}>{{ $mar->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Unidad de Medida</label>
                                <select name="unidad_medida_id" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($unidades as $un)
                                        <option value="{{ $un->id }}" {{ $producto->unidad_medida_id == $un->id ? 'selected' : '' }}>{{ $un->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-info card-outline h-100 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-clipboard-list me-2"></i>Especificaciones del Registro
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Lote</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-box"></i></span>
                                    <input type="text" name="lote" class="form-control border-start-0" value="{{ $detalle->lote }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Fecha Vencimiento</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" name="fecha_vencimiento" class="form-control border-start-0" value="{{ $detalle->fecha_vencimiento ? $detalle->fecha_vencimiento->format('Y-m-d') : '' }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Cantidad Actual</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-hashtag"></i></span>
                                    <input type="number" step="0.01" name="cantidad" class="form-control border-start-0 bg-light-info" value="{{ $detalle->cantidad }}" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted text-success">Costo de Compra (S/)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-success text-success border-end-0"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" step="0.01" name="costo" class="form-control border-start-0" value="{{ $detalle->costo }}" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Peso (KG)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-weight"></i></span>
                                    <input type="number" step="0.01" name="peso" class="form-control border-start-0" value="{{ $detalle->peso ?? 0 }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Stock Mínimo</label>
                                <input type="number" name="stock_min" class="form-control" value="{{ $detalle->stock_min ?? 0 }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Stock Máximo</label>
                                <input type="number" name="stock_max" class="form-control" value="{{ $detalle->stock_max ?? 0 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-success card-outline h-100 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-tag me-2"></i>Estrategia de Precios (S/)
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">PVP (Público)</label>
                                <input type="number" step="0.01" name="pvp" class="form-control form-control-lg fw-bold text-primary" value="{{ $detalle->pvp }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">PVP con Descuento</label>
                                <input type="number" step="0.01" name="pvpd" class="form-control" value="{{ $detalle->pvpd }}">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">PVC (Corporativo)</label>
                                <input type="number" step="0.01" name="pvc" class="form-control" value="{{ $detalle->pvc }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">PVC Descuento</label>
                                <input type="number" step="0.01" name="pvcd" class="form-control" value="{{ $detalle->pvcd }}">
                            </div>

                            <div class="col-12 mt-4">
                                <div class="alert alert-info border-0 small mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Los cambios afectarán únicamente a este lote/registro de ingreso.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-end">
                <hr>
                <a href="{{ route('almacen.index') }}" class="btn btn-light px-4 border me-2">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .bg-light-info { background-color: #f0f9ff !important; }
    .bg-light-success { background-color: #f0fff4 !important; }
    .card-outline { border-top-width: 3px !important; }
</style>
@endpush

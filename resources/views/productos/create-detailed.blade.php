@extends('layout.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .teal-bar {
        background: #0d8b86;
        color: #fff;
        padding: 0.6rem 1rem;
        font-weight: 600;
    }
    .product-table thead th {
        background: #f3f3f3;
        border-top: 1px solid #ddd;
    }
    .product-line-selected {
        background: #e6f7ff;
    }
    .big-action {
        height: 56px;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-purple {
        background:#6c2f5b;
        color:#fff;
    }
    .input-compact { padding: .375rem .5rem; }
    .form-label.small-muted { font-size: .9rem; color: #666; font-weight:600; }
    .header-note { color:#c03; font-weight:600; font-size:.95rem; margin-bottom:.5rem; }
</style>

<div class="container py-4">
    <h1 class="h5 mb-4">Registrar Producto — Detalle</h1>

    <form method="POST" action="{{ route('productos.store') }}" id="product-detailed-form">
        @csrf

        <!-- Hidden fields desde quick form -->
        <input type="hidden" name="laboratorio" value="{{ old('laboratorio', $quick['laboratorio'] ?? '') }}">
        <input type="hidden" name="familia_id" value="{{ old('familia_id', $quick['familia_id'] ?? '') }}">
        <input type="hidden" name="subfamilia_id" value="{{ old('subfamilia_id', $quick['subfamilia_id'] ?? '') }}">
        <input type="hidden" name="marca_id" value="{{ old('marca_id', $quick['marca_id'] ?? '') }}">
        <input type="hidden" name="unidad_medida_id" value="{{ old('unidad_medida_id', $quick['unidad_medida_id'] ?? '') }}">
        <input type="hidden" name="tipo_impuesto" value="{{ old('tipo_impuesto', $quick['tipo_impuesto'] ?? '') }}">
        <input type="hidden" name="opciones_avanzadas" value="{{ old('opciones_avanzadas', $quick['opciones_avanzadas'] ?? 0) }}">
        <input type="hidden" name="condicion_venta" value="{{ old('condicion_venta', $quick['condicion_venta'] ?? '') }}">
        <input type="hidden" name="attr_numero_serie" value="{{ old('attr_numero_serie', $quick['attr_numero_serie'] ?? 0) }}">
        <input type="hidden" name="attr_fecha_vencimiento" value="{{ old('attr_fecha_vencimiento', $quick['attr_fecha_vencimiento'] ?? 0) }}">
        <input type="hidden" name="attr_lote_produccion" value="{{ old('attr_lote_produccion', $quick['attr_lote_produccion'] ?? 0) }}">
        <input type="hidden" name="attr_venta_menudeo" value="{{ old('attr_venta_menudeo', $quick['attr_venta_menudeo'] ?? 0) }}">
        <input type="hidden" name="nombre" value="{{ old('nombre', $quick['nombre'] ?? '') }}">

        <!-- Hidden que contendrá las líneas de producto en JSON -->
        <input type="hidden" name="product_lines" id="product_lines" value="[]">

        <!-- Formulario grid (simula la disposición de la foto) -->
        <div class="row gx-3 gy-3 align-items-end">
            <div class="col-lg-6">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small-muted">Código Barras</label>
                        <input type="text" id="cb" class="form-control input-compact" value="">
                    </div>
                    <div class="col-6">
                        <label class="form-label small-muted">Cantidad</label>
                        <input type="number" id="cantidad" class="form-control input-compact" value="1" min="0">
                    </div>

                    <div class="col-6">
                        <label class="form-label small-muted">Registro Sanitario</label>
                        <input type="text" id="registro" class="form-control input-compact" value="">
                    </div>
                    <div class="col-6">
                        <label class="form-label small-muted">Lote de Prod.</label>
                        <input type="text" id="lote" class="form-control input-compact" value="">
                    </div>

                    <div class="col-6">
                        <label class="form-label small-muted">Principio activo uno</label>
                        <input type="text" id="pa1" class="form-control input-compact" value="">
                    </div>
                    <div class="col-6">
                        <label class="form-label small-muted">Fecha Venc.</label>
                        <input type="date" id="fecha_venc" class="form-control input-compact" value="">
                    </div>

                    <div class="col-6">
                        <label class="form-label small-muted">Principio activo dos</label>
                        <input type="text" id="pa2" class="form-control input-compact" value="">
                    </div>
                    <div class="col-6">
                        <label class="form-label small-muted">Precio Compra S/</label>
                        <input type="text" id="precio_compra" class="form-control input-compact" value="">
                    </div>

                    <div class="col-6">
                        <label class="form-label small-muted">Presentación ó Modelo</label>
                        <select id="presentacion" class="form-select input-compact">
                            <option value="">-- seleccionar --</option>
                            <option value="SACO">SACO</option>
                            <option value="BOLSA">BOLSA</option>
                            <option value="CAJA">CAJA</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small-muted">Costo Operativo S/</label>
                        <input type="text" id="costo_operativo" class="form-control input-compact" value="0.00">
                    </div>

                    <div class="col-6">
                        <label class="form-label small-muted">Concentración ó Detalle</label>
                        <select id="concentracion" class="form-select input-compact">
                            <option value="">-- seleccionar --</option>
                            <option value="40 KG">40 KG</option>
                            <option value="20 KG">20 KG</option>
                            <option value="10 KG">10 KG</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small-muted">Peso (KGM)</label>
                        <input type="text" id="peso" class="form-control input-compact" value="0">
                    </div>
                </div>
            </div>

            <!-- Column derecha: precios -->
            <div class="col-lg-6">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small-muted">PVP S/</label>
                        <input type="text" id="pvp" class="form-control input-compact" value="">
                    </div>
                    <div class="col-6">
                        <label class="form-label small-muted">PV/Docena S/</label>
                        <input type="text" id="pv_docena" class="form-control input-compact" value="">
                    </div>

                    <div class="col-6">
                        <label class="form-label small-muted">PVP/Dcto. S/</label>
                        <input type="text" id="pvp_dto" class="form-control input-compact" value="">
                    </div>
                    <div class="col-6">
                        <label class="form-label small-muted">PVC S/</label>
                        <input type="text" id="pvc" class="form-control input-compact" value="">
                    </div>

                    <div class="col-6">
                        <label class="form-label small-muted">PVC/Dcto. S/</label>
                        <input type="text" id="pvc_dto" class="form-control input-compact" value="">
                    </div>
                    <div class="col-6">
                        <label class="form-label small-muted">PVP (otro)</label>
                        <input type="text" id="pvp2" class="form-control input-compact" value="">
                    </div>

                    <!-- espacio para mantener estructura -->
                    <div class="col-12 mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Botón agregar producto (barra teal) -->
        <div class="mt-3 mb-2">
            <div class="teal-bar d-flex align-items-center justify-content-between">
                <div><i class="bi bi-box-seam"></i> Agregar Producto</div>
                <div>
                    <button type="button" class="btn btn-light btn-sm" id="btn-add-line">Agregar</button>
                </div>
            </div>
        </div>

        <!-- Tabla de líneas -->
        <div class="table-responsive">
            <table class="table table-bordered product-table">
                <thead>
                    <tr>
                        <th style="width:80px">CB</th>
                        <th style="width:120px">Código Ref</th>
                        <th>Presentación ó Modelo</th>
                        <th>Concentración ó Detalle</th>
                        <th style="width:80px">Cantidad</th>
                        <th style="width:100px">PC</th>
                        <th style="width:100px">PVP</th>
                        <th style="width:100px">PVPD</th>
                        <th style="width:80px">Peso</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody id="product-lines-body">
                    {{-- filas añadidas dinámicamente --}}
                </tbody>
            </table>
        </div>

        <!-- Acciones inferiores -->
        <div class="row mt-4">
            <div class="col-md-6">
                <button type="button" id="btn-comprar" class="btn btn-purple big-action w-100">Comprar</button>
            </div>
            <div class="col-md-6">
                <a href="{{ route('compras.create') }}" class="btn btn-teal big-action w-100" style="background:#0d8b86;color:#fff">Volver Presupuestos</a>
            </div>
        </div>

    </form>
</div>

<!-- Dependencias: jQuery y SweetAlert (si las usas) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    (function($){
        'use strict';

        // Mantener un array local de líneas
        let lines = [];

        function renderLines() {
            const $body = $('#product-lines-body').empty();
            lines.forEach((ln, idx) => {
                const $tr = $('<tr>');
                $tr.append(`<td><small>${ln.cb}</small></td>`);
                $tr.append(`<td><small>${ln.codigo_ref || ''}</small></td>`);
                $tr.append(`<td>${ln.presentacion || ''}</td>`);
                $tr.append(`<td>${ln.concentracion || ''}</td>`);
                $tr.append(`<td class="text-end">${ln.cantidad}</td>`);
                $tr.append(`<td class="text-end">${ln.precio_compra || ''}</td>`);
                $tr.append(`<td class="text-end">${ln.pvp || ''}</td>`);
                $tr.append(`<td class="text-end">${ln.pvp_dto || ''}</td>`);
                $tr.append(`<td class="text-end">${ln.peso || ''}</td>`);
                $tr.append(`<td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-line" data-idx="${idx}">x</button></td>`);
                $body.append($tr);
            });

            // actualizar hidden input con JSON
            $('#product_lines').val(JSON.stringify(lines));
        }

        // Añadir línea cuando se presiona el botón
        $('#btn-add-line').on('click', function(){
            const ln = {
                cb: $('#cb').val().trim(),
                codigo_ref: '', // si lo tienes, añade input
                presentacion: $('#presentacion').val() || $('#presentacion option:selected').text(),
                concentracion: $('#concentracion').val() || $('#concentracion option:selected').text(),
                cantidad: Number($('#cantidad').val()) || 0,
                precio_compra: $('#precio_compra').val().trim(),
                pvp: $('#pvp').val().trim(),
                pvp_dto: $('#pvp_dto').val().trim(),
                peso: $('#peso').val().trim(),
                pa1: $('#pa1').val().trim(),
                pa2: $('#pa2').val().trim(),
                lote: $('#lote').val().trim(),
                fecha_venc: $('#fecha_venc').val(),
            };

            // validación mínima
            if (!ln.cb && !ln.presentacion && !ln.concentracion && !ln.cantidad) {
                alert('Ingrese al menos Código Barras, Presentación, Concentración o Cantidad.');
                return;
            }

            lines.push(ln);
            renderLines();

            // opcional: limpiar algunos inputs
            // $('#cb').val(''); $('#cantidad').val(1);
        });

        // eliminar línea
        $(document).on('click', '.btn-remove-line', function(){
            const idx = Number($(this).data('idx'));
            if (!isNaN(idx)) {
                lines.splice(idx,1);
                renderLines();
            }
        });

        // Comprar (submit del form). Antes, verificar que haya al menos 1 línea.
        $('#btn-comprar').on('click', function(){
            if (lines.length === 0) {
                alert('Agrega al menos un producto antes de continuar.');
                return;
            }

            // Enviar el formulario
            $('#product-detailed-form').submit();
        });

        // si la vista viene con líneas preexistentes (por ejemplo edit), carga desde hidden
        $(function(){
            try {
                const pre = $('#product_lines').val();
                if (pre) {
                    const parsed = JSON.parse(pre);
                    if (Array.isArray(parsed) && parsed.length) {
                        lines = parsed;
                        renderLines();
                    }
                }
            } catch(e){ console.warn(e); }
        });

    })(jQuery);
</script>
@endsection
@extends('layout.app')

@section('title', 'Detalle del Producto')
@section('page-title', 'Registrar Producto - Detalle')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Inventario</a></li>
    <li class="breadcrumb-item"><a href="#">Productos</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

<link rel="stylesheet" href="{{ asset('css/products-detail.css') }}">

@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 m-0 mb-1">
                        @yield('breadcrumb')
                    </ol>
                </nav>
                <h1 class="h4 mb-0 text-gray-800">
                    <span class="badge bg-primary me-2">{{ $producto_data['nombre'] }}</span>
                    Detalle de Registro
                </h1>
            </div>
            <button type="button" class="btn btn-light btn-sm shadow-sm" onclick="history.back()">
                <i class="fas fa-arrow-left me-1"></i> Regresar
            </button>
        </div>

        <form method="POST" action="{{ route('productos.store') }}" id="product-detailed-form"
            enctype="multipart/form-data">
            @csrf
            @include('productos.partials.hidden_fields')

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card card-primary card-outline h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-clipboard-list me-2"></i>Especificaciones Técnicas
                            </h6>
                        </div>
                        <div class="card-body compact-form">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Código de Barras</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-primary border-end-0"><i
                                                class="bx bx-barcode"></i></span>
                                        <input type="text" id="cb"
                                            class="form-control border-start-0 border-end-0"
                                            placeholder="Escanear o ingresar...">
                                        <button type="button" class="btn btn-outline-success" id="btn-generar-cb"
                                            title="Generar código automáticamente">
                                            <i class="bx bx-magic-wand"></i> Auto
                                        </button>
                                    </div>
                                    <div id="barcode-container" class="mt-2 text-center p-3 border rounded bg-white shadow-sm d-none">
                                        <div class="d-flex justify-content-center gap-2 mb-3">
                                            <div class="input-group input-group-sm" style="width: 90px;">
                                                <span class="input-group-text p-1"><i class="bx bx-arrow-to-bottom"></i></span>
                                                <input type="number" id="bc-width" class="form-control" value="2" min="1" max="4" title="Grosor de barras">
                                            </div>
                                            <div class="input-group input-group-sm" style="width: 90px;">
                                                <span class="input-group-text p-1"><i class="bx bx-arrow-to-bottom"></i></span>
                                                <input type="number" id="bc-height" class="form-control" value="60" min="20" max="150" title="Altura en px">
                                            </div>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-print-bc" title="Imprimir etiqueta">
                                                    <i class="bx bx-printer"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" id="btn-download-bc" title="Descargar PNG">
                                                    <i class="bx bx-download"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="barcode-wrapper bg-white py-2">
                                            <svg id="barcode-svg" style="max-width: 100%; height: auto;"></svg>
                                        </div>
                                        <canvas id="barcode-canvas" class="d-none"></canvas>
                                    </div>
                                    <small class="text-muted">Click en "Auto" para generar código...</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" id="cantidad" class="form-control" value="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reg. Sanitario</label>
                                    <input type="text" id="registro" class="form-control" placeholder="N° Registro">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Lote</label>
                                    <input type="text" id="lote" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-danger">Principio Activo 1</label>
                                    <input type="text" id="pa1" class="form-control" placeholder="Compuesto 1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Vencimiento</label>
                                    <input type="date" id="fecha_venc" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Principio Activo 2</label>
                                    <input type="text" id="pa2" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-success">Precio Compra S/</label>
                                    <input type="number" step="0.01" id="precio_compra"
                                        class="form-control bg-light-success" placeholder="0.00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-info">Costo Operativo S/</label>
                                    <input type="number" step="0.01" id="costo_operativo" class="form-control"
                                        value="0.00">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Presentación</label>
                                    <div class="input-group">
                                        <select id="presentacion" class="form-select">
                                            <option value="">-- Ver --</option>
                                            @foreach ($presentaciones as $presentacion)
                                                <option value="{{ $presentacion->nombre }}">{{ $presentacion->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" id="btn-add-presentacion"><i
                                                class='bx bx-plus'></i></button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Concentración</label>
                                    <div class="input-group">
                                        <select id="concentracion" class="form-select">
                                            <option value="">-- Ver --</option>
                                            @foreach ($concentraciones as $concentracion)
                                                <option value="{{ $concentracion->nombre }}">{{ $concentracion->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary"
                                            id="btn-add-concentracion"><i class='bx bx-plus'></i></button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Peso (KG)</label>
                                    <input type="number" step="0.01" id="peso" class="form-control"
                                        value="0.00">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stock Máximo</label>
                                    <input type="number" id="stock_maximo" class="form-control" value="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stock Mínimo</label>
                                    <input type="number" id="stock_minimo" class="form-control" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-success card-outline shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-tag me-2"></i>Estrategia de Precios
                            </h6>
                        </div>
                        <div class="card-body compact-form bg-light-50">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">PVP (PRECIO VENTA PÚBLICO)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" id="pvp" class="form-control"
                                            placeholder="0.00">
                                    </div>
                                </div>

                                <div class="col-6">
                                    <label class="form-label">PV/Docena</label>
                                    <input type="number" step="0.01" id="pv_docena" class="form-control"
                                        placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">PVP con Dcto</label>
                                    <input type="number" step="0.01" id="pvp_dto" class="form-control"
                                        placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">PVC (Corp.)</label>
                                    <input type="number" step="0.01" id="pvc" class="form-control"
                                        placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">PVC con Dcto</label>
                                    <input type="number" step="0.01" id="pvc_dto" class="form-control"
                                        placeholder="0.00">
                                </div>

                                <div class="col-6">
                                    <label class="form-label">PVP (OTRO /
                                        ALTERNATIVO)</label>
                                    <input type="number" step="0.01" id="pvp2" class="form-control"
                                        placeholder="0.00">
                                </div>
                                <div class="col-12 text-center mt-3">
                                    <button type="button" class="btn btn-primary w-100 btn-action shadow-sm py-2"
                                        id="btn-add-line">
                                        <i class="fas fa-plus-circle me-2"></i> Añadir a la Lista
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-info card-outline">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <h6 class="m-0 font-weight-bold text-info">Productos para Procesar</h6>
                            <span class="badge bg-info" id="count-items">0 Items</span>
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

            <div class="row mt-4 pb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('productos.step1') }}" class="btn btn-light px-4 border">
                            Cancelar Registro
                        </a>
                        <button type="button" id="btn-comprar" class="btn btn-success px-5 btn-action">
                            <i class="fas fa-save me-2"></i> Finalizar y Guardar Todo
                        </button>
                    </div>
                </div>
            </div>
        </form>

        @include('productos.partials.modals.modal_presentacion')
        @include('productos.partials.modals.modal_concentracion')
    </div>
@endsection

@push('scripts')
    <!-- Dependencias JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <script>
        (function($) {
            'use strict';

            // Mantener un array local de líneas e índice de edición
            let lines = [];
            let editingIndex = -1;

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
                        $tr.append(
                            `<td><small class="badge bg-secondary">${(ln.cb || '').substring(0, 8) || 'N/A'}</small></td>`
                        );
                        $tr.append(
                            `<td><small class="text-muted">${ln.codigo_ref ? ln.codigo_ref.substring(0, 8) + '...' : 'AUTO'}</small></td>`
                        );
                        $tr.append(
                            `<td><span class="fw-bold" style="font-size:0.8rem">${(ln.presentacion || 'Sin esp.').substring(0, 15)}</span></td>`
                        );
                        $tr.append(
                            `<td style="font-size:0.75rem">${(ln.concentracion || 'Sin esp.').substring(0, 15)}</td>`
                        );
                        $tr.append(
                            `<td class="text-center"><span class="badge bg-primary">${ln.cantidad}</span></td>`
                        );
                        $tr.append(
                            `<td class="text-end fw-bold text-success" style="font-size:0.8rem">${ln.precio_compra || '0.00'}</td>`
                        );
                        $tr.append(
                            `<td class="text-end fw-bold text-info" style="font-size:0.8rem">${ln.pvp || '0.00'}</td>`
                        );
                        $tr.append(
                            `<td class="text-end fw-bold text-warning" style="font-size:0.8rem">${ln.pvp_dto || '0.00'}</td>`
                        );
                        $tr.append(`<td class="text-end" style="font-size:0.75rem">${ln.peso || '0'}</td>`);
                        $tr.append(
                            `<td class="text-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-warning btn-edit-line" data-idx="${idx}" title="Editar"><i class="bx bx-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" data-idx="${idx}" title="Eliminar"><i class="bx bx-trash"></i></button>
                                </div>
                            </td>`
                        );
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
                
                // Reset botón de agregar
                editingIndex = -1;
                $('#btn-add-line').html('<i class="fas fa-plus-circle me-2"></i> Añadir a la Lista').removeClass('btn-warning').addClass('btn-primary');
            }

            // Función para generar código de barras EAN-13
            function generarCodigoBarras() {
                // Prefijo para productos internos (200-299 son para uso interno según estándar EAN)
                const prefijo = '200';

                // Generar 9 dígitos basados en timestamp y random
                const timestamp = Date.now().toString().slice(-6); // últimos 6 dígitos del timestamp
                const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0'); // 3 dígitos random

                // Formar los primeros 12 dígitos
                const codigo12 = prefijo + timestamp + random;

                // Calcular dígito verificador EAN-13
                let suma = 0;
                for (let i = 0; i < 12; i++) {
                    const digito = parseInt(codigo12.charAt(i));
                    suma += (i % 2 === 0) ? digito : digito * 3;
                }
                const verificador = (10 - (suma % 10)) % 10;

                return codigo12 + verificador;
            }

            function updateBarcode() {
                const code = $('#cb').val().trim();
                const $container = $('#barcode-container');
                const bWidth = parseInt($('#bc-width').val()) || 2;
                const bHeight = parseInt($('#bc-height').val()) || 60;

                if (code.length >= 4) {
                    $container.removeClass('d-none');
                    try {
                        JsBarcode("#barcode-svg", code, {
                            format: code.length === 13 ? "EAN13" : "CODE128",
                            lineColor: "#000",
                            width: bWidth,
                            height: bHeight,
                            displayValue: true,
                            fontSize: 14,
                            background: "#ffffff",
                            margin: 10
                        });
                    } catch (e) {
                        console.warn('Barcode preview error:', e);
                        $container.addClass('d-none');
                    }
                } else {
                    $container.addClass('d-none');
                }
            }

            // Descargar código de barras
            $('#btn-download-bc').on('click', function() {
                const svg = document.querySelector("#barcode-svg");
                const svgData = new XMLSerializer().serializeToString(svg);
                const canvas = document.createElement("canvas");
                const ctx = canvas.getContext("2d");
                const img = new Image();

                const svgSize = svg.getBBox();
                canvas.width = svgSize.width + 20;
                canvas.height = svgSize.height + 20;

                img.onload = function() {
                    ctx.fillStyle = "white";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 10, 10);
                    const pngFile = canvas.toDataURL("image/png");
                    const downloadLink = document.createElement("a");
                    downloadLink.download = `barcode-${$('#cb').val().trim()}.png`;
                    downloadLink.href = pngFile;
                    downloadLink.click();
                };

                img.src = "data:image/svg+xml;base64," + btoa(unescape(encodeURIComponent(svgData)));
            });

            // Imprimir código de barras
            $('#btn-print-bc').on('click', function() {
                const code = $('#cb').val().trim();
                const svgContent = document.getElementById('barcode-svg').outerHTML;
                const printWindow = window.open('', '_blank', 'width=600,height=400');
                
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Imprimir Código de Barras - ${code}</title>
                            <style>
                                body { display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                                .print-container { text-align: center; }
                            </style>
                        </head>
                        <body>
                            <div class="print-container">
                                ${svgContent}
                            </div>
                            <script>
                                window.onload = function() {
                                    window.print();
                                    setTimeout(function() { window.close(); }, 500);
                                };
                            <\/script>
                        </body>
                    </html>
                `);
                printWindow.document.close();
            });

            // Detectar cambios manuales y de tamaño
            $('#cb, #bc-width, #bc-height').on('input change', updateBarcode);

            // Handler para generar código de barras automáticamente
            $('#btn-generar-cb').on('click', function() {
                const nuevoCodigo = generarCodigoBarras();
                $('#cb').val(nuevoCodigo);
                updateBarcode();

                // Efecto visual de éxito
                const $input = $('#cb');
                $input.addClass('is-valid');
                setTimeout(() => $input.removeClass('is-valid'), 2000);

                // Toast de confirmación
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Código generado: ' + nuevoCodigo,
                    showConfirmButton: false,
                    timer: 2000
                });
            });

            // Añadir línea cuando se presiona el botón
            $('#btn-add-line').on('click', function() {
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
                    pvp2: parseFloat($('#pvp2').val()) || 0,
                    stock_maximo: parseInt($('#stock_maximo').val()) || 0,
                    stock_minimo: parseInt($('#stock_minimo').val()) || 0,
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

                if (editingIndex !== -1) {
                    lines[editingIndex] = ln;
                    editingIndex = -1;
                    $('#btn-add-line').html('<i class="fas fa-plus-circle me-2"></i> Añadir a la Lista').removeClass('btn-warning').addClass('btn-primary');
                } else {
                    lines.push(ln);
                }

                renderLines();
                clearForm();

                // Toast de éxito
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: editingIndex !== -1 ? 'Producto actualizado' : 'Producto agregado',
                    showConfirmButton: false,
                    timer: 1500
                });
            });

            // Editar línea
            $(document).on('click', '.btn-edit-line', function() {
                const idx = Number($(this).data('idx'));
                if (isNaN(idx)) return;

                const ln = lines[idx];
                editingIndex = idx;

                // Cargar datos al formulario
                $('#cb').val(ln.cb);
                $('#cantidad').val(ln.cantidad);
                $('#registro').val(ln.registro || '');
                $('#lote').val(ln.lote || '');
                $('#pa1').val(ln.pa1 || '');
                $('#pa2').val(ln.pa2 || '');
                $('#precio_compra').val(ln.precio_compra);
                $('#pvp').val(ln.pvp);
                $('#pvp_dto').val(ln.pvp_dto);
                $('#peso').val(ln.peso);
                $('#presentacion').val(ln.presentacion);
                $('#concentracion').val(ln.concentracion);
                $('#stock_maximo').val(ln.stock_maximo || 0);
                $('#stock_minimo').val(ln.stock_minimo || 0);
                $('#costo_operativo').val(ln.costo_operativo || 0);
                $('#pv_docena').val(ln.pv_docena || 0);
                $('#pvc').val(ln.pvc || 0);
                $('#pvc_dto').val(ln.pvc_dto || 0);
                $('#pvp2').val(ln.pvp2 || 0);
                $('#fecha_venc').val(ln.fecha_venc);

                // Cambiar botón
                $('#btn-add-line').html('<i class="fas fa-sync-alt me-2"></i> Actualizar en la Lista').removeClass('btn-primary').addClass('btn-warning');
                
                // Scroll al inicio del card para ver el formulario
                $('.col-lg-8')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                updateBarcode();
            });

            // Eliminar línea
            $(document).on('click', '.btn-remove-line', function() {
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
                            lines.splice(idx, 1);
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
            $('#btn-comprar').on('click', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const originalText = $btn.html();

                // Confirmación con SweetAlert
                Swal.fire({
                    title: '¿Completar registro?',
                    text: lines.length > 0 ?
                        `Se registrarán ${lines.length} línea(s) de producto` :
                        'Se registrará el producto sin líneas adicionales',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, completar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    // Mostrar loading en botón
                    $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop(
                        'disabled', true);

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
                                        errors[field].forEach(msg => errorList.push(
                                            msg));
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
                                html: errorDetails ||
                                    'Revise los datos e intente nuevamente.',
                                confirmButtonText: 'Entendido'
                            });
                        },
                        timeout: 30000 // 30 segundos timeout
                    });
                });
            });

            // Cargar líneas preexistentes si las hay
            try {
                const pre = $('#product_lines').val();
                if (pre && pre !== '[]') {
                    const parsed = JSON.parse(pre);
                    if (Array.isArray(parsed) && parsed.length) {
                        lines = parsed;
                    }
                }
            } catch (e) {
                console.warn('Error cargando líneas preexistentes:', e);
            }
            
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
                            $('#presentacion').append(new Option(response.data.nombre, response.data
                                .nombre));
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
                            $('#concentracion').append(new Option(response.data.nombre, response
                                .data.nombre));
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
@endpush

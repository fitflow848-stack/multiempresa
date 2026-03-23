@extends('layout.app')

@section('content')
    {{-- Dependencias --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

    <style>
        .almacen-container {
            padding: 20px;
            padding-bottom: 110px;
        }

        /* Estilos de UI */
        .breadcrumb-custom {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2d3436;
            margin: 0;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #edf2f7;
            font-weight: 700;
            color: #4a5568;
            padding: 12px 20px;
        }

        /* Tablas */
        .table thead th {
            background-color: var(--bg-light);
            text-transform: uppercase;
            font-size: 0.7rem;
            color: #718096;
            border: none;
        }

        .table tbody td {
            vertical-align: middle;
            font-size: 0.85rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-hover tbody tr:hover {
            background-color: #f8faff;
            cursor: pointer;
        }

        tr.selected {
            background-color: #e0e7ff !important;
            border-left: 4px solid var(--primary-color);
        }

        /* Inputs de tabla corregidos */
        .table-input {
            width: 100%;
            border: 1px solid #e2e8f0;
            background: #fff;
            text-align: right;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .table-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
        }

        /* Footer Fijo */
        .summary-footer {
            position: fixed;
            bottom: 0;
            left: 10;
            right: 0;
            width: 50%;
            background: #fff;
            border-top: 2px solid #4361ee;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1050;
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.08);
        }

        .stat-group {
            display: flex;
            gap: 40px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #718096;
            font-weight: 700;
        }

        .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1a202c;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .almacen-container {
                padding: 10px;
                padding-bottom: 250px; /* More space for stacked footer */
            }
            .summary-footer {
                flex-direction: column;
                width: 100% !important;
                left: 0 !important;
                padding: 15px;
                gap: 15px;
            }
            .stat-group {
                flex-direction: column;
                gap: 10px;
                width: 100%;
                text-align: center;
            }
            .stat-value {
                font-size: 1.1rem;
            }
            #btn-recibir {
                width: 100%;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>

    <div class="container-fluid almacen-container">
        {{-- Formulario de Búsqueda --}}
        <div class="card">
            <div class="card-body">
                <form id="search-form" class="row g-2">
                    <div class="col-md-2">
                        <label class="small fw-bold">LOCAL</label>
                        <select class="form-select form-select-sm" name="local">
                            <option value="">TODOS</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" {{ request('local') == $sucursal->id ? 'selected' : '' }}>
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">DESDE</label>
                        <input type="date" class="form-control form-control-sm" name="desde" value="{{ request('desde') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">HASTA</label>
                        <input type="date" class="form-control form-control-sm" name="hasta" value="{{ request('hasta') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">PROVEEDOR</label>
                        <input type="text" class="form-control form-control-sm" name="proveedor" placeholder="Nombre..." value="{{ request('proveedor') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">BUSCAR</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabla de Documentos --}}
        <div class="card">
            <div class="card-header">Documentos Pendientes</div>
            <div class="table-responsive" style="max-height: 250px;">
                <table class="table table-hover mb-0" id="tabla-compras">
                    <thead>
                        <tr>
                            <th># ID</th>
                            <th>Almacén</th>
                            <th>Serie-Número</th>
                            <th>Proveedor</th>
                            <th class="text-end">Total Neto</th>
                            <th>Fecha Registro</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($compras as $compra)
                            <tr data-compra-id="{{ $compra->id }}">
                                <td>{{ $compra->id }}</td>
                                <td>{{ $compra->almacen ? $compra->almacen->nombre : $compra->local_destino }}</td>
                                <td class="fw-bold">Z-{{ $compra->id }}</td>
                                <td>{{ $compra->proveedor->nombre_comercial ?? 'Sin Proveedor' }}</td>
                                <td class="text-end fw-bold">{{ number_format($compra->total_bruto, 2) }}</td>
                                <td>{{ $compra->created_at }}</td>
                                <td>{{ $compra->usuario->name ?? '---' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabla de Detalles --}}
        <div class="card">
            <div class="card-header bg-light">Detalle del Documento Seleccionado</div>
            <div class="table-responsive">
                <table class="table table-sm" id="tabla-detalle">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th width="70">Cant.</th>
                            <th width="100">Costo Ref.</th>
                            <th width="110">COP (S/)</th>
                            <th width="90">MU %</th>
                            <th width="90">MU/D %</th>
                            <th class="text-end">MUP</th>
                            <th class="text-end">PVP</th>
                            <th class="text-end text-primary">PA</th>
                            <th class="text-end">PVP/D</th>
                            <th class="text-end text-danger">PA/D</th>
                            <th width="90">PVC</th>
                            <th width="90">PVC/D</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">Seleccione una compra para ver los
                                productos</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Resumen Fijo --}}
    <div class="summary-footer">
        <div class="stat-group">
            <div class="stat-item">
                <span class="stat-label">Margen Total (MUP)</span>
                <span class="stat-value text-dark" id="total-mup">0.00</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Precio Venta Total (PA)</span>
                <span class="stat-value text-primary" id="total-pa">0.00</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Venta con Desc. (PA/D)</span>
                <span class="stat-value text-danger" id="total-pa-d">0.00</span>
            </div>
        </div>
        <button class="btn btn-primary btn-lg shadow px-5 fw-bold" id="btn-recibir">
            RECIBIR PRODUCTOS
        </button>
    </div>

    {{-- Scripts --}}
    <!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
    @push('scripts')
        <script>
            let compraSeleccionada = null;

            $(function() {
                // 1. Selección de Fila
                $('#tabla-compras tbody').on('click', 'tr', function() {
                    const $row = $(this);
                    const id = $row.data('compra-id');

                    if (compraSeleccionada === id) return;

                    compraSeleccionada = id;
                    $('#tabla-compras tbody tr').removeClass('selected');
                    $row.addClass('selected');

                    cargarDetalle(id);
                });

                // 2. Cargar Detalle vía AJAX
                function cargarDetalle(id) {
                    Swal.fire({
                        title: 'Cargando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.get(`{{ url('/recibir-productos') }}/${id}/detalle`, function(detalles) {
                        Swal.close();
                        renderDetalle(detalles);
                    }).fail(() => {
                        Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
                    });
                }

                // 3. Renderizar filas de productos
                function renderDetalle(detalles) {
                    const $tbody = $('#tabla-detalle tbody');
                    $tbody.empty();

                    if (detalles.length === 0) {
                        $tbody.append('<tr><td colspan="13" class="text-center">No hay productos</td></tr>');
                        return;
                    }

                    detalles.forEach((item) => {
                        // valores base
                        const nombre = item.nombre || item.descripcion || '';
                        const cantidad = item.cantidad || 1;
                        const detalleText = item.detalle || '';
                        const stockMin = item.stock_min || item.stockMin || 0;
                        const stockMax = item.stock_max || item.stockMax || 0;
                        const lote = item.lote || '';

                        const copVal = parseFloat(item.compra_costo ?? item.costo ?? 0) || 0;
                        const pvpVal = parseFloat(item.master_pvp ?? item.compra_pvp ?? item.pvp ?? 0) || 0;
                        const pvpDVal = parseFloat(item.master_pvp_dto ?? item.pvp_dto ?? 0) || 0;
                        const pvcVal = parseFloat(item.master_pvc ?? item.compra_pvc ?? item.pvc ?? 0) || 0;
                        const pvcDVal = parseFloat(item.master_pvc_dto ?? item.compra_pvc_dto ?? item.pvc_dto ??
                            item.pvcd ?? 0) || 0;

                        // Si el PVP es igual al costo, forzamos un margen predeterminado para evitar MU=0
                        // a menos que el usuario lo haya definido así explícitamente en el catálogo.
                        let muVal = 10;
                        if (copVal > 0 && pvpVal > 0) {
                            muVal = ((pvpVal - copVal) / copVal) * 100;
                        }

                        let muDVal = 0;
                        if (copVal > 0 && pvpDVal > 0) {
                            muDVal = ((pvpDVal - copVal) / copVal) * 100;
                        }

                        $tbody.append(`
                            <tr data-producto-id="${item.product_id || item.product_linea_id || item.id}" 
                                data-producto-id-linea="${item.product_linea_id || ''}"
                                data-cantidad="${cantidad}"
                                data-detalle="${detalleText}"
                                data-stock-min="${stockMin}"
                                data-stock-max="${stockMax}"
                                data-lote="${lote}"
                                data-fecha-vencimiento="${item.fecha_vencimiento || item.fecha_venc || ''}"
                                data-pvc="${item.master_pvc || item.compra_pvc || 0}">
                                <td class="fw-bold">${nombre}</td>
                                <td class="text-center">${cantidad}</td>
                                <td class="costo-ref text-muted text-end">${copVal.toFixed(2)}</td>
                                <td><input type="number" class="table-input cop" value="${copVal.toFixed(2)}"></td>
                                <td><input type="number" class="table-input mu" value="${muVal.toFixed(2)}"></td>
                                <td><input type="number" class="table-input mu_desc" value="${muDVal.toFixed(2)}"></td>
                                <td class="mup text-end">0.00</td>
                                <td class="pvp text-end">${pvpVal.toFixed(2)}</td>
                                <td class="pa text-end fw-bold text-primary">0.00</td>
                                <td class="pvp_d text-end">0.00</td>
                                <td class="pa_d text-end fw-bold text-danger">0.00</td>
                                <td><input type="number" class="table-input pvc" value="${pvcVal.toFixed(2)}" step="0.01"></td>
                                <td><input type="number" class="table-input pvcd" value="${pvcDVal.toFixed(2)}" step="0.01"></td>
                            </tr>
                        `);
                    });
                    // recalcular actualizará mup/pa etc, pero dejando pvp si venía del origen
                    recalcular();
                }

                // Cargar detalle desde un array de objetos (JSON) — función pública
                window.loadDetalleFromArray = function(productos) {
                    const $tbody = $('#tabla-detalle tbody');
                    $tbody.empty();

                    if (!Array.isArray(productos) || productos.length === 0) {
                        $tbody.append('<tr><td colspan="13" class="text-center">No hay productos</td></tr>');
                        return;
                    }

                    productos.forEach(function(p) {
                        // Mapear campos del objeto entrante a los que usa la tabla
                        const nombre = p.nombre || p.descripcion || p.descripcion_producto || '';
                        const cantidad = p.cantidad || p.qty || 1;
                        const detalle = p.detalle || '';
                        const costo = (p.costo !== undefined && p.costo !== null) ? Number(p.costo).toFixed(
                            2) : (p.precio_compra ? Number(p.precio_compra).toFixed(2) : '0.00');
                        const stock_min = p.stock_min || p.stockMin || 0;
                        const stock_max = p.stock_max || p.stockMax || 0;
                        const lote = p.lote || '';
                        const fecha_vencimiento = p.fecha_vencimiento || p.fecha_venc || '';
                        const pvcVal = parseFloat(p.pvc || 0);
                        const pvcDVal = parseFloat(p.pvc_dto || p.pvcd || 0);

                        $tbody.append(`
                            <tr data-producto-id="${p.producto_id || p.product_id || p.product_linea_id || ''}"
                                data-producto-id-linea="${p.product_linea_id || ''}"
                                data-cantidad="${cantidad}"
                                data-stock-min="${stock_min}"
                                data-stock-max="${stock_max}"
                                data-lote="${lote}"
                                data-fecha-vencimiento="${fecha_vencimiento}">
                                <td class="fw-bold">${nombre}</td>
                                <td class="text-center">${cantidad}</td>
                                <td class="costo-ref text-muted text-end">${costo}</td>
                                <td><input type="number" class="table-input cop" value="${costo}"></td>
                                <td><input type="number" class="table-input mu" value="10"></td>
                                <td><input type="number" class="table-input mu_desc" value="0"></td>
                                <td class="mup text-end">0.00</td>
                                <td class="pvp text-end">0.00</td>
                                <td class="pa text-end fw-bold text-primary">0.00</td>
                                <td class="pvp_d text-end">0.00</td>
                                <td class="pa_d text-end fw-bold text-danger">0.00</td>
                                <td><input type="number" class="table-input pvc" value="${pvcVal.toFixed(2)}" step="0.01"></td>
                                <td><input type="number" class="table-input pvcd" value="${pvcDVal.toFixed(2)}" step="0.01"></td>
                                <!-- Campos editables extras -->
                                <input type="hidden" class="detalle-text" value="${detalle}">
                                <input type="hidden" class="original-costo" value="${costo}">
                                <input type="hidden" class="original-nombre" value="${nombre}">
                                <input type="hidden" class="original-fecha-venc" value="${fecha_vencimiento}">
                            </tr>
                        `);
                    });

                    // Aplicar recalculos iniciales
                    recalcular();
                };

                // 4. Cálculos en Tiempo Real
                $(document).on('input', '.cop, .mu, .mu_desc', function() {
                    recalcular();
                });

                function recalcular() {
                    let tMup = 0,
                        tPa = 0,
                        tPad = 0;

                    $('#tabla-detalle tbody tr').each(function() {
                        const $tr = $(this);
                        const cant = parseFloat($tr.data('cantidad')) || 0;
                        const cop = parseFloat($tr.find('.cop').val()) || 0;
                        const mu = parseFloat($tr.find('.mu').val()) || 0;
                        const muD = parseFloat($tr.find('.mu_desc').val()) || 0;

                        // Cálculos estándar
                        const mupVal = cop * (mu / 100);
                        const pvpVal = cop + mupVal;
                        const paVal = pvpVal * cant;

                        // Cálculos descuento
                        const mupDVal = cop * (muD / 100);
                        const pvpDVal = cop + mupDVal;
                        const paDVal = pvpDVal * cant;

                        // Actualizar celdas
                        $tr.find('.mup').text(mupVal.toFixed(2));
                        $tr.find('.pvp').text(pvpVal.toFixed(2));
                        $tr.find('.pa').text(paVal.toFixed(2));
                        $tr.find('.pvp_d').text(pvpDVal.toFixed(2));
                        $tr.find('.pa_d').text(paDVal.toFixed(2));

                        // Acumular totales
                        tMup += (mupVal * cant);
                        tPa += paVal;
                        tPad += paDVal;
                    });

                    $('#total-mup').text(tMup.toLocaleString('es-PE', {
                        minimumFractionDigits: 2
                    }));
                    $('#total-pa').text(tPa.toLocaleString('es-PE', {
                        minimumFractionDigits: 2
                    }));
                    $('#total-pa-d').text(tPad.toLocaleString('es-PE', {
                        minimumFractionDigits: 2
                    }));
                }

                // 5. Botón Recibir (Enviar Datos)
                $('#btn-recibir').on('click', function() {
                    if (!compraSeleccionada) {
                        return Swal.fire('Atención', 'Seleccione un documento primero', 'warning');
                    }

                    let dataFinal = [];

                    $('#tabla-detalle tbody tr').each(function() {
                        const $tr = $(this);
                        dataFinal.push({
                            producto_id: $tr.data('producto-id'),
                            producto_id_linea: $tr.data('producto-id-linea'),
                            nombre: $tr.find('td:eq(0)').text(),
                            cantidad: parseFloat($tr.find('td:eq(1)').text()),
                            detalle: $tr.data('detalle') || '',
                            costo: parseFloat($tr.find('.costo-ref').text()) || 0,

                            cop: parseFloat($tr.find('.cop').val()) || 0,
                            mu: parseFloat($tr.find('.mu').val()) || 0,
                            mu_desc: parseFloat($tr.find('.mu_desc').val()) || 0,

                            mup: parseFloat($tr.find('.mup').text()) || 0,
                            pvp: parseFloat($tr.find('.pvp').text()) || 0,
                            pa: parseFloat($tr.find('.pa').text()) || 0,

                            pvp_d: parseFloat($tr.find('.pvp_d').text()) || 0,
                            pa_d: parseFloat($tr.find('.pa_d').text()) || 0,

                            pvc: parseFloat($tr.find('.pvc').val()) || 0,
                            pvcd: parseFloat($tr.find('.pvcd').val()) || 0
                        });
                    });

                    // Agregar campos adicionales si existen en data-attributes
                    dataFinal = dataFinal.map(function(item, idx) {
                        const $tr = $('#tabla-detalle tbody tr').eq(idx);
                        item.stock_min = parseFloat($tr.data('stock-min')) || 0;
                        item.stock_max = parseFloat($tr.data('stock-max')) || 0;
                        item.lote = $tr.data('lote') || '';
                        item.fecha_vencimiento = $tr.data('fecha-vencimiento') || null;
                        return item;
                    });

                    enviarFormulario(dataFinal);
                });

                function enviarFormulario(productos) {
                    const form = $('<form>', {
                        method: 'POST',
                        action: '{{ route('recibir-productos.confirmacion') }}'
                    });
                    form.append($('<input>', {
                        type: 'hidden',
                        name: '_token',
                        value: '{{ csrf_token() }}'
                    }));
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'compra_id',
                        value: compraSeleccionada
                    }));
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'productos',
                        value: JSON.stringify(productos)
                    }));
                    $('body').append(form);
                    form.submit();
                }
            });
        </script>
    @endpush
@endsection

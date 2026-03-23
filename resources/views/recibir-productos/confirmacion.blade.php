@extends('layout.app')

@section('content')
    {{-- Dependencias --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary-bg: #f4f7f6;
            --accent-color: #4361ee;
            --border-color: #edf2f7;
        }

        body {
            background-color: var(--primary-bg);
            font-family: 'Inter', sans-serif;
            color: #2d3748;
        }

        .almacen-container {
            padding: 1.5rem;
            padding-bottom: 120px;
        }

        @media (max-width: 768px) {
            .almacen-container {
                padding: 0.8rem;
                padding-bottom: 220px;
            }
        }

        /* Cabecera y Breadcrumb */
        .breadcrumb-custom {
            font-size: 0.85rem;
            color: #718096;
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            background: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .page-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 1.5rem;
        }

        /* Estructura de Tabla Compacta */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: #fff;
            font-weight: 700;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem;
        }

        .table-responsive {
            max-height: 60vh;
            overflow: both;
            border-radius: 0 0 12px 12px;
        }

        .table-custom {
            font-size: 0.75rem;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        /* Sticky Headers */
        .table-custom thead tr:nth-child(1) th {
            top: 0;
            z-index: 11;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom thead tr:nth-child(2) th {
            top: 32px;
            z-index: 10;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-custom thead th {
            position: sticky;
            padding: 8px 4px;
            text-transform: uppercase;
            font-weight: 700;
            border-right: 1px solid var(--border-color);
            white-space: nowrap;
        }

        /* Columna de Producto Fija */
        .sticky-col {
            position: sticky;
            left: 0;
            background: #fff !important;
            z-index: 5;
            min-width: 200px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            padding-left: 10px !important;
        }

        /* Agrupadores de diseño */
        .bg-precios {
            background-color: #eef2ff !important;
            color: #4338ca;
        }

        .bg-stock {
            background-color: #ecfdf5 !important;
            color: #065f46;
        }

        .bg-lote {
            background-color: #fffbeb !important;
            color: #92400e;
        }

        /* Estilo de Inputs tipo Excel */
        .table-input {
            width: 100%;
            height: 26px;
            padding: 2px 5px;
            font-size: 0.8rem;
            border: 1px solid transparent;
            background: transparent;
            text-align: right;
            border-radius: 4px;
            transition: 0.2s;
        }

        .table-input:hover {
            border-color: #cbd5e0;
            background: #fff;
        }

        .table-input:focus {
            outline: none;
            border-color: var(--accent-color);
            background: #fff;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
        }

        .val-calc {
            font-weight: 600;
            text-align: right;
            display: block;
        }

        .text-pa {
            color: #e53e3e;
            font-weight: 700;
        }

        /* Footer Flotante */
        .summary-footer {
            position: fixed;
            bottom: 20px;
            left: 50%;
            width: 80%;
            max-width: 900px;
            transform: translateX(-50%);
            background: #fff;
            padding: 15px 30px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            z-index: 1050;
            border: 1px solid #e2e8f0;
        }

        @media (max-width: 768px) {
            .summary-footer {
                width: 95%;
                padding: 10px 15px;
                gap: 10px;
                border-radius: 15px;
                flex-wrap: wrap;
                justify-content: center;
                bottom: 10px;
            }
            .stat-group {
                flex: 1 1 30%;
                min-width: 80px;
                text-align: center;
            }
            .stat-group.border-start {
                border-start: none !important;
                border-left: 1px solid #edf2f7 !important;
            }
            .stat-val {
                font-size: 0.9rem;
            }
            #btn-recibir {
                width: 100%;
                order: 4;
                margin-top: 5px;
            }
            .summary-footer .btn-light {
                display: none; /* Ocultar cancelar en footer móvil para ahorrar espacio */
            }
        }

        .stat-group {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.65rem;
            color: #718096;
            text-transform: uppercase;
            font-weight: 700;
        }

        .stat-val {
            font-size: 1.1rem;
            font-weight: 800;
        }
    </style>

    <div class="almacen-container">
        <div class="breadcrumb-custom">
            <div>Almacén &gt; <strong>Recibir Productos</strong></div>
            <div>Local: <strong>{{ $company->nombre_comercial }}</strong> | Operador: <strong>{{ $user->name }}</strong>
            </div>
        </div>

        <h1 class="page-title">Recepción de Documento: Z-{{ $compraId }}</h1>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>Listado de Productos por Recibir</span>
                <span class="badge bg-soft-primary text-primary">{{ count($productos) }} Items</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-custom" id="tabla-recepcion">
                        <thead>
                            {{-- Fila 1: Grupos --}}
                            <tr>
                                <th colspan="4" class="text-center">Información del Producto</th>
                                <th colspan="4" class="text-center bg-precios">Costos y Margen</th>
                                <th colspan="3" class="text-center bg-precios">PVP Sugerido</th>
                                <th colspan="4" class="text-center bg-precios">Venta con Desc.</th>
                                <th colspan="2" class="text-center bg-stock">Inventario</th>
                                <th colspan="2" class="text-center bg-lote">Trazabilidad</th>
                            </tr>
                            {{-- Fila 2: Columnas --}}
                            <tr>
                                <th width="30">#</th>
                                <th class="sticky-col">Nombre Producto</th>
                                <th width="60">Cant.</th>
                                <th width="100">Detalle</th>

                                <th width="80" class="bg-precios">Costo</th>
                                <th width="80" class="bg-precios">COP</th>
                                <th width="50" class="bg-precios">MU%</th>
                                <th width="70" class="bg-precios">MUP</th>

                                <th width="80" class="bg-precios">PVP</th>
                                <th width="80" class="bg-precios">PA</th>
                                <th width="50" class="bg-precios">MU/D%</th>

                                <th width="80" class="bg-precios">PVP/D</th>
                                <th width="80" class="bg-precios">PA</th>
                                <th width="70" class="bg-precios">PVC</th>
                                <th width="70" class="bg-precios">PVC/D</th>

                                <th width="70" class="bg-stock">Min</th>
                                <th width="70" class="bg-stock">Max</th>
                                <th width="100" class="bg-lote">Lote</th>
                                <th width="120" class="bg-lote">Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $i => $p)
                                <tr data-producto-id="{{ $p['producto_id'] }}"
                                    data-producto-id-linea="{{ $p['producto_id_linea'] ?? '' }}">
                                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                                    <td class="sticky-col fw-bold">{{ $p['nombre'] }}</td>
                                    <td class="text-center fw-bold">{{ $p['cantidad'] }}</td>
                                    <td><span class="text-muted"
                                            style="font-size: 0.7rem;">{{ $p['detalle'] ?? '-' }}</span></td>

                                    {{-- Costos y Margen --}}
                                    <td class="bg-precios"><input type="number" class="table-input costo"
                                            value="{{ $p['costo'] ?? 0 }}"></td>
                                    <td class="bg-precios"><input type="number" class="table-input cop"
                                            value="{{ $p['cop'] ?? 0 }}"></td>
                                    <td class="bg-precios"><input type="number" class="table-input mu"
                                            value="{{ $p['mu'] ?? 0 }}"></td>
                                    <td class="bg-precios"><span class="val-calc mup">0.00</span></td>

                                    {{-- PVP --}}
                                    <td class="bg-precios"><input type="number" class="table-input pvp"
                                            value="{{ $p['pvp'] ?? 0 }}"></td>
                                    <td class="bg-precios"><span class="val-calc pa-pvp text-pa">0.00</span></td>
                                    <td class="bg-precios"><input type="number" class="table-input mud" value="0">
                                    </td>

                                    {{-- Venta Desc --}}
                                    <td class="bg-precios"><input type="number" class="table-input pvpd"
                                            value="{{ $p['pvpd'] ?? ($p['pvp_d'] ?? 0) }}"></td>
                                    <td class="bg-precios"><span class="val-calc pa-pvpd text-pa">0.00</span></td>
                                    <td class="bg-precios"><input type="number" class="table-input pvc"
                                            value="{{ $p['pvc'] ?? 0 }}"></td>
                                    <td class="bg-precios"><input type="number" class="table-input pvcd"
                                            value="{{ $p['pvcd'] ?? 0 }}"></td>

                                    {{-- Stock --}}
                                    <td class="bg-stock"><input type="number" class="table-input stock-min"
                                            value="{{ $p['stock_min'] ?? 0 }}"></td>
                                    <td class="bg-stock"><input type="number" class="table-input stock-max"
                                            value="{{ $p['stock_max'] ?? 0 }}"></td>

                                    {{-- Lote --}}
                                    <td class="bg-lote"><input type="text" class="table-input lote"
                                            value="{{ $p['lote'] ?? '' }}" placeholder="Lote..."></td>
                                    <td class="bg-lote"><input type="date" class="table-input fecha-vencimiento"
                                            value="{{ $p['fecha_vencimiento'] ?? '' }}"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen Flotante --}}
    <div class="summary-footer">
        <div class="stat-group">
            <span class="stat-label">Total Margen (MUP)</span>
            <span class="stat-val text-dark" id="res-mup">S/ 0.00</span>
        </div>
        <div class="stat-group border-start ps-3">
            <span class="stat-label">Venta Total (PA)</span>
            <span class="stat-val text-primary" id="res-pa">S/ 0.00</span>
        </div>
        <div class="stat-group border-start ps-3 me-3">
            <span class="stat-label">Venta con Desc.</span>
            <span class="stat-val text-pa" id="res-pad">S/ 0.00</span>
        </div>
        <a href="{{ route('recibir-productos.index') }}" class="btn btn-light btn-sm rounded-pill px-3">Cancelar</a>
        <button class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" id="btn-recibir">RECIBIR
            PRODUCTOS</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {

            function recalcularFila($row) {
                const cant = parseFloat($row.find('td:eq(2)').text()) || 0;
                const cop = parseFloat($row.find('.cop').val()) || 0;
                const mu = parseFloat($row.find('.mu').val()) || 0;
                const pvp = parseFloat($row.find('.pvp').val()) || 0;
                const pvpd = parseFloat($row.find('.pvpd').val()) || 0;

                // 1. MUP: Costo * Margen %
                const mup = cop * (mu / 100);
                $row.find('.mup').text(mup.toFixed(2));

                // 2. PA PVP: Precio Venta * Cantidad
                const paPvp = pvp * cant;
                $row.find('.pa-pvp').text(paPvp.toFixed(2));

                // 3. PA PVPD: Precio Venta Desc * Cantidad
                const paPvpd = pvpd * cant;
                $row.find('.pa-pvpd').text(paPvpd.toFixed(2));

                actualizarTotales();
            }

            function actualizarTotales() {
                let tMup = 0,
                    tPa = 0,
                    tPad = 0;

                $('tbody tr').each(function() {
                    const $tr = $(this);
                    const cant = parseFloat($tr.find('td:eq(2)').text()) || 0;
                    tMup += (parseFloat($tr.find('.mup').text()) || 0) * cant;
                    tPa += parseFloat($tr.find('.pa-pvp').text()) || 0;
                    tPad += parseFloat($tr.find('.pa-pvpd').text()) || 0;
                });

                $('#res-mup').text('S/ ' + tMup.toFixed(2));
                $('#res-pa').text('S/ ' + tPa.toFixed(2));
                $('#res-pad').text('S/ ' + tPad.toFixed(2));
            }

            // Eventos de cálculo
            $(document).on('input', '.cop, .mu, .pvp, .pvpd', function() {
                recalcularFila($(this).closest('tr'));
            });

            // Inicializar cálculos
            $('tbody tr').each(function() {
                recalcularFila($(this));
            });

            // Enviar Datos
            $('#btn-recibir').click(function() {
                let items = [];
                let valid = true;

                $('tbody tr').each(function() {
                    const $tr = $(this);
                    const sMin = parseFloat($tr.find('.stock-min').val());
                    const sMax = parseFloat($tr.find('.stock-max').val());

                    if (sMax > 0 && sMax < sMin) {
                        Swal.fire('Error', 'El Stock Max no puede ser menor al Min', 'error');
                        valid = false;
                        return false;
                    }

                    items.push({
                        compra_id: {{ $compraId }},
                        producto_id: $tr.data('producto-id'),
                        producto_id_linea: $tr.data('producto-id-linea'),
                        cantidad: parseFloat($tr.find('td:eq(2)').text()),
                        costo: parseFloat($tr.find('input:eq(0)').val()),
                        cop: parseFloat($tr.find('.cop').val()),
                        mu: parseFloat($tr.find('.mu').val()),
                        mud: parseFloat($tr.find('.mud').val()),
                        mup: parseFloat($tr.find('.mup').text()),
                        pvp: parseFloat($tr.find('.pvp').val()) || 0,
                        pvpd: parseFloat($tr.find('.pvpd').val()) || 0,
                        pvc: parseFloat($tr.find('.pvc').val()) || 0,
                        pvcd: parseFloat($tr.find('.pvcd').val()) || 0,
                        // Nuevos campos
                        stock_min: parseFloat($tr.find('.stock-min').val()) || 0,
                        stock_max: parseFloat($tr.find('.stock-max').val()) || 0,
                        lote: $tr.find('.lote').val() || '',
                        fecha_vencimiento: $tr.find('.fecha-vencimiento').val() || null
                    });
                });

                if (!valid) return;

                Swal.fire({
                    title: '¿Confirmar Recepción?',
                    text: "Se registrará el ingreso de mercadería.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, procesar',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            url: "{{ route('recibir-productos.guardar') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                items: items,
                                compraId: "{{ $compraId }}"
                            }
                        }).catch(err => {
                            Swal.showValidationMessage(
                                `Error: ${err.responseJSON.message}`);
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('¡Éxito!', 'Productos recibidos correctamente', 'success')
                            .then(() => location.href = "{{ route('recibir-productos.index') }}");
                    }
                });
            });
        });
    </script>
@endsection

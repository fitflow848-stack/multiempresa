@extends('layout.app')

@section('content')
    {{-- ===================== STYLES ===================== --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/principal.css') }}">

    <div class="almacen-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <div>
                Almacén &gt; Recibir Productos
            </div>
            <div style="float: right;">
                Local: <strong>{{ $company->nombre_comercial }}</strong> | Operador: <strong>{{ $user->name }}</strong>
            </div>
        </div>

        <!-- Título -->
        <div class="page-title">
            Recibir Productos
        </div>

        <!-- Sección de búsqueda -->
        <div class="search-section">
            <form id="search-form">
                <div class="search-row">
                    <div class="form-group">
                        <label>Local:</label>
                        <select class="form-control" name="local">
                            <option value="PURINA">PURINA</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Desde:</label>
                        <input type="date" class="form-control" name="desde" value="">
                    </div>

                    <div class="form-group">
                        <label>Hasta:</label>
                        <input type="date" class="form-control" name="hasta" value="">
                    </div>

                    <div class="form-group">
                        <label>Documento:</label>
                        <select class="form-control" name="documento">
                            <option value="Todos">Todos</option>
                            <option value="Con Stock">Con Stock</option>
                            <option value="Sin Stock">Sin Stock</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Proveedor:</label>
                        <input type="text" class="form-control" name="proveedor" placeholder="">
                    </div>

                    <div class="form-group">
                        <label>Serie-Número:</label>
                        <input type="text" class="form-control" name="serie_numero" placeholder="F001-00001234">
                    </div>

                    <div style="display: flex; gap: 5px;">
                        <button type="submit" class="btn-search">Buscar</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de inventario -->
        <div class="inventory-table">
            <div class="table-header">
                Documentos por recibir
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th style="width: 80px;">Almacén</th>
                        <th style="width: 100px;">Serie-Número</th>
                        <th style="width: 100px;">Documento</th>
                        <th style="width: 300px;">Proveedor</th>
                        <th style="width: 80px;">Total Neto</th>
                        <th style="width: 60px;">Fecha de Registro</th>
                        <th style="width: 60px;">Usuario</th>
                        <th style="width: 60px;">Obs.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($compras as $compra)
                        <tr data-compra-id="{{ $compra->id }}">
                            <th scope="row">{{ $compra->id }}</th>
                            <td>{{ $compra->local_destino }}</td>
                            <td>Z-{{ $compra->id }}</td>
                            <td>{{ $compra->moneda }}</td>
                            <td>{{ $compra->total_bruto }}</td>
                            <td>{{ $compra->proveedor->nombre_comercial }}</td>
                            <td>{{ $compra->created_at }}</td>
                            <td>{{ $compra->usuario->name }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="inventory-table">
            <div class="table-header">
                Detalle
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th style="width: 80px;">Producto</th>
                        <th style="width: 100px;">Cantidad</th>
                        <th style="width: 100px;">Detalle</th>
                        <th style="width: 300px;">Costo</th>
                        <th style="width: 80px;">COP</th>
                        <th style="width: 60px;">MU%</th>
                        <th style="width: 60px;">MU/D%</th>
                        <th style="width: 60px;">MUP</th>
                        <th style="width: 60px;">PVP</th>
                        <th style="width: 60px; color:red">PA</th>
                        <th style="width: 60px;">PVP/D</th>
                        <th style="width: 60px; color:red">PA</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>

        <!-- Resumen -->
        <div class="summary-section">
            <div class="summary-title">Resumen</div>
            <div style="display: flex; justify-content: space-between">
                <div class="summary-stats">
                    <div class="stat-item">
                        Total MUP: <span class="stat-value" id="total-mup">0.00</span>
                    </div>
                    <div class="stat-item">
                        Total PA: <span class="stat-value" id="total-pa">0.00</span>
                    </div>
                    <div class="stat-item">
                        Total PA/D: <span class="stat-value" id="total-pa-d">0.00</span>
                    </div>
                </div>
                <div>
                    <button class="btn btn-primary" id="btn-recibir">
                        Recibir
                    </button>

                </div>
            </div>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        let compraSeleccionada = null;
        $(function() {

            $('.inventory-table table:first tbody').on('click', 'tr', function() {

                const $row = $(this);
                const compraId = $row.data('compra-id');

                // 👉 Si vuelve a hacer clic en la misma fila (toggle off)
                if (compraSeleccionada === compraId) {
                    compraSeleccionada = null;
                    $row.removeClass('selected');

                    // Limpiar detalle
                    limpiarDetalle();
                    return;
                }

                // 👉 Nueva selección
                compraSeleccionada = compraId;

                // Limpiar selección previa
                $('.inventory-table table:first tbody tr').removeClass('selected');
                $row.addClass('selected');

                // Cargar detalle
                cargarDetalleCompra(compraId);
            });

            function cargarDetalleCompra(compraId) {
                Swal.fire({
                    title: 'Cargando detalle...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: `{{ url('/recibir-productos') }}/${compraId}/detalle`,
                    method: 'GET',
                    success: function(data) {
                        Swal.close();
                        renderDetalle(data);
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                    }
                });
            }

            function renderDetalle(detalles) {
                const $tbody = $('.inventory-table:eq(1) tbody');
                $tbody.empty();

                detalles.forEach((item, index) => {
                    $tbody.append(`
                    <tr  data-producto-id="${item.id}" data-cantidad="${item.cantidad}">
                        <td>${index + 1}</td>
                        <td>${item.nombre}</td>
                        <td>${item.cantidad}</td>
                        <td>${item.detalle}</td>
                        <td class="costo">${item.precio_compra}</td>

                        <td>
                            <input type="number" class="form-control form-control-sm cop" value="${item.cop ?? item.precio_compra}">
                        </td>

                        <td>
                            <input type="number" class="form-control form-control-sm mu" value="${item.mu ?? 10}">
                        </td>

                        <td>
                            <input type="number" class="form-control form-control-sm mu_desc" value="${item.mu_desc ?? 0}">
                        </td>

                        <td class="mup text-end">0.00</td>
                        <td class="pvp text-end">0.00</td>
                        <td class="pa text-end text-danger">0.00</td>
                        <td class="pvp_d text-end">0.00</td>
                        <td class="pa_d text-end text-danger">0.00</td>
                    </tr>
                    `);
                });

                recalcularTodo();
            }

            $(document).on('input', '.cop, .mu, .mu_desc', function() {
                recalcularTodo();
            });

            function recalcularTodo() {
                let totalPA = 0;
                let totalPAD = 0;
                let totalMUP = 0;

                $('.inventory-table:eq(1) tbody tr').each(function() {
                    const $tr = $(this);

                    const cantidad = parseFloat($tr.data('cantidad'));
                    const cop = parseFloat($tr.find('.cop').val()) || 0;
                    const mu = parseFloat($tr.find('.mu').val()) || 0;
                    const muDesc = parseFloat($tr.find('.mu_desc').val()) || 0;

                    // Cálculos
                    const mup = cop * (mu / 100);
                    const pvp = cop + mup;
                    const pa = pvp * cantidad;

                    const mupD = cop * (muDesc / 100);
                    const pvpD = cop + mupD;
                    const paD = pvpD * cantidad;

                    // Pintar fila
                    $tr.find('.mup').text(mup.toFixed(2));
                    $tr.find('.pvp').text(pvp.toFixed(2));
                    $tr.find('.pa').text(pa.toFixed(2));
                    $tr.find('.pvp_d').text(pvpD.toFixed(2));
                    $tr.find('.pa_d').text(paD.toFixed(2));

                    // Totales
                    totalPA += pa;
                    totalPAD += paD;
                    totalMUP += mup * cantidad;
                });

                actualizarResumen(totalMUP, totalPA, totalPAD);
            }


            function actualizarResumen(mup, pa, pad) {
                $('#total-mup').text(mup.toFixed(2));
                $('#total-pa').text(pa.toFixed(2));
                $('#total-pa-d').text(pad.toFixed(2));
            }


            function limpiarDetalle() {
                const $tbody = $('.inventory-table:eq(1) tbody');
                $tbody.empty().append(`
            <tr>
                <td colspan="13" class="text-center text-muted">
                    Seleccione un documento para ver el detalle
                </td>
            </tr>
        `);
            }

        });

        $('#btn-recibir').on('click', function() {

            if (!compraSeleccionada) {
                Swal.fire('Atención', 'Debe seleccionar una compra', 'warning');
                return;
            }

            let productos = [];

            $('.inventory-table:eq(1) tbody tr').each(function() {
                const $tr = $(this);

                productos.push({
                    producto_id: $tr.data('producto-id'),
                    nombre: $tr.find('td:eq(1)').text(),
                    cantidad: parseFloat($tr.find('td:eq(2)').text()),
                    detalle: $tr.find('td:eq(3)').text(),
                    costo: parseFloat($tr.find('.costo').text()),

                    cop: parseFloat($tr.find('.cop').val()),
                    mu: parseFloat($tr.find('.mu').val()),
                    mu_desc: parseFloat($tr.find('.mu_desc').val()),

                    mup: parseFloat($tr.find('.mup').text()),
                    pvp: parseFloat($tr.find('.pvp').text()),
                    pa: parseFloat($tr.find('.pa').text()),

                    pvp_d: parseFloat($tr.find('.pvp_d').text()),
                    pa_d: parseFloat($tr.find('.pa_d').text())
                });

            });

            if (productos.length === 0) {
                Swal.fire('Atención', 'No hay productos para recibir', 'warning');
                return;
            }

            enviarRecibo(compraSeleccionada, productos);
        });

        function enviarRecibo(compraId, productos) {

            const form = $('<form>', {
                method: 'POST',
                action: '{{ route('recibir-productos.confirmacion') }}'
            });

            form.append('@csrf');

            form.append(
                $('<input>', {
                    type: 'hidden',
                    name: 'compra_id',
                    value: compraId
                })
            );

            form.append(
                $('<input>', {
                    type: 'hidden',
                    name: 'productos',
                    value: JSON.stringify(productos)
                })
            );

            $('body').append(form);
            form.submit();
        }
    </script>
@endsection

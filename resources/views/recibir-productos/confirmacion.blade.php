@extends('layout.app')

@section('content')
    {{-- ===================== STYLES ===================== --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

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


        <!-- Tabla de inventario -->
        <div class="card mb-3">
            <div class="card-header">
                Documentos por recibir
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Detalle</th>
                        <th>Costo</th>
                        <th>COP</th>
                        <th>MU%</th>
                        <th>MU/D%</th>
                        <th>MUP</th>
                        <th>PVP</th>
                        <th>PA</th>
                        <th>PVP/D</th>
                        <th>PA</th>
                        <th>MUC</th>
                        <th>PVC</th>
                        <th>PA</th>
                        <th style="background: #e8f5e8;" title="Stock mínimo recomendado para el producto">
                            Stock Min <span class="help-icon">ℹ️</span>
                        </th>
                        <th style="background: #e8f5e8;" title="Stock máximo recomendado para el producto">
                            Stock Max <span class="help-icon">ℹ️</span>
                        </th>
                        <th style="background: #fff3e0;" title="Número de lote del producto">
                            Lote <span class="help-icon">📦</span>
                        </th>
                        <th style="background: #fff3e0;" title="Fecha de vencimiento del lote">
                            F. Vencimiento <span class="help-icon">📅</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @dd($productos) --}}
                    @foreach ($productos as $i => $p)
                        <tr data-producto-id="{{ $p['producto_id'] }}">
                            <td>{{ $i + 1 }}</td>
                            <td class="text-start">{{ $p['nombre'] }}</td>
                            <td>{{ $p['cantidad'] }} NIU</td>
                            <td>{{ $p['detalle'] ?? '-' }}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" value="{{ $p['costo'] ?? 0 }}">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm cop" value="{{ $p['cop'] ?? 0 }}">
                            </td>

                            <td>
                                <input type="number" class="form-control form-control-sm mu" value="{{ $p['mu'] ?? 0 }}">
                            </td>

                            <td>
                                <input type="number" class="form-control form-control-sm mud" value="0">
                            </td>

                            <td class="mup">0.00</td>

                            <td>
                                <input type="number" class="form-control form-control-sm pvp" value="{{ $p['pvp'] ?? 0 }}">
                            </td>

                            <td class="pa-pvp text-danger">0.00</td>

                            <td>
                                <input type="number" class="form-control form-control-sm pvpd"
                                    value="{{ $p['pvp'] ?? 0 }}">
                            </td>

                            <td class="pa-pvpd text-danger">0.00</td>

                            <td class="muc">0.00</td>

                            <td>
                                <input type="number" class="form-control form-control-sm pvc" value="{{ $p['pvp'] ?? 0 }}">
                            </td>

                            <td class="pa-pvc text-danger">0.00</td>
                            
                            <!-- Nuevos campos editables -->
                            <td style="background: #f8f9fa;">
                                <input type="number" class="form-control form-control-sm stock-min" 
                                       value="{{ $p['stock_min'] ?? 0 }}" 
                                       placeholder="0" min="0">
                            </td>
                            
                            <td style="background: #f8f9fa;">
                                <input type="number" class="form-control form-control-sm stock-max" 
                                       value="{{ $p['stock_max'] ?? 0 }}" 
                                       placeholder="0" min="0">
                            </td>
                            
                            <td style="background: #fffbf0;">
                                <input type="text" class="form-control form-control-sm lote" 
                                       value="{{ $p['lote'] ?? '' }}" 
                                       placeholder="Lote..." maxlength="50">
                            </td>
                            
                            <td style="background: #fffbf0;">
                                <input type="date" class="form-control form-control-sm fecha-vencimiento" 
                                       value="{{ $p['fecha_vencimiento'] ?? '' }}">
                            </td>
                        </tr>
                    @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="summary-section card"
            style="position: fixed; bottom: 20px; right: 20px; width: auto; padding: 12px; border: 1px solid #ddd; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); z-index: 1050;">
            <div style="display: flex; justify-content: flex-end;">
                <div class="summary-stats">
                    <div class="stat-item">
                        <a class="btn btn-primary" href="{{ route('recibir-productos.index') }}">
                            Volver
                        </a>
                    </div>
                    <div class="stat-item">
                        <button class="btn btn-success" id="btn-recibir">
                            Recibir productos
                        </button>
                    </div>
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

    <style>
        /* Estilos para los nuevos campos editables */
        .stock-min, .stock-max {
            background-color: #f0f8f0 !important;
            border: 1px solid #28a745 !important;
        }
        
        .lote, .fecha-vencimiento {
            background-color: #fef9e7 !important;
            border: 1px solid #ffc107 !important;
        }
        
        .stock-min:focus, .stock-max:focus {
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5) !important;
        }
        
        .lote:focus, .fecha-vencimiento:focus {
            box-shadow: 0 0 5px rgba(255, 193, 7, 0.5) !important;
        }
        
        /* Indicadores visuales */
        .field-indicator {
            position: relative;
        }
        
        .field-indicator::after {
            content: "*";
            color: #dc3545;
            font-weight: bold;
            margin-left: 2px;
        }
        
        /* Tooltip para ayuda */
        .help-icon {
            color: #6c757d;
            cursor: help;
            margin-left: 5px;
        }
        
        .help-icon:hover {
            color: #495057;
        }
        
        /* Validación visual */
        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #f8d7da !important;
        }
        
        .is-valid {
            border-color: #28a745 !important;
            background-color: #d4edda !important;
        }

        /* Mejoras visuales generales (solo presentación) */
        .card-header {
            background: linear-gradient(90deg, #f8fafc, #ffffff);
            font-weight: 600;
            color: #343a40;
        }

        .table-responsive {
            max-height: 62vh;
            overflow: auto;
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #f8f9fa;
        }

        .help-icon {
            color: #6c757d;
            cursor: help;
            margin-left: 6px;
            font-size: 0.95em;
        }

        .summary-section.card {
            border-radius: 8px;
            padding: 10px 12px !important;
        }

        .summary-stats .btn {
            min-width: 130px;
        }

        .stat-item { display: inline-block; margin-left: 8px; }

        @media (max-width: 768px) {
            .summary-section.card { position: static; width: 100%; margin-top: 12px; }
            .table-responsive { max-height: 45vh; }
        }
    </style>

    <script>
        function recalcularFila($row) {
            let cop = parseFloat($row.find('.cop').val()) || 0;
            let mu = parseFloat($row.find('.mu').val()) || 0;
            let mud = parseFloat($row.find('.mud').val()) || 0;
            let pvp = parseFloat($row.find('.pvp').val()) || 0;
            let pvpd = parseFloat($row.find('.pvpd').val()) || 0;
            let pvc = parseFloat($row.find('.pvc').val()) || 0;

            // MUP
            let mup = cop * (mu / 100);
            $row.find('.mup').text(mup.toFixed(2));

            // PA PVP
            let paPvp = pvp - cop;
            $row.find('.pa-pvp').text(paPvp.toFixed(2));

            // PA PVP/D
            let paPvpd = pvpd - cop;
            $row.find('.pa-pvpd').text(paPvpd.toFixed(2));

            // MUC
            let muc = cop * (mud / 100);
            $row.find('.muc').text(muc.toFixed(2));

            // PA PVC
            let paPvc = pvc - cop;
            $row.find('.pa-pvc').text(paPvc.toFixed(2));
        }

        $(document).on('input', '.cop, .mu, .mud, .pvp, .pvpd, .pvc', function() {
            let $row = $(this).closest('tr');
            recalcularFila($row);
        });

        // recalcular todo al cargar
        $('tbody tr').each(function() {
            recalcularFila($(this));
        });

        // Validaciones para nuevos campos
        $(document).on('input', '.stock-min, .stock-max', function() {
            const $input = $(this);
            const value = parseInt($input.val()) || 0;
            
            if (value < 0) {
                $input.addClass('is-invalid').removeClass('is-valid');
                $input.val(0);
            } else {
                $input.addClass('is-valid').removeClass('is-invalid');
            }
            
            // Validar que stock_max >= stock_min
            const $row = $input.closest('tr');
            const stockMin = parseInt($row.find('.stock-min').val()) || 0;
            const stockMax = parseInt($row.find('.stock-max').val()) || 0;
            
            if (stockMax > 0 && stockMax < stockMin) {
                $row.find('.stock-max').addClass('is-invalid');
                $row.find('.stock-min').addClass('is-invalid');
            } else {
                $row.find('.stock-max').removeClass('is-invalid').addClass('is-valid');
                $row.find('.stock-min').removeClass('is-invalid').addClass('is-valid');
            }
        });

        // Validación de lote (caracteres especiales)
        $(document).on('input', '.lote', function() {
            const $input = $(this);
            let value = $input.val();
            
            // Remover caracteres especiales excepto guiones y puntos
            value = value.replace(/[^a-zA-Z0-9\-\.]/g, '');
            $input.val(value);
            
            if (value.length > 0) {
                $input.addClass('is-valid').removeClass('is-invalid');
            } else {
                $input.removeClass('is-valid is-invalid');
            }
        });

        // Validación de fecha de vencimiento
        $(document).on('change', '.fecha-vencimiento', function() {
            const $input = $(this);
            const fechaIngresada = new Date($input.val());
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            if ($input.val()) {
                if (fechaIngresada < hoy) {
                    $input.addClass('is-invalid').removeClass('is-valid');
                    alert('⚠️ Advertencia: La fecha de vencimiento es anterior a la fecha actual');
                } else {
                    $input.addClass('is-valid').removeClass('is-invalid');
                }
            } else {
                $input.removeClass('is-valid is-invalid');
            }
        });

        // Mejorar función de guardado con validación
        function validarFormulario() {
            let esValido = true;
            let errores = [];

            $('tbody tr').each(function(index) {
                const $row = $(this);
                const stockMin = parseInt($row.find('.stock-min').val()) || 0;
                const stockMax = parseInt($row.find('.stock-max').val()) || 0;
                const producto = $row.find('td:eq(1)').text();

                if (stockMax > 0 && stockMax < stockMin) {
                    errores.push(`Producto "${producto}": Stock máximo debe ser mayor o igual al stock mínimo`);
                    esValido = false;
                }
            });

            if (!esValido) {
                Swal.fire({
                    title: 'Errores de validación',
                    html: errores.join('<br>'),
                    icon: 'error',
                    confirmButtonText: 'Corregir'
                });
            }

            return esValido;
        }

        $('#btn-recibir').click(function() {
            // Validar formulario antes de procesar
            if (!validarFormulario()) {
                return;
            }

            // Confirmar acción
            Swal.fire({
                title: '¿Confirmar recepción?',
                text: 'Se procesarán todos los productos con la información ingresada',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, recibir productos',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarRecepcion();
                }
            });
        });

        function procesarRecepcion() {
            let items = [];

            $('tbody tr').each(function() {
                let $tr = $(this);

                items.push({
                    compra_id: {{ $compraId }},
                    producto_id: $tr.data('producto-id'),
                    cantidad: parseFloat($tr.find('td:eq(2)').text()),
                    costo: parseFloat($tr.find('input:eq(0)').val()),
                    cop: parseFloat($tr.find('.cop').val()),
                    mu: parseFloat($tr.find('.mu').val()),
                    mud: parseFloat($tr.find('.mud').val()),
                    mup: parseFloat($tr.find('.mup').text()),
                    pvp: parseFloat($tr.find('.pvp').val()),
                    pvpd: parseFloat($tr.find('.pvpd').val()),
                    pvc: parseFloat($tr.find('.pvc').val()),
                    // Nuevos campos
                    stock_min: parseFloat($tr.find('.stock-min').val()) || 0,
                    stock_max: parseFloat($tr.find('.stock-max').val()) || 0,
                    lote: $tr.find('.lote').val() || '',
                    fecha_vencimiento: $tr.find('.fecha-vencimiento').val() || null
                });
            });

            // Mostrar loading
            Swal.fire({
                title: 'Procesando recepción...',
                text: 'Por favor espere mientras se actualizan los productos',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('recibir-productos.guardar') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    items: items,
                    compraId: {{ $compraId }}
                },
                success: function(resp) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: resp.message + '. Productos actualizados correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        location.href = "{{ route('recibir-productos.index') }}";
                    });
                },
                error: function(err) {
                    const errorMsg = err.responseJSON?.message || 'No se pudo guardar la recepción';
                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'Reintentar'
                    });
                }
            });
        }
    </script>
@endsection

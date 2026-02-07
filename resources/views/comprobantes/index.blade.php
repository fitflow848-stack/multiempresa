@extends('layout.app')

@section('title', 'Comprobantes')

@section('content')
    <style>
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1px;
            padding: 15px 10px;
            border-bottom: 2px solid #edf2f7;
        }

        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge-pill {
            padding: 5px 12px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.7rem;
        }

        .btn-action-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        /* Estilo para la fila seleccionada */
        .table-active {
            background-color: #f0f7ff !important;
            border-left: 4px solid #3b82f6 !important;
        }

        .summary-item {
            border-bottom: 1px dashed #e2e8f0;
            padding: 8px 0;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .scroll-custom {
            max-height: 550px;
            overflow-y: auto;
        }

        .comprobante-row:hover {
            background-color: #f8f9fa;
        }

        .table-active {
            background-color: #e3f2fd !important;
        }

        .comprobante-cancelado {
            background-color: #fff0f0 !important;
            border-left: 4px solid #ef4444 !important;
            color: #6b0000 !important;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row align-items-center mb-4">
            <div class="col-md-5">
                <h2 class="fw-bold text-dark mb-0">Gestión de Comprobantes</h2>
                <p class="text-muted small">Visualiza, filtra y gestiona tus documentos electrónicos</p>
            </div>
            <div class="col-md-7 text-md-end">
                <div class="btn-group shadow-sm">
                    <button class="btn btn-white border px-3" id="btn-seleccionar-todo">
                        <i class="bx bx-check-double me-1 text-primary"></i> Todo
                    </button>
                    <button class="btn btn-danger border px-3" id="btn-anular">
                        <i class="bx bx-x-circle me-1"></i> Anular
                    </button>
                </div>
                <a class="btn btn-dark ms-2 px-4 shadow-sm" href="{{ route('pos.index') }}">
                    <i class="bx bx-plus-circle me-1"></i> volver
                </a>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-body p-3">
                <form id="form-filtros" method="GET" action="{{ route('comprobantes.index') }}"
                    class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Desde</label>
                        <input type="date" class="form-control border-0 bg-light" name="fecha_desde"
                            value="{{ $fechaDesde }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Hasta</label>
                        <input type="date" class="form-control border-0 bg-light" name="fecha_hasta"
                            value="{{ $fechaHasta }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Buscar Cliente</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light text-muted"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control border-0 bg-light" name="cliente"
                                value="{{ $cliente }}" placeholder="Nombre o RUC...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Tipo Documento</label>
                        <select class="form-select border-0 bg-light" name="tipo_documento">
                            <option value="todos" {{ $tipoDocumento == 'todos' ? 'selected' : '' }}>📄 Todos los documentos
                            </option>
                            <option value="ticket" {{ $tipoDocumento == 'ticket' ? 'selected' : '' }}>🎫 Ticket</option>
                            <option value="boleta" {{ $tipoDocumento == 'boleta' ? 'selected' : '' }}>🧾 Boleta</option>
                            <option value="factura" {{ $tipoDocumento == 'factura' ? 'selected' : '' }}>🏢 Factura</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <!-- Botón de aplicar filtros eliminado; filtros se aplican automáticamente al cambiar -->
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="table-container shadow-sm">
                    <div class="table-responsive scroll-custom">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3"><input type="checkbox" class="form-check-input" id="check-all"></th>
                                    <th>Tipo</th>
                                    <th>Serie-Número</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">SUNAT</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventas as $venta)
                                    <tr class="comprobante-row {{ $comprobanteSeleccionado && $comprobanteSeleccionado->id_venta == $venta->id_venta ? 'table-active' : '' }} {{ isset($venta->estado) && $venta->estado == 0 ? 'comprobante-cancelado' : '' }}"
                                        data-venta-id="{{ $venta->id_venta }}" data-total="{{ $venta->total }}"
                                        data-pagado="{{ $venta->pagado ? 1 : 0 }}"
                                        data-estado="{{ $venta->estado ?? 1 }}">
                                        <td class="ps-3">
                                            <input type="checkbox" class="form-check-input comprobante-check"
                                                value="{{ $venta->id_venta }}"
                                                {{ isset($venta->estado) && $venta->estado == 0 ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            @php
                                                $tipoDoc = strtolower($venta->tipo_documento ?? 'ticket');
                                                $badgeClass = match ($tipoDoc) {
                                                    'factura' => 'bg-primary',
                                                    'boleta' => 'bg-success',
                                                    'nota-venta' => 'bg-warning text-dark',
                                                    default => 'bg-info text-white',
                                                };
                                            @endphp
                                            <span
                                                class="badge badge-pill {{ $badgeClass }}">{{ strtoupper($venta->tipo_documento ?? 'TIC') }}</span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            {{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <div class="text-dark small fw-bold">
                                                {{ $venta->cliente ? $venta->cliente->nombre : 'CLIENTE PARTICULAR' }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                {{ $venta->fecha_emision->format('d/m/Y H:i') }}</div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-dark">S/
                                                {{ number_format($venta->total, 2) }}</span><br>
                                            <small class="text-{{ $venta->pagado ? 'success' : 'danger' }}"
                                                style="font-size: 0.65rem;">
                                                {{ $venta->pagado ? 'PAGADO' : 'PENDIENTE' }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @if ($venta->enviado_sunat)
                                                <i class="bx bxs-check-circle text-success fs-4"
                                                    title="Enviado correctamente"></i>
                                            @elseif(strtolower($venta->tipo_documento) !== 'ticket')
                                                <button class="btn btn-sm btn-light text-info btn-send-sunat"
                                                    data-venta="{{ $venta->id_venta }}">
                                                    <i class="bx bx-send"></i>
                                                </button>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if (isset($venta->estado) && $venta->estado == 0)
                                                <span class="badge bg-danger">CANCELADO</span>
                                            @elseif(isset($venta->estado) && $venta->estado == 3)
                                                <span class="badge bg-warning text-dark">DEVUELTO</span>
                                            @else
                                                <span class="badge bg-success">ACTIVO</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button"
                                                    class="btn btn-action-icon btn-light text-primary btn-detalle"
                                                    data-venta-id="{{ $venta->id_venta }}">
                                                    <i class="bx bx-search-alt"></i>
                                                </button>
                                                <button class="btn btn-action-icon btn-light text-secondary btn-imprimir"
                                                    type="button" data-venta-id="{{ $venta->id_venta }}"
                                                    data-tipo="{{ strtolower($venta->tipo_documento ?? 'ticket') }}"
                                                    title="Imprimir">
                                                    <i class="bx bx-printer"></i>
                                                </button>
                                                @if (isset($venta->ventaSunat->nombre_xml))
                                                    <div class="dropdown">
                                                        <button type="button"
                                                            class="btn btn-action-icon btn-light text-dark"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <ul class="dropdown-menu shadow-sm border-0">
                                                            <li><a class="dropdown-item small"
                                                                    href="{{ env('APP_URL') }}/storage/xml_sunat/{{ $venta->ventaSunat->nombre_xml }}.xml"
                                                                    target="_blank">📄 Ver XML</a></li>
                                                            @if ($venta->enviado_sunat)
                                                                <li><a class="dropdown-item small"
                                                                        href="{{ env('APP_URL') }}/storage/cdrs/R-{{ $venta->ventaSunat->nombre_xml }}.zip"
                                                                        target="_blank">📦 Descargar CDR</a></li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <img src="https://illustrations.popsy.co/flat/paper-documents.svg"
                                                style="width: 120px;" class="mb-3">
                                            <p class="text-muted">No se encontraron comprobantes registrados.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="bx bx-list-ul me-2"></i>Contenido del Documento
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive scroll-custom" style="max-height: 250px;">
                            <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Producto</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-end pe-3">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($comprobanteSeleccionado)
                                        @foreach ($detalleSeleccionado as $detalle)
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="fw-bold">
                                                        {{ $detalle->producto->nombre ?? $detalle->descripcion }}</div>
                                                    <small
                                                        class="text-muted">{{ $detalle->producto->codigo_barras ?? '' }}</small>
                                                </td>
                                                <td class="text-center">{{ number_format($detalle->cantidad, 0) }}</td>
                                                <td class="text-end pe-3">S/
                                                    {{ number_format($detalle->precio_total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small">
                                                <i class="bx bx-pointer fs-4 mb-2 d-block"></i>
                                                Selecciona una fila para ver detalles
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 bg-dark text-white">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Resumen del Periodo</h6>
                        <div class="summary-item d-flex justify-content-between">
                            <span class="text-white-50">Total Ventas:</span>
                            <span class="fw-bold">S/ {{ number_format($resumen['total_ventas'], 2) }}</span>
                        </div>
                        <div class="summary-item d-flex justify-content-between">
                            <span class="text-white-50">En Efectivo:</span>
                            <span class="text-success fw-bold">S/
                                {{ number_format($resumen['importe_efectivo'], 2) }}</span>
                        </div>
                        <div class="summary-item d-flex justify-content-between border-0">
                            <span class="text-white-50">Por Cobrar:</span>
                            <span class="text-warning fw-bold">S/
                                {{ number_format($resumen['importe_cuotas'], 2) }}</span>
                        </div>

                        <div class="mt-3 p-3 bg-secondary rounded-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Seleccionados:</small>
                                <span class="fw-bold text-info" id="importe-seleccionados">S/ 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small>Pendiente de éstos:</small>
                                <span class="fw-bold text-danger" id="pendiente-seleccionados">S/ 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- JS dependencies -->

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
        <script>
            // Re-initialize dropdowns to avoid being clipped by the table's scroll container
            try {
                document.querySelectorAll('.dropdown > button[data-bs-toggle="dropdown"]').forEach(function(btn) {
                    // Initialize with custom popper boundary (document.body) to prevent clipping
                    new bootstrap.Dropdown(btn, {
                        popperConfig: function(defaultBsPopperConfig) {
                            defaultBsPopperConfig = defaultBsPopperConfig || {};
                            defaultBsPopperConfig.modifiers = defaultBsPopperConfig.modifiers || [];
                            defaultBsPopperConfig.modifiers.push({
                                name: 'preventOverflow',
                                options: {
                                    boundary: document.body
                                }
                            });
                            return defaultBsPopperConfig;
                        }
                    });
                });
            } catch (err) {
                console.error('Dropdown init error:', err);
            }

            // Ensure clicks inside dropdown toggles/menus don't propagate to the row click
            // Attach direct handlers to stop propagation before the event bubbles to the <tr>
            try {
                document.querySelectorAll('.dropdown > button[data-bs-toggle="dropdown"]').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                    // debug: log clicks on dropdown toggles
                    btn.addEventListener('click', function() {
                        console.debug('dropdown toggle clicked', btn);
                    });
                });
                document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                    menu.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                });
            } catch (err) {
                // Fallback for older browsers or if elements not yet present
                $(document).on('click', '.dropdown > button[data-bs-toggle="dropdown"]', function(e) {
                    e.stopPropagation();
                });
                $(document).on('click', '.dropdown-menu', function(e) {
                    e.stopPropagation();
                });
            }
            $(document).ready(function() {
                let seleccionados = [];

                // Auto-submit filters when user changes inputs (debounced for text input)
                const $formFiltros = $('#form-filtros');
                const debounce = (fn, delay) => {
                    let timer = null;
                    return function(...args) {
                        clearTimeout(timer);
                        timer = setTimeout(() => fn.apply(this, args), delay);
                    };
                };

                // Date inputs: submit on change
                $formFiltros.find('input[name="fecha_desde"], input[name="fecha_hasta"]').on('change', function() {
                    $formFiltros.submit();
                });

                // Tipo documento: submit on change
                $formFiltros.find('select[name="tipo_documento"]').on('change', function() {
                    $formFiltros.submit();
                });

                // Cliente search: debounce input
                $formFiltros.find('input[name="cliente"]').on('input', debounce(function() {
                    $formFiltros.submit();
                }, 600));

                // Manejar click en filas para seleccionar comprobante (sin recargar la página)
                function fetchDetalleVenta(ventaId, markRow = true) {
                    const urlTemplate = '{{ route('comprobantes.detalle', ':id') }}';
                    const url = urlTemplate.replace(':id', ventaId);

                    $.get(url)
                        .done(function(resp) {
                            if (resp.success) {
                                const detalles = resp.detalles || [];
                                const $tbody = $('.col-lg-4 .card-body .table-responsive table tbody');
                                let html = '';

                                if (detalles.length > 0) {
                                    detalles.forEach(function(d) {
                                        const nombre = (d.producto && d.producto.nombre) ? d.producto
                                            .nombre : (d.descripcion || '');
                                        const codigo = (d.producto && d.producto.codigo_barras) ? d.producto
                                            .codigo_barras : '';
                                        const cantidad = parseFloat(d.cantidad || 0).toFixed(0);
                                        const subtotal = parseFloat(d.precio_total || d.subtotal || 0)
                                            .toFixed(2);

                                        html += `<tr>` +
                                            `<td class="ps-3"><div class="fw-bold">${nombre}</div><small class="text-muted">${codigo}</small></td>` +
                                            `<td class="text-center">${cantidad}</td>` +
                                            `<td class="text-end pe-3">S/ ${subtotal}</td>` +
                                            `</tr>`;
                                    });
                                } else {
                                    html =
                                        `<tr><td colspan="3" class="text-center py-4 text-muted small"><i class="bx bx-pointer fs-4 mb-2 d-block"></i> Selecciona una fila para ver detalles</td></tr>`;
                                }

                                $tbody.html(html);

                                if (markRow) {
                                    // marcar la fila correspondiente y sincronizar checkbox
                                    const $row = $(`tr[data-venta-id="${ventaId}"]`);
                                    $('.comprobante-row').removeClass('table-active');
                                    $row.addClass('table-active');
                                    $('.comprobante-check:not(:disabled)').prop('checked', false);
                                    const $chk = $row.find('.comprobante-check');
                                    if (!$chk.is(':disabled')) {
                                        $chk.prop('checked', true);
                                    }
                                    actualizarSeleccionados();
                                }
                            }
                        })
                        .fail(function() {
                            Swal.fire('Error', 'No se pudo obtener el detalle del comprobante', 'error');
                        });
                }

                $('.comprobante-row').click(function(e) {
                    // Ignorar clicks en inputs, botones, iconos y controles internos (incluyendo dropdowns)
                    if (!$(e.target).is('input, button, a, .btn, .btn-send-sunat, svg, path, i') &&
                        !$(e.target).closest('.btn, .btn-send-sunat, .btn-group, .dropdown, .dropdown-menu')
                        .length) {
                        const ventaId = $(this).data('venta-id');
                        fetchDetalleVenta(ventaId, true);
                    }
                });

                // Manejar selección individual de comprobantes
                $('.comprobante-check').change(function() {
                    actualizarSeleccionados();
                });

                // Seleccionar/deseleccionar todos
                $('#check-all').change(function() {
                    $('.comprobante-check:not(:disabled)').prop('checked', this.checked);
                    actualizarSeleccionados();
                });

                // Actualizar contador de seleccionados
                function actualizarSeleccionados() {
                    seleccionados = [];
                    let importeTotal = 0;
                    let pendienteTotal = 0;

                    $('.comprobante-check:checked').each(function() {
                        const row = $(this).closest('tr');
                        const ventaId = $(this).val();
                        const total = parseFloat(row.data('total') || 0);
                        const pagado = parseInt(row.data('pagado') || 0);

                        seleccionados.push(ventaId);
                        importeTotal += isNaN(total) ? 0 : total;
                        pendienteTotal += isNaN(total) ? 0 : (pagado ? 0 : total);
                    });

                    $('#importe-seleccionados').text('S/ ' + importeTotal.toFixed(2));
                    $('#pendiente-seleccionados').text('S/ ' + pendienteTotal.toFixed(2));
                }

                // Ver detalle (botón lupa) — cargar por AJAX
                $('.btn-detalle').click(function(e) {
                    e.stopPropagation();
                    const ventaId = $(this).data('venta-id');
                    fetchDetalleVenta(ventaId, true);
                });

                // Imprimir (A4 o 8cm para tickets)
                $('.btn-imprimir').click(function(e) {
                    e.stopPropagation();
                    const $btn = $(this);
                    const ventaId = $btn.data('venta-id');
                    const tipo = ($btn.data('tipo') || '').toString().toLowerCase();

                    // Genero las URLs con placeholders usando route() y luego reemplazo :id por el id real
                    const urlA4 = '{{ route('pos.pdf', ['id' => ':id', 'format' => 'default']) }}'.replace(
                        ':id', ventaId);
                    const url8cm = '{{ route('pos.pdf', ['id' => ':id', 'format' => '8cm']) }}'.replace(
                        ':id', ventaId);

                    const openUrl = (tipo === 'ticket') ? url8cm : urlA4;
                    window.open(openUrl, '_blank');
                });

                // Seleccionar todo
                $('#btn-seleccionar-todo, #btn-seleccionar-todo-resumen').click(function() {
                    $.post('{{ route('comprobantes.seleccionar-todo') }}', {
                            _token: '{{ csrf_token() }}',
                            ...Object.fromEntries(new FormData(document.getElementById('form-filtros')))
                        })
                        .done(function(response) {
                            if (response.success) {
                                $('.comprobante-check:not(:disabled)').prop('checked', true);
                                actualizarSeleccionados();
                                Swal.fire('Éxito', 'Todos los comprobantes han sido seleccionados',
                                    'success');
                            }
                        })
                        .fail(function() {
                            Swal.fire('Error', 'Error al seleccionar comprobantes', 'error');
                        });
                });

                // Anular seleccionados
                $('#btn-anular').click(function() {
                    if (seleccionados.length === 0) {
                        Swal.fire('Advertencia', 'Selecciona al menos un comprobante', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: '¿Anular comprobantes?',
                        text: `¿Deseas anular ${seleccionados.length} comprobante(s) seleccionado(s)? Esta acción no se puede deshacer.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Sí, anular',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.post('{{ route('comprobantes.cancelar') }}', {
                                    _token: '{{ csrf_token() }}',
                                    ventas_ids: seleccionados
                                })
                                .done(function(response) {
                                    if (response.success) {
                                        // Remover las filas anuladas de la tabla
                                        seleccionados.forEach(function(id) {
                                            $(`tr[data-venta-id="${id}"]`).fadeOut(300,
                                                function() {
                                                    $(this).remove();
                                                });
                                        });

                                        Swal.fire({
                                            title: '¡Anulado!',
                                            text: response.message ||
                                                'Comprobantes anulados correctamente',
                                            icon: 'success',
                                            timer: 2000,
                                            showConfirmButton: false
                                        });

                                        // Limpiar selección
                                        seleccionados = [];
                                        actualizarSeleccionados();
                                    }
                                })
                                .fail(function() {
                                    Swal.fire('Error', 'Error al anular comprobantes', 'error');
                                });
                        }
                    });
                });

            });

            /**
             * NEW: handler for btn-send-sunat
             * - reads data-venta (venta id)
             * - asks for confirmation
             * - sends POST to /ventas/sendDocumentoSunat/{id}
             * - shows progress and result, reloads table on success
             */
            $(document).on('click', '.btn-send-sunat', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $btn = $(this);
                const idVenta = $btn.data('venta');

                if (!idVenta) {
                    Swal.fire({
                        icon: 'error',
                        title: 'ID no encontrado',
                        text: 'No se encontró el ID de la venta para enviar a SUNAT.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Enviar a SUNAT',
                    text: `¿Deseas enviar la venta ${idVenta} a SUNAT ahora?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar'
                }).then((res) => {
                    if (!res.isConfirmed) return;

                    // disable button and show spinner
                    $btn.prop('disabled', true);
                    const originalHtml = $btn.html();
                    $btn.html(
                        `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando`
                    );

                    // AJAX POST to controller endpoint
                    $.ajax({
                        url: `/pos/sendDocumentoSunat/${idVenta}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        timeout: 120000, // 2 min (adjust as needed)
                        success: function(resp) {
                            // resp expected to contain summary structure from controller
                            let processed = 0;
                            let failed = 0;
                            if (resp && resp.summary) {
                                processed = resp.summary.processed_count || 0;
                                failed = resp.summary.failed_count || 0;
                            } else if (resp && resp.processed) {
                                processed = resp.processed.length || 0;
                                failed = resp.failed.length || 0;
                            }

                            let msg =
                                `Envío completado. Procesadas: ${processed}. Fallos: ${failed}.`;
                            Swal.fire({
                                icon: failed > 0 ? 'warning' : 'success',
                                title: 'Resultado SUNAT',
                                html: `<div>${msg}</div>`,
                                width: 600,
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                // reload page to reflect changes (enviado_sunat)
                                location.reload();
                            });
                        },
                        error: function(xhr, status, err) {
                            let text = 'Error al enviar la venta a SUNAT.';
                            if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                                text = xhr.responseJSON.error;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: text
                            });
                        },
                        complete: function() {
                            // restore button state
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

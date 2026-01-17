@extends('layout.app')

@section('title', 'Comprobantes')

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bx bx-receipt me-2"></i>
                        Comprobantes
                    </h2>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" id="btn-seleccionar-todo">
                            <i class="bx bx-check-square me-1"></i>
                            Seleccionar Todo
                        </button>
                        <button class="btn btn-outline-warning" id="btn-cancelar">
                            <i class="bx bx-times me-1"></i>
                            Cancelar
                        </button>
                        <button class="btn btn-outline-info" id="btn-devolver">
                            <i class="bx bx-undo me-1"></i>
                            Devolver
                        </button>
                        <a class="btn btn-secondary" href="{{ route('pos.index') }}">
                            <i class="bx bx-undo me-1"></i>
                            Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('comprobantes.index') }}" id="form-filtros">
                    <div class="row align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small">Desde</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_desde"
                                value="{{ $fechaDesde }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Hasta</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_hasta"
                                value="{{ $fechaHasta }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Cliente</label>
                            <input type="text" class="form-control form-control-sm" name="cliente"
                                value="{{ $cliente }}" placeholder="Nombre o documento">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Documento</label>
                            <select class="form-select form-select-sm" name="tipo_documento">
                                <option value="todos" {{ $tipoDocumento == 'todos' ? 'selected' : '' }}>Todos</option>
                                <option value="ticket" {{ $tipoDocumento == 'ticket' ? 'selected' : '' }}>Ticket</option>
                                <option value="boleta" {{ $tipoDocumento == 'boleta' ? 'selected' : '' }}>Boleta</option>
                                <option value="factura" {{ $tipoDocumento == 'factura' ? 'selected' : '' }}>Factura</option>
                                <option value="nota-venta" {{ $tipoDocumento == 'nota-venta' ? 'selected' : '' }}>Nota Venta
                                </option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-search"></i>
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de comprobantes -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Comprobantes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="check-all">
                                        </th>
                                        <th>Documento</th>
                                        <th>Serie-Nro</th>
                                        <th>Cliente</th>
                                        <th>Fecha Emisión</th>
                                        <th class="text-end">Total Importe</th>
                                        <th class="text-end">Importe Pendiente</th>
                                        <th>Estado Venta</th>
                                        <th>Estado Pago</th>
                                        <th>Sunat</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ventas as $venta)
                                        <tr class="comprobante-row {{ $comprobanteSeleccionado && $comprobanteSeleccionado->id_venta == $venta->id_venta ? 'table-active' : '' }}"
                                            data-venta-id="{{ $venta->id_venta }}" style="cursor: pointer;">
                                            <td>
                                                <input type="checkbox" class="form-check-input comprobante-check"
                                                    value="{{ $venta->id_venta }}">
                                            </td>
                                            <td>
                                                @php
                                                    $tipoDoc = strtolower($venta->tipo_documento ?? 'ticket');
                                                    $badgeClass = match ($tipoDoc) {
                                                        'factura' => 'bg-primary',
                                                        'boleta' => 'bg-success',
                                                        'nota-venta' => 'bg-warning',
                                                        default => 'bg-info',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ strtoupper($venta->tipo_documento ?? 'TICKET') }}
                                                </span>
                                            </td>
                                            <td>{{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted">
                                                        {{ $venta->cliente ? $venta->cliente->tipo_documento : 'PARTICULAR' }}
                                                    </small>
                                                    <br>
                                                    {{ $venta->cliente ? $venta->cliente->nombre : 'CLIENTE PARTICULAR' }}
                                                </div>
                                            </td>
                                            <td>{{ $venta->fecha_emision->format('d/m/Y H:i') }}</td>
                                            <td class="text-end">S/ {{ number_format($venta->total, 2) }}</td>
                                            <td class="text-end text-{{ $venta->pagado ? 'success' : 'danger' }}">
                                                S/ {{ $venta->pagado ? '0.00' : number_format($venta->total, 2) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $venta->estado == 1 ? 'success' : ($venta->estado == 3 ? 'warning' : 'secondary') }}">
                                                   {{ $venta->estado == 1 ? 'Activo' : ($venta->estado == 3 ? 'Devuelto' : 'Anulado') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $venta->pagado ? 'success' : 'warning' }}">
                                                    {{ $venta->pagado ? 'PAGADA' : 'PENDIENTE PAGO' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($venta->enviado_sunat)
                                                    <span class=" badge bg-success">Enviado</span>
                                                @else
                                                    @php $tipo = strtolower($venta->tipo_documento ?? 'ticket'); @endphp
                                                    @if($tipo !== 'ticket')
                                                        <span class="badge bg-warning">Pendiente</span>
                                                        <i data-venta="{{ $venta->id_venta }}"
                                                            class="btn-send-sunat btn-sm btn btn-info bi bi-send-arrow-up-fill"
                                                            title="Enviar a SUNAT"><svg xmlns="http://www.w3.org/2000/svg"
                                                                width="16" height="16" fill="currentColor"
                                                                class="bi bi-send" viewBox="0 0 16 16">
                                                                <path
                                                                    d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z" />
                                                                </svg></i>
                                                    @else
                                                        <span class="badge bg-info">Ticket</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info btn-sm btn-detalle"
                                                        data-venta-id="{{ $venta->id_venta }}" title="Ver detalle">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    @if (isset($venta->ventaSunat->nombre_xml) &&$venta->ventaSunat->nombre_xml)
                                                        <a href="{{ env('APP_URL') }}/storage/xml_sunat/{{ $venta->ventaSunat->nombre_xml }}.xml"
                                                            target="_blank" class="btn btn-sm btn-info"
                                                            alt="ver archivo XML" title="ver archivo XML"> <i
                                                                class=""></i>XML</a>
                                                    @endif

                                                    @if ($venta->enviado_sunat)
                                                        <a href="{{ env('APP_URL') }}/storage/cdrs/R-{{ $venta->ventaSunat->nombre_xml }}.zip"
                                                            target="_blank" class="btn btn-sm btn-success" alt="Ver CDR"
                                                            title="Ver CDR"> <i class="fa fa-file-zip"></i>CDR</a>
                                                    @endif

                                                    <button class="btn btn-outline-secondary btn-sm btn-imprimir"
                                                        data-venta-id="{{ $venta->id_venta }}" data-tipo="{{ strtolower($venta->tipo_documento ?? 'ticket') }}" title="Imprimir">
                                                        <i class="bx bx-printer"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-receipt fa-2x mb-2"></i>
                                                    <p>No hay comprobantes en el rango de fechas seleccionado</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ $ventas->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Detalle del comprobante seleccionado -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            @if ($comprobanteSeleccionado)
                                Detalle {{ strtoupper($comprobanteSeleccionado->tipo_documento ?? 'TICKET') }}
                                {{ $comprobanteSeleccionado->serie }}-{{ str_pad($comprobanteSeleccionado->numero, 8, '0', STR_PAD_LEFT) }}
                            @else
                                Selecciona un comprobante
                            @endif
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Detalle</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Importe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($comprobanteSeleccionado)
                                        @foreach ($detalleSeleccionado as $detalle)
                                            <tr>
                                                <td>{{ $detalle->producto->codigo_barras ?? '' }}</td>
                                                <td>{{ $detalle->producto->nombre ?? $detalle->descripcion }}</td>
                                                <td>
                                                    <small class="text-muted">
                                                        Peso(KGM): {{ number_format($detalle->cantidad, 2) }}
                                                        PV/U: {{ number_format($detalle->precio_unitario, 2) }}
                                                        Dcto: 0%
                                                        IGV: {{ number_format($detalle->igv, 2) }}
                                                    </small>
                                                </td>
                                                <td class="text-center">{{ $detalle->cantidad }}</td>
                                                <td class="text-end">{{ number_format($detalle->precio_unitario, 2) }}
                                                </td>
                                                <td class="text-end">{{ number_format($detalle->precio_total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center py-3">
                                                <small class="text-muted">Selecciona un comprobante para ver su
                                                    detalle</small>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Resumen Comprobantes</h6>
                        <div class="mt-2">
                            <small class="text-muted">Acciones Devolución:</small>
                            <div class="btn-group btn-group-sm ms-2">
                                <button class="btn btn-outline-primary btn-sm"
                                    id="btn-seleccionar-todo-resumen">Seleccionar Todo</button>
                                <button class="btn btn-outline-danger btn-sm" id="btn-cancelar-resumen">Cancelar</button>
                                <button class="btn btn-outline-warning btn-sm" id="btn-devolver-resumen">Devolver</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="d-flex justify-content-between">
                                    <span>Facturas:</span>
                                    <strong class="text-primary">{{ $resumen['facturas'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex justify-content-between">
                                    <span>Boletas:</span>
                                    <strong class="text-success">{{ $resumen['boletas'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex justify-content-between">
                                    <span>Tickets:</span>
                                    <strong class="text-info">{{ $resumen['tickets'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex justify-content-between">
                                    <span>Nota Venta:</span>
                                    <strong class="text-warning">{{ $resumen['nota_venta'] }}</strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span>Total:</span>
                                    <strong class="text-dark">S/ {{ number_format($resumen['total_ventas'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span>Importe Efectivo:</span>
                                    <strong class="text-success">S/
                                        {{ number_format($resumen['importe_efectivo'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span>Importe Cuotas:</span>
                                    <strong class="text-warning">S/
                                        {{ number_format($resumen['importe_cuotas'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span>Importe Seleccionados:</span>
                                    <strong class="text-primary" id="importe-seleccionados">S/ 0.00</strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span>Pendiente Seleccionados:</span>
                                    <strong class="text-danger" id="pendiente-seleccionados">S/ 0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .comprobante-row:hover {
                background-color: #f8f9fa;
            }

            .table-active {
                background-color: #e3f2fd !important;
            }
        </style>
    @endpush
    <!-- JS dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            let seleccionados = [];

            // Manejar click en filas para seleccionar comprobante
            $('.comprobante-row').click(function(e) {
                // Mejorar la detección de elementos que no deben activar la selección de fila
                if (!$(e.target).is('input, button, a, .btn, .btn-send-sunat, svg, path') && 
                    !$(e.target).closest('.btn, .btn-send-sunat, .btn-group').length) {
                    const ventaId = $(this).data('venta-id');
                    window.location.href = '{{ route('comprobantes.index') }}?venta_id=' + ventaId + '&' +
                        $('#form-filtros').serialize();
                }
            });

            // Manejar selección individual de comprobantes
            $('.comprobante-check').change(function() {
                actualizarSeleccionados();
            });

            // Seleccionar/deseleccionar todos
            $('#check-all').change(function() {
                $('.comprobante-check').prop('checked', this.checked);
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
                    const importe = parseFloat(row.find('td:eq(5)').text().replace('S/ ', '').replace(',',
                        ''));
                    const pendiente = parseFloat(row.find('td:eq(6)').text().replace('S/ ', '').replace(',',
                        ''));

                    seleccionados.push(ventaId);
                    importeTotal += importe;
                    pendienteTotal += pendiente;
                });

                $('#importe-seleccionados').text('S/ ' + importeTotal.toFixed(2));
                $('#pendiente-seleccionados').text('S/ ' + pendienteTotal.toFixed(2));
            }

            // Ver detalle
            $('.btn-detalle').click(function(e) {
                e.stopPropagation();
                const ventaId = $(this).data('venta-id');
                window.location.href = '{{ route('comprobantes.index') }}?venta_id=' + ventaId + '&' + $(
                    '#form-filtros').serialize();
            });

            // Imprimir (A4 o 8cm para tickets)
            $('.btn-imprimir').click(function(e) {
                e.stopPropagation();
                const $btn = $(this);
                const ventaId = $btn.data('venta-id');
                const tipo = ($btn.data('tipo') || '').toString().toLowerCase();
                const urlA4 = '{{ route('pos.pdfVenta', ':id') }}'.replace(':id', ventaId);
                const url8cm = '{{ route('pos.pdfVenta8cm', ':id') }}'.replace(':id', ventaId);
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
                            $('.comprobante-check').prop('checked', true);
                            actualizarSeleccionados();
                            Swal.fire('Éxito', 'Todos los comprobantes han sido seleccionados',
                                'success');
                        }
                    })
                    .fail(function() {
                        Swal.fire('Error', 'Error al seleccionar comprobantes', 'error');
                    });
            });

            // Cancelar seleccionados
            $('#btn-cancelar, #btn-cancelar-resumen').click(function() {
                if (seleccionados.length === 0) {
                    Swal.fire('Advertencia', 'Selecciona al menos un comprobante', 'warning');
                    return;
                }

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Deseas cancelar los comprobantes seleccionados?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ route('comprobantes.cancelar') }}', {
                                _token: '{{ csrf_token() }}',
                                ventas_ids: seleccionados
                            })
                            .done(function(response) {
                                if (response.success) {
                                    Swal.fire('Éxito', response.message, 'success');
                                    location.reload();
                                }
                            })
                            .fail(function() {
                                Swal.fire('Error', 'Error al cancelar comprobantes', 'error');
                            });
                    }
                });
            });

            // Devolver seleccionados
            $('#btn-devolver, #btn-devolver-resumen').click(function() {
                if (seleccionados.length === 0) {
                    Swal.fire('Advertencia', 'Selecciona al menos un comprobante', 'warning');
                    return;
                }

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Deseas procesar las devoluciones de los comprobantes seleccionados?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, devolver',
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ route('comprobantes.devolver') }}', {
                                _token: '{{ csrf_token() }}',
                                ventas_ids: seleccionados
                            })
                            .done(function(response) {
                                if (response.success) {
                                    Swal.fire('Éxito', response.message, 'success');
                                    location.reload();
                                }
                            })
                            .fail(function() {
                                Swal.fire('Error', 'Error al procesar devoluciones', 'error');
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

                        let msg = `Envío completado. Procesadas: ${processed}. Fallos: ${failed}.`;
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
@endsection

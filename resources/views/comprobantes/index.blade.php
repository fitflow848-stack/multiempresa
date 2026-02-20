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

    <div class="container-fluid py-2">
        <div class="card mb-2 shadow-sm">
            <div class="card-body p-2">
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
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Tipo Documento</label>
                        <select class="form-select border-0 bg-light" name="tipo_documento">
                            <option value="todos" {{ $tipoDocumento == 'todos' ? 'selected' : '' }}>📄 Todos los documentos
                            </option>
                            <option value="ticket" {{ $tipoDocumento == 'ticket' ? 'selected' : '' }}>🎫 Ticket</option>
                            <option value="boleta" {{ $tipoDocumento == 'boleta' ? 'selected' : '' }}>🧾 Boleta</option>
                            <option value="factura" {{ $tipoDocumento == 'factura' ? 'selected' : '' }}>🏢 Factura</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end d-flex gap-2">
                        <div class="btn-group shadow-sm w-100">
                            <button type="button" class="btn btn-white border px-2 py-2 small" id="btn-seleccionar-todo"
                                style="font-size: 0.8rem;">
                                <i class="bx bx-check-double me-1 text-primary"></i> Todo
                            </button>
                            <button type="button" class="btn btn-danger border px-2 py-2 small" id="btn-anular"
                                style="font-size: 0.8rem;">
                                <i class="bx bx-x-circle me-1"></i> Anular
                            </button>
                        </div>
                        <a class="btn btn-dark px-3 py-2 shadow-sm small d-flex align-items-center justify-content-center"
                            href="{{ route('pos.index') }}" style="font-size: 0.8rem;">
                            <i class="bx bx-plus-circle me-1"></i> volver
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-2">
                <div class="table-container shadow-sm border rounded">
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-hover mb-0 align-middle table-sm border-0" style="font-size: 0.8rem;">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-3" style="width: 40px;"><input type="checkbox" class="form-check-input"
                                            id="check-all"></th>
                                    <th class="text-center" style="width: 30px;">#</th>
                                    <th>Documento</th>
                                    <th>Serie-Nro</th>
                                    <th>Cliente</th>
                                    <th>Fecha Emisión</th>
                                    <th class="text-end">Dscto Global</th>
                                    <th class="text-end">Total Importe</th>
                                    <th class="text-end">Importe Pendiente</th>
                                    <th class="text-center">Estado Pago</th>
                                    <th class="text-end">Deuda Total</th>
                                    <th class="text-center">SUNAT</th>
                                    <th class="text-start">Obs</th>
                                    <th class="text-end pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventas as $index => $venta)
                                    @php
                                        $tipoDoc = strtolower($venta->tipo_documento ?? 'ticket');
                                        $badgeClass = match ($tipoDoc) {
                                            'factura' => 'bg-primary',
                                            'boleta' => 'bg-success',
                                            'nota-venta' => 'bg-warning text-dark',
                                            default => 'bg-info text-white',
                                        };
                                        $isCancelado = isset($venta->estado) && $venta->estado == 0;

                                        $pendiente = 0;
                                        $deudaTotal = 0;
                                        if ($venta->pagado) {
                                            $pendiente = 0;
                                            $deudaTotal = $venta->deuda ? $venta->deuda->monto_total : 0;
                                            $estadoPago = 'PAGADA';
                                        } else {
                                            $pendiente = $venta->deuda ? $venta->deuda->monto_deuda : $venta->total;
                                            $deudaTotal = $venta->deuda ? $venta->deuda->monto_total : $venta->total;
                                            $estadoPago = 'PENDIENTE PAGO';
                                        }
                                        if ($isCancelado) {
                                            $pendiente = 0;
                                            $deudaTotal = 0;
                                            $estadoPago = 'CANCELADO';
                                        }
                                    @endphp
                                    <tr class="comprobante-row {{ $comprobanteSeleccionado && $comprobanteSeleccionado->id_venta == $venta->id_venta ? 'table-active' : '' }} {{ $isCancelado ? 'comprobante-cancelado' : '' }}"
                                        data-venta-id="{{ $venta->id_venta }}" data-total="{{ $venta->total }}"
                                        data-pagado="{{ $venta->pagado ? 1 : 0 }}"
                                        data-estado="{{ $venta->estado ?? 1 }}">
                                        <td class="ps-3">
                                            <input type="checkbox" class="form-check-input comprobante-check"
                                                value="{{ $venta->id_venta }}" {{ $isCancelado ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-center text-muted">
                                            {{ $ventas->firstItem() + $index }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-pill {{ $badgeClass }}">{{ strtoupper($venta->tipo_documento ?? 'TIC') }}</span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            {{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td>
                                            <div class="text-dark fw-bold">
                                                {{ $venta->cliente ? $venta->cliente->nombre : 'CLIENTE PARTICULAR' }}
                                            </div>
                                        </td>
                                        <td class="text-muted">
                                            {{ $venta->fecha_emision->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="text-end text-muted">
                                            S/ {{ number_format($venta->descuento_monto ?? 0, 2) }}
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            S/ {{ number_format($venta->total, 2) }}
                                        </td>
                                        <td class="text-end text-danger fw-bold">
                                            S/ {{ number_format($pendiente, 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if ($isCancelado)
                                                <span class="badge bg-danger">CANCELADA</span>
                                            @elseif(isset($venta->estado) && $venta->estado == 3)
                                                <span class="badge bg-warning text-dark">DEVUELTO</span>
                                            @else
                                                <small class="text-{{ $venta->pagado ? 'success' : 'danger' }} fw-bold"
                                                    style="font-size: 0.70rem;">
                                                    {{ $estadoPago }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-end text-muted">
                                            S/ {{ number_format($deudaTotal, 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if ($venta->enviado_sunat)
                                                <i class="bx bxs-check-circle text-success fs-5"
                                                    title="Enviado correctamente"></i>
                                            @elseif(strtolower($venta->tipo_documento) !== 'ticket')
                                                <button class="btn btn-sm btn-light text-info btn-send-sunat p-1"
                                                    data-venta="{{ $venta->id_venta }}">
                                                    <i class="bx bx-send"></i>
                                                </button>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-start small text-truncate" style="max-width: 100px;"
                                            title="{{ $venta->observacion }}">
                                            {{ $venta->observacion }}
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
                                        <td colspan="12" class="text-center py-5">
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

            <!-- Detalle Panel (Tab Style) -->
            <div class="col-12 mb-2">
                <ul class="nav nav-tabs tab-custom border-bottom" id="detalleTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active bg-white text-primary border-bottom-0 pb-2 px-4 shadow-sm"
                            style="border-radius: 8px 8px 0 0;" id="detalle-tab" data-bs-toggle="tab"
                            data-bs-target="#detalle-docs" type="button" role="tab">
                            <i class="bx bx-list-ul me-1"></i> Detalle del Documento
                        </button>
                    </li>
                </ul>
                <div class="tab-content border border-top-0 bg-white p-0 shadow-sm" style="border-radius: 0 8px 8px 8px;"
                    id="detalleTabsContent">
                    <div class="tab-pane fade show active" id="detalle-docs" role="tabpanel">
                        <div class="table-responsive" style="max-height: 160px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 0.70rem;">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="ps-2 text-center" style="width: 30px;">#</th>
                                        <th class="text-center">CR</th>
                                        <th class="text-center">CB</th>
                                        <th class="ps-2">Producto</th>
                                        <th class="text-center">Detalle</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-end">Peso(KGM)</th>
                                        <th class="text-end">PV/U</th>
                                        <th class="text-end">Dscto</th>
                                        <th class="text-end">IGV</th>
                                        <th class="text-end">ICBPER</th>
                                        <th class="text-end pe-3">Importe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($comprobanteSeleccionado)
                                        @foreach ($detalleSeleccionado as $index => $detalle)
                                            @php
                                                $peso = ($detalle->producto->peso ?? 0) * $detalle->cantidad;
                                                $descuento =
                                                    $detalle->precio_unitario * $detalle->cantidad - $detalle->importe;
                                                $lote = $detalle->almacenIngresoDetalle->lote ?? '';
                                                $vencimiento = $detalle->almacenIngresoDetalle->fecha_vencimiento
                                                    ? 'fv.' . $detalle->almacenIngresoDetalle->fecha_vencimiento
                                                    : '';
                                                $detalleTxt = $lote ? $lote . ' ' . $vencimiento : $vencimiento;
                                            @endphp
                                            <tr>
                                                <td class="ps-2 text-center text-muted">{{ $index + 1 }}</td>
                                                <td class="text-center">{{ $detalle->servicio_id }}</td>
                                                <td class="text-center">{{ $detalle->producto->codigo_barras ?? '' }}</td>
                                                <td class="ps-2">
                                                    <div class="fw-bold">
                                                        {{ $detalle->producto->nombre ?? $detalle->nombre_servicio }}</div>
                                                </td>
                                                <td class="text-center small text-muted">{{ $detalleTxt }}</td>
                                                <td class="text-center">{{ number_format($detalle->cantidad, 0) }}
                                                    {{ $detalle->producto->unidadMedida->nombre ?? '' }}</td>
                                                <td class="text-end">{{ number_format($peso, 3) }}</td>
                                                <td class="text-end">{{ number_format($detalle->precio_unitario, 2) }}
                                                </td>
                                                <td class="text-end text-danger">
                                                    {{ number_format(max(0, $descuento), 2) }}</td>
                                                <td class="text-end">{{ number_format($detalle->igv ?? 0, 3) }}</td>
                                                <td class="text-end">0.00</td>
                                                <td class="text-end pe-3 fw-bold">S/
                                                    {{ number_format($detalle->importe ?? ($detalle->precio_total ?? 0), 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="15" class="text-center py-4 text-muted small">
                                                <i class="bx bx-pointer fs-4 mb-2 d-block"></i> Selecciona una fila para
                                                ver detalles
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Resumen -->
            <div class="col-12">
                <div class="card shadow-sm border-0"
                    style="background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);">
                    <div class="card-body py-1 px-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                            <div class="text-primary fw-bold text-uppercase" style="font-size: 0.75rem;">
                                <i class="bx bx-bar-chart-alt-2"></i> Resumen Comprobantes
                            </div>
                            <div class="d-flex flex-wrap gap-3" style="font-size: 0.75rem;">
                                <div><span class="text-muted">Ventas:</span> <span
                                        class="fw-bold text-dark">{{ $ventas->total() }}</span></div>
                                <div><span class="text-muted">Facturas:</span> <span
                                        class="fw-bold text-dark">{{ $resumen['facturas'] }}</span></div>
                                <div><span class="text-muted">Boletas:</span> <span
                                        class="fw-bold text-dark">{{ $resumen['boletas'] }}</span></div>
                                <div><span class="text-muted">Tickets:</span> <span
                                        class="fw-bold text-dark">{{ $resumen['tickets'] }}</span></div>
                                <div><span class="text-muted">Nota Venta:</span> <span
                                        class="fw-bold text-dark">{{ $resumen['nota_venta'] }}</span></div>
                                <div class="border-start ps-3"><span class="text-muted">Importe:</span> <span
                                        class="fw-bold text-primary">S/
                                        {{ number_format($resumen['total_ventas'], 2) }}</span></div>
                                <div><span class="text-muted">Pendiente:</span> <span class="fw-bold text-danger">S/
                                        {{ number_format($resumen['importe_cuotas'], 2) }}</span></div>
                                <div><span class="text-muted">Total:</span> <span class="fw-bold text-success">S/
                                        {{ number_format($resumen['importe_efectivo'], 2) }}</span></div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-4 align-items-center border-top pt-1"
                            style="font-size: 0.75rem;">
                            <div><span class="text-muted">Importe Efectivo:</span> <span
                                    class="fw-bold text-success fs-6 ms-1">S/
                                    {{ number_format($resumen['importe_efectivo'], 2) }}</span></div>
                            <div><span class="text-muted">Importe Cuentas:</span> <span
                                    class="fw-bold text-warning fs-6 ms-1">S/
                                    {{ number_format($resumen['importe_cuotas'], 2) }}</span></div>

                            <div class="border-start ps-4 ms-auto d-flex gap-4">
                                <div><span class="text-muted">Importe Seleccionado:</span> <span
                                        class="fw-bold text-info fs-6 ms-1" id="importe-seleccionados">S/ 0.00</span>
                                </div>
                                <div><span class="text-muted">Pendiente Seleccionado:</span> <span
                                        class="fw-bold text-warning border-bottom border-warning fs-6 ms-1"
                                        id="pendiente-seleccionados">S/ 0.00</span></div>
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
                                const $tbody = $('#detalle-docs table tbody');
                                let html = '';

                                if (detalles.length > 0) {
                                    detalles.forEach(function(d, index) {
                                        const nombre = (d.producto && d.producto.nombre) ? d.producto
                                            .nombre : (d.nombre_servicio || '');
                                        const cr = d.servicio_id || '';
                                        const cb = (d.producto && d.producto.codigo_barras) ? d.producto
                                            .codigo_barras : '';

                                        const batch = d.almacen_ingreso_detalle || {};
                                        const lote = batch.lote || '';
                                        const fv = batch.fecha_vencimiento ? 'fv.' + batch
                                            .fecha_vencimiento : '';
                                        const detailTxt = lote ? lote + ' ' + fv : fv;

                                        const cantidad = parseFloat(d.cantidad || 0);
                                        const peso = (parseFloat(d.producto ? d.producto.peso : 0) *
                                            cantidad).toFixed(3);
                                        const pvu = parseFloat(d.precio_unitario || 0);
                                        const importe = parseFloat(d.importe || d.precio_total || 0);
                                        const dscto = Math.max(0, (pvu * cantidad) - importe).toFixed(2);
                                        const igv = parseFloat(d.igv || 0).toFixed(3);

                                        html += `<tr>` +
                                            `<td class="ps-2 text-center text-muted">${index + 1}</td>` +
                                            `<td class="text-center">${cr}</td>` +
                                            `<td class="text-center">${cb}</td>` +
                                            `<td class="ps-2"><div class="fw-bold">${nombre}</div></td>` +
                                            `<td class="text-center small text-muted">${detailTxt}</td>` +
                                            `<td class="text-center">${cantidad.toFixed(0)} ${(d.producto && d.producto.unidad_medida) ? d.producto.unidad_medida.nombre : ''}</td>` +
                                            `<td class="text-end">${peso}</td>` +
                                            `<td class="text-end">${pvu.toFixed(2)}</td>` +
                                            `<td class="text-end text-danger">${dscto}</td>` +
                                            `<td class="text-end">${igv}</td>` +
                                            `<td class="text-end">0.00</td>` +
                                            `<td class="text-end pe-3 fw-bold">S/ ${importe.toFixed(2)}</td>` +
                                            `</tr>`;
                                    });
                                } else {
                                    html =
                                        `<tr><td colspan="15" class="text-center py-4 text-muted small"><i class="bx bx-pointer fs-4 mb-2 d-block"></i> Selecciona una fila para ver detalles</td></tr>`;
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
                    const isChecked = $(this).is(':checked');
                    const $row = $(this).closest('tr');

                    if (!isChecked && $row.hasClass('table-active')) {
                        $row.removeClass('table-active');
                        $('#detalle-docs table tbody').html(
                            '<tr><td colspan="15" class="text-center py-4 text-muted small"><i class="bx bx-pointer fs-4 mb-2 d-block"></i> Selecciona una fila para ver detalles</td></tr>'
                            );
                    } else if (isChecked) {
                        const ventaId = $row.data('venta-id');
                        // Cargar detalle pero NO limpiar otros checks si se hace manualmente
                        fetchDetalleVenta(ventaId, false);
                        $('.comprobante-row').removeClass('table-active');
                        $row.addClass('table-active');
                    }

                    actualizarSeleccionados();
                });

                // Seleccionar/deseleccionar todos
                $('#check-all').change(function() {
                    const isChecked = this.checked;
                    $('.comprobante-check:not(:disabled)').prop('checked', isChecked);

                    if (!isChecked) {
                        $('.comprobante-row').removeClass('table-active');
                        $('#detalle-docs table tbody').html(
                            '<tr><td colspan="15" class="text-center py-4 text-muted small"><i class="bx bx-pointer fs-4 mb-2 d-block"></i> Selecciona una fila para ver detalles</td></tr>'
                            );
                    }

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

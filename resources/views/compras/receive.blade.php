@extends('layout.app')

@section('title', 'Recepción de Compra')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Recepción - Ticket #{{ $compra->id }}</h1>
            <small class="text-muted">Registra la recepción de los productos del ticket</small>
        </div>
        <div>
            <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-outline-secondary">Ver Ticket</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <strong>Proveedor</strong>
                            <div>{{ $compra->proveedor_nombre ?? $compra->proveedor_id ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Fecha Emisión</strong>
                            <div>{{ $compra->fecha_emision ? \Carbon\Carbon::parse($compra->fecha_emision)->format('d/m/Y') : '—' }}</div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <strong>Total</strong>
                            <div class="fw-bold">S/ {{ number_format($compra->total_pagar ?? 0, 2) }}</div>
                        </div>
                    </div>

                    <h5 class="mt-3">Líneas</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Costo</th>
                                    <th class="text-end">Importe</th>
                                    <th class="text-center">Precios</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($compra->lineas as $i => $ln)
                                    <tr data-linea-id="{{ $ln->id }}">
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $ln->descripcion }}</td>
                                        <td class="text-end">{{ $ln->cantidad }}</td>
                                        <td class="text-end linea-costo" data-cantidad="{{ $ln->cantidad }}">{{ number_format($ln->costo, 2) }}</td>
                                        <td class="text-end fw-semibold linea-importe">{{ number_format(($ln->costo ?? 0) * ($ln->cantidad ?? 0), 2) }}</td>
                                        <td class="text-center">
                                            @if ($ln->product_id)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-info btn-ver-precios"
                                                        data-producto-id="{{ $ln->product_id }}"
                                                        data-producto-nombre="{{ $ln->descripcion }}"
                                                        title="Ver precios">
                                                    <i class="bx bx-dollar"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end" id="lineas-total-importe">
                                        {{ number_format($compra->lineas->sum(fn($l) => ($l->costo ?? 0) * ($l->cantidad ?? 0)), 2) }}
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">Registrar Recepción</div>
                <div class="card-body">
                    <form action="{{ route('compras.receive.store', $compra->id) }}" method="POST">
                        @csrf

                        {{-- Overrides de precios editados en el modal "Ver precios".
                             Solo se aplican en el servidor si este formulario se envía. --}}
                        @foreach ($compra->lineas as $ln)
                            @if ($ln->product_id)
                                <input type="hidden" class="ov-pvp" data-producto-id="{{ $ln->product_id }}" name="precios_override[{{ $ln->product_id }}][pvp]" value="">
                                <input type="hidden" class="ov-pvp-dto" data-producto-id="{{ $ln->product_id }}" name="precios_override[{{ $ln->product_id }}][pvp_dto]" value="">
                                <input type="hidden" class="ov-pvc" data-producto-id="{{ $ln->product_id }}" name="precios_override[{{ $ln->product_id }}][pvc]" value="">
                                <input type="hidden" class="ov-pvc-dto" data-producto-id="{{ $ln->product_id }}" name="precios_override[{{ $ln->product_id }}][pvc_dto]" value="">
                                <input type="hidden" class="ov-pv-docena" data-producto-id="{{ $ln->product_id }}" name="precios_override[{{ $ln->product_id }}][pv_docena]" value="">
                                <input type="hidden" class="ov-costo" data-producto-id="{{ $ln->product_id }}" name="precios_override[{{ $ln->product_id }}][costo]" value="">
                            @endif
                        @endforeach

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3" placeholder="Opcional...">{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="alert alert-info small mb-3">
                            <i class="bx bx-info-circle me-1"></i>
                            Al confirmar se ingresarán los productos al almacén y se actualizará el stock.
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-success">Recibir e Ingresar a {{ $sucursal->nombre ?? 'Almacén' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: precios del producto en la sucursal de destino -->
    <div class="modal fade" id="preciosProductoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Precios del producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <strong id="precios-producto-nombre"></strong>
                        <div class="text-muted small">
                            Últimos precios registrados en <span id="precios-sucursal-nombre" class="fw-semibold"></span>
                        </div>
                    </div>
                    <div id="precios-loading" class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-info" role="status"></div>
                    </div>
                    <div id="precios-contenido" class="row g-2" style="display:none;">
                        <div class="col-6">
                            <label class="form-label text-primary fw-bold small mb-0">PVP (Soles)</label>
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end" id="precios-pvp">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-warning fw-bold small mb-0">PVP Dcto.</label>
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end" id="precios-pvp-dto">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-info fw-bold small mb-0">PVC (Corp.)</label>
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end" id="precios-pvc">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-primary fw-bold small mb-0">PVC Dcto.</label>
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end" id="precios-pvc-dto">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-success fw-bold small mb-0">PV Docena</label>
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end" id="precios-pv-docena">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small mb-0">Costo</label>
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end" id="precios-costo">
                        </div>
                        <div class="col-12">
                            <small class="text-muted" id="precios-origen"></small>
                        </div>
                        <div class="col-12">
                            <small class="text-warning">
                                <i class="bx bx-info-circle"></i>
                                Estos cambios recién se guardan al confirmar "Recibir e Ingresar a {{ $sucursal->nombre ?? 'Almacén' }}".
                            </small>
                        </div>
                    </div>
                    <div id="precios-error" class="text-danger small" style="display:none;">
                        No se pudieron cargar los precios de este producto.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-guardar-precios">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function($) {
        'use strict';
        $(function() {
            const preciosUrlBase = '{{ url('compras/' . $compra->id . '/recibir/precios') }}';
            const $modal = $('#preciosProductoModal');

            function recalcularTotalLineas() {
                let total = 0;
                $('.linea-importe').each(function() {
                    total += parseFloat($(this).text()) || 0;
                });
                $('#lineas-total-importe').text(total.toFixed(2));
            }

            function ovInputs(productoId) {
                return {
                    pvp: $(`.ov-pvp[data-producto-id="${productoId}"]`),
                    pvpDto: $(`.ov-pvp-dto[data-producto-id="${productoId}"]`),
                    pvc: $(`.ov-pvc[data-producto-id="${productoId}"]`),
                    pvcDto: $(`.ov-pvc-dto[data-producto-id="${productoId}"]`),
                    pvDocena: $(`.ov-pv-docena[data-producto-id="${productoId}"]`),
                    costo: $(`.ov-costo[data-producto-id="${productoId}"]`),
                };
            }

            $(document).on('click', '.btn-ver-precios', function() {
                const $btn = $(this);
                const productoId = $btn.data('producto-id');
                const productoNombre = $btn.data('producto-nombre') || '';

                $modal.data('producto-id', productoId);
                $modal.data('btn', $btn);

                $('#precios-producto-nombre').text(productoNombre);
                $('#precios-sucursal-nombre').text('...');
                $('#precios-loading').show();
                $('#precios-contenido').hide();
                $('#precios-error').hide();

                const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
                modalInstance.show();

                // Si ya se editó este producto en esta sesión (aún no enviado el
                // formulario), reabrir el modal con lo editado, sin volver a
                // consultar el servidor.
                const ov = ovInputs(productoId);
                if (ov.pvp.val() !== '') {
                    $('#precios-sucursal-nombre').text('{{ $sucursal->nombre ?? "—" }}');
                    $('#precios-pvp').val(ov.pvp.val());
                    $('#precios-pvp-dto').val(ov.pvpDto.val());
                    $('#precios-pvc').val(ov.pvc.val());
                    $('#precios-pvc-dto').val(ov.pvcDto.val());
                    $('#precios-pv-docena').val(ov.pvDocena.val());
                    $('#precios-costo').val(ov.costo.val());
                    $('#precios-origen').text('Editado en esta sesión (pendiente de confirmar recepción).');
                    $('#precios-loading').hide();
                    $('#precios-contenido').show();
                    return;
                }

                $.get(`${preciosUrlBase}/${productoId}`)
                    .done(function(data) {
                        $('#precios-sucursal-nombre').text(data.sucursal || '—');
                        $('#precios-pvp').val(Number(data.pvp || 0).toFixed(2));
                        $('#precios-pvp-dto').val(Number(data.pvp_dto || 0).toFixed(2));
                        $('#precios-pvc').val(Number(data.pvc || 0).toFixed(2));
                        $('#precios-pvc-dto').val(Number(data.pvc_dto || 0).toFixed(2));
                        $('#precios-pv-docena').val(Number(data.pv_docena || 0).toFixed(2));
                        $('#precios-costo').val(Number(data.costo || 0).toFixed(2));
                        $('#precios-origen').text(data.origen === 'almacen'
                            ? ('Ingreso más reciente en este almacén' + (data.fecha ? (' (' + data.fecha + ')') : ''))
                            : 'Producto nuevo, sin ingresos previos en este almacén — se muestra el precio base configurado en el producto.');

                        $('#precios-loading').hide();
                        $('#precios-contenido').show();
                    })
                    .fail(function() {
                        $('#precios-loading').hide();
                        $('#precios-error').show();
                    });
            });

            // "Guardar" solo deja el valor listo en el formulario de recepción;
            // no persiste nada hasta que se confirme "Recibir e Ingresar a...".
            $('#btn-guardar-precios').on('click', function() {
                const productoId = $modal.data('producto-id');
                const $btn = $modal.data('btn');
                if (!productoId) return;

                const pvp = Number($('#precios-pvp').val()) || 0;
                const pvpDto = Number($('#precios-pvp-dto').val()) || 0;
                const pvc = Number($('#precios-pvc').val()) || 0;
                const pvcDto = Number($('#precios-pvc-dto').val()) || 0;
                const pvDocena = Number($('#precios-pv-docena').val()) || 0;
                const costo = Number($('#precios-costo').val()) || 0;

                const ov = ovInputs(productoId);
                ov.pvp.val(pvp.toFixed(2));
                ov.pvpDto.val(pvpDto.toFixed(2));
                ov.pvc.val(pvc.toFixed(2));
                ov.pvcDto.val(pvcDto.toFixed(2));
                ov.pvDocena.val(pvDocena.toFixed(2));
                ov.costo.val(costo.toFixed(2));

                if ($btn && $btn.length) {
                    const $row = $btn.closest('tr');
                    $row.find('.linea-costo').text(costo.toFixed(2));
                    const cantidad = parseFloat($row.find('.linea-costo').data('cantidad')) || 0;
                    $row.find('.linea-importe').text((costo * cantidad).toFixed(2));
                    recalcularTotalLineas();
                }

                const modalInstance = bootstrap.Modal.getInstance($modal[0]);
                if (modalInstance) modalInstance.hide();

                if (window.Swal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Precios listos',
                        text: 'Se guardarán al confirmar la recepción.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });
    })(jQuery);
</script>
@endpush
@endsection

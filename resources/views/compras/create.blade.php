@extends('layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <div class="container py-4">
        <h1 class="h5 mb-4">Registrar Compras</h1>

        <form method="POST" action="{{route('compras.store')}}" id="compra-form">
            @csrf

            <div class="card mb-4">
                <div class="card-header">Ticket</div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-6">
                            <label for="proveedor_select" class="form-label small text-muted">Proveedor</label>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#proveedorModal" aria-label="Alta proveedor">+</button>

                                <select id="proveedor_select" name="proveedor_id" class="form-select form-select-sm"
                                    style="width:100%"></select>
                            </div>
                        </div>

                        <div class="col-6 col-lg-2">
                            <label for="presupuesto" class="form-label small text-muted">Presupuesto</label>
                            <select id="presupuesto" name="presupuesto" class="form-select form-select-sm">
                                <option>Compra</option>
                                <option>Servicio</option>
                            </select>
                        </div>

                        <div class="col-6 col-lg-2">
                            <label for="tipo" class="form-label small text-muted">Tipo</label>
                            <select id="tipo" name="tipo" class="form-select form-select-sm">
                                <option>Ticket</option>
                                <option>Factura</option>
                                <option>Boleta</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-12 col-md-6">
                            <label for="fecha_emision" class="form-label small text-muted">Fecha Emisión</label>
                            <input id="fecha_emision" name="fecha_emision" type="date"
                                class="form-control form-control-sm" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="fecha_pago" class="form-label small text-muted">Fecha Pago</label>
                            <input id="fecha_pago" name="fecha_pago" type="date" class="form-control form-control-sm" />
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3 align-items-center mt-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="moneda" id="moneda_sol" value="sol"
                                checked>
                            <label class="form-check-label small" for="moneda_sol">Sol</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="moneda" id="moneda_usd" value="usd">
                            <label class="form-check-label small" for="moneda_usd">Dólar</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="credito" name="credito">
                            <label class="form-check-label small" for="credito">Crédito</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="percepcion" name="percepcion">
                            <label class="form-check-label small" for="percepcion">Percepción</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="inc_impuesto" name="inc_impuesto">
                            <label class="form-check-label small" for="inc_impuesto">Inc. Impuesto</label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Productos</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#productSearchModal" aria-label="Alta proveedor">...</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>#</th>
                                    <th>CB</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Costo/Unid.</th>
                                    <th class="text-end">Dscto</th>
                                    <th class="text-end">VCPC</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Resumen</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Total Bruto</label>
                            <input name="total_bruto" type="number" step="0.01"
                                class="form-control form-control-sm text-end" />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Total Descuento</label>
                            <input name="total_descuento" type="number" step="0.01"
                                class="form-control form-control-sm text-end" />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Bruto Neto</label>
                            <input name="bruto_neto" type="number" step="0.01"
                                class="form-control form-control-sm text-end" />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Total Impuesto</label>
                            <input name="total_impuesto" type="number" step="0.01"
                                class="form-control form-control-sm text-end" />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Total Neto</label>
                            <input name="total_neto" type="number" step="0.01"
                                class="form-control form-control-sm text-end" />
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Flete</label>
                            <input name="flete" type="number" step="0.01"
                                class="form-control form-control-sm text-end" />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Total a Pagar</label>
                            <input name="total_pagar" type="number" step="0.01"
                                class="form-control form-control-sm text-end" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>

        <!-- Modal crear proveedor -->
        @include('compras.partials.modal-proveedor')

        <!-- PRODUCT SEARCH / NEW PRODUCT modal (single modal with steps) -->
        <!-- Modal: Búsqueda de Productos -->
        @include('compras.partials.modal-producto-search')

    </div>

    <!-- JS dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        $(function() {
            // CSRF header for all ajax
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const apiRucUrl =
                '{{ route('apidocumento.ruc') }}'; // endpoint que devuelve datos RUC (ajusta si no existe)
            const proveedoresSelectUrl = '{{ route('proveedores.select') }}';
            const proveedoresStoreUrl = '{{ route('proveedores.store') }}';

            // Inicializar Select2 (Select2 + Bootstrap tema)
            $('#proveedor_select').select2({
                theme: 'bootstrap-5',
                width: 'resolve',
                placeholder: 'Buscar proveedor...',
                allowClear: true,
                ajax: {
                    url: proveedoresSelectUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(p) {
                                return {
                                    id: p.id,
                                    text: (p.nombre_comercial || p.nombre_legal || p.ruc ||
                                        'Proveedor')
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            // Buscar RUC y autocompletar campos del modal
            $('#btn-ruc').on('click', function(e) {
                e.preventDefault();
                const ruc = $('#ruc').val().trim();
                const $status = $('#ruc-status');

                if (!/^\d{11}$/.test(ruc)) {
                    $status.text('Ingrese un RUC válido de 11 dígitos.').removeClass(
                        'text-success text-info').addClass('text-danger');
                    return;
                }

                $status.text('Buscando...').removeClass('text-success text-danger').addClass('text-info');
                Swal.fire({
                    title: 'Buscando RUC',
                    html: 'Por favor, espere.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.post(apiRucUrl, {
                        documento: ruc
                    })
                    .done(function(response) {
                        Swal.close();
                        if (response && (response.razonSocial || response.nombre)) {
                            $('#nombre_comercial').val(response.razonSocial || response.nombre || '');
                            $('#nombre_legal').val(response.nombreLegal || response.razonSocial || '');
                            $('#direccion').val(response.direccion || '');
                            $('#localidad').val([response.distrito, response.provincia, response
                                .departamento
                            ].filter(Boolean).join(', '));
                            $('#codigo_postal').val(response.codigo_postal || '');
                            $('#email').val(response.email || '');
                            $('#ubigeo').val(response.ubigeo || '');
                            $status.text('RUC encontrado ✓').removeClass('text-info text-danger')
                                .addClass('text-success');
                        } else {
                            $status.text('RUC no encontrado').removeClass('text-info text-success')
                                .addClass('text-danger');
                            Swal.fire({
                                icon: 'error',
                                title: 'RUC no encontrado',
                                text: 'No se encontró información para este RUC.'
                            });
                        }
                    })
                    .fail(function() {
                        Swal.close();
                        $status.text('Error al buscar RUC').removeClass('text-info text-success')
                            .addClass('text-danger');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al buscar información del RUC.'
                        });
                    });
            });

            // Guardar proveedor por AJAX sin recargar la página
            $('#proveedor-form').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const formData = $form.serialize();

                Swal.fire({
                    title: 'Guardando proveedor',
                    html: 'Por favor, espere.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.post(proveedoresStoreUrl, formData)
                    .done(function(resp) {
                        Swal.close();
                        if (resp && resp.id) {
                            const text = resp.nombre_comercial || resp.nombre_legal || resp.ruc || (
                                'Proveedor ' + resp.id);
                            // Insertar y seleccionar la nueva opción en Select2
                            const newOption = new Option(text, resp.id, true, true);
                            $('#proveedor_select').append(newOption).trigger('change');

                            // Cerrar modal
                            const modalEl = document.getElementById('proveedorModal');
                            const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(
                                modalEl);
                            bsModal.hide();

                            // Resetear form del modal
                            $form[0].reset();
                            $('#ruc-status').text('');

                            Swal.fire('Guardado', 'Proveedor guardado correctamente', 'success');
                        } else {
                            // Si servidor no devuelve JSON con id -> forzar recarga (fallback)
                            Swal.fire('Ok', 'Proveedor guardado. Recargando para sincronizar.', 'info')
                                .then(() => location.reload());
                        }
                    })
                    .fail(function(xhr) {
                        Swal.close();
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            // Form validation errors
                            const errors = xhr.responseJSON.errors;
                            const messages = Object.values(errors).flat().join('<br>');
                            Swal.fire({
                                icon: 'error',
                                title: 'Errores de validación',
                                html: messages
                            });
                        } else {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr
                                .responseJSON.message : 'No se pudo guardar el proveedor.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
            });

            // Opcional: cuando el modal se oculte, limpiar estado
            $('#proveedorModal').on('hidden.bs.modal', function() {
                $('#proveedor-form')[0].reset();
                $('#ruc-status').text('');
            });


            $('#btn-open-quick-create').on('click', function() {
                location.href = '{{ route('productos.step1') }}';
            });
        });
    </script>

    <!-- JS: comportamiento del modal de búsqueda -->
   @include('compras.partials.js.compra-product-search-and-add')
@endsection

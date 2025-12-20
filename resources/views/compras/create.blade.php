@extends('layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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
                                <select id="proveedor_select" name="proveedor_id" class="form-select form-select-sm" style="width:100%"></select>
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
                            <input id="fecha_emision" name="fecha_emision" type="date" class="form-control form-control-sm" />
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="fecha_pago" class="form-label small text-muted">Fecha Pago</label>
                            <input id="fecha_pago" name="fecha_pago" type="date" class="form-control form-control-sm" />
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3 align-items-center mt-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="moneda" id="moneda_sol" value="sol" checked>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Productos</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#productSearchModal">...</button>
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
                            <tbody></tbody>
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
                            <input name="total_bruto" type="number" step="0.01" class="form-control form-control-sm text-end" value="0.00" readonly />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Total Descuento</label>
                            <input name="total_descuento" type="number" step="0.01" class="form-control form-control-sm text-end" value="0.00" readonly />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Bruto Neto</label>
                            <input name="bruto_neto" type="number" step="0.01" class="form-control form-control-sm text-end" value="0.00" readonly />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Total Impuesto</label>
                            <input name="total_impuesto" type="number" step="0.01" class="form-control form-control-sm text-end" value="0.00" readonly />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small text-muted">Total Neto</label>
                            <input name="total_neto" type="number" step="0.01" class="form-control form-control-sm text-end" value="0.00" readonly />
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>

        @include('compras.partials.modal-proveedor')
        @include('compras.partials.modal-producto-search')
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            const apiRucUrl = '{{ route("apidocumento.ruc") }}';
            const proveedoresSelectUrl = '{{ route("proveedores.select") }}';
            const proveedoresStoreUrl = '{{ route("proveedores.store") }}';

            // Select2 Setup
            $('#proveedor_select').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar proveedor...',
                allowClear: true,
                ajax: {
                    url: proveedoresSelectUrl,
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term || '' }),
                    processResults: data => ({
                        results: data.map(p => ({
                            id: p.id,
                            text: (p.nombre_comercial || p.nombre_legal || p.ruc)
                        }))
                    }),
                    cache: true
                },
                minimumInputLength: 1
            });

            // Lógica RUC
            $('#btn-ruc').on('click', function() {
                const ruc = $('#ruc').val().trim();
                if (!/^\d{11}$/.test(ruc)) return Swal.fire('Error', 'RUC inválido', 'warning');

                Swal.fire({ title: 'Buscando...', didOpen: () => Swal.showLoading() });

                $.post(apiRucUrl, { documento: ruc })
                    .done(resp => {
                        Swal.close();
                        if (resp.razonSocial || resp.nombre) {
                            $('#nombre_comercial').val(resp.razonSocial || resp.nombre);
                            $('#nombre_legal').val(resp.nombreLegal || resp.razonSocial);
                            $('#direccion').val(resp.direccion || '');
                        } else {
                            Swal.fire('No encontrado', 'RUC no existe', 'error');
                        }
                    })
                    .fail(() => Swal.fire('Error', 'Falla en el servicio RUC', 'error'));
            });

            // Guardar Proveedor (CORREGIDO)
            $('#proveedor-form').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);

                $.post(proveedoresStoreUrl, $form.serialize())
                    .done(function(resp) {
                        if (resp && resp.id) {
                            // 1. Añadir al select2
                            const text = resp.nombre_comercial || resp.nombre_legal || resp.ruc;
                            const newOption = new Option(text, resp.id, true, true);
                            $('#proveedor_select').append(newOption).trigger('change');

                            // 2. CERRAR MODAL Y LIMPIAR FONDO (Solución al problema)
                            const modalEl = document.getElementById('proveedorModal');
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) modalInstance.hide();
                            
                            // Limpieza forzada de backdrop
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });

                            $form[0].reset();
                            Swal.fire('Éxito', 'Proveedor registrado', 'success');
                        }
                    })
                    .fail(xhr => {
                        let msg = xhr.responseJSON?.message || 'Error al guardar';
                        Swal.fire('Error', msg, 'error');
                    });
            });
        });
    </script>

   @include('compras.partials.js.compra-product-search-and-add')
@endsection
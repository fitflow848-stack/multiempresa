@extends('layout.app')

@section('content')

{{-- ===================== STYLES ===================== --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

<style>
    .page-title { font-weight: 600; }
    .card {
        border: 0;
        box-shadow: 0 4px 12px rgba(0,0,0,.05);
        border-radius: .75rem;
    }
    .card-header {
        background: #f8f9fa;
        font-weight: 600;
    }
    .table thead th {
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 1;
    }
    .summary-box {
        background: #f8fafc;
        border-radius: .75rem;
        padding: 1rem;
    }
    .summary-box .total {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0d6efd;
    }
</style>

<div class="container py-4">

    {{-- ===================== HEADER ===================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title mb-1">🧾 Registrar Compra</h1>
            <div class="text-muted small">Gestión de comprobantes y productos</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                Cancelar
            </a>
            <button form="compra-form" type="submit" class="btn btn-primary">
                💾 Guardar Compra
            </button>
        </div>
    </div>

    {{-- ===================== FORM ===================== --}}
    <form method="POST" action="{{ route('compras.store') }}" id="compra-form">
        @csrf

        {{-- ===================== TICKET ===================== --}}
        <div class="card mb-4">
            <div class="card-header">📄 Datos del comprobante</div>
            <div class="card-body">

                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">Proveedor</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-toggle="modal" data-bs-target="#proveedorModal">+</button>
                            <select id="proveedor_select" name="proveedor_id" class="form-select"></select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">Presupuesto</label>
                        <select name="presupuesto" class="form-select">
                            <option value="Compra">Compra</option>
                            <option value="Servicio">Servicio</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select">
                            <option>Ticket</option>
                            <option>Factura</option>
                            <option>Boleta</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Fecha Emisión</label>
                        <input type="date" name="fecha_emision" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha Pago</label>
                        <input type="date" name="fecha_pago" class="form-control">
                    </div>
                </div>

                {{-- OPCIONES --}}
                <div class="border rounded p-3 mt-3">
                    <div class="fw-semibold mb-2">Opciones</div>
                    <div class="d-flex flex-wrap gap-4 align-items-center">

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="moneda" value="sol" checked>
                            <label class="form-check-label">Soles</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="moneda" value="usd">
                            <label class="form-check-label">Dólares</label>
                        </div>

                        <div class="vr"></div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="credito">
                            <label class="form-check-label">Crédito</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="percepcion">
                            <label class="form-check-label">Percepción</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="inc_impuesto">
                            <label class="form-check-label">Inc. Impuesto</label>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ===================== PRODUCTOS ===================== --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>📦 Productos</span>
                <button type="button" class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal" data-bs-target="#productSearchModal">
                    ➕ Agregar producto
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>CB</th>
                                <th>Descripción</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Costo</th>
                                <th class="text-end">Dscto</th>
                                <th class="text-end">VCPC</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================== RESUMEN ===================== --}}
        <div class="card mb-4">
            <div class="card-header">💰 Resumen</div>
            <div class="card-body">
                <div class="summary-box">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Bruto</label>
                            <input name="total_bruto" class="form-control text-end" readonly value="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Descuento</label>
                            <input name="total_descuento" class="form-control text-end" readonly value="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bruto Neto</label>
                            <input name="bruto_neto" class="form-control text-end" readonly value="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Impuestos</label>
                            <input name="total_impuesto" class="form-control text-end" readonly value="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total Neto</label>
                            <input name="total_neto" class="form-control text-end total" readonly value="0.00">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

    {{-- ===================== MODALES ===================== --}}
    @include('compras.partials.modal-proveedor')
    @include('compras.partials.modal-producto-search')

</div>

{{-- ===================== SCRIPTS ===================== --}}
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
            $('#btn-open-quick-create').on('click', function() {
                location.href = '{{ route('productos.step1') }}';
            });
        });
    </script>

   @include('compras.partials.js.compra-product-search-and-add')

@endsection

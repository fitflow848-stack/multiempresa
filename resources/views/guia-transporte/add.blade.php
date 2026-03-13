@extends('layout.app')

@section('title', 'Generar Guía de Remisión')

@section('content')
<style>
    :root {
        --novik-green: #2ecc71;
        --novik-light-bg: #f8fafc;
        --novik-border: #e2e8f0;
        --novik-text: #334155;
    }

    body {
        background-color: var(--novik-light-bg);
        color: var(--novik-text);
        font-family: 'Inter', sans-serif;
    }

    .form-container {
        max-width: 1200px;
        margin: 1.5rem auto;
        padding: 0 1rem;
    }

    .novik-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--novik-border);
        box-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.03);
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .novik-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .novik-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }

    .label-novik {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .novik-input-group {
        position: relative;
    }

    .novik-control {
        width: 100%;
        padding: 0.65rem 0.8rem;
        padding-left: 2.2rem;
        border-radius: 12px;
        border: 1px solid var(--novik-border);
        background-color: #f8fafc;
        transition: all 0.2s;
        font-size: 0.9rem;
    }

    .novik-control:focus {
        outline: none;
        border-color: var(--novik-green);
        background-color: white;
        box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.05);
    }

    .novik-icon {
        position: absolute;
        left: 0.8rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.1rem;
    }

    .btn-novik-primary {
        background-color: var(--novik-green);
        color: white;
        border: none;
        padding: 0.65rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-novik-primary:hover:not(:disabled) {
        background-color: #27ae60;
        transform: translateY(-1px);
    }

    .btn-novik-outline {
        border: 1px solid var(--novik-border);
        background: white;
        color: #64748b;
        padding: 0.65rem 1.2rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .section-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-label i {
        color: var(--novik-green);
    }

    /* Search Results */
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--novik-border);
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 100;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .search-item {
        padding: 0.6rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
    }

    .search-item:hover {
        background: #f8fafc;
    }

    /* Ubigeo Boxes */
    .ubigeo-container {
        display: flex;
        gap: 0.5rem;
    }

    .ubigeo-box {
        flex: 1;
    }

    .novik-check-icon {
        color: var(--novik-green);
        display: none;
        margin-left: 0.5rem;
    }

    /* Table */
    .novik-table {
        width: 100%;
        margin-top: 1rem;
    }
    .novik-table th {
        background: #f1f5f9;
        padding: 0.75rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        border-radius: 8px;
    }
    .novik-table td {
        padding: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
    }
</style>

<div class="form-container">
    <div class="novik-header">
        <h1 class="novik-title">Crear Guía de Remisión</h1>
        <a href="{{ route('guia.index') }}" class="btn-novik-outline">
            <i class="bx bx-arrow-back"></i> Regresar
        </a>
    </div>

    <!-- Buscador de Venta -->
    <div class="novik-card border-primary" style="background: #f1f7ff; border-style: dashed;">
        <div class="section-label"><i class="bx bx-search-alt"></i> Buscar Documento de Venta (Opcional)</div>
        <div class="row g-2">
            <div class="col-md-3">
                <select class="novik-control" id="ref_tipo" style="padding-left:0.8rem">
                    <option value="01">Factura</option>
                    <option value="03">Boleta</option>
                </select>
            </div>
            <div class="col-md-5 position-relative">
                <div class="novik-input-group">
                    <i class="bx bx-file novik-icon"></i>
                    <input type="text" class="novik-control" id="ref_search" placeholder="Serie y número (ej: F001-123)">
                    <div class="search-results" id="ref_results"></div>
                </div>
            </div>
            <div class="col-md-4">
                <p class="text-muted small mb-0 mt-2">
                    <i class="bx bx-info-circle text-primary"></i> Solo se mostrarán documentos <strong>enviados a SUNAT</strong>.
                </p>
            </div>
        </div>
    </div>

    <form id="formNovik">
        @csrf
        <input type="hidden" name="serie" value="{{ $serie }}">
        <input type="hidden" name="numero" value="{{ $numero }}">
        <input type="hidden" name="documento_relacionado" id="input_ref_doc">

        <div class="row g-3">
            <!-- Columna Izquierda: Traslado y Ubigeos -->
            <div class="col-md-8">
                <div class="novik-card">
                    <div class="section-label"><i class="bx bx-info-circle"></i> Información del Traslado</div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="label-novik">Modalidad</label>
                            <div class="novik-input-group">
                                <i class="bx bx-shuffle novik-icon"></i>
                                <select class="novik-control" name="modalidad_traslado_codigo" id="modalidad_select">
                                    <option value="01">Transporte Público</option>
                                    <option value="02">Transporte Privado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="label-novik">F. Traslado</label>
                            <div class="novik-input-group">
                                <i class="bx bx-calendar novik-icon"></i>
                                <input type="date" class="novik-control" name="fecha_traslado" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="label-novik">Peso Total (KGM)</label>
                            <div class="novik-input-group">
                                <i class="bx bx-package novik-icon"></i>
                                <input type="number" step="0.01" class="novik-control" name="peso_bruto" id="total_peso" value="1.00">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ubigeo Salida -->
                <div class="novik-card">
                    <div class="section-label"><i class="bx bx-map-pin"></i> Punto de Partida</div>
                    <div class="ubigeo-container mb-2">
                        <div class="ubigeo-box">
                            <label class="label-novik">Dep.</label>
                            <select class="novik-control" style="padding-left:0.8rem" id="dep_salida" name="departamento_partida">
                                @foreach($departamentos as $d)
                                    <option value="{{ $d->dep_cod }}">{{ $d->dep_nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ubigeo-box">
                            <label class="label-novik">Prov.</label>
                            <select class="novik-control" style="padding-left:0.8rem" id="prov_salida" name="provincia_partida"></select>
                        </div>
                        <div class="ubigeo-box">
                            <label class="label-novik">Dist.</label>
                            <select class="novik-control" style="padding-left:0.8rem" id="dist_salida" name="distrito_partida"></select>
                        </div>
                    </div>
                    <div class="novik-input-group">
                        <i class="bx bx-home novik-icon"></i>
                        <input type="text" class="novik-control" name="direccion_partida" id="dir_salida" placeholder="Dirección exacta de salida">
                    </div>
                </div>

                <!-- Ubigeo Llegada -->
                <div class="novik-card">
                    <div class="section-label"><i class="bx bxs-map-pin"></i> Punto de Llegada</div>
                    <div class="ubigeo-container mb-2">
                        <div class="ubigeo-box">
                            <label class="label-novik">Dep.</label>
                            <select class="novik-control" style="padding-left:0.8rem" id="dep_llegada" name="departamento_llegada">
                                @foreach($departamentos as $d)
                                    <option value="{{ $d->dep_cod }}">{{ $d->dep_nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ubigeo-box">
                            <label class="label-novik">Prov.</label>
                            <select class="novik-control" style="padding-left:0.8rem" id="prov_llegada" name="provincia_llegada"></select>
                        </div>
                        <div class="ubigeo-box">
                            <label class="label-novik">Dist.</label>
                            <select class="novik-control" style="padding-left:0.8rem" id="dist_llegada" name="distrito_llegada"></select>
                        </div>
                    </div>
                    <div class="novik-input-group">
                        <i class="bx bx-navigation novik-icon"></i>
                        <input type="text" class="novik-control" name="direccion_llegada" id="dir_llegada" placeholder="Dirección exacta de destino">
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Destinatario y Transportista -->
            <div class="col-md-4">
                <div class="novik-card">
                    <div class="section-label"><i class="bx bx-user"></i> Destinatario</div>
                    <div class="novik-input-group mb-2">
                        <i class="bx bx-id-card novik-icon"></i>
                        <input type="text" class="novik-control" name="cliente_documento" id="cli_doc" placeholder="RUC/DNI">
                    </div>
                    <div class="novik-input-group">
                        <i class="bx bx-user-circle novik-icon"></i>
                        <input type="text" class="novik-control" name="cliente_nombre" id="cli_nombre" placeholder="Nombre completo">
                    </div>
                </div>

                <div class="novik-card">
                    <div class="section-label"><i class="bx bx-truck"></i> Transportista</div>
                    <div id="section_publico">
                        <div class="novik-input-group mb-2">
                            <i class="bx bx-building novik-icon"></i>
                            <input type="text" class="novik-control" name="transportista_doc" id="trans_ruc" placeholder="RUC Transportista">
                        </div>
                        <div class="novik-input-group">
                            <i class="bx bx-briefcase novik-icon"></i>
                            <input type="text" class="novik-control" name="transportista_nombre" id="trans_nombre" placeholder="Razón Social">
                        </div>
                    </div>
                    <div id="section_privado" style="display:none">
                        <div class="novik-input-group mb-2">
                            <i class="bx bx-car novik-icon"></i>
                            <input type="text" class="novik-control" name="vehiculo_placa" placeholder="Placa del Vehículo">
                        </div>
                        <div class="novik-input-group mb-2">
                            <i class="bx bx-user novik-icon"></i>
                            <input type="text" class="novik-control" name="conductor_doc_numero" placeholder="DNI Conductor">
                        </div>
                        <div class="novik-input-group">
                            <i class="bx bx-id-card novik-icon"></i>
                            <input type="text" class="novik-control" name="conductor_licencia" placeholder="Licencia de Conducir">
                        </div>
                    </div>
                </div>

                <div class="novik-card">
                    <div class="section-label"><i class="bx bx-list-check"></i> Motivo</div>
                    <select class="novik-control" style="padding-left:0.8rem" name="motivo_traslado_codigo">
                        <option value="01">Venta</option>
                        <option value="14">Venta sujeta a confirmación</option>
                        <option value="02">Compra</option>
                        <option value="04">Traslado entre establecimientos</option>
                        <option value="13">Otros</option>
                    </select>
                </div>
            </div>

            <!-- Productos -->
            <div class="col-12">
                <div class="novik-card">
                    <div class="section-label"><i class="bx bx-package"></i> Detalles de la Carga</div>
                    <div class="novik-input-group mb-3" style="max-width: 500px;">
                        <i class="bx bx-search novik-icon"></i>
                        <input type="text" class="novik-control" id="item_search" placeholder="Añadir producto por código o nombre...">
                        <div class="search-results" id="item_results"></div>
                    </div>

                    <table class="novik-table" id="table_items">
                        <thead>
                            <tr>
                                <th>Cód.</th>
                                <th>Descripción</th>
                                <th>U.M.</th>
                                <th width="100">Cantidad</th>
                                <th width="100">Peso (Kg)</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 text-center pb-4">
                <button type="submit" class="btn-novik-primary shadow-lg px-5 py-3" id="btn_submit">
                    <i class="bx bx-check-double" style="font-size: 1.5rem"></i> EMITIR GUÍA DE REMISIÓN ELECTRÓNICA
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const token = $('meta[name="csrf-token"]').attr('content');

    // Ubigeos Dynamic
    function loadProv(dep, targetProv, targetDist) {
        $.post("{{ route('provincia.get') }}", { _token: token, dep: dep }).done(res => {
            let html = '';
            res.forEach(p => html += `<option value="${p.pro_id}">${p.pro_nombre}</option>`);
            $(targetProv).html(html).trigger('change');
        });
    }

    function loadDist(prov, targetDist) {
        $.post("{{ route('distrito.get') }}", { _token: token, prov: prov }).done(res => {
            let html = '';
            res.forEach(d => html += `<option value="${d.dis_id}">${d.dis_nombre}</option>`);
            $(targetDist).html(html);
        });
    }

    $('#dep_salida').change(function() { loadProv($(this).val(), '#prov_salida', '#dist_salida'); });
    $('#prov_salida').change(function() { loadDist($(this).val(), '#dist_salida'); });
    $('#dep_llegada').change(function() { loadProv($(this).val(), '#prov_llegada', '#dist_llegada'); });
    $('#prov_llegada').change(function() { loadDist($(this).val(), '#dist_llegada'); });

    $('#dep_salida, #dep_llegada').trigger('change');

    // Modalidad Toggle
    $('#modalidad_select').change(function() {
        if ($(this).val() === '01') { $('#section_publico').show(); $('#section_privado').hide(); }
        else { $('#section_publico').hide(); $('#section_privado').show(); }
    });

    // Buscar Venta para Referencia
    $('#ref_search').on('input', function() {
        const query = $(this).val();
        const tipo = $('#ref_tipo').val();
        if (query.length > 2) {
            $.get("{{ route('comprobantes.buscar') }}", { q: query, tipo: tipo }).done(res => {
                let html = '';
                res.forEach(v => {
                    html += `<div class="search-item ref-item" data-id="${v.id_venta}" data-num="${v.serie}-${v.numero}">
                        <strong>${v.serie}-${v.numero}</strong> - ${v.cliente ? v.cliente.nombre : 'S/N'}
                    </div>`;
                });
                $('#ref_results').html(html).show();
            });
        } else { $('#ref_results').hide(); }
    });

    // Cambiar tipo refresca búsqueda si hay texto
    $('#ref_tipo').change(function() {
        if ($('#ref_search').val().length > 2) {
            $('#ref_search').trigger('input');
        }
    });

    $(document).on('click', '.ref-item', function() {
        const id = $(this).data('id');
        const num = $(this).data('num');
        $('#ref_search').val(num);
        $('#input_ref_doc').val(num);
        $('#ref_results').hide();
        importVenta(id);
    });

    function importVenta(id) {
        Swal.fire({ title: 'Importando datos...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        $.get(`{{ url('comprobantes') }}/${id}/detalle`).done(res => {
            Swal.close();
            if (res.success) {
                const v = res.venta;
                const cli = v.cliente || {};
                const emp = res.empresa || {};

                $('#cli_doc').val(cli.numero_documento || '');
                $('#cli_nombre').val(cli.nombre || '');
                $('#dir_salida').val(emp.direccion_fiscal || '');
                $('#dir_llegada').val(cli.direccion || '');
                
                $('#table_items tbody').empty();
                res.detalles.forEach(d => {
                    const prod = d.producto || {};
                    addItem(d.servicio_id, prod.nombre || d.nombre_servicio, prod.unidad_medida ? prod.unidad_medida.nombre : 'NIU', d.cantidad, prod.peso || 0);
                });
                calcTotal();
            }
        });
    }

    // Buscar Productos
    $('#item_search').on('input', function() {
        const q = $(this).val();
        if (q.length > 2) {
            $.get("{{ url('pos/buscar-productos') }}", { q: q }).done(res => {
                let html = '';
                res.forEach(p => {
                    html += `<div class="search-item p-item" data-cod="${p.producto_id || p.id}" data-desc="${p.nombre || p.descripcion}" data-unit="${p.unidad_medida_nombre || 'NIU'}" data-peso="${p.peso || 0.1}">
                        <strong>${p.producto_id || '-'}</strong> - ${p.nombre || p.descripcion}
                    </div>`;
                });
                $('#item_results').html(html).show();
            });
        } else { $('#item_results').hide(); }
    });

    $(document).on('click', '.p-item', function() {
        const p = $(this).data();
        addItem(p.cod, p.desc, p.unit, 1, p.peso);
        $('#item_results').hide();
        $('#item_search').val('');
    });

    function addItem(cod, desc, unit, cant, peso) {
        const row = `<tr class="item-row">
            <td><span class="fw-bold">${cod}</span></td>
            <td>${desc}</td>
            <td>${unit}</td>
            <td><input type="number" class="form-control form-control-sm text-center row-cant" value="${cant}"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm text-end row-peso" value="${peso}"></td>
            <td><button type="button" class="btn btn-link text-danger p-0 btn-remove"><i class="bx bx-trash"></i></button></td>
            <input type="hidden" class="row-data" value='${JSON.stringify({ cod_sap: cod, descripcion: desc, unidad_medida: unit, tipo: 'producto' })}'>
        </tr>`;
        $('#table_items tbody').append(row);
        calcTotal();
    }

    $(document).on('click', '.btn-remove', function() { $(this).closest('tr').remove(); calcTotal(); });
    $(document).on('input', '.row-cant, .row-peso', function() { calcTotal(); });

    function calcTotal() {
        let total = 0;
        $('.item-row').each(function() {
            const c = parseFloat($(this).find('.row-cant').val()) || 0;
            const p = parseFloat($(this).find('.row-peso').val()) || 0;
            total += (c * p);
        });
        $('#total_peso').val(total.toFixed(2));
    }

    // Submit
    $('#formNovik').submit(function(e) {
        e.preventDefault();
        if ($('.item-row').length === 0) { Swal.fire('Error', 'Debe agregar productos.', 'warning'); return; }

        const btn = $('#btn_submit');
        btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> PROCESANDO...');

        let formData = new FormData(this);
        $('.item-row').each(function(i) {
            const base = JSON.parse($(this).find('.row-data').val());
            const item = { ...base, cantidad: $(this).find('.row-cant').val(), peso: $(this).find('.row-peso').val() };
            formData.append(`detalle[${i}]`, JSON.stringify(item));
        });

        $.ajax({
            url: "{{ route('guia.save') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.id) {
                    Swal.fire({ icon: 'success', title: 'Guía Emitida', timer: 2000 }).then(() => {
                        window.open(`{{ url('guia/remision') }}/${res.id}`, '_blank');
                        window.location.href = "{{ route('guia.index') }}";
                    });
                } else {
                    Swal.fire('Error', res.error || 'API Error', 'error');
                    btn.prop('disabled', false).html('<i class="bx bx-check-double"></i> EMITIR GUÍA');
                }
            },
            error: function() {
                Swal.fire('Error', 'Fallo de conexión', 'error');
                btn.prop('disabled', false).html('<i class="bx bx-check-double"></i> EMITIR GUÍA');
            }
        });
    });

    // API Search for DNI/RUC
    $('#cli_doc').on('input', function() {
        const val = $(this).val();
        if (val.length === 8 || val.length === 11) {
            const r = val.length === 11 ? "{{ route('apidocumento.ruc') }}" : "{{ route('apidocumento.dni') }}";
            $.post(r, { _token: token, documento: val }).done(res => {
                if (res) $('#cli_nombre').val(res.razonSocial || (res.nombres + ' ' + res.apellidoPaterno));
            });
        }
    });

    $('#trans_ruc').on('input', function() {
        if ($(this).val().length === 11) {
            $.post("{{ route('apidocumento.ruc') }}", { _token: token, documento: $(this).val() }).done(res => {
                if (res) $('#trans_nombre').val(res.razonSocial);
            });
        }
    });
});
</script>
@endpush
@endsection

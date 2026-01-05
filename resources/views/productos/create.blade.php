@extends('layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <style>
        #productQuickCreateModal .modal-body {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        @media (min-width: 1200px) {
            #productQuickCreateModal .modal-dialog {
                max-width: 1100px;
            }
        }
    </style>


    <div class="container py-4">
        <h1 class="h5 mb-4">Registrar Compras</h1>

        <div class="container-fluid">
            <div class="row gy-3">
                <div class="col-12 col-lg-8">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted">Lab. hab</label>
                            <div class="input-group">
                                <select id="np-laboratorio" name="laboratorio_id" class="form-select form-select-sm">
                                    <option value="">-- seleccionar laboratorio --</option>
                                    @foreach ($laboratorios ?? [] as $laboratorio)
                                        <option value="{{ $laboratorio->id }}">{{ $laboratorio->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="np-lab-add"
                                    title="Agregar laboratorio">+</button>
                            </div>
                            <div class="form-text small text-muted">Usa + para crear nuevo laboratorio si hace falta.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted">Fam/Subfam</label>
                            <div class="input-group position-relative">
                                <select name="familia" id="np-familia" class="form-select form-select-sm" readonly style="pointer-events: none;">
                                    <option value="">-- seleccionar --</option>
                                    <option value="nutrientes">NUTRIENTES</option>
                                    <option value="fertilizantes">FERTILIZANTES</option>
                                    <option value="varios">VARIOS</option>
                                </select>
                                <div class="select-overlay" style="position: absolute; top: 0; left: 0; right: 42px; bottom: 0; z-index: 10; cursor: pointer;"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="np-fam-add"
                                    title="Agregar familia">+</button>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-muted">Nombre <span class="text-danger">*</span></label>
                            <textarea name="nombre" id="np-nombre" class="form-control form-control-sm" rows="2" required
                                placeholder="Nombre | marca o modelo"></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted">Marca</label>
                            <div class="input-group">
                                <select id="np-marca" name="marca" class="form-select form-select-sm">
                                    @foreach ($marcas as $marca)
                                        <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="np-marca-add"
                                    title="Guardar marca">+</button>
                            </div>
                            <div class="form-text small text-muted">Presiona + para guardar la marca y
                                disponerla en selects.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted">Unidad Medida</label>
                            <div class="input-group">
                                <select id="np-unidad" name="unidad_medida" class="form-select form-select-sm">
                                    @foreach ($unidades as $unidad)
                                        <option value="{{ $unidad->id }}">{{ $unidad->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="np-unidad-add"
                                    title="Agregar unidad">+</button>
                            </div>
                            <div class="form-text small text-muted">Usa + para crear nueva unidad si hace
                                falta.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted">Tipo Impuesto</label>
                            <select name="tipo_impuesto" id="np-tipo-impuesto" class="form-select form-select-sm">
                                <option value="gravado">Gravado - Operación Onerosa</option>
                                <option value="exonerado">Exonerado</option>
                                <option value="inafecto">Inafecto</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="np-op-avanzadas"
                                    name="opciones_avanzadas" />
                                <label class="form-check-label small ms-2" for="np-op-avanzadas">Opciones
                                    Avanzadas</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted">Condición de venta</label>
                            <select name="condicion_venta" id="np-condicion-venta" class="form-select form-select-sm">
                                <option value="sin_receta">Sin Receta Médica</option>
                                <option value="con_receta">Con Receta Médica</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="mb-2">
                        <h6 class="small text-danger mb-2">Atributos Stock</h6>

                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" id="np-attr-serie" name="attr_numero_serie">
                            <label class="form-check-label small" for="np-attr-serie">Número Serie</label>
                        </div>

                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" id="np-attr-fecha-venc"
                                name="attr_fecha_vencimiento">
                            <label class="form-check-label small" for="np-attr-fecha-venc">Fecha
                                Vencimiento</label>
                        </div>

                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" id="np-attr-lote"
                                name="attr_lote_produccion">
                            <label class="form-check-label small" for="np-attr-lote">Lote
                                Producción</label>
                        </div>

                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" id="np-attr-venta-menudeo"
                                name="attr_venta_menudeo">
                            <label class="form-check-label small" for="np-attr-venta-menudeo">Venta
                                Menudeo</label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="small text-muted">Acciones</p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('compras.create') }}" type="button" class="btn btn-outline-secondary"
                                id="np-cancel">Volver
                                Presupuestos</a>
                            <button type="button" class="btn btn-success" id="np-next">Siguiente</button>
                        </div>
                    </div>
                </div>
            </div> <!-- row -->
        </div> <!-- container -->
    </div>

    <!-- Modal para escoger/crear Familia y Subfamilia -->
    <div class="modal fade" id="familiaSubfamiliaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Familia y Sub Familia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="d-flex mb-2">
                                <input type="text" id="fam-filter" class="form-control form-control-sm me-2"
                                    placeholder="Buscar familia...">
                                <button class="btn btn-sm btn-outline-primary" id="fam-create-btn"
                                    title="Crear familia">+</button>
                            </div>
                            <div class="list-group" id="fam-list" style="max-height:320px; overflow:auto;"></div>
                        </div>

                        <div class="col-6">
                            <div class="d-flex mb-2">
                                <input type="text" id="subfam-filter" class="form-control form-control-sm me-2"
                                    placeholder="Buscar subfamilia...">
                                <button class="btn btn-sm btn-outline-primary" id="subfam-create-btn"
                                    title="Crear subfamilia">+</button>
                            </div>
                            <div class="list-group" id="subfam-list" style="max-height:320px; overflow:auto;"></div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <small>Seleccionado: <span id="familia-subfam-selected">—</span></small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="fam-subfam-apply">Usar selección</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Public JS: public/js/product-quick-create.js
        // Requiere jQuery y SweetAlert2 (ya los cargas en la vista que mostraste)

        (function($) {
            'use strict';

            // Función para mostrar alert de éxito pequeño
            function toastSuccess(message) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }

            // Función para mostrar errores de validación
            function showValidationErrors(errors) {
                let html = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                for (const key in errors) {
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        errors[key].forEach(msg => {
                            html += `<li>${msg}</li>`;
                        });
                    }
                }
                html += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: 'Errores',
                    html: html
                });
            }

            $(function() {
                $('#np-marca-add').on('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Nueva marca',
                        input: 'text',
                        inputLabel: 'Nombre de la marca',
                        inputPlaceholder: 'Ej. ACME',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        preConfirm: (value) => {
                            if (!value || !value.trim()) {
                                Swal.showValidationMessage('El nombre es requerido');
                                return false;
                            }
                            return value.trim();
                        }
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        const nombre = result.value;

                        $.ajax({
                            url: '{{env('APP_URL')}}/marcas',
                            method: 'POST',
                            data: {
                                nombre: nombre
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            success: function(data, textStatus, jqXHR) {
                                // data debe contener { id, nombre } según tu controller
                                const status = jqXHR
                                    .status; // 200 = existente, 201 = creado
                                const id = data.id;
                                const nombreResp = data.nombre ?? data.nombre;

                                // Verificar si ya existe la opción (por id)
                                let $select = $('#np-marca');
                                if ($select.find('option[value="' + id + '"]')
                                    .length === 0) {
                                    // agregar nueva opción
                                    const option = new Option(nombreResp, id, true,
                                        true);
                                    $select.append(option);
                                } else {
                                    // si existe, seleccionarla y actualizar texto por si cambió
                                    $select.find('option[value="' + id + '"]').text(
                                        nombreResp).prop('selected', true);
                                }

                                // Si usas select2, disparar evento para refrescar
                                if ($select.hasClass('select2-hidden-accessible')) {
                                    $select.trigger('change.select2');
                                }

                                if (status === 201) {
                                    toastSuccess('Marca creada');
                                } else {
                                    toastSuccess('Marca disponible');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                if (jqXHR.status === 422) {
                                    // errores de validación Laravel
                                    const json = jqXHR.responseJSON;
                                    if (json && json.errors) {
                                        showValidationErrors(json.errors);
                                        return;
                                    }
                                }

                                // otro error
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: jqXHR.responseJSON && jqXHR
                                        .responseJSON.message ?
                                        jqXHR.responseJSON.message :
                                        'Hubo un error al guardar la marca'
                                });
                            }
                        });
                    });
                });
            });
        })(jQuery);

        (function($) {
            'use strict';

            // Reusar funciones definidas ya para marcas: toastSuccess y showValidationErrors
            function toastSuccess(message) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }

            function showValidationErrors(errors) {
                let html = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                for (const key in errors) {
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        errors[key].forEach(msg => {
                            html += `<li>${msg}</li>`;
                        });
                    }
                }
                html += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: 'Errores',
                    html: html
                });
            }

            $(function() {
                // Handler para crear Unidad de Medida
                $('#np-unidad-add').on('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Nueva Unidad de Medida',
                        html: '<input id="swal-unidad-codigo" class="swal2-input" placeholder="Código (ej. KG, L)">' +
                            '<input id="swal-unidad-nombre" class="swal2-input" placeholder="Nombre (ej. Kilogramo, Litro)">',
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        preConfirm: () => {
                            const codigo = document.getElementById('swal-unidad-codigo')
                                .value;
                            const nombre = document.getElementById('swal-unidad-nombre')
                                .value;
                            if (!codigo || !codigo.trim()) {
                                Swal.showValidationMessage('El código es requerido');
                                return false;
                            }
                            if (!nombre || !nombre.trim()) {
                                Swal.showValidationMessage('El nombre es requerido');
                                return false;
                            }
                            return {
                                codigo: codigo.trim(),
                                nombre: nombre.trim()
                            };
                        }
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        const payload = result.value;

                        $.ajax({
                            url: '{{env('APP_URL')}}/unidades',
                            method: 'POST',
                            data: payload,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            success: function(data, textStatus, jqXHR) {
                                const status = jqXHR
                                    .status; // 200 = existente, 201 = creado
                                const id = data.id;
                                const nombreResp = data.nombre;

                                let $select = $('#np-unidad');
                                if ($select.find('option[value="' + id + '"]')
                                    .length === 0) {
                                    const option = new Option(nombreResp, id, true,
                                        true);
                                    $select.append(option);
                                } else {
                                    $select.find('option[value="' + id + '"]').text(
                                        nombreResp).prop('selected', true);
                                }

                                if ($select.hasClass('select2-hidden-accessible')) {
                                    $select.trigger('change.select2');
                                }

                                if (status === 201) {
                                    toastSuccess('Unidad creada');
                                } else {
                                    toastSuccess('Unidad disponible');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                if (jqXHR.status === 422) {
                                    const json = jqXHR.responseJSON;
                                    if (json && json.errors) {
                                        showValidationErrors(json.errors);
                                        return;
                                    }
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: jqXHR.responseJSON && jqXHR
                                        .responseJSON.message ?
                                        jqXHR.responseJSON.message :
                                        'Hubo un error al guardar la unidad'
                                });
                            }
                        });
                    });
                });
            });

        })(jQuery);

        // Funcionalidad para Laboratorio
        (function($) {
            'use strict';

            // Reusar funciones definidas para otros modales
            function toastSuccess(message) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }

            function showValidationErrors(errors) {
                let html = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                for (const key in errors) {
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        errors[key].forEach(msg => {
                            html += `<li>${msg}</li>`;
                        });
                    }
                }
                html += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: 'Errores',
                    html: html
                });
            }

            $(function() {
                $('#np-lab-add').on('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Nuevo laboratorio',
                        input: 'text',
                        inputLabel: 'Nombre del laboratorio',
                        inputPlaceholder: 'Ej. LABORATORIO XYZ',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        preConfirm: (value) => {
                            if (!value || !value.trim()) {
                                Swal.showValidationMessage('El nombre es requerido');
                                return false;
                            }
                            return value.trim();
                        }
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        const nombre = result.value;

                        $.ajax({
                            url: '{{env('APP_URL')}}/laboratorios',
                            method: 'POST',
                            data: {
                                nombre: nombre
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(data, textStatus, jqXHR) {
                                const status = jqXHR.status; // 200 = existente, 201 = creado
                                const id = data.id;
                                const nombreResp = data.nombre;

                                // Verificar si ya existe la opción (por id)
                                let $select = $('#np-laboratorio');
                                if ($select.find('option[value="' + id + '"]').length === 0) {
                                    // agregar nueva opción
                                    const option = new Option(nombreResp, id, true, true);
                                    $select.append(option);
                                } else {
                                    // si existe, seleccionarla y actualizar texto por si cambió
                                    $select.find('option[value="' + id + '"]').text(nombreResp).prop('selected', true);
                                }

                                // Si usas select2, disparar evento para refrescar
                                if ($select.hasClass('select2-hidden-accessible')) {
                                    $select.trigger('change.select2');
                                }

                                if (status === 201) {
                                    toastSuccess('Laboratorio creado');
                                } else {
                                    toastSuccess('Laboratorio disponible');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                if (jqXHR.status === 422) {
                                    // errores de validación Laravel
                                    const json = jqXHR.responseJSON;
                                    if (json && json.errors) {
                                        showValidationErrors(json.errors);
                                        return;
                                    }
                                }

                                // otro error
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: jqXHR.responseJSON && jqXHR.responseJSON.message ?
                                        jqXHR.responseJSON.message :
                                        'Hubo un error al guardar el laboratorio'
                                });
                            }
                        });
                    });
                });
            });
        })(jQuery);


        // Requiere jQuery, Bootstrap JS y SweetAlert2 (ya los cargas en la vista)
        (function($) {
            'use strict';

            // URLs (si prefieres rutas nombradas, asigna window.routes desde Blade)
            const URLS = {
                // Rutas simples
                familiasIndex: '{{ route('familias.index') }}',
                familiasStore: '{{ route('familias.store') }}',
                subfamiliasIndex: '{{ route('subfamilias.index') }}',
                subfamiliasStore: '{{ route('subfamilias.store') }}',

                // Ruta con parámetro dinámico
                familiaSubfamilias: (id) => {
                    // Generamos la ruta en Blade usando un placeholder 'ID_PLACEHOLDER'
                    let url = '{{ route('familias.subfamilias', ['familia' => 'ID_PLACEHOLDER']) }}';
                    // Reemplazamos el placeholder con el ID real de JS
                    return url.replace('ID_PLACEHOLDER', id);
                }
            };

            // Estado local
            let selectedFamilia = null;
            let selectedSubfamilia = null;

            // Helpers
            function csrfHeader() {
                return {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                };
            }

            function toastSuccess(msg) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: msg,
                    showConfirmButton: false,
                    timer: 1500
                });
            }

            function showValidationErrors(errors) {
                let html = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                for (const key in errors) {
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        errors[key].forEach(msg => html += `<li>${msg}</li>`);
                    }
                }
                html += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: 'Errores',
                    html: html
                });
            }

            // Render listas
            function renderFamilias(items) {
                const $list = $('#fam-list').empty();
                items.forEach(f => {
                    const $el = $(
                        `<button type="button" class="list-group-item list-group-item-action">${f.nombre}</button>`
                    );
                    $el.data('item', f);
                    $el.on('click', function() {
                        selectedFamilia = $(this).data('item');
                        selectedSubfamilia = null;
                        highlightSelected();
                        fetchSubfamilias(selectedFamilia.id);
                        updateSelectedLabel();
                    });
                    $list.append($el);
                });
            }

            function renderSubfamilias(items) {
                const $list = $('#subfam-list').empty();
                // Si no hay familia seleccionada, mostrar placeholder
                if (!selectedFamilia) {
                    $list.html('<div class="text-muted small p-2">Seleccione primero una familia</div>');
                    return;
                }

                // opción "—" vacía
                const empty = $(
                    `<button type="button" class="list-group-item list-group-item-action text-muted">—</button>`);
                empty.data('item', null);
                empty.on('click', function() {
                    selectedSubfamilia = null;
                    highlightSelected();
                    updateSelectedLabel();
                });
                $list.append(empty);

                items.forEach(s => {
                    const $el = $(
                        `<button type="button" class="list-group-item list-group-item-action">${s.nombre}</button>`
                    );
                    $el.data('item', s);
                    $el.on('click', function() {
                        selectedSubfamilia = $(this).data('item');
                        highlightSelected();
                        updateSelectedLabel();
                    });
                    $list.append($el);
                });
            }

            function highlightSelected() {
                $('#fam-list .list-group-item').removeClass('active');
                $('#subfam-list .list-group-item').removeClass('active');

                if (selectedFamilia) {
                    $('#fam-list .list-group-item').filter(function() {
                        const it = $(this).data('item');
                        return it && it.id === selectedFamilia.id;
                    }).addClass('active');
                }
                if (selectedSubfamilia) {
                    $('#subfam-list .list-group-item').filter(function() {
                        const it = $(this).data('item');
                        return it && it.id === selectedSubfamilia.id;
                    }).addClass('active');
                }
            }

            function updateSelectedLabel() {
                const famText = selectedFamilia ? selectedFamilia.nombre : '—';
                const subText = selectedSubfamilia ? selectedSubfamilia.nombre : '—';
                $('#familia-subfam-selected').text(`${famText}  —  ${subText}`);
            }

            // Fetch data
            function fetchFamilias(q = '') {
                $.get(URLS.familiasIndex, {
                    q: q
                }).done(data => {
                    renderFamilias(data);
                });
            }

            function fetchSubfamilias(familiaId, q = '') {
                if (!familiaId) return renderSubfamilias([]);
                $.get(URLS.familiaSubfamilias(familiaId), {
                    q: q
                }).done(data => {
                    renderSubfamilias(data);
                });
            }

            // Crear familia
            function createFamilia(nombre) {
                return $.ajax({
                    url: URLS.familiasStore,
                    method: 'POST',
                    data: {
                        nombre: nombre
                    },
                    headers: csrfHeader()
                });
            }

            // Crear subfamilia
            function createSubfamilia(familia_id, nombre) {
                return $.ajax({
                    url: URLS.subfamiliasStore,
                    method: 'POST',
                    data: {
                        familia_id: familia_id,
                        nombre: nombre
                    },
                    headers: csrfHeader()
                });
            }

            // Aplicar selección al formulario principal
            function applySelectionToForm() {
                if (!selectedFamilia) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Debe seleccionar una familia'
                    });
                    return;
                }

                // Asegurar que el select #np-familia tiene la opción
                const $selectFam = $('#np-familia');
                if ($selectFam.find(`option[value="${selectedFamilia.id}"]`).length === 0) {
                    $selectFam.append(new Option(selectedFamilia.nombre, selectedFamilia.id, true, true));
                } else {
                    $selectFam.val(selectedFamilia.id);
                    $selectFam.find(`option[value="${selectedFamilia.id}"]`).text(selectedFamilia.nombre);
                }

                // Asegurar que hay un input hidden para subfamilia
                if ($('#np-subfamilia').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'np-subfamilia',
                        name: 'subfamilia_id',
                        value: selectedSubfamilia ? selectedSubfamilia.id : ''
                    }).appendTo('form'); // agrega al primer form en la página
                } else {
                    $('#np-subfamilia').val(selectedSubfamilia ? selectedSubfamilia.id : '');
                }

                // Si usas select2, disparar cambio
                if ($selectFam.hasClass('select2-hidden-accessible')) {
                    $selectFam.trigger('change.select2');
                }

                toastSuccess('Familia y subfamilia aplicadas');
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('familiaSubfamiliaModal'));
                modal.hide();
            }

            // Initialize
            $(function() {
                // Prevenir que el select se abra
                $('#np-familia').on('mousedown keydown', function(e) {
                    e.preventDefault();
                    return false;
                });

                // Abrir modal desde el botón existente #np-fam-add o el overlay del select
                $('#np-fam-add, .select-overlay').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Reset estado
                    selectedFamilia = null;
                    selectedSubfamilia = null;
                    $('#fam-filter').val('');
                    $('#subfam-filter').val('');
                    $('#familia-subfam-selected').text('—');
                    // Cargar familias
                    fetchFamilias();
                    renderSubfamilias([]);
                    const modalEl = document.getElementById('familiaSubfamiliaModal');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                });

                // Filtros
                let famTimeout, subfamTimeout;
                $('#fam-filter').on('input', function() {
                    clearTimeout(famTimeout);
                    const q = $(this).val();
                    famTimeout = setTimeout(() => fetchFamilias(q), 250);
                });

                $('#subfam-filter').on('input', function() {
                    clearTimeout(subfamTimeout);
                    const q = $(this).val();
                    if (selectedFamilia) {
                        subfamTimeout = setTimeout(() => fetchSubfamilias(selectedFamilia.id, q), 250);
                    }
                });

                // Crear familia desde modal
                $('#fam-create-btn').on('click', function() {
                    const modalEl = document.getElementById('familiaSubfamiliaModal');
                    // obtener instancia de bootstrap Modal (si no existe, crear una temporal)
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(
                        modalEl);
                    const wasShown = modalEl.classList.contains('show');

                    // ocultar modal para que Swal pueda recibir foco
                    if (wasShown) {
                        modalInstance.hide();
                    }

                    Swal.fire({
                        title: 'Crear nueva familia',
                        input: 'text',
                        inputLabel: 'Nombre de la familia',
                        inputPlaceholder: 'Ej. NUTRIENTES',
                        showCancelButton: true,
                        confirmButtonText: 'Crear',
                        // enfocar input cuando Swal abre (ayuda en algunos casos)
                        didOpen: () => {
                            const input = Swal.getInput();
                            if (input) input.focus();
                        },
                        preConfirm: async (val) => {
                            if (!val || !val.trim()) {
                                Swal.showValidationMessage('El nombre es obligatorio');
                                return false;
                            }

                            try {
                                const response = await $.ajax({
                                    url: URLS
                                        .familiasStore, // asegúrate que URLS.familiasStore === '/familias'
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                            .attr('content')
                                    },
                                    data: {
                                        nombre: val.trim()
                                    }
                                });
                                // devolver respuesta para then()
                                return response;
                            } catch (xhr) {
                                // Si Laravel devuelve 422 con errores, mostrarlos en Swal
                                if (xhr && xhr.status === 422 && xhr.responseJSON && xhr
                                    .responseJSON.errors) {
                                    // concatenar mensajes o mostrar el primero
                                    const errs = xhr.responseJSON.errors;
                                    let msgs = [];
                                    Object.keys(errs).forEach(k => msgs = msgs.concat(errs[
                                        k]));
                                    Swal.showValidationMessage(msgs.join('<br>'));
                                    return false; // evita cerrar el modal si hay validación
                                }
                                // otro error
                                Swal.showValidationMessage('Error al crear la familia');
                                return false;
                            }
                        },
                        // al cerrar Swal, si el modal estaba abierto, reabrirlo
                        willClose: () => {
                            if (wasShown) {
                                // esperar un tick para evitar conflictos de z-index
                                setTimeout(() => modalInstance.show(), 50);
                            }
                        }
                    }).then(result => {
                        if (result.isConfirmed && result.value) {
                            toastSuccess('Familia creada');
                            // recargar lista de familias (asegúrate que cargarFamilias o fetchFamilias existe)
                            if (typeof cargarFamilias === 'function') {
                                cargarFamilias();
                            } else if (typeof fetchFamilias === 'function') {
                                fetchFamilias();
                            } else {
                                // alternativa: emitir evento o recargar manualmente
                                console.warn(
                                    'Función cargarFamilias o fetchFamilias no encontrada.');
                            }
                        }
                    });
                });
                // Crear subfamilia desde modal (requiere familia seleccionada)
                $('#subfam-create-btn').on('click', function() {
                    if (!selectedFamilia) {
                        Swal.fire('Atención', 'Seleccione primero una familia', 'warning');
                        return;
                    }

                    const modalEl = document.getElementById('familiaSubfamiliaModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(
                        modalEl);
                    const wasShown = modalEl.classList.contains('show');

                    // ocultar modal para que Swal pueda recibir foco
                    if (wasShown) modalInstance.hide();

                    Swal.fire({
                        title: `Crear subfamilia para "${selectedFamilia.nombre}"`,
                        input: 'text',
                        inputLabel: 'Nombre de la subfamilia',
                        inputPlaceholder: 'Ej. MAIZ',
                        showCancelButton: true,
                        confirmButtonText: 'Crear',
                        didOpen: () => {
                            const input = Swal.getInput();
                            if (input) input.focus();
                        },
                        preConfirm: async (value) => {
                            if (!value || !value.trim()) {
                                Swal.showValidationMessage('El nombre es requerido');
                                return false;
                            }

                            try {
                                // Si tienes la función createSubfamilia que hace $.ajax, úsala:
                                // const resp = await createSubfamilia(selectedFamilia.id, value.trim());

                                // O hacer la llamada AJAX directa aquí:
                                const resp = await $.ajax({
                                    url: URLS
                                        .subfamiliasStore, // asegúrate que URLS.subfamiliasStore === '/subfamilias'
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                            .attr('content')
                                    },
                                    data: {
                                        familia_id: selectedFamilia.id,
                                        nombre: value.trim()
                                    }
                                });

                                return resp; // lo recibirá then()
                            } catch (xhr) {
                                // Mostrar errores de validación (Laravel 422)
                                if (xhr && xhr.status === 422 && xhr.responseJSON && xhr
                                    .responseJSON.errors) {
                                    const errs = xhr.responseJSON.errors;
                                    let msgs = [];
                                    Object.keys(errs).forEach(k => msgs = msgs.concat(errs[
                                        k]));
                                    Swal.showValidationMessage(msgs.join('<br>'));
                                    return false;
                                }

                                Swal.showValidationMessage('Error al crear la subfamilia');
                                return false;
                            }
                        },
                        willClose: () => {
                            // reabrir modal si estaba abierto antes
                            if (wasShown) {
                                setTimeout(() => modalInstance.show(), 50);
                            }
                        }
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        const data = result.value;
                        if (!data) return;

                        // data = respuesta del servidor con la subfamilia creada o existente
                        selectedSubfamilia = data;
                        // refrescar lista de subfamilias para la familia actual
                        if (typeof fetchSubfamilias === 'function') {
                            fetchSubfamilias(selectedFamilia.id);
                        } else {
                            // si tienes otra función para recargar, úsala
                            console.warn(
                                'fetchSubfamilias no definida: actualiza manualmente la lista.'
                            );
                        }
                        updateSelectedLabel();
                        highlightSelected();
                        toastSuccess('Subfamilia creada');
                    });
                });
                // Aplicar selección al formulario
                $('#fam-subfam-apply').on('click', function() {
                    applySelectionToForm();
                });
            });

        })(jQuery);



        ///step1

        // product-quick-next.js
        (function($) {
            'use strict';

            $('#np-next').on('click', function(e) {
                e.preventDefault();

                // Validación cliente mínima
                const nombre = $('#np-nombre').val();
                if (!nombre || !nombre.trim()) {
                    Swal.fire('Atención', 'El nombre es obligatorio', 'warning');
                    return;
                }

                // Crear form dinámico para POST (incluye CSRF)
                const form = $('<form>', {
                    method: 'POST',
                    action: '{{ env('APP_URL') }}/productos/quick-create/step2'
                });

                // CSRF token
                const token = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: token
                }));

                // Campos a enviar (ajusta nombres según tu validación en controller)
                const map = {
                    laboratorio_id: '#np-laboratorio',
                    familia_id: '#np-familia',
                    // si usas select2 y guarda id en value, está bien
                    subfamilia_id: '#np-subfamilia',
                    nombre: '#np-nombre',
                    marca_id: '#np-marca',
                    unidad_medida_id: '#np-unidad',
                    tipo_impuesto: '#np-tipo-impuesto',
                    condicion_venta: '#np-condicion-venta'
                };

                Object.keys(map).forEach(k => {
                    const selector = map[k];
                    const $el = $(selector);
                    if ($el.length) {
                        form.append($('<input>', {
                            type: 'hidden',
                            name: k,
                            value: $el.val() ?? ''
                        }));
                    }
                });

                // checkboxes (atributos stock)
                const checkboxes = {
                    attr_numero_serie: '#np-attr-serie',
                    attr_fecha_vencimiento: '#np-attr-fecha-venc',
                    attr_lote_produccion: '#np-attr-lote',
                    attr_venta_menudeo: '#np-attr-venta-menudeo',
                    opciones_avanzadas: '#np-op-avanzadas'
                };
                Object.keys(checkboxes).forEach(k => {
                    const sel = checkboxes[k];
                    const $c = $(sel);
                    form.append($('<input>', {
                        type: 'hidden',
                        name: k,
                        value: $c.is(':checked') ? 1 : 0
                    }));
                });

                // Añadir el form al body y enviar
                form.appendTo('body').submit();
            });
        })(jQuery);
    </script>
@endsection

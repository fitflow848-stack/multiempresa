@extends('layout.app')

@section('title', 'Nueva Compra')
@section('page-title', 'Registrar Compra')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
    <li class="breadcrumb-item active">Nueva Compra</li>
@endsection

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet">



@section('content')
    <!-- Step Indicator -->
    <form method="POST" action="{{ route('compras.store') }}" id="compra-form">
        @csrf
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Datos del Comprobante -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice mr-2"></i>
                            Datos del Comprobante
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proveedor_select">
                                        <i class="fas fa-truck mr-1"></i>
                                        Proveedor <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <select id="proveedor_select" name="proveedor_id" class="form-control col-8"
                                            required></select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#proveedorModal" title="Nuevo Proveedor">
                                                <i class='bx  bx-plus'></i> 
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-list mr-1"></i>
                                        Presupuesto
                                    </label>
                                    <select name="presupuesto" class="form-control">
                                        <option value="Compra">Compra</option>
                                        <option value="Servicio">Servicio</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-receipt mr-1"></i>
                                        Tipo de Documento
                                    </label>
                                    <select name="tipo" class="form-control">
                                        <option value="Ticket">Ticket</option>
                                        <option value="Factura">Factura</option>
                                        <option value="Boleta">Boleta</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-calendar mr-1"></i>
                                        Fecha de Emisión <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="fecha_emision" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-calendar-check mr-1"></i>
                                        Fecha de Pago
                                    </label>
                                    <input type="date" name="fecha_pago" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Opciones -->
                        <div class="options-card p-3 rounded">
                            <h6 class="mb-3">
                                <i class="fas fa-cog mr-2"></i>
                                Opciones de Compra
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label mb-2">Moneda</label>
                                    <div class="currency-toggle">
                                        <input type="radio" class="btn-check" name="moneda" value="sol" id="sol"
                                            checked>
                                        <label class="btn btn-outline-primary" for="sol">
                                            <i class="fas fa-coins mr-1"></i> Soles
                                        </label>

                                        <input type="radio" class="btn-check" name="moneda" value="usd"
                                            id="usd">
                                        <label class="btn btn-outline-primary" for="usd">
                                            <i class="fas fa-dollar-sign mr-1"></i> Dólares
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label mb-2">Condiciones</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="credito" id="credito">
                                            <label class="form-check-label" for="credito">
                                                <i class="fas fa-credit-card mr-1"></i> Crédito
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="percepcion"
                                                id="percepcion">
                                            <label class="form-check-label" for="percepcion">
                                                <i class="fas fa-percentage mr-1"></i> Percepción
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="inc_impuesto"
                                                id="inc_impuesto">
                                            <label class="form-check-label" for="inc_impuesto">
                                                <i class="fas fa-calculator mr-1"></i> Inc. Impuesto
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-boxes mr-2"></i>
                            Productos
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#productSearchModal">
                                <i class="fas fa-plus mr-1"></i> Agregar Producto
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped product-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th style="width: 100px">Código</th>
                                        <th>Descripción</th>
                                        <th style="width: 100px" class="text-center">Cantidad</th>
                                        <th style="width: 100px" class="text-right">Costo</th>
                                        <th style="width: 100px" class="text-right">Descuento</th>
                                        <th style="width: 80px" class="text-center">Stock Min</th>
                                        <th style="width: 80px" class="text-center">Stock Max</th>
                                        <th style="width: 100px" class="text-center">Lote</th>
                                        <th style="width: 120px" class="text-center">F. Vencimiento</th>
                                        <th style="width: 100px" class="text-right">Total</th>
                                        <th style="width: 60px" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="productos-tbody">
                                    <tr id="no-products" class="text-center text-muted">
                                        <td colspan="12" class="py-4">
                                            <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                            No hay productos agregados.
                                            <a href="#" class="text-success" data-bs-toggle="modal"
                                                data-bs-target="#productSearchModal">Agregar el primero</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Resumen -->
                <div class="card card-warning card-outline sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calculator mr-2"></i>
                            Resumen de Compra
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="summary-card p-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotal-display">S/ 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Descuento:</span>
                                <span id="descuento-display">S/ 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Impuestos:</span>
                                <span id="impuestos-display">S/ 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between total-amount p-2 rounded">
                                <span>TOTAL:</span>
                                <span id="total-display">S/ 0.00</span>
                            </div>
                        </div>

                        <!-- Hidden inputs for form -->
                        <input type="hidden" name="total_bruto" id="total_bruto" value="0.00">
                        <input type="hidden" name="total_descuento" id="total_descuento" value="0.00">
                        <input type="hidden" name="bruto_neto" id="bruto_neto" value="0.00">
                        <input type="hidden" name="total_impuesto" id="total_impuesto" value="0.00">
                        <input type="hidden" name="total_neto" id="total_neto" value="0.00">

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Compra
                            </button>
                            <a href="{{ route('compras.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Volver al Listado
                            </a>
                            <button type="button" class="btn btn-outline-warning btn-sm" id="clear-temp-data">
                                <i class="fas fa-eraser mr-1"></i>
                                Limpiar Datos Temporales
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Estadísticas Rápidas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-shopping-cart"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Compras este Mes</span>
                                <span class="info-box-number">24</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-boxes"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Productos en esta Compra</span>
                                <span class="info-box-number" id="productos-count">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modals -->
    @include('compras.partials.modal-proveedor')
    @include('compras.partials.modal-producto-search')
    @include('compras.partials.modal-product-detail')


    {{-- ===================== SCRIPTS ===================== --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Estilos para los nuevos campos editables */
        .stock-min-input, .stock-max-input {
            background-color: #f0f8f0 !important;
        }
        
        .lote-input, .fecha-vencimiento-input {
            background-color: #fef9e7 !important;
        }
        
        .stock-min-input:focus, .stock-max-input:focus {
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.3) !important;
        }
        
        .lote-input:focus, .fecha-vencimiento-input:focus {
            box-shadow: 0 0 5px rgba(255, 193, 7, 0.3) !important;
        }
        
        /* Validación visual para errores */
        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #f8d7da !important;
        }
        
        .is-valid {
            border-color: #28a745 !important;
        }
        
        /* Responsividad para tabla más ancha */
        .table-responsive {
            min-height: 200px;
        }
        
        .product-table th, .product-table td {
            white-space: nowrap;
            vertical-align: middle;
        }
        
        .product-table input[type="text"], 
        .product-table input[type="number"], 
        .product-table input[type="date"] {
            min-width: 70px;
            font-size: 12px;
        }
        
        /* Tooltips para campos nuevos */
        .stock-min-input, .stock-max-input {
            position: relative;
        }
        
        .lote-input[title]:hover, .fecha-vencimiento-input[title]:hover {
            cursor: help;
        }
    </style>

    <script>
        $(document).ready(function() {
            // CSRF setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const apiRucUrl = '{{ route('apidocumento.ruc') }}';
            const proveedoresSelectUrl = '{{ route('proveedores.select') }}';
            const proveedoresStoreUrl = '{{ route('proveedores.store') }}';

            // Initialize Select2 with error handling
            if (typeof $.fn.select2 !== 'undefined') {
                $('#proveedor_select').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Buscar proveedor...',
                    allowClear: true,
                    width: '100%',
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
                                        text: (p.nombre_comercial || p.nombre_legal || p.ruc)
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                    // allow clearing the initial selection
                    templateResult: function (data) { return data.text; }
                });

                // Load an initial page of providers when opening the select so user sees a list immediately
                $('#proveedor_select').on('select2:open', function() {
                    const $this = $(this);
                    if ($this.data('providersLoaded')) return;

                    $.get(proveedoresSelectUrl, { q: '' })
                        .done(function(items) {
                            if (!Array.isArray(items)) return;
                            items.forEach(function(p) {
                                const text = p.nombre_comercial || p.nombre_legal || p.ruc;
                                // avoid duplicating options
                                if ($this.find('option[value="' + p.id + '"]').length === 0) {
                                    const newOption = new Option(text, p.id, false, false);
                                    $this.append(newOption);
                                }
                            });
                            $this.data('providersLoaded', true);
                            // reopen dropdown content is already open
                        })
                        .fail(function() {
                            console.warn('No se pudo cargar listado inicial de proveedores');
                        });
                });
                console.log('Select2 initialized successfully');
            } else {
                console.error('Select2 is not loaded!');
            }

            // RUC search functionality
            $(document).on('click', '#btn-ruc', function() {
                const ruc = $('#ruc').val().trim();
                if (!/^\d{11}$/.test(ruc)) {
                    Swal.fire('Error', 'RUC debe tener 11 dígitos', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Buscando...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.post(apiRucUrl, {
                        documento: ruc
                    })
                    .done(function(resp) {
                        Swal.close();
                        if (resp.razonSocial || resp.nombre) {
                            $('#nombre_comercial').val(resp.razonSocial || resp.nombre);
                            $('#nombre_legal').val(resp.nombreLegal || resp.razonSocial);
                            $('#direccion').val(resp.direccion || '');
                        } else {
                            Swal.fire('No encontrado', 'RUC no existe', 'error');
                        }
                    })
                    .fail(function() {
                        Swal.close();
                        Swal.fire('Error', 'Falla en el servicio RUC', 'error');
                    });
            });

            // Save provider
            $(document).on('submit', '#proveedor-form', function(e) {
                e.preventDefault();
                const $form = $(this);

                $.post(proveedoresStoreUrl, $form.serialize())
                    .done(function(resp) {
                        if (resp && resp.id) {
                            const text = resp.nombre_comercial || resp.nombre_legal || resp.ruc;
                            const newOption = new Option(text, resp.id, true, true);
                            $('#proveedor_select').append(newOption).trigger('change');

                            // Bootstrap 5 modal hide using getOrCreateInstance and cleanup after hidden
                            const modalEl = document.getElementById('proveedorModal');
                            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

                            const onHidden = function() {
                                modalEl.removeEventListener('hidden.bs.modal', onHidden);
                                // reset form after fully hidden
                                $form[0].reset();

                                // cleanup any leftover backdrops or classes
                                (function cleanup() {
                                    const anyShown = document.querySelectorAll('.modal.show').length > 0;
                                    if (anyShown) return;
                                    document.querySelectorAll('.modal-backdrop').forEach(function(el) {
                                        el.parentNode && el.parentNode.removeChild(el);
                                    });
                                    document.body.classList.remove('modal-open');
                                    document.body.style.paddingRight = '';
                                })();

                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Proveedor registrado!',
                                    text: 'El proveedor se ha añadido correctamente',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            };

                            modalEl.addEventListener('hidden.bs.modal', onHidden);
                            modalInstance.hide();
                        }
                    })
                    .fail(function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Error al guardar';
                        Swal.fire('Error', msg, 'error');
                    });
            });

            // Calculate totals function
            function calculateTotals() {
                let subtotal = 0;
                let descuento = 0;
                let impuestos = 0;
                let productCount = 0;

                $('#productos-tbody tr:not(#no-products)').each(function() {
                    const cantidad = parseFloat($(this).find('.cantidad-input').val()) || 0;
                    const costo = parseFloat($(this).find('.costo-input').val()) || 0;
                    const desc = parseFloat($(this).find('.descuento-input').val()) || 0;

                    const lineTotal = (cantidad * costo) - desc;
                    subtotal += lineTotal;
                    descuento += desc;
                    productCount++;
                });

                // Update displays
                $('#subtotal-display').text('S/ ' + subtotal.toFixed(2));
                $('#descuento-display').text('S/ ' + descuento.toFixed(2));
                $('#impuestos-display').text('S/ ' + impuestos.toFixed(2));
                $('#total-display').text('S/ ' + subtotal.toFixed(2));
                $('#productos-count').text(productCount);

                // Update hidden inputs
                $('#total_bruto').val(subtotal.toFixed(2));
                $('#total_descuento').val(descuento.toFixed(2));
                $('#total_impuesto').val(impuestos.toFixed(2));
                $('#total_neto').val(subtotal.toFixed(2));

                // Show/hide no products message
                if (productCount === 0) {
                    $('#no-products').show();
                } else {
                    $('#no-products').hide();
                }
            }

            // Make function globally available
            window.calculateTotals = calculateTotals;

            // Form validation
            $('#compra-form').on('submit', function(e) {
                const proveedorId = $('#proveedor_select').val();
                const productCount = $('#productos-tbody tr:not(#no-products)').length;

                if (!proveedorId) {
                    e.preventDefault();
                    Swal.fire('Error', 'Debe seleccionar un proveedor', 'warning');
                    return;
                }

                if (productCount === 0) {
                    e.preventDefault();
                    Swal.fire('Error', 'Debe agregar al menos un producto', 'warning');
                    return;
                }
            });

            // Quick create product
            $(document).on('click', '#btn-open-quick-create', function() {
                // Guardar temporalmente los datos del formulario de compra
                saveCompraDataToSession();
            });

            // Auto-guardar datos del formulario automáticamente
            function autoSaveCompraData() {
                const formData = {
                    proveedor_id: $('#proveedor_select').val(),
                    proveedor_text: $('#proveedor_select').find('option:selected').text(),
                    presupuesto: $('select[name="presupuesto"]').val(),
                    tipo: $('select[name="tipo"]').val(),
                    fecha_emision: $('input[name="fecha_emision"]').val(),
                    fecha_pago: $('input[name="fecha_pago"]').val(),
                    moneda: $('input[name="moneda"]:checked').val(),
                    credito: $('#credito').is(':checked'),
                    percepcion: $('#percepcion').is(':checked'),
                    inc_impuesto: $('#inc_impuesto').is(':checked'),
                    productos: getProductsData(),
                    timestamp: new Date().getTime()
                };

                localStorage.setItem('temp_compra_data', JSON.stringify(formData));
                console.log('Auto-guardado realizado:', formData);
            }

            // Hacer disponible globalmente para el modal de productos
            window.autoSaveCompraData = autoSaveCompraData;

            // Configurar auto-guardado en tiempo real
            function setupAutoSave() {
                // Auto-guardar cuando cambien los campos del formulario
                $('#proveedor_select, select[name="presupuesto"], select[name="tipo"], input[name="fecha_emision"], input[name="fecha_pago"]')
                    .on('change', debounce(autoSaveCompraData, 500));
                
                // Auto-guardar cuando cambien las opciones
                $('input[name="moneda"], #credito, #percepcion, #inc_impuesto')
                    .on('change', debounce(autoSaveCompraData, 500));
                
                // Auto-guardar cuando cambien los productos (incluyendo nuevos campos)
                $(document).on('input change', '.cantidad-input, .costo-input, .descuento-input, .stock-min-input, .stock-max-input, .lote-input, .fecha-vencimiento-input', 
                    debounce(autoSaveCompraData, 1000));

                // Validaciones para stock mínimo y máximo
                $(document).on('input', '.stock-min-input, .stock-max-input', function() {
                    const $input = $(this);
                    const value = parseInt($input.val()) || 0;
                    
                    if (value < 0) {
                        $input.val(0);
                    }
                    
                    // Validar que stock_max >= stock_min en la misma fila
                    const $row = $input.closest('tr');
                    const stockMin = parseInt($row.find('.stock-min-input').val()) || 0;
                    const stockMax = parseInt($row.find('.stock-max-input').val()) || 0;
                    
                    if (stockMax > 0 && stockMax < stockMin) {
                        $row.find('.stock-max-input').css('border-color', '#dc3545');
                        $row.find('.stock-min-input').css('border-color', '#dc3545');
                    } else {
                        $row.find('.stock-max-input').css('border-color', '#28a745');
                        $row.find('.stock-min-input').css('border-color', '#28a745');
                    }
                });

                // Validación para lote (solo caracteres alfanuméricos, guiones y puntos)
                $(document).on('input', '.lote-input', function() {
                    const $input = $(this);
                    let value = $input.val();
                    
                    // Remover caracteres especiales excepto guiones y puntos
                    value = value.replace(/[^a-zA-Z0-9\-\.]/g, '');
                    $input.val(value);
                });

                // Validación para fecha de vencimiento
                $(document).on('change', '.fecha-vencimiento-input', function() {
                    const $input = $(this);
                    const fechaIngresada = new Date($input.val());
                    const hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);
                    
                    if ($input.val() && fechaIngresada < hoy) {
                        $input.css('border-color', '#ffc107');
                        // Mostrar advertencia
                        $input.attr('title', 'Advertencia: La fecha de vencimiento es anterior a hoy');
                    } else {
                        $input.css('border-color', '');
                        $input.removeAttr('title');
                    }
                });
            }

            // Función debounce para evitar guardado excesivo
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = function() {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Función para guardar datos de compra en sessionStorage
            function saveCompraDataToSession() {
                // Guardar en localStorage (ya se hace automáticamente)
                autoSaveCompraData();
                
                // Marcar en sesión del servidor que debe regresar a compras
                $.post('{{ route('session.store') }}', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    key: 'return_to_compras',
                    value: true
                }).done(function() {
                    console.log('Sesión guardada, navegando...');
                    window.location.href = '{{ route('productos.step1') }}';
                }).fail(function() {
                    console.error('Error guardando sesión');
                    Swal.fire('Error', 'No se pudo guardar la información temporal', 'error');
                });
            }

            // Función para obtener datos de productos actuales
            function getProductsData() {
                const productos = [];
                $('#productos-tbody tr:not(#no-products)').each(function() {
                    const $row = $(this);
                    productos.push({
                        linea_id: $row.data('linea-id') || '',
                        producto_id: $row.find('input[name="product_id[]"]').val() || '',
                        codigo: $row.find('input[name="codigo[]"]').val() || '',
                        descripcion: $row.find('input[name="descripcion[]"]').val() || '',
                        cantidad: $row.find('input[name="cantidad[]"]').val() || '1',
                        costo: $row.find('input[name="costo[]"]').val() || '0.00',
                        descuento: $row.find('input[name="descuento[]"]').val() || '0.00',
                        stock_min: $row.find('input[name="stock_min[]"]').val() || '0',
                        stock_max: $row.find('input[name="stock_max[]"]').val() || '0',
                        lote: $row.find('input[name="lote[]"]').val() || '',
                        fecha_vencimiento: $row.find('input[name="fecha_vencimiento[]"]').val() || '',
                        total: $row.find('.total-line').text().trim() || 'S/ 0.00'
                    });
                });
                console.log('Productos obtenidos:', productos);
                return productos;
            }

            // Hacer disponible globalmente
            window.getProductsData = getProductsData;

            // Función para restaurar datos de compra
            function restoreCompraData() {
                const tempData = localStorage.getItem('temp_compra_data');
                if (tempData) {
                    try {
                        const data = JSON.parse(tempData);
                        console.log('Restaurando datos:', data);
                        
                        // Restaurar campos del formulario
                        if (data.proveedor_id && data.proveedor_text) {
                            const option = new Option(data.proveedor_text, data.proveedor_id, true, true);
                            $('#proveedor_select').append(option).trigger('change');
                        }
                        
                        $('select[name="presupuesto"]').val(data.presupuesto);
                        $('select[name="tipo"]').val(data.tipo);
                        $('input[name="fecha_emision"]').val(data.fecha_emision);
                        $('input[name="fecha_pago"]').val(data.fecha_pago);
                        $('input[name="moneda"][value="' + data.moneda + '"]').prop('checked', true);
                        $('#credito').prop('checked', data.credito);
                        $('#percepcion').prop('checked', data.percepcion);
                        $('#inc_impuesto').prop('checked', data.inc_impuesto);
                        
                        // Restaurar productos con validación mejorada
                        console.log('Productos a restaurar:', data.productos);
                        if (data.productos && Array.isArray(data.productos) && data.productos.length > 0) {
                            // Procesar productos secuencialmente y, si falta linea_id, intentar obtenerla por API
                            (async function() {
                                for (let index = 0; index < data.productos.length; index++) {
                                    const producto = data.productos[index];
                                    console.log(`Restaurando producto ${index + 1}:`, producto);

                                    const productoCompleto = {
                                        linea_id: producto.linea_id || '',
                                        producto_id: producto.producto_id || '',
                                        codigo: producto.codigo || '',
                                        descripcion: producto.descripcion || '',
                                        cantidad: producto.cantidad || '1',
                                        costo: producto.costo || '0.00',
                                        descuento: producto.descuento || '0.00',
                                        stock_min: producto.stock_min || '0',
                                        stock_max: producto.stock_max || '0',
                                        lote: producto.lote || '',
                                        fecha_vencimiento: producto.fecha_vencimiento || '',
                                        total: producto.total || 'S/ 0.00'
                                    };

                                    // Si no tenemos linea_id pero sí producto_id, intentar obtener la primera linea por API
                                    if ((!productoCompleto.linea_id || productoCompleto.linea_id === '') && productoCompleto.producto_id) {
                                        try {
                                            const apiUrl = '{{ route("productos.api.get", ":id") }}'.replace(':id', productoCompleto.producto_id);
                                            const resp = await fetch(apiUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json());
                                            const primeraLinea = resp.lineas && resp.lineas.length > 0 ? resp.lineas[0] : null;
                                            productoCompleto.linea_id = primeraLinea ? primeraLinea.id : productoCompleto.linea_id || '';

                                            // rellenar valores faltantes desde la API si es necesario
                                            if ((!productoCompleto.codigo || productoCompleto.codigo === '') && primeraLinea) {
                                                productoCompleto.codigo = primeraLinea.cb || productoCompleto.codigo || '';
                                            }
                                            if ((!productoCompleto.costo || Number(productoCompleto.costo) === 0) && primeraLinea) {
                                                productoCompleto.costo = primeraLinea.precio_compra || productoCompleto.costo || 0;
                                            }
                                        } catch (err) {
                                            console.error('Error al obtener producto desde API para restauración', err);
                                        }
                                    }

                                    // Llamar función de debug antes de agregar
                                    if (window.debugProductData) {
                                        window.debugProductData(productoCompleto);
                                    }

                                    addProductToTable(productoCompleto);
                                }
                                console.log(`Se restauraron ${data.productos.length} productos exitosamente`);
                            })();
                        } else {
                            console.log('No hay productos para restaurar');
                        }
                        
                        console.log('Datos restaurados exitosamente');
                        return true;
                    } catch (error) {
                        console.error('Error al restaurar datos:', error);
                        // Si hay error, limpiar datos corruptos
                        localStorage.removeItem('temp_compra_data');
                        return false;
                    }
                }
                console.log('No hay datos temporales para restaurar');
                return false;
            }

            // Función para agregar producto a la tabla
            function addProductToTable(producto) {
                console.log('Agregando producto a tabla:', producto);
                
                const idx = $('#productos-tbody tr:not(#no-products)').length + 1;
                
                // Ocultar mensaje "No hay productos"
                $('#no-products').hide();
                
                const totalCalculado = (Number(producto.cantidad || 1) * Number(producto.costo || 0) - Number(producto.descuento || 0)).toFixed(2);
                
                const row = `
                    <tr data-idx="${idx}" data-linea-id="${producto.linea_id || ''}" data-producto-id="${producto.producto_id || ''}">
                        <td class="text-center">${idx}
                            <input type="hidden" name="product_id[]" value="${producto.producto_id || ''}">
                            <input type="hidden" name="linea_id[]" value="${producto.linea_id || ''}">
                        </td>
                        <td>
                            <input name="codigo[]" type="text" class="form-control form-control-sm" 
                                   value="${producto.codigo || ''}" readonly>
                        </td>
                        <td>
                            <input name="descripcion[]" type="text" class="form-control form-control-sm" 
                                   value="${producto.descripcion || ''}" readonly>
                        </td>
                        <td>
                            <input name="cantidad[]" type="number" step="1" min="1" 
                                   class="form-control form-control-sm text-center cantidad-input" 
                                   value="${producto.cantidad || 1}">
                        </td>
                        <td>
                            <input name="costo[]" type="number" step="0.01" min="0" 
                                   class="form-control form-control-sm text-end costo-input" 
                                   value="${Number(producto.costo || 0).toFixed(2)}">
                        </td>
                        <td>
                            <input name="descuento[]" type="number" step="0.01" min="0" 
                                   class="form-control form-control-sm text-end descuento-input" 
                                   value="${Number(producto.descuento || 0).toFixed(2)}">
                        </td>
                        <td>
                            <input name="stock_min[]" type="number" step="1" min="0" 
                                   class="form-control form-control-sm text-center stock-min-input" 
                                   value="${producto.stock_min || 0}" placeholder="0">
                        </td>
                        <td>
                            <input name="stock_max[]" type="number" step="1" min="0" 
                                   class="form-control form-control-sm text-center stock-max-input" 
                                   value="${producto.stock_max || 0}" placeholder="0">
                        </td>
                        <td>
                            <input name="lote[]" type="text" maxlength="50" 
                                   class="form-control form-control-sm text-center lote-input" 
                                   value="${producto.lote || ''}" placeholder="Lote...">
                        </td>
                        <td>
                            <input name="fecha_vencimiento[]" type="date" 
                                   class="form-control form-control-sm fecha-vencimiento-input" 
                                   value="${producto.fecha_vencimiento || ''}">
                        </td>
                        <td class="text-end total-line">S/ ${totalCalculado}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" title="Eliminar">
                                <i class='bx bx-x-circle'></i> 
                            </button>
                        </td>
                    </tr>
                `;

                $('#productos-tbody').append(row);
                calculateTotals();
                
                // Auto-guardar después de agregar producto
                setTimeout(autoSaveCompraData, 500);
                
                console.log('Producto agregado exitosamente a la tabla');
            }

            // Función para actualizar numeración de filas
            function updateRowNumbers() {
                $('#productos-tbody tr:not(#no-products)').each(function(index) {
                    $(this).attr('data-idx', index + 1);
                    $(this).find('td:eq(0)').text(index + 1); // Primera columna (#)
                });
            }

            // NUEVO: Verificar automáticamente si hay datos temporales guardados
            // Esto se ejecuta siempre al cargar la página, sin importar cómo se llegue
            $(document).ready(function() {
                const tempData = localStorage.getItem('temp_compra_data');
                const urlParams = new URLSearchParams(window.location.search);
                const isFromSession = @json(session('restore_compra_data'));
                const isFromURL = urlParams.get('restore_compra_data') === 'true';
                
                console.log('Verificando datos temporales al cargar:', {
                    tempData: !!tempData,
                    isFromSession: isFromSession,
                    isFromURL: isFromURL
                });
                
                if (tempData) {
                    if (isFromSession || isFromURL) {
                        // Restauración automática (viene de productos)
                        setTimeout(function() {
                            if (restoreCompraData()) {
                                // Si hay un nuevo producto creado, agregarlo automáticamente
                                const newProductId = @json(session('new_product_id')) || urlParams.get('new_product_id');
                                if (newProductId) {
                                    fetchAndAddNewProduct(parseInt(newProductId));
                                }
                                
                                // Limpiar parámetros URL si existen
                                if (isFromURL) {
                                    const cleanUrl = window.location.origin + window.location.pathname;
                                    window.history.replaceState({}, document.title, cleanUrl);
                                }
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Datos restaurados',
                                    text: 'Se han restaurado los datos de la compra anterior',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        }, 500);
                    } else {
                        // Navegación normal - restaurar silenciosamente
                        setTimeout(function() {
                            if (restoreCompraData()) {
                                console.log('Datos restaurados silenciosamente');
                            }
                        }, 300);
                    }
                }
                
                // Configurar auto-guardado después de la restauración
                setTimeout(function() {
                    setupAutoSave();
                    
                    // Forzar un primer auto-guardado si hay datos
                    if ($('#productos-tbody tr:not(#no-products)').length > 0 || $('#proveedor_select').val()) {
                        setTimeout(autoSaveCompraData, 1000);
                        console.log('Auto-guardado inicial forzado');
                    }
                }, 1000);
            });

            // Verificar si debe restaurar datos al cargar la página (casos especiales)
            @if(session('restore_compra_data'))
                // Ya manejado arriba
            @endif
            
            // También verificar parámetros URL para restauración (cuando viene de AJAX)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('restore_compra_data') === 'true') {
                // Ya manejado arriba
            }

            // Event listener para eliminar productos
            $(document).on('click', '.btn-remove-line', function() {
                $(this).closest('tr').remove();
                updateRowNumbers();
                calculateTotals();
                
                // Mostrar mensaje "No hay productos" si no quedan productos
                if ($('#productos-tbody tr:not(#no-products)').length === 0) {
                    $('#no-products').show();
                }
                
                // Auto-guardar después de eliminar producto
                setTimeout(autoSaveCompraData, 500);
            });

            // Event listeners para recalcular totales cuando cambien los inputs (ya incluye auto-save)
            $(document).on('input', '.cantidad-input, .costo-input, .descuento-input', function() {
                calculateTotals();
                // Auto-save ya configurado en setupAutoSave()
            });

            // Event listeners específicos para validar nuevos campos en tiempo real
            $(document).on('input', '.stock-min-input, .stock-max-input, .lote-input', function() {
                // Las validaciones ya están en setupAutoSave(), solo ejecutar auto-save
                // El debounce evitará llamadas excesivas
            });

            $(document).on('change', '.fecha-vencimiento-input', function() {
                // Las validaciones ya están en setupAutoSave()
            });

            // Función de debugging para ver datos del producto
            window.debugProductData = function(producto) {
                console.log('=== DEBUG PRODUCTO ===');
                console.log('Datos recibidos:', producto);
                console.log('Campos individuales:');
                console.log('- ID:', producto.producto_id);
                console.log('- Código:', producto.codigo);
                console.log('- Descripción:', producto.descripcion);
                console.log('- Cantidad:', producto.cantidad);
                console.log('- Costo:', producto.costo);
                console.log('- Stock Min:', producto.stock_min);
                console.log('- Stock Max:', producto.stock_max);
                console.log('- Lote:', producto.lote);
                console.log('- Fecha Venc:', producto.fecha_vencimiento);
                console.log('======================');
                return true;
            };

            // Handler para limpiar datos temporales
            $('#clear-temp-data').on('click', function() {
                const tempData = localStorage.getItem('temp_compra_data');
                if (!tempData) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No hay datos temporales',
                        text: 'No se encontraron datos temporales para eliminar',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }
                
                Swal.fire({
                    title: '¿Limpiar datos temporales?',
                    text: 'Se eliminarán todos los datos guardados temporalmente y se recargará la página',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, limpiar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#f39c12',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        localStorage.removeItem('temp_compra_data');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Datos temporales eliminados',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Recargar la página para empezar limpio
                            window.location.reload();
                        });
                    }
                });
            });

            // Limpiar datos temporales al completar compra exitosamente
            $('#compra-form').on('submit', function() {
                // Dar tiempo a que se procese la compra antes de limpiar
                setTimeout(function() {
                    localStorage.removeItem('temp_compra_data');
                    console.log('Datos temporales limpiados después de enviar compra');
                }, 1000);
            });

            // Función para obtener y agregar producto recién creado
            function fetchAndAddNewProduct(productId) {
                const apiUrl = '{{ route("productos.api.get", ":id") }}'.replace(':id', productId);
                $.get(apiUrl)
                    .done(function(producto) {
                        // Obtener la primera línea del producto o crear una básica
                        const primeraLinea = producto.lineas && producto.lineas.length > 0 ? producto.lineas[0] : null;
                        
                        const newProducto = {
                            linea_id: primeraLinea ? primeraLinea.id : '',
                            producto_id: producto.id,
                            codigo: primeraLinea ? primeraLinea.cb : producto.codigo_barras || '',
                            descripcion: producto.nombre + (primeraLinea ? ' - ' + (primeraLinea.presentacion || '') : ''),
                            cantidad: primeraLinea ? primeraLinea.cantidad : 1,
                            costo: primeraLinea ? primeraLinea.precio_compra : 0,
                            descuento: 0,
                            stock_min: primeraLinea.stock_min ||  0,
                            stock_max: primeraLinea.stock_max || 0,
                            lote: primeraLinea ? primeraLinea.lote : '',
                            fecha_vencimiento: primeraLinea ? primeraLinea.fecha_vencimiento : '',
                            total: 'S/ 0.00'
                        };
                        
                        addProductToTable(newProducto);
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Producto agregado!',
                            text: 'El producto recién creado se ha agregado automáticamente a la compra',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    })
                    .fail(function() {
                        console.error('No se pudo cargar el producto recién creado');
                    });
            }
        });
    </script>

    @include('compras.partials.js.compra-product-search-and-add')
@endsection

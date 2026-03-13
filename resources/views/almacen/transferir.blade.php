@extends('layout.app')

@section('title', 'Transferencia Masiva')
@section('page-title', 'Transferencia Masiva entre Sucursales')

@section('content')
    <style>
        .product-meta {
            font-size: 0.8rem;
            color: #64748b;
        }
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
        }
    </style>

    <div class="container-fluid py-4">
        <form action="{{ route('almacen.transferir.store') }}" method="POST" id="transferForm">
            @csrf
            
            <div class="row">
                <!-- Configuración General -->
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-cog me-2"></i>Configuración</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Sucursal Destino <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg shadow-sm" name="sucursal_destino_id" required>
                                    <option value="">Seleccionar local...</option>
                                    @foreach ($sucursales as $suc)
                                        <option value="{{ $suc->id }}" {{ old('sucursal_destino_id') == $suc->id ? 'selected' : '' }}>
                                            {{ $suc->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Observaciones Generales</label>
                                <textarea class="form-control" name="observaciones" rows="3" placeholder="Opcional...">{{ old('observaciones') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Selector de Productos -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-success"><i class="fas fa-plus-circle me-2"></i>Añadir Producto</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Producto</label>
                                <select class="form-control select2-producto" id="search_producto">
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lote / Stock Origen</label>
                                <select class="form-select" id="search_lote" disabled>
                                    <option value="">Seleccione producto...</option>
                                </select>
                                <div id="stock_info" class="mt-1 small fw-bold text-primary"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="search_cantidad" step="0.01" min="0.01">
                            </div>
                            <button type="button" class="btn btn-success w-100" id="btnAddItem">
                                <i class="fas fa-plus me-1"></i> Agregar a la Lista
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lista de Transferencia -->
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-dark"><i class="fa fa-list me-2"></i>Productos a Transferir</h6>
                            <span class="badge bg-primary rounded-pill px-3" id="itemCount">0 Items</span>
                        </div>
                        <div class="card-body p-0">
                            @if ($errors->any())
                                <div class="alert alert-danger m-3">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="transferTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Producto</th>
                                            <th>Lote Seleccionado</th>
                                            <th class="text-center" style="width: 150px;">Can. Transferir</th>
                                            <th class="text-end pe-4">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsContainer">
                                        <!-- Items dinámicos -->
                                    </tbody>
                                </table>
                            </div>

                            <div id="emptyState" class="text-center py-5">
                                <i class="fas fa-exchange-alt fa-3x text-light mb-3"></i>
                                <h5 class="text-muted fw-light">Aún no has agregado productos a la transferencia</h5>
                            </div>
                        </div>
                        <div class="card-footer bg-white py-4 text-end">
                            <a href="{{ route('almacen.index') }}" class="btn btn-light px-4 me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 py-2 shadow" id="btnSubmit" disabled>
                                <i class="fas fa-check-circle me-2"></i>Confirmar Transferencia Masiva
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let itemIndex = 0;
            const $itemsContainer = $('#itemsContainer');
            const $emptyState = $('#emptyState');
            const $btnSubmit = $('#btnSubmit');
            const $itemCount = $('#itemCount');

            // Select2 para búsqueda de productos
            $('#search_producto').select2({
                theme: 'bootstrap-5',
                placeholder: 'Escriba nombre o código...',
                ajax: {
                    url: '{{ route('api.productos.search') }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.nombre + 
                                          (item.presentacion ? ' ' + item.presentacion : '') + 
                                          (item.concentracion ? ' ' + item.concentracion : '') + 
                                          (item.cb ? ' (' + item.cb + ')' : ''),
                                    id: item.linea_id, // Usamos linea_id para que sea único
                                    producto_id: item.id // Guardamos producto_id para el form
                                }
                            })
                        };
                    }
                }
            });

            // Al seleccionar producto, cargar lotes
            $('#search_producto').on('change', function() {
                const lineaId = $(this).val();
                const selData = $(this).select2('data')[0];
                const productoId = selData ? selData.producto_id : null;
                const $loteSelect = $('#search_lote');
                
                $loteSelect.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
                $('#stock_info').text('');

                if (lineaId) {
                    $.get('{{ route('almacen.api.lotes') }}', { 
                        linea_id: lineaId,
                        producto_id: productoId 
                    }, function(data) {
                        $loteSelect.empty().append('<option value="">Seleccionar lote...</option>');
                        if (data.length > 0) {
                            data.forEach(lote => {
                                $loteSelect.append(`<option value="${lote.id}" data-stock="${lote.stock}" data-text="${lote.text}">${lote.text}</option>`);
                            });
                            $loteSelect.prop('disabled', false);
                        } else {
                            $loteSelect.append('<option value="">Sin stock disponible</option>');
                        }
                    });
                }
            });

            $('#search_lote').on('change', function() {
                const stock = $(this).find(':selected').data('stock');
                $('#stock_info').text(stock ? `Stock disponible: ${stock}` : '');
                $('#search_cantidad').val(stock).attr('max', stock);
            });

            // Agregar item a la lista
            $('#btnAddItem').on('click', function() {
                const prodData = $('#search_producto').select2('data')[0];
                const loteId = $('#search_lote').val();
                const loteText = $('#search_lote').find(":selected").data('text');
                const cantidad = parseFloat($('#search_cantidad').val());
                const maxStock = parseFloat($('#search_lote').find(":selected").data('stock'));

                if (!prodData || !loteId || isNaN(cantidad) || cantidad <= 0) {
                    Swal.fire('Atención', 'Por favor complete todos los campos del producto.', 'warning');
                    return;
                }

                if (cantidad > maxStock) {
                    Swal.fire('Error', 'La cantidad supera el stock disponible.', 'error');
                    return;
                }

                // Verificar si ya existe el lote en la lista
                if ($(`.lote-input[value="${loteId}"]`).length > 0) {
                    Swal.fire('Nota', 'Este lote ya está en la lista. Por favor edite la cantidad o elimine el anterior.', 'info');
                    return;
                }

                const row = `
                    <tr id="item_${itemIndex}">
                        <td class="ps-4">
                            <div class="fw-bold">${prodData.text}</div>
                            <input type="hidden" name="items[${itemIndex}][producto_id]" value="${prodData.producto_id}">
                        </td>
                        <td>
                            <div class="small text-muted">${$('#search_lote option:selected').text()}</div>
                            <input type="hidden" name="items[${itemIndex}][lote_origen_id]" class="lote-input" value="${loteId}">
                        </td>
                        <td class="text-center">
                            <input type="number" name="items[${itemIndex}][cantidad]" class="form-control form-control-sm text-center fw-bold" 
                                   value="${cantidad}" step="0.01" min="0.01" max="${maxStock}" required>
                        </td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-outline-danger btnRemove" data-target="#item_${itemIndex}">
                                <i class="fa fa-trash me-1"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                `;

                $itemsContainer.append(row);
                itemIndex++;
                updateUI();
                
                // Limpiar selector para el siguiente
                $('#search_producto').val(null).trigger('change');
                $('#search_cantidad').val('');
            });

            $itemsContainer.on('click', '.btnRemove', function() {
                $($(this).data('target')).remove();
                updateUI();
            });

            function updateUI() {
                const count = $itemsContainer.children().length;
                if (count > 0) {
                    $emptyState.addClass('d-none');
                    $btnSubmit.prop('disabled', false);
                } else {
                    $emptyState.removeClass('d-none');
                    $btnSubmit.prop('disabled', true);
                }
                $itemCount.text(`${count} Item${count !== 1 ? 's' : ''}`);
            }
        });
    </script>
@endpush

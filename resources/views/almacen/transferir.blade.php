@extends('layout.app')

@section('title', 'Transferir Stock')
@section('page-title', 'Transferir Stock entre Sucursales')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Nueva Transferencia de Almacén</h6>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('almacen.transferir.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Selección de Producto -->
                        <div class="col-md-12 mb-3">
                            <label for="producto_id" class="form-label">Producto <span class="text-danger">*</span></label>
                            <select class="form-control select2-producto" name="producto_id" id="producto_id" required
                                style="width: 100%;">
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Selección de Lote Origen -->
                        <div class="col-md-12 mb-3">
                            <label for="lote_origen_id" class="form-label">Lote Origen / Stock Disponible <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" name="lote_origen_id" id="lote_origen_id" required disabled>
                                <option value="">Seleccione primero un producto</option>
                            </select>
                            <small class="text-muted" id="loteInfo"></small>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Sucursal Destino -->
                        <div class="col-md-6 mb-3">
                            <label for="sucursal_destino_id" class="form-label">Sucursal Destino <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" name="sucursal_destino_id" required>
                                <option value="">Seleccione sucursal...</option>
                                @foreach ($sucursales as $suc)
                                    <option value="{{ $suc->id }}"
                                        {{ $suc->id == old('sucursal_destino_id') ? 'selected' : '' }}>
                                        {{ $suc->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cantidad a Transferir -->
                        <div class="col-md-6 mb-3">
                            <label for="cantidad" class="form-label">Cantidad a Transferir <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="cantidad" id="cantidad" step="0.01"
                                min="0.01" required>
                            <div class="invalid-feedback">La cantidad no puede superar el stock disponible.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2"></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('almacen.kardex') }}" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="fas fa-exchange-alt me-1"></i> Realizar Transferencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 Producto
            $('.select2-producto').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar producto...',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('api.productos.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.nombre + (item.cb ? ' (' + item.cb + ')' : ''),
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            // Al cambiar producto, cargar lotes
            $('#producto_id').on('change', function() {
                var productoId = $(this).val();
                var $loteSelect = $('#lote_origen_id');

                $loteSelect.empty().append('<option value="">Cargando lotes...</option>').prop('disabled',
                    true);
                $('#loteInfo').text('');
                $('#cantidad').val('');

                if (productoId) {
                    $.ajax({
                        url: '{{ route('almacen.api.lotes') }}',
                        data: {
                            producto_id: productoId
                        },
                        success: function(data) {
                            $loteSelect.empty().append(
                                '<option value="">Seleccione lote origen...</option>');
                            if (data.length > 0) {
                                $.each(data, function(index, lote) {
                                    var option = $('<option></option>')
                                        .attr('value', lote.id)
                                        .text(lote.text)
                                        .data('stock', lote.stock);
                                    $loteSelect.append(option);
                                });
                                $loteSelect.prop('disabled', false);
                            } else {
                                $loteSelect.append(
                                    '<option value="">No hay stock disponible</option>');
                            }
                        },
                        error: function() {
                            $loteSelect.empty().append(
                                '<option value="">Error al cargar lotes</option>');
                        }
                    });
                } else {
                    $loteSelect.empty().append('<option value="">Seleccione primero un producto</option>');
                }
            });

            // Al cambiar lote, validar max stock
            $('#lote_origen_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var stock = selectedOption.data('stock');
                if (stock) {
                    $('#loteInfo').text('Stock Máximo Disponible: ' + stock);
                    $('#cantidad').attr('max', stock);
                } else {
                    $('#loteInfo').text('');
                    $('#cantidad').removeAttr('max');
                }
            });
        });
    </script>
@endpush

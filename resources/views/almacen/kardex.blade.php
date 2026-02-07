@extends('layout.app')

@section('title', 'Kardex de Producto')
@section('page-title', 'Kardex de Producto')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Consultar Movimientos de Producto</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('almacen.kardex') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <label for="producto_id">Buscar Producto:</label>
                            <select class="form-control select2-producto" name="producto_id" required style="width: 100%;">
                                @if ($producto)
                                    <option value="{{ $producto->id }}">{{ $producto->nombre }}
                                        ({{ $producto->codigo_barras }})</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Consultar Kardex
                            </button>
                        </div>
                    </div>
                </form>

                @if ($producto)
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Producto:</strong> {{ $producto->nombre }} <br>
                            <strong>Código:</strong> {{ $producto->codigo_barras }}
                        </div>
                        <!-- El stock actual podría venir de la suma del kardex o de la tabla productos -->
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="dataTableKardex" width="100%"
                            cellspacing="0">
                            <thead class="text-center bg-light">
                                <tr>
                                    <th rowspan="2" class="align-middle">Fecha / Hora</th>
                                    <th rowspan="2" class="align-middle">Tipo</th>
                                    <th rowspan="2" class="align-middle">Detalle / Documento</th>
                                    <th rowspan="2" class="align-middle">Usuario</th>
                                    <th colspan="3">Cantidades</th>
                                </tr>
                                <tr>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $saldo = 0; @endphp
                                @forelse($movimientos as $mov)
                                    @php
                                        $entrada = floatval($mov->entrada);
                                        $salida = floatval($mov->salida);
                                        $saldo += $entrada - $salida;
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            @if ($mov->tipo == 'ENTRADA')
                                                <span class="badge bg-success">ENTRADA</span>
                                            @else
                                                <span class="badge bg-danger">SALIDA</span>
                                            @endif
                                        </td>
                                        <td>{{ $mov->detalle }}</td>
                                        <td>{{ $mov->usuario }}</td>
                                        <td class="text-right font-weight-bold text-success">
                                            {{ $entrada > 0 ? number_format($entrada, 2) : '-' }}
                                        </td>
                                        <td class="text-right font-weight-bold text-danger">
                                            {{ $salida > 0 ? number_format($salida, 2) : '-' }}
                                        </td>
                                        <td class="text-right font-weight-bold bg-light">
                                            {{ number_format($saldo, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No se encontraron movimientos para
                                            este producto.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="6" class="text-right">SALDO FINAL:</td>
                                    <td class="text-right">{{ number_format($saldo ?? 0, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-boxes fa-3x mb-3"></i>
                        <p>Seleccione un producto para ver su historial completo de movimientos.</p>
                    </div>
                @endif
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
            $('.select2-producto').select2({
                theme: 'bootstrap-5',
                placeholder: 'Escriba nombre o código de barras...',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('api.productos.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // search term
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
        });
    </script>
@endpush

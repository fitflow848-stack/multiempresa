<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>CAPITAL ACTUAL - VALORIZADO A COSTO DE COMPRA</h5>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 offset-md-3">
        <div class="card bg-primary text-white text-center">
            <div class="card-body">
                <h6 class="card-title">Total Capital Invertido (Stock Actual)</h6>
                <h2 class="display-4 fw-bold">S/ {{ number_format($total, 2) }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-striped table-hover">
        <thead class="bg-light">
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Marca</th>
                <th>Familia</th>
                <th>Laboratorio</th>
                <th class="text-center">Stock</th>
                <th class="text-end">Costo Unit.</th>
                <th class="text-end">Valor Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detalles as $row)
                @php
                    $costoUnitario = $row->stock > 0 ? $row->valor / $row->stock : 0;
                @endphp
                <tr>
                    <td>
                        <code>{{ $row->producto->codigo_barras ?? '-' }}</code>
                    </td>
                    <td>
                        <strong>{{ $row->producto->nombre ?? 'N/A' }}</strong>
                        @if ($row->producto->presentacion_modelo)
                            <br><small class="text-muted">{{ $row->producto->presentacion_modelo }}</small>
                        @endif
                    </td>
                    <td>{{ $row->producto->marca->nombre ?? '-' }}</td>
                    <td>{{ $row->producto->familia->nombre ?? '-' }}</td>
                    <td>{{ $row->producto->laboratorio->nombre ?? '-' }}</td>
                    <td class="text-center fw-bold">{{ number_format($row->stock, 2) }}</td>
                    <td class="text-end">S/ {{ number_format($costoUnitario, 2) }}</td>
                    <td class="text-end text-primary fw-bold">S/ {{ number_format($row->valor, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-3">No hay stock valorizado</td>
                </tr>
            @endforelse
        </tbody>
        @if (count($detalles) > 0)
            <tfoot class="table-dark">
                <tr>
                    <th colspan="5" class="text-end">TOTALES:</th>
                    <th class="text-center">{{ number_format($detalles->sum('stock'), 2) }}</th>
                    <th></th>
                    <th class="text-end">S/ {{ number_format($total, 2) }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

<div class="row mb-3">
    <div class="col-12 text-center text-danger">
        <h5>ALMACEN - STOCK CRÍTICO Y AGOTADO</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover text-center">
        <thead class="bg-light text-center">
            <tr>
                <th class="text-center">Producto</th>
                <th class="text-center">Marca</th>
                <th class="text-center">Categoría</th>
                <th class="text-center text-primary">Stock Min.</th>
                <th class="text-center text-danger">Stock Actual</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $item)
                <tr class="{{ $item->stock_actual == 0 ? 'table-danger' : ($item->stock_actual < $item->stock_minimo ? 'table-warning' : '') }}">
                    <td class="text-start">
                        <strong>{{ $item->nombre }}</strong>
                    </td>
                    <td>{{ $item->marca->nombre ?? '-' }}</td>
                    <td>{{ $item->familia->nombre ?? '-' }}</td>
                    <td class="fw-bold text-primary">{{ number_format($item->stock_minimo ?? 0, 2) }}</td>
                    <td class="fw-bold {{ $item->stock_actual == 0 ? 'text-danger' : 'text-dark' }}">
                        {{ number_format($item->stock_actual, 2) }}
                    </td>
                    <td>
                        @if($item->stock_actual == 0)
                            <span class="badge bg-danger">AGOTADO</span>
                        @elseif($item->stock_actual < $item->stock_minimo)
                            <span class="badge bg-warning text-dark">CRÍTICO</span>
                        @else
                            <span class="badge bg-info">REVISIÓN</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">No se encontraron productos con stock crítico o agotado. ¡Todo en orden!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

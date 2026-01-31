<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>PRODUCTOS CON CONDICIÓN DE VENTA</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Familia</th>
                <th>Laboratorio</th>
                <th class="text-center bg-warning text-dark">Condición de Venta</th>
                <th class="text-end">Precio Venta</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $detalle)
                <tr>
                    <td>{{ $detalle->producto->codigo_barras ?? '-' }}</td>
                    <td>{{ $detalle->producto->nombre ?? 'Producto Eliminado' }}</td>
                    <td>{{ $detalle->producto->familia->nombre ?? '' }}</td>
                    <td>{{ $detalle->producto->laboratorio->nombre ?? '' }}</td>
                    <td class="text-center fw-bold">{{ $detalle->producto->condicion_venta }}</td>
                    <td class="text-end">{{ number_format($detalle->pvp, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-muted">No se encontraron productos con condición de venta específica</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

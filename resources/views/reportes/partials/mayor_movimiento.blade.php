<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>PRODUCTOS CON MAYOR MOVIMIENTO (TOP 50)</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Ranking</th>
                <th>Código</th>
                <th>Producto</th>
                <th class="text-center">Und. Medida</th>
                <th class="text-center bg-info text-white">Cantidad Vendida</th>
                <th class="text-end">Monto Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $index => $row)
                <tr>
                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                    <td>{{ $row->producto->codigo_barras ?? '-' }}</td>
                    <td>{{ $row->producto->nombre ?? 'Producto Eliminado' }}</td>
                    <td class="text-center">{{ $row->producto->unidadMedida->codigo ?? 'NIU' }}</td>
                    <td class="text-center fw-bold fs-5">{{ (float) $row->total_cantidad }}</td>
                    <td class="text-end">{{ number_format($row->total_venta, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-muted">No se encontraron movimientos de productos</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

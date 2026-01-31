<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>PRODUCTOS CON MAYOR UTILIDAD (ESTIMADO)</h5>
        <small class="text-muted">Calculado como: (Ventas Totales - (Costo Actual * Cantidad Vendida))</small>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Ranking</th>
                <th>Producto</th>
                <th class="text-end">Cantidad</th>
                <th class="text-end">Venta Total</th>
                <th class="text-end">Costo Unit. (Ref)</th>
                <th class="text-end bg-success text-white">Utilidad Estimada</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $index => $row)
                <tr>
                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                    <td>{{ $row->producto->nombre ?? 'Producto Eliminado' }}</td>
                    <td class="text-end">{{ (float) $row->total_cantidad }}</td>
                    <td class="text-end">{{ number_format($row->total_venta, 2) }}</td>
                    <td class="text-end text-muted">{{ number_format($row->producto->precio_compra ?? 0, 2) }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($row->utilidad_estimada, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-muted">No se encontraron datos de utilidad</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>COMPRAS - CONSOLIDADO POR PRODUCTO</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Ranking</th>
                <th>Producto</th>
                <th class="text-center">Unidad</th>
                <th class="text-center bg-info text-white">Cantidad Comprada</th>
                <th class="text-end">Costo Total Estimado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $index => $row)
                <tr>
                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                    <td>{{ $row->producto->nombre ?? 'Producto Eliminado' }}</td>
                    <td class="text-center">{{ $row->producto->unidadMedida->codigo ?? '' }}</td>
                    <td class="text-center fw-bold fs-5">{{ (float) $row->total_cantidad }}</td>
                    <td class="text-end">{{ number_format($row->total_costo, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <span class="text-muted">No se encontraron productos comprados en el periodo</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

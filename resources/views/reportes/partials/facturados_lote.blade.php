<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>CONSOLIDADO - FACTURADOS POR LOTE</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th class="bg-dark text-white text-center">Lote</th>
                <th>Producto</th>
                <th class="text-center">Cant. Vendida</th>
                <th class="text-end">Total Facturado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $row)
                <tr>
                    <td class="text-center fw-bold">{{ $row->lote ?? 'SIN LOTE' }}</td>
                    <td>{{ $row->producto->nombre ?? 'Producto Eliminado' }}</td>
                    <td class="text-center fs-5">{{ (float) $row->total_cantidad }}</td>
                    <td class="text-end">{{ number_format($row->total_venta, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <span class="text-muted">No se encontraron ventas con información de lote</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

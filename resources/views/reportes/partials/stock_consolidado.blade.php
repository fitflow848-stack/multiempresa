<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>STOCK CONSOLIDADO POR PRODUCTO</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Producto</th>
                <th class="text-center">Stock Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $row)
                <tr>
                    <td>{{ $row->producto->nombre ?? 'N/A' }}</td>
                    <td class="text-center fw-bold fs-5">{{ (float) $row->stock_total }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center py-5">
                        <span class="text-muted">No hay stock registrado</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

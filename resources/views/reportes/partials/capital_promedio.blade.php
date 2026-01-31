<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>CAPITAL ACTUAL - VALORIZADO PROMEDIO</h5>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 offset-md-3">
        <div class="card bg-success text-white text-center">
            <div class="card-body">
                <h6 class="card-title">Total Capital (Valor Promedio)</h6>
                <h2 class="display-4 fw-bold">S/ {{ number_format($total, 2) }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-striped">
        <thead class="bg-light">
            <tr>
                <th>Producto</th>
                <th class="text-center">Stock Actual</th>
                <th class="text-end">Valor Total (Promedio)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detalles as $row)
                <tr>
                    <td>{{ $row->producto->nombre ?? 'N/A' }}</td>
                    <td class="text-center">{{ (float) $row->stock }}</td>
                    <td class="text-end">{{ number_format($row->valor, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-3">No hay stock valorizado</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

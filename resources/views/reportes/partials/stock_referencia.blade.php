<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>STOCK POR REFERENCIA (LOTE)</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Producto</th>
                <th>Referencia / Lote</th>
                <th>Vencimiento</th>
                <th class="text-center">Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $row)
                <tr>
                    <td>{{ $row->producto->nombre ?? 'N/A' }}</td>
                    <td class="fw-bold">{{ $row->lote }}</td>
                    <td>{{ $row->fecha_vencimiento }}</td>
                    <td class="text-center">{{ (float) $row->cantidad }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <span class="text-muted">No hay stock registrado</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

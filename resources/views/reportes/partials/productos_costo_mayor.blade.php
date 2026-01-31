<div class="row mb-3">
    <div class="col-12 text-center">
        <h5 class="text-danger">PRODUCTOS CON COSTO MAYOR AL PRECIO DE VENTA</h5>
        <small class="text-muted">Alerta: Estos productos generan pérdida si se venden a su precio actual.</small>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Producto</th>
                <th>Lote</th>
                <th class="text-end">Costo</th>
                <th class="text-end">Precio Venta (PVP)</th>
                <th class="text-end text-danger">Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $row)
                <tr>
                    <td>{{ $row->producto->nombre ?? 'N/A' }}</td>
                    <td>{{ $row->lote }}</td>
                    <td class="text-end">{{ number_format($row->costo, 2) }}</td>
                    <td class="text-end">{{ number_format($row->pvp, 2) }}</td>
                    <td class="text-end text-danger fw-bold">
                        {{ number_format($row->pvp - $row->costo, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <span class="text-success"><i class="fas fa-check-circle"></i> No se encontraron productos con
                            costo mayor al precio.</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

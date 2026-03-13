<div class="row mb-3">
    <div class="col-12 text-center">
        <h5 class="text-uppercase fw-bold text-primary">Reporte de Transferencia de Productos</h5>
        <p class="text-muted small">
            Relación de movimientos de stock entre sucursales
        </p>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover align-middle">
        <thead class="table-dark">
            <tr class="text-center">
                <th>FECHA</th>
                <th>PRODUCTO</th>
                <th>ORIGEN</th>
                <th>DESTINO</th>
                <th class="text-end">CANTIDAD</th>
                <th>USUARIO</th>
                <th>OBSERVACIONES</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $item)
                <tr>
                    <td class="text-center small">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="fw-bold">{{ $item->producto->nombre ?? 'N/A' }}</div>
                        <small class="text-muted">{{ $item->producto->codigo_barras ?? '' }}</small>
                    </td>
                    <td class="text-center">{{ $item->sucursalOrigen->nombre ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->sucursalDestino->nombre ?? 'N/A' }}</td>
                    <td class="text-end fw-bold">{{ number_format($item->cantidad, 2) }}</td>
                    <td class="text-center small">{{ $item->user->name ?? 'N/A' }}</td>
                    <td class="small">{{ $item->observaciones }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <h6 class="text-muted">No se encontraron transferencias en el rango seleccionado.</h6>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

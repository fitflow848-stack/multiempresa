<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - DEVOLUCIONES / NOTAS DE CRÉDITO</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha</th>
                <th>Nota Credito</th>
                <th>Cliente</th>
                <th class="text-end">Monto Anulado/Devuelto</th>
                <th>Vendedor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $venta)
                <tr>
                    <td>{{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y') : '' }}</td>
                    <td>{{ $venta->serie }}-{{ $venta->numero }}</td>
                    <td>{{ $venta->cliente->nombre ?? 'Sin Cliente' }}</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($venta->total, 2) }}</td>
                    <td>{{ $venta->user->name ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <span class="text-muted">No se encontraron devoluciones</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

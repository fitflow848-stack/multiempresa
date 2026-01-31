<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - COMPROBANTES</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Serie</th>
                <th>Número</th>
                <th>Cliente</th>
                <th>Moneda</th>
                <th class="text-end">Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $venta)
                <tr>
                    <td>{{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y') : '' }}</td>
                    <td>{{ strtoupper($venta->tipo_documento) }}</td>
                    <td>{{ $venta->serie }}</td>
                    <td>{{ $venta->numero }}</td>
                    <td>{{ $venta->cliente->nombre ?? 'Sin Cliente' }}</td>
                    <td>{{ $venta->moneda }}</td>
                    <td class="text-end">{{ number_format($venta->total, 2) }}</td>
                    <td>
                        @if ($venta->estado == '1')
                            <span class="badge bg-success">Aceptado</span>
                        @else
                            <span class="badge bg-danger">Anulado</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <span class="text-muted">No se encontraron comprobantes</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

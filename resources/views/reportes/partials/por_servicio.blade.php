<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - POR SERVICIO</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Servicio</th>
                <th>Cliente</th>
                <th class="text-end">Monto</th>
                <th>Vendedor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $detalle)
                <tr>
                    <td>{{ $detalle->venta->fecha_emision ? $detalle->venta->fecha_emision->format('d/m/Y H:i') : '' }}</td>
                    <td>{{ $detalle->venta->serie }}-{{ $detalle->venta->numero }}</td>
                    <td>{{ $detalle->producto->nombre ?? 'Servicio' }}</td>
                    <td>{{ $detalle->venta->cliente->nombre ?? 'Publico General' }}</td>
                    <td class="text-end">{{ number_format($detalle->subtotal, 2) }}</td>
                    <td>{{ $detalle->venta->user->name ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-muted">No se encontraron ventas de servicios</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

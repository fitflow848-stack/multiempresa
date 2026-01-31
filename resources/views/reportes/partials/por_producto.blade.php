<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - POR PRODUCTO</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Precio Unit.</th>
                <th>Total</th>
                <th>Vendedor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $detalle)
                <tr>
                    <td>{{ $detalle->venta->fecha_emision ? $detalle->venta->fecha_emision->format('d/m/Y') : '' }}</td>
                    <td>{{ $detalle->venta->serie ?? '' }}-{{ $detalle->venta->numero ?? '' }}</td>
                    <td>{{ $detalle->producto->nombre ?? 'Producto Eliminado' }}</td>
                    <td class="text-end">{{ $detalle->cantidad }}</td>
                    <td class="text-end">{{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="text-end">{{ number_format($detalle->subtotal, 2) }}</td>
                    <td>{{ $detalle->venta->user->name ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <span class="text-muted">El listado resultó vacío</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

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
                <th class="text-center">Cant.</th>
                <th class="text-end">P. Compra</th>
                <th class="text-end">P. Venta</th>
                <th class="text-end">Total Venta</th>
                <th class="text-end">Valor Venta</th>
                <th class="text-end">IGV</th>
                <th class="text-end">Costo</th>
                <th class="text-end">Ganancia</th>
                <th>Vendedor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $detalle)
                <tr>
                    <td>{{ $detalle->venta->fecha_emision ? $detalle->venta->fecha_emision->format('d/m/Y H:i') : '' }}</td>
                    <td>{{ $detalle->venta->serie ?? '' }}-{{ $detalle->venta->numero ?? '' }}</td>
                    <td class="small">{{ $detalle->nombre_completo }}</td>
                    <td class="text-center">{{ number_format($detalle->cantidad, 2) }}</td>
                    <td class="text-end">{{ number_format($detalle->costo_unitario, 2) }}</td>
                    <td class="text-end">{{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($detalle->subtotal, 2) }}</td>
                    <td class="text-end">{{ number_format($detalle->valor_venta, 2) }}</td>
                    <td class="text-end">{{ number_format($detalle->igv, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($detalle->costo_total, 2) }}</td>
                    <td class="text-end {{ $detalle->ganancia >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                        {{ number_format($detalle->ganancia, 2) }}
                    </td>
                    <td>{{ $detalle->venta->user->name ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center py-5">
                        <span class="text-muted">El listado resultó vacío</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if (isset($totales) && count($resultados) > 0)
            <tfoot class="table-dark">
                <tr>
                    <th colspan="3" class="text-end">TOTALES:</th>
                    <th class="text-center">{{ number_format($totales->cantidad, 2) }}</th>
                    <th></th>
                    <th></th>
                    <th class="text-end">S/ {{ number_format($totales->subtotal, 2) }}</th>
                    <th class="text-end">S/ {{ number_format($totales->valor_venta, 2) }}</th>
                    <th class="text-end">S/ {{ number_format($totales->igv, 2) }}</th>
                    <th class="text-end">S/ {{ number_format($totales->costo_total, 2) }}</th>
                    <th class="text-end {{ $totales->ganancia >= 0 ? 'text-success' : 'text-danger' }}">
                        S/ {{ number_format($totales->ganancia, 2) }}
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

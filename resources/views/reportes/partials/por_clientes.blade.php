<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - POR CLIENTES (DETALLADO)</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Tipo Pago</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentClient = null;
                $subtotal = 0;
            @endphp
            @forelse($resultados as $venta)
                @if ($currentClient !== $venta->id_cliente)
                    @if (!is_null($currentClient))
                        <tr class="bg-light fw-bold">
                            <td colspan="4" class="text-end">Total Cliente:</td>
                            <td class="text-end">{{ number_format($subtotal, 2) }}</td>
                        </tr>
                        @php $subtotal = 0; @endphp
                    @endif
                    <tr class="table-info">
                        <td colspan="5" class="fw-bold"><i class="fas fa-user-circle me-2"></i>
                            {{ $venta->cliente->nombre ?? 'Publico General / Sin Nombre' }}</td>
                    </tr>
                    @php $currentClient = $venta->id_cliente; @endphp
                @endif

                <tr>
                    <td></td>
                    <td>{{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y') : '' }}</td>
                    <td>{{ $venta->serie }}-{{ $venta->numero }}</td>
                    <td>{{ $venta->tipoPago->nombre ?? '-' }}</td>
                    <td class="text-end">{{ number_format($venta->total, 2) }}</td>
                </tr>
                @php $subtotal += $venta->total; @endphp

                @if ($loop->last)
                    <tr class="bg-light fw-bold">
                        <td colspan="4" class="text-end">Total Cliente:</td>
                        <td class="text-end">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <span class="text-muted">No se encontraron ventas</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

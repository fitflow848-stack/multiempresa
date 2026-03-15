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
                <th>Doc. Nro</th>
                <th>Cliente</th>
                <th class="text-end">Subtotal</th>
                <th class="text-end">IGV</th>
                <th class="text-end">Total (S/)</th>
                <th class="text-end">Pagado (S/)</th>
                <th class="text-end">Pendiente (S/)</th>
                <th class="text-end text-danger bg-light">Deuda Cliente (S/)</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $venta)
                @php
                    $igvValue = $venta->igv ?? 0;
                    $subtotal = $venta->total - $igvValue;
                @endphp
                <tr>
                    <td>{{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y H:i') : '' }}</td>
                    <td class="small">{{ strtoupper($venta->tipo_documento) }}</td>
                    <td class="small fw-bold">{{ $venta->serie }}-{{ $venta->numero }}</td>
                    <td class="small">{{ $venta->cliente->nombre ?? 'Sin Cliente' }}</td>
                    <td class="text-end font-monospace">{{ number_format($subtotal, 2) }}</td>
                    <td class="text-end font-monospace">{{ number_format($igvValue, 2) }}</td>
                    <td class="text-end font-monospace">{{ number_format($venta->total, 2) }}</td>
                    <td class="text-end font-monospace text-success">{{ number_format($venta->monto_pagado_doc, 2) }}</td>
                    <td
                        class="text-end font-monospace {{ $venta->monto_pendiente_doc > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                        {{ number_format($venta->monto_pendiente_doc, 2) }}
                    </td>
                    <td class="text-end font-monospace fw-bold bg-light">
                        {{ number_format($venta->deuda_total_cliente, 2) }}
                    </td>
                    <td class="text-center">
                        @if ($venta->estado == '1')
                            <span class="badge bg-success" style="font-size: 0.7rem;">OK</span>
                        @else
                            <span class="badge bg-danger" style="font-size: 0.7rem;">ANULADO</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <span class="text-muted">No se encontraron comprobantes</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
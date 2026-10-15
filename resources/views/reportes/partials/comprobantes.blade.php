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
            @php $netoSubtotal = 0; $netoIgv = 0; $netoTotal = 0; @endphp
            @forelse($resultados as $venta)
                @php
                    $esNC = ($venta->id_tido == 5);
                    $signo = $esNC ? -1 : 1;
                    $igvValue = $venta->igv ?? 0;
                    $subtotal = $venta->total - $igvValue;
                    $netoSubtotal += $signo * $subtotal;
                    $netoIgv += $signo * $igvValue;
                    $netoTotal += $signo * $venta->total;
                @endphp
                <tr>
                    <td>{{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y H:i') : '' }}</td>
                    <td class="small">{{ strtoupper($venta->tipo_documento) }}</td>
                    <td class="small fw-bold">{{ $venta->serie }}-{{ $venta->numero }}</td>
                    <td class="small">{{ $venta->cliente->nombre ?? 'Sin Cliente' }}</td>
                    <td class="text-end font-monospace {{ $esNC ? 'text-danger' : '' }}">{{ $esNC ? '-' : '' }}{{ number_format($subtotal, 2) }}</td>
                    <td class="text-end font-monospace {{ $esNC ? 'text-danger' : '' }}">{{ $esNC ? '-' : '' }}{{ number_format($igvValue, 2) }}</td>
                    <td class="text-end font-monospace {{ $esNC ? 'text-danger' : '' }}">{{ $esNC ? '-' : '' }}{{ number_format($venta->total, 2) }}</td>
                    <td class="text-end font-monospace text-success">{{ number_format($venta->monto_pagado_doc, 2) }}</td>
                    <td
                        class="text-end font-monospace {{ $venta->monto_pendiente_doc > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                        {{ number_format($venta->monto_pendiente_doc, 2) }}
                    </td>
                    <td class="text-end font-monospace fw-bold bg-light">
                        {{ number_format($venta->deuda_total_cliente, 2) }}
                    </td>
                    <td class="text-center">
                        @if ($esNC)
                            <span class="badge bg-secondary" style="font-size: 0.7rem;">NOTA DE CRÉDITO</span>
                        @elseif ($venta->estado == '1')
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
        @if (count($resultados) > 0)
            <tfoot class="table-dark">
                <tr>
                    <th colspan="4" class="text-end">TOTAL NETO (las notas de crédito restan):</th>
                    <th class="text-end">{{ number_format($netoSubtotal, 2) }}</th>
                    <th class="text-end">{{ number_format($netoIgv, 2) }}</th>
                    <th class="text-end">{{ number_format($netoTotal, 2) }}</th>
                    <th colspan="4"></th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
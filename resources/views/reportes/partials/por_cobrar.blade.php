<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>DEUDA DE CLIENTE</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha Venta</th>
                <th>Vencimiento</th>
                <th>Cliente</th>
                <th>Comprobante</th>
                <th class="text-end">Total Original</th>
                <th class="text-end">Pagado</th>
                <th class="text-end text-danger">Por Cobrar (Saldo)</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalPorCobrar = 0; 
                $isExcel = !empty($is_excel);
            @endphp
            @forelse($resultados as $deuda)
                <tr>
                    <td>{{ $deuda->fecha_venta ? $deuda->fecha_venta->format('d/m/Y') : '' }}</td>
                    <td class="{{ $deuda->fecha_vencimiento < now() ? 'text-danger fw-bold' : '' }}">
                        {{ $deuda->fecha_vencimiento ? $deuda->fecha_vencimiento->format('d/m/Y') : '-' }}
                    </td>
                    <td>{{ $deuda->cliente->nombre ?? 'Sin Cliente' }}</td>
                    <td>{{ $deuda->tipo_documento }} {{ $deuda->numero_comprobante }}</td>
                    <td class="text-end">{{ moneda($deuda->monto_total, $isExcel, !$isExcel) }}</td>
                    <td class="text-end">{{ moneda($deuda->monto_pagado, $isExcel, !$isExcel) }}</td>
                    <td class="text-end fw-bold text-danger">{{ moneda($deuda->monto_deuda, $isExcel, !$isExcel) }}</td>
                    <td>
                        <span class="badge bg-warning text-dark">{{ ucfirst($deuda->estado) }}</span>
                    </td>
                </tr>
                @php $totalPorCobrar += $deuda->monto_deuda; @endphp
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <span class="text-success"><i class="fas fa-check-circle fa-2x mb-2"></i><br>No hay deudas
                            pendientes por cobrar</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-light">
            <tr>
                <td colspan="6" class="text-end fw-bold">TOTAL POR COBRAR:</td>
                <td class="text-end fw-bold text-danger fs-6">{{ moneda($totalPorCobrar, $isExcel, !$isExcel) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

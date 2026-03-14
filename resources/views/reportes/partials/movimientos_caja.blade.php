<div class="row mb-3 text-center">
    <div class="col-12">
        <h5 class="fw-bold text-uppercase">Movimientos Detallados de Caja</h5>
        <p class="text-muted small">Ventas y Operaciones Manuales consolidadas</p>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover align-middle">
        <thead class="table-dark">
            <tr class="text-center">
                <th>FECHA</th>
                <th>CAJA</th>
                <th>OPERACIÓN</th>
                <th>TIPO</th>
                <th>DETALLE</th>
                <th>CONCEPTO</th>
                <th>MÉTODO PAGO</th>
                <th class="text-end">INGRESO (S/)</th>
                <th class="text-end">EGRESO (S/)</th>
                <th>USUARIO</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalIngresos = 0; 
                $totalEgresos = 0; 
                $isExcel = !empty($is_excel);
            @endphp
            @forelse($resultados as $item)
                @php
                    $esIngreso = in_array($item->tipo, ['ingreso', 'aporte', 'aportacion']);
                    $importe = floatval($item->importe);
                    if ($esIngreso) $totalIngresos += $importe;
                    else $totalEgresos += $importe;
                @endphp
                <tr>
                    <td class="small text-nowrap">{{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y H:i') }}</td>
                    <td class="small fw-bold text-primary">{{ $item->caja ?? 'N/A' }}</td>
                    <td class="small">{{ $item->operacion }}</td>
                    <td class="text-center">
                        <span class="badge {{ $esIngreso ? 'bg-success' : 'bg-danger' }}">
                            {{ strtoupper($item->tipo) }}
                        </span>
                    </td>
                    <td class="small">{{ $item->detalle }}</td>
                    <td class="small">{{ $item->concepto }}</td>
                    <td class="text-center small">{{ $item->metodo_pago }}</td>
                    <td class="text-end font-monospace {{ $esIngreso ? 'text-success' : 'text-muted' }}">
                        @if($isExcel)
                            {{ $esIngreso ? $importe : 0 }}
                        @else
                            {{ $esIngreso ? number_format($importe, 2) : '0.00' }}
                        @endif
                    </td>
                    <td class="text-end font-monospace {{ !$esIngreso ? 'text-danger' : 'text-muted' }}">
                        @if($isExcel)
                            {{ !$esIngreso ? $importe : 0 }}
                        @else
                            {{ !$esIngreso ? number_format($importe, 2) : '0.00' }}
                        @endif
                    </td>
                    <td class="small text-center">{{ $item->usuario }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <span class="text-muted">No se encontraron movimientos en el rango seleccionado</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($resultados->count() > 0)
        <tfoot class="table-light">
            <tr class="fw-bold">
                <td colspan="7" class="text-end text-uppercase">Totales del Periodo:</td>
                <td class="text-end text-success">
                    @if($isExcel)
                        {{ $totalIngresos }}
                    @else
                        S/ {{ number_format($totalIngresos, 2) }}
                    @endif
                </td>
                <td class="text-end text-danger">
                    @if($isExcel)
                        {{ $totalEgresos }}
                    @else
                        S/ {{ number_format($totalEgresos, 2) }}
                    @endif
                </td>
                <td class="text-center bg-dark text-white">
                    @if($isExcel)
                        {{ $totalIngresos - $totalEgresos }}
                    @else
                        SALDO: S/ {{ number_format($totalIngresos - $totalEgresos, 2) }}
                    @endif
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

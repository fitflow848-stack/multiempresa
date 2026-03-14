<div class="row mb-3">
    <div class="col-12 text-center">
        <h5 class="text-uppercase fw-bold text-primary">Reporte de Movimientos de Bóveda</h5>
        <p class="text-muted small">
            Detalle de ingresos y egresos de las cajas marcadas como Bóveda
        </p>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover align-middle">
        <thead class="table-dark">
            <tr class="text-center">
                <th>FECHA</th>
                <th>BÓVEDA</th>
                <th>TIPO</th>
                <th>PARTIDA</th>
                <th>CONCEPTO</th>
                <th class="text-end">IMPORTE</th>
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
                    $tipoNormalizado = strtolower(trim($item->tipo));
                    $esIngreso = in_array($tipoNormalizado, ['ingreso', 'aporte', 'aportacion']);
                    if ($esIngreso) {
                        $totalIngresos += $item->importe;
                    } else {
                        $totalEgresos += $item->importe;
                    }
                @endphp
                <tr>
                    <td class="text-center small">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center small">{{ $item->cierre->caja->nombre ?? 'Bóveda' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $esIngreso ? 'bg-success' : 'bg-danger' }}">
                            {{ $item->tipo }}
                        </span>
                    </td>
                    <td class="text-center small">{{ $item->partida }}</td>
                    <td class="small">{{ $item->concepto }}</td>
                    <td class="text-end fw-bold {{ $esIngreso ? 'text-success' : 'text-danger' }}">
                        @if($isExcel)
                            {{ $item->importe }}
                        @else
                            S/ {{ number_format($item->importe, 2) }}
                        @endif
                    </td>
                    <td class="text-center small">{{ $item->user->name ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <h6 class="text-muted">No se encontraron movimientos de bóveda en el rango seleccionado.</h6>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($resultados->count() > 0)
            <tfoot class="table-light">
                <tr>
                    <td colspan="5" class="text-end fw-bold">TOTAL INGRESOS:</td>
                    <td class="text-end fw-bold text-success">
                        @if($isExcel)
                            {{ $totalIngresos }}
                        @else
                            S/ {{ number_format($totalIngresos, 2) }}
                        @endif
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end fw-bold">TOTAL EGRESOS:</td>
                    <td class="text-end fw-bold text-danger">
                        @if($isExcel)
                            {{ $totalEgresos }}
                        @else
                            S/ {{ number_format($totalEgresos, 2) }}
                        @endif
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end fw-bold">SALDO NETO:</td>
                    <td class="text-end fw-bold text-primary">
                        @if($isExcel)
                            {{ $totalIngresos - $totalEgresos }}
                        @else
                            S/ {{ number_format($totalIngresos - $totalEgresos, 2) }}
                        @endif
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

<div class="row mb-3">
    <div class="col-12 text-center">
        <h5 class="text-uppercase fw-bold text-primary">Reporte de Productos Próximos a Vencer</h5>
        <p class="text-muted small">Mostrando productos con vencimiento menor o igual a <strong>{{ $meses }}
                meses</strong></p>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover align-middle">
        <thead class="table-dark">
            <tr class="text-center">
                <th>PRODUCTO</th>
                <th>LOTE</th>
                <th>F. VENCIMIENTO</th>
                <th>DÍAS RESTANTES</th>
                <th>FAMILIA</th>
                <th class="text-end">STOCK</th>
                <th class="text-end">COSTO (S/)</th>
                <th class="text-end">PVP (S/)</th>
                <th class="text-end">VALOR TOTAL (S/)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalValor = 0; @endphp
            @forelse($resultados as $item)
                @php
                    $dias = now()->diffInDays($item->fecha_vencimiento, false);
                    $claseFila = '';
                    if ($dias <= 0)
                        $claseFila = 'table-danger';
                    elseif ($dias <= 30)
                        $claseFila = 'table-warning text-dark';
                    elseif ($dias <= 90)
                        $claseFila = 'table-info text-dark';

                    $valor = $item->cantidad * $item->costo;
                    $totalValor += $valor;
                @endphp
                <tr class="{{ $claseFila }}">
                    <td class="small">{{ $item->producto->nombre ?? 'N/A' }}</td>
                    <td class="text-center font-monospace small">{{ $item->lote ?? '-' }}</td>
                    <td class="text-center">
                        {{ $item->fecha_vencimiento ? \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center fw-bold">
                        @if($dias < 0)
                            <span class="badge bg-danger">VENCIDO ({{ abs($dias) }} días)</span>
                        @elseif($dias == 0)
                            <span class="badge bg-danger">POR VENCER HOY</span>
                        @else
                            <span
                                class="badge {{ $dias <= 30 ? 'bg-warning text-dark' : ($dias <= 90 ? 'bg-info text-dark' : 'bg-secondary') }}">
                                {{ $dias }} días
                            </span>
                        @endif
                    </td>
                    <td class="text-center small">{{ $item->producto->familia->nombre ?? '-' }}</td>
                    <td class="text-end font-monospace">{{ number_format($item->cantidad, 2) }}</td>
                    <td class="text-end">{{ number_format($item->costo, 2) }}</td>
                    <td class="text-end">{{ number_format($item->pvp, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($valor, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h6 class="text-muted">No se encontraron productos próximos a vencer en el rango seleccionado.</h6>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($resultados->count() > 0)
            <tfoot class="table-light">
                <tr>
                    <td colspan="8" class="text-end fw-bold text-uppercase">Valor Total de Productos en Riesgo:</td>
                    <td class="text-end fw-bold text-danger" style="font-size: 1.1rem;">S/
                        {{ number_format($totalValor, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

<div class="alert alert-light border mt-3 small">
    <strong>Leyenda de colores:</strong>
    <span class="badge bg-danger ms-2">Vencido</span>
    <span class="badge bg-warning text-dark ms-2">Menos de 30 días</span>
    <span class="badge bg-info text-dark ms-2">Entre 30 y 90 días</span>
</div>
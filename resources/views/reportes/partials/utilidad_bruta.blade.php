<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - RESUMEN DE UTILIDAD</h5>
    </div>
</div>

<div class="table-responsive mb-4">
    <table class="table table-bordered table-sm" style="max-width: 520px; margin: 0 auto;">
        <tbody>
            <tr><th>Total venta</th><td class="text-end">S/ {{ number_format($totales->subtotal, 2) }}</td></tr>
            <tr><th>Total valor venta</th><td class="text-end">S/ {{ number_format($totales->valor_venta, 2) }}</td></tr>
            <tr><th>Total IGV</th><td class="text-end">S/ {{ number_format($totales->igv, 2) }}</td></tr>
            <tr><th>Total compra (costo)</th><td class="text-end text-danger">S/ {{ number_format($totales->costo_total, 2) }}</td></tr>
            <tr class="table-dark"><th>Total utilidad</th>
                <td class="text-end fw-bold {{ $totales->ganancia >= 0 ? 'text-success' : 'text-danger' }}">S/ {{ number_format($totales->ganancia, 2) }}</td></tr>
        </tbody>
    </table>
</div>

@foreach ([['Utilidad por vendedor / usuario', $resumenVendedor, 'Vendedor'], ['Utilidad por sucursal', $resumenSucursal, 'Sucursal']] as [$titulo, $filas, $col])
    <h6 class="fw-bold mt-3">{{ $titulo }}</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm table-hover">
            <thead class="bg-light">
                <tr>
                    <th>{{ $col }}</th>
                    <th class="text-center">Ventas</th>
                    <th class="text-end">Total Venta</th>
                    <th class="text-end">Costo</th>
                    <th class="text-end">Utilidad</th>
                    <th class="text-end">Margen</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($filas as $f)
                    <tr>
                        <td>{{ $f->nombre }}</td>
                        <td class="text-center">{{ $f->ventas }}</td>
                        <td class="text-end">{{ number_format($f->total_venta, 2) }}</td>
                        <td class="text-end text-danger">{{ number_format($f->costo, 2) }}</td>
                        <td class="text-end fw-bold {{ $f->ganancia >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($f->ganancia, 2) }}</td>
                        <td class="text-end">{{ number_format($f->margen, 1) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">El listado resultó vacío</td></tr>
                @endforelse
            </tbody>
            @if (count($filas) > 0)
                @php
                    $tv = $filas->sum('total_venta');
                    $tg = $filas->sum('ganancia');
                @endphp
                <tfoot class="table-dark">
                    <tr>
                        <th class="text-end">TOTALES:</th>
                        <th class="text-center">{{ $filas->sum('ventas') }}</th>
                        <th class="text-end">S/ {{ number_format($tv, 2) }}</th>
                        <th class="text-end">S/ {{ number_format($filas->sum('costo'), 2) }}</th>
                        <th class="text-end {{ $tg >= 0 ? 'text-success' : 'text-danger' }}">S/ {{ number_format($tg, 2) }}</th>
                        <th class="text-end">{{ number_format($tv > 0 ? ($tg / $tv) * 100 : 0, 1) }}%</th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endforeach

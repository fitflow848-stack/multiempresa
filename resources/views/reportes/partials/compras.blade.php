<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>REPORTE DE COMPRAS</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha Emisión</th>
                <th>Proveedor</th>
                <th>Tipo Doc.</th>
                <th>Condición</th>
                <th>Moneda</th>
                <th class="text-end">Total Bruto</th>
                <th class="text-end">IGV/Imp</th>
                <th class="text-end fw-bold">Total Neto</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalBruto = 0;
                $totalImpuesto = 0;
                $totalNeto = 0;
                $isExcel = !empty($is_excel);
            @endphp
            @forelse($resultados as $compra)
                @php
                    $totalBruto += $compra->total_bruto;
                    $totalImpuesto += $compra->total_impuesto;
                    $totalNeto += $compra->total_pagar;
                @endphp
                <tr>
                    <td>{{ $compra->fecha_emision }}</td>
                    <td>{{ $compra->proveedor->nombre_comercial ?? ($compra->proveedor->razon_social ?? 'Proveedor Eliminado') }}
                    </td>
                    <td>{{ $compra->tipo }}</td>
                    <td>
                        @if($compra->credito)
                            <span class="badge bg-info text-dark">Crédito</span>
                        @else
                            <span class="badge bg-secondary text-white">Contado</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $compra->moneda }}</td>
                    <td class="text-end">{{ moneda($compra->total_bruto, $isExcel, !$isExcel) }}</td>
                    <td class="text-end">{{ moneda($compra->total_impuesto, $isExcel, !$isExcel) }}</td>
                    <td class="text-end fw-bold">{{ moneda($compra->total_pagar, $isExcel, !$isExcel) }}</td>
                    <td>{{ $compra->usuario->name ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <span class="text-muted">No se encontraron compras en el periodo seleccionado</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($resultados->count() > 0)
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="5" class="text-end text-uppercase">Totales del periodo:</td>
                    <td class="text-end">{{ moneda($totalBruto, $isExcel, !$isExcel) }}</td>
                    <td class="text-end">{{ moneda($totalImpuesto, $isExcel, !$isExcel) }}</td>
                    <td class="text-end text-primary">{{ moneda($totalNeto, $isExcel, !$isExcel) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

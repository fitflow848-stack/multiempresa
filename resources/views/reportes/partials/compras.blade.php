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
                <th>Moneda</th>
                <th class="text-end">Total Bruto</th>
                <th class="text-end">IGV/Imp</th>
                <th class="text-end fw-bold">Total Neto</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $compra)
                <tr>
                    <td>{{ $compra->fecha_emision }}</td>
                    <td>{{ $compra->proveedor->nombre_comercial ?? ($compra->proveedor->razon_social ?? 'Proveedor Eliminado') }}
                    </td>
                    <td>{{ $compra->tipo }}</td>
                    <td class="text-center">{{ $compra->moneda }}</td>
                    <td class="text-end">{{ number_format($compra->total_bruto, 2) }}</td>
                    <td class="text-end">{{ number_format($compra->total_impuesto, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($compra->total_pagar, 2) }}</td>
                    <td>{{ $compra->usuario->name ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <span class="text-muted">No se encontraron compras en el periodo seleccionado</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

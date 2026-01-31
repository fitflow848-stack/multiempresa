<div class="row mb-3">
    <div class="col-12">
        <form method="GET" action="" class="d-flex gap-2 justify-content-center">
            <input type="hidden" name="reporte_id" value="33">
            <input type="text" name="busqueda" class="form-control w-25" placeholder="Buscar Lote..."
                value="{{ $search ?? '' }}">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th class="bg-dark text-white text-center">Lote</th>
                <th>Comprobante</th>
                <th>Producto</th>
                <th>Fecha Venta</th>
                <th class="text-center">Cant. Vendida</th>
                <th class="text-end">Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $row)
                <tr>
                    <td class="fw-bold text-center">{{ $row->lote }}</td>
                    <td>
                        <a href="#" onclick="verVenta({{ $row->venta->id_venta }}); return false;">
                            {{ $row->venta->serie }}-{{ str_pad($row->venta->numero, 8, '0', STR_PAD_LEFT) }}
                        </a>
                    </td>
                    <td>{{ $row->nombre_servicio ?? ($row->producto->nombre ?? 'N/A') }}</td>
                    <td>{{ $row->venta->fecha_emision ?? '' }}</td>
                    <td class="text-center">{{ (float) $row->cantidad }}</td>
                    <td class="text-end">{{ number_format($row->importe, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-muted">No se encontraron ventas para este lote</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row mb-3">
    <div class="col-12">
        <form method="GET" action="" class="d-flex gap-2 justify-content-center">
            <input type="hidden" name="reporte_id" value="32">
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
                <th>Producto</th>
                <th>Fecha Ingreso</th>
                <th class="text-center">Cant. Original</th>
                <th class="text-center">Cant. Actual</th>
                <th>Costo Unit.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $row)
                <tr>
                    <td class="fw-bold text-center">{{ $row->lote }}</td>
                    <td>{{ $row->producto->nombre ?? 'N/A' }}</td>
                    <td>{{ $row->ingreso->fecha_emision ?? $row->created_at->format('Y-m-d') }}</td>
                    <td class="text-center">{{ (float) $row->cantidad }}</td>
                    <td class="text-center">
                        {{-- Cantidad actual no la tenemos en detalle directamente si no se actualiza --}}
                        {{-- Pero la logica de stock usa esto, asi que asumimos es la actual si se descuenta --}}
                        {{ (float) $row->cantidad }}
                    </td>
                    <td class="text-end">{{ number_format($row->costo, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-muted">No se encontraron registros de lote</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>STOCK POR LOCAL</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Local / Almacén</th>
                <th>Producto</th>
                <th class="text-center">Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $row)
                <tr>
                    <td>Almacén Principal (Default)</td>
                    <td>
                        <strong>{{ $row->producto->nombre ?? 'N/A' }}</strong>
                        @if ($row->productoLinea)
                            <br><small class="text-primary">{{ $row->productoLinea->presentacion }} {{ $row->productoLinea->concentracion }}</small>
                        @elseif ($row->producto->presentacion_modelo || $row->producto->concentracion_detalle)
                            <br><small class="text-muted">{{ $row->producto->presentacion_modelo }} {{ $row->producto->concentracion_detalle }}</small>
                        @endif
                    </td>
                    <td class="text-center fw-bold">{{ (float) $row->stock_total }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-5">
                        <span class="text-muted">No hay stock registrado</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

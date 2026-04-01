<div class="row mb-3">
    <div class="col-12 text-center text-danger">
        <h5>ALMACEN - PRODUCTOS SIN STOCK / AGOTADOS</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover text-center">
        <thead class="bg-light text-center">
            <tr>
                <th class="text-center">Producto</th>
                <th class="text-center">Presentación</th>
                <th class="text-center">Concentración</th>
                <th class="text-center">Marca</th>
                <th class="text-center">Categoría</th>
                <th class="text-center text-danger">Stock Actual</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $item)
                <tr class="table-danger">
                    <td class="text-start"><strong>{{ $item->producto_nombre }}</strong></td>
                    <td>{{ $item->presentacion ?: '-' }}</td>
                    <td>{{ $item->concentracion ?: '-' }}</td>
                    <td>{{ $item->marca_nombre ?? '-' }}</td>
                    <td>{{ $item->familia_nombre ?? '-' }}</td>
                    <td class="fw-bold text-danger">{{ number_format($item->stock_actual, 2) }}</td>
                    <td>
                        <span class="badge bg-danger">SIN STOCK / AGOTADO</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">No se encontraron productos agotados. ¡Stock al día!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

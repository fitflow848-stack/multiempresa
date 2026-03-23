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
                <th class="text-center">Marca</th>
                <th class="text-center">Categoría</th>
                <th class="text-center text-primary">Precios (PVP)</th>
                <th class="text-center text-danger">Stock Actual</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $item)
                <tr class="table-danger">
                    <td class="text-start">
                        <strong>{{ $item->nombre }}</strong>
                        @if ($item->presentacion_modelo || $item->concentracion_detalle)
                            <br><small class="text-muted">{{ $item->presentacion_modelo }} {{ $item->concentracion_detalle }}</small>
                        @endif
                    </td>
                    <td>{{ $item->marca->nombre ?? '-' }}</td>
                    <td>{{ $item->familia->nombre ?? '-' }}</td>
                    <td class="text-primary">S/ {{ number_format($item->pvp ?? 0, 2) }}</td>
                    <td class="fw-bold text-danger">
                        0.00
                    </td>
                    <td>
                        <span class="badge bg-danger">SIN STOCK / AGOTADO</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">No se encontraron productos agotados. ¡Stock al día!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

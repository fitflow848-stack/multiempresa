<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>PRODUCTOS EN PROMOCIÓN / OFERTA</h5>
        <small class="text-success">Listado de productos con PVP Descuento activo</small>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Familia</th>
                <th class="text-end text-decoration-line-through text-muted">Precio Normal</th>
                <th class="text-end bg-success text-white">Precio Oferta</th>
                <th class="text-end fw-bold">Ahorro</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $producto)
                <tr>
                    <td>{{ $producto->codigo_barras ?? '-' }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->familia->nombre ?? 'Sin Familia' }}</td>
                    <td class="text-end text-decoration-line-through text-muted">{{ number_format($producto->pvp, 2) }}
                    </td>
                    <td class="text-end fw-bold text-success fs-5">{{ number_format($producto->pvp_dto, 2) }}</td>
                    <td class="text-end fw-bold text-primary">
                        {{ number_format($producto->pvp - $producto->pvp_dto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-muted">No hay promociones activas en este momento</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

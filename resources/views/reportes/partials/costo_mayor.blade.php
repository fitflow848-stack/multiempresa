<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - CON COSTO MAYOR A PRECIO</h5>
        <small class="text-danger">Productos donde el Costo de Compra excede el Precio de Venta (PVP)</small>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Familia</th>
                <th class="text-end bg-danger text-white">Costo Compra</th>
                <th class="text-end bg-success text-white">Precio Venta (PVP)</th>
                <th class="text-end">Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $producto)
                <tr>
                    <td>{{ $producto->codigo_barras ?? '-' }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->familia_id }}</td> <!-- Relacion no cargada, mostrar ID temporalmente -->
                    <td class="text-end text-danger fw-bold">{{ number_format($producto->precio_compra, 2) }}</td>
                    <td class="text-end text-success fw-bold">{{ number_format($producto->pvp, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($producto->pvp - $producto->precio_compra, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-success"><i class="fas fa-check-circle fa-2x mb-2"></i><br>Todo en orden. No
                            hay productos con costo mayor al precio.</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

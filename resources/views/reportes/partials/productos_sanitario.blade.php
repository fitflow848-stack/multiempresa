<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>PRODUCTOS CON REGISTRO SANITARIO</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Laboratorio</th>
                <th class="text-center bg-info text-white">Registro Sanitario</th>
                <th>Ubicación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $producto)
                <tr>
                    <td>{{ $producto->codigo_barras ?? '-' }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->laboratorio->nombre ?? '' }}</td>
                    <td class="text-center fw-bold">{{ $producto->registro_sanitario }}</td>
                    <td>{{ $producto->ubicacion ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <span class="text-muted">No se encontraron productos con registro sanitario</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

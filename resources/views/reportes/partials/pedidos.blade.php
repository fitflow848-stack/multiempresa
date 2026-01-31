<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - PEDIDOS / COTIZACIONES</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha</th>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Vigencia</th>
                <th class="text-end">Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $cotizacion)
                <tr>
                    <td>{{ $cotizacion->fecha ? $cotizacion->fecha->format('d/m/Y') : '' }}</td>
                    <td>{{ $cotizacion->numero ?? $cotizacion->id }}</td>
                    <td>{{ $cotizacion->cliente->nombre ?? 'Sin Cliente' }}</td>
                    <td>{{ $cotizacion->vigencia ? $cotizacion->vigencia->format('d/m/Y') : '' }}</td>
                    <td class="text-end">{{ number_format($cotizacion->total, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $cotizacion->color_estado ?? 'secondary' }}">
                            {{ ucfirst($cotizacion->estado) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-muted">No se encontraron pedidos</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

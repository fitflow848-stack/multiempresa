<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - RESUMEN POR USUARIO / VENDEDOR</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Ranking</th>
                <th>Usuario / Vendedor</th>
                <th class="text-center">Cant. Ventas</th>
                <th class="text-end">Monto Total Vendido</th>
                <th class="text-end">Ticket Promedio</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $index => $row)
                <tr>
                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                    <td>
                        <i class="fas fa-user me-2 text-primary"></i>
                        {{ $row->user->name ?? 'Usuario Sistema' }}
                    </td>
                    <td class="text-center">{{ $row->total_ventas }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($row->monto_total, 2) }}</td>
                    <td class="text-end">{{ number_format($row->monto_total / ($row->total_ventas ?: 1), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <span class="text-muted">No se encontraron resultados</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

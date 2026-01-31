<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - CLIENTES FRECUENTES</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Ranking</th>
                <th>Cliente</th>
                <th>Documento</th>
                <th class="text-center">Cant. Compras</th>
                <th class="text-end">Total Gastado</th>
                <th class="text-end">Ticket Promedio</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $index => $row)
                <tr>
                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                    <td>{{ $row->cliente->nombre ?? 'Cliente General / Anónimo' }}</td>
                    <td>{{ $row->cliente->numero_documento ?? '-' }}</td>
                    <td class="text-center">{{ $row->total_compras }}</td>
                    <td class="text-end">S/ {{ number_format($row->total_gastado, 2) }}</td>
                    <td class="text-end">S/ {{ number_format($row->total_gastado / $row->total_compras, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <span class="text-muted">No se encontraron resultados</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

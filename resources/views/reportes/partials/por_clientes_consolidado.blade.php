<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - POR CLIENTES (CONSOLIDADO)</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Ranking</th>
                <th>Cliente</th>
                <th>Documento</th>
                <th class="text-center">Transacciones</th>
                <th class="text-end">Monto Total Comprado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->cliente->nombre ?? 'Cliente General' }}</td>
                    <td>{{ $row->cliente->numero_documento ?? '-' }}</td>
                    <td class="text-center">{{ $row->total_transacciones }}</td>
                    <td class="text-end fw-bold">{{ number_format($row->monto_total, 2) }}</td>
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

<table class="table table-bordered table-sm table-striped">
    <thead class="table-dark">
        <tr>
            <th>Fecha</th>
            <th>Cuenta</th>
            <th>Sucursal</th>
            <th>Tipo</th>
            <th>Concepto</th>
            <th>Referencia</th>
            <th>Usuario</th>
            <th class="text-end">Monto</th>
        </tr>
    </thead>
    <tbody>
        @php $totalIngresos = 0; $totalEgresos = 0; @endphp
        @forelse($resultados as $mov)
        <tr>
            <td class="small">{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
            <td class="small">{{ $mov->cuenta->banco_nombre ?? '-' }}</td>
            <td class="small">{{ $mov->sucursal->nombre ?? '-' }}</td>
            <td>
                <span class="badge bg-{{ $mov->tipo === 'ingreso' ? 'success' : 'danger' }}">
                    {{ ucfirst($mov->tipo) }}
                </span>
            </td>
            <td class="small">{{ $mov->concepto }}</td>
            <td class="small">{{ $mov->referencia ?? '-' }}</td>
            <td class="small">{{ $mov->user->name ?? '-' }}</td>
            <td class="text-end fw-bold text-{{ $mov->tipo === 'ingreso' ? 'success' : 'danger' }}">
                {{ $mov->tipo === 'ingreso' ? '+' : '-' }} {{ number_format($mov->monto, 2) }}
            </td>
        </tr>
        @php
            if ($mov->tipo === 'ingreso') $totalIngresos += $mov->monto;
            else $totalEgresos += $mov->monto;
        @endphp
        @empty
        <tr><td colspan="8" class="text-center text-muted">No hay movimientos bancarios en el rango seleccionado.</td></tr>
        @endforelse
    </tbody>
    @if(count($resultados) > 0)
    <tfoot class="table-light fw-bold">
        <tr>
            <td colspan="7" class="text-end">Total Ingresos:</td>
            <td class="text-end text-success">+ {{ number_format($totalIngresos, 2) }}</td>
        </tr>
        <tr>
            <td colspan="7" class="text-end">Total Egresos:</td>
            <td class="text-end text-danger">- {{ number_format($totalEgresos, 2) }}</td>
        </tr>
        <tr>
            <td colspan="7" class="text-end">Neto:</td>
            <td class="text-end">{{ number_format($totalIngresos - $totalEgresos, 2) }}</td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>ARQUEO DE CAJA {{ $porUsuario ? 'POR USUARIO' : 'GENERAL' }}</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha Cierre</th>
                <th>Usuario</th>
                <th class="text-end">Monto Apertura</th>
                <th class="text-end">Ingresos</th>
                <th class="text-end">Egresos</th>
                <th class="text-end fw-bold">Saldo Teórico</th>
                <th class="text-end bg-info text-white">Monto Cierre (Real)</th>
                <th class="text-end">Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $cierre)
                @php
                    $saldoTeorico =
                        $cierre->monto_apertura +
                        $cierre->ingresos +
                        $cierre->aportaciones -
                        $cierre->egresos -
                        $cierre->sustracciones;
                    $diferencia = $cierre->monto_cierre - $saldoTeorico;
                @endphp
                <tr>
                    <td>{{ $cierre->fecha_cierre ? $cierre->fecha_cierre->format('d/m/Y H:i') : '' }}</td>
                    <td>{{ $cierre->user->name ?? 'Usuario Sistema' }}</td>
                    <td class="text-end">{{ number_format($cierre->monto_apertura, 2) }}</td>
                    <td class="text-end text-success">+{{ number_format($cierre->ingresos + $cierre->aportaciones, 2) }}
                    </td>
                    <td class="text-end text-danger">-{{ number_format($cierre->egresos + $cierre->sustracciones, 2) }}
                    </td>
                    <td class="text-end fw-bold">{{ number_format($saldoTeorico, 2) }}</td>
                    <td class="text-end fw-bold bg-light">{{ number_format($cierre->monto_cierre, 2) }}</td>
                    <td
                        class="text-end {{ $diferencia < 0 ? 'text-danger' : ($diferencia > 0 ? 'text-success' : '') }}">
                        {{ number_format($diferencia, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <span class="text-muted">No se encontraron registros de cierres de caja</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

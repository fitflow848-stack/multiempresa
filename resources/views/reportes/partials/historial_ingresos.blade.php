<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>HISTORIAL DE INGRESOS (VENTAS Y COBRANZAS)</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Concepto / Referencia</th>
                <th>Método</th>
                <th class="text-end">Monto</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $item->tipo == 'VENTA' ? 'bg-success' : 'bg-primary' }}">
                            {{ $item->tipo }}
                        </span>
                    </td>
                    <td>{{ $item->cliente }}</td>
                    <td class="small">{{ $item->concepto }}</td>
                    <td>{{ $item->metodo }}</td>
                    <td class="text-end fw-bold text-primary">S/ {{ number_format($item->monto, 2) }}</td>
                    <td>{{ $item->usuario }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">No se encontraron movimientos registrados.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($resultados) > 0)
            <tfoot class="table-dark">
                <tr>
                    <th colspan="5" class="text-end">TOTAL DE INGRESOS:</th>
                    <th class="text-end">S/ {{ number_format($total, 2) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

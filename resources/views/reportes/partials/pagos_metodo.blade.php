<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>FINANZAS - PAGOS POR YAPE O TRANSFERENCIA</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Comprobante/Ref.</th>
                <th>Método de Pago</th>
                <th class="text-end">Monto</th>
                <th>Vendedor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $item->tipo == 'Venta Directa' ? 'bg-success' : 'bg-primary' }}">
                            {{ $item->tipo }}
                        </span>
                    </td>
                    <td>{{ $item->cliente }}</td>
                    <td>{{ $item->documento }}</td>
                    <td class="fw-bold">{{ $item->metodo }}</td>
                    <td class="text-end text-primary fw-bold">S/ {{ number_format($item->monto, 2) }}</td>
                    <td>{{ $item->vendedor }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">No se encontraron pagos con los métodos especificados en este rango de fechas.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($resultados) > 0)
            <tfoot class="table-dark">
                <tr>
                    <th colspan="5" class="text-end">TOTAL ACUMULADO:</th>
                    <th class="text-end">S/ {{ number_format($totales->monto, 2) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

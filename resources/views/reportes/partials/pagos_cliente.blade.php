<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>COBRANZA - PAGOS REALIZADOS POR CLIENTE</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Fecha Pago</th>
                <th>Cliente</th>
                <th>Comprobante Pago</th>
                <th>Deuda Refs.</th>
                <th>Método</th>
                <th class="text-end">Monto Pagado</th>
                <th>Cobrado Por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resultados as $pago)
                <tr>
                    <td>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : '' }}</td>
                    <td>{{ $pago->deuda->cliente->nombre ?? 'Sin Cliente' }}</td>
                    <td>{{ $pago->codigo_comprobante }}</td>
                    <td>{{ $pago->deuda->tipo_documento ?? '' }} {{ $pago->deuda->numero_comprobante ?? '' }}</td>
                    <td>{{ ucfirst($pago->metodo_pago) }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($pago->monto, 2) }}</td>
                    <td>{{ $pago->user->name ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <span class="text-muted">No se encontraron pagos registrados</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

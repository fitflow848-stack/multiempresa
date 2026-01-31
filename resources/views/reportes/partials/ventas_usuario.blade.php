<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>VENTAS - DETALLE POR USUARIO</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead class="bg-light">
            <tr>
                <th>Vendedor</th>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Cliente</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentUser = null;
                $subtotal = 0;
            @endphp
            @forelse($resultados as $venta)
                @if ($currentUser !== $venta->id_usuario)
                    @if (!is_null($currentUser))
                        <tr class="bg-light fw-bold">
                            <td colspan="4" class="text-end">Subtotal Vendedor:</td>
                            <td class="text-end">{{ number_format($subtotal, 2) }}</td>
                        </tr>
                        @php $subtotal = 0; @endphp
                    @endif
                    <tr class="table-primary">
                        <td colspan="5" class="fw-bold"><i class="fas fa-user me-2"></i>
                            {{ $venta->user->name ?? 'Usuario Desconocido' }}</td>
                    </tr>
                    @php $currentUser = $venta->id_usuario; @endphp
                @endif

                <tr>
                    <td></td> <!-- Indentado visualmente -->
                    <td>{{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y H:i') : '' }}</td>
                    <td>{{ $venta->serie }}-{{ $venta->numero }}</td>
                    <td>{{ $venta->cliente->nombre ?? 'Publico General' }}</td>
                    <td class="text-end">{{ number_format($venta->total, 2) }}</td>
                </tr>
                @php $subtotal += $venta->total; @endphp

                @if ($loop->last)
                    <tr class="bg-light fw-bold">
                        <td colspan="4" class="text-end">Subtotal Vendedor:</td>
                        <td class="text-end">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <span class="text-muted">No se encontraron ventas</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

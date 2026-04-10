@extends('layouts.print')

@section('title', 'Imprimir Comprobante')

@section('content')
<div class="print-container">
    <div class="company-header text-center mb-3">
        <h3>{{ $company->razon_social }}</h3>
        <p class="mb-1">RUC: {{ $company->ruc }}</p>
        <p class="mb-1">{{ isset($venta) && $venta->sucursal_ref && $venta->sucursal_ref->direccion ? $venta->sucursal_ref->direccion : $company->direccion }}</p>
        <p class="mb-0">Tel: {{ isset($venta) && $venta->sucursal_ref && $venta->sucursal_ref->telefono ? $venta->sucursal_ref->telefono : $company->telefono }}</p>
    </div>

    <hr>

    <div class="document-header mb-3">
        <div class="row">
            <div class="col-6">
                <h4>{{ strtoupper($venta->tipo_documento ?? 'TICKET') }}</h4>
                <p class="mb-1"><strong>Nro:</strong> {{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}</p>
                <p class="mb-1"><strong>Fecha:</strong> {{ $venta->fecha_emision->format('d/m/Y H:i') }}</p>
            </div>
            <div class="col-6 text-end">
                @if($venta->cliente)
                <p class="mb-1"><strong>Cliente:</strong> {{ $venta->cliente->nombre }}</p>
                <p class="mb-1"><strong>{{ $venta->cliente->tipo_documento }}:</strong> {{ $venta->cliente->numero_documento }}</p>
                @if($venta->cliente->direccion)
                <p class="mb-1"><strong>Dirección:</strong> {{ $venta->cliente->direccion }}</p>
                @endif
                @else
                <p class="mb-1"><strong>Cliente:</strong> CLIENTE PARTICULAR</p>
                @endif
            </div>
        </div>
    </div>

    <div class="items-table mb-3">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Descripción</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-end">Desc.</th>
                    <th class="text-end">P.Unit</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $detalle)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ str_replace('(Marca: ', '/ ', str_replace(')', '', $detalle->nombre_servicio ?? ($detalle->producto->nombre ?? '-'))) }}</td>
                    <td class="text-center">{{ $detalle->cantidad }}</td>
                    <td class="text-end">{{ number_format(($detalle->precio_unitario * $detalle->cantidad) - $detalle->importe, 2) }}</td>
                    <td class="text-end">{{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="text-end">{{ number_format($detalle->importe ?? ($detalle->precio_unitario * $detalle->cantidad), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="totals mb-3">
        <div class="row">
            <div class="col-8"></div>
            <div class="col-4">
                <div class="d-flex justify-content-between">
                    <span>Total a pagar:</span>
                    <span>S/ {{ number_format($venta->total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Descuento:</span>
                    <span>S/ {{ number_format($venta->descuento_monto ?? 0, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>IGV:</span>
                    <span>S/ {{ number_format($venta->igv, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2">
                    <strong>Importe total:</strong>
                    <strong>S/ {{ number_format($venta->total, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if($venta->observacion)
    <div class="observations mb-3">
        <strong>Observaciones:</strong>
        <p>{{ $venta->observacion }}</p>
    </div>
    @endif

    <div class="footer text-center mt-4">
        @if($company->bank)
        <div class="bank-accounts mb-3 p-3 border rounded text-start d-inline-block" style="min-width: 300px; background-color: #f8f9fa;">
            <h6 class="mb-2 border-bottom pb-1"><strong>CUENTAS BANCARIAS</strong></h6>
            <p class="mb-1"><strong>Banco:</strong> {{ $company->bank }}</p>
            <p class="mb-1"><strong>{{ $company->account_type == 'corriente' ? 'Cta. Corriente' : 'Cta. Ahorros' }}:</strong> {{ $company->account_number }}</p>
            @if($company->cci)
            <p class="mb-0"><strong>CCI:</strong> {{ $company->cci }}</p>
            @endif
        </div>
        <br>
        @endif

        <p class="mb-1">Estado: 
            <span class="badge bg-{{ $venta->pagado ? 'success' : 'warning' }}">
                {{ $venta->pagado ? 'PAGADO' : 'PENDIENTE' }}
            </span>
        </p>
        <p class="mb-1 mt-2"><strong>{{ $company->ticket_footer_message ?? 'Gracias por su preferencia' }}</strong></p>
        <small class="text-muted">Documento generado el {{ now()->format('d/m/Y H:i') }}</small>
    </div>
</div>

@push('styles')
<style>
@media print {
    .print-container {
        font-size: 12px;
    }
    
    .company-header h3 {
        font-size: 16px;
    }
    
    .document-header h4 {
        font-size: 14px;
    }
}

.print-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-imprimir al cargar la página
    window.print();
});
</script>
@endpush
@endsection
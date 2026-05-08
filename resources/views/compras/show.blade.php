@extends('layout.app')

@section('title', 'Detalle Compra')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Ticket #{{ $compra->id }}</h1>
            <small class="text-muted">Detalle y resumen de la compra</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">Volver</a>
            <a href="{{ route('compras.pdf', $compra->id) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-print me-1"></i>Imprimir
            </a>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalBarcodesPdf">
                <i class="bx bx-barcode me-1"></i>Etiquetas PDF
            </button>
            @if(!$compra->received_at)
                <a href="{{ route('recibir-productos.index', ['id' => $compra->id]) }}" class="btn btn-primary">Recibir Ticket</a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-5">
                            <strong>Proveedor</strong>
                            <div>{{ $compra->proveedor_nombre ?? $compra->proveedor_id ?? '—' }}</div>
                        </div>
                        <div class="col-md-2">
                            <strong>Fecha Emisión</strong>
                            <div>{{ $compra->fecha_emision ? \Carbon\Carbon::parse($compra->fecha_emision)->format('d/m/Y') : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Tipo</strong>
                            <div>{{ $compra->tipo ?? '—' }} ({{ $compra->serie_comprobante }}-{{ $compra->numero_comprobante }})</div>
                        </div>
                        <div class="col-md-2">
                            <strong>Condición</strong>
                            <div>
                                @if($compra->credito)
                                    <span class="badge bg-info">Crédito</span>
                                @else
                                    <span class="badge bg-secondary">Contado</span>
                                @endif

                                @if($compra->inc_impuesto)
                                    <span class="badge bg-success">INC. IGV</span>
                                @else
                                    <span class="badge bg-warning text-dark">SIN IGV</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-3">Detalle</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Costo</th>
                                    <th class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($compra->lineas as $i => $ln)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $ln->descripcion }}</td>
                                        <td class="text-end">{{ $ln->cantidad }}</td>
                                        <td class="text-end">{{ number_format($ln->costo, 2) }}</td>
                                        <td class="text-end">{{ number_format(($ln->costo ?? 0) * ($ln->cantidad ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Resumen</div>
                <div class="card-body">
                    @php
                        $tasa = 0.18;
                        $totalPagar = $compra->total_pagar ?? 0;
                        $descuento = $compra->total_descuento ?? 0;

                        if ($compra->inc_impuesto) {
                            // Si incluyó IGV: El total ya tiene el 18%.
                            // Base = Total / 1.18
                            // IGV = Total - Base
                            $base = $totalPagar / (1 + $tasa);
                            $impuesto = $totalPagar - $base;
                        } else {
                            // Si NO incluyó IGV: Subtotal es el neto
                            $base = $compra->total_bruto - $descuento; 
                            $impuesto = $base * $tasa;
                        }
                    @endphp

                    <p class="mb-1"><strong>Precio sin IGV:</strong></p>
                    <div class="mb-2 h5">S/ {{ number_format($base, 2) }}</div>

                    <p class="mb-1"><strong>Total Descuento:</strong></p>
                    <div class="mb-2">S/ {{ number_format($descuento, 2) }}</div>

                    <p class="mb-1"><strong>IGV (18%):</strong></p>
                    <div class="mb-2">S/ {{ number_format($impuesto, 2) }}</div>

                    <hr>
                    <p class="mb-1"><strong>Total a Pagar</strong></p>
                    <div class="h4">S/ {{ number_format($compra->total_pagar ?? 0, 2) }}</div>

                    @php
                        $received = null;
                        if (!empty($compra->received_at)) {
                            $received = $compra->received_at instanceof \DateTime ? $compra->received_at : \Carbon\Carbon::parse($compra->received_at);
                        }
                    @endphp

                    <div class="mt-3">
                        <small class="text-muted">Recepción:</small>
                        <div>{{ $received ? $received->format('d/m/Y H:i') : 'No recibido' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Etiquetas Barcodes -->
<div class="modal fade" id="modalBarcodesPdf" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:#1e293b; color:#fff;">
                <h5 class="modal-title"><i class="bx bx-barcode me-2"></i>Configurar Etiquetas a Imprimir</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Ajusta la cantidad de etiquetas por producto. Desmarca los que no quieras imprimir.</p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30px;"></th>
                                <th>Producto</th>
                                <th style="width:130px;">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($almacenDetalles as $det)
                                <tr>
                                    <td><input type="checkbox" class="form-check-input chk-bcp" checked data-id="{{ $det->id }}"></td>
                                    <td class="small fw-semibold">{{ $det->producto->nombre ?? '-' }}</td>
                                    <td><input type="number" class="form-control form-control-sm qty-bcp" value="{{ (int)$det->cantidad ?: 1 }}" min="1" step="1"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="btn-generar-barcodes-compra">
                    <i class="fas fa-file-pdf me-1"></i>Generar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<form id="form-barcodes-compra" action="{{ route('almacen.barcodes-pdf') }}" method="POST" target="_blank" style="display:none;">
    @csrf
    <div id="form-barcodes-inputs"></div>
</form>

@push('scripts')
<script>
    document.getElementById('btn-generar-barcodes-compra').addEventListener('click', function() {
        const filas = document.querySelectorAll('#modalBarcodesPdf tbody tr');
        const container = document.getElementById('form-barcodes-inputs');
        container.innerHTML = '';
        let idx = 0;
        filas.forEach(tr => {
            const chk = tr.querySelector('.chk-bcp');
            const qty = parseInt(tr.querySelector('.qty-bcp').value) || 0;
            if (!chk.checked || qty < 1) return;
            container.innerHTML += `<input type="hidden" name="productos[${idx}][id]" value="${chk.getAttribute('data-id')}">`;
            container.innerHTML += `<input type="hidden" name="productos[${idx}][qty]" value="${qty}">`;
            idx++;
        });
        if (idx === 0) { alert('No hay productos seleccionados con cantidad válida.'); return; }
        bootstrap.Modal.getInstance(document.getElementById('modalBarcodesPdf')).hide();
        document.getElementById('form-barcodes-compra').submit();
    });
</script>
@endpush
@endsection

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
                <a href="{{ route('compras.receive', $compra->id) }}" class="btn btn-primary">Recibir Ticket</a>
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
                                    <th class="text-center">Precios</th>
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
                                        <td class="text-center">
                                            @if ($ln->product_id)
                                                @if ($compra->recibido)
                                                    {{-- Ya recibido: estos son los precios realmente aplicados en la recepción --}}
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-info btn-ver-precios"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#preciosProductoModal"
                                                            data-nombre="{{ $ln->descripcion }}"
                                                            data-pvp="{{ number_format($ln->pvp ?? 0, 2, '.', '') }}"
                                                            data-pvp-dto="{{ number_format($ln->pvp_dto ?? 0, 2, '.', '') }}"
                                                            data-pvc="{{ number_format($ln->pvc ?? 0, 2, '.', '') }}"
                                                            data-pvc-dto="{{ number_format($ln->pvc_dto ?? 0, 2, '.', '') }}"
                                                            data-pv-docena="{{ number_format(optional($ln->producto)->pv_docena ?? 0, 2, '.', '') }}"
                                                            title="Ver precios">
                                                        <i class="bx bx-dollar"></i>
                                                    </button>
                                                @else
                                                    {{-- Pendiente de recibir: mostrar los precios ACTUALES vigentes
                                                         (no los fijados cuando se creó el ticket), que son los que
                                                         se aplicarán por defecto al recibir. --}}
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-info btn-ver-precios-actuales"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#preciosProductoModal"
                                                            data-nombre="{{ $ln->descripcion }}"
                                                            data-linea-id="{{ $ln->id }}"
                                                            title="Ver precios actuales">
                                                        <i class="bx bx-dollar"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted small mb-0">Ajusta la cantidad de etiquetas por producto. Desmarca los que no quieras imprimir.</p>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 fw-semibold small">Precio:</label>
                        <select id="compra-price-selector" class="form-select form-select-sm" style="width:120px;">
                            <option value="pvp">PVP</option>
                            <option value="pvpd">PVP Dto.</option>
                            <option value="pvc">PVC</option>
                            <option value="pvcd">PVC Dto.</option>
                        </select>
                    </div>
                </div>
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
    <input type="hidden" name="price_type" id="compra-price-type-input" value="pvp">
    <div id="form-barcodes-inputs"></div>
</form>

<!-- Modal: precios del producto (solo lectura) -->
<div class="modal fade" id="preciosProductoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Precios del producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <strong id="precios-producto-nombre"></strong>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label text-primary fw-bold small mb-0">PVP (Soles)</label>
                        <div class="fw-semibold" id="precios-pvp">—</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-warning fw-bold small mb-0">PVP Dcto.</label>
                        <div class="fw-semibold" id="precios-pvp-dto">—</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-info fw-bold small mb-0">PVC (Corp.)</label>
                        <div class="fw-semibold" id="precios-pvc">—</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-primary fw-bold small mb-0">PVC Dcto.</label>
                        <div class="fw-semibold" id="precios-pvc-dto">—</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-success fw-bold small mb-0">PV Docena</label>
                        <div class="fw-semibold" id="precios-pv-docena">—</div>
                    </div>
                    <div class="col-12" id="precios-nota-actual" style="display:none;">
                        <small class="text-warning">
                            <i class="bx bx-info-circle"></i>
                            Precios vigentes ahora mismo (no lo fijado al crear el ticket). Son los que se aplicarán por defecto al recibir, salvo que los edites en la recepción.
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.btn-ver-precios').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('precios-nota-actual').style.display = 'none';
            document.getElementById('precios-producto-nombre').textContent = this.getAttribute('data-nombre') || '';
            document.getElementById('precios-pvp').textContent = 'S/ ' + parseFloat(this.getAttribute('data-pvp') || 0).toFixed(2);
            document.getElementById('precios-pvp-dto').textContent = 'S/ ' + parseFloat(this.getAttribute('data-pvp-dto') || 0).toFixed(2);
            document.getElementById('precios-pvc').textContent = 'S/ ' + parseFloat(this.getAttribute('data-pvc') || 0).toFixed(2);
            document.getElementById('precios-pvc-dto').textContent = 'S/ ' + parseFloat(this.getAttribute('data-pvc-dto') || 0).toFixed(2);
            document.getElementById('precios-pv-docena').textContent = 'S/ ' + parseFloat(this.getAttribute('data-pv-docena') || 0).toFixed(2);
        });
    });

    // Ticket aún no recibido: mostrar los precios vigentes ahora mismo
    // (los que realmente se aplicarán al recibir), no los fijados al crear el ticket.
    document.querySelectorAll('.btn-ver-precios-actuales').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const lineaId = this.getAttribute('data-linea-id');
            document.getElementById('precios-nota-actual').style.display = '';
            document.getElementById('precios-producto-nombre').textContent = this.getAttribute('data-nombre') || '';
            ['precios-pvp', 'precios-pvp-dto', 'precios-pvc', 'precios-pvc-dto', 'precios-pv-docena']
                .forEach(id => document.getElementById(id).textContent = 'Cargando...');

            fetch(`{{ url('compras/' . $compra->id . '/recibir/precios/linea') }}/${lineaId}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('precios-pvp').textContent = 'S/ ' + parseFloat(data.pvp || 0).toFixed(2);
                    document.getElementById('precios-pvp-dto').textContent = 'S/ ' + parseFloat(data.pvp_dto || 0).toFixed(2);
                    document.getElementById('precios-pvc').textContent = 'S/ ' + parseFloat(data.pvc || 0).toFixed(2);
                    document.getElementById('precios-pvc-dto').textContent = 'S/ ' + parseFloat(data.pvc_dto || 0).toFixed(2);
                    document.getElementById('precios-pv-docena').textContent = 'S/ ' + parseFloat(data.pv_docena || 0).toFixed(2);
                })
                .catch(() => {
                    ['precios-pvp', 'precios-pvp-dto', 'precios-pvc', 'precios-pvc-dto', 'precios-pv-docena']
                        .forEach(id => document.getElementById(id).textContent = '—');
                });
        });
    });

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
        document.getElementById('compra-price-type-input').value = document.getElementById('compra-price-selector').value;
        bootstrap.Modal.getInstance(document.getElementById('modalBarcodesPdf')).hide();
        document.getElementById('form-barcodes-compra').submit();
    });
</script>
@endpush
@endsection

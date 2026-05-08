@extends('layout.app')

@section('title', 'Inventario Stock Almacén')
@section('page-title', 'Inventario Stock Almacén')

@section('content')
    <style>
        :root {
            --primary-soft: #eef2ff;
            --accent-color: #6366f1;
        }

        .bg-gradient-inventory {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        /* Estilos para Stock */
        .badge-stock {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
        }

        .stock-high {
            background: #dcfce7;
            color: #15803d;
        }

        .stock-low {
            background: #fee2e2;
            color: #b91c1c;
        }

        .product-cell {
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .product-meta {
            font-size: 0.75rem;
            color: #64748b;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.65rem 1rem;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-1">Control de Inventario</h2>
                <p class="text-muted mb-0">Monitorización de activos y existencias en tiempo real</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="{{ route('almacen.import-template') }}" class="btn btn-outline-success me-2 border-0 shadow-sm" style="background: #f0fdf4; color: #15803d;">
                    <i class="fas fa-file-download me-2"></i>Plantilla Excel
                </a>
                <button type="button" class="btn btn-outline-primary me-2 border-0 shadow-sm" style="background: #eff6ff; color: #1d4ed8;" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-upload me-2"></i>Importar Excel
                </button>
                <button type="button" class="btn btn-dark me-2 shadow-sm" id="btn-etiquetas-pdf-almacen">
                    <i class="bx bx-barcode me-1"></i>Etiquetas PDF
                </button>
                <a href="{{ route('almacen.alta-rapida') }}"
                    class="btn btn-primary px-4 py-2 shadow-sm bg-gradient-inventory border-0">
                    <i class="fas fa-bolt me-2"></i>Alta Rápida de Stock
                </a>
            </div>
        </div>
        <div class="table-container shadow-sm">
            <form method="GET" action="{{ route('almacen.index') }}" class="row g-3 mb-4 border-bottom pb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i
                                class="bx bx-search"></i></span>
                        <input type="text" name="producto" class="form-control border-start-0"
                            placeholder="Producto o código..." value="{{ request('producto') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="sucursal" class="form-select">
                        <option value="">Todos los locales</option>
                        @foreach ($sucursales ?? [] as $s)
                            <option value="{{ $s->id }}" {{ request('sucursal') == $s->id ? 'selected' : '' }}>
                                {{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="existencias" class="form-select">
                        <option value="">Stock: Todos</option>
                        <option value="con" {{ request('existencias') == 'con' ? 'selected' : '' }}>Con Stock</option>
                        <option value="sin" {{ request('existencias') == 'sin' ? 'selected' : '' }}>Sin Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="codigo" class="form-control" placeholder="EAN / SKU"
                        value="{{ request('codigo') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold">Filtrar</button>
                    <a href="{{ route('almacen.index') }}" class="btn btn-light border" title="Limpiar"><i
                            class="bx bx-refresh"></i></a>
                </div>  
            </form>

            @if (isset($productos) && $productos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 py-3 text-center" style="width: 40px;">
                                    <input type="checkbox" id="check-all-labels" class="form-check-input">
                                </th>
                                <th class="border-0 text-muted py-3" style="font-size: 0.8rem;">PRODUCTO / INFO</th>
                                <th class="border-0 text-muted py-3" style="font-size: 0.8rem;">LOCAL</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">COSTO</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">PVP</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">PVP DTO</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">PVC</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">PVC DTO</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">PV DOCENA</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">STOCK ACTUAL</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">CÓDIGO</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">OPERACIONES
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $p)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input check-label" value="{{ $p->id }}"
                                            data-nombre="{{ $p->producto ?? '-' }}"
                                            data-concentracion="{{ $p->concentracion ?? '' }}">
                                    </td>
                                    <td>
                                        <div class="product-cell">
                                            <span class="product-title">{{ $p->producto ?? '-' }}</span>
                                            <div class="d-flex gap-2 mt-1">
                                                @if($p->presentacion)
                                                    <span class="badge bg-soft-info text-info border-info" style="font-size: 0.65rem; background: #e0f2fe;">{{ $p->presentacion }}</span>
                                                @endif
                                                @if($p->concentracion)
                                                    <span class="badge bg-soft-primary text-primary border-primary" style="font-size: 0.65rem; background: #eef2ff;">{{ $p->concentracion }}</span>
                                                @endif
                                            </div>
                                            <span class="product-meta mt-1"><i class="fas fa-barcode me-1"></i>
                                                {{ $p->codigo ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><i class="fas fa-store me-1"></i>
                                            {{ $p->almacen_nombre ?? 'Principal' }}</span>
                                    </td>
                                    <td class="text-center fw-semibold text-muted">
                                        {{ isset($p->costo) ? 'S/. ' . number_format($p->costo, 2) : '-' }}
                                    </td>
                                    <td class="text-center fw-bold text-success">
                                        {{ isset($p->pvp) ? 'S/. ' . number_format($p->pvp, 2) : '-' }}
                                    </td>
                                    <td class="text-center text-muted">
                                        {{ isset($p->pvpd) ? 'S/. ' . number_format($p->pvpd, 2) : '-' }}
                                    </td>
                                    <td class="text-center text-primary">
                                        {{ isset($p->pvc) ? 'S/. ' . number_format($p->pvc, 2) : '-' }}
                                    </td>
                                    <td class="text-center text-muted">
                                        {{ isset($p->pvcd) ? 'S/. ' . number_format($p->pvcd, 2) : '-' }}
                                    </td>
                                    <td class="text-center text-info">
                                        {{ isset($p->pv_docena) ? 'S/. ' . number_format($p->pv_docena, 2) : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge-stock {{ ($p->existencias ?? 0) > 0 ? 'stock-high' : 'stock-low' }}">
                                            {{ number_format($p->existencias ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-light border btn-show-barcode"
                                                data-id="{{ $p->id }}"
                                                data-barcode="{{ $p->codigo }}"
                                                data-name="{{ $p->producto }}">
                                            <i class="bx bx-barcode"></i>
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">

                                            <a href="{{ route('almacen.kardex', ['producto_id' => $p->producto_id]) }}"
                                                class="btn btn-sm btn-white border shadow-sm px-2 rounded-pill text-info fw-bold" title="Ver Kardex">
                                                <i class="bx bx-history"></i>
                                            </a>
                                            <a href="{{ route('almacen.edit', $p->id) }}"
                                                class="btn btn-sm btn-white border shadow-sm px-2 rounded-pill text-warning fw-bold">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <a href="{{ route('almacen.ajustar-existencias', $p->id) }}"
                                                class="btn btn-sm btn-white border shadow-sm px-2 rounded-pill text-primary fw-bold" title="Ajustar Stock">
                                                <i class="bx bx-slider"></i>
                                            </a>
                                            <button type="button" 
                                                class="btn btn-sm btn-white border shadow-sm px-2 rounded-pill text-danger fw-bold btn-delete-product" 
                                                data-id="{{ $p->id }}" data-name="{{ $p->producto }}" title="Eliminar Producto">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Mostrando {{ $productos->firstItem() }} al {{ $productos->lastItem() }} de
                        {{ $productos->total() }} registros
                    </div>
                    <div>
                        {{ $productos->withQueryString()->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bx bx-search fa-4x text-light"></i>
                    </div>
                    <h4 class="text-muted fw-light">Sin coincidencias para la búsqueda</h4>
                    <a href="{{ route('almacen.index') }}" class="btn btn-primary mt-3">Restablecer filtros</a>
                </div>
            @endif
        </div>
    </div>

 

    <!-- Modal para ver Código de Barras -->
    <div class="modal fade" id="barcodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius: 1.5rem;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="barcodeModalLabel">Código de Barras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <h6 id="barcodeProductName" class="text-muted mb-3 small"></h6>
                    <div class="barcode-container p-4 bg-white border rounded-3 mb-3 d-inline-block">
                        <svg id="barcode-svg"></svg>
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                        <label class="form-label mb-0 fw-semibold small">Cantidad de etiquetas:</label>
                        <input type="number" id="barcode-qty-input" class="form-control form-control-sm" value="1" min="1" style="width:80px;">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="btn-print-barcode">
                            <i class="bx bx-file-pdf me-2"></i>Imprimir Etiqueta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Importar Excel -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius: 1.5rem;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Importar Productos desde Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('almacen.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 shadow-none mb-4" style="background: #f0f9ff; color: #0c4a6e; border-radius: 1rem;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-3 fa-lg"></i>
                                <div>
                                    <p class="mb-1 fw-bold">Guía Rápida:</p>
                                    <p class="small mb-2">1. Descarga la <a href="{{ route('almacen.import-template') }}" class="fw-bold text-decoration-underline">plantilla oficial</a>.</p>
                                    <p class="small mb-2">2. Completa los datos requeridos (Nombre, EAN, Precios).</p>
                                    <p class="small mb-0">3. Sube el archivo para procesar masivamente.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Seleccionar archivo Excel</label>
                            <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text mt-2" style="font-size: 0.75rem;">Máximo 10MB (Formatos: .xlsx, .xls, .csv)</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 bg-gradient-inventory border-0">
                            <i class="fas fa-rocket me-2"></i>Iniciar Importación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>

    <!-- BOTÓN FLOTANTE PARA IMPRESIÓN MASIVA -->
    <div id="bulk-print-container" style="display:none; position: fixed; bottom: 30px; right: 30px; z-index: 9999;">
        <button type="button" class="btn btn-dark btn-lg shadow-lg px-4 rounded-pill" id="btn-bulk-print">
            <i class="fas fa-print me-2"></i>Imprimir Etiquetas (<span id="selected-count">0</span>)
        </button>
    </div>

    <!-- FORMULARIO OCULTO PARA PDF MASIVO -->
    <form id="form-bulk-pdf" action="{{ route('almacen.barcodes-pdf') }}" method="POST" target="_blank" style="display:none;">
        @csrf
        <div id="form-inputs-container"></div>
    </form>

    <!-- FORMULARIO OCULTO PARA PDF INDIVIDUAL -->
    <form id="form-single-pdf" action="{{ route('almacen.barcodes-pdf') }}" method="POST" target="_blank" style="display:none;">
        @csrf
        <input type="hidden" id="single-pdf-id" name="productos[0][id]" value="">
        <input type="hidden" id="single-pdf-qty" name="productos[0][qty]" value="1">
    </form>

    <!-- MODAL CANTIDADES POR PRODUCTO -->
    <div class="modal fade" id="modalCantidadesPdf" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:#1e293b; color:#fff;">
                    <h5 class="modal-title"><i class="fas fa-print me-2"></i>Configurar Etiquetas a Imprimir</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Ajusta la cantidad de etiquetas por producto. Deselecciona los que no quieras imprimir.</p>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="tabla-cantidades-pdf">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:30px;"></th>
                                    <th>Producto</th>
                                    <th style="width:130px;">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-cantidades-pdf"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-dark" id="btn-generar-pdf-final">
                        <i class="fas fa-file-pdf me-1"></i>Generar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lógica de Selección Masiva
            const checkAll = document.getElementById('check-all-labels');
            const rowChecks = document.querySelectorAll('.check-label');
            const bulkContainer = document.getElementById('bulk-print-container');
            const selectedCountSpan = document.getElementById('selected-count');
            const btnBulkPrint = document.getElementById('btn-bulk-print');

            function updateBulkVisibility() {
                const checked = document.querySelectorAll('.check-label:checked');
                selectedCountSpan.textContent = checked.length;
                bulkContainer.style.display = checked.length > 0 ? 'block' : 'none';
            }

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    rowChecks.forEach(chk => chk.checked = checkAll.checked);
                    updateBulkVisibility();
                });
            }

            rowChecks.forEach(chk => {
                chk.addEventListener('change', updateBulkVisibility);
            });

            const modalCantidades = new bootstrap.Modal(document.getElementById('modalCantidadesPdf'));

            function cargarProductosEnModal(checks) {
                const tbody = document.getElementById('tbody-cantidades-pdf');
                tbody.innerHTML = '';
                checks.forEach(chk => {
                    const nombre = chk.getAttribute('data-nombre') || 'Producto';
                    const concentracion = chk.getAttribute('data-concentracion') || '';
                    const label = concentracion ? `${nombre} <small class="text-muted">${concentracion}</small>` : nombre;
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><input type="checkbox" class="form-check-input chk-incluir" checked data-id="${chk.value}"></td>
                        <td class="small fw-semibold">${label}</td>
                        <td><input type="number" class="form-control form-control-sm qty-etiqueta" value="1" min="1" step="1"></td>
                    `;
                    tbody.appendChild(tr);
                });
                modalCantidades.show();
            }

            // Botón fijo en header: carga TODOS los productos de la página
            document.getElementById('btn-etiquetas-pdf-almacen').addEventListener('click', function() {
                const allChecks = document.querySelectorAll('.check-label');
                cargarProductosEnModal(Array.from(allChecks));
            });

            // Botón flotante: carga solo los seleccionados con checkbox
            btnBulkPrint.addEventListener('click', function() {
                const checked = document.querySelectorAll('.check-label:checked');
                cargarProductosEnModal(Array.from(checked));
            });

            document.getElementById('btn-generar-pdf-final').addEventListener('click', function() {
                const filas = document.querySelectorAll('#tbody-cantidades-pdf tr');
                const container = document.getElementById('form-inputs-container');
                container.innerHTML = '';
                let idx = 0;
                filas.forEach(tr => {
                    const chk = tr.querySelector('.chk-incluir');
                    const qty = parseInt(tr.querySelector('.qty-etiqueta').value) || 0;
                    if (!chk.checked || qty < 1) return;

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = `productos[${idx}][id]`;
                    idInput.value = chk.getAttribute('data-id');

                    const qtyInput = document.createElement('input');
                    qtyInput.type = 'hidden';
                    qtyInput.name = `productos[${idx}][qty]`;
                    qtyInput.value = qty;

                    container.appendChild(idInput);
                    container.appendChild(qtyInput);
                    idx++;
                });

                if (idx === 0) {
                    Swal.fire('Atención', 'No hay productos seleccionados con cantidad válida.', 'warning');
                    return;
                }
                modalCantidades.hide();
                document.getElementById('form-bulk-pdf').submit();
            });

            const barcodeModal = new bootstrap.Modal(document.getElementById('barcodeModal'));
            const barcodeSvg = document.getElementById('barcode-svg');
            const productNameEl = document.getElementById('barcodeProductName');
            const printBtn = document.getElementById('btn-print-barcode');
            let currentBarcodeId = '';

            document.querySelectorAll('.btn-show-barcode').forEach(btn => {
                btn.addEventListener('click', function() {
                    const code = this.getAttribute('data-barcode');
                    const name = this.getAttribute('data-name');
                    currentBarcodeId = this.getAttribute('data-id');

                    productNameEl.textContent = name;
                    document.getElementById('barcode-qty-input').value = 1;

                    if (code) {
                        JsBarcode("#barcode-svg", code, {
                            format: code.length === 13 ? "EAN13" : "CODE128",
                            width: 2,
                            height: 80,
                            displayValue: true
                        });
                        barcodeModal.show();
                    } else {
                        Swal.fire('Atención', 'Este producto no tiene código de barras asignado.', 'warning');
                    }
                });
            });

            printBtn.addEventListener('click', function() {
                const qty = parseInt(document.getElementById('barcode-qty-input').value) || 1;
                document.getElementById('single-pdf-id').value = currentBarcodeId;
                document.getElementById('single-pdf-qty').value = qty;
                barcodeModal.hide();
                document.getElementById('form-single-pdf').submit();
            });

            // Lógica para eliminar producto
            document.querySelectorAll('.btn-delete-product').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: `Vas a eliminar el producto "${name}" de todo el sistema. Esta acción no se puede deshacer y borrará el stock en todos los locales.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`{{ url('almacen/destroy') }}/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        '¡Eliminado!',
                                        data.message,
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error',
                                        data.message,
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire(
                                    'Error',
                                    'Ocurrió un error inesperado al intentar eliminar el producto.',
                                    'error'
                                );
                            });
                        }
                    });
                });
            });
        });

        // Persistencia de filtros para Almacén
        (function() {
            const form = document.querySelector('form[action="{{ route('almacen.index') }}"]');
            if (form) {
                form.addEventListener('submit', function() {
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    sessionStorage.setItem('almacen_last_query', params.toString());
                });
            }

            // Restaurar si entramos "limpio"
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                // Si no hay parámetros (excepto tal vez 'page'), restaurar el último
                if (!urlParams.has('producto') && !urlParams.has('sucursal') && !urlParams.has('existencias') && !urlParams.has('codigo')) {
                    const lastQuery = sessionStorage.getItem('almacen_last_query');
                    if (lastQuery) {
                        window.location.search = lastQuery;
                    }
                }
            });
        })();
    </script>
    @endpush

@endsection

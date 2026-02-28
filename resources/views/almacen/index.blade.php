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
                                class="fas fa-search"></i></span>
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
                                <th class="border-0 text-muted py-3" style="font-size: 0.8rem;">PRODUCTO / INFO</th>
                                <th class="border-0 text-muted py-3" style="font-size: 0.8rem;">LOCAL</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">COSTO</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">VENTA (PVP)</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">STOCK ACTUAL
                                </th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">CÓDIGO</th>
                                <th class="border-0 text-muted py-3 text-center" style="font-size: 0.8rem;">OPERACIONES
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $p)
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <span class="product-title">{{ $p->producto ?? '-' }}</span>
                                            <span class="product-meta"><i class="fas fa-barcode me-1"></i>
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
                                    <td class="text-center">
                                        <span
                                            class="badge-stock {{ ($p->existencias ?? 0) > 0 ? 'stock-high' : 'stock-low' }}">
                                            {{ number_format($p->existencias ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-light border btn-show-barcode" 
                                                data-barcode="{{ $p->codigo }}" data-name="{{ $p->producto }}">
                                            <i class="bx bx-barcode"></i>
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('almacen.edit', $p->id) }}"
                                                class="btn btn-sm btn-white border shadow-sm px-2 rounded-pill text-warning fw-bold">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <a href="{{ route('almacen.ajustar-existencias', $p->id) }}"
                                                class="btn btn-sm btn-white border shadow-sm px-2 rounded-pill text-primary fw-bold">
                                                <i class="bx bx-slider"></i>
                                            </a>
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
                        <i class="fas fa-search fa-4x text-light"></i>
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
                    <h6 id="barcodeProductName" class="text-muted mb-4 small"></h6>
                    <div class="barcode-container p-4 bg-white border rounded-3 mb-4 d-inline-block">
                        <svg id="barcode-svg"></svg>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="btn-print-barcode">
                            <i class="fas fa-print me-2"></i>Imprimir Etiqueta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const barcodeModal = new bootstrap.Modal(document.getElementById('barcodeModal'));
            const barcodeSvg = document.getElementById('barcode-svg');
            const productNameEl = document.getElementById('barcodeProductName');
            const printBtn = document.getElementById('btn-print-barcode');
            let currentBarcode = '';

            document.querySelectorAll('.btn-show-barcode').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentBarcode = this.getAttribute('data-barcode');
                    const name = this.getAttribute('data-name');
                    
                    productNameEl.textContent = name;
                    
                    if (currentBarcode) {
                        JsBarcode("#barcode-svg", currentBarcode, {
                            format: currentBarcode.length === 13 ? "EAN13" : "CODE128",
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
                const svgContent = barcodeSvg.outerHTML;
                const name = productNameEl.textContent;
                const printWindow = window.open('', '_blank', 'width=600,height=400');
                
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Etiqueta - ${name}</title>
                            <style>
                                body { display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; font-family: sans-serif; }
                                .label { text-align: center; padding: 20px; border: 1px dashed #ccc; }
                                .name { font-size: 14px; margin-bottom: 10px; font-weight: bold; }
                            </style>
                        </head>
                        <body>
                            <div class="label">
                                <div class="name">${name}</div>
                                ${svgContent}
                            </div>
                            <script>
                                window.onload = function() {
                                    window.print();
                                    setTimeout(function() { window.close(); }, 500);
                                };
                            <\/script>
                        </body>
                    </html>
                `);
                printWindow.document.close();
            });
        });
    </script>
    @endpush

@endsection

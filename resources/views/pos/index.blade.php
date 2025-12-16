@extends('layout.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
    <div class="pos-container">
        <div class="left-sidebar">
            <div class="company-header">
                <div>PURINA</div>
                <div class="company-options">
                    <div><i class="fa-solid fa-user-group"></i> Clientes</div>
                    <div><i class="fa-solid fa-book-open"></i> Comprobantes</div>
                    <div><i class="fa-solid fa-money-bill-wave"></i> Caja</div>
                    <div><i class="fa-solid fa-user"></i> {{ Auth::user()->name }}</div>
                    <div><i class="fa-solid fa-shop"></i> TPV VD</div>
                </div>
            </div>

            <div class="top-bar">
                <!-- Sección Izquierda -->
                <div class="left-section">
                    <div class="families-section">
                        <span>📦</span>
                        <span style="font-weight: bold;">Familias</span>
                        <span>📋</span>
                    </div>

                    <div class="search-fields">
                        <input type="text" class="search-input" placeholder="Código de Barras">
                        <input type="text" class="search-input" placeholder="Nombre | Marca | Modelo | Detalle">
                    </div>

                    <div class="search-buttons">
                        <button class="search-btn">📋</button>
                        <button class="search-btn">📄</button>
                    </div>
                </div>

                <!-- Sección Derecha -->
                <div class="right-section">
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <span>🛒</span>
                    </div>

                    <div class="payment-options">
                        <div class="payment-option">
                            <input type="radio" name="payment" checked>
                            <span>Contado</span>
                        </div>
                        <div class="payment-option">
                            <input type="radio" name="payment">
                            <span>Crédito</span>
                        </div>
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <input type="checkbox" name="Proforma">
                            <span>Proforma</span>
                        </div>
                    </div>

                    <div style="color: #666; font-size: 11px;">
                        SOL -
                    </div>
                </div>
            </div>

        </div>

        <!-- Contenido Principal -->
        <div class="main-content">
            <!-- Área Central -->
            <div class="center-area">
                <div class="genack-watermark">genack</div>
                <div style="color: #ccc; font-size: 11px;">core business</div>
                <div style="margin-top: 20px; text-align: center; line-height: 1.5;">
                    <div style="color: #999;">Selecciona una familia</div>
                    <div style="color: #999;">para ver productos</div>
                </div>
            </div>

            <!-- Sección del Ticket -->
            <div class="ticket-section">
                <div class="ticket-header">
                    TICKET ACTUAL
                    <span>▷</span>
                </div>

                <div class="ticket-controls">
                    <div class="controls-left">
                        <button class="control-btn">X</button>
                        <button class="control-btn primary">Ctrl.</button>
                        <button class="control-btn">Prec.</button>
                        <button class="control-btn">🗑️</button>
                    </div>

                    <div class="controls-right">
                        <button class="control-btn danger">❌ Cancelar</button>
                        <button class="control-btn primary">💾 Guardar</button>
                        <button class="control-btn success">📤 Emitir</button>
                    </div>
                </div>

                <div class="ticket-table">
                    <div class="table-header">
                        <div>Producto</div>
                        <div>Ctd.</div>
                        <div>Dsdo.</div>
                        <div>Impuesto</div>
                        <div>PVU</div>
                        <div>Importe</div>
                    </div>
                    <div class="table-body">
                        <div class="empty-message">
                            <div style="margin-bottom: 15px; color: #ddd;">El ticket está vacío</div>
                            <div style="font-size: 10px; color: #bbb;">Agrega productos desde el panel izquierdo</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="pos-footer">
                <div class="footer-totals">
                    <span>Gravada <span class="footer-total">S/ 0.00</span></span>
                    <span>IGV <span class="footer-total">S/ 0.00</span></span>
                </div>
                <div class="footer-info">
                    <!-- Información adicional del footer -->
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('POS Sistema cargado correctamente');
            });
        </script>
    @endpush
@endsection

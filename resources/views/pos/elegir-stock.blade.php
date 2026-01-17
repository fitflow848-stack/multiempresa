@extends('layout.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
    <style>
        .elegir-stock-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .header-section {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-info {
            font-size: 12px;
        }

        .title-section {
            background: #34495e;
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        .product-section {
            background: white;
            padding: 20px;
            border-bottom: 3px solid #3498db;
        }

        .product-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .product-code {
            color: #e74c3c;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .product-name {
            font-size: 14px;
            color: #2c3e50;
            font-weight: bold;
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .stock-table th {
            background: #3498db;
            color: white;
            padding: 10px 8px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }

        .stock-table td {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
        }

        .stock-table tbody tr:hover {
            background-color: #e8f4f8;
        }

        .cantidad-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 4px;
            border-radius: 3px;
        }

        .price-section {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            justify-content: center;
        }

        .price-info {
            text-align: center;
        }

        .price-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .price-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .totals-section {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .totals-row.total {
            font-weight: bold;
            font-size: 14px;
            color: #2c3e50;
            border-top: 2px solid #bdc3c7;
            padding-top: 8px;
            margin-top: 10px;
        }

        .actions-section {
            text-align: center;
            margin-top: 20px;
        }

        .btn-volver {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-volver:hover {
            background: #2980b9;
        }

        .btn-agregar {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-agregar:hover {
            background: #45a049;
        }

        .stock-seleccionado {
            color: #27ae60;
            font-weight: bold;
        }

        .stock-cero {
            color: #95a5a6;
            font-style: italic;
        }
    </style>

    <div class="elegir-stock-container">
        <!-- Title -->
        <div class="title-section">
            Carrito TPV - Elegir Stock
        </div>

        <!-- Product Info -->
        <div class="product-section">
            <div class="product-header">
                <div class="product-code">{{ $producto->codigo_ref ?? 'COD-PROD' }} {{ $producto->nombre }}</div>
                <div class="product-name">{{ $producto->presentacion_modelo ?? '' }} {{ $producto->concentracion_detalle ?? '' }}</div>
            </div>

            <!-- Stock Table -->
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th>Stock</th>
                        <th>Empaque</th>
                        <th>Unidades</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lotes as $index => $lote)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $lote->lote ?? 'S/N' }}</td>
                        <td>{{ $lote->fecha_formato }}</td>
                        <td class="{{ $lote->cantidad > 0 ? 'stock-disponible' : 'stock-cero' }}">
                            {{ $lote->cantidad }}
                        </td>
                        <td>{{ $lote->empaque }}</td>
                        <td>{{ $lote->unidades }}</td>
                        <td>
                            @if($lote->cantidad > 0)
                                <input type="number" class="cantidad-input" 
                                       min="0" max="{{ $lote->cantidad }}" 
                                       value="0" 
                                       data-lote-id="{{ $lote->id }}"
                                       data-precio="{{ $lote->pvp }}"
                                       onchange="actualizarCantidad(this)">
                            @else
                                <span class="stock-cero">0</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Price Section -->
            <div class="price-section">
                <div class="price-info">
                    <div class="price-label">Precio x Unidad</div>
                    <div class="price-value" id="precio-unitario">{{ number_format($lotes[0]->pvp ?? 0, 2) }}</div>
                </div>
            </div>

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-row">
                    <span>Stock:</span>
                    <span id="total-stock">0</span>
                </div>
                <div class="totals-row">
                    <span>Stock Seleccionado:</span>
                    <span id="stock-seleccionado" class="stock-seleccionado">0NIU</span>
                </div>
                <div class="totals-row total">
                    <span>TOTAL:</span>
                    <span id="total-importe">S/ 0.00</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions-section">
                <button class="btn-volver" onclick="window.history.back()">
                    ← Volver TPV
                </button>
                <button class="btn-agregar" onclick="agregarAlTicketYVolver()" style="background: #4CAF50; margin-left: 10px;">
                    ✓ Agregar al Ticket
                </button>
            </div>
        </div>
    </div>

    <script>
        // Calcular totales iniciales
        document.addEventListener('DOMContentLoaded', function() {
            calcularTotales();
        });

        function actualizarCantidad(input) {
            const cantidad = parseInt(input.value) || 0;
            const max = parseInt(input.getAttribute('max'));
            
            if (cantidad > max) {
                input.value = max;
                alert(`Stock máximo disponible: ${max} unidades`);
            }
            
            calcularTotales();
        }

        function calcularTotales() {
            let totalCantidad = 0;
            let totalImporte = 0;
            let stockTotal = 0;

            // Calcular stock total
            @foreach($lotes as $lote)
                stockTotal += {{ $lote->cantidad }};
            @endforeach

            // Calcular cantidades seleccionadas
            document.querySelectorAll('.cantidad-input').forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                const precio = parseFloat(input.dataset.precio);
                
                totalCantidad += cantidad;
                totalImporte += cantidad * precio;
            });

            // Actualizar display
            document.getElementById('total-stock').textContent = stockTotal + ' NIU';
            document.getElementById('stock-seleccionado').textContent = totalCantidad + 'NIU';
            document.getElementById('total-importe').textContent = 'S/ ' + totalImporte.toFixed(2);
        }

        function agregarAlTicketYVolver() {
            const lotesSeleccionados = [];
            const producto = {
                id: {{ $producto->id }},
                nombre: '{{ $producto->nombre }}',
                codigo: '{{ $producto->codigo_ref ?? "COD-PROD" }}',
                producto_linea_id: {{ $lotes[0]->producto_linea_id ?? 'null' }}
            };

            // Recopilar todas las cantidades seleccionadas
            document.querySelectorAll('.cantidad-input').forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                if (cantidad > 0) {
                    const loteId = input.dataset.loteId;
                    const precio = parseFloat(input.dataset.precio);
                    
                    // Buscar los datos del lote en el array original
                    @foreach($lotes as $index => $lote)
                        if (loteId === '{{ $lote->id }}') {
                            lotesSeleccionados.push({
                                lote_id: '{{ $lote->id }}',
                                lote: '{{ $lote->lote }}',
                                cantidad: cantidad,
                                precio: precio,
                                pvp: {{ $lote->pvp }},
                                pvc: {{ $lote->pvc }},
                                fecha_vencimiento: null,
                                importe: cantidad * precio,
                                producto_linea_id: {{ $lote->producto_linea_id ?? 'null' }}
                            });
                        }
                    @endforeach
                }
            });

            if (lotesSeleccionados.length === 0) {
                alert('Debe seleccionar al menos una cantidad mayor a 0 para agregar al ticket.');
                return;
            }

            // Guardar en sessionStorage para que el POS lo recoja
            const datosParaTicket = {
                producto: producto,
                lotes: lotesSeleccionados,
                timestamp: Date.now()
            };

            sessionStorage.setItem('lotesSeleccionados', JSON.stringify(datosParaTicket));

            // Mostrar confirmación y regresar
            alert(`Se agregarán ${lotesSeleccionados.length} lote(s) al ticket. Total: S/ ${lotesSeleccionados.reduce((sum, lote) => sum + lote.importe, 0).toFixed(2)}`);
            
            // Regresar al POS
            window.location.href = '{{ route("pos.index") }}';
        }
    </script>
@endsection
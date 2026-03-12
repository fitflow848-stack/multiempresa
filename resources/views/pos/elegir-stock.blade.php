@extends('layout.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
    <style>
        /* Sobrescribir el bloqueo de scroll de pos.css */
        html, body {
            overflow: auto !important;
            height: auto !important;
            min-height: 100vh;
        }

        .elegir-stock-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 15px;
            background: #f5f5f5;
            min-height: calc(100vh - 70px);
            padding-bottom: 80px; /* Espacio extra para asegurar visibilidad de botones */
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
            border-radius: 8px 8px 0 0;
        }

        .product-section {
            background: white;
            padding: 20px;
            border-bottom: 3px solid #3498db;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .stock-table th {
            background: #3498db;
            color: white;
            padding: 12px 10px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            position: sticky;
            top: 0;
        }

        .stock-table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .stock-table tbody tr:hover {
            background-color: #f8fbff;
        }

        .cantidad-input {
            width: 70px;
            text-align: center;
            border: 2px solid #3498db;
            padding: 6px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }

        .cantidad-input:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }

        .price-section {
            display: flex;
            gap: 20px;
            margin: 25px 0;
            justify-content: center;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .price-info {
            text-align: center;
        }

        .price-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .price-value {
            font-size: 22px;
            font-weight: 800;
            color: #2c3e50;
        }

        .totals-section {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 5px solid #3498db;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .totals-row.total {
            font-weight: 900;
            font-size: 18px;
            color: #2c3e50;
            border-top: 2px solid #bdc3c7;
            padding-top: 10px;
            margin-top: 15px;
        }

        .actions-section {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            padding-bottom: 20px;
        }

        .btn-volver {
            background: #95a5a6;
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-volver:hover {
            background: #7f8c8d;
            transform: translateY(-1px);
        }

        .btn-agregar {
            background: #27ae60;
            color: white;
            padding: 14px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 4px 0 #219150;
            transition: all 0.1s;
        }

        .btn-agregar:hover {
            background: #2ecc71;
            transform: translateY(-1px);
        }

        .btn-agregar:active {
            transform: translateY(3px);
            box-shadow: 0 1px 0 #219150;
        }

        .stock-seleccionado {
            color: #27ae60;
            font-weight: bold;
        }

        .stock-cero {
            color: #95a5a6;
            font-style: italic;
        }

        /* Mobile specific adjustments */
        @media (max-width: 600px) {
            .actions-section {
                flex-direction: column-reverse;
            }
            .btn-agregar, .btn-volver {
                width: 100%;
            }
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
                @php
                    $presentacion = trim($producto->presentacion_modelo ?? '');
                    $concentracion = trim($producto->concentracion_detalle ?? '');
                    $presentacion = $presentacion === '-- Ver --' || $presentacion === '' ? '' : $presentacion;
                    $concentracion = $concentracion === '-- Ver --' || $concentracion === '' ? '' : $concentracion;
                    $detalles = collect([$presentacion, $concentracion])
                        ->filter()
                        ->implode(' ');
                @endphp
                @if ($detalles)
                    <div class="product-name">{{ $detalles }}</div>
                @endif
                <div style="font-size: 13px; color: #3498db; margin-top: 5px;">Marca: {{ $producto->marca ?? '-' }}</div>
            </div>

            <!-- Stock Table -->
            <div class="table-container">
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
                        @foreach ($lotes as $index => $lote)
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
                                    @if ($lote->cantidad > 0)
                                        <input type="number" class="cantidad-input" min="0" max="{{ $lote->cantidad }}" value="0"
                                            data-lote-id="{{ $lote->id }}" data-precio="{{ $lote->pvp }}"
                                            onchange="actualizarCantidad(this)">
                                    @else
                                        <span class="stock-cero">0</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
                <button class="btn-agregar" onclick="agregarAlTicketYVolver()">
                    ✓ Agregar al Ticket
                </button>
            </div>
        </div>
    </div>

    <script>
        // Calcular totales iniciales
        document.addEventListener('DOMContentLoaded', function () {
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
            @foreach ($lotes as $lote)
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
                marca: '{{ $producto->marca ?? '' }}',
                codigo: '{{ $producto->codigo_ref ?? 'COD-PROD' }}',
                producto_linea_id: {{ $lotes[0]->producto_linea_id ?? 'null' }}
                };

            // Recopilar todas las cantidades seleccionadas
            document.querySelectorAll('.cantidad-input').forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                if (cantidad > 0) {
                    const loteId = input.dataset.loteId;
                    const precio = parseFloat(input.dataset.precio);

                    // Buscar los datos del lote en el array original
                    @foreach ($lotes as $index => $lote)
                        if (loteId === '{{ $lote->id }}') {
                            lotesSeleccionados.push({
                                lote_id: '{{ $lote->id }}',
                                lote: '{{ $lote->lote }}',
                                cantidad: cantidad,
                                precio: precio,
                                pvp: {{ $lote->pvp }},
                                pvc: {{ $lote->pvc }},
                                fecha_vencimiento: '{{ $lote->fecha_vencimiento }}',
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
            alert(
                `Se agregarán ${lotesSeleccionados.length} lote(s) al ticket. Total: S/ ${lotesSeleccionados.reduce((sum, lote) => sum + lote.importe, 0).toFixed(2)}`);

            // Regresar al POS
            window.location.href = '{{ route('pos.index') }}';
        }
    </script>
@endsection
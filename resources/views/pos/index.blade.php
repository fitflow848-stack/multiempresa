@extends('layout.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
    <style>
        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
        }

        .producto-card {
            border: 1px solid #ddd;
            padding: 10px;
            cursor: pointer;
            background: #fff;
            text-align: center;
        }

        .producto-card:hover {
            background: #f5f5f5;
        }

        .productos-table th,
        .productos-table td {
            border-bottom: 1px solid #eee;
            padding: 4px 8px;
            text-align: left;
        }

        .productos-table tr:hover {
            background: #E7F3FF;
        }

        .pos-footer-led {
            background: #000;
            color: #25ff07;
            font-family: 'Consolas', 'Courier New', monospace;
            padding: 8px 10px 2px 15px;
            display: flex;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 17px;
            position: relative;
        }

        .footer-totals-led {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 160px;
        }

        .footer-totals-led .label,
        .footer-dsctos .label {
            font-weight: bold;
        }

        .footer-dsctos {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 340px;
        }

        .footer-dsctos .productos-listados {
            color: #25ff07;
            font-size: 14px;
            margin-left: 7px;
        }

        .footer-cliente {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            font-size: 19px;
            font-weight: bold;
        }

        .footer-actions {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .footer-btn {
            background: #222;
            color: #25ff07;
            border: 1px solid #333;
            font-size: 18px;
            padding: 0 7px;
            border-radius: 3px;
            cursor: pointer;
        }

        .footer-btn:active {
            background: #444;
        }

        @media (max-width:800px) {
            .pos-footer-led {
                flex-direction: column;
                font-size: 14px;
            }

            .footer-cliente {
                justify-content: flex-start;
            }

            .footer-actions {
                flex-direction: row;
            }
        }
    </style>

    <div class="pos-container">
        <div class="left-sidebar">
            <div class="company-header">
                <select name="sucursal" id="sucursal-select">
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
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
                <!-- Reemplaza el grid por una tabla; ponlo donde quieras mostrar resultados -->
                <div id="productos-listado" style="width:100%">
                    <table class="productos-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f5f5f5;">
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>PVP</th>
                                <th>PVC</th>
                            </tr>
                        </thead>
                        <tbody id="productos-tbody">
                            <!-- Los resultados aparecen aquí -->
                        </tbody>
                    </table>
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
                        <button class="control-btn success" onclick="window.location='{{ route('pos.emitir') }}'">📤
                            Emitir</button>
                    </div>
                </div>

                <!-- Reemplaza el ticket-table por esta tabla, y pon un div para el fondo/marca de agua si lo deseas -->
                <div class="ticket-table" style="position:relative;">
                    <table class="productos-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f5f5f5;">
                                <th style="width: 30px;">#</th>
                                <th>Producto</th>
                                <th>Vence</th>
                                <th>Ctd.</th>
                                <th>Dscto</th>
                                <th>Impuesto</th>
                                <th>PVU</th>
                                <th>Importe</th>
                            </tr>
                        </thead>
                        <tbody id="ticket-tbody">
                            <!-- Los productos agregados aparecerán aquí -->
                        </tbody>
                    </table>
                    {{-- <div
                        style="position:absolute; left:10%; top:10%; opacity:0.07; font-size:80px; z-index:0; pointer-events:none;">
                        <img src="tu_logo.png" alt="Marca de agua" style="max-width:40vw;">
                    </div> --}}
                </div>
            </div>

            <!-- Footer -->
            <!-- Reemplaza tu div .pos-footer por esto -->
            <div class="pos-footer-led">
                <div class="footer-totals-led">
                    <div>
                        <span class="label">Gravada</span> : <span class="value">S/ <span
                                id="footer-gravada">0.00</span></span>
                    </div>
                    <div>
                        <span class="label">IGV</span> : <span class="value">S/ <span id="footer-igv">0.00</span></span>
                    </div>
                    <div>
                        <span class="label">ICBPER</span> : <span class="value">S/ <span
                                id="footer-icbper">0.00</span></span>
                    </div>
                </div>
                <div class="footer-dsctos">
                    <div>
                        <span class="label">Dscto Detalle</span> : <span class="value">S/ <span
                                id="footer-dscto">0.00</span></span>
                        <span class="productos-listados">en <span id="footer-productos-listados">0</span> productos
                            listados</span>
                    </div>
                    <div>
                        <span class="label">TOTAL</span> : <span class="value">S/ <span
                                id="footer-total">0.00</span></span>
                        <span class="productos-listados" style="opacity:0;">listados</span>
                    </div>
                </div>
                <div class="footer-cliente">
                    <span id="footer-cliente" style="font-weight:bold;">Cliente Contado</span>
                </div>
                <div class="footer-actions">
                    <button class="footer-btn">&#8644;</button>
                    <button class="footer-btn">&#8645;</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let ticket = [];

        document.querySelectorAll('.search-input')[1].addEventListener('keyup', function() {
            let q = this.value;

            if (q.length < 2) return;

            fetch(`{{ route('pos.buscar') }}?q=${q}`)
                .then(r => r.json())
                .then(renderProductos);
        });

        // Modifica la función renderProductos para llenar la tabla (no el grid):
        function renderProductos(productos) {
            const tbody = document.getElementById('productos-tbody');
            tbody.innerHTML = ''; // Limpia resultados previos

            productos.forEach(p => {
                const tr = document.createElement('tr');
                tr.style.cursor = 'pointer';
                tr.onclick = () => agregarAlTicket(p);
                tr.innerHTML = `
            <td>${p.nombre_producto || p.nombre}</td>
            <td>${p.cantidad ? (parseInt(p.cantidad) + "NIU") : ""}</td>
            <td>${p.pvp ? parseFloat(p.pvp).toFixed(2) : ""}</td>
            <td>${p.pvc ? parseFloat(p.pvc).toFixed(2) : ""}</td>
        `;
                tbody.appendChild(tr);
            });
        }

        function agregarAlTicket(producto) {
            let existente = ticket.find(p => p.id === producto.id);

            if (existente) {
                existente.cantidad++;
                existente.importe = existente.cantidad * existente.precio;
            } else {
                ticket.push({
                    id: producto.producto_id,
                    nombre: producto.nombre,
                    cantidad: 1,
                    precio: parseFloat(producto.pvp),
                    importe: producto.cantidad * producto.pvp
                });
            }

            renderTicket();
        }

        function renderTicket() {
            const tbody = document.getElementById('ticket-tbody');
            tbody.innerHTML = '';

            let total = 0;

            ticket.forEach((p, idx) => {
                total += p.importe;

                let fechaVencimiento = p.vencimiento ? p.vencimiento : '---';

                tbody.innerHTML += `
            <tr style="background:${idx % 2 === 0 ? '#f8fdff' : '#fff'};">
                <td>${idx + 1}</td>
                <td>${p.nombre}</td>
                <td>${fechaVencimiento}</td>
                <td>${p.cantidad} NIU</td>
                <td>0.00</td>
                <td>0.915</td>
                <td>${p.precio.toFixed(2)}</td>
                <td>${p.importe.toFixed(2)}</td>
            </tr>
        `;
            });

            actualizarFooter(total);
        }

        function actualizarFooter(total) {
            let gravada = total / 1.18;
            let igv = total - gravada;
            let icbper = 0.00; // Si tienes cálculo real ponlo aquí
            let dscto = 0.00; // Si tienes descuentos individuales, súmalos aquí
            let cantListado = ticket.length;

            document.getElementById('footer-gravada').innerText = gravada.toFixed(2);
            document.getElementById('footer-igv').innerText = igv.toFixed(2);
            document.getElementById('footer-icbper').innerText = icbper.toFixed(2);
            document.getElementById('footer-dscto').innerText = dscto.toFixed(2);
            document.getElementById('footer-total').innerText = total.toFixed(2);
            document.getElementById('footer-productos-listados').innerText = cantListado;
            // Si cambias tipo de cliente, actualiza así
            //document.getElementById('footer-cliente').innerText = tipoCliente;
        }
    </script>
@endsection

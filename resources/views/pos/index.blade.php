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
            padding: 6px 8px;
            text-align: left;
            font-size: 12px;
        }

        .productos-table th {
            background: #f5f5f5;
            font-weight: 600;
            color: #333;
            text-align: center;
        }

        .productos-table td:first-child {
            text-align: left;
        }

        .productos-table td:not(:first-child) {
            text-align: center;
        }

        .productos-table td:last-child,
        .productos-table td:nth-last-child(2) {
            text-align: right;
        }

        .productos-table tr:hover {
            background: #E7F3FF !important;
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
            transition: all 0.2s ease;
        }

        .footer-cliente:hover {
            background: #333;
            color: #00ff00;
            border-radius: 4px;
            padding: 4px 8px;
        }

        .tipo-documento-option:hover {
            background: #e3f2fd !important;
            border-color: #2196f3 !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
                    <div style="cursor: pointer;" onclick="navegarAClientes()"><i class="fa-solid fa-user-group"></i> Clientes</div>
                    <div><i class="fa-solid fa-book-open"></i> <a href="{{ route("comprobantes.index") }}">Comprobantes</a></div>
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
                                <th style="width: 60%;">Producto</th>
                                <th style="width: 15%;">Stock</th>
                                <th style="width: 12.5%;">PVP</th>
                                <th style="width: 12.5%;">PVC</th>
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
                        <button class="control-btn success" type="button" onclick="mostrarSeleccionTipoDocumento()">📤
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
                <div class="footer-cliente" onclick="mostrarBuscadorClientes()" style="cursor: pointer;">
                    <span id="footer-cliente" style="font-weight:bold;">Cliente Contado</span>
                </div>
                <div class="footer-actions">
                    <button class="footer-btn">&#8644;</button>
                    <button class="footer-btn">&#8645;</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Menú contextual para opciones de producto -->
    <div id="context-menu"
        style="position: absolute; background: white; border: 1px solid #ccc; box-shadow: 2px 2px 10px rgba(0,0,0,0.2); display: none; z-index: 1000; min-width: 200px; border-radius: 4px; font-family: Arial, sans-serif;">
        <div class="context-menu-item" onclick="venderLoteVencimiento()"
            style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
            <span style="color: #4CAF50;">🛒</span>
            <span>Vender a Lote Vencimiento</span>
            <span style="margin-left: auto;">▶</span>
        </div>
        <div class="context-menu-item" onclick="venderPrecioCorp()"
            style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
            <span style="color: #2196F3;">🛒</span>
            <span>Vender a Precio Corp</span>
        </div>
        <div class="context-menu-item" onclick="mostrarFichaTecnica()"
            style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
            <span style="color: #FF9800;">📋</span>
            <span>Ficha Técnica</span>
        </div>
        <div class="context-menu-item" onclick="mostrarFichaExistencias()"
            style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
            <span style="color: #9C27B0;">📊</span>
            <span>Ficha de Existencias</span>
        </div>
        <div class="context-menu-item" onclick="mostrarUbicacionProducto()"
            style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
            <span style="color: #607D8B;">📍</span>
            <span>Ubicación del Producto</span>
        </div>
        <div class="context-menu-item" onclick="verListado()"
            style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
            <span style="color: #795548;">📄</span>
            <span>Ver Listado</span>
            <span style="margin-left: auto;">▶</span>
        </div>
        <div class="context-menu-item" onclick="anotarNuevoProducto()"
            style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
            <span style="color: #4CAF50;">✏️</span>
            <span>Anotar Nuevo Producto</span>
        </div>
        <div class="context-menu-item" onclick="cancelarVenta()"
            style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
            <span style="color: #F44336;">❌</span>
            <span>Cancelar Venta</span>
        </div>
        <div class="context-menu-item" onclick="limpiarLista()"
            style="padding: 8px 12px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <span style="color: #FF5722;">🗑️</span>
            <span>Limpiar Lista</span>
        </div>
    </div>

    <!-- Modal de Búsqueda de Clientes -->
    <div id="modal-buscar-clientes" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: none; justify-content: center; align-items: center;">
        <div style="background: white; padding: 0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 700px; max-height: 80%; overflow: hidden; font-family: Arial, sans-serif;">
            <!-- Header -->
            <div style="background: #17a2b8; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 16px;">📋 Selección de Cliente</h3>
                <button onclick="cerrarBuscadorClientes()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">×</button>
            </div>
            
            <!-- Botones de acción -->
            <div style="padding: 15px 20px; border-bottom: 1px solid #dee2e6; display: flex; gap: 10px;">
                <button onclick="mostrarFormularioNuevoCliente()" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">✚ Nuevo cliente</button>
                <button onclick="mostrarFormularioDNI()" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">🔍 Cliente Contado</button>
                <button onclick="seleccionarClienteSeleccionado()" style="padding: 8px 15px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">👤 Usar seleccionado</button>
            </div>
            
            <!-- Campo de búsqueda -->
            <div style="padding: 15px 20px; border-bottom: 1px solid #dee2e6;">
                <input type="text" id="buscar-cliente-input" placeholder="Buscar cliente" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" onkeyup="buscarClientes()">
            </div>
            
            <!-- Lista de clientes -->
            <div style="max-height: 400px; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa; position: sticky; top: 0;">
                        <tr>
                            <th style="padding: 8px; text-align: left; font-size: 12px; border-bottom: 1px solid #dee2e6;">#</th>
                            <th style="padding: 8px; text-align: left; font-size: 12px; border-bottom: 1px solid #dee2e6;">DNI/RUC</th>
                            <th style="padding: 8px; text-align: left; font-size: 12px; border-bottom: 1px solid #dee2e6;">Cliente</th>
                            <th style="padding: 8px; text-align: right; font-size: 12px; border-bottom: 1px solid #dee2e6;">Debe</th>
                        </tr>
                    </thead>
                    <tbody id="lista-clientes-tbody">
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; color: #6c757d;">Cargando clientes...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Footer -->
            <div style="background: #17a2b8; color: white; padding: 10px 20px; text-align: center; font-size: 12px;">
                Volver TPV
            </div>
        </div>
    </div>

    <!-- Modal de Nuevo Cliente por DNI -->
    <div id="modal-cliente-dni" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2001; display: none; justify-content: center; align-items: center;">
        <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 500px; font-family: Arial, sans-serif;">
            <h3 style="margin: 0 0 20px 0; color: #007bff; text-align: center;">🔍 Buscar Cliente por DNI</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Número de DNI:</label>
                <input type="text" id="dni-input" placeholder="Ingrese DNI (8 dígitos)" maxlength="8" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" onkeypress="if(event.key==='Enter') buscarPorDNI()">
            </div>
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button onclick="buscarPorDNI()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">🔍 Buscar</button>
                <button onclick="cerrarModalClienteDNI()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Nuevo Cliente Manual -->
    <div id="modal-nuevo-cliente" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2001; display: none; justify-content: center; align-items: center;">
        <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 600px; max-height: 80%; overflow-y: auto; font-family: Arial, sans-serif;">
            <h3 style="margin: 0 0 20px 0; color: #28a745; text-align: center;">👤 Nuevo Cliente</h3>
            <div style="color: #6c757d; text-align: center; margin-bottom: 20px; font-size: 14px;">Datos administrativos del cliente</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="grid-column: 1 / -1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Tipo Cliente:</label>
                    <select id="tipo-cliente" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="Particular">Particular</option>
                        <option value="Empresa">Empresa</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Tipo Documento:</label>
                    <select id="tipo-documento" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="DNI">DNI</option>
                        <option value="RUC">RUC</option>
                        <option value="CE">Carnet de Extranjería</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Nro. Documento:</label>
                    <input type="text" id="nro-documento" placeholder="Número de documento" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                
                <div style="grid-column: 1 / -1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Nombre:</label>
                    <input type="text" id="nombre-cliente" placeholder="Nombre completo" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                
                <div style="grid-column: 1 / -1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Dirección:</label>
                    <input type="text" id="direccion-cliente" placeholder="Dirección completa" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Email:</label>
                    <input type="email" id="email-cliente" placeholder="correo@ejemplo.com" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Teléfono:</label>
                    <input type="text" id="telefono-cliente" placeholder="Número de teléfono" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 25px;">
                <button onclick="registrarNuevoCliente()" style="padding: 10px 25px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">💾 Registrar</button>
                <button onclick="cerrarModalNuevoCliente()" style="padding: 10px 25px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Selección de Tipo de Documento -->
    <div id="modal-tipo-documento" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2001; display: none; justify-content: center; align-items: center;">
        <div style="background: white; padding: 0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 400px; font-family: Arial, sans-serif;">
            <!-- Header -->
            <div style="background: #007bff; color: white; padding: 15px 20px; text-align: center; border-radius: 8px 8px 0 0;">
                <h3 style="margin: 0; font-size: 16px;">📋 Seleccionar Tipo de Documento</h3>
            </div>
            
            <!-- Lista de tipos de documento -->
            <div style="padding: 20px;">
                <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('ticket')" 
                     style="display: flex; align-items: center; padding: 12px 15px; margin: 8px 0; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                    <div style="background: #6f42c1; color: white; padding: 8px 12px; border-radius: 4px; margin-right: 12px; font-weight: bold; min-width: 60px; text-align: center;">📄</div>
                    <div>
                        <div style="font-weight: 600; color: #495057;">Ticket</div>
                        <div style="font-size: 12px; color: #6c757d;">Comprobante interno</div>
                    </div>
                </div>
                
                <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('boleta')" 
                     style="display: flex; align-items: center; padding: 12px 15px; margin: 8px 0; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                    <div style="background: #17a2b8; color: white; padding: 8px 12px; border-radius: 4px; margin-right: 12px; font-weight: bold; min-width: 60px; text-align: center;">🧾</div>
                    <div>
                        <div style="font-weight: 600; color: #495057;">Boleta</div>
                        <div style="font-size: 12px; color: #6c757d;">Para personas naturales</div>
                    </div>
                </div>
                
                <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('factura')" 
                     style="display: flex; align-items: center; padding: 12px 15px; margin: 8px 0; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                    <div style="background: #28a745; color: white; padding: 8px 12px; border-radius: 4px; margin-right: 12px; font-weight: bold; min-width: 60px; text-align: center;">📊</div>
                    <div>
                        <div style="font-weight: 600; color: #495057;">Factura</div>
                        <div style="font-size: 12px; color: #6c757d;">Para empresas con RUC</div>
                    </div>
                </div>
                
                <div class="tipo-documento-option" onclick="seleccionarTipoDocumento('nota-venta')" 
                     style="display: flex; align-items: center; padding: 12px 15px; margin: 8px 0; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                    <div style="background: #20c997; color: white; padding: 8px 12px; border-radius: 4px; margin-right: 12px; font-weight: bold; min-width: 60px; text-align: center;">📝</div>
                    <div>
                        <div style="font-weight: 600; color: #495057;">Nota Venta</div>
                        <div style="font-size: 12px; color: #6c757d;">Documento informativo</div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div style="background: #f8f9fa; padding: 10px 20px; text-align: center; border-radius: 0 0 8px 8px;">
                <button onclick="cerrarModalTipoDocumento()" style="padding: 8px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        let ticket = [];
        let currentProduct = null;

        // Verificar si hay lotes seleccionados pendientes de agregar al ticket
        document.addEventListener('DOMContentLoaded', function() {
            verificarLotesPendientes();
            verificarClienteSeleccionado();
        });

        function verificarClienteSeleccionado() {
            // Primero restaurar el ticket guardado si existe
            const ticketGuardado = sessionStorage.getItem('ticketGuardadoPOS');
            if (ticketGuardado) {
                try {
                    ticket = JSON.parse(ticketGuardado);
                    renderTicket();
                    mostrarNotificacion(`Se restauraron ${ticket.length} productos al ticket`);
                    
                    // Limpiar ticket guardado
                    sessionStorage.removeItem('ticketGuardadoPOS');
                } catch (error) {
                    console.error('Error al restaurar ticket:', error);
                    sessionStorage.removeItem('ticketGuardadoPOS');
                }
            }
            
            // Luego verificar si hay cliente seleccionado
            const clienteData = sessionStorage.getItem('clienteSeleccionadoPOS');
            if (clienteData) {
                try {
                    const cliente = JSON.parse(clienteData);
                    clienteActual = cliente;
                    document.getElementById('footer-cliente').innerText = cliente.nombre;
                    mostrarNotificacion(`Cliente seleccionado: ${cliente.nombre}`);
                    
                    // Limpiar sessionStorage después de usar
                    sessionStorage.removeItem('clienteSeleccionadoPOS');
                } catch (error) {
                    console.error('Error al cargar cliente seleccionado:', error);
                    sessionStorage.removeItem('clienteSeleccionadePOS');
                }
            } else {
                // Si no hay cliente seleccionado pero había uno guardado, restaurarlo
                const clienteGuardado = sessionStorage.getItem('clienteGuardadoPOS');
                if (clienteGuardado) {
                    try {
                        clienteActual = JSON.parse(clienteGuardado);
                        document.getElementById('footer-cliente').innerText = clienteActual.nombre;
                        sessionStorage.removeItem('clienteGuardadoPOS');
                    } catch (error) {
                        console.error('Error al restaurar cliente guardado:', error);
                        sessionStorage.removeItem('clienteGuardadoPOS');
                    }
                }
            }
        }

        function verificarLotesPendientes() {
            const lotesData = sessionStorage.getItem('lotesSeleccionados');
            if (lotesData) {
                try {
                    const datos = JSON.parse(lotesData);

                    // Verificar que los datos no sean muy antiguos (5 minutos máximo)
                    if (Date.now() - datos.timestamp < 300000) {
                        // Agregar cada lote al ticket
                        datos.lotes.forEach(lote => {
                            const productoParaTicket = {
                                id: `lote_${lote.lote_id}`,
                                producto_id: datos.producto.id,
                                almacen_detalle_id: lote.lote_id, // ID del detalle del almacén para actualizar stock
                                nombre: datos.producto.nombre,
                                lote: lote.lote,
                                cantidad: lote.cantidad,
                                cantidad_disponible: lote.cantidad + 100, // Disponible mayor para permitir edición
                                precio: lote.precio,
                                importe: lote.importe,
                                pvp: lote.pvp,
                                pvc: lote.pvc,
                                fecha_vencimiento: lote.fecha_vencimiento,
                                es_lote_especifico: true
                            };
                            ticket.push(productoParaTicket);
                        });

                        // Actualizar la vista del ticket
                        renderTicket();

                        // Mostrar notificación
                        mostrarNotificacion(`Se agregaron ${datos.lotes.length} lote(s) al ticket`);
                    }

                    // Limpiar los datos de sessionStorage
                    sessionStorage.removeItem('lotesSeleccionados');
                } catch (error) {
                    console.error('Error procesando lotes pendientes:', error);
                    sessionStorage.removeItem('lotesSeleccionados');
                }
            }
        }

        function mostrarNotificacion(mensaje) {
            // Crear notificación temporal
            const notificacion = document.createElement('div');
            notificacion.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                z-index: 1000;
                font-size: 14px;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            notificacion.textContent = mensaje;
            document.body.appendChild(notificacion);

            // Mostrar con animación
            setTimeout(() => notificacion.style.opacity = '1', 100);

            // Ocultar después de 3 segundos
            setTimeout(() => {
                notificacion.style.opacity = '0';
                setTimeout(() => document.body.removeChild(notificacion), 300);
            }, 3000);
        }

        document.querySelectorAll('.search-input')[0].addEventListener('keyup', function() {
            let q = this.value;

            if (q.length < 2) return;

            fetch(`{{ route('pos.buscar') }}?q=${q}`)
                .then(r => r.json())
                .then(renderProductos);
        });

        document.querySelectorAll('.search-input')[1].addEventListener('keyup', function() {
            let q = this.value;

            if (q.length < 2) return;

            fetch(`{{ route('pos.buscar') }}?q=${q}`)
                .then(r => r.json())
                .then(renderProductos);
        });

        // Función para navegar a clientes marcando que viene desde POS
        function navegarAClientes() {
            // Guardar el ticket actual antes de navegar
            if (ticket.length > 0) {
                sessionStorage.setItem('ticketGuardadoPOS', JSON.stringify(ticket));
                sessionStorage.setItem('clienteGuardadoPOS', JSON.stringify(clienteActual));
            }
            sessionStorage.setItem('navegandoDesdePOS', 'true');
            window.location.href = '{{ route("clientes.index") }}';
        }

        // Función para mostrar la modal de selección de tipo de documento
        function mostrarSeleccionTipoDocumento() {
            if (ticket.length === 0) {
                alert('No hay productos en el ticket para emitir');
                return;
            }
            
            // Validar que haya un cliente seleccionado (diferente del cliente por defecto)
            if (!clienteActual || !clienteActual.id || clienteActual.nombre === 'Cliente Contado') {
                alert('Debe seleccionar un cliente antes de emitir el comprobante. Haga clic en el área del cliente para seleccionar uno.');
                mostrarBuscadorClientes();
                return;
            }
            
            document.getElementById('modal-tipo-documento').style.display = 'flex';
        }

        // Función para cerrar la modal de tipo de documento
        function cerrarModalTipoDocumento() {
            document.getElementById('modal-tipo-documento').style.display = 'none';
        }

        // Función para seleccionar el tipo de documento y proceder a emitir
        function seleccionarTipoDocumento(tipo) {
            cerrarModalTipoDocumento();
            emitirVentaConTipo(tipo);
        }

        // Función para emitir venta con el tipo de documento seleccionado
        function emitirVentaConTipo(tipoDocumento) {
            // Doble validación antes de proceder
            if (ticket.length === 0) {
                alert('No hay productos en el ticket para emitir');
                return;
            }
            
            if (!clienteActual || !clienteActual.id || clienteActual.nombre === 'Cliente Contado') {
                alert('Error: No se ha seleccionado un cliente válido');
                mostrarBuscadorClientes();
                return;
            }
            
            // Calcular totales - Los precios ya incluyen IGV
            let total_con_igv = 0;
            let subtotal = 0;
            let igv = 0;
            let total = 0;
            
            ticket.forEach(item => {
                const itemTotal = parseFloat(item.precio) * parseInt(item.cantidad);
                total_con_igv += itemTotal;
            });
            
            // Separar IGV del total (precio ya incluye IGV del 18%)
            subtotal = Math.round((total_con_igv / 1.18) * 100) / 100;  // Base sin IGV
            igv = Math.round((total_con_igv - subtotal) * 100) / 100;   // IGV calculado
            total = total_con_igv;  // Total es el precio con IGV incluido
            
            // Preparar datos para enviar
            const datosVenta = {
                ticket: JSON.stringify(ticket),
                cliente: JSON.stringify(clienteActual),
                subtotal: subtotal.toFixed(2),
                igv: igv.toFixed(2),
                total: total.toFixed(2),
                tipo_documento: tipoDocumento,
                _token: '{{ csrf_token() }}'
            };
            
            // Crear formulario y enviarlo por POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("pos.emitir.post") }}';
            form.style.display = 'none';
            
            // Agregar datos como campos ocultos
            Object.keys(datosVenta).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = datosVenta[key];
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }

        // Función para mostrar el menú contextual
        function mostrarMenuLotes(event, producto) {
            event.preventDefault();
            event.stopPropagation();

            currentProduct = producto;
            const contextMenu = document.getElementById('context-menu');

            // Posicionar el menú (ajustar si se sale de la ventana)
            let x = event.pageX;
            let y = event.pageY;

            // Ajustar posición para que no se salga de la ventana
            if (x + 250 > window.innerWidth) {
                x = window.innerWidth - 260;
            }
            if (y + 300 > window.innerHeight) {
                y = window.innerHeight - 310;
            }

            contextMenu.style.left = x + 'px';
            contextMenu.style.top = y + 'px';
            contextMenu.style.display = 'block';
        }

        // Funciones para las opciones del menú
        function venderLoteVencimiento() {
            if (!currentProduct) return;

            // Redirigir a la pantalla de elegir stock
            window.location.href = `{{ route('pos.elegir-stock') }}?producto_id=${currentProduct.producto_id}`;
            cerrarContextMenu();
        }

        function venderPrecioCorp() {
            if (!currentProduct) return;

            const precioCorp = parseFloat(currentProduct.pvc || 0);
            if (precioCorp <= 0) {
                alert('Este producto no tiene precio corporativo definido');
                cerrarContextMenu();
                return;
            }

            // Crear modal para precio corporativo
            const modalHtml = `
                <div id="modal-precio-corp" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; justify-content: center; align-items: center;" onclick="cerrarModalPrecioCorp()">
                    <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); min-width: 350px; font-family: Arial, sans-serif;" onclick="event.stopPropagation()">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div style="font-weight: 600; font-size: 14px; color: #333; margin-bottom: 10px;">
                                ¿Cuántas unidades a precio corporativo ( PvCU: ${precioCorp.toFixed(2)} )?
                            </div>
                        </div>
                        <div style="text-align: center; margin-bottom: 20px;">
                            <input type="number" id="cantidad-precio-corp" value="1" min="1" max="999" 
                                   style="width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-size: 16px;"
                                   onkeypress="if(event.key==='Enter') aceptarPrecioCorp()">
                        </div>
                        <div style="display: flex; justify-content: center; gap: 10px;">
                            <button onclick="aceptarPrecioCorp()" style="padding: 8px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Aceptar</button>
                            <button onclick="cerrarModalPrecioCorp()" style="padding: 8px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Cancelar</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Enfocar el input y seleccionar el texto
            setTimeout(() => {
                const input = document.getElementById('cantidad-precio-corp');
                input.focus();
                input.select();
            }, 100);
            
            cerrarContextMenu();
        }

        function aceptarPrecioCorp() {
            const cantidadInput = document.getElementById('cantidad-precio-corp');
            const cantidad = parseInt(cantidadInput.value);
            
            if (!cantidad || cantidad <= 0) {
                alert('Ingrese una cantidad válida');
                return;
            }

            if (!currentProduct) {
                cerrarModalPrecioCorp();
                return;
            }

            // Crear producto para el ticket con precio corporativo
            const productoConPrecioCorp = {
                id: `corp_${currentProduct.producto_id}_${Date.now()}`,
                producto_id: currentProduct.producto_id,
                nombre: currentProduct.nombre + ' (Precio Corp.)',
                cantidad: cantidad,
                cantidad_disponible: currentProduct.cantidad_total || 999,
                precio: parseFloat(currentProduct.pvc),
                importe: cantidad * parseFloat(currentProduct.pvc),
                pvp: currentProduct.pvp,
                pvc: currentProduct.pvc,
                es_precio_corporativo: true
            };

            ticket.push(productoConPrecioCorp);
            renderTicket();
            
            mostrarNotificacion(`Se agregó ${cantidad} unidad(es) a precio corporativo: S/ ${parseFloat(currentProduct.pvc).toFixed(2)}`);
            cerrarModalPrecioCorp();
        }

        function cerrarModalPrecioCorp() {
            const modal = document.getElementById('modal-precio-corp');
            if (modal) {
                modal.remove();
            }
        }

        function mostrarFichaTecnica() {
            if (!currentProduct) return;

            // Crear modal de ficha técnica
            const modalHtml = `
                <div id="modal-ficha-tecnica" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; justify-content: center; align-items: center;" onclick="cerrarModalFichaTecnica()">
                    <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 600px; max-height: 80%; overflow-y: auto; font-family: Arial, sans-serif;" onclick="event.stopPropagation()">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
                            <h3 style="margin: 0; color: #007bff; font-size: 18px;">📋 Ficha Técnica del Producto</h3>
                            <button onclick="cerrarModalFichaTecnica()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #666;">×</button>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div style="grid-column: 1 / -1;">
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #007bff;">
                                    <h4 style="margin: 0 0 10px 0; color: #007bff; font-size: 16px;">${currentProduct.nombre || 'N/A'}</h4>
                                    <p style="margin: 0; color: #6c757d; font-size: 14px;">${currentProduct.detalle || 'Sin descripción disponible'}</p>
                                </div>
                            </div>
                            
                            <div>
                                <label style="font-weight: 600; color: #495057; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; display: block;">Código del Producto</label>
                                <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; font-family: monospace; font-size: 14px;">
                                    ${currentProduct.producto_id || 'N/A'}
                                </div>
                            </div>
                            
                            <div>
                                <label style="font-weight: 600; color: #495057; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; display: block;">Marca</label>
                                <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; font-size: 14px;">
                                    ${currentProduct.marca || 'Sin marca'}
                                </div>
                            </div>
                            
                            <div>
                                <label style="font-weight: 600; color: #495057; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; display: block;">Familia</label>
                                <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; font-size: 14px;">
                                    ${currentProduct.familia || 'Sin clasificar'}
                                </div>
                            </div>
                            
                            <div>
                                <label style="font-weight: 600; color: #495057; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; display: block;">Unidad de Medida</label>
                                <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; font-size: 14px;">
                                    NIU (Unidades)
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin: 20px 0; border-top: 1px solid #dee2e6; padding-top: 15px;">
                            <h4 style="margin: 0 0 15px 0; color: #28a745; font-size: 16px;">💰 Información de Precios</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                                <div style="text-align: center; background: #e8f5e8; padding: 12px; border-radius: 6px;">
                                    <div style="font-size: 12px; color: #28a745; font-weight: 600; margin-bottom: 5px;">PRECIO PÚBLICO</div>
                                    <div style="font-size: 16px; font-weight: bold; color: #28a745;">S/ ${currentProduct.pvp ? parseFloat(currentProduct.pvp).toFixed(2) : '0.00'}</div>
                                </div>
                                <div style="text-align: center; background: #e3f2fd; padding: 12px; border-radius: 6px;">
                                    <div style="font-size: 12px; color: #1976d2; font-weight: 600; margin-bottom: 5px;">PRECIO CORPORATIVO</div>
                                    <div style="font-size: 16px; font-weight: bold; color: #1976d2;">S/ ${currentProduct.pvc ? parseFloat(currentProduct.pvc).toFixed(2) : '0.00'}</div>
                                </div>
                                <div style="text-align: center; background: #fff3e0; padding: 12px; border-radius: 6px;">
                                    <div style="font-size: 12px; color: #f57c00; font-weight: 600; margin-bottom: 5px;">DESCUENTO</div>
                                    <div style="font-size: 16px; font-weight: bold; color: #f57c00;">${currentProduct.pvp && currentProduct.pvc ? ((parseFloat(currentProduct.pvp) - parseFloat(currentProduct.pvc)) / parseFloat(currentProduct.pvp) * 100).toFixed(1) + '%' : '0%'}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin: 20px 0; border-top: 1px solid #dee2e6; padding-top: 15px;">
                            <h4 style="margin: 0 0 15px 0; color: #17a2b8; font-size: 16px;">📦 Información de Stock</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div>
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; display: block;">Stock Total</label>
                                    <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; font-size: 14px; font-weight: bold; color: ${(currentProduct.cantidad_total || 0) > 0 ? '#28a745' : '#dc3545'};">
                                        ${currentProduct.cantidad_total || 0} unidades
                                    </div>
                                </div>
                                <div>
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; display: block;">Lotes Disponibles</label>
                                    <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; font-size: 14px;">
                                        ${currentProduct.total_lotes || 1} lote(s)
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin: 20px 0; border-top: 1px solid #dee2e6; padding-top: 15px;">
                            <h4 style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">📍 Estado del Producto</h4>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <span style="background: ${(currentProduct.cantidad_total || 0) > 0 ? '#d4edda' : '#f8d7da'}; color: ${(currentProduct.cantidad_total || 0) > 0 ? '#155724' : '#721c24'}; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    ${(currentProduct.cantidad_total || 0) > 0 ? '✅ DISPONIBLE' : '❌ SIN STOCK'}
                                </span>
                                ${currentProduct.total_lotes > 1 ? '<span style="background: #cce5ff; color: #004085; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">🔄 MÚLTIPLES LOTES</span>' : ''}
                                ${currentProduct.pvc ? '<span style="background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">💼 PRECIO CORPORATIVO</span>' : ''}
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                            <button onclick="cerrarModalFichaTecnica()" style="padding: 10px 25px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">Cerrar Ficha Técnica</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            cerrarContextMenu();
        }

        function cerrarModalFichaTecnica() {
            const modal = document.getElementById('modal-ficha-tecnica');
            if (modal) {
                modal.remove();
            }
        }

        // Variables globales para clientes
        let clienteActual = {
            id: null,
            tipo_documento: 'DNI',
            numero_documento: '',
            nombre: 'Cliente Contado',
            direccion: '',
            email: '',
            telefono: ''
        };
        let clientesDisponibles = [];
        let clienteSeleccionado = null;

        // Funciones para manejo de clientes
        function mostrarBuscadorClientes() {
            const modal = document.getElementById('modal-buscar-clientes');
            modal.style.display = 'flex';
            cargarListaClientes();
        }

        function cerrarBuscadorClientes() {
            const modal = document.getElementById('modal-buscar-clientes');
            modal.style.display = 'none';
            clienteSeleccionado = null;
        }

        function cargarListaClientes() {
            const tbody = document.getElementById('lista-clientes-tbody');
            tbody.innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #6c757d;">Cargando clientes...</td></tr>';
            
            fetch(`{{ route('clientes.buscar-pos') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            })
                .then(response => response.json())
                .then(data => {
                    clientesDisponibles = data;
                    renderizarListaClientes(clientesDisponibles);
                })
                .catch(error => {
                    tbody.innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #dc3545;">Error al cargar clientes</td></tr>';
                    console.error('Error:', error);
                });
        }

        function renderizarListaClientes(clientes) {
            const tbody = document.getElementById('lista-clientes-tbody');
            tbody.innerHTML = '';
            
            if (clientes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #6c757d;">No se encontraron clientes</td></tr>';
                return;
            }
            
            clientes.forEach((cliente, index) => {
                const tr = document.createElement('tr');
                tr.style.cursor = 'pointer';
                tr.onclick = () => seleccionarCliente(cliente);
                tr.onmouseover = () => tr.style.backgroundColor = '#f8f9fa';
                tr.onmouseout = () => tr.style.backgroundColor = clienteSeleccionado && clienteSeleccionado.id === cliente.id ? '#e3f2fd' : 'white';
                
                if (clienteSeleccionado && clienteSeleccionado.id === cliente.id) {
                    tr.style.backgroundColor = '#e3f2fd';
                }
                
                // Icono según tipo de documento
                const icono = cliente.tipo_documento === 'RUC' ? '🏢' : 
                             cliente.tipo_documento === 'CE' ? '🌍' : '👤';
                
                tr.innerHTML = `
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">${icono}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; font-family: monospace;">${cliente.numero_documento}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">${cliente.nombre}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right; color: ${cliente.debe > 0 ? '#dc3545' : '#28a745'};">S/ ${cliente.debe.toFixed(2)}</td>
                `;
                
                tbody.appendChild(tr);
            });
        }

        function seleccionarCliente(cliente) {
            clienteSeleccionado = cliente;
            renderizarListaClientes(clientesDisponibles);
        }

        function seleccionarClienteSeleccionado() {
            if (!clienteSeleccionado) {
                alert('Seleccione un cliente de la lista');
                return;
            }
            
            clienteActual = clienteSeleccionado;
            document.getElementById('footer-cliente').innerText = clienteActual.nombre;
            mostrarNotificacion(`Cliente seleccionado: ${clienteActual.nombre}`);
            cerrarBuscadorClientes();
        }

        function buscarClientes() {
            const termino = document.getElementById('buscar-cliente-input').value.toLowerCase();
            
            if (termino.length === 0) {
                renderizarListaClientes(clientesDisponibles);
                return;
            }
            
            const clientesFiltrados = clientesDisponibles.filter(cliente => 
                cliente.nombre.toLowerCase().includes(termino) ||
                cliente.numero_documento.includes(termino)
            );
            
            renderizarListaClientes(clientesFiltrados);
        }

        function mostrarFormularioDNI() {
            document.getElementById('modal-cliente-dni').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('dni-input').focus();
            }, 100);
        }

        function cerrarModalClienteDNI() {
            document.getElementById('modal-cliente-dni').style.display = 'none';
            document.getElementById('dni-input').value = '';
        }

        function buscarPorDNI() {
            const dni = document.getElementById('dni-input').value.trim();
            
            if (dni.length !== 8) {
                alert('El DNI debe tener 8 dígitos');
                return;
            }
            
            if (!/^\d{8}$/.test(dni)) {
                alert('El DNI debe contener solo números');
                return;
            }
            
            // Consultar RENIEC
            mostrarNotificacion('Consultando RENIEC...');
            
            fetch(`{{ route('pos.consultar-reniec') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ dni: dni })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const nuevoCliente = {
                        id: Date.now(),
                        tipo_documento: 'DNI',
                        numero_documento: dni,
                        nombre: data.data.nombre_completo,
                        direccion: '',
                        email: '',
                        telefono: '',
                        debe: 0
                    };
                    
                    clienteActual = nuevoCliente;
                    document.getElementById('footer-cliente').innerText = clienteActual.nombre;
                    mostrarNotificacion(`Cliente creado desde RENIEC: ${clienteActual.nombre}`);
                    cerrarModalClienteDNI();
                    cerrarBuscadorClientes();
                } else {
                    alert('Error al consultar RENIEC: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                alert('Error de conexión al consultar RENIEC');
                console.error('Error:', error);
            });
        }

        function mostrarFormularioNuevoCliente() {
            document.getElementById('modal-nuevo-cliente').style.display = 'flex';
        }

        function cerrarModalNuevoCliente() {
            document.getElementById('modal-nuevo-cliente').style.display = 'none';
            limpiarFormularioCliente();
        }

        function limpiarFormularioCliente() {
            document.getElementById('tipo-cliente').value = 'Particular';
            document.getElementById('tipo-documento').value = 'DNI';
            document.getElementById('nro-documento').value = '';
            document.getElementById('nombre-cliente').value = '';
            document.getElementById('direccion-cliente').value = '';
            document.getElementById('email-cliente').value = '';
            document.getElementById('telefono-cliente').value = '';
        }

        function registrarNuevoCliente() {
            const tipoDocumento = document.getElementById('tipo-documento').value;
            const nroDocumento = document.getElementById('nro-documento').value.trim();
            const nombre = document.getElementById('nombre-cliente').value.trim();
            
            if (!nroDocumento) {
                alert('El número de documento es requerido');
                return;
            }
            
            if (!nombre) {
                alert('El nombre es requerido');
                return;
            }
            
            const datosCliente = {
                tipo_documento: tipoDocumento,
                numero_documento: nroDocumento,
                nombre: nombre.toUpperCase(),
                direccion: document.getElementById('direccion-cliente').value.trim(),
                email: document.getElementById('email-cliente').value.trim(),
                telefono: document.getElementById('telefono-cliente').value.trim()
            };
            
            mostrarNotificacion('Registrando cliente...');
            
            fetch(`{{ route('pos.crear-cliente') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(datosCliente)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    clienteActual = data.data;
                    document.getElementById('footer-cliente').innerText = clienteActual.nombre;
                    mostrarNotificacion(`Cliente registrado: ${clienteActual.nombre}`);
                    cerrarModalNuevoCliente();
                    cerrarBuscadorClientes();
                } else {
                    alert('Error al registrar cliente');
                }
            })
            .catch(error => {
                alert('Error de conexión al registrar cliente');
                console.error('Error:', error);
            });
        }

        function mostrarFichaExistencias() {
            if (!currentProduct) return;

            alert('Función de Ficha de Existencias - En desarrollo');
            cerrarContextMenu();
        }

        function mostrarUbicacionProducto() {
            if (!currentProduct) return;

            alert('Función de Ubicación del Producto - En desarrollo');
            cerrarContextMenu();
        }

        function verListado() {
            if (!currentProduct) return;

            // Mostrar los lotes en una tabla simple
            mostrarListadoLotes();
        }

        function anotarNuevoProducto() {
            alert('Función de Anotar Nuevo Producto - En desarrollo');
            cerrarContextMenu();
        }

        function cancelarVenta() {
            if (confirm('¿Está seguro que desea cancelar la venta?')) {
                ticket = [];
                renderTicket();
            }
            cerrarContextMenu();
        }

        function limpiarLista() {
            if (confirm('¿Está seguro que desea limpiar la lista de productos?')) {
                document.getElementById('productos-tbody').innerHTML = '';
            }
            cerrarContextMenu();
        }

        // Función para mostrar listado de lotes en una ventana modal simple
        function mostrarListadoLotes() {
            if (!currentProduct) return;

            fetch(`{{ route('pos.lotes') }}?producto_id=${currentProduct.producto_id}`)
                .then(r => r.json())
                .then(lotes => {
                    let html = `
                        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; justify-content: center; align-items: center;" onclick="cerrarModal(this)">
                            <div style="background: white; padding: 20px; border-radius: 8px; max-width: 600px; max-height: 80%; overflow-y: auto;" onclick="event.stopPropagation()">
                                <h3 style="margin-top: 0;">Listado de Lotes - ${currentProduct.nombre}</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #f5f5f5;">
                                            <th style="border: 1px solid #ddd; padding: 8px;">Lote</th>
                                            <th style="border: 1px solid #ddd; padding: 8px;">Vencimiento</th>
                                            <th style="border: 1px solid #ddd; padding: 8px;">Stock</th>
                                            <th style="border: 1px solid #ddd; padding: 8px;">PVP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    lotes.forEach(lote => {
                        html += `
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;">${lote.lote || 'S/N'}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">${lote.fecha_vencimiento ? new Date(lote.fecha_vencimiento).toLocaleDateString('es-PE') : 'Sin fecha'}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">${lote.cantidad}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">S/ ${parseFloat(lote.pvp).toFixed(2)}</td>
                            </tr>
                        `;
                    });

                    html += `
                                    </tbody>
                                </table>
                                <div style="text-align: center; margin-top: 20px;">
                                    <button onclick="cerrarModal(this.closest('.modal'))" style="padding: 8px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    `;

                    document.body.insertAdjacentHTML('beforeend', html);
                })
                .catch(error => {
                    alert('Error al cargar el listado de lotes');
                });

            cerrarContextMenu();
        }

        function cerrarModal(modal) {
            if (modal) {
                modal.remove();
            }
        }

        // Función para agregar todos los lotes como un producto
        function agregarTodosLosLotes(producto) {
            const productoCompleto = {
                id: `prod_${producto.producto_id}`,
                producto_id: producto.producto_id,
                nombre: producto.nombre,
                cantidad_disponible: producto.cantidad_total,
                precio: parseFloat(producto.pvp),
                pvp: parseFloat(producto.pvp),
                pvc: parseFloat(producto.pvc),
                es_lote_especifico: false
            };

            agregarAlTicket(productoCompleto);
        }

        // Función para cerrar el menú contextual
        function cerrarContextMenu() {
            document.getElementById('context-menu').style.display = 'none';
        }

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(event) {
            const contextMenu = document.getElementById('context-menu');
            if (contextMenu.style.display === 'block' && !contextMenu.contains(event.target)) {
                cerrarContextMenu();
            }
        });

        // Cerrar menú con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarContextMenu();
            }
        });

        // Agregar estilos CSS para el menú contextual
        const style = document.createElement('style');
        style.textContent = `
            .context-menu-item {
                position: relative;
                transition: all 0.2s ease;
                font-size: 13px;
            }
            .context-menu-item:hover {
                background-color: #e3f2fd !important;
                font-weight: 500;
            }
            .context-menu-item:active {
                background-color: #bbdefb !important;
            }
            .productos-table tr:hover {
                background: #E7F3FF !important;
                cursor: pointer;
            }
            .productos-table tr:hover td {
                font-weight: 500;
            }
            #context-menu {
                animation: fadeIn 0.2s ease;
                border: 2px solid #2196F3;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: scale(0.9); }
                to { opacity: 1; transform: scale(1); }
            }
        `;
        document.head.appendChild(style);

        // Modifica la función renderProductos para llenar la tabla (no el grid):
        function renderProductos(productos) {
            const tbody = document.getElementById('productos-tbody');
            tbody.innerHTML = ''; // Limpia resultados previos

            productos.forEach(p => {
                const tr = document.createElement('tr');
                tr.style.cursor = 'pointer';
                tr.onclick = () => mostrarMenuLotes(event, p);

                // Agregar evento de clic derecho
                tr.oncontextmenu = (event) => {
                    event.preventDefault();
                    mostrarMenuLotes(event, p);
                };

                // Formatear nombre + detalle pegado al lado derecho
                let nombreCompleto = p.nombre || '';
                if (p.detalle && p.detalle.trim()) {
                    nombreCompleto += ' ' + p.detalle;
                }

                // Indicadores visuales para múltiples lotes
                const indicadorLotes = p.total_lotes > 1 ?
                    ` <span style="background: #2196f3; color: white; padding: 1px 4px; border-radius: 8px; font-size: 10px; margin-left: 5px;">${p.total_lotes} lotes</span>` :
                    '';

                const stockDisplay = p.cantidad_total ? `${parseInt(p.cantidad_total)} NIU${indicadorLotes}` : '';

                tr.innerHTML = `
                        <td style="position: relative; padding: 6px 8px;">
                            <div style="font-weight: 500; color: #333;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>${nombreCompleto}</div>
                                    <div>${p.detalle}</div>
                                </div>
                                ${p.total_lotes > 1 ? '<span style="color: #2196f3; font-size: 12px; margin-left: 5px;">🔄</span>' : ''}
                            </div>
                        </td>
                        <td style="padding: 6px 8px; text-align: center; font-weight: 500;">${stockDisplay}</td>
                        <td style="padding: 6px 8px; text-align: right; color: #2e7d32; font-weight: 500;">${p.pvp ? 'S/ ' + parseFloat(p.pvp).toFixed(2) : ""}</td>
                        <td style="padding: 6px 8px; text-align: right; color: #1976d2; font-weight: 500;">${p.pvc ? 'S/ ' + parseFloat(p.pvc).toFixed(2) : ""}</td>
                `;

                tbody.appendChild(tr);
            });
        }

        function agregarProductoAlTicket(producto) {
            // Verificar si ya existe en el ticket
            const existente = ticket.find(p => {
                if (producto.es_lote_especifico) {
                    // Para lotes específicos, comparar por ID único
                    return p.id === producto.id;
                } else {
                    // Para productos agrupados, buscar por producto_id
                    return p.producto_id === producto.producto_id && !p.es_lote_especifico;
                }
            });

            if (existente) {
                // Verificar disponibilidad antes de incrementar
                if (existente.cantidad < existente.cantidad_disponible) {
                    existente.cantidad++;
                    existente.importe = existente.cantidad * existente.precio;
                } else {
                    alert(`Stock insuficiente. Disponible: ${existente.cantidad_disponible} unidades`);
                }
            } else {
                // Agregar nuevo producto al ticket
                const nuevoProducto = {
                    id: producto.id,
                    producto_id: producto.producto_id,
                    nombre: producto.nombre,
                    cantidad: 1,
                    cantidad_disponible: producto.cantidad_disponible || producto.cantidad_total || 999,
                    precio: producto.precio || parseFloat(producto.pvp),
                    importe: producto.precio || parseFloat(producto.pvp),
                    pvp: producto.pvp,
                    pvc: producto.pvc,
                    lote: producto.lote || null,
                    fecha_vencimiento: producto.fecha_vencimiento || null,
                    es_lote_especifico: producto.es_lote_especifico || false
                };

                ticket.push(nuevoProducto);
            }

            renderTicket();
        }

        function renderTicket() {
            const tbody = document.getElementById('ticket-tbody');
            tbody.innerHTML = '';

            let total = 0;

            ticket.forEach((p, idx) => {
                total += p.importe;

                // Formatear información de lote y vencimiento
                let infoLote = '';
                if (p.es_lote_especifico && p.lote) {
                    infoLote = ` (Lt: ${p.lote})`;
                }

                let fechaVencimiento = '---';
                if (p.fecha_vencimiento) {
                    const fecha = new Date(p.fecha_vencimiento);
                    fechaVencimiento = fecha.toLocaleDateString('es-PE');
                } else if (p.es_lote_especifico) {
                    fechaVencimiento = 'S/F';
                }

                // Color de fondo diferente para lotes específicos y precio corporativo
                let bgColor;
                if (p.es_precio_corporativo) {
                    bgColor = idx % 2 === 0 ? '#e8f5e8' : '#d4edda'; // Verde claro para precio corporativo
                } else if (p.es_lote_especifico) {
                    bgColor = idx % 2 === 0 ? '#fff3e0' : '#ffe0b2'; // Naranja para lotes específicos
                } else {
                    bgColor = idx % 2 === 0 ? '#f8fdff' : '#fff'; // Azul claro para productos normales
                }

                tbody.innerHTML += `
            <tr style="background:${bgColor};" onclick="editarLinea(${idx})">
                <td>${idx + 1}</td>
                <td title="${p.nombre}${infoLote}">
                    ${p.nombre.length > 25 ? p.nombre.substring(0, 25) + '...' : p.nombre}${infoLote}
                </td>
                <td>${fechaVencimiento}</td>
                <td>
                    <input type="number" value="${p.cantidad}" min="1" max="${p.cantidad_disponible}" 
                           style="width: 50px; border: none; background: transparent; text-align: center;"
                           onchange="actualizarCantidad(${idx}, this.value)"
                           onclick="event.stopPropagation()">
                    NIU
                </td>
                <td>0.00</td>
                <td>0.915</td>
                <td>${p.precio.toFixed(2)}</td>
                <td>${p.importe.toFixed(2)}</td>
            </tr>
        `;
            });

            actualizarFooter(total);
        }

        // Función para actualizar cantidad directamente
        function actualizarCantidad(index, nuevaCantidad) {
            const cantidad = parseInt(nuevaCantidad);
            const producto = ticket[index];

            if (cantidad <= 0) {
                if (confirm('¿Eliminar este producto del ticket?')) {
                    ticket.splice(index, 1);
                    renderTicket();
                }
                return;
            }

            if (cantidad > producto.cantidad_disponible) {
                alert(`Stock insuficiente. Disponible: ${producto.cantidad_disponible} unidades`);
                // Restaurar valor anterior
                renderTicket();
                return;
            }

            producto.cantidad = cantidad;
            producto.importe = producto.cantidad * producto.precio;
            renderTicket();
        }

        // Función para editar línea (placeholder para futuras funcionalidades)
        function editarLinea(index) {
            // Aquí se puede agregar funcionalidad para editar precios, descuentos, etc.
            console.log('Editando línea:', index, ticket[index]);
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

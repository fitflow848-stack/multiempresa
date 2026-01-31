@extends('layout.app')

@section('content')
@section('title', 'Punto de Venta (POS)')
<link rel="stylesheet" href="{{ asset('css/pos.css') }}">

<div class="pos-container">
    <div class="pos-header">
        <div class="header-left">
            <img src="{{ $logo }}" alt="Logo" style="height: 30px; margin-right: 15px;">
            <select name="sucursal" id="sucursal-select" class="header-select">
                @foreach ($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="header-right">
            <div class="nav-item" onclick="navegarAClientes()"><i class="fa-solid fa-user-group"></i> Clientes</div>
            <div class="nav-item"><a href="{{ route('comprobantes.index') }}"><i class="fa-solid fa-book-open"></i>
                    Comprobantes</a></div>
            <div class="nav-item"><a href="{{ route('cierre-caja.index') }}"><i class="fa-solid fa-money-bill-wave"></i>
                    Caja</a></div>
            <div class="nav-info"><i class="fa-solid fa-user"></i> {{ Auth::user()->name }}</div>
            <div class="nav-info"><i class="fa-solid fa-shop"></i> TPV VD</div>
        </div>
    </div>

    <div class="action-bar compact-bar">
        <!-- Sección Izquierda: Búsqueda -->
        <div class="bar-left">
            <div class="families-btn" title="Familias">
                <span>📦</span>
            </div>

            <div class="search-group">
                <input type="text" class="search-input main-search" placeholder="Buscar por Código de Barras..."
                    autofocus>
                <input type="text" class="search-input secondary-search"
                    placeholder="Nombre | Marca | Modelo | Detalle">
            </div>

            <div class="action-buttons">
                <button class="icon-btn" title="Listado">📋</button>
            </div>
        </div>

        <!-- Sección Derecha: Pago y Opciones -->
        <div class="bar-right">
            <div class="payment-group">
                <label class="payment-radio">
                    <input type="radio" name="payment" checked>
                    <span>Contado</span>
                </label>
                <label class="payment-radio">
                    <input type="radio" name="payment">
                    <span>Crédito</span>
                </label>
            </div>

            <div class="option-check">
                <input type="checkbox" id="proforma-checkbox" name="Proforma">
                <label for="proforma-checkbox">Proforma</label>
            </div>

            <div class="currency-display">
                SOL -
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="main-content-split">
        <!-- Area de Resultados (Izquierda o Arriba según preferencia, aquí lo hacemos flexible) -->
        <div class="results-area">
            <div id="productos-listado">
                <table class="productos-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Producto</th>
                            <th style="width: 15%;">Stock</th>
                            <th style="width: 15%;">PVP</th>
                            <th style="width: 20%;">PVC</th>
                        </tr>
                    </thead>
                    <tbody id="productos-tbody">
                        <!-- Los resultados aparecen aquí -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sección del Ticket (Derecha o Abajo) -->
        <div class="ticket-area">
            <div class="ticket-header-compact">
                <span>TICKET ACTUAL ▷</span>
                <div class="ticket-actions-mini">
                    <button
                        onclick="typeof Swal !== 'undefined' ? limpiarTicketRapidoConSweetAlert() : limpiarTicketRapido()"
                        title="Limpiar todo">🗑️</button>
                    <button onclick="aplicarDescuentoGlobal()" title="Descuento Global">💸</button>
                </div>
            </div>

            <div class="ticket-controls-compact">
                <button class="btn-compact danger"
                    onclick="typeof Swal !== 'undefined' ? cancelarVentaConSweetAlert() : cancelarVenta()">Cancelar</button>
                <button class="btn-compact primary" onclick="guardarTicket()">Guardar</button>
                <button class="btn-compact success" onclick="mostrarSeleccionTipoDocumento()">Emitir</button>
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

            <!-- Ventas asignadas a la caja abierta -->
            <div id="caja-ventas-list"
                style="margin-top:8px; padding:8px; background:#fafafa; border:1px solid #eee; border-radius:6px; min-height:42px;">
                <div style="color:#666; font-size:12px;">Estado de caja desconocido.</div>
            </div>
        </div>

    </div>

    <!-- Footer -->
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
                <span class="label">ICBPER</span> : <span class="value">S/ <span id="footer-icbper">0.00</span></span>
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
            <span id="footer-cliente" style="font-weight:bold;">CLIENTE CONTABLE</span>
        </div>
    </div>
</div>

<!-- Menú contextual para opciones de producto -->
@include('pos.partials.modals.context-menu')

<!-- Modal de Búsqueda de Clientes -->
@include('pos.partials.modals.modal-buscar-clientes')

<!-- Modal de Nuevo Cliente por DNI -->
@include('pos.partials.modals.modal-cliente-dni')

<!-- Modal de Nuevo Cliente Manual -->
@include('pos.partials.modals.modal-nuevo-cliente')

<!-- Modal de Selección de Tipo de Documento -->
@include('pos.partials.modals.modal-tipo-documento')

<script src="{{ asset('assets/js/helpers.js') }}"></script>
@include('pos.partials.js.persistencia-venta')

@if (isset($cotizacionData) && $cotizacionData)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sobreescribir persistencia si viene de una cotización
            // Limpiamos persistencia anterior para evitar mezclas
            try {
                localStorage.removeItem('ventaPersistentePOS');
                sessionStorage.removeItem('ticketGuardadoPOS');
            } catch (e) {}

            ticket = [];
            let cotizacionData = @json($cotizacionData);
            let idCoti = cotizacionData.cotizacion.id;
            // Variable global para usar al emitir
            window.cotizacionId = idCoti;

            // Poblar ticket
            if (cotizacionData.productos) {
                cotizacionData.productos.forEach(p => {
                    ticket.push({
                        id: p.producto_id,
                        producto_id: p.producto_id,
                        product_linea_id: null, // Si hace falta buscarlo
                        nombre: p.descripcion,
                        cantidad: parseFloat(p.cantidad),
                        cantidad_disponible: 9999, // Asumimos stock disponible al venir de cotización aprobada, o 9999
                        precio: parseFloat(p.precio),
                        importe: parseFloat(p.importe),
                        pvp: parseFloat(p.precio),
                        pvc: parseFloat(p.precio),
                        lote: p.lote,
                        fecha_vencimiento: p.fecha_vencimiento,
                        es_lote_especifico: !!p.lote,
                        descuento: p.descuento || 0,
                        descuentoFijo: 0,
                        descuentoTexto: (p.descuento ? p.descuento + '%' : '0%')
                    });
                });
            }

            // Renderizar
            renderTicket();

            // Cargar cliente si existe
            if (cotizacionData.cliente) {
                window.clienteActual = cotizacionData.cliente;
                // Actualizar UI
                const elNombre = document.getElementById('footer-cliente');
                if (elNombre) elNombre.innerText = clienteActual.nombre;

                // Y otros elementos de cliente
                const infoNombre = document.getElementById('cliente-info-nombre');
                if (infoNombre) infoNombre.textContent = clienteActual.nombre;
                const infoDoc = document.getElementById('cliente-info-documento');
                if (infoDoc) infoDoc.textContent = clienteActual.numero_documento || '';
            }

            mostrarNotificacion('Datos cargados desde Cotización #' + cotizacionData.cotizacion.numero);
        });
    </script>
@endif

@include('pos.partials.js.cliente-venta')

<script>
    // Verificar si hay lotes seleccionados pendientes de agregar al ticket
    document.addEventListener('DOMContentLoaded', function() {
        inicializarSistemaVentaPersistente();
        verificarLotesPendientes();
        verificarClienteSeleccionado();
        // Consultar estado de caja al iniciar
        if (typeof checkCajaStatus === 'function') {
            checkCajaStatus();
        }
    });

    async function checkCajaStatus() {
        try {
            const res = await fetch('{{ route('cierre-caja.caja.open') }}');
            if (!res.ok) throw new Error('Network response was not ok');
            const data = await res.json();
            const el = document.getElementById('caja-ventas-list');
            if (!el) return;
            if (data.open && data.caja) {
                const ingresos = parseFloat(data.caja.ingresos || 0).toFixed(2);
                const egresos = parseFloat(data.caja.egresos || 0).toFixed(2);
                el.innerText = `Caja Abierta • In: S/ ${ingresos} / Eg: S/ ${egresos}`;
                el.style.color = '#117a37';
            } else {
                el.innerText = 'Caja Cerrada';
                el.style.color = '#666';
            }
        } catch (err) {
            console.error('Error al consultar caja:', err);
            const el = document.getElementById('caja-status');
            if (el) {
                el.innerText = '(error)';
                el.style.color = '#d9534f';
            }
        }
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
        window.location.href = '{{ route('clientes.index') }}';
    }

    // Función para mostrar la modal de selección de tipo de documento
    async function mostrarSeleccionTipoDocumento() {

        if (ticket.length === 0) {
            alert('No hay productos en el ticket para emitir');
            return;
        }

        // Verificar que la caja esté abierta
        try {
            const res = await fetch('{{ route('cierre-caja.caja.open') }}');
            if (!res.ok) throw new Error('Error de red');
            const data = await res.json();
            if (!data.open) {
                alert('No hay una caja abierta. Abra una caja antes de emitir ventas.');
                return;
            }
        } catch (err) {
            console.error('No se pudo verificar caja abierta:', err);
            alert('No se pudo verificar el estado de la caja. Intente nuevamente.');
            return;
        }

        // Si es "Cliente Contable", crear cliente contable automáticamente
        if (!clienteActual || !clienteActual.id || clienteActual.nombre === 'CLIENTE CONTABLE' || clienteActual
            .nombre === 'Cliente Contado') {
            crearClienteContable();
        }

        // Si el checkbox de proforma está marcado, emitir como cotización directamente
        if (document.getElementById('proforma-checkbox') && document.getElementById('proforma-checkbox').checked) {
            // Calcular totales (precios ya incluyen IGV)
            let total_con_igv = 0;
            ticket.forEach(item => {
                total_con_igv += parseFloat(item.precio || 0) * parseFloat(item.cantidad || 0);
            });

            const subtotal = Math.round((total_con_igv / 1.18) * 100) / 100;
            const igv = Math.round((total_con_igv - subtotal) * 100) / 100;

            const datosVenta = {
                ticket: JSON.stringify(ticket),
                cliente: JSON.stringify(clienteActual || {}),
                subtotal: subtotal.toFixed(2),
                igv: igv.toFixed(2),
                total: total_con_igv.toFixed(2),
                tipo_documento: 'cotizacion',
                proforma: '1',
                _token: '{{ csrf_token() }}'
            };

            // Deshabilitar el botón activo para evitar doble clic
            const btnAceptar = document.activeElement || null;
            if (btnAceptar && btnAceptar.tagName === 'BUTTON') {
                btnAceptar.disabled = true;
                btnAceptar.textContent = 'Procesando...';
            }

            fetch('{{ route('cotizaciones.save-cotizacion') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(datosVenta)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(
                            `¡Cotización guardada exitosamente!\nNúmero: ${data.data.numero_completo || data.numero_completo || 'N/A'}\nTotal: S/ ${data.data.total || data.total || total_con_igv.toFixed(2)}`
                        );

                        // Abrir PDF de cotización en nueva pestaña
                        const cotizacionId = data.data.cotizacion_id || data.data.venta_id || data.data.id ||
                            data.id;
                        if (cotizacionId) {
                            const urlA4 = '{{ route('cotizaciones.pdfCotizacion', ':id') }}'.replace(':id',
                                cotizacionId);
                            const url8cm = '{{ route('cotizaciones.pdfCotizacion8cm', ':id') }}'.replace(':id',
                                cotizacionId);
                            const openUrl = url8cm; // Para proformas usar formato 8cm por defecto
                            window.open(openUrl, '_blank');
                        }

                        // Limpiar ticket y datos temporales
                        try {
                            localStorage.removeItem('ticketPOS');
                            sessionStorage.removeItem('ticketPOS');
                            localStorage.removeItem('ventaPersistentePOS');
                            sessionStorage.removeItem('ticketGuardadoPOS');
                            sessionStorage.removeItem('clienteGuardadoPOS');
                        } catch (e) {
                            console.warn('No se pudieron limpiar algunas claves de storage:', e);
                        }

                        // Limpiar ticket actual y regresar al POS
                        ticket = [];
                        renderTicket();

                        setTimeout(() => {
                            window.location.href = '{{ route('pos.index') }}';
                        }, 1000);
                    } else {
                        alert('Error al guardar la cotización: ' + (data.message || 'Error desconocido'));
                        if (btnAceptar) {
                            btnAceptar.disabled = false;
                            btnAceptar.textContent = 'Emitir';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error guardando cotización:', error);
                    alert('Error de conexión al guardar la cotización');
                    if (btnAceptar) {
                        btnAceptar.disabled = false;
                        btnAceptar.textContent = 'Emitir';
                    }
                });

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

        // Asegurar que siempre hay un cliente (crear cliente contable si es necesario)
        if (!clienteActual || !clienteActual.id) {
            crearClienteContable();
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
        subtotal = Math.round((total_con_igv / 1.18) * 100) / 100; // Base sin IGV
        igv = Math.round((total_con_igv - subtotal) * 100) / 100; // IGV calculado
        total = total_con_igv; // Total es el precio con IGV incluido

        // Preparar datos para enviar
        const datosVenta = {
            ticket: JSON.stringify(ticket),
            cliente: JSON.stringify(clienteActual),
            subtotal: subtotal.toFixed(2),
            igv: igv.toFixed(2),
            total: total.toFixed(2),
            tipo_documento: tipoDocumento,
            proforma: (document.getElementById('proforma-checkbox') && document.getElementById('proforma-checkbox')
                .checked) ? '1' : '0',
            id_coti: window.cotizacionId || null,
            _token: '{{ csrf_token() }}'
        };

        // Crear formulario y enviarlo por POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('pos.emitir.post') }}';
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
                            <input type="number" id="cantidad-precio-corp" value="1" min="0.001" step="any" max="999" 
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

    async function aceptarPrecioCorp() {
        const cantidadInput = document.getElementById('cantidad-precio-corp');
        const cantidad = parseFloat(cantidadInput.value);

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
            producto_linea_id: currentProduct.product_linea_id || currentProduct.producto_linea_id || null,
            nombre: currentProduct.nombre + ' (Precio Corp.)',
            cantidad: cantidad,
            cantidad_disponible: currentProduct.cantidad_total || 999,
            precio: parseFloat(currentProduct.pvc),
            importe: cantidad * parseFloat(currentProduct.pvc),
            pvp: currentProduct.pvp,
            pvc: currentProduct.pvc,
            es_precio_corporativo: true,
            descuento: 0, // Inicializar descuento porcentual
            descuentoFijo: 0, // Inicializar descuento fijo
            descuentoTexto: '0%', // Texto mostrado en UI
            fecha_vencimiento: currentProduct.fecha_vencimiento
        };

        // Si el producto tiene un solo lote, intentar obtener su almacen_detalle_id
        if (currentProduct.total_lotes && parseInt(currentProduct.total_lotes) === 1) {
            try {
                const resp = await fetch(
                    `/pos/obtener-lotes?producto_id=${encodeURIComponent(currentProduct.producto_id)}`);
                if (resp.ok) {
                    const lotes = await resp.json();
                    if (Array.isArray(lotes) && lotes.length > 0) {
                        const lote = lotes[0];
                        productoConPrecioCorp.almacen_detalle_id = lote.lote_id || lote.id || null;
                        productoConPrecioCorp.lote = lote.lote || productoConPrecioCorp.lote;
                        productoConPrecioCorp.fecha_vencimiento = lote.fecha_vencimiento || productoConPrecioCorp
                            .fecha_vencimiento;
                        productoConPrecioCorp.es_lote_especifico = true;
                    }
                }
            } catch (e) {
                console.warn('No fue posible obtener lotes:', e);
            }
        }

        agregarProductoAlTicket(productoConPrecioCorp);

        mostrarNotificacion(
            `Se agregó ${cantidad} unidad(es) a precio corporativo: S/ ${parseFloat(currentProduct.pvc).toFixed(2)}`
        );
        cerrarModalPrecioCorp();
    }

    function cerrarModalPrecioCorp() {
        const modal = document.getElementById('modal-precio-corp');
        if (modal) {
            modal.remove();
        }
    }

    function venderPrecioPublico() {
        if (!currentProduct) return;

        const precioPublico = parseFloat(currentProduct.pvp || 0);
        if (precioPublico <= 0) {
            alert('Este producto no tiene precio público definido');
            cerrarContextMenu();
            return;
        }

        // Crear modal para precio público
        const modalHtml = `
                <div id="modal-precio-publico" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; justify-content: center; align-items: center;" onclick="cerrarModalPrecioPublico()">
                    <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); min-width: 350px; font-family: Arial, sans-serif;" onclick="event.stopPropagation()">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div style="font-weight: 600; font-size: 14px; color: #333; margin-bottom: 10px;">
                                ¿Cuántas unidades a precio público ( PVP: S/ ${precioPublico.toFixed(2)} )?
                            </div>
                        </div>
                        <div style="text-align: center; margin-bottom: 20px;">
                            <input type="number" id="cantidad-precio-publico" value="1" min="0.001" step="any" max="${currentProduct.cantidad_total || 999}" 
                                style="width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-size: 16px;"
                                   onkeypress="if(event.key==='Enter') aceptarPrecioPublico()">
                        </div>
                        <div style="display: flex; justify-content: center; gap: 10px;">
                            <button onclick="aceptarPrecioPublico()" style="padding: 8px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Aceptar</button>
                            <button onclick="cerrarModalPrecioPublico()" style="padding: 8px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Cancelar</button>
                        </div>
                    </div>
                </div>
            `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Enfocar el input y seleccionar el texto
        setTimeout(() => {
            const input = document.getElementById('cantidad-precio-publico');
            input.focus();
            input.select();
        }, 100);

        cerrarContextMenu();
    }

    async function aceptarPrecioPublico() {
        const cantidadInput = document.getElementById('cantidad-precio-publico');
        const cantidad = parseFloat(cantidadInput.value);

        if (!cantidad || cantidad <= 0) {
            alert('Ingrese una cantidad válida');
            return;
        }

        if (!currentProduct) {
            cerrarModalPrecioPublico();
            return;
        }

        // Verificar stock disponible
        if (cantidad > (currentProduct.cantidad_total || 0)) {
            alert(`Stock insuficiente. Disponible: ${currentProduct.cantidad_total || 0} unidades`);
            return;
        }

        // Crear producto para el ticket con precio público
        const productoConPrecioPublico = {
            id: `pub_${currentProduct.producto_id}_${Date.now()}`,
            producto_id: currentProduct.producto_id,
            producto_linea_id: currentProduct.product_linea_id || currentProduct.producto_linea_id || null,
            nombre: currentProduct.nombre,
            cantidad: cantidad,
            cantidad_disponible: currentProduct.cantidad_total || 0,
            precio: parseFloat(currentProduct.pvp),
            importe: cantidad * parseFloat(currentProduct.pvp),
            pvp: currentProduct.pvp,
            pvc: currentProduct.pvc,
            es_precio_publico: true,
            descuento: 0, // Inicializar descuento porcentual
            descuentoFijo: 0, // Inicializar descuento fijo
            descuentoTexto: '0%', // Texto mostrado en UI,
            fecha_vencimiento: currentProduct.fecha_vencimiento
        };

        // Si el producto tiene un solo lote, intentar obtener su almacen_detalle_id
        if (currentProduct.total_lotes && parseInt(currentProduct.total_lotes) === 1) {
            try {
                const resp = await fetch(
                    `/pos/obtener-lotes?producto_id=${encodeURIComponent(currentProduct.producto_id)}`);
                if (resp.ok) {
                    const lotes = await resp.json();
                    if (Array.isArray(lotes) && lotes.length > 0) {
                        const lote = lotes[0];
                        productoConPrecioPublico.almacen_detalle_id = lote.lote_id || lote.id || null;
                        productoConPrecioPublico.lote = lote.lote || productoConPrecioPublico.lote;
                        productoConPrecioPublico.fecha_vencimiento = lote.fecha_vencimiento ||
                            productoConPrecioPublico.fecha_vencimiento;
                        productoConPrecioPublico.es_lote_especifico = true;
                    }
                }
            } catch (e) {
                console.warn('No fue posible obtener lotes:', e);
            }
        }

        agregarProductoAlTicket(productoConPrecioPublico);

        mostrarNotificacion(
            `Se agregó ${cantidad} unidad(es) a precio público: S/ ${parseFloat(currentProduct.pvp).toFixed(2)}`
        );
        cerrarModalPrecioPublico();
    }

    function cerrarModalPrecioPublico() {
        const modal = document.getElementById('modal-precio-publico');
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

    // Función para limpiar venta persistente al completar venta exitosamente
    function limpiarVentaCompletada() {
        try {
            // Limpiar venta persistente
            localStorage.removeItem(AUTOSAVE_KEY);

            // Detener auto-guardado
            if (autoSaveInterval) {
                clearInterval(autoSaveInterval);
                autoSaveInterval = null;
            }

            // Limpiar también storage temporal
            sessionStorage.removeItem('ticketGuardadoPOS');
            sessionStorage.removeItem('clienteGuardadoPOS');
            localStorage.removeItem('ticketPOS');

            console.log('Venta completada - persistencia limpiada');

            // Reiniciar auto-guardado para próxima venta
            setTimeout(() => {
                iniciarAutoGuardado();
            }, 1000);

        } catch (error) {
            console.error('Error limpiando venta completada:', error);
        }
    }

    // Función para mostrar estado de la venta persistente (para debugging)
    function mostrarEstadoVentaPersistente() {
        const ventaGuardada = localStorage.getItem(AUTOSAVE_KEY);
        if (ventaGuardada) {
            const ventaData = JSON.parse(ventaGuardada);
            const fechaFormateada = new Date(ventaData.fecha).toLocaleString('es-PE');
            console.log('Estado venta persistente:', {
                productos: ventaData.ticket.length,
                cliente: ventaData.cliente?.nombre || 'Sin cliente',
                fecha: fechaFormateada,
                total: ventaData.ticket.reduce((sum, item) => sum + (item.importe || 0), 0).toFixed(2)
            });
            return ventaData;
        } else {
            console.log('No hay venta persistente guardada');
            return null;
        }
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
        if (ticket.length === 0) {
            mostrarNotificacion('No hay productos en el ticket para cancelar');
            if (typeof cerrarContextMenu === 'function') cerrarContextMenu();
            return;
        }

        if (confirm('¿Está seguro que desea cancelar la venta? Se perderán todos los productos del ticket.')) {
            // Resetear ticket
            ticket = [];

            // Limpiar storage y venta persistente
            sessionStorage.removeItem('ticketGuardadoPOS');
            localStorage.removeItem('ticketPOS');
            sessionStorage.removeItem('clienteGuardadoPOS');

            // LIMPIAR VENTA PERSISTENTE
            localStorage.removeItem(AUTOSAVE_KEY);

            // Detener auto-guardado
            if (autoSaveInterval) {
                clearInterval(autoSaveInterval);
                autoSaveInterval = null;
            }

            // Resetear cliente a "Cliente Contable"
            window.clienteActual = {
                id: null,
                documento: '',
                nombre: 'CLIENTE CONTABLE',
                direccion: '',
                telefono: '',
                email: ''
            };

            // Actualizar UI del cliente
            const clienteNombre = document.getElementById('cliente-info-nombre');
            const clienteDoc = document.getElementById('cliente-info-documento');
            if (clienteNombre) clienteNombre.textContent = 'CLIENTE CONTABLE';
            if (clienteDoc) clienteDoc.textContent = '';

            // Limpiar búsqueda de productos
            const searchInput = document.querySelector('input[placeholder="Buscar productos..."]');
            if (searchInput) {
                searchInput.value = '';
            }

            // Limpiar tabla de productos
            const productosTable = document.getElementById('productos-tbody');
            if (productosTable) {
                productosTable.innerHTML = '';
            }

            // Renderizar ticket vacío
            renderTicket();

            // Notificación de éxito
            console.log('Venta cancelada y sistema reseteado');

            // Mostrar mensaje de confirmación
            setTimeout(() => {
                mostrarNotificacion('✅ Venta cancelada correctamente. Sistema reseteado.');
            }, 100);
        }
        if (typeof cerrarContextMenu === 'function') cerrarContextMenu();
    }

    function guardarTicket() {
        if (ticket.length === 0) {
            mostrarNotificacion('No hay productos en el ticket para guardar');
            return;
        }

        try {
            // Guardar en sistema persistente
            guardarVentaPersistente();

            // También en sessionStorage para compatibilidad
            sessionStorage.setItem('ticketGuardadoPOS', JSON.stringify(ticket));

            // Guardar información del cliente actual también
            if (clienteActual && clienteActual.nombre !== 'CLIENTE CONTABLE' && clienteActual.nombre !==
                'Cliente Contado') {
                sessionStorage.setItem('clienteGuardadoPOS', JSON.stringify(clienteActual));
            }

            // Notificación de éxito
            const total = ticket.reduce((sum, item) => sum + (item.importe || 0), 0);
            const fechaGuardado = new Date().toLocaleString('es-PE');
            mostrarNotificacion(
                `💾 Venta guardada permanentemente: ${ticket.length} productos - Total: S/ ${total.toFixed(2)} (${fechaGuardado})`
            );

            console.log('Ticket guardado manualmente:', {
                ticket,
                cliente: clienteActual,
                fecha: fechaGuardado
            });
        } catch (error) {
            console.error('Error al guardar ticket:', error);
            mostrarNotificacion('❌ Error al guardar el ticket. Intente nuevamente.');
        }
    }

    function limpiarTicketRapido() {
        if (ticket.length === 0) {
            mostrarNotificacion('El ticket ya está vacío');
            return;
        }

        if (confirm(
                '¿Está seguro que desea limpiar el ticket? Solo se eliminarán los productos, el cliente seleccionado se mantendrá.'
            )) {
            // Solo limpiar ticket, mantener cliente
            ticket = [];

            // Limpiar storage del ticket
            sessionStorage.removeItem('ticketGuardadoPOS');
            localStorage.removeItem('ticketPOS');

            // Renderizar ticket vacío
            renderTicket();

            mostrarNotificacion('🗑️ Ticket limpiado correctamente');
            console.log('Ticket limpiado, cliente mantenido');
        }
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

    // Modifica la función renderProductos para llenar la tabla (no el grid):
    function renderProductos(productos) {
        const tbody = document.getElementById('productos-tbody');
        tbody.innerHTML = ''; // Limpia resultados previos

        productos.forEach(p => {
            const tr = document.createElement('tr');
            tr.style.cursor = 'pointer';

            // Clic izquierdo: agregar directamente 1 unidad a precio público
            tr.addEventListener('click', async (ev) => {
                ev.preventDefault();

                const precioPublico = parseFloat(p.pvp || 0);
                if (precioPublico <= 0) {
                    alert('Este producto no tiene precio público definido');
                    return;
                }

                // Si hay un solo lote disponible, intentar obtener su id para decrementar stock en el backend
                let almacenDetalleId = null;
                try {
                    if (p.total_lotes && parseInt(p.total_lotes) === 1) {
                        const resp = await fetch(
                            `/pos/obtener-lotes?producto_id=${encodeURIComponent(p.producto_id)}`
                        );
                        if (resp.ok) {
                            const lotes = await resp.json();
                            if (Array.isArray(lotes) && lotes.length > 0) {
                                almacenDetalleId = lotes[0].id || lotes[0].ad_id || lotes[0]
                                    .almacen_detalle_id || null;
                            }
                        }
                    }
                } catch (e) {
                    console.warn('No fue posible obtener lotes:', e);
                }

                const productoParaTicket = {
                    id: `pub_${p.producto_id}_${Date.now()}`,
                    producto_id: p.producto_id,
                    producto_linea_id: p.product_linea_id || p.producto_linea_id || null,
                    nombre: p.nombre,
                    cantidad: 1,
                    cantidad_disponible: p.cantidad_total || 0,
                    precio: precioPublico,
                    importe: precioPublico * 1,
                    pvp: p.pvp,
                    pvc: p.pvc,
                    es_precio_publico: true,
                    es_lote_especifico: false,
                    descuento: 0,
                    descuentoFijo: 0,
                    descuentoTexto: '0%',
                    fecha_vencimiento: p.fecha_vencimiento,
                };

                if (almacenDetalleId) {
                    productoParaTicket.almacen_detalle_id = almacenDetalleId;
                    // Mark as lote specific so ticket matching uses unique id when needed
                    productoParaTicket.es_lote_especifico = true;
                }
                console.log(productoParaTicket);
                agregarProductoAlTicket(productoParaTicket);
            });

            // Clic derecho: mostrar menú contextual/listado
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
        // Soporta agregar múltiple cantidad y agrupar por lote/producto y precio
        const qtyToAdd = producto.cantidad ? parseFloat(producto.cantidad) : 1;
        const precioProducto = Number(producto.precio ?? producto.pvp ?? 0);

        const existente = ticket.find(p => {
            const precioExistente = Number(p.precio ?? p.pvp ?? 0);
            if (producto.almacen_detalle_id && p.almacen_detalle_id) {
                return String(p.almacen_detalle_id) === String(producto.almacen_detalle_id) &&
                    precioExistente === precioProducto;
            }
            // Agrupar por producto_id y precio cuando no hay info de lote
            return p.producto_id === producto.producto_id && precioExistente === precioProducto;
        });

        if (existente) {
            // Verificar disponibilidad antes de incrementar
            // Si es reemplazo, la cantidad final es qtyToAdd
            // Si es suma, la cantidad final es existente.cantidad + qtyToAdd
            const cantidadFinal = producto.replaceQuantity ? qtyToAdd : (existente.cantidad + qtyToAdd);

            if (cantidadFinal <= existente.cantidad_disponible) {
                existente.cantidad = cantidadFinal;
                existente.importe = existente.cantidad * existente.precio;
            } else {
                alert(`Stock insuficiente. Disponible: ${existente.cantidad_disponible} unidades`);
            }
        } else {
            // Agregar nuevo producto al ticket respetando la cantidad solicitada
            console.log("aca");
            console.log(producto);
            const nuevoProducto = {
                id: producto.id,
                producto_id: producto.producto_id,
                producto_linea_id: producto.producto_linea_id,
                nombre: producto.nombre,
                cantidad: qtyToAdd,
                cantidad_disponible: producto.cantidad_disponible || producto.cantidad_total || 999,
                precio: producto.precio || parseFloat(producto.pvp),
                importe: (producto.precio || parseFloat(producto.pvp)) * qtyToAdd,
                pvp: producto.pvp,
                pvc: producto.pvc,
                lote: producto.lote || null,
                fecha_vencimiento: producto.fecha_vencimiento || null,
                almacen_detalle_id: producto.almacen_detalle_id || null,
                es_lote_especifico: producto.es_lote_especifico || false,
                descuento: 0, // Inicializar descuento porcentual
                descuentoFijo: 0, // Inicializar descuento fijo
                descuentoTexto: '0%' // Texto mostrado en UI
            };

            ticket.push(nuevoProducto);
        }

        renderTicket();
        // Auto-guardar después de agregar producto
        guardarVentaPersistente();
    }

    function renderTicket() {
        const tbody = document.getElementById('ticket-tbody');
        tbody.innerHTML = '';

        let total = 0;

        ticket.forEach((p, idx) => {
            total += p.importe;

            // Formatear información de lote y vencimiento
            // Mostrar el nombre tal cual (como en la foto 1). Ocultamos la etiqueta de lote
            // en la vista para que el nombre quede igual, pero la dejamos en el title (hover).
            let infoLote = '';
            let titleLote = '';
            if (p.es_lote_especifico && p.lote) {
                infoLote = '';
                titleLote = ` (Lt: ${p.lote})`;
            }

            let fechaVencimiento = '---';
            if (p.fecha_vencimiento) {
                fechaVencimiento = p.fecha_vencimiento;
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
            <tr style="background:${bgColor};" onclick="editarLinea(${idx})" oncontextmenu='mostrarMenuTicket(event, ${JSON.stringify(p)})'>
                <td>${idx + 1}</td>
                <td title="${p.nombre}${titleLote}">
                    ${p.nombre.length > 100 ? p.nombre.substring(0, 25) + '...' : p.nombre}
                </td>
                <td>${fechaVencimiento}</td>
                <td>
                    <input type="number" value="${p.cantidad}" min="1" max="${p.cantidad_disponible}" 
                           style="width: 50px; border: none; background: transparent; text-align: center;"
                           onchange="actualizarCantidad(${idx}, this.value)"
                           onclick="event.stopPropagation()">
                    NIU
                </td>
                <td>
                    <input type="text" value="${p.descuentoTexto || (p.descuento || 0) + '%'}" 
                           style="width: 80px; border: none; background: transparent; text-align: center; color: #d63384; font-size: 11px;"
                           onchange="actualizarDescuento(${idx}, this.value)"
                           onclick="event.stopPropagation()"
                           placeholder="0% o S/0"
                           title="Ingrese porcentaje (ej: 12%) o monto fijo (ej: S/12)">
                </td>
                <td>0.915</td>
                <td>${p.precio.toFixed(2)}</td>
                <td>${p.importe.toFixed(2)}</td>
            </tr>
        `;
        });

        actualizarFooter(total);

        // Auto-guardar después de renderizar (con throttle para evitar muchas llamadas)
        if (ticket.length > 0) {
            clearTimeout(window.renderTicketSaveTimeout);
            window.renderTicketSaveTimeout = setTimeout(() => {
                guardarVentaPersistente();
            }, 1000); // Esperar 1 segundo después del último cambio
        }
    }

    // Función para actualizar cantidad directamente
    function actualizarCantidad(index, nuevaCantidad) {
        const cantidad = parseFloat(nuevaCantidad);
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
        // Recalcular importe considerando descuento
        const subtotalSinDescuento = producto.cantidad * producto.precio;
        const montoDescuento = subtotalSinDescuento * (producto.descuento || 0) / 100;
        producto.importe = subtotalSinDescuento - montoDescuento;
        renderTicket();
    }

    // Función para editar línea (placeholder para futuras funcionalidades)
    function editarLinea(index) {
        // Aquí se puede agregar funcionalidad para editar precios, descuentos, etc.
        console.log('Editando línea:', index, ticket[index]);
    }

    // Función para actualizar descuento (porcentaje o monto fijo)
    function actualizarDescuento(index, valorDescuento) {
        const producto = ticket[index];
        const valor = valorDescuento.toString().trim();

        if (!valor || valor === '0' || valor === '0%' || valor === 'S/0') {
            // Sin descuento
            producto.descuento = 0;
            producto.descuentoFijo = 0;
            producto.descuentoTexto = '0%';
            producto.importe = producto.cantidad * producto.precio;
            renderTicket();
            return;
        }

        const subtotalSinDescuento = producto.cantidad * producto.precio;
        let montoDescuento = 0;
        let textoDescuento = '';

        if (valor.includes('S/') || valor.includes('s/')) {
            // Descuento fijo en soles
            const montoFijo = parseFloat(valor.replace(/[S\/s\/\s]/g, ''));

            if (isNaN(montoFijo) || montoFijo < 0) {
                alert('Ingrese un monto válido (ej: S/12)');
                renderTicket();
                return;
            }

            if (montoFijo >= subtotalSinDescuento) {
                alert('El descuento no puede ser mayor o igual al subtotal del producto');
                renderTicket();
                return;
            }

            montoDescuento = montoFijo;
            producto.descuento = 0;
            producto.descuentoFijo = montoFijo;
            producto.descuentoTexto = `S/${montoFijo.toFixed(2)}`;
            textoDescuento = `Descuento fijo de S/ ${montoFijo.toFixed(2)}`;

        } else {
            // Descuento porcentual
            const porcentaje = parseFloat(valor.replace('%', ''));

            if (isNaN(porcentaje) || porcentaje < 0 || porcentaje > 100) {
                alert('El descuento debe estar entre 0% y 100%');
                renderTicket();
                return;
            }

            montoDescuento = subtotalSinDescuento * porcentaje / 100;
            producto.descuento = porcentaje;
            producto.descuentoFijo = 0;
            producto.descuentoTexto = `${porcentaje}%`;
            textoDescuento = `Descuento del ${porcentaje}%`;
        }

        // Aplicar descuento
        producto.importe = subtotalSinDescuento - montoDescuento;

        renderTicket();

        // Mostrar notificación si se aplicó descuento
        if (montoDescuento > 0) {
            mostrarNotificacion(`${textoDescuento} aplicado: -S/ ${montoDescuento.toFixed(2)}`);
        }
    }

    // Función para aplicar descuento global a todo el ticket
    function aplicarDescuentoGlobal() {
        if (ticket.length === 0) {
            alert('No hay productos en el ticket');
            return;
        }

        const descuentoGlobal = prompt(
            'Ingrese el descuento global que desea aplicar:\\n\\n• Para porcentaje: 15%\\n• Para monto fijo: S/12\\n• Para quitar descuentos: 0',
            '0%');

        if (descuentoGlobal === null) return; // Cancelado

        const valor = descuentoGlobal.toString().trim();

        if (!valor || valor === '0' || valor === '0%' || valor === 'S/0') {
            // Remover todos los descuentos
            ticket.forEach(producto => {
                producto.descuento = 0;
                producto.descuentoFijo = 0;
                producto.descuentoTexto = '0%';
                producto.importe = producto.cantidad * producto.precio;
            });
            mostrarNotificacion('Descuentos removidos de todos los productos');
            renderTicket();
            return;
        }

        let totalDescuentoAplicado = 0;
        let tipoDescuento = '';

        if (valor.includes('S/') || valor.includes('s/')) {
            // Descuento fijo en soles
            const montoFijo = parseFloat(valor.replace(/[S\\/s\\/\\s]/g, ''));

            if (isNaN(montoFijo) || montoFijo < 0) {
                alert('Ingrese un monto válido (ej: S/12)');
                return;
            }

            ticket.forEach(producto => {
                const subtotalSinDescuento = producto.cantidad * producto.precio;

                if (montoFijo >= subtotalSinDescuento) {
                    // Si el descuento es mayor al subtotal, aplicar máximo posible
                    const descuentoMaximo = subtotalSinDescuento - 0.01; // Dejar al menos 1 centavo
                    producto.descuentoFijo = descuentoMaximo;
                    producto.descuento = 0;
                    producto.descuentoTexto = `S/${descuentoMaximo.toFixed(2)}`;
                    producto.importe = 0.01;
                    totalDescuentoAplicado += descuentoMaximo;
                } else {
                    producto.descuentoFijo = montoFijo;
                    producto.descuento = 0;
                    producto.descuentoTexto = `S/${montoFijo.toFixed(2)}`;
                    producto.importe = subtotalSinDescuento - montoFijo;
                    totalDescuentoAplicado += montoFijo;
                }
            });

            tipoDescuento = `Descuento fijo de S/ ${montoFijo.toFixed(2)}`;

        } else {
            // Descuento porcentual
            const porcentaje = parseFloat(valor.replace('%', ''));

            if (isNaN(porcentaje) || porcentaje < 0 || porcentaje > 100) {
                alert('El descuento debe estar entre 0% y 100%');
                return;
            }

            ticket.forEach(producto => {
                const subtotalSinDescuento = producto.cantidad * producto.precio;
                const montoDescuento = subtotalSinDescuento * porcentaje / 100;

                producto.descuento = porcentaje;
                producto.descuentoFijo = 0;
                producto.descuentoTexto = `${porcentaje}%`;
                producto.importe = subtotalSinDescuento - montoDescuento;
                totalDescuentoAplicado += montoDescuento;
            });

            tipoDescuento = `Descuento del ${porcentaje}%`;
        }

        mostrarNotificacion(
            `${tipoDescuento} aplicado a ${ticket.length} productos. Total descontado: S/ ${totalDescuentoAplicado.toFixed(2)}`
        );
        renderTicket();
    }

    function actualizarFooter(total) {
        let gravada = total / 1.18;
        let igv = total - gravada;
        let icbper = 0.00; // Si tienes cálculo real ponlo aquí

        // Calcular descuentos totales
        let totalDescuentos = 0;
        ticket.forEach(item => {
            if (item.descuentoFijo && item.descuentoFijo > 0) {
                // Descuento fijo
                totalDescuentos += item.descuentoFijo;
            } else if (item.descuento && item.descuento > 0) {
                // Descuento porcentual
                const subtotalSinDescuento = item.cantidad * item.precio;
                const montoDescuento = subtotalSinDescuento * item.descuento / 100;
                totalDescuentos += montoDescuento;
            }
        });

        let cantListado = ticket.length;

        document.getElementById('footer-gravada').innerText = gravada.toFixed(2);
        document.getElementById('footer-igv').innerText = igv.toFixed(2);
        document.getElementById('footer-icbper').innerText = icbper.toFixed(2);
        document.getElementById('footer-dscto').innerText = totalDescuentos.toFixed(2);
        document.getElementById('footer-total').innerText = total.toFixed(2);
        document.getElementById('footer-productos-listados').innerText = cantListado;
        // Si cambias tipo de cliente, actualiza así
        // document.getElementById('footer-cliente').innerText = tipoCliente;
    }
</script>
@include('pos.partials.modals.context-menu-ticket')
@include('pos.partials.js.finalizar-venta')
@endsection

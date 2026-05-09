@extends('layout.app')

@section('content')
@php $canModificarPrecio = auth()->user()->can('productos.modificar_precio') ? 'true' : 'false'; @endphp
<script>window.__canModificarPrecio = {{ $canModificarPrecio }};</script>
<link rel="stylesheet" href="{{ asset('css/pos-horizontal.css') }}">
<script>
    // Ocultar navbar por defecto en POS
    (function() {
        // Forzamos el estado oculto en localStorage para que el script de include/sidebar lo procese
        localStorage.setItem('navbarHidden', 'true');
        // Aplicamos la clase al documentElement de inmediato para evitar el salto de altura en el CSS
        document.documentElement.classList.add('nav-is-hidden');
    })();
</script>


<!-- NAV INFERIOR PARA MÓVIL -->
<div class="pos-mobile-nav">
    <button class="mobile-nav-item active" onclick="switchPosTab('search')">
        <i class='bx bx-search'></i>
        <span>BUSCAR</span>
    </button>
    <button class="mobile-nav-item" onclick="switchPosTab('ticket')">
        <i class='bx bx-cart'></i>
        <span class="badge-count" id="mobile-ticket-count" style="display:none">0</span>
        <span>TICKET</span>
    </button>
    <button class="mobile-nav-item" onclick="switchPosTab('payment')">
        <i class='bx bx-credit-card-front'></i>
        <span>PAGO</span>
    </button>
</div>

<div class="pos-container">
    <!-- COLUMNA IZQUIERDA: BUSCADOR -->
    <aside class="pos-left-sidebar active">
        {{-- Sidebar header con sucursal eliminado a pedido del usuario --}}

        <div class="pos-search-container">
            <i class='bx bx-search pos-search-icon'></i>
            <input type="text" id="main-search-input" class="pos-search-input"
                placeholder="Buscar por Nombre | Código | Marca" autofocus>
        </div>

        <div class="pos-results-scroll" id="product-results-grid">
            <!-- Resultados dinámicos -->
            <div style="text-align: center; color: #888; margin-top: 50px;">
                <i class='bx bx-package' style="font-size: 40px; opacity: 0.3;"></i>
                <p>Busque un producto para comenzar</p>
            </div>
        </div>
    </aside>

    <!-- SECCIÓN CENTRAL: TICKET -->
    <main class="pos-center-content">
        <header class="ticket-header-new">
            <h2>TICKET ACTUAL ▷</h2>
            <div class="ticket-buttons">
                <button class="btn-pos btn-pos-cancel" onclick="cancelarVentaConSweetAlert()">Cancelar</button>
                <button class="btn-pos btn-pos-save" onclick="guardarTicket()">Guardar</button>
                <button type="button" class="btn-pos-history ms-1" onclick="abrirModalVentasGuardadas()" title="Ver Ventas en Espera" 
                        style="height: 38px; width: 42px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; color: #64748b; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                    <i class='bx bx-history fs-4'></i>
                </button>
            </div>
        </header>

        <div class="ticket-table-scroll">
            <table class="table-new">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>PRODUCTO</th>
                        <th style="width: 80px;">CTD.</th>
                        <th style="width: 80px;">DCTO.</th>
                        <th style="width: 100px;">PVU</th>
                        <th style="width: 100px;">IMPORTE</th>
                        <th style="width: 50px;">ACC.</th>
                    </tr>
                </thead>
                <tbody id="ticket-tbody">
                    <!-- Productos agregados -->
                </tbody>
            </table>
        </div>

        <footer class="ticket-footer-new">
            <div class="footer-left-totals">
                <div class="total-item-small">
                    <span>GRAVADA</span>
                    <span class="val">S/ <span id="footer-gravada">0.00</span></span>
                </div>
                <div class="total-item-small">
                    <span>EXONERADA</span>
                    <span class="val">S/ <span id="footer-exonerada">0.00</span></span>
                </div>
                <div class="total-item-small">
                    <span>I.G.V. (18%)</span>
                    <span class="val">S/ <span id="footer-igv">0.00</span></span>
                </div>
            </div>
            <div class="footer-right-totals">
                <div class="total-item-small" style="font-size: 14px;">
                    <span style="color: #fbbf24;">DESCUENTO</span>
                    <span class="val" style="color: #fbbf24;">S/ <span id="footer-dscto">0.00</span></span>
                </div>
                <div class="total-display-large">
                    <span class="label">TOTAL</span>
                    <span class="val">S/ <span id="footer-total">0.00</span></span>
                </div>
            </div>
        </footer>
    </main>

    <!-- COLUMNA DERECHA: EMISIÓN -->
    <aside class="pos-right-sidebar">
        <div class="section-title">Pago</div>

        <div class="payment-tabs">
            <button class="payment-tab active" onclick="cambiarTipoPago('contado')">CONTADO</button>
            <button class="payment-tab" onclick="cambiarTipoPago('credito')">CRÉDITO</button>
            <button class="payment-tab" onclick="cambiarTipoPago('proforma')">PROFORMA</button>
            <input type="hidden" id="tipo-pago-hidden" value="contado">
            <input type="checkbox" id="proforma-checkbox" style="display:none">
        </div>

        <div class="form-group">
            <label>Medio de Pago</label>
            <select id="medio-pago-select" class="form-control-new">
                @foreach($metodos as $metodo)
                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" id="plazo-credito-section" style="display:none">
            <label>Plazo Crédito (Días)</label>
            <input type="number" id="input-plazo-dias" class="form-control-new" value="30" min="1">
        </div>

        <div class="payment-grid">
            <div class="form-group">
                <label>Paga con (Entrega)</label>
                <input type="number" id="input-entrega" class="form-control-new" value="0.00"
                    onkeyup="calcularCambioPOS()" onchange="calcularCambioPOS()">
            </div>
            <div class="form-group">
                <label id="label-tipo-cambio">Cambio</label>
                <div class="change-box" id="pago-status-box">S/ <span id="label-cambio">0.00</span></div>
            </div>
        </div>

        <!-- SECCIÓN PAGO MIXTO (Oculta por defecto) -->
        <div id="seccion-pago-mixto" style="display:none; background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px; border: 1px dashed #6b2e51;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="fw-bold text-primary mb-0"><i class="bx bx-list-check me-1"></i> Desglose Mixto</label>
                <span class="badge bg-label-primary" id="mixto-total-indicador">S/ 0.00</span>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="small fw-bold">Efectivo</label>
                    <input type="number" id="mixto-efectivo" class="form-control-new" value="0.00" onkeyup="actualizarTotalesMixtos()">
                </div>
                <div class="col-6">
                    <label class="small fw-bold">Digital</label>
                    <input type="number" id="mixto-digital" class="form-control-new" value="0.00" onkeyup="actualizarTotalesMixtos()">
                </div>
                <div class="col-12 mt-2">
                    <select id="mixto-metodo-digital" class="form-select form-select-sm">
                        @foreach($metodos->where('es_digital', 1) as $m)
                            <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Observaciones</label>
            <textarea id="input-observaciones" class="form-control-new" style="height: 60px; resize: none;"
                placeholder="Opcional..."></textarea>
        </div>

        <div class="form-group">
            <label>Cliente</label>
            <div class="client-box" onclick="mostrarBuscadorClientes()" style="cursor: pointer;">
                <i class='bx bx-user' style="font-size: 1.25rem;"></i>
                <div class="client-info-container" style="flex: 1;">
                    <div class="client-info-text" id="cliente-info-nombre">CLIENTE CONTABLE</div>
                    <div id="cliente-info-documento" class="client-doc-text">00000000</div>
                </div>
                <i class='bx bx-pencil client-edit-btn' style="font-size: 1.1rem; opacity: 0.5;"></i>
            </div>
        </div>

        <div class="form-group">
            <label>Vendedor</label>
            <select id="vendedor-select" class="form-control-new">
                @foreach($vendedores as $vend)
                    <option value="{{ $vend->id }}" {{ $vend->id == auth()->id() ? 'selected' : '' }}>
                        {{ $vend->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Campos Ocultos para Compatibilidad con el Script de Venta -->
        <div id="pos-documento-section" style="display:none">
            <select id="tipo-documento-select" class="form-control-new">
                @foreach($documentos as $doc)
                    <option value="{{ str_contains(strtolower($doc->nombre), 'factura') ? 'factura' : (str_contains(strtolower($doc->nombre), 'boleta') ? 'boleta' : 'ticket') }}" 
                            data-serie="{{ $doc->series }}">
                        {{ $doc->nombre }}
                    </option>
                @endforeach
            </select>
            <div id="serie-numero-grid">
                <input type="hidden" id="input-serie">
                <input type="hidden" id="input-numero">
            </div>
        </div>

        <button class="btn-confirm-venta" onclick="abrirModalTipoDocumento()">Confirmar Venta</button>
    </aside>

    <!-- Div para previsualización de imagen -->
    <div id="image-preview-tooltip" class="image-preview-tooltip">
        <img src="" alt="Vista previa" id="tooltip-img">
    </div>
</div>

<!-- Modal de Atajos de Teclado -->
<div id="modal-atajos"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 3000; display: none; justify-content: center; align-items: center;">
    <div
        style="background: white; padding: 0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-width: 500px; width: 90%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow: hidden;">
        <!-- Header -->
        <div
            style="background: #6b2e51; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-keyboard"></i> Atajos de Teclado (POS)
            </h3>
            <span onclick="cerrarModalAtajos()" style="cursor: pointer; font-size: 20px;">&times;</span>
        </div>

        <!-- Body -->
        <div style="padding: 20px; max-height: 70vh; overflow-y: auto;">
            <div style="margin-bottom: 20px;">
                <h4
                    style="color: #6b2e51; border-bottom: 2px solid #f0f0f0; padding-bottom: 5px; margin-bottom: 12px; font-size: 15px;">
                    Operaciones de Venta</h4>
                <div style="display: grid; grid-template-columns: 100px 1fr; gap: 10px; font-size: 14px;">
                    <span
                        style="font-weight: bold; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #ddd; text-align: center;">F1</span>
                    <span>Buscar Producto</span>

                    <span
                        style="font-weight: bold; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #ddd; text-align: center;">F3</span>
                    <span>Buscar Cliente</span>

                    <span
                        style="font-weight: bold; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #ddd; text-align: center;">F4</span>
                    <span>Seleccionar Tipo Documento</span>

                    <span
                        style="font-weight: bold; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #ddd; text-align: center;">F8</span>
                    <span>Aplicar Descuento Global</span>

                    <span
                        style="font-weight: bold; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #ddd; text-align: center;">F10</span>
                    <span>Guardar Ticket</span>

                    <span
                        style="font-weight: bold; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #ddd; text-align: center;">F12</span>
                    <span>Emitir / Finalizar Cobro</span>
                </div>
            </div>

            <div>
                <h4
                    style="color: #d63384; border-bottom: 2px solid #f0f0f0; padding-bottom: 5px; margin-bottom: 12px; font-size: 15px;">
                    Gestión y Navegación</h4>
                <div style="display: grid; grid-template-columns: 100px 1fr; gap: 10px; font-size: 14px;">
                    <span
                        style="font-weight: bold; background: #fff1f0; color: #cf1322; padding: 2px 6px; border-radius: 4px; border: 1px solid #ffa39e; text-align: center;">Supr</span>
                    <span>Eliminar Línea Seleccionada</span>

                    <span
                        style="font-weight: bold; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #ddd; text-align: center;">Esc</span>
                    <span>Cancelar / Limpiar Venta</span>

                    <span
                        style="font-weight: bold; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #ddd; text-align: center;">Ctrl
                        + P</span>
                    <span>Imprimir Último Comprobante</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 15px 20px; background: #f8f9fa; text-align: right; border-top: 1px solid #eee;">
            <button onclick="cerrarModalAtajos()"
                style="background: #6b2e51; color: white; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; font-weight: 600;">Entendido</button>
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

<!-- Modales de Acciones de Cliente (Global) -->
@include('pos.partials.modals.modal-editar-cliente-pos')
@include('pos.partials.modals.modal-cobrar-deuda-pos')

<!-- Modal de Selección de Tipo de Documento -->
@include('pos.partials.modals.modal-tipo-documento')

<!-- Modal Romper Docena / Saco -->
@include('pos.partials.modals.modal-romper-docena')
@include('pos.partials.js.romper-docena')

<script>
    // ESTADO GLOBAL DEL POS - Declarado una sola vez al inicio
    window.TIPO_PAGO_MIXTO_ID = {{ $tipoPagoMixtoId ?? 'null' }};
    window.ticket = [];
    window.clienteActual = {
        id: null,
        tipo_documento: 'DNI',
        numero_documento: '',
        nombre: 'Cliente Contado',
        direccion: '',
        email: '',
        telefono: ''
    };
    window.currentProduct = null;
    window.cotizacionId = null;
    window.selectedIndex = -1;
    window.selectedTicketIndex = -1;

    // Funciones para previsualización de imágenes
    function showImagePreview(event, imgSrc) {
        if (!imgSrc) return;
        const tooltip = document.getElementById('image-preview-tooltip');
        const img = document.getElementById('tooltip-img');

        img.src = '/storage/' + imgSrc;
        tooltip.style.display = 'block';

        // Posicionar el tooltip cerca del cursor
        const x = event.clientX + 15;
        const y = event.clientY + 15;

        // Ajustar si se sale de la pantalla (tooltip tiene max-width 250px)
        const tooltipWidth = 260;
        const tooltipHeight = 260;

        let finalX = x;
        let finalY = y;

        if (x + tooltipWidth > window.innerWidth) {
            finalX = x - tooltipWidth - 30;
        }

        if (y + tooltipHeight > window.innerHeight) {
            finalY = y - tooltipHeight - 30;
        }

        tooltip.style.left = finalX + 'px';
        tooltip.style.top = finalY + 'px';
    }

    function hideImagePreview() {
        const tooltip = document.getElementById('image-preview-tooltip');
        if (tooltip) {
            tooltip.style.display = 'none';
            const img = document.getElementById('tooltip-img');
            if (img) img.src = '';
        }
    }

    // Lógica de cambio de pestañas en móvil
    function switchPosTab(tab) {
        // Remover clases activas de la nav
        document.querySelectorAll('.mobile-nav-item').forEach(el => el.classList.remove('active'));
        // Agregar a la clicada
        const targetNav = event.currentTarget || document.querySelector(`.mobile-nav-item[onclick*="${tab}"]`);
        if (targetNav) targetNav.classList.add('active');

        // Remover activas de las secciones
        const left = document.querySelector('.pos-left-sidebar');
        const center = document.querySelector('.pos-center-content');
        const right = document.querySelector('.pos-right-sidebar');

        left.classList.remove('active');
        center.classList.remove('active');
        right.classList.remove('active');

        if (tab === 'search') left.classList.add('active');
        if (tab === 'ticket') center.classList.add('active');
        if (tab === 'payment') right.classList.add('active');
    }

    // Actualizar contador de items en el badge de móvil
    function actualizarCountMobile() {
        const badge = document.getElementById('mobile-ticket-count');
        if (badge) {
            const count = (window.ticket || []).length;
            if (count > 0) {
                badge.innerText = count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    // Inyectar en actualizarFooter
    const oldUpdateCount = window.actualizarFooter;
    window.actualizarFooter = function() {
        if (typeof oldUpdateCount === 'function') oldUpdateCount();
        actualizarCountMobile();
    };
</script>

<script src="{{ asset('assets/js/helpers.js') }}"></script>
@include('pos.partials.js.persistencia-venta')

@if (isset($cotizacionData) && $cotizacionData)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sobreescribir persistencia si viene de una cotización
            // Limpiamos persistencia anterior para evitar mezclas
            try {
                localStorage.removeItem('ventaPersistentePOS');
                sessionStorage.removeItem('ticketGuardadoPOS');
            } catch (e) { }

            ticket = [];
            let cotizacionData = @json($cotizacionData);
            let idCoti = cotizacionData.cotizacion.id;
            // Variable global para usar al emitir
            cotizacionId = idCoti;

            // Poblar ticket
            if (cotizacionData.productos) {
                cotizacionData.productos.forEach(p => {
                    ticket.push({
                        id: p.producto_id,
                        producto_id: p.producto_id,
                        product_linea_id: null, // Si hace falta buscarlo
                        nombre: p.descripcion,
                        marca: p.marca || '',
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
                        descuentoTexto: (p.descuento ? p.descuento + '%' : '0%'),
                        imagen_principal: p.imagen_principal || ''
                    });
                });
            }

            // Renderizar
            renderTicket();

            // Cargar cliente si existe
            if (cotizacionData.cliente) {
                clienteActual = cotizacionData.cliente;
                // Actualizar UI
                const elNombre = document.getElementById('footer-cliente');
                if (elNombre) elNombre.innerText =
                    `${clienteActual.nombre} - ${clienteActual.numero_documento || ''}`;

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
    const isAdmin = @json($isAdmin ?? false);
    // Verificar si hay lotes seleccionados pendientes de agregar al ticket
    document.addEventListener('DOMContentLoaded', async function () {
        inicializarSistemaVentaPersistente();
        verificarLotesPendientes();
        verificarClienteSeleccionado();
        
        // Cargar cliente contable por defecto si no hay uno seleccionado
        if (!clienteActual || !clienteActual.id) {
            await crearClienteContable();
        }

        if (typeof checkCajaStatus === 'function') {
            checkCajaStatus();
        }
        
        cambiarTipoDocumento();

        // Verificar si se solicita un tipo de pago específico por URL
        const urlParams = new URLSearchParams(window.location.search);
        const requestedType = urlParams.get('type');
        if (requestedType === 'proforma') {
            cambiarTipoPago('proforma');
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

    // Función Debounce para evitar múltiples peticiones seguidas
    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    // ── Helper: intentar auto-agregar al ticket si hay resultado único ────────
    function intentarAutoAgregarProducto(productos, limpiarInput) {
        if (productos.length === 1) {
            const p = productos[0];
            const prodAdd = {
                producto_id: p.producto_id,
                id: p.id,
                nombre: p.nombre,
                marca: p.marca || '',
                precio: parseFloat(p.pvp || 0),
                pvp: parseFloat(p.pvp || 0),
                pvc: parseFloat(p.pvc || 0),
                pv_docena: parseFloat(p.pv_docena || 0),
                cantidad: 1,
                descuento: 0,
                importe: parseFloat(p.pvp || 0),
                cantidad_disponible: parseFloat(p.cantidad_total || 0),
                tipo_impuesto: p.tipo_impuesto,
                imagen_principal: p.imagen_principal || '',
                almacen_detalle_id: p.id
            };
            agregarProductoAlTicket(prodAdd);

            // Feedback visual rápido
            const grid = document.getElementById('product-results-grid');
            grid.innerHTML = `
                <div style="text-align:center; color:#22c55e; margin-top:40px;">
                    <i class='bx bx-check-circle' style="font-size:48px;"></i>
                    <p style="font-weight:600; margin-top:8px;">✔ ${p.nombre} agregado al ticket</p>
                </div>`;

            if (limpiarInput) {
                const inp = document.getElementById('main-search-input');
                inp.value = '';
                inp.focus();
                // Limpiar el grid después de un momento
                setTimeout(() => {
                    grid.innerHTML = `
                        <div style="text-align: center; color: #888; margin-top: 50px;">
                            <i class='bx bx-package' style="font-size: 40px; opacity: 0.3;"></i>
                            <p>Busque un producto para comenzar</p>
                        </div>`;
                }, 1200);
            }
            return true; // se agregó
        }
        return false; // no se agregó
    }

    const buscarProductosDebounced = debounce(function (q) {
        const grid = document.getElementById('product-results-grid');
        grid.innerHTML = `
            <div style="text-align: center; margin-top: 50px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Buscando...</p>
            </div>`;

        fetch(`{{ route('pos.buscar') }}?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(productos => {
                // Si la búsqueda parece un código de barras (≥6 chars numéricos) y hay 1 único resultado
                // → auto-agregar sin mostrar tarjetas (flujo scanner sin Enter)
                const esCodigoBarras = q.length >= 6 && /^[\d\w\-]+$/.test(q);
                if (esCodigoBarras && intentarAutoAgregarProducto(productos, true)) {
                    return; // ya se agregó, no renderizar grid
                }
                renderProductosNuevos(productos);
            })
            .catch(() => {
                grid.innerHTML = '<div style="text-align: center; padding: 20px; color: #d9534f;">Error en la búsqueda</div>';
            });
    }, 350);

    // ── PISTOLA / SCANNER DE BARRAS ──────────────────────────────────────────
    // Detectamos escritura extremadamente rápida (< 50 ms entre teclas) como
    // señal de que viene de una pistola de barras y no de un humano.
    let _lastKeyTime = 0;
    let _scanTimer   = null;

    document.getElementById('main-search-input').addEventListener('keydown', function (e) {
        const now = Date.now();
        const gap = now - _lastKeyTime;
        _lastKeyTime = now;

        if (gap < 50) {
            if (_scanTimer) clearTimeout(_scanTimer);
            _scanTimer = setTimeout(() => {}, 200);
        }

        // Cuando la pistola (o usuario) presiona Enter → búsqueda inmediata + auto-agregar
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = this.value.trim();
            
            // Si hay un producto seleccionado con flechas, agregarlo
            if (window.selectedIndex !== -1) {
                const cards = document.querySelectorAll('.pos-product-card');
                if (cards[window.selectedIndex]) {
                    cards[window.selectedIndex].dispatchEvent(new MouseEvent('dblclick'));
                    return;
                }
            }

            if (q.length < 2) return;

            const grid = document.getElementById('product-results-grid');
            grid.innerHTML = `
                <div style="text-align: center; margin-top: 50px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Buscando...</p>
                </div>`;

            fetch(`{{ route('pos.buscar') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(productos => {
                    // Siempre intentar auto-agregar al presionar Enter si hay un solo resultado
                    if (intentarAutoAgregarProducto(productos, true)) return;
                    // Si hay múltiples resultados, mostrarlos para que el usuario elija
                    renderProductosNuevos(productos);
                    
                    // Seleccionar el primero por defecto para navegar con flechas
                    if (productos.length > 0) {
                        window.selectedIndex = 0;
                        actualizarSeleccionVisual();
                    }
                })
                .catch(() => {
                    grid.innerHTML = '<div style="text-align: center; padding: 20px; color: #d9534f;">Error en la búsqueda</div>';
                });
            return;
        }

        // Navegación con flechas
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            const cards = document.querySelectorAll('.pos-product-card');
            if (cards.length === 0) return;
            e.preventDefault();

            if (e.key === 'ArrowDown') {
                window.selectedIndex = (window.selectedIndex + 1) % cards.length;
            } else {
                window.selectedIndex = (window.selectedIndex - 1 + cards.length) % cards.length;
            }
            actualizarSeleccionVisual();
            return;
        }

        // TAB para navegar (similar a flecha abajo)
        if (e.key === 'Tab') {
            const cards = document.querySelectorAll('.pos-product-card');
            if (cards.length > 0) {
                e.preventDefault();
                window.selectedIndex = (window.selectedIndex + 1) % cards.length;
                actualizarSeleccionVisual();
            }
        }
    });

    function actualizarSeleccionVisual() {
        const cards = document.querySelectorAll('.pos-product-card');
        cards.forEach((card, index) => {
            if (index === window.selectedIndex) {
                card.classList.add('selected');
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                card.classList.remove('selected');
            }
        });
    }

    // Atajos para cantidad (+ / -) y otros
    document.addEventListener('keydown', function(e) {
        // Evitar si estamos escribiendo en cualquier input o si hay un modal abierto
        const activeTag = document.activeElement.tagName;
        const isInput = activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT';

        // Si hay modales abiertos, no procesar atajos globales
        const openModal = document.querySelector('.modal.show');
        if (openModal) return;

        // Atajos de cantidad + y - para el item seleccionado del ticket (o el último)
        if (!isInput) {
            if (e.key === '+' || e.key === '=' || e.code === 'NumpadAdd') {
                e.preventDefault();
                modificarCantidadSeleccionada(1);
            } else if (e.key === '-' || e.key === '_' || e.code === 'NumpadSubtract') {
                e.preventDefault();
                modificarCantidadSeleccionada(-1);
            }
        }
    });

    function modificarCantidadSeleccionada(delta) {
        if (window.ticket.length === 0) return;
        // Usar fila seleccionada del ticket; si no hay, usar la última
        const index = (window.selectedTicketIndex >= 0 && window.selectedTicketIndex < window.ticket.length)
            ? window.selectedTicketIndex
            : window.ticket.length - 1;
        const item = window.ticket[index];
        const nuevaCantidad = Math.max(0, item.cantidad + delta);
        actualizarCantidad(index, nuevaCantidad);
    }

    document.getElementById('main-search-input').addEventListener('input', function () {
        let q = this.value;
        window.selectedIndex = -1; // Resetear selección al escribir
        if (q.length < 2) {
            document.getElementById('product-results-grid').innerHTML = `
                <div style="text-align: center; color: #888; margin-top: 50px;">
                    <i class='bx bx-package' style="font-size: 40px; opacity: 0.3;"></i>
                    <p>Busque un producto para comenzar</p>
                </div>`;
            return;
        }
        buscarProductosDebounced(q);
    });

    function renderProductosNuevos(productos) {
        const grid = document.getElementById('product-results-grid');
        grid.innerHTML = '';

        if (productos.length === 0) {
            grid.innerHTML = '<div style="text-align: center; padding: 20px; color: #888;">No se encontraron productos</div>';
            return;
        }

        productos.forEach(p => {
            const card = document.createElement('div');
            card.className = 'pos-product-card';

            const pvp = parseFloat(p.pvp || 0).toFixed(2);
            const pvd = parseFloat(p.pvpd || 0).toFixed(2);
            const pvc = parseFloat(p.pvc || 0).toFixed(2);
            const pdoc = parseFloat(p.pv_docena || 0).toFixed(2);
            const stock = parseFloat(p.cantidad_total || 0).toFixed(2);
            const marca = p.marca || '-';
            const fVenc = p.fecha_vencimiento ? p.fecha_vencimiento.split(' ')[0].split('-').reverse().join('/') : '-';

            const imgPathRaw = p.imagen_principal || null;

            card.innerHTML = `
                <div class="pos-product-main" style="margin-bottom: 5px;">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="pos-product-title text-truncate" style="font-size: 13px; line-height: 1.2; font-weight: 600;" title="${p.nombre}">
                            ${p.nombre}
                        </div>
                        ${imgPathRaw ? `
                            <div class="image-preview-wrapper" 
                                onmouseover="showImagePreview(event, '${imgPathRaw}')" 
                                onmouseout="hideImagePreview()"
                                style="cursor: help;">
                                <i class='bx bx-image text-primary' style="font-size: 20px;"></i>
                            </div>
                        ` : ''}
                    </div>
                    <div style="font-size: 10px; color: #666; display: flex; flex-wrap: wrap; gap: 4px; margin-top: 2px;">
                        <span class="text-truncate" style="max-width: 150px;">M: <strong>${marca}</strong></span>
                        <span>Stk: <strong style="color: ${stock <= 5 ? '#ef4444' : '#22c55e'}">${stock}</strong></span>
                        <span>V: <strong>${fVenc}</strong></span>
                    </div>
                </div>
                <div class="pos-product-prices" style="border-top: 1px dashed #eee; padding-top: 3px; font-size: 11px;">
                    <div class="d-flex justify-content-between align-items-center" style="gap: 5px; flex-wrap: wrap;">
                        <span>Púb: <strong class="text-dark">S/ ${pvp}</strong></span>
                        <span>Dct: <strong class="text-warning">S/ ${pvd}</strong></span>
                        <span>Corp: <strong class="text-primary">S/ ${pvc}</strong></span>
                        <span>Doc: <strong class="text-info">S/ ${pdoc}</strong></span>
                    </div>
                </div>
            `;

            // No es necesario realizar acciones aquí ya que usamos onmouseover/onmouseout arriba

            // Un solo clic: No hace nada (actualiza el producto actual para el menú)
            card.onclick = (e) => {
                currentProduct = p;
                // No abrir modal ni redirigir
            };

            // Doble clic: Pasar directo al ticket
            card.ondblclick = (event) => {
                event.preventDefault();
                event.stopPropagation();
                currentProduct = p;
                
                // Cerrar cualquier modal abierto por si acaso
                cerrarModalCantidad();
                
                const prodAdd = {
                    producto_id: p.producto_id,
                    id: p.id,
                    nombre: p.nombre,
                    marca: p.marca || '',
                    precio: parseFloat(p.pvp || 0),
                    pvp: parseFloat(p.pvp || 0),
                    pvc: parseFloat(p.pvc || 0),
                    pv_docena: parseFloat(p.pv_docena || 0),
                    cantidad: 1,
                    descuento: 0,
                    importe: parseFloat(p.pvp || 0),
                    cantidad_disponible: parseFloat(p.cantidad_total || 0),
                    tipo_impuesto: p.tipo_impuesto,
                    imagen_principal: p.imagen_principal || '',
                    almacen_detalle_id: p.id // Usar el ID de lote directo del resultado
                };
                agregarProductoAlTicket(prodAdd);
            };

            // Anticlic (Click derecho): Abrir menú de opciones
            card.oncontextmenu = (event) => {
                event.preventDefault();
                currentProduct = p;
                mostrarMenuLotes(event, p);
            };

            grid.appendChild(card);
        });
    }

    function cambiarTipoPago(tipo) {
        document.querySelectorAll('.payment-tab').forEach(btn => {
            btn.classList.remove('active');
            let text = btn.innerText.toLowerCase()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, ""); // Quitar acentos
            if (text === tipo) {
                btn.classList.add('active');
            }
        });

        document.getElementById('tipo-pago-hidden').value = tipo;

        // Si es crédito o proforma, el entrega suele ser 0
        if (tipo === 'credito' || tipo === 'proforma') {
            const amountInput = document.getElementById('input-entrega');
            if (amountInput) {
                amountInput.value = (0).toFixed(2);
                window.pagaConManual = true; // Evitar que el auto-sync lo vuelva a subir
            }
        }

        // Manejar checkbox de proforma y visibilidad de sección documento
        const proformaCheck = document.getElementById('proforma-checkbox');
        const documentoSection = document.getElementById('pos-documento-section');
        const plazoSection = document.getElementById('plazo-credito-section');
        
        if (tipo === 'proforma') {
            if (proformaCheck) proformaCheck.checked = true;
            // if (documentoSection) documentoSection.style.display = 'none';
        } else {
            if (proformaCheck) proformaCheck.checked = false;
            // if (documentoSection) documentoSection.style.display = 'block';
        }

        if (tipo === 'credito') {
            if (plazoSection) plazoSection.style.display = 'block';
        } else {
            if (plazoSection) plazoSection.style.display = 'none';
        }

        // Si es contado, volver a habilitar el sincronismo automático del total
        if (tipo === 'contado') {
            window.pagaConManual = false;
            // No necesitamos llamar a actualizarFooter manualmente porque renderTicket se llama a menudo
            // Pero para respuesta inmediata, podemos forzar el sincronismo aquí
            const totalVal = ticket.reduce((sum, item) => sum + (item.importe || 0), 0);
            const amountInput = document.getElementById('input-entrega');
            if (amountInput) {
                amountInput.value = totalVal.toFixed(2);
            }
        }

        calcularCambioPOS();
    }

    // MÓDULO PAGOS MIXTOS
    document.getElementById('medio-pago-select').addEventListener('change', function() {
        const isMixto = window.TIPO_PAGO_MIXTO_ID && this.value == window.TIPO_PAGO_MIXTO_ID;
        document.getElementById('seccion-pago-mixto').style.display = isMixto ? 'block' : 'none';
        
        if (isMixto) {
            const total = ticket.reduce((sum, item) => sum + (item.importe || 0), 0);
            document.getElementById('mixto-efectivo').value = total.toFixed(2);
            document.getElementById('mixto-digital').value = "0.00";
            actualizarTotalesMixtos();
        }
    });

    function actualizarTotalesMixtos() {
        const efectivo = parseFloat(document.getElementById('mixto-efectivo').value) || 0;
        const digital = parseFloat(document.getElementById('mixto-digital').value) || 0;
        const totalMixto = efectivo + digital;
        
        document.getElementById('mixto-total-indicador').innerText = 'S/ ' + totalMixto.toFixed(2);
        
        // Sincronizar con el input de entrega principal
        document.getElementById('input-entrega').value = totalMixto.toFixed(2);
        calcularCambioPOS();
    }

    function calcularCambioPOS() {
        const total = ticket.reduce((sum, item) => sum + (item.importe || 0), 0);
        const amountPaid = parseFloat(document.getElementById('input-entrega').value) || 0;
        const change = amountPaid - total;

        const changeEl = document.getElementById('label-cambio');
        const labelEl = document.getElementById('label-tipo-cambio');
        const boxEl = document.getElementById('pago-status-box');

        if (changeEl && labelEl && boxEl) {
            // Usamos un pequeño margen de error para evitar problemas de precisión decimal
            if (change < -0.001) {
                // Hay saldo pendiente (Crédito/Parcial)
                labelEl.innerText = 'Saldo Pendiente';
                labelEl.style.color = '#dc3545';
                changeEl.innerText = Math.abs(change).toFixed(2);
                boxEl.style.borderColor = '#dc3545';
                boxEl.style.color = '#dc3545';
            } else {
                // Pago completo o vuelto
                labelEl.innerText = 'Cambio';
                labelEl.style.color = 'inherit';
                changeEl.innerText = change.toFixed(2);
                boxEl.style.borderColor = '#28a745';
                boxEl.style.color = '#28a745';
            }
        }
    }

    async function cambiarTipoDocumento() {
        const tipoSelect = document.getElementById('tipo-documento-select');
        if (!tipoSelect) return;
        
        const tipo = tipoSelect.value;
        const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
        const serieFromData = selectedOption ? selectedOption.getAttribute('data-serie') : '';
        
        const grid = document.getElementById('serie-numero-grid');
        if (tipo) {
            if (grid) grid.style.display = 'grid';
        } else {
            if (grid) grid.style.display = 'none';
            return;
        }

        const tipoMap = { 'boleta': 'boleta', 'factura': 'factura', 'ticket': 'ticket' };
        const tipoBackend = tipoMap[tipo] || 'boleta';
        const serieDefecto = { 'boleta': 'B001', 'factura': 'F001', 'ticket': 'NV01' };
        const finalSerie = serieFromData || serieDefecto[tipo] || 'B001';

        try {
            const res = await fetch('{{ route('pos.obtener-siguiente-numero') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ serie: finalSerie, tipo_documento: tipoBackend })
            });
            const data = await res.json();
            document.getElementById('input-serie').value = data.serie || finalSerie;
            document.getElementById('input-numero').value = data.numero;
        } catch (e) { console.error(e); }
    }

    async function ejecutarConfirmarVenta() {
        if (isProcessingEmission) return;
        if (ticket.length === 0) {
            Swal.fire('Atención', 'El ticket está vacío', 'warning');
            return;
        }

        isProcessingEmission = true;

        try {
            // VALIDACIÓN: Caja abierta
            const rcVal = await fetch('{{ route('cierre-caja.caja.open') }}');
            const dcVal = await rcVal.json();
            if (!dcVal.open) {
                Swal.fire('Caja Cerrada', 'Abra caja antes de vender', 'error');
                isProcessingEmission = false;
                return;
            }

            const tipoPagoString = document.getElementById('tipo-pago-hidden').value;

            // La validación de cliente para crédito se movió después del cálculo de totales para cubrir pagos parciales.

            // VALIDACIÓN: Documento seleccionado (Solo si NO es proforma)
            const tipoDoc = document.getElementById('tipo-documento-select').value;
            if (tipoPagoString !== 'proforma') {
                if (!tipoDoc || tipoDoc === "" || tipoDoc === "S") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tipo de Documento Requerido',
                        text: 'Por favor, seleccione un tipo de documento antes de confirmar la venta.'
                    });
                    isProcessingEmission = false;
                    return;
                }
            }

            const total = ticket.reduce((sum, item) => sum + (item.importe || 0), 0);
            const amountPaid = parseFloat(document.getElementById('input-entrega').value) || 0;

            // VALIDACIÓN: Crédito o Pago Parcial requieren un cliente real (No genérico)
            // Usamos un margen de 0.01 para evitar errores por precisión de punto flotante
            if ((tipoPagoString === 'credito' || (total - amountPaid) > 0.009) && tipoPagoString !== 'proforma') {
                const esClienteGenerico = !clienteActual || !clienteActual.id || 
                    clienteActual.id == 999999 || 
                    (clienteActual.nombre && (
                        clienteActual.nombre.toUpperCase().includes('CONTABLE') || 
                        clienteActual.nombre.toUpperCase().includes('PARTICULAR') ||
                        clienteActual.nombre.toUpperCase().includes('GENERAL')
                    ));

                if (esClienteGenerico) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Identificación de Cliente Requerida',
                        text: 'Las ventas con saldo pendiente requieren identificar a un cliente real. Por favor, seleccione o registre un cliente para continuar.',
                        confirmButtonColor: '#6b2e51'
                    });
                    isProcessingEmission = false;
                    if (typeof cerrarModalTipoDocumento === 'function') cerrarModalTipoDocumento();
                    return;
                }
            }
            const change = Math.max(0, amountPaid - total);
            const tipoPagoId = document.getElementById('medio-pago-select').value;
            const serie = document.getElementById('input-serie').value;
            const numero = document.getElementById('input-numero').value;
            const observaciones = document.getElementById('input-observaciones').value;

            const tipoDocBackend = tipoDoc === 'factura' ? 'factura' : (tipoDoc === 'boleta' ? 'boleta' : 'ticket');

            if (tipoDoc === 'factura') {
                const doc = clienteActual?.numero_documento || clienteActual?.documento || '';
                if (doc.length !== 11) {
                    Swal.fire('Atención', 'Para Factura se requiere RUC de 11 dígitos', 'warning');
                    isProcessingEmission = false;
                    return;
                }
            }

            Swal.fire({ title: 'Procesando Venta...', didOpen: () => Swal.showLoading() });

            const datos = {
                ticket: JSON.stringify(ticket),
                cliente: JSON.stringify(clienteActual),
                tipo_documento: tipoDocBackend,
                tipo_pago_id: tipoPagoId,
                metodo_pago: tipoPagoString,
                total: total.toFixed(2),
                entrega: amountPaid.toFixed(2),
                cambio: change.toFixed(2),
                serie: serie,
                numero: numero,
                observaciones: observaciones,
                proforma: tipoPagoString === 'proforma' ? 1 : 0,
                plazo_dias: tipoPagoString === 'credito' ? (document.getElementById('input-plazo-dias').value || 30) : null,
                vendedor_id: document.getElementById('vendedor-select').value,
                pago_mixto: (window.TIPO_PAGO_MIXTO_ID && tipoPagoId == window.TIPO_PAGO_MIXTO_ID) ? {
                    efectivo: document.getElementById('mixto-efectivo').value,
                    digital: document.getElementById('mixto-digital').value,
                    tipo_pago_digital_id: document.getElementById('mixto-metodo-digital').value
                } : null,
                _token: '{{ csrf_token() }}'
            };

            const isProforma = tipoPagoString === 'proforma';
            const saveUrl = isProforma ? '{{ route("cotizaciones.save-cotizacion") }}' : '{{ route("pos.save-venta") }}';

            // Guardar marca de proforma global para manejo de impresión
            window.currentIsProforma = isProforma;

            const res = await fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(datos)
            });
            const data = await res.json();

            if (data.success) {
                const vid = data.data.venta_id;
                // Sincronizar con variables globales para compatibilidad con otros scripts
                window.currentVentaId = vid;
                window.currentVentaData = data.data;

                // Abrir directamente el modal de formatos de impresión
                Swal.close();
                if (typeof abrirModalFormatosVenta === 'function') {
                    abrirModalFormatosVenta(vid, data.data.total);
                } else {
                    limpiarVentaCompletada();
                    window.location.reload();
                }
                isProcessingEmission = false;
            } else {
                Swal.fire('Error', data.message, 'error');
                isProcessingEmission = false;
            }
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            isProcessingEmission = false;
        }
    }

    function abrirModalTipoDocumento() {
        if (ticket.length === 0) {
            Swal.fire('Atención', 'El ticket está vacío', 'warning');
            return;
        }

        const total = ticket.reduce((sum, item) => sum + (item.importe || 0), 0);
        const amountPaid = parseFloat(document.getElementById('input-entrega').value) || 0;
        const tipoPagoString = document.getElementById('tipo-pago-hidden').value;

        // VALIDACIÓN: Crédito o Pago Parcial requieren un cliente real (No genérico)
        if ((tipoPagoString === 'credito' || amountPaid < total) && tipoPagoString !== 'proforma') {
            const esClienteGenerico = !window.clienteActual || !window.clienteActual.id || 
                window.clienteActual.id == 999999 || 
                (window.clienteActual.nombre && (
                    window.clienteActual.nombre.toUpperCase().includes('CONTABLE') || 
                    window.clienteActual.nombre.toUpperCase().includes('PARTICULAR') ||
                    window.clienteActual.nombre.toUpperCase().includes('GENERAL')
                ));

            // También verificar por documento si es 00000000 o similar
            const doc = String(window.clienteActual?.numero_documento || window.clienteActual?.documento || '');
            const esDocGenerico = doc === '00000000' || doc === '0' || doc === '';

            if (esClienteGenerico || esDocGenerico) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Identificación de Cliente Requerida',
                    text: 'Las ventas con saldo pendiente requieren identificar a un cliente real. Por favor, seleccione o registre un cliente para continuar.',
                    confirmButtonColor: '#6b2e51'
                });
                return;
            }
        }

        // VALIDACIÓN: Caja abierta
        fetch('{{ route('cierre-caja.caja.open') }}')
            .then(res => res.json())
            .then(data => {
                if (!data.open) {
                    Swal.fire('Caja Cerrada', 'Abra caja antes de vender', 'error');
                    return;
                }
                
                // Mostrar modal
                document.getElementById('modal-tipo-documento').style.display = 'flex';
                
                // No enfocar el input de entrega automáticamente para evitar conflictos con los atajos numéricos [1, 2, 3...]
                // const inputPago = document.getElementById('input-entrega');
                // if (inputPago) { inputPago.focus(); inputPago.select(); }
            })
            .catch(e => {
                console.error(e);
                // Si falla el fetch de caja, al menos mostrar el modal para no bloquear
                document.getElementById('modal-tipo-documento').style.display = 'flex';
            });
    }

    // Función para cerrar la modal de tipo de documento
    function cerrarModalTipoDocumento() {
        document.getElementById('modal-tipo-documento').style.display = 'none';
        
        // Resetear selección en el modal
        if (typeof documentSelectedVal !== 'undefined') {
            documentSelectedVal = null;
            documentSelectedName = '';
            document.querySelectorAll('.tipo-documento-option').forEach(opt => {
                opt.classList.remove('active');
                opt.style.borderColor = '#e5e7eb';
                opt.style.background = '#ffffff';
                const check = opt.querySelector('.check-icon');
                if(check) check.style.display = 'none';
            });
        }
    }

    let isProcessingEmission = false;

    // Función para seleccionar el tipo de documento y proceder a emitir
    async function seleccionarTipoDocumento(tipo) {
        if (isProcessingEmission) return;

        cerrarModalTipoDocumento();

        const tipoSelect = document.getElementById('tipo-documento-select');
        if (tipoSelect) {
            tipoSelect.value = tipo;
        }

        // Obtener el siguiente número correlativo antes de confirmar
        await cambiarTipoDocumento();

        // Ejecutar la confirmación de venta real (AJAX)
        await ejecutarConfirmarVenta();
    }

    // La función emitirVentaConTipo ha sido reemplazada por ejecutarConfirmarVenta
    // para mantener la fluidez de la aplicación sin recargas innecesarias.

    // Función para mostrar el menú contextual
    function mostrarMenuLotes(event, producto) {
        event.preventDefault();
        event.stopPropagation();

        currentProduct = producto;
        const contextMenu = document.getElementById('context-menu');

        // Mostrar temporalmente para obtener dimensiones reales
        contextMenu.style.visibility = 'hidden';
        contextMenu.style.display = 'block';

        const menuHeight = contextMenu.offsetHeight;
        const menuWidth = contextMenu.offsetWidth;

        // Posicionar el menú
        let x = event.pageX;
        let y = event.pageY;

        // Ajustar posición horizontal para que no se salga de la ventana
        if (x + menuWidth > window.innerWidth) {
            x = window.innerWidth - menuWidth - 10;
        }

        // Ajustar posición vertical - si no hay espacio abajo, mostrar arriba del clic
        if (y + menuHeight > window.innerHeight + window.scrollY) {
            y = event.pageY - menuHeight;
            // Si tampoco hay espacio arriba, fijar al borde superior con scroll
            if (y < window.scrollY) {
                y = window.scrollY + 10;
            }
        }

        contextMenu.style.left = x + 'px';
        contextMenu.style.top = y + 'px';
        contextMenu.style.visibility = 'visible';
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
            id: `corp_${currentProduct.producto_id}_${Date.now()}`,
            producto_id: currentProduct.producto_id,
            producto_linea_id: currentProduct.product_linea_id || currentProduct.producto_linea_id || null,
            nombre: currentProduct.nombre + ' (Precio Corp.)',
            marca: currentProduct.marca || '',
            cantidad: cantidad,
            cantidad_disponible: currentProduct.cantidad_total || 999,
            precio: parseFloat(currentProduct.pvc),
            importe: cantidad * parseFloat(currentProduct.pvc),
            pvp: currentProduct.pvp,
            pvc: currentProduct.pvc,
            pv_docena: currentProduct.pv_docena || 0,
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
            marca: currentProduct.marca || '',
            cantidad: cantidad,
            cantidad_disponible: currentProduct.cantidad_total || 0,
            precio: parseFloat(currentProduct.pvp),
            importe: cantidad * parseFloat(currentProduct.pvp),
            pvp: currentProduct.pvp,
            pvc: currentProduct.pvc,
            pv_docena: currentProduct.pv_docena || 0,
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

        // Parsear ficha técnica
        let fichaTecnicaData = {};
        try {
            let rawData = currentProduct.ficha_tecnica;

            if (rawData) {
                if (typeof rawData === 'string') {
                    // Intentar parsear JSON
                    try {
                        let parsed = JSON.parse(rawData);
                        // Fix: Si está doblemente codificado (sigue siendo string después de parsear)
                        if (typeof parsed === 'string') {
                            parsed = JSON.parse(parsed);
                        }
                        fichaTecnicaData = parsed || {};
                    } catch (e) {
                        console.warn('No es un JSON válido, tal vez es texto plano', e);
                        // Si falla, quizás no es JSON.
                    }
                } else if (typeof rawData === 'object') {
                    fichaTecnicaData = rawData;
                }
            }
        } catch (e) {
            console.warn('Error general parsing ficha_tecnica:', e);
        }

        // Construir HTML extra de ficha técnica si existe
        let extraInfoHtml = '';
        if (fichaTecnicaData && Object.keys(fichaTecnicaData).length > 0) {
            extraInfoHtml +=
                '<div style="margin-top: 15px; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6;">';
            extraInfoHtml +=
                '<h5 style="margin: 0 0 10px 0; color: #495057; font-size: 14px;">Detalles Médicos</h5>';
            extraInfoHtml += '<ul style="padding-left: 20px; margin: 0; font-size: 13px;">';

            const fieldsMap = {
                'composicion': 'Composición',
                'mecanismo_accion': 'Mecanismo de Acción',
                'indicaciones': 'Indicaciones',
                'contraindicaciones': 'Contraindicaciones',
                'dosificacion': 'Dosificación',
                'efectos_secundarios': 'Efectos Secundarios',
                'interacciones': 'Interacciones',
                'sobredosis': 'Sobredosis'
            };

            // Mostrar campos específicos en orden
            for (const [key, label] of Object.entries(fieldsMap)) {
                if (fichaTecnicaData[key]) {
                    extraInfoHtml += `<li><strong>${label}:</strong> ${fichaTecnicaData[key]}</li>`;
                }
            }

            // Mostrar otros campos que no estén en la lista
            for (const [key, value] of Object.entries(fichaTecnicaData)) {
                if (!fieldsMap[key]) {
                    // Capitalize key for cleaner display if needed, or just show as is
                    const cleanKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    extraInfoHtml += `<li><strong>${cleanKey}:</strong> ${value}</li>`;
                }
            }

            extraInfoHtml += '</ul></div>';
        }

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
                                                        ${extraInfoHtml}
                                                    </div>

                                                    <div>
                                                        <label style="font-weight: 600; color: #495057; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; display: block;">Código del Producto</label>
                                                        <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; font-family: monospace; font-size: 14px;">
                                                            ${currentProduct.codigo_barras || currentProduct.producto_id || 'N/A'}
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
                                                            ${currentProduct.unidad_medida || 'NIU (Unidades)'}
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
            // Limpiar datos en memoria para evitar que se re-guarden en onbeforeunload
            if (typeof ticket !== 'undefined') ticket = [];
            if (typeof clienteActual !== 'undefined') {
                clienteActual = { id: '', nombre: 'CLIENTE VARIOS', documento: '' };
            }

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
        // Reutilizamos el listado de lotes como ficha de existencias por ahora
        mostrarListadoLotes();
    }

    function mostrarUbicacionProducto() {
        if (!currentProduct) return;

        // Parsear almacenamiento si es JSON
        let ubicacionData = null;
        try {
            if (currentProduct.almacenamiento && typeof currentProduct.almacenamiento === 'string') {
                ubicacionData = JSON.parse(currentProduct.almacenamiento);
            } else if (typeof currentProduct.almacenamiento === 'object') {
                ubicacionData = currentProduct.almacenamiento;
            }
        } catch (e) {
            console.warn('Error parsing almacenamiento:', e);
        }

        // Si no hay datos parseados, usar el string raw o mensaje default
        let contenidoUbicacion = '';
        if (ubicacionData) {
            // Si es objeto, intentar mostrar campos comunes
            if (Object.keys(ubicacionData).length > 0) {
                contenidoUbicacion = '<ul style="text-align: left;">';
                for (const [key, value] of Object.entries(ubicacionData)) {
                    contenidoUbicacion += `<li><strong>${key}:</strong> ${value}</li>`;
                }
                contenidoUbicacion += '</ul>';
            } else {
                contenidoUbicacion = '<p>Sin información detallada de ubicación.</p>';
            }
        } else {
            contenidoUbicacion =
                `<p>${currentProduct.almacenamiento || 'No se ha registrado ubicación para este producto.'}</p>`;
        }

        const modalHtml = `
                                <div id="modal-ubicacion" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; justify-content: center; align-items: center;" onclick="document.getElementById('modal-ubicacion').remove()">
                                    <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); min-width: 300px; max-width: 500px; text-align: center;" onclick="event.stopPropagation()">
                                        <h3 style="color: #dc3545; margin-top: 0;">📍 Ubicación del Producto</h3>
                                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border: 1px solid #dee2e6; margin: 15px 0;">
                                            ${contenidoUbicacion}
                                        </div>
                                        <button onclick="document.getElementById('modal-ubicacion').remove()" style="padding: 8px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cerrar</button>
                                    </div>
                                </div>
                            `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
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
            window.selectedTicketIndex = -1;

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
            if (clienteDoc) clienteDoc.textContent = '00000000';

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

    // Se eliminó la función guardarTicket de aquí para usar la centralizada en ventas-guardadas.blade.php

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
            window.selectedTicketIndex = -1;

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
    document.addEventListener('click', function (event) {
        const contextMenu = document.getElementById('context-menu');
        if (contextMenu.style.display === 'block' && !contextMenu.contains(event.target)) {
            cerrarContextMenu();
        }
    });

    // Cerrar menú con tecla ESC
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            cerrarContextMenu();
        }
    });

    // Modifica la función renderProductos para llenar la tabla (no el grid):
    function renderProductos(productos) {
        const tbody = document.getElementById('productos-tbody');
        const resultsArea = document.querySelector('.results-area');
        tbody.innerHTML = ''; // Limpia resultados previos

        if (productos.length === 0) {
            resultsArea.classList.remove('has-results');
            return;
        }

        resultsArea.classList.add('has-results');
        productos.forEach(p => {
            const tr = document.createElement('tr');
            tr.style.cursor = 'pointer';

            // Doble clic: agregar directamente 1 unidad a precio público
            tr.addEventListener('dblclick', async (ev) => {
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
                    tipo_impuesto: p.tipo_impuesto || 'gravado',
                    producto_linea_id: p.product_linea_id || p.producto_linea_id || null,
                    nombre: p.nombre,
                    marca: p.marca || '',
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
                    imagen_principal: p.imagen_principal || ''
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

            // Formatear nombre (sin el detalle de lote, que irá separado)
            let nombreProducto = p.nombre || '';

            // El detalle contiene lote y fecha vencimiento
            let detalleInfo = p.detalle || '';

            // Indicadores visuales para múltiples lotes
            const indicadorLotes = p.total_lotes > 1 ?
                ` <span style="background: #2196f3; color: white; padding: 1px 4px; border-radius: 8px; font-size: 10px; margin-left: 5px;">${p.total_lotes} lotes</span>` :
                '';

            // Indicador de stock bajo
            const stockBajo = p.stock_bajo == 1;
            const indicadorStockBajo = stockBajo ?
                `<span style="background: #ef4444; color: white; padding: 1px 6px; border-radius: 8px; font-size: 10px; margin-left: 5px;" title="Stock mínimo: ${p.stock_min || 0}">⚠️ Stock Bajo</span>` :
                '';

            // Estilo de fila si tiene stock bajo
            if (stockBajo) {
                tr.style.background = 'linear-gradient(90deg, #fef2f2 0%, #fff 100%)';
                tr.style.borderLeft = '3px solid #ef4444';
            }

            const stockDisplay = p.cantidad_total ? `${parseFloat(p.cantidad_total).toFixed(2)} NIU${indicadorLotes}${indicadorStockBajo}` : '';

            tr.innerHTML = `
                                                <td style="position: relative; padding: 6px 8px;">
                                                    <div style="display: flex; align-items: flex-start; gap: 4px;">
                                                        <span class="photo-icon ${p.imagen_principal ? 'has-photo' : 'no-photo'}" 
                                                              onmouseover="showImagePreview(event, '${p.imagen_principal || ''}')" 
                                                              onmouseout="hideImagePreview()">
                                                            <i class="bx bx-image"></i>
                                                        </span>
                                                        <div style="font-weight: 500; color: #333; flex: 1;">
                                                            <div>${nombreProducto}</div>
                                                            ${detalleInfo ? `<div style="font-size: 11px; color: #666;">${detalleInfo}</div>` : ''}
                                                            ${p.total_lotes > 1 ? '<span style="color: #2196f3; font-size: 12px; margin-left: 5px;">🔄</span>' : ''}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="padding: 6px 8px; text-align: center; font-weight: 500; color: #555;">${p.marca || '-'}</td>
                                                <td style="padding: 6px 8px; text-align: center; font-weight: 500; ${stockBajo ? 'color: #ef4444;' : ''}">${stockDisplay}</td>
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

                // Aplicar lógica de Precio por Docena (>= 12 unidades)
                if (existente.cantidad >= 12 && (existente.pv_docena || producto.pv_docena) > 0) {
                    existente.precio = existente.pv_docena || producto.pv_docena;
                    existente.es_precio_docena = true;
                } else if (existente.es_precio_docena) {
                    // Si ya tenía precio docena pero bajó de 12, restaurar pvp
                    existente.precio = existente.pvp || producto.pvp || existente.precio;
                    existente.es_precio_docena = false;
                }

                // Recalcular importe considerando descuento previo si existe (REVERTIDO A TOTAL)
                const subtotalSinDescuento = existente.cantidad * existente.precio;
                if (existente.descuentoFijo > 0) {
                    existente.importe = subtotalSinDescuento - existente.descuentoFijo;
                } else {
                    const montoDescuento = subtotalSinDescuento * (existente.descuento || 0) / 100;
                    existente.importe = subtotalSinDescuento - montoDescuento;
                }
            } else {
                alert(`Stock insuficiente. Disponible: ${existente.cantidad_disponible} unidades`);
            }
        } else {
            // Agregar nuevo producto al ticket respetando la cantidad solicitada
            console.log("aca");
            console.log(producto);
            const nuevoProducto = {
                ...producto,
                cantidad: qtyToAdd,
                descuento: producto.descuento || 0,
                descuentoFijo: producto.descuentoFijo || 0,
                descuentoTexto: producto.descuentoTexto || '0%',
                es_precio_corporativo: producto.es_precio_corporativo || false,
                es_precio_publico: producto.es_precio_publico || false
            };

            // Aplicar lógica de Precio por Docena al agregar nuevo
            if (nuevoProducto.cantidad >= 12 && nuevoProducto.pv_docena > 0) {
                nuevoProducto.precio = nuevoProducto.pv_docena;
                nuevoProducto.es_precio_docena = true;
            }

            nuevoProducto.importe = nuevoProducto.importe || (qtyToAdd * (nuevoProducto.precio || parseFloat(nuevoProducto.pvp || 0)));

            ticket.push(nuevoProducto);
        }

        renderTicket();
        // Auto-guardar después de agregar producto
        guardarVentaPersistente();

        // Focus al buscador de Nombre/Marca después de agregar producto
        setTimeout(() => {
            const searchInput = document.getElementById('main-search-input');
            if (searchInput) {
                searchInput.focus();
                searchInput.value = ''; // Opcional: limpiar búsqueda anterior
            }
        }, 150);

    }

    function renderTicket() {
        const tbody = document.getElementById('ticket-tbody');
        tbody.innerHTML = '';
        const canModificarPrecio = {{ $canModificarPrecio }};

        let total = 0;

        ticket.forEach((p, idx) => {
            total += p.importe;

            // Color de fondo diferente para lotes específicos y precio corporativo
            let bgColor;
            if (p.es_precio_docena) {
                bgColor = idx % 2 === 0 ? '#e3f2fd' : '#bbdefb'; // Azul suave para precio docena
            } else if (p.es_precio_corporativo) {
                bgColor = idx % 2 === 0 ? '#e8f5e8' : '#d4edda'; // Verde claro para precio corporativo
            } else if (p.es_lote_especifico) {
                bgColor = idx % 2 === 0 ? '#fff3e0' : '#ffe0b2'; // Naranja para lotes específicos
            } else {
                bgColor = idx % 2 === 0 ? '#f8fdff' : '#fff'; // Azul claro para productos normales
            }

            // Resaltar si está seleccionada
            let borderStyle = (idx === window.selectedTicketIndex) ?
                'border: 2px solid #6b2e51; box-shadow: inset 0 0 8px rgba(107, 46, 81, 0.2);' : '';

            const tr = document.createElement('tr');
            tr.style.background = bgColor;
            if (borderStyle) tr.style.cssText += borderStyle;
            tr.style.cursor = 'pointer';
            
            // Eventos
            tr.onclick = () => editarLinea(idx);
            tr.oncontextmenu = (e) => {
                e.preventDefault();
                mostrarMenuTicket(e, p);
            };

            const nombreStr = p.nombre || 'S/N';
            const nombreDisplay = nombreStr.length > 50 ? nombreStr.substring(0, 47) + '...' : nombreStr;
            const loteInfo = p.lote ? ` <small class="text-muted" style="color:#888;">(Lt: ${p.lote})</small>` : '';

            tr.innerHTML = `
                <td>${idx + 1}</td>
                <td title="${nombreStr}${p.lote ? ' - Lote: ' + p.lote : ''}">
                    <div style="display: flex; flex-direction: column; gap: 0;">
                        <div style="font-weight: 700; font-size: 13px; color: #333; line-height: 1.2; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 320px;">
                            ${nombreDisplay}${loteInfo}
                            ${p.es_precio_docena ? '<span style="background: #2196f3; color: white; padding: 1px 4px; border-radius: 4px; font-size: 9px; margin-left: 5px; vertical-align: middle;">DOCENA</span>' : ''}
                        </div>
                        <div style="font-size: 10px; color: #64748b; font-weight: 500; margin-top: 2px;">
                            MARCA: ${p.marca || '-'}
                        </div>
                    </div>
                </td>
                <td>
                    <input type="number" value="${p.cantidad}" min="0.001" step="any" 
                           style="width: 60px; border: 1px solid #ddd; border-radius: 4px; text-align: center; padding: 2px;"
                           onchange="actualizarCantidad(${idx}, this.value)"
                           onclick="event.stopPropagation()">
                </td>
                <td>
                    <input type="text" value="${((parseFloat(p.cantidad || 0) * parseFloat(p.precio || 0)) - parseFloat(p.importe || 0)).toFixed(2)}" 
                           style="width: 70px; border: 1px solid #ddd; border-radius: 4px; text-align: center; padding: 2px; color: #d63384; font-weight: 600;"
                           onchange="actualizarDescuento(${idx}, this.value)"
                           onclick="event.stopPropagation()">
                </td>
                <td>
                    <div style="display: flex; align-items: center; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; height: 32px;" onclick="event.stopPropagation()">
                        <span style="background: #f1f5f9; padding: 0 6px; font-size: 10px; color: #64748b; font-weight: 700; height: 100%; display: flex; align-items: center; border-right: 1px solid #ddd;">S/</span>
                        <input type="number" value="${parseFloat(p.precio || 0).toFixed(2)}" step="0.01" 
                               style="width: 70px; border: none; text-align: center; padding: 2px 4px; font-weight: 700; color: #334155; height: 100%; font-size: 13px; ${!canModificarPrecio ? 'background:#f1f5f9; cursor:not-allowed;' : ''}"
                               ${canModificarPrecio ? `onchange="actualizarPrecio(${idx}, this.value)"` : 'readonly'}>
                    </div>
                </td>

                <td style="font-weight: 800; color: #22c55e;">S/ ${parseFloat(p.importe || 0).toFixed(2)}</td>
                <td style="text-align: center;">
                    <button onclick="event.stopPropagation(); eliminarLinea(${idx})" 
                            style="background: #fee2e2; border: none; color: #ef4444; border-radius: 4px; cursor: pointer; padding: 5px 8px;">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        actualizarFooter(total);

        // Auto-guardar después de renderizar (con throttle para evitar muchas llamadas)
        if (ticket.length > 0) {
            clearTimeout(window.renderTicketSaveTimeout);
            window.renderTicketSaveTimeout = setTimeout(() => {
                guardarVentaPersistente();
            }, 1000); 
        }
    }

    function eliminarLinea(index) {
        const producto = ticket[index];

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Quitar producto?',
                text: `¿Desea eliminar "${producto.nombre}" del ticket?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    ticket.splice(index, 1);
                    window.selectedIndex = -1;
                    window.selectedTicketIndex = -1;
                    renderTicket();
                    guardarVentaPersistente();
                    if (typeof mostrarNotificacion === 'function') {
                        mostrarNotificacion(`🗑️ "${producto.nombre.substring(0, 20)}..." eliminado`);
                    }
                }
            });
        } else {
            if (confirm(`¿Quitar "${producto.nombre}" del ticket?`)) {
                ticket.splice(index, 1);
                window.selectedIndex = -1;
                window.selectedTicketIndex = -1;
                renderTicket();
                guardarVentaPersistente();
            }
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

        // Aplicar lógica de Precio por Docena (>= 12 unidades)
        if (producto.cantidad >= 12 && producto.pv_docena > 0) {
            producto.precio = producto.pv_docena;
            producto.es_precio_docena = true;
        } else if (producto.es_precio_docena) {
            // Si ya tenía precio docena pero bajó de 12, restaurar pvp
            producto.precio = producto.pvp || producto.precio;
            producto.es_precio_docena = false;
        }

        // Recalcular importe considerando descuento (REVERTIDO A TOTAL)
        const subtotalSinDescuento = producto.cantidad * producto.precio;
        if (producto.descuentoFijo > 0) {
            producto.importe = subtotalSinDescuento - producto.descuentoFijo;
        } else {
            const montoDescuento = subtotalSinDescuento * (producto.descuento || 0) / 100;
            producto.importe = subtotalSinDescuento - montoDescuento;
        }
        renderTicket();
    }

    // Función para editar línea (ahora también maneja la selección)
    function editarLinea(index) {
        window.selectedIndex = index;
        window.selectedTicketIndex = index;
        // Guardar el producto seleccionado para el menú contextual si se quiere
        window.currentProduct = ticket[index];
        renderTicket();
        console.log('Línea seleccionada:', index, ticket[index]);
    }

    // Guía de Atajos
    function mostrarModalAtajos() {
        document.getElementById('modal-atajos').style.display = 'flex';
    }

    function cerrarModalAtajos() {
        document.getElementById('modal-atajos').style.display = 'none';
    }

    // Lógica de Atajos de Teclado
    document.addEventListener('keydown', function (e) {
        // No interferir si el usuario está en un input (excepto para F1 que es foco)
        const isInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA';

        // F1: Buscar Producto
        if (e.key === 'F1') {
            e.preventDefault();
            const mainSearch = document.querySelector('.main-search');
            if (mainSearch) {
                mainSearch.focus();
                mainSearch.select();
            }
        }

        // F3: Buscar Cliente
        if (e.key === 'F3') {
            e.preventDefault();
            mostrarBuscadorClientes();
        }

        // F4: Tipo de Documento
        if (e.key === 'F4') {
            e.preventDefault();
            abrirModalTipoDocumento();
        }

        // F8: Descuento Global
        if (e.key === 'F8') {
            e.preventDefault();
            aplicarDescuentoGlobal();
        }

        // F10: Guardar Ticket
        if (e.key === 'F10') {
            e.preventDefault();
            guardarTicket();
        }

        // F12 or Enter: Emitir (Finalizar)
        if (e.key === 'F12' || (e.key === 'Enter' && !isInput)) {
            e.preventDefault();
            abrirModalTipoDocumento();
        }

        // Esc: Cancelar / Limpiar
        if (e.key === 'Escape') {
            // Solo si no hay modales abiertos (o cerrar modales)
            const modalAtajos = document.getElementById('modal-atajos');
            if (modalAtajos && modalAtajos.style.display === 'flex') {
                cerrarModalAtajos();
                return;
            }

            const modalTipo = document.getElementById('modal-tipo-documento');
            if (modalTipo && modalTipo.style.display === 'flex') {
                cerrarModalTipoDocumento();
                return;
            }

            if (!isInput) {
                cancelarVentaConSweetAlert();
            }
        }

        // Supr (Delete): Eliminar Línea
        if (e.key === 'Delete') {
            if (window.selectedTicketIndex !== -1 && !isInput) {
                eliminarLinea(window.selectedTicketIndex);
            }
        }

        // Ctrl + P: Imprimir Último Comprobante
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            imprimirUltimoComprobante();
        }
    });

    function imprimirUltimoComprobante() {
        const ultimoId = localStorage.getItem('ultimoVentaId');
        if (ultimoId) {
            // Por defecto intentamos A4 o el que sea estándar
            const url = '{{ route('pos.pdf', ['id' => ':id', 'format' => 'default']) }}'.replace(':id', ultimoId);
            window.open(url, '_blank');
        } else {
            Swal.fire({
                icon: 'info',
                title: 'No hay registros',
                text: 'No se encontró el ID de la última venta en esta sesión corporativa.'
            });
        }
    }

    // Función para actualizar precio unitario directamente
    function actualizarPrecio(index, nuevoPrecio) {
        if (!window.__canModificarPrecio) { renderTicket(); return; }
        const producto = ticket[index];
        const precio = parseFloat(nuevoPrecio);
        
        if (isNaN(precio) || precio < 0) {
            alert('Ingrese un precio válido');
            renderTicket();
            return;
        }

        producto.precio = precio;
        
        // Recalcular importe respetando el descuento (si existe)
        const subtotalSinDescuento = producto.cantidad * producto.precio;
        let montoDescuento = 0;

        if (producto.descuento && producto.descuento > 0) {
            montoDescuento = subtotalSinDescuento * producto.descuento / 100;
        } else if (producto.descuentoFijo && producto.descuentoFijo > 0) {
            montoDescuento = producto.descuentoFijo;
        }

        producto.importe = Math.max(0, subtotalSinDescuento - montoDescuento);
        
        renderTicket();
        mostrarNotificacion(`Se actualizó el precio de ${producto.nombre} a S/ ${precio.toFixed(2)}`);
    }

    // Función para actualizar descuento (porcentaje o monto fijo)
    // skipValidation=true cuando ya fue validado externamente (ej: modificarDescuentoLinea)
    async function actualizarDescuento(index, valorDescuento, skipValidation = false) {
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

        // Si contiene %, se trata como porcentaje. De lo contrario, se trata como MONTO (Soles).
        if (valor.includes('%')) {
            // Descuento porcentual
            const porcentaje = parseFloat(valor.replace('%', ''));

            if (isNaN(porcentaje) || porcentaje < 0 || porcentaje > 100) {
                alert('El descuento debe estar entre 0% y 100%');
                renderTicket();
                return;
            }

            montoDescuento = subtotalSinDescuento * porcentaje / 100;

            // Validar contra el descuento máximo configurado por producto (solo vendedores)
            if (!isAdmin && !skipValidation) {
                const maxPermitido = await obtenerMaxDescuentoProducto(producto);
                if (maxPermitido !== null && montoDescuento > (maxPermitido + 0.01)) {
                    alert(`¡Error! El descuento máximo para este producto es de S/ ${maxPermitido.toFixed(2)}. No puede aplicar S/ ${montoDescuento.toFixed(2)}`);
                    renderTicket();
                    return;
                }
            }

            producto.descuento = porcentaje;
            producto.descuentoFijo = 0;
            producto.descuentoTexto = `${porcentaje}%`;
            textoDescuento = `Descuento del ${porcentaje}%`;
        } else {
            // Descuento fijo en soles (por defecto si no hay %)
            const montoFijo = parseFloat(valor.replace(/[S\/s\/\s]/g, ''));

            if (isNaN(montoFijo) || montoFijo < 0) {
                alert('Ingrese un monto válido (ej: 12.50)');
                renderTicket();
                return;
            }

            if (montoFijo >= subtotalSinDescuento) {
                alert('El descuento no puede ser mayor o igual al subtotal del producto');
                renderTicket();
                return;
            }

            // Validar contra el descuento máximo configurado por producto (solo vendedores)
            if (!isAdmin && !skipValidation) {
                const maxPermitido = await obtenerMaxDescuentoProducto(producto);
                if (maxPermitido !== null && montoFijo > (maxPermitido + 0.01)) {
                    alert(`¡Error! El descuento máximo para este producto es de S/ ${maxPermitido.toFixed(2)}. No puede aplicar S/ ${montoFijo.toFixed(2)}`);
                    renderTicket();
                    return;
                }
            }

            montoDescuento = montoFijo;
            producto.descuento = 0;
            producto.descuentoFijo = montoFijo;
            producto.descuentoTexto = `S/${montoFijo.toFixed(2)}`;
            textoDescuento = `Descuento fijo de S/ ${montoFijo.toFixed(2)}`;
        }

        // Aplicar descuento
        producto.importe = Math.max(0, subtotalSinDescuento - montoDescuento);

        renderTicket();

        // Mostrar notificación si se aplicó descuento
        if (montoDescuento > 0) {
            mostrarNotificacion(`${textoDescuento} aplicado: -S/ ${montoDescuento.toFixed(2)}`);
        }
    }

    // Función auxiliar para obtener el descuento máximo permitido de un producto
    async function obtenerMaxDescuentoProducto(producto) {
        try {
            const q = new URLSearchParams({
                producto_id: producto.producto_id || '',
                almacen_detalle_id: producto.almacen_detalle_id || '',
                cantidad: producto.cantidad || 1,
                precio: producto.precio || 0,
                tipo: producto.es_precio_corporativo ? 'corporativo' : 'publico'
            });

            const resp = await fetch(`{{ route('pos.pvpd') }}?${q.toString()}`);
            if (!resp.ok) return null;

            const data = await resp.json();
            const maxAmount = parseFloat(data.maxAmount);

            return isNaN(maxAmount) ? null : maxAmount;
        } catch (e) {
            console.error('Error al obtener descuento máximo:', e);
            return null; // En caso de error, permitir (no bloquear la venta)
        }
    }

    // Función para aplicar descuento global a todo el ticket
    async function aplicarDescuentoGlobal() {
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

        // Para vendedores, obtener los límites de descuento de cada producto antes de aplicar
        let limitesProductos = {};
        if (!isAdmin) {
            for (const producto of ticket) {
                const maxPermitido = await obtenerMaxDescuentoProducto(producto);
                const key = `${producto.producto_id}_${producto.almacen_detalle_id}`;
                limitesProductos[key] = maxPermitido;
            }
        }

        let totalDescuentoAplicado = 0;
        let productosExcedidos = [];
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
                const key = `${producto.producto_id}_${producto.almacen_detalle_id}`;
                const maxPermitido = limitesProductos[key];
                let descuentoAplicar = montoFijo;

                // Validar contra máximo permitido por producto
                if (!isAdmin && maxPermitido !== null && maxPermitido !== undefined) {
                    if (descuentoAplicar > (maxPermitido + 0.01)) {
                        descuentoAplicar = maxPermitido;
                        productosExcedidos.push(producto.nombre);
                    }
                }

                if (descuentoAplicar >= subtotalSinDescuento) {
                    const descuentoMaximo = subtotalSinDescuento - 0.01;
                    producto.descuentoFijo = descuentoMaximo;
                    producto.descuento = 0;
                    producto.descuentoTexto = `S/${descuentoMaximo.toFixed(2)}`;
                    producto.importe = 0.01;
                    totalDescuentoAplicado += descuentoMaximo;
                } else {
                    producto.descuentoFijo = descuentoAplicar;
                    producto.descuento = 0;
                    producto.descuentoTexto = `S/${descuentoAplicar.toFixed(2)}`;
                    producto.importe = subtotalSinDescuento - descuentoAplicar;
                    totalDescuentoAplicado += descuentoAplicar;
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
                let montoDescuento = subtotalSinDescuento * porcentaje / 100;
                const key = `${producto.producto_id}_${producto.almacen_detalle_id}`;
                const maxPermitido = limitesProductos[key];

                // Validar contra máximo permitido por producto
                if (!isAdmin && maxPermitido !== null && maxPermitido !== undefined) {
                    if (montoDescuento > (maxPermitido + 0.01)) {
                        montoDescuento = maxPermitido;
                        productosExcedidos.push(producto.nombre);
                    }
                }

                const porcentajeReal = (montoDescuento / subtotalSinDescuento) * 100;
                producto.descuento = porcentajeReal;
                producto.descuentoFijo = 0;
                producto.descuentoTexto = `${porcentajeReal.toFixed(2)}%`;

                const prevImporte = subtotalSinDescuento;
                producto.importe = Math.max(0, subtotalSinDescuento - montoDescuento);
                totalDescuentoAplicado += (prevImporte - producto.importe);
            });

            tipoDescuento = `Descuento del ${porcentaje}%`;
        }

        if (productosExcedidos.length > 0) {
            mostrarNotificacion(`Descuento ajustado al máximo permitido en: ${productosExcedidos.join(', ')}`);
        }

        mostrarNotificacion(
            `${tipoDescuento} aplicado a ${ticket.length} productos. Total descontado: S/ ${totalDescuentoAplicado.toFixed(2)}`
        );
        renderTicket();
    }

    function actualizarFooter(total) {
        let gravada = 0;
        let exonerada = 0;
        let igv = 0;
        let icbper = 0.00;

        ticket.forEach(item => {
            if (item.tipo_impuesto === 'exonerado') {
                exonerada += item.importe;
            } else {
                let mGravada = item.importe / 1.18;
                gravada += mGravada;
                igv += (item.importe - mGravada);
            }
        });

        // Calcular descuentos totales
        let totalDescuentos = 0;
        ticket.forEach(item => {
            if (item.descuentoFijo && item.descuentoFijo > 0) {
                totalDescuentos += item.descuentoFijo;
            } else if (item.descuento && item.descuento > 0) {
                const subtotalSinDescuento = item.cantidad * item.precio;
                const montoDescuento = subtotalSinDescuento * item.descuento / 100;
                totalDescuentos += montoDescuento;
            }
        });

        let cantListado = ticket.length;

        // Helper seguro para no fallar si el elemento no existe aún
        function setEl(id, value, prefix = '') {
            const el = document.getElementById(id);
            if (el) el.innerText = prefix + value;
        }

        // Actualizar UI del footer central
        setEl('pos-footer-gravada', gravada.toFixed(2), 'S/ ');
        setEl('pos-footer-igv', igv.toFixed(2), 'S/ ');
        setEl('pos-footer-exonerada', exonerada.toFixed(2), 'S/ ');
        setEl('pos-footer-discount', totalDescuentos.toFixed(2), 'S/ ');
        setEl('pos-footer-total', total.toFixed(2), 'S/ ');

        // Actualizar UI del resumen derecho
        setEl('pos-summary-total', total.toFixed(2), 'S/ ');

        // Actualizar inputs de pago si no han sido editados manualmente
        const amountPaidInput = document.getElementById('input-entrega');
        if (amountPaidInput) {
            // Attach event listener once to track manual edits
            if (!amountPaidInput.dataset.listenerAttached) {
                amountPaidInput.addEventListener('input', () => {
                    window.pagaConManual = true;
                });
                amountPaidInput.dataset.listenerAttached = 'true';
            }

            if (!window.pagaConManual) {
                amountPaidInput.value = total.toFixed(2);
            }
            // Always call calcularCambioPOS to update change, regardless of manual edit
            if (typeof calcularCambioPOS === 'function') {
                calcularCambioPOS();
            }
        }

        // Compatibilidad con IDs antiguos por si acaso
        setEl('footer-gravada', gravada.toFixed(2));
        setEl('footer-igv', igv.toFixed(2));
        setEl('footer-icbper', icbper.toFixed(2));
        setEl('footer-dscto', totalDescuentos.toFixed(2));
        setEl('footer-exonerada', exonerada.toFixed(2));
        setEl('footer-total', total.toFixed(2));

        // Actualizar badge móvil
        if (typeof actualizarCountMobile === 'function') {
            actualizarCountMobile();
        }
    }


</script>
@include('pos.partials.modals.context-menu-ticket')
@include('pos.partials.modals.modal-formatos-proforma')
@include('pos.partials.modals.modal-formatos-venta')
@include('pos.partials.modals.modal-buscar-clientes')
@include('pos.partials.modals.modal-nuevo-cliente')
@include('pos.partials.modals.modal-tipo-documento')
@include('pos.partials.js.finalizar-venta')
@include('pos.partials.js.sucursal-toggle')
@include('pos.partials.js.cantidad-venta')
@include('pos.partials.js.cliente-venta')
@include('pos.partials.js.persistencia-venta')

@include('pos.partials.modals.modal-ventas-guardadas')
@include('pos.partials.js.ventas-guardadas')

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}"
            });
        });
    </script>
@endif

@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: "{{ session('success') }}"
            });
        });
    </script>
@endif
@endsection
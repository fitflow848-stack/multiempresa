<div id="context-menu-ticket"
    style="position: absolute; background: white; border: 1px solid #ccc; box-shadow: 2px 2px 10px rgba(0,0,0,0.2); display: none; z-index: 1100; min-width: 240px; border-radius: 4px; font-family: Arial, sans-serif; max-height: 70vh; overflow-y: auto;">

    <div class="context-menu-item"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;"
        onclick="abrirModalCantidad(true)">
        <span style="color: #4CAF50;">🔢</span>
        <span>Unidades</span>
        <span style="margin-left: auto;">▶</span>
    </div>

    <div class="context-menu-item" onclick="modificarPrecioLinea()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #2196F3;">💲</span>
        <span>Modificar precio</span>
    </div>

    <div class="context-menu-item" onclick="modificarDescuentoLinea()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #ff9800;">%-</span>
        <span>Modificar descuento</span>
    </div>

    <div class="context-menu-item" onclick="modificarImporteLinea()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #6f42c1;">✏️</span>
        <span>Modificar importe</span>
    </div>

    <div class="context-menu-item" onclick="descuentoGlobalPrompt()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #dc3545;">🧾</span>
        <span>Descuento Global</span>
    </div>

    <div class="context-menu-item" onclick="otrosComision()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #17a2b8;">💳</span>
        <span>Otros Comision (Tarjeta Credito)</span>
    </div>

    <div class="context-menu-item"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;"
        onclick="toggleImpuestosSubmenu()">
        <span style="color: #343a40;">⚖️</span>
        <span>Impuestos</span>
        <span style="margin-left: auto;">▶</span>
    </div>

    <div class="context-menu-item" onclick="modificarConcepto()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #6c757d;">✍️</span>
        <span>Modificar Concepto</span>
    </div>

    <div class="context-menu-item" onclick="agregarPesoLinea()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #20bf6b;">⚖️</span>
        <span>Agregar Peso</span>
    </div>

    <div class="context-menu-item" onclick="cancelarVenta()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #F44336;">❌</span>
        <span>Cancelar venta</span>
    </div>

    <div class="context-menu-item" onclick="quitarArticuloTicket()"
        style="padding: 8px 12px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
        <span style="color: #FF5722;">🗑️</span>
        <span>Quitar articulo</span>
    </div>
</div>
@include('pos.partials.js.cantidad-venta')

<script>
    // Handlers mínimos para acciones del menú del ticket. Usan `currentProduct` y `ticket`.
    function mostrarMenuTicket(event, producto) {
        event.preventDefault();
        event.stopPropagation();
        currentProduct = producto;
        const menu = document.getElementById('context-menu-ticket');

        // Mostrar temporalmente para obtener dimensiones reales
        menu.style.visibility = 'hidden';
        menu.style.display = 'block';

        const menuHeight = menu.offsetHeight;
        const menuWidth = menu.offsetWidth;

        let x = event.pageX;
        // Centrar verticalmente respecto al clic
        let y = event.pageY - (menuHeight / 2);

        // Ajustar posición horizontal - mostrar a la izquierda si no hay espacio
        if (x + menuWidth > window.innerWidth) {
            x = event.pageX - menuWidth - 5;
        }

        // Ajustar posición vertical - no salirse por arriba ni por abajo
        const minY = window.scrollY + 10;
        const maxY = window.scrollY + window.innerHeight - menuHeight - 10;

        if (y < minY) {
            y = minY;
        } else if (y > maxY) {
            y = maxY;
        }

        menu.style.left = x + 'px';
        menu.style.top = y + 'px';
        menu.style.visibility = 'visible';
    }

    // Parsea entradas de cantidad soportando fracciones tipo "1/2" y comas
    function parseQuantityInput(input) {
        if (input === null || input === undefined) return NaN;
        const s = String(input).trim();
        if (s.includes('/')) {
            const parts = s.split('/');
            if (parts.length === 2) {
                const a = parseFloat(parts[0].replace(',', '.'));
                const b = parseFloat(parts[1].replace(',', '.'));
                if (!isNaN(a) && !isNaN(b) && b !== 0) return a / b;
            }
        }
        return parseFloat(s.replace(',', '.'));
    }

    function cerrarContextMenuTicket() {
        const menu = document.getElementById('context-menu-ticket');
        if (menu) menu.style.display = 'none';
    }

    function findTicketIndexFromCurrent() {
        if (!currentProduct) return -1;
        return ticket.findIndex(t => String(t.id) === String(currentProduct.id) || (t.producto_id && currentProduct
            .producto_id && String(t.producto_id) === String(currentProduct.producto_id)));
    }

    function modificarPrecioLinea() {
        const idx = findTicketIndexFromCurrent();
        cerrarContextMenuTicket();
        if (idx === -1) return alert('No se encontró la línea del ticket');
        const nuevo = prompt('Ingrese nuevo precio unitario:', ticket[idx].precio.toFixed(2));
        if (nuevo === null) return;
        const val = parseFloat(nuevo);
        if (isNaN(val) || val <= 0) return alert('Precio inválido');
        ticket[idx].precio = val;
        ticket[idx].importe = parseFloat((ticket[idx].cantidad * val).toFixed(2));
        renderTicket();
    }

    async function modificarDescuentoLinea() {
        const idx = findTicketIndexFromCurrent();
        cerrarContextMenuTicket();
        if (idx === -1) return alert('No se encontró la línea del ticket');
        try {
            // 1. Obtener datos del servidor
            const q = new URLSearchParams({
                producto_id: ticket[idx].producto_id || '',
                almacen_detalle_id: ticket[idx].almacen_detalle_id || '',
                cantidad: ticket[idx].cantidad || 1,
                precio: ticket[idx].precio || 0,
                tipo: ticket[idx].es_precio_corporativo ? 'corporativo' : 'publico'
            });

            const resp = await fetch(`{{ url('/pos/pvpd') }}?${q.toString()}`);
            const data = await resp.json();

            const pvpd = parseFloat(data.pvpd); // Ej: 0.25 (25%)
            const maxMontoPermitido = parseFloat(data.maxAmount); // Ej: S/ 50.00
            const subtotal = (ticket[idx].cantidad || 0) * (ticket[idx].precio || 0);

            if (isNaN(pvpd)) return alert('Este producto no tiene descuento máximo configurado.');

            // 2. Pedir el monto al usuario
            const mensajeGuia = `Máximo descuento permitido: S/ ${maxMontoPermitido.toFixed(2)}`;
            const inputUsuario = prompt(`Ingrese el monto a descontar (S/):\n${mensajeGuia}`, '0');

            if (inputUsuario === null) return;

            // Limpiar el input por si ponen "S/" o espacios
            const montoIngresado = parseFloat(inputUsuario.replace(/[S\/s\/\s]/g, '').replace(',', '.'));

            if (isNaN(montoIngresado) || montoIngresado < 0) {
                return alert('Monto inválido.');
            }

            // 3. VALIDACIÓN ESTRICTA contra el monto de la ruta
            if (!isAdmin && montoIngresado > (maxMontoPermitido + 0.01)) {
                return alert(
                    `¡Error! El descuento máximo para este producto es de S/ ${maxMontoPermitido.toFixed(2)}. No puede aplicar S/ ${montoIngresado.toFixed(2)}`
                );
            }

            // 4. CONVERSIÓN A PORCENTAJE (Por detrás)
            // Fórmula: (Monto Descuento / Subtotal) * 100
            const porcentajeCalculado = (montoIngresado / subtotal) * 100;

            // 5. APLICAR
            // Guardamos el texto como porcentaje para que el resto de tu sistema lo procese
            const descuentoTexto = `${porcentajeCalculado.toFixed(2)}%`;

            actualizarDescuento(idx, descuentoTexto);

        } catch (e) {
            console.error(e);
            alert('Error al validar el descuento.');
        }
    }

    function modificarImporteLinea() {
        const idx = findTicketIndexFromCurrent();
        cerrarContextMenuTicket();
        if (idx === -1) return alert('No se encontró la línea del ticket');
        const nuevo = prompt('Ingrese importe total para la línea:', ticket[idx].importe.toFixed(2));
        if (nuevo === null) return;
        const val = parseFloat(nuevo);
        if (isNaN(val) || val < 0) return alert('Importe inválido');
        // Ajustar precio en función de la cantidad
        ticket[idx].precio = ticket[idx].cantidad > 0 ? parseFloat((val / ticket[idx].cantidad).toFixed(2)) : ticket[
            idx].precio;
        ticket[idx].importe = parseFloat(val.toFixed(2));
        renderTicket();
    }

    function descuentoGlobalPrompt() {
        cerrarContextMenuTicket();
        aplicarDescuentoGlobal();
    }

    function otrosComision() {
        cerrarContextMenuTicket();
        alert('Función Otros Comision - Implementar según reglas de negocio');
    }

    function toggleImpuestosSubmenu() {
        cerrarContextMenuTicket();
        alert('Mostrar submenu de impuestos - En desarrollo');
    }

    function modificarConcepto() {
        const idx = findTicketIndexFromCurrent();
        cerrarContextMenuTicket();
        if (idx === -1) return alert('No se encontró la línea del ticket');
        const nuevo = prompt('Modificar concepto / descripción:', ticket[idx].nombre || '');
        if (nuevo === null) return;
        ticket[idx].nombre = nuevo;
        renderTicket();
    }

    function agregarPesoLinea() {
        const idx = findTicketIndexFromCurrent();
        cerrarContextMenuTicket();
        if (idx === -1) return alert('No se encontró la línea del ticket');
        const nuevo = prompt('Ingrese peso (KGM):', ticket[idx].peso || '');
        if (nuevo === null) return;
        const val = parseFloat(nuevo);
        if (isNaN(val)) return alert('Peso inválido');
        ticket[idx].peso = val;
        renderTicket();
    }

    function quitarArticuloTicket() {
        const idx = findTicketIndexFromCurrent();
        cerrarContextMenuTicket();
        if (idx === -1) return alert('No se encontró la línea del ticket');
        if (!confirm('¿Quitar este artículo del ticket?')) return;
        ticket.splice(idx, 1);
        renderTicket();
    }

    // Cerrar el menú ticket al hacer click fuera
    document.addEventListener('click', function (e) {
        const menu = document.getElementById('context-menu-ticket');
        if (menu && menu.style.display === 'block' && !menu.contains(e.target)) {
            cerrarContextMenuTicket();
        }
    });
</script>
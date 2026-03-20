<script>
    if (typeof isEditMode === 'undefined') {
        var isEditMode = false;
    }

    // Abre un modal para conocer cantidad y precio antes de agregar al ticket
    function abrirModalCantidad(editMode = false) {
        if (!currentProduct) return;

        isEditMode = editMode;
        const precio = parseFloat(currentProduct.pvp || currentProduct.pvc || currentProduct.precio || 0).toFixed(2);
        const cantidadInicial = editMode && currentProduct.cantidad ? parseFloat(currentProduct.cantidad) : 1;
        const titulo = editMode ? 'Actualizar Cantidad' : 'Agregar al Carrito';
        const btnTexto = editMode ? 'Actualizar' : '🛒 Agregar';
        const subtitulo = editMode ? 'Modifique la cantidad actual' : 'Ajuste la cantidad y el precio del producto';

        const modalHtml = `
        <div id="modal-cantidad-general" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(33, 37, 41, 0.8); backdrop-filter: blur(4px); z-index: 4000; display: flex; justify-content: center; align-items: center;" onclick="cerrarModalCantidad()">
            <div style="background: #ffffff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); width: 900px; max-width: 95%; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; overflow: hidden;" onclick="event.stopPropagation()">
                
                <div style="background: #f8f9fa; padding: 20px 24px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin: 0; font-size: 20px; color: #1a1d23; font-weight: 700;">${titulo}</h3>
                        <p style="margin: 4px 0 0; color: #6c757d; font-size: 14px;">${subtitulo}</p>
                    </div>
                </div>

                <div style="display: flex; flex-wrap: wrap;">
                    <div style="flex: 1; padding: 24px; min-width: 400px; border-right: 1px solid #f0f0f0;">
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 24px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 8px;">Cantidad</label>
                                <input id="modal-cantidad-cantidad" type="number" step="any" value="${cantidadInicial}" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 18px; font-weight: 600; text-align: center; color: #2d3436; outline: none; transition: border-color 0.2s;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 8px;">Precio Unit.</label>
                                <input id="modal-cantidad-precio" type="text" value="${precio}" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 18px; font-weight: 600; text-align: center; color: #2d3436; outline: none;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #007bff; text-transform: uppercase; margin-bottom: 8px;">Total Item</label>
                                <input id="modal-cantidad-importe" type="text" value="${(cantidadInicial * precio).toFixed(2)}" style="width: 100%; padding: 12px; border: 2px solid #e7f1ff; border-radius: 10px; font-size: 18px; font-weight: 700; text-align: center; color: #007bff; background: #f0f7ff;" readonly>
                            </div>
                        </div>

                        <div style="background: #fcfcfc; border: 1px solid #eef2f3; border-radius: 12px; padding: 20px; margin-bottom: 24px; position: relative;">
                            <div style="display: flex; gap: 15px;">
                                <div style="flex: 1;">
                                    <span style="font-family: monospace; background: #343a40; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px;">ID: ${currentProduct.producto_id || '000'}</span>
                                    <h4 style="margin: 10px 0 5px; font-size: 18px; color: #2d3436;">${currentProduct.nombre || currentProduct.descripcion || 'Producto sin nombre'}</h4>
                                    <div style="font-size: 13px; color: #636e72; line-height: 1.6;">
                                        PVP: <b>S/${currentProduct.pvp || '0.00'}</b> • PVC: <b>S/${currentProduct.pvc || '0.00'}</b>
                                    </div>
                                </div>
                                <div style="width: 80px; text-align: center;">
                                  <i class='bx  bx-box'></i> 
                                    <div style="margin-top: 8px; font-weight: 800; color: #20bf6b; font-size: 14px;">${currentProduct.unidad || 'UND'}</div>
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <button onclick="cerrarModalCantidad()" style="padding: 14px; border-radius: 10px; border: 1px solid #dee2e6; background: #fff; color: #495057; font-weight: 600; cursor: pointer; transition: 0.2s;">↶ Volver</button>
                            <button id="modal-cantidad-agregar" style="padding: 14px; border-radius: 10px; border: none; background: #10ac84; color: #fff; font-weight: 700; font-size: 16px; cursor: pointer; box-shadow: 0 4px 15px rgba(16, 172, 132, 0.3);">${btnTexto}</button>
                        </div>
                    </div>

                    <div style="width: 320px; background: #f8f9fa; padding: 24px;">
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">
                            ${['0.125', '0.25', '0.5'].map((v, i) => `<button class="keypad-btn" data-key="${v}" style="padding: 12px; border: 1px solid #dee2e6; border-radius: 8px; background: #fff; font-weight: 600; color: #4b6584; cursor: pointer;">${['1/8','1/4','1/2'][i]}</button>`).join('')}
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                            ${[1,2,3,4,5,6,7,8,9].map(n => `<button class="num-btn" data-key="${n}" style="padding: 18px; border: none; border-radius: 10px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-size: 20px; font-weight: 600; color: #2d3436; cursor: pointer;">${n}</button>`).join('')}
                            <button class="op-btn" data-op="." style="padding: 18px; border-radius: 10px; border: none; background: #e9ecef; font-size: 20px; font-weight: 700;">.</button>
                            <button class="num-btn" data-key="0" style="padding: 18px; border-radius: 10px; border: none; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-size: 20px; font-weight: 600;">0</button>
                            <button id="key-borrar" style="padding: 18px; border-radius: 10px; border: none; background: #ff7675; color: #fff; font-weight: 700;">DEL</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const qty = document.getElementById('modal-cantidad-cantidad');
        const price = document.getElementById('modal-cantidad-precio');
        const importe = document.getElementById('modal-cantidad-importe');

        function actualizarImporte() {
            const q = parseFloat(qty.value) || 0;
            const p = parseFloat(price.value) || 0;
            importe.value = (q * p).toFixed(2);
        }

        qty.addEventListener('input', actualizarImporte);
        price.addEventListener('input', actualizarImporte);

        // Enfocar y seleccionar cantidad
        setTimeout(() => {
            qty.focus();
            qty.select();
        }, 80);

        document.getElementById('modal-cantidad-agregar').addEventListener('click', aceptarModalCantidad);

        // Keypad behavior: track focused field and handle clicks
        let focusedField = 'cantidad';
        qty.addEventListener('focus', () => focusedField = 'cantidad');
        price.addEventListener('focus', () => focusedField = 'precio');

        function appendToFocused(text) {
            if (focusedField === 'cantidad') {
                // For quantity allow decimals
                const cur = qty.value ? String(qty.value) : '';
                if (text === 'B') return qty.value = '';
                if (text === '±') return qty.value = (parseFloat(qty.value || 0) * -1) || 0;
                qty.value = (cur === '0' ? text : cur + text);
                actualizarImporte();
            } else {
                const cur = price.value ? String(price.value) : '';
                if (text === 'B') return price.value = '';
                if (text === '±') return price.value = (parseFloat(price.value || 0) * -1) || 0;
                price.value = (cur === '0' ? text : cur + text);
                actualizarImporte();
            }
        }

        // Attach numeric buttons
        document.querySelectorAll('#modal-cantidad-general .num-btn').forEach(b => {
            b.addEventListener('click', () => appendToFocused(b.dataset.key));
        });
        document.querySelectorAll('#modal-cantidad-general .op-btn').forEach(b => {
            b.addEventListener('click', () => {
                const k = b.dataset.op;
                if (k === '.') appendToFocused('.');
                else appendToFocused(k);
            });
        });
        document.querySelectorAll('#modal-cantidad-general .keypad-btn').forEach(b => {
            b.addEventListener('click', () => {
                const v = parseFloat(b.dataset.key);
                // Set quantity to fraction of 1
                qty.value = v;
                actualizarImporte();
                qty.focus();
            });
        });

        const borrar = document.getElementById('key-borrar');
        const sig = document.getElementById('key-sig');
        if (borrar) borrar.addEventListener('click', () => appendToFocused('B'));
        if (sig) sig.addEventListener('click', () => appendToFocused('±'));

        // Handle Enter key for quick submit
        const handleEnter = (e) => {
            if (e.key === 'Enter') {
                aceptarModalCantidad();
            }
        };
        qty.addEventListener('keypress', handleEnter);
        price.addEventListener('keypress', handleEnter);

        cerrarContextMenu();
    }

    async function aceptarModalCantidad() {
        const qtyEl = document.getElementById('modal-cantidad-cantidad');
        const priceEl = document.getElementById('modal-cantidad-precio');
        const cantidad = parseFloat(qtyEl.value);
        const precio = parseFloat(priceEl.value) || 0;

        if (isNaN(cantidad) || cantidad <= 0) {
            alert('Ingrese una cantidad válida');
            return;
        }

        if (!currentProduct) {
            cerrarModalCantidad();
            return;
        }

        if (cantidad > (currentProduct.cantidad_total || 99999)) {
            // Allow adding but warn
            if (!confirm(
                    `Stock insuficiente. Disponible: ${currentProduct.cantidad_total || 0}. Agregar de todos modos?`
                )) {
                return;
            }
        }

        const productoParaTicket = {
            id: currentProduct.id || `ctx_${currentProduct.producto_id}_${Date.now()}`,
            producto_id: currentProduct.producto_id,
            producto_linea_id: currentProduct.product_linea_id || currentProduct.producto_linea_id || null,
            nombre: currentProduct.nombre || currentProduct.descripcion || '',
            marca: currentProduct.marca || '',
            imagen_principal: currentProduct.imagen_principal || '',
            cantidad: cantidad,
            cantidad_disponible: currentProduct.cantidad_total || currentProduct.cantidad_disponible || 0,
            precio: precio,
            importe: parseFloat((cantidad * precio).toFixed(2)),
            pvp: currentProduct.pvp || precio,
            pvc: currentProduct.pvc || precio,
            tipo_impuesto: currentProduct.tipo_impuesto || 'gravado',
            descuento: currentProduct.descuento || 0,
            descuentoFijo: currentProduct.descuentoFijo || 0,
            descuentoTexto: currentProduct.descuentoTexto || '0%',

            // Flag to replace instead of add
            replaceQuantity: isEditMode
        };

        // Si tiene un solo lote intentar resolver almacen_detalle_id
        // Solo hacerlo si no es edit (en edit ya tenemos id)
        if (!isEditMode && currentProduct.total_lotes && parseInt(currentProduct.total_lotes) === 1) {
            try {
                const resp = await fetch(
                    `/pos/obtener-lotes?producto_id=${encodeURIComponent(currentProduct.producto_id)}`);
                if (resp.ok) {
                    const lotes = await resp.json();
                    if (Array.isArray(lotes) && lotes.length > 0) {
                        const lote = lotes[0];
                        productoParaTicket.almacen_detalle_id = lote.lote_id || lote.id || null;
                        productoParaTicket.lote = lote.lote || productoParaTicket.lote;
                        productoParaTicket.fecha_vencimiento = lote.fecha_vencimiento || productoParaTicket
                            .fecha_vencimiento;
                        productoParaTicket.es_lote_especifico = true;
                    }
                }
            } catch (e) {
                console.warn('No fue posible obtener lotes:', e);
            }
        } else {
            // Keep existing identifiers
            productoParaTicket.almacen_detalle_id = currentProduct.almacen_detalle_id || null;
            productoParaTicket.lote = currentProduct.lote || null;
            productoParaTicket.fecha_vencimiento = currentProduct.fecha_vencimiento || null;
            productoParaTicket.es_lote_especifico = currentProduct.es_lote_especifico || false;
        }

        // Usar la función central para agregar al ticket
        if (typeof agregarProductoAlTicket === 'function') {
            agregarProductoAlTicket(productoParaTicket);
        } else {
            // Fallback
            console.error("Function agregarProductoAlTicket not found");
        }

        mostrarNotificacion && typeof mostrarNotificacion === 'function' && mostrarNotificacion(
            `Se ${isEditMode ? 'actualizó' : 'agregó'} ${cantidad} unidad(es) de ${productoParaTicket.nombre}`);

        cerrarModalCantidad();
    }

    function cerrarModalCantidad() {
        const modal = document.getElementById('modal-cantidad-general');
        if (modal) modal.remove();
    }
</script>

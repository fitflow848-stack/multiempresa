<script>
    let productToBreak = null;
    let targetBreakProduct = null;
    let typingTimer;
    const doneTypingInterval = 500;

    function abrirModalRomperDocena() {
        if (!currentProduct) return;
        productToBreak = currentProduct;
        
        // Setup UI
        document.getElementById('romper-nombre-origen').textContent = productToBreak.nombre;
        document.getElementById('romper-stock-origen').textContent = parseFloat(productToBreak.cantidad_total || 0).toFixed(2);
        document.getElementById('romper-unidad-bulk').textContent = productToBreak.unidad_medida || 'UNIDADES / SACOS';
        
        // Reset fields
        document.getElementById('romper-cantidad-bulk').value = 1;
        document.getElementById('romper-search-target').value = '';
        document.getElementById('romper-resultados-container').innerHTML = '';
        document.getElementById('romper-resultados-container').style.display = 'none';
        document.getElementById('romper-producto-seleccionado').style.display = 'none';
        document.getElementById('romper-factor-container').style.display = 'none';
        
        // Mostrar modal
        document.getElementById('modal-romper-docena').style.display = 'flex';
        cerrarContextMenu();

        // Intentar autocompletar búsqueda con nombre similar (quitar SACO, etc)
        let baseSearch = productToBreak.nombre.split('/')[0].trim();
        document.getElementById('romper-search-target').value = baseSearch;
        buscarProductoTargetRomper(baseSearch);
    }

    function cerrarModalRomperDocena() {
        document.getElementById('modal-romper-docena').style.display = 'none';
        productToBreak = null;
        targetBreakProduct = null;
    }

    // Input listener con debounce
    document.getElementById('romper-search-target').addEventListener('input', function() {
        clearTimeout(typingTimer);
        const query = this.value.trim();
        if (query.length > 2) {
            typingTimer = setTimeout(() => buscarProductoTargetRomper(query), doneTypingInterval);
        }
    });

    async function buscarProductoTargetRomper(query) {
        const container = document.getElementById('romper-resultados-container');
        container.innerHTML = '<div style="padding: 10px; color: #666; font-size: 0.9rem;"><i class="bx bx-loader-alt bx-spin"></i> Buscando...</div>';
        container.style.display = 'block';

        try {
            const resp = await fetch(`/pos/buscar-productos?q=${encodeURIComponent(query)}&include_empty=1`);
            const productos = await resp.json();

            container.innerHTML = '';
            if (productos.length === 0) {
                container.innerHTML = '<div style="padding: 10px; color: #dc3545; font-size: 0.9rem;">No se encontraron productos similares.</div>';
                return;
            }

            productos.forEach(p => {
                // No permitir seleccionarse a sí mismo
                if (p.producto_id == productToBreak.producto_id) return;

                const div = document.createElement('div');
                div.style.padding = '10px 15px';
                div.style.borderBottom = '1px solid #eee';
                div.style.cursor = 'pointer';
                div.style.fontSize = '0.95rem';
                div.innerHTML = `
                    <div style="font-weight: 700;">${p.nombre}</div>
                    <div style="font-size: 0.8rem; color: #6c757d;">Stock: ${p.cantidad_total || 0} | Marca: ${p.marca || '-'}</div>
                `;
                div.onclick = () => seleccionarDestinoRomper(p);
                div.onmouseover = () => div.style.background = '#f1f3f5';
                div.onmouseout = () => div.style.background = 'transparent';
                container.appendChild(div);
            });
        } catch (e) {
            console.error(e);
            container.innerHTML = '<div style="padding: 10px; color: #dc3545; font-size: 0.9rem;">Error al buscar.</div>';
        }
    }

    function seleccionarDestinoRomper(p) {
        targetBreakProduct = p;
        document.getElementById('romper-search-target').style.display = 'none';
        document.getElementById('romper-resultados-container').style.display = 'none';
        
        const selDiv = document.getElementById('romper-producto-seleccionado');
        document.getElementById('romper-nombre-destino').textContent = p.nombre;
        selDiv.style.display = 'flex';
        
        // Mostrar configuración de factor
        document.getElementById('romper-factor-container').style.display = 'block';
        
        // Intentar adivinar factor (si es docena -> 12, si es saco 50kg -> 50)
        let suggestedFactor = 12;
        if (productToBreak.nombre.toUpperCase().includes('50 KG') || productToBreak.nombre.toUpperCase().includes('50KG')) suggestedFactor = 50;
        if (productToBreak.nombre.toUpperCase().includes('S/40')) suggestedFactor = 40;
        if (productToBreak.nombre.toUpperCase().includes('DOZ') || productToBreak.nombre.toUpperCase().includes('DOCENA')) suggestedFactor = 12;
        
        document.getElementById('romper-factor').value = suggestedFactor;
        actualizarCalculoDestino();
    }

    function cancelarSeleccionDestino() {
        targetBreakProduct = null;
        document.getElementById('romper-search-target').style.display = 'block';
        document.getElementById('romper-search-target').focus();
        document.getElementById('romper-producto-seleccionado').style.display = 'none';
        document.getElementById('romper-factor-container').style.display = 'none';
    }

    function actualizarCalculoDestino() {
        const qtyBulk = parseFloat(document.getElementById('romper-cantidad-bulk').value) || 0;
        const factor = parseFloat(document.getElementById('romper-factor').value) || 0;
        const total = qtyBulk * factor;
        document.getElementById('romper-total-destino').textContent = `${total.toFixed(2)} Und.`;
    }

    // Listeners para el cálculo en tiempo real
    document.getElementById('romper-cantidad-bulk').oninput = actualizarCalculoDestino;
    document.getElementById('romper-factor').oninput = actualizarCalculoDestino;

    async function procesarRoturaManual() {
        if (!productToBreak || !targetBreakProduct) {
            return Swal.fire('Error', 'Debes seleccionar un producto de destino.', 'warning');
        }

        const qtyBulk = parseFloat(document.getElementById('romper-cantidad-bulk').value) || 0;
        const factor = parseFloat(document.getElementById('romper-factor').value) || 0;

        if (qtyBulk <= 0 || factor <= 0) {
            return Swal.fire('Atención', 'Ingresa una cantidad y factor válidos.', 'warning');
        }

        // Validación de stock
        if (qtyBulk > parseFloat(productToBreak.cantidad_total || 0)) {
            return Swal.fire('Error', 'No tienes suficiente stock en el producto origen.', 'error');
        }

        const confirm = await Swal.fire({
            title: '¿Confirmar Rotura?',
            text: `Se descontarán ${qtyBulk} del origen y se aumentarán ${(qtyBulk * factor).toFixed(2)} en el producto destino. Esta acción ajustará el inventario directamente.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, aplicar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d63384'
        });

        if (!confirm.isConfirmed) return;

        const btn = document.getElementById('btn-confirmar-romper');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

        try {
            const resp = await fetch(`/pos/procesar-rotura-stock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    origen_producto_id: productToBreak.producto_id,
                    origen_lote_id: productToBreak.id, // ID del detalle de almacén
                    cantidad_origen: qtyBulk,
                    destino_producto_id: targetBreakProduct.producto_id,
                    destino_producto_linea_id: targetBreakProduct.product_linea_id,
                    destino_lote_id: targetBreakProduct.id,
                    factor: factor
                })
            });

            const data = await resp.json();

            if (data.success) {
                await Swal.fire('¡Éxito!', 'Stock ajustado correctamente.', 'success');
                cerrarModalRomperDocena();
                
                // Refrescar buscador del POS
                const posSearchInput = document.getElementById('main-search-input');
                if (posSearchInput && typeof buscarProductosDebounced === 'function') {
                    buscarProductosDebounced(posSearchInput.value);
                }
            } else {
                Swal.fire('Error', data.error || 'No se pudo procesar el ajuste.', 'error');
            }
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'Hubo un problema al procesar la solicitud.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'DIFERENCIAR Y AUMENTAR STOCK';
        }
    }
</script>

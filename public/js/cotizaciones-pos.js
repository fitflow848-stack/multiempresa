// Variables globales para el sistema de cotizaciones
let productosEnCotizacion = [];
let clienteSeleccionado = null;
let contadorProductos = 1;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    initializeCotizaciones();
    cargarDatosSiVieneDeCotizacion();
});

function initializeCotizaciones() {
    setupBuscadorProductos();
    setupEventListeners();
    actualizarTotales();
}

function setupBuscadorProductos() {
    const codigoBarrasInput = document.getElementById('codigo-barras-input');
    const buscarProductoInput = document.getElementById('buscar-producto-input');
    
    // Búsqueda por código de barras (Enter)
    codigoBarrasInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarPorCodigoBarras(this.value);
        }
    });

    // Búsqueda por nombre (tiempo de espera)
    let searchTimeout;
    buscarProductoInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => buscarProductos(query), 500);
        } else if (query.length === 0) {
            limpiarResultados();
        }
    });

    // Búsqueda manual con botón
    document.querySelector('.search-btn').addEventListener('click', function() {
        const query = buscarProductoInput.value.trim();
        if (query) {
            buscarProductos(query);
        }
    });
}

function setupEventListeners() {
    // Vigencia
    document.getElementById('vigencia-dias').addEventListener('change', function() {
        document.getElementById('footer-vigencia').textContent = this.value + ' días';
    });

    // Cliente por defecto
    document.getElementById('footer-cliente').addEventListener('click', mostrarBuscadorClientes);
}

function buscarPorCodigoBarras(codigo) {
    if (!codigo.trim()) return;

    fetch(`/api/productos/search?cb=${encodeURIComponent(codigo)}`)
        .then(response => response.json())
        .then(productos => {
            if (productos.length > 0) {
                // Si encontramos exactamente un producto, agregarlo directamente
                if (productos.length === 1) {
                    abrirModalCantidad(productos[0]);
                } else {
                    mostrarResultadosBusqueda(productos);
                }
            } else {
                alert('No se encontró ningún producto con ese código');
            }
            document.getElementById('codigo-barras-input').value = '';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al buscar producto');
        });
}

function buscarProductos(query = null) {
    const buscarQuery = query || document.getElementById('buscar-producto-input').value.trim();
    if (!buscarQuery) return;

    const tbody = document.getElementById('productos-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="5" style="text-align: center; padding: 20px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Buscando...</span>
                </div>
                <p style="margin-top: 10px;">Buscando productos...</p>
            </td>
        </tr>
    `;

    fetch(`/api/productos/search?q=${encodeURIComponent(buscarQuery)}`)
        .then(response => response.json())
        .then(productos => {
            mostrarResultadosBusqueda(productos);
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2" style="display: block;"></i>
                        Error al buscar productos
                    </td>
                </tr>
            `;
        });
}

function mostrarResultadosBusqueda(productos) {
    const tbody = document.getElementById('productos-tbody');
    
    if (productos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-search fa-2x mb-3" style="display: block;"></i>
                    No se encontraron productos
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    productos.forEach(producto => {
        html += `
            <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#e8f4fd'" onmouseout="this.style.backgroundColor=''">
                <td>
                    <div style="font-weight: 600; font-size: 13px; color: #333;">
                        ${producto.nombre}
                    </div>
                    ${producto.presentacion || producto.concentracion ? 
                        `<div style="font-size: 11px; color: #666; margin-top: 2px;">
                            ${[producto.presentacion, producto.concentracion].filter(Boolean).join(' - ')}
                        </div>` : ''
                    }
                    ${producto.codigo_ref ? 
                        `<div style="font-size: 10px; color: #999; margin-top: 1px;">
                            Ref: ${producto.codigo_ref}
                        </div>` : ''
                    }
                </td>
                <td style="text-align: center; font-size: 12px; color: ${producto.stock > 0 ? '#28a745' : '#dc3545'};">
                    ${producto.stock || 0}
                </td>
                <td style="text-align: center; font-weight: 600; font-size: 13px;">
                    ${producto.precio_compra ? 'S/ ' + parseFloat(producto.precio_compra).toFixed(2) : 'Sin precio'}
                </td>
                <td style="text-align: center; font-size: 11px; color: #666;">
                    ${producto.lote || '-'}
                </td>
                <td style="text-align: center;">
                    <button onclick="abrirModalCantidad(${JSON.stringify(producto).replace(/"/g, '&quot;')})" 
                            class="btn btn-sm btn-primary" style="padding: 4px 8px; font-size: 11px;">
                        ➕ Agregar
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function limpiarResultados() {
    document.getElementById('productos-tbody').innerHTML = `
        <tr>
            <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-search fa-2x mb-3" style="display: block;"></i>
                Busca productos para agregarlos a la cotización
            </td>
        </tr>
    `;
}

function limpiarBusqueda() {
    document.getElementById('codigo-barras-input').value = '';
    document.getElementById('buscar-producto-input').value = '';
    limpiarResultados();
}

function abrirModalCantidad(producto) {
    // Crear modal dinámico
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; justify-content: center; align-items: center;">
            <div style="background: white; padding: 25px; border-radius: 8px; max-width: 500px; width: 90%; font-family: Arial, sans-serif;">
                <h4 style="margin: 0 0 20px 0; color: #007bff; text-align: center;">🛒 Agregar a Cotización</h4>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>${producto.nombre}</strong>
                    ${producto.presentacion || producto.concentracion ? 
                        `<br><small style="color: #666;">${[producto.presentacion, producto.concentracion].filter(Boolean).join(' - ')}</small>` : ''
                    }
                    ${producto.codigo_ref ? `<br><small style="color: #999;">Ref: ${producto.codigo_ref}</small>` : ''}
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Cantidad:</label>
                        <input type="number" id="modal-cantidad" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" 
                               value="1" min="0.001" step="0.001">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Precio Unitario:</label>
                        <input type="number" id="modal-precio" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" 
                               value="${producto.precio_compra || producto.pvp || 0}" min="0" step="0.01">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Descuento:</label>
                        <input type="number" id="modal-descuento" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" 
                               value="0" min="0" step="0.01">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Total:</label>
                        <input type="text" id="modal-total" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa;" 
                               readonly>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Lote:</label>
                        <input type="text" id="modal-lote" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" 
                               value="${producto.lote || ''}">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">F. Vencimiento:</label>
                        <input type="date" id="modal-fecha-venc" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" 
                               value="${producto.fecha_vencimiento || ''}">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: center; gap: 10px;">
                    <button onclick="this.closest('div[style*=\"position: fixed\"]').remove()" 
                            style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Cancelar
                    </button>
                    <button onclick="agregarProductoACotizacion(${JSON.stringify(producto).replace(/"/g, '&quot;')})" 
                            style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        ➕ Agregar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Event listeners para recalcular total
    const cantidadInput = modal.querySelector('#modal-cantidad');
    const precioInput = modal.querySelector('#modal-precio');
    const descuentoInput = modal.querySelector('#modal-descuento');
    const totalInput = modal.querySelector('#modal-total');
    
    function calcularTotal() {
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const precio = parseFloat(precioInput.value) || 0;
        const descuento = parseFloat(descuentoInput.value) || 0;
        const total = (cantidad * precio) - descuento;
        totalInput.value = total.toFixed(2);
    }
    
    cantidadInput.addEventListener('input', calcularTotal);
    precioInput.addEventListener('input', calcularTotal);
    descuentoInput.addEventListener('input', calcularTotal);
    
    calcularTotal(); // Calcular inicial
}

function agregarProductoACotizacion(producto) {
    const modal = document.querySelector('div[style*="position: fixed"]');
    const cantidad = parseFloat(modal.querySelector('#modal-cantidad').value);
    const precio = parseFloat(modal.querySelector('#modal-precio').value);
    const descuento = parseFloat(modal.querySelector('#modal-descuento').value) || 0;
    const lote = modal.querySelector('#modal-lote').value;
    const fechaVenc = modal.querySelector('#modal-fecha-venc').value;
    
    if (!cantidad || cantidad <= 0) {
        alert('La cantidad debe ser mayor a 0');
        return;
    }
    
    if (!precio || precio < 0) {
        alert('El precio debe ser mayor o igual a 0');
        return;
    }
    
    const total = (cantidad * precio) - descuento;
    
    const productoEnCotizacion = {
        id: contadorProductos++,
        producto_id: producto.id,
        descripcion: producto.nombre + (producto.presentacion ? ' ' + producto.presentacion : ''),
        cantidad: cantidad,
        precio_unitario: precio,
        descuento: descuento,
        subtotal: total,
        lote: lote,
        fecha_vencimiento: fechaVenc,
        // Datos adicionales para mostrar
        stock: producto.stock || 0,
        cb: producto.cb || producto.codigo_ref || ''
    };
    
    productosEnCotizacion.push(productoEnCotizacion);
    actualizarTablaCotizacion();
    actualizarTotales();
    
    // Cerrar modal
    modal.remove();
}

function actualizarTablaCotizacion() {
    const tbody = document.getElementById('cotizacion-tbody');
    
    if (productosEnCotizacion.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-shopping-basket fa-2x mb-2" style="display: block;"></i>
                    No hay productos en la cotización
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    productosEnCotizacion.forEach((producto, index) => {
        html += `
            <tr>
                <td style="text-align: center; font-weight: 600; color: #666;">${index + 1}</td>
                <td>
                    <div style="font-size: 12px; font-weight: 600;">${producto.descripcion}</div>
                    ${producto.cb ? `<div style="font-size: 10px; color: #999;">CB: ${producto.cb}</div>` : ''}
                </td>
                <td style="text-align: center; font-size: 11px;">${producto.lote || '-'}</td>
                <td style="text-align: center; font-weight: 600;">${producto.cantidad}</td>
                <td style="text-align: center; color: #dc3545;">${producto.descuento > 0 ? producto.descuento.toFixed(2) : '-'}</td>
                <td style="text-align: center; font-weight: 600;">S/ ${producto.precio_unitario.toFixed(2)}</td>
                <td style="text-align: center; font-weight: bold; color: #28a745;">S/ ${producto.subtotal.toFixed(2)}</td>
                <td style="text-align: center;">
                    <button onclick="eliminarProducto(${index})" 
                            style="background: #dc3545; color: white; border: none; padding: 2px 6px; border-radius: 3px; cursor: pointer; font-size: 12px;"
                            title="Eliminar producto">
                        🗑️
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function eliminarProducto(index) {
    if (confirm('¿Estás seguro de eliminar este producto de la cotización?')) {
        productosEnCotizacion.splice(index, 1);
        actualizarTablaCotizacion();
        actualizarTotales();
    }
}

function actualizarTotales() {
    const subtotal = productosEnCotizacion.reduce((sum, p) => sum + p.subtotal, 0);
    const descuentos = productosEnCotizacion.reduce((sum, p) => sum + p.descuento, 0);
    const igv = subtotal * 0.18;
    const total = subtotal + igv;
    
    document.getElementById('footer-subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('footer-igv').textContent = igv.toFixed(2);
    document.getElementById('footer-dscto').textContent = descuentos.toFixed(2);
    document.getElementById('footer-total').textContent = total.toFixed(2);
    document.getElementById('footer-productos-listados').textContent = productosEnCotizacion.length;
}

function mostrarBuscadorClientes() {
    // Implementar modal de clientes similar al POS
    alert('Funcionalidad de selección de clientes en desarrollo');
}

function aplicarDescuentoGlobal() {
    const descuento = prompt('Ingrese el descuento global en soles:');
    if (descuento !== null && !isNaN(descuento) && parseFloat(descuento) >= 0) {
        // Implementar lógica de descuento global
        alert('Funcionalidad en desarrollo');
    }
}

function limpiarCotizacion() {
    if (productosEnCotizacion.length === 0 || confirm('¿Estás seguro de limpiar toda la cotización?')) {
        productosEnCotizacion = [];
        clienteSeleccionado = null;
        contadorProductos = 1;
        
        actualizarTablaCotizacion();
        actualizarTotales();
        
        document.getElementById('footer-cliente').textContent = 'SELECCIONAR CLIENTE';
        document.getElementById('vigencia-dias').value = 30;
        document.getElementById('footer-vigencia').textContent = '30 días';
        document.getElementById('observaciones-input').value = '';
        
        limpiarBusqueda();
    }
}

function cancelarCotizacion() {
    if (confirm('¿Estás seguro de cancelar la cotización? Se perderán todos los datos.')) {
        window.location.href = '/cotizaciones';
    }
}

function guardarBorrador() {
    alert('Funcionalidad de borrador en desarrollo');
}

function guardarCotizacion() {
    if (productosEnCotizacion.length === 0) {
        alert('Debe agregar al menos un producto a la cotización');
        return;
    }
    
    if (!clienteSeleccionado) {
        alert('Debe seleccionar un cliente');
        return;
    }
    
    const vigenciaDias = parseInt(document.getElementById('vigencia-dias').value) || 30;
    const observaciones = document.getElementById('observaciones-input').value;
    
    const subtotal = productosEnCotizacion.reduce((sum, p) => sum + p.subtotal, 0);
    const descuentos = productosEnCotizacion.reduce((sum, p) => sum + p.descuento, 0);
    const igv = subtotal * 0.18;
    const total = subtotal + igv;
    
    const data = {
        cliente_id: clienteSeleccionado.id,
        productos: productosEnCotizacion,
        subtotal: subtotal,
        descuento_total: descuentos,
        igv: igv,
        total: total,
        observaciones: observaciones,
        vigencia_dias: vigenciaDias
    };
    
    // Mostrar loading
    const btnGuardar = document.querySelector('.control-btn.success');
    const originalText = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '⏳ Guardando...';
    btnGuardar.disabled = true;
    
    fetch('/cotizaciones', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cotización guardada exitosamente');
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = '/cotizaciones';
            }
        } else {
            alert(data.message || 'Error al guardar la cotización');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar la cotización');
    })
    .finally(() => {
        btnGuardar.innerHTML = originalText;
        btnGuardar.disabled = false;
    });
}

function cargarDatosSiVieneDeCotizacion() {
    // Si viene desde una cotización existente, cargar los datos
    const urlParams = new URLSearchParams(window.location.search);
    const cotizacionId = urlParams.get('cotizacion_id');
    
    if (cotizacionId) {
        fetch(`/cotizaciones/api/${cotizacionId}/datos`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cargar datos de la cotización
                    const cotizacion = data.data.cotizacion;
                    const cliente = data.data.cliente;
                    const productos = data.data.productos;
                    
                    // Establecer cliente
                    clienteSeleccionado = cliente;
                    document.getElementById('footer-cliente').textContent = cliente.nombre;
                    
                    // Cargar productos
                    productosEnCotizacion = productos.map((p, index) => ({
                        id: index + 1,
                        producto_id: p.producto_id,
                        descripcion: p.descripcion,
                        cantidad: parseFloat(p.cantidad),
                        precio_unitario: parseFloat(p.precio),
                        descuento: parseFloat(p.descuento || 0),
                        subtotal: parseFloat(p.importe),
                        lote: p.lote || '',
                        fecha_vencimiento: p.fecha_vencimiento || ''
                    }));
                    
                    actualizarTablaCotizacion();
                    actualizarTotales();
                    
                    contadorProductos = productosEnCotizacion.length + 1;
                }
            })
            .catch(error => {
                console.error('Error al cargar cotización:', error);
            });
    }
}
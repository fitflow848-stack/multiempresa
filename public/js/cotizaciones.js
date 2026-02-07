// Variables globales
let selectedProducts = [];
let clientesCache = [];
let currentEditingProduct = null;

// Helper para filtrar valores vacíos o placeholder '-- Ver --'
function cleanProductDetail(value) {
    if (!value || value === '-- Ver --' || value.trim() === '') {
        return '';
    }
    return value.trim();
}

function formatProductDetails(presentacion, concentracion) {
    const parts = [
        cleanProductDetail(presentacion),
        cleanProductDetail(concentracion)
    ].filter(Boolean);
    return parts.join(' ');
}

// Inicializar cuando la página está cargada
document.addEventListener('DOMContentLoaded', function () {
    initializeCotizacion();
});

function initializeCotizacion() {
    // Configurar búsqueda de productos
    setupProductSearch();

    // Configurar búsqueda de clientes
    setupClienteSearch();

    // Configurar modal de cantidad
    setupCantidadModal();

    // Configurar botón de guardar
    setupSaveButton();

    // Configurar descuento general
    setupDescuentoGeneral();
}

function setupProductSearch() {
    const searchInput = document.getElementById('search-product');
    const searchBtn = document.getElementById('search-btn');
    const resultsContainer = document.getElementById('search-results');

    let searchTimeout;

    // Búsqueda automática
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (this.value.length >= 2) {
                searchProducts(this.value);
            } else {
                showEmptySearch();
            }
        }, 300);
    });

    // Búsqueda manual
    searchBtn.addEventListener('click', function () {
        const query = searchInput.value.trim();
        if (query) {
            searchProducts(query);
        }
    });

    // Enter para buscar
    searchInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBtn.click();
        }
    });
}

function searchProducts(query) {
    const resultsContainer = document.getElementById('search-results');

    // Mostrar loading
    resultsContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Buscando...</span>
            </div>
            <p class="mt-2">Buscando productos...</p>
        </div>
    `;

    // Hacer la petición
    fetch(`/api/productos/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(products => {
            displayProductResults(products);
        })
        .catch(error => {
            console.error('Error:', error);
            resultsContainer.innerHTML = `
                <div class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <p>Error al buscar productos</p>
                </div>
            `;
        });
}

function displayProductResults(products) {
    const resultsContainer = document.getElementById('search-results');

    if (products.length === 0) {
        resultsContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-3"></i>
                <p>No se encontraron productos</p>
            </div>
        `;
        return;
    }

    let html = `
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th width="100">Precio</th>
                    <th width="80">Acción</th>
                </tr>
            </thead>
            <tbody>
    `;

    products.forEach(product => {
        html += `
            <tr>
                <td>
                    <div>
                        <strong>${product.nombre}</strong>
                        <br><small class="text-muted">
                            ${formatProductDetails(product.presentacion, product.concentracion)}
                        </small>
                        ${product.codigo_ref ? `<br><small class="text-muted">Ref: ${product.codigo_ref}</small>` : ''}
                    </div>
                </td>
                <td class="text-end">
                    ${product.pvp ? 'S/ ' + parseFloat(product.pvp).toFixed(2) : 'Sin precio'}
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" 
                            onclick="selectProduct(${JSON.stringify(product).replace(/"/g, '&quot;')})">
                        <i class="fas fa-plus"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';
    resultsContainer.innerHTML = html;
}

function showEmptySearch() {
    const resultsContainer = document.getElementById('search-results');
    resultsContainer.innerHTML = `
        <div class="text-center text-muted py-5">
            <i class="fas fa-search fa-2x mb-3"></i>
            <p>Busca productos para agregarlos a la cotización</p>
        </div>
    `;
}

function selectProduct(product) {
    currentEditingProduct = product;

    // Llenar el modal con los datos del producto
    document.getElementById('producto-info').innerHTML = `
        <div class="bg-light p-3 rounded">
            <h6>${product.nombre}</h6>
            <small class="text-muted">
                ${formatProductDetails(product.presentacion, product.concentracion)}
                ${product.codigo_ref ? ' - Ref: ' + product.codigo_ref : ''}
            </small>
        </div>
    `;

    // Configurar valores por defecto
    document.getElementById('cantidad-input').value = 1;
    document.getElementById('precio-input').value = product.pvp || 0;
    document.getElementById('descuento-input').value = 0;
    document.getElementById('lote-input').value = product.lote || '';
    document.getElementById('fecha-vencimiento-input').value = product.fecha_vencimiento || '';

    // Calcular subtotal inicial
    calculateSubtotal();

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('cantidadModal'));
    modal.show();
}

function setupCantidadModal() {
    const cantidadInput = document.getElementById('cantidad-input');
    const precioInput = document.getElementById('precio-input');
    const descuentoInput = document.getElementById('descuento-input');
    const addButton = document.getElementById('add-product-btn');

    // Recalcular cuando cambien los valores
    [cantidadInput, precioInput, descuentoInput].forEach(input => {
        input.addEventListener('input', calculateSubtotal);
    });

    // Agregar producto
    addButton.addEventListener('click', addProductToCotizacion);
}

function calculateSubtotal() {
    const cantidad = parseFloat(document.getElementById('cantidad-input').value) || 0;
    const precio = parseFloat(document.getElementById('precio-input').value) || 0;
    const descuento = parseFloat(document.getElementById('descuento-input').value) || 0;

    const subtotal = (cantidad * precio) - descuento;
    document.getElementById('subtotal-input').value = subtotal.toFixed(2);
}

function addProductToCotizacion() {
    const cantidad = parseFloat(document.getElementById('cantidad-input').value);
    const precio = parseFloat(document.getElementById('precio-input').value);
    const descuento = parseFloat(document.getElementById('descuento-input').value) || 0;
    const lote = document.getElementById('lote-input').value;
    const fechaVencimiento = document.getElementById('fecha-vencimiento-input').value;

    if (!cantidad || cantidad <= 0) {
        alert('La cantidad debe ser mayor a 0');
        return;
    }

    if (!precio || precio < 0) {
        alert('El precio debe ser mayor o igual a 0');
        return;
    }

    const subtotal = (cantidad * precio) - descuento;

    const productToAdd = {
        id: Date.now(), // ID temporal único
        producto_id: currentEditingProduct.id,
        descripcion: [currentEditingProduct.nombre, formatProductDetails(currentEditingProduct.presentacion, currentEditingProduct.concentracion)].filter(Boolean).join(' '),
        cantidad: cantidad,
        precio_unitario: precio,
        descuento: descuento,
        subtotal: subtotal,
        lote: lote,
        fecha_vencimiento: fechaVencimiento
    };

    selectedProducts.push(productToAdd);
    updateProductsList();
    updateTotals();

    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('cantidadModal'));
    modal.hide();

    // Habilitar botón de guardar si hay productos y cliente
    checkSaveButtonState();
}

function updateProductsList() {
    const container = document.getElementById('selected-products');

    if (selectedProducts.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-shopping-basket fa-2x mb-2"></i>
                <p class="mb-0">No hay productos seleccionados</p>
            </div>
        `;
        return;
    }

    let html = '';
    selectedProducts.forEach((product, index) => {
        html += `
            <div class="border rounded p-2 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong>${product.descripcion}</strong>
                        <div class="small text-muted">
                            Cant: ${product.cantidad} × S/ ${product.precio_unitario.toFixed(2)}
                            ${product.descuento > 0 ? ` - S/ ${product.descuento.toFixed(2)}` : ''}
                            ${product.lote ? `<br>Lote: ${product.lote}` : ''}
                            ${product.fecha_vencimiento ? `<br>Venc: ${product.fecha_vencimiento}` : ''}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">S/ ${product.subtotal.toFixed(2)}</div>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="removeProduct(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function removeProduct(index) {
    selectedProducts.splice(index, 1);
    updateProductsList();
    updateTotals();
    checkSaveButtonState();
}

function updateTotals() {
    const subtotal = selectedProducts.reduce((sum, product) => sum + product.subtotal, 0);
    const descuentoGeneral = parseFloat(document.getElementById('descuento-general').value) || 0;
    const subtotalConDescuento = subtotal - descuentoGeneral;
    const igv = subtotalConDescuento * 0.18;
    const total = subtotalConDescuento + igv;

    document.getElementById('subtotal-display').textContent = `S/ ${subtotal.toFixed(2)}`;
    document.getElementById('descuento-display').textContent = `-S/ ${descuentoGeneral.toFixed(2)}`;
    document.getElementById('igv-display').textContent = `S/ ${igv.toFixed(2)}`;
    document.getElementById('total-display').textContent = `S/ ${total.toFixed(2)}`;
}

function setupDescuentoGeneral() {
    document.getElementById('descuento-general').addEventListener('input', updateTotals);
}

function setupClienteSearch() {
    const clienteSearch = document.getElementById('cliente-search');
    const modalClienteSearch = document.getElementById('modal-cliente-search');

    // Búsqueda en el input principal
    clienteSearch.addEventListener('input', function () {
        if (this.value.length >= 2) {
            searchClientes(this.value, false);
        }
    });

    // Búsqueda en el modal
    modalClienteSearch.addEventListener('input', function () {
        if (this.value.length >= 2) {
            searchClientes(this.value, true);
        } else {
            loadAllClientes();
        }
    });

    // Cargar clientes cuando se abre el modal
    document.getElementById('clienteModal').addEventListener('shown.bs.modal', function () {
        loadAllClientes();
    });
}

function searchClientes(query, inModal = false) {
    fetch(`/api/clientes/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(clientes => {
            clientesCache = clientes;
            if (inModal) {
                displayClientesInModal(clientes);
            } else {
                displayClientesSuggestions(clientes);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function loadAllClientes() {
    fetch('/api/clientes')
        .then(response => response.json())
        .then(clientes => {
            clientesCache = clientes;
            displayClientesInModal(clientes);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function displayClientesInModal(clientes) {
    const container = document.getElementById('modal-clientes-list');

    if (clientes.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No se encontraron clientes</p>';
        return;
    }

    let html = '<table class="table table-hover"><tbody>';
    clientes.forEach(cliente => {
        html += `
            <tr style="cursor: pointer;" onclick="selectCliente(${cliente.id})">
                <td>
                    <strong>${cliente.nombre}</strong>
                    <br><small class="text-muted">${cliente.tipo_doc} ${cliente.documento}</small>
                    ${cliente.telefono ? `<br><small class="text-muted">${cliente.telefono}</small>` : ''}
                </td>
            </tr>
        `;
    });
    html += '</tbody></table>';

    container.innerHTML = html;
}

function selectCliente(clienteId) {
    const cliente = clientesCache.find(c => c.id === clienteId);
    if (cliente) {
        document.getElementById('cliente_id').value = cliente.id;
        document.getElementById('cliente-search').value = cliente.nombre;
        document.getElementById('cliente-nombre').textContent = cliente.nombre;
        document.getElementById('cliente-documento').textContent = `${cliente.tipo_doc} ${cliente.documento}`;
        document.getElementById('cliente-info').classList.remove('d-none');

        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('clienteModal'));
        modal.hide();

        checkSaveButtonState();
    }
}

function setupSaveButton() {
    document.getElementById('save-cotizacion').addEventListener('click', saveCotizacion);
}

function checkSaveButtonState() {
    const saveButton = document.getElementById('save-cotizacion');
    const hasCliente = document.getElementById('cliente_id').value !== '';
    const hasProducts = selectedProducts.length > 0;

    saveButton.disabled = !(hasCliente && hasProducts);
}

function saveCotizacion() {
    const clienteId = document.getElementById('cliente_id').value;
    const vigenciaDias = document.getElementById('vigencia_dias') ? document.getElementById('vigencia_dias').value : 30;
    const observaciones = document.querySelector('textarea[name="observaciones"]').value;
    const descuentoGeneral = parseFloat(document.getElementById('descuento-general').value) || 0;

    if (!clienteId) {
        alert('Debe seleccionar un cliente');
        return;
    }

    if (selectedProducts.length === 0) {
        alert('Debe agregar al menos un producto');
        return;
    }

    // Calcular totales
    const subtotal = selectedProducts.reduce((sum, product) => sum + product.subtotal, 0);
    const subtotalConDescuento = subtotal - descuentoGeneral;
    const igv = subtotalConDescuento * 0.18;
    const total = subtotalConDescuento + igv;

    const data = {
        cliente_id: clienteId,
        productos: selectedProducts,
        subtotal: subtotal,
        descuento_total: descuentoGeneral,
        igv: igv,
        total: total,
        observaciones: observaciones,
        vigencia_dias: vigenciaDias
    };

    // Deshabilitar botón
    const saveButton = document.getElementById('save-cotizacion');
    saveButton.disabled = true;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

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
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message);
                    limpiarCotizacion();
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
            saveButton.disabled = false;
            saveButton.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Cotización';
        });
}

function limpiarCotizacion() {
    // Limpiar productos
    selectedProducts = [];
    updateProductsList();

    // Limpiar cliente
    document.getElementById('cliente_id').value = '';
    document.getElementById('cliente-search').value = '';
    document.getElementById('cliente-info').classList.add('d-none');

    // Limpiar campos
    document.getElementById('descuento-general').value = '0.00';
    document.querySelector('textarea[name="observaciones"]').value = '';

    // Limpiar búsqueda
    document.getElementById('search-product').value = '';
    showEmptySearch();

    // Actualizar totales y estado del botón
    updateTotals();
    checkSaveButtonState();
}
<script>
    // Variables globales para clientes
    clientesDisponibles = [];
    currentFilteredList = []; // New variable to track current results
    clienteSeleccionado = null;

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
        // Limpiar búsqueda al cerrar
        const input = document.getElementById('buscar-cliente-input');
        if (input) input.value = '';
    }

    function cargarListaClientes(termino = '') {
        const tbody = document.getElementById('lista-clientes-tbody');
        
        // Si no hay término, mostramos cargando inicialmente
        if (!termino) {
            tbody.innerHTML =
                '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #6c757d;">Cargando clientes...</td></tr>';
        }

        fetch(`{{ route('clientes.buscar-pos') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ q: termino })
        })
            .then(response => response.json())
            .then(data => {
                // Si no hay término, actualizamos la lista base
                if (!termino) {
                    clientesDisponibles = data;
                }
                currentFilteredList = data; // Set initial filtered list
                renderizarListaClientes(currentFilteredList);
            })
            .catch(error => {
                if (!termino) {
                    tbody.innerHTML =
                        '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #dc3545;">Error al cargar clientes</td></tr>';
                }
                console.error('Error:', error);
            });
    }

    // ... (renderizarListaClientes stays similar but I'll update it later if needed)
    
    function seleccionarCliente(cliente) {
        clienteSeleccionado = cliente;
        // Re-render only the current filtered state to maintain context
        renderizarListaClientes(currentFilteredList);
    }
    
    function buscarClientes() {
        const termino = document.getElementById('buscar-cliente-input').value.toLowerCase();

        if (termino.length === 0) {
            currentFilteredList = clientesDisponibles;
            renderizarListaClientes(currentFilteredList);
            return;
        }

        currentFilteredList = clientesDisponibles.filter(cliente =>
            (cliente.nombre && cliente.nombre.toLowerCase().includes(termino)) ||
            (cliente.numero_documento && cliente.numero_documento.includes(termino))
        );

        renderizarListaClientes(currentFilteredList);
    }

    function renderizarListaClientes(clientes) {
        const tbody = document.getElementById('lista-clientes-tbody');
        tbody.innerHTML = '';

        if (clientes.length === 0) {
            tbody.innerHTML =
                '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #6c757d;">No se encontraron clientes</td></tr>';
            return;
        }

        clientes.forEach((cliente, index) => {
            const tr = document.createElement('tr');
            tr.style.cursor = 'pointer';
            tr.onclick = (e) => {
                // Si el click es en los botones de acción, no seleccionar el cliente para venta
                if (e.target.closest('.btn-cliente-action')) return;
                seleccionarCliente(cliente);
            };

            // Agregar funcionalidad de doble click para confirmar selección
            tr.ondblclick = (e) => {
                if (e.target.closest('.btn-cliente-action')) return;
                seleccionarCliente(cliente);
                confirmarSeleccionCliente(); // Confirmar automáticamente
            };

            tr.onmouseover = () => tr.style.backgroundColor = '#f8f9fa';
            tr.onmouseout = () => tr.style.backgroundColor = clienteSeleccionado && clienteSeleccionado.id ===
                cliente.id ? '#e3f2fd' : 'white';

            if (clienteSeleccionado && clienteSeleccionado.id === cliente.id) {
                tr.style.backgroundColor = '#e3f2fd';
            }

            // Icono según tipo de documento
            const icono = cliente.tipo_documento === 'RUC' ? '🏢' :
                cliente.tipo_documento === 'CE' ? '🌍' : '👤';

            // Convertir debe a número para evitar errores
            const debeNumero = parseFloat(cliente.debe) || 0;
            const esContable = cliente.id === 999999 || (cliente.nombre && cliente.nombre.includes('CONTABLE'));

            tr.innerHTML = `
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">${icono}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; font-family: monospace;">${cliente.numero_documento}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">${cliente.nombre}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right; color: ${debeNumero > 0 ? '#dc3545' : '#28a745'}; font-weight: bold;">S/ ${debeNumero.toFixed(2)}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">
                        <div style="display: flex; gap: 5px; justify-content: center;">
                            ${debeNumero > 0 ? `
                                <button onclick="abrirModalCobrarDeuda(${cliente.id}, '${cliente.nombre.replace(/'/g, "\\'")}', ${debeNumero})" class="btn-cliente-action" title="Cobrar Deuda" style="padding: 5px 8px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                    <i class='bx bx-money'></i> Cobrar
                                </button>
                            ` : ''}
                            ${!esContable ? `
                                <button onclick="abrirModalEditarCliente(${cliente.id})" class="btn-cliente-action" title="Editar Cliente" style="padding: 5px 8px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                    <i class='bx bx-edit'></i> Editar
                                </button>
                            ` : ''}
                        </div>
                    </td>
                `;

            // Agregar tooltip para indicar la funcionalidad
            tr.title = 'Click para seleccionar, doble click para confirmar';

            tbody.appendChild(tr);
        });
    }

    // --- FUNCIONES DE ACCIONES DE CLIENTE ---

    function abrirModalEditarCliente(clienteId) {
        const cliente = clientesDisponibles.find(c => c.id === clienteId);
        if (!cliente) return;

        document.getElementById('edit-cliente-id').value = cliente.id;
        document.getElementById('edit-cliente-nombre').value = cliente.nombre;
        document.getElementById('edit-cliente-telefono').value = cliente.telefono || '';
        document.getElementById('edit-cliente-direccion').value = cliente.direccion || '';
        document.getElementById('edit-cliente-email').value = cliente.email || '';

        document.getElementById('modal-editar-cliente-pos').style.display = 'flex';
    }

    function cerrarModalEditarCliente() {
        document.getElementById('modal-editar-cliente-pos').style.display = 'none';
        document.getElementById('form-editar-cliente-pos').reset();
    }

    function guardarEdicionCliente(event) {
        event.preventDefault();

        const id = document.getElementById('edit-cliente-id').value;
        const datos = {
            telefono: document.getElementById('edit-cliente-telefono').value,
            direccion: document.getElementById('edit-cliente-direccion').value,
            email: document.getElementById('edit-cliente-email').value,
            _token: '{{ csrf_token() }}',
            _method: 'PUT'
        };

        const btn = document.getElementById('btn-guardar-edit-cliente');
        const originalText = btn.innerText;
        btn.innerText = 'Guardando...';
        btn.disabled = true;

        fetch(`{{ url('clientes') }}/${id}/pos-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(datos)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('✅ Cliente actualizado correctamente');
                cerrarModalEditarCliente();
                cargarListaClientes(); // Recargar lista para ver cambios
            } else {
                Swal.fire('Error', data.message || 'No se pudo actualizar el cliente', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Problema al conectar con el servidor', 'error');
        })
        .finally(() => {
            btn.innerText = originalText;
            btn.disabled = false;
        });
    }

    function abrirModalCobrarDeuda(clienteId, nombre, deuda) {
        document.getElementById('cobrar-cliente-id').value = clienteId;
        document.getElementById('cobrar-cliente-nombre').innerText = nombre;
        document.getElementById('cobrar-monto-total').innerText = 'S/ ' + parseFloat(deuda).toFixed(2);
        document.getElementById('cobrar-monto-pago').value = parseFloat(deuda).toFixed(2);
        document.getElementById('cobrar-monto-pago').max = parseFloat(deuda).toFixed(2);
        
        document.getElementById('modal-cobrar-deuda-pos').style.display = 'flex';
        
        setTimeout(() => {
            document.getElementById('cobrar-monto-pago').focus();
            document.getElementById('cobrar-monto-pago').select();
        }, 100);
    }

    function cerrarModalCobrarDeuda() {
        document.getElementById('modal-cobrar-deuda-pos').style.display = 'none';
        document.getElementById('form-cobrar-deuda-pos').reset();
    }

    function procesarCobroDeuda(event) {
        event.preventDefault();

        const clienteId = document.getElementById('cobrar-cliente-id').value;
        const monto = parseFloat(document.getElementById('cobrar-monto-pago').value);
        const metodo = document.getElementById('cobrar-metodo-pago').value;
        const observaciones = document.getElementById('cobrar-observaciones').value;

        if (!monto || monto <= 0) {
            Swal.fire('Atención', 'Ingrese un monto válido para el pago', 'warning');
            return;
        }

        const btn = document.getElementById('btn-confirmar-cobro');
        const originalText = btn.innerText;
        btn.innerText = 'Procesando...';
        btn.disabled = true;

        fetch(`{{ route('deudas.pagar-acumulado') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                cliente_id: clienteId,
                monto_pago: monto,
                metodo_pago: metodo,
                observaciones: observaciones
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                cerrarModalCobrarDeuda();
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Cobro realizado!',
                    text: data.message,
                    showCancelButton: true,
                    confirmButtonText: 'Imprimir Recibo',
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (data.batch_id) {
                            window.open(`{{ url('deudas/pago') }}/${data.pago_ids[0]}/comprobante`, '_blank');
                        }
                    }
                    cargarListaClientes();
                    
                    // Si el usuario tiene abierta la caja en otra pestaña o algo, esto refresca el estado en el POS
                    if (typeof checkCajaStatus === 'function') checkCajaStatus();
                });
            } else {
                Swal.fire('Error', data.message || 'No se pudo procesar el cobro', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Problema al conectar con el servidor', 'error');
        })
        .finally(() => {
            btn.innerText = originalText;
            btn.disabled = false;
        });
    }

    // --- FIN FUNCIONES DE ACCIONES ---

    function seleccionarCliente(cliente) {
        clienteSeleccionado = cliente;
        // Re-render only the current filtered state to maintain context
        renderizarListaClientes(currentFilteredList);
    }

    function seleccionarClienteSeleccionado() {
        if (!clienteSeleccionado) {
            alert('Seleccione un cliente de la lista');
            return;
        }

        clienteActual = clienteSeleccionado;
        
        // Helper para actualizar UI de forma segura
        const updateUI = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.innerText = text;
        };

        const displayStr = `${clienteActual.nombre} - ${clienteActual.numero_documento || ''}`;
        updateUI('footer-cliente', displayStr);
        updateUI('cliente-info-nombre', clienteActual.nombre);
        updateUI('cliente-info-documento', clienteActual.numero_documento || '');

        mostrarNotificacion(`Cliente seleccionado: ${clienteActual.nombre}`);
        cerrarBuscadorClientes();

        // Auto-guardar después de seleccionar cliente
        guardarVentaPersistente();
    }

    // Función para confirmar selección con doble click
    function confirmarSeleccionCliente() {
        if (!clienteSeleccionado) {
            return;
        }

        // Usar la misma lógica que seleccionarClienteSeleccionado
        clienteActual = clienteSeleccionado;

        // Actualizar UI del cliente en ambos lugares
        const displayStr = `${clienteActual.nombre} - ${clienteActual.numero_documento || ''}`;
        const footerEl = document.getElementById('footer-cliente');
        if (footerEl) footerEl.innerText = displayStr;

        const clienteNombre = document.getElementById('cliente-info-nombre');
        const clienteDoc = document.getElementById('cliente-info-documento');
        if (clienteNombre) clienteNombre.textContent = clienteActual.nombre;
        if (clienteDoc) clienteDoc.textContent = clienteActual.numero_documento || '';

        // Notificación especial para doble click
        mostrarNotificacion(`✅ Cliente confirmado (doble click): ${clienteActual.nombre}`);

        // Cerrar modal
        cerrarBuscadorClientes();

        // Auto-guardar después de seleccionar cliente
        guardarVentaPersistente();
    }

    let busquedaClienteTimeout = null;
    function buscarClientes() {
        const termino = document.getElementById('buscar-cliente-input').value.trim();

        if (termino.length === 0) {
            currentFilteredList = clientesDisponibles;
            renderizarListaClientes(currentFilteredList);
            return;
        }

        // Primero filtramos localmente para respuesta instantánea
        currentFilteredList = clientesDisponibles.filter(cliente =>
            (cliente.nombre && cliente.nombre.toLowerCase().includes(termino.toLowerCase())) ||
            (cliente.numero_documento && cliente.numero_documento.includes(termino))
        );
        renderizarListaClientes(currentFilteredList);

        // Si el término tiene al menos 2 caracteres, buscamos en el servidor (debounced)
        if (termino.length >= 2) {
            clearTimeout(busquedaClienteTimeout);
            busquedaClienteTimeout = setTimeout(() => {
                cargarListaClientes(termino);
            }, 500);
        }
    }

    function mostrarFormularioDNI() {
        document.getElementById('modal-cliente-dni').style.display = 'flex';
        setTimeout(() => {
            const input = document.getElementById('dni-input');
            if (input) {
                input.value = '';
                input.focus();
            }
        }, 100);
    }

    function cerrarModalClienteDNI() {
        document.getElementById('modal-cliente-dni').style.display = 'none';
        document.getElementById('dni-input').value = '';
    }

    /**
     * Versión simplificada para creación directa desde modal pequeño
     */
    function buscarPorDNIrapido() {
        const documento = document.getElementById('dni-input').value.trim();

        if (documento.length !== 8 && documento.length !== 11) {
            Swal.fire('Atención', 'El documento debe tener 8 dígitos (DNI) o 11 (RUC)', 'warning');
            return;
        }

        const btn = document.getElementById('btn-crear-reniec-directo');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creando...';
        btn.disabled = true;

        // Usamos el endpoint que automatiza la creación completa
        fetch(`{{ route('clientes.crear-desde-reniec') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    documento: documento
                })
            })
            .then(response => response.json())
            .then(res => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;

                if (res.success) {
                    clienteActual = res.data;
                    
                    // Actualizar UI
                    const footerCliente = document.getElementById('footer-cliente');
                    if (footerCliente) footerCliente.innerText = `${clienteActual.nombre} - ${clienteActual.numero_documento}`;

                    const clienteNombre = document.getElementById('cliente-info-nombre');
                    const clienteDoc = document.getElementById('cliente-info-documento');
                    if (clienteNombre) clienteNombre.textContent = clienteActual.nombre;
                    if (clienteDoc) clienteDoc.textContent = clienteActual.numero_documento;

                    mostrarNotificacion(`✅ Cliente ${res.message === 'El cliente ya existe en el sistema' ? 'encontrado' : 'registrado'}: ${clienteActual.nombre}`);
                    
                    cerrarModalClienteDNI();
                    cerrarBuscadorClientes();
                    
                    // Auto-guardar
                    guardarVentaPersistente();
                } else {
                    Swal.fire('Error', res.error || 'No se pudo crear el cliente', 'error');
                }
            })
            .catch(error => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                console.error('Error:', error);
                Swal.fire('Error', 'Problema al conectar con el servicio', 'error');
            });
    }

    function mostrarFormularioNuevoCliente() {
        document.getElementById('modal-nuevo-cliente').style.display = 'flex';
        setTimeout(() => {
            const input = document.getElementById('nro-documento');
            if (input) {
                input.value = '';
                input.focus();
            }
        }, 100);
    }

    function cerrarModalNuevoCliente() {
        document.getElementById('modal-nuevo-cliente').style.display = 'none';
        limpiarFormularioCliente();
    }

    function setTipoCliente(tipo) {
        document.getElementById('tipo-cliente').value = tipo;
        const btnPersona = document.getElementById('btn-tipo-persona');
        const btnEmpresa = document.getElementById('btn-tipo-empresa');
        const selectDoc = document.getElementById('tipo-documento');

        if (tipo === 'Particular') {
            btnPersona.style.background = '#eafaf1';
            btnPersona.style.borderColor = '#28a745';
            btnPersona.style.color = '#1e7e34';
            btnEmpresa.style.background = 'white';
            btnEmpresa.style.borderColor = '#dee2e6';
            btnEmpresa.style.color = '#6c757d';
            selectDoc.value = 'DNI';
        } else {
            btnEmpresa.style.background = '#eafaf1';
            btnEmpresa.style.borderColor = '#28a745';
            btnEmpresa.style.color = '#1e7e34';
            btnPersona.style.background = 'white';
            btnPersona.style.borderColor = '#dee2e6';
            btnPersona.style.color = '#6c757d';
            selectDoc.value = 'RUC';
        }
        ajustarPlaceholderDoc();
    }

    function ajustarPlaceholderDoc() {
        const type = document.getElementById('tipo-documento').value;
        const input = document.getElementById('nro-documento');
        if (type === 'DNI') {
            input.placeholder = 'Ingrese 8 dígitos';
            input.maxLength = 8;
        } else if (type === 'RUC') {
            input.placeholder = 'Ingrese 11 dígitos';
            input.maxLength = 11;
        } else {
            input.placeholder = 'Nro documento';
            input.maxLength = 15;
        }
    }

    function consultarServicioIdentidad() {
        const doc = document.getElementById('nro-documento').value.trim();
        if (doc.length !== 8 && doc.length !== 11) {
            Swal.fire('Atención', 'El número de documento debe tener 8 (DNI) u 11 (RUC) dígitos.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-search-reniec');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;

        fetch(`{{ route('pos.consultar-reniec') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    documento: doc
                })
            })
            .then(response => response.json())
            .then(res => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;

                if (res.success) {
                    const data = res.data;
                    document.getElementById('nombre-cliente').value = data.nombre || '';
                    document.getElementById('direccion-cliente').value = data.direccion || '';
                    if (res.is_ruc) {
                        setTipoCliente('Empresa');
                    } else {
                        setTipoCliente('Particular');
                    }
                    mostrarNotificacion('✅ Datos recuperados correctamente');
                } else {
                    Swal.fire('No encontrado', res.error || 'No se hallaron datos para este documento', 'info');
                }
            })
            .catch(error => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                console.error('Error:', error);
                Swal.fire('Error', 'Problema al conectar con el servicio de identidad', 'error');
            });
    }

    function limpiarFormularioCliente() {
        setTipoCliente('Particular');
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

        if (!nroDocumento || (tipoDocumento === 'DNI' && nroDocumento.length !== 8) || (tipoDocumento === 'RUC' &&
                nroDocumento.length !== 11)) {
            Swal.fire('Error', 'Número de documento inválido', 'error');
            return;
        }

        if (!nombre) {
            Swal.fire('Error', 'El nombre o razón social es requerido', 'error');
            return;
        }

        const datosCliente = {
            tipo_cliente: document.getElementById('tipo-cliente').value,
            tipo_documento: tipoDocumento,
            numero_documento: nroDocumento,
            nombre: nombre.toUpperCase(),
            direccion: document.getElementById('direccion-cliente').value.trim(),
            email: document.getElementById('email-cliente').value.trim(),
            telefono: document.getElementById('telefono-cliente').value.trim(),
            pos: true,
        };

        // Mostrar loading
        Swal.fire({
            title: 'Registrando cliente...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`{{ route('clientes.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(datosCliente)
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    clienteActual = data.data;
                    const footerCliente = document.getElementById('footer-cliente');
                    if (footerCliente) footerCliente.innerText =
                        `${clienteActual.nombre} - ${clienteActual.numero_documento}`;

                    const clienteNombre = document.getElementById('cliente-info-nombre');
                    const clienteDoc = document.getElementById('cliente-info-documento');
                    if (clienteNombre) clienteNombre.textContent = clienteActual.nombre;
                    if (clienteDoc) clienteDoc.textContent = clienteActual.numero_documento;

                    mostrarNotificacion(`✅ Cliente registrado: ${clienteActual.nombre}`);
                    cerrarModalNuevoCliente();
                    cerrarBuscadorClientes();

                    // Auto-guardar después de registrar cliente
                    guardarVentaPersistente();
                } else {
                    Swal.fire('Error', data.message || 'Error al registrar cliente', 'error');
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error', 'Error de conexión al registrar cliente', 'error');
                console.error('Error:', error);
            });
    }

    // Función para crear/obtener cliente contable en base de datos
    function crearClienteContable() {
        // Intentar obtener cliente contable existente primero y devolver la promesa
        return obtenerClienteContableExistente();
    }

    // Función para obtener cliente contable existente (id=999999, global para todas las empresas)
    function obtenerClienteContableExistente() {
        return new Promise((resolve, reject) => {
            fetch(`{{ route('clientes.cliente-contable') }}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data && data.id) {
                        // Cliente contable encontrado
                        clienteActual = {
                            id: data.id,
                            tipo_documento: data.tipo_documento,
                            numero_documento: data.numero_documento,
                            documento: data.numero_documento,
                            nombre: data.nombre,
                            direccion: data.direccion || '',
                            telefono: data.telefono || '',
                            email: data.email || ''
                        };

                        // Actualizar UI de forma segura
                        const footerCliente = document.getElementById('footer-cliente');
                        if (footerCliente) footerCliente.innerText = `${clienteActual.nombre} - ${clienteActual.numero_documento || ''}`;
                        
                        const clienteNombre = document.getElementById('cliente-info-nombre');
                        const clienteDoc = document.getElementById('cliente-info-documento');
                        if (clienteNombre) clienteNombre.textContent = clienteActual.nombre;
                        if (clienteDoc) clienteDoc.textContent = clienteActual.numero_documento || '';

                        mostrarNotificacion('✅ Cliente contable configurado');
                        console.log('Cliente contable encontrado:', clienteActual);
                        resolve(clienteActual);
                    } else {
                        console.error('Cliente contable (id=999999) no encontrado en BD');
                        clienteActual = clienteTemporalContable();
                        resolve(clienteActual);
                    }
                })
                .catch(error => {
                    console.error('Error buscando cliente contable:', error);
                    clienteActual = clienteTemporalContable();
                    resolve(clienteActual);
                });
        });
    }

    // Función para crear cliente contable temporal (fallback)
    function clienteTemporalContable() {
        const clienteTemp = {
            id: 999999, // ID temporal alto para evitar conflictos
            documento: '00000000',
            numero_documento: '00000000',
            nombre: 'CLIENTE CONTABLE',
            direccion: 'SIN DIRECCION',
            telefono: '',
            email: ''
        };

        // Actualizar UI
        const clienteNombre = document.getElementById('cliente-info-nombre');
        const clienteDoc = document.getElementById('cliente-info-documento');
        if (clienteNombre) clienteNombre.textContent = 'CLIENTE CONTABLE';
        if (clienteDoc) clienteDoc.textContent = '00000000';

        const footerCliente = document.getElementById('footer-cliente');
        if (footerCliente) {
            footerCliente.innerHTML = `
                <div class="client-info-container">
                    <div class="client-info-text" id="cliente-info-nombre">CLIENTE CONTABLE</div>
                    <div id="cliente-info-documento" class="client-doc-text">00000000</div>
                </div>
            `;
        }

        console.log('Cliente contable temporal configurado');
        mostrarNotificacion('⚠️ Cliente contable temporal configurado');
        return clienteTemp;
    }

    // Función para usar cliente contable directamente
    async function usarClienteContado() {
        // Crear/obtener cliente contable de la base de datos
        mostrarNotificacion('🔄 Configurando cliente contable...');
        await crearClienteContable();

        // Cerrar modal si está abierto
        const modal = document.getElementById('modal-buscar-clientes');
        if (modal) modal.style.display = 'none';

        console.log('Cliente contable configurado por usuario.');
    }

</script>
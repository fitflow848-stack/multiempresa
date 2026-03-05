<script>
    // Variables globales para clientes
    clientesDisponibles = [];
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
    }

    function cargarListaClientes() {
        const tbody = document.getElementById('lista-clientes-tbody');
        tbody.innerHTML =
            '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #6c757d;">Cargando clientes...</td></tr>';

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
                tbody.innerHTML =
                    '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #dc3545;">Error al cargar clientes</td></tr>';
                console.error('Error:', error);
            });
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
            tr.onclick = () => seleccionarCliente(cliente);

            // Agregar funcionalidad de doble click para confirmar selección
            tr.ondblclick = () => {
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

            tr.innerHTML = `
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">${icono}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; font-family: monospace;">${cliente.numero_documento}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">${cliente.nombre}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right; color: ${debeNumero > 0 ? '#dc3545' : '#28a745'};">S/ ${debeNumero.toFixed(2)}</td>
                `;

            // Agregar tooltip para indicar la funcionalidad
            tr.title = 'Click para seleccionar, doble click para confirmar';

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
        document.getElementById('footer-cliente').innerText = `${clienteActual.nombre} - ${clienteActual.numero_documento}`;
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
        document.getElementById('footer-cliente').innerText = `${clienteActual.nombre} - ${clienteActual.numero_documento}`;

        // Actualizar también en el área de cliente si existe
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
            body: JSON.stringify({
                dni: dni
            })
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
                    document.getElementById('footer-cliente').innerText = `${clienteActual.nombre} - ${clienteActual.numero_documento}`;
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
            tipo_cliente: document.getElementById('tipo-cliente').value,
            tipo_documento: tipoDocumento,
            numero_documento: nroDocumento,
            nombre: nombre.toUpperCase(),
            direccion: document.getElementById('direccion-cliente').value.trim(),
            email: document.getElementById('email-cliente').value.trim(),
            telefono: document.getElementById('telefono-cliente').value.trim(),
            pos: true,
        };

        mostrarNotificacion('Registrando cliente...');

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
                if (data.success) {
                    clienteActual = data.data;
                    document.getElementById('footer-cliente').innerText = `${clienteActual.nombre} - ${clienteActual.numero_documento}`;
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

    // Función para crear/obtener cliente contable en base de datos
    function crearClienteContable() {
        // Intentar obtener cliente contable existente primero
        obtenerClienteContableExistente();
    }

    // Función para obtener cliente contable existente (id=999999, global para todas las empresas)
    function obtenerClienteContableExistente() {
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

                    // Actualizar UI
                    document.getElementById('footer-cliente').innerText = `${clienteActual.nombre} - ${clienteActual.numero_documento}`;
                    const clienteNombre = document.getElementById('cliente-info-nombre');
                    const clienteDoc = document.getElementById('cliente-info-documento');
                    if (clienteNombre) clienteNombre.textContent = clienteActual.nombre;
                    if (clienteDoc) clienteDoc.textContent = clienteActual.numero_documento;

                    mostrarNotificacion('✅ Cliente contable configurado');
                    console.log('Cliente contable encontrado:', clienteActual);
                } else {
                    console.error('Cliente contable (id=999999) no encontrado en BD');
                    clienteTemporalContable();
                }
            })
            .catch(error => {
                console.error('Error buscando cliente contable:', error);
                clienteTemporalContable();
            });
    }

    // Función para crear nuevo cliente contable en base de datos
    function crearNuevoClienteContable() {
        const datosClienteContable = {
            tipo_documento: 'dni',
            numero_documento: '00000000',
            nombre: 'CLIENTE CONTABLE',
            direccion: 'SIN DIRECCION',
            telefono: '',
            email: ''
        };

        fetch('{{ route('clientes.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(datosClienteContable)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cliente contable creado exitosamente
                    clienteActual = {
                        id: data.cliente ? data.cliente.id : data.data.id,
                        documento: '00000000',
                        nombre: 'CLIENTE CONTABLE',
                        direccion: 'SIN DIRECCION',
                        telefono: '',
                        email: ''
                    };

                    // Actualizar UI
                    document.getElementById('cliente-info-nombre').textContent = 'CLIENTE CONTABLE';
                    document.getElementById('cliente-info-documento').textContent = '00000000';

                    console.log('Cliente contable creado exitosamente:', clienteActual);
                    mostrarNotificacion('✅ Cliente contable creado y configurado');
                } else {
                    // Error al crear, usar cliente temporal para continuar
                    console.error('Error creando cliente contable:', data.message || 'Error desconocido');
                    clienteTemporalContable();
                }
            })
            .catch(error => {
                console.error('Error de conexión creando cliente contable:', error);
                clienteTemporalContable();
            });
    }

    // Función para crear cliente contable temporal (fallback)
    function clienteTemporalContable() {
        clienteActual = {
            id: 999999, // ID temporal alto para evitar conflictos
            documento: '00000000',
            nombre: 'CLIENTE CONTABLE',
            direccion: 'SIN DIRECCION',
            telefono: '',
            email: ''
        };

        // Actualizar UI
        document.getElementById('cliente-info-nombre').textContent = 'CLIENTE CONTABLE';
        document.getElementById('cliente-info-documento').textContent = '00000000';

        console.log('Cliente contable temporal configurado');
        mostrarNotificacion('⚠️ Cliente contable temporal configurado');
    }

    // Función para usar cliente contable directamente
    function usarClienteContado() {
        // Crear/obtener cliente contable de la base de datos
        crearClienteContable();

        // Cerrar modal si está abierto
        const modal = document.getElementById('modal-buscar-clientes');
        if (modal) modal.style.display = 'none';

        mostrarNotificacion('🔄 Configurando cliente contable...');

        console.log('Configurando cliente contable...');
    }

</script>
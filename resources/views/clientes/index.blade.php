@extends('layout.app')

@section('content')
@section('title', 'Gestión de Clientes')
    <style>
        .clientes-header {
            background: #17a2b8;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
        }

        .clientes-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .cliente-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-crear-cliente {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
        }

        .btn-crear-cliente:hover {
            background: #218838;
            color: white;
        }

        .btn-buscar-dni {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-buscar-dni:hover {
            background: #0056b3;
        }

        .clientes-table {
            width: 100%;
            border-collapse: collapse;
        }

        .clientes-table th,
        .clientes-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }

        .clientes-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .clientes-table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        /* Estilos para filas clickeables */
        .clientes-table tbody tr {
            transition: all 0.2s ease;
        }

        .clientes-table tbody tr:hover {
            background-color: #f8f9fa !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Indicador visual para modo POS */
        .pos-mode-row {
            position: relative;
        }

        .pos-mode-row::before {
            content: '🛒';
            position: absolute;
            left: -20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            opacity: 0.6;
        }

        /* Tooltip personalizado */
        .clientes-table tbody tr[title]:hover::after {
            content: attr(title);
            position: absolute;
            background: #333;
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 11px;
            z-index: 1000;
            white-space: nowrap;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            animation: fadeInTooltip 0.3s ease forwards;
        }

        @keyframes fadeInTooltip {
            to {
                opacity: 1;
            }
        }
    </style>

    <div class="cliente-card">
        <!-- Header -->
        <div class="clientes-header">
            <div>
                <h3 style="margin: 0; font-size: 18px;">📋 Listado de Clientes</h3>
                <small style="opacity: 0.9;">Gestión de clientes de {{ $company->name }}</small>
                <div id="modo-pos-indicator" style="background: #28a745; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; margin-top: 5px; display: none;">
                    🛒 Seleccionando cliente para POS
                </div>
                <div id="modo-cotizacion-indicator" style="background: #17a2b8; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; margin-top: 5px; display: none;">
                    📋 Seleccionando cliente para COTIZACIÓN
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <span style="background: rgba(255,255,255,0.2); padding: 6px 12px; border-radius: 20px; font-size: 12px;">
                    <span id="total-clientes">0</span> clientes
                </span>
            </div>
        </div>

        <!-- Acciones -->
        <div class="clientes-actions">
            <a href="{{ route('clientes.create') }}" class="btn-crear-cliente">
                ➕ Nuevo cliente
            </a>
            <button onclick="mostrarModalBuscarDNI()" class="btn-buscar-dni">
                🔍 Cliente Contado
            </button>
            <button onclick="mostrarModalReniec()" class="btn-buscar-dni" style="background: #28a745;">
                🏛️ RENIEC
            </button>
            <div style="margin-left: auto; display: flex; gap: 10px; align-items: center;">
                <input type="text" id="buscar-input" placeholder="Buscar cliente..." 
                       style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 250px;">
                <button onclick="filtrarClientes()" class="btn-buscar-dni">Buscar</button>
            </div>
        </div>

        <!-- Tabla de clientes -->
        <div style="overflow-x: auto;">
            <table class="clientes-table" id="clientes-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 10%;">Documento</th>
                        <th style="width: 35%;">Cliente</th>
                        <th style="width: 15%;">Teléfono</th>
                        <th style="width: 15%;">Deuda</th>
                        <th style="width: 8%;">Estado</th>
                        <th style="width: 12%;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="clientes-tbody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6c757d;">
                            Cargando clientes...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de búsqueda por DNI -->
    <div id="modal-buscar-dni" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: none; justify-content: center; align-items: center;">
        <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 500px; font-family: Arial, sans-serif;">
            <h3 style="margin: 0 0 20px 0; color: #007bff; text-align: center;">🔍 Buscar Cliente por DNI</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Número de DNI:</label>
                <input type="text" id="dni-buscar" placeholder="Ingrese DNI (8 dígitos)" maxlength="8" 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" 
                       onkeypress="if(event.key==='Enter') buscarClientePorDNI()">
            </div>
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button onclick="buscarClientePorDNI()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">🔍 Buscar</button>
                <button onclick="cerrarModalBuscarDNI()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal de RENIEC -->
    <div id="modal-reniec" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; display: none; justify-content: center; align-items: center;">
        <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 500px; font-family: Arial, sans-serif;">
            <h3 style="margin: 0 0 20px 0; color: #28a745; text-align: center;">🏛️ Crear Cliente desde RENIEC</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #495057;">Número de Documento:</label>
                <input type="text" id="documento-reniec" placeholder="DNI (8 dígitos) o RUC (11 dígitos)" maxlength="11" 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" 
                       onkeypress="if(event.key==='Enter') crearClienteDesdeReniec()"
                       oninput="validarTipoDocumento()">
                <small id="tipo-documento-info" style="color: #6c757d; font-size: 12px;">Ingrese 8 dígitos para DNI o 11 para RUC</small>
            </div>
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button onclick="crearClienteDesdeReniec()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">🏛️ Crear Cliente</button>
                <button onclick="cerrarModalReniec()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
            </div>
            <div id="reniec-loading" style="display: none; text-align: center; margin-top: 15px; color: #28a745; font-weight: 600;">🔄 Consultando RENIEC/SUNAT...</div>
        </div>
    </div>

    <script>
        let clientes = [];

        document.addEventListener('DOMContentLoaded', function() {
            cargarClientes();
            
            // Verificar si viene desde el POS
            if (document.referrer.includes('pos') || sessionStorage.getItem('navegandoDesdePOS')) {
                const indicator = document.getElementById('modo-pos-indicator');
                indicator.style.display = 'block';
                indicator.innerHTML = '🛒 Seleccionando cliente para POS - <small>Doble click en cualquier fila para selección rápida</small>';
                sessionStorage.setItem('navegandoDesdePOS', 'true');
            }
            
            // Verificar si viene desde COTIZACIONES
            if (document.referrer.includes('cotizaciones') || sessionStorage.getItem('navegandoDesdeCotizacion')) {
                const indicator = document.getElementById('modo-cotizacion-indicator');
                indicator.style.display = 'block';
                indicator.innerHTML = '📋 Seleccionando cliente para COTIZACIÓN - <small>Doble click en cualquier fila para selección rápida</small>';
                sessionStorage.setItem('navegandoDesdeCotizacion', 'true');
            }
        });

        function cargarClientes() {
            const tbody = document.getElementById('clientes-tbody');
            
            fetch(`{{ route('clientes.data') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                clientes = data.data;
                renderClientes(clientes);
                document.getElementById('total-clientes').textContent = clientes.length;
            })
            .catch(error => {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #dc3545;">Error al cargar clientes</td></tr>';
                console.error('Error:', error);
            });
        }

        function renderClientes(clientesData) {
            const tbody = document.getElementById('clientes-tbody');
            
            if (clientesData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #6c757d;">No se encontraron clientes</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            clientesData.forEach((cliente, index) => {
                const tr = document.createElement('tr');
                
                // Agregar estilos y eventos para interacción
                tr.style.cursor = 'pointer';
                
                // Tooltip dinámico dependiendo del contexto
                const esModoPos = sessionStorage.getItem('navegandoDesdePOS');
                if (esModoPos) {
                    tr.title = '🛒 Doble click para seleccionar y regresar al POS';
                    tr.classList.add('pos-mode-row');
                } else {
                    tr.title = 'Doble click para seleccionar cliente e ir al POS';
                }
                
                // Eventos de hover para feedback visual
                tr.onmouseover = function() {
                    this.style.backgroundColor = '#f8f9fa';
                };
                tr.onmouseout = function() {
                    this.style.backgroundColor = 'white';
                };
                
                // Función de doble click para seleccionar cliente directamente
                tr.ondblclick = function(e) {
                    e.preventDefault();
                    
                    // Verificar si viene desde el POS
                    if (sessionStorage.getItem('navegandoDesdePOS')) {
                        // Seleccionar cliente directamente al POS con doble click
                        seleccionarClienteDirectoPOS(cliente.id, cliente.nombre);
                    } 
                    // Verificar si viene desde COTIZACIONES
                    else if (sessionStorage.getItem('navegandoDesdeCotizacion')) {
                        // Seleccionar cliente directamente para cotización con doble click
                        seleccionarClienteDirectoCotizacion(cliente.id, cliente.nombre);
                    } 
                    else {
                        // Si no viene del POS ni cotizaciones, mostrar opción
                        if (confirm(`¿Seleccionar "${cliente.nombre}" para ir al POS?`)) {
                            seleccionarParaPOS(cliente.id, cliente.nombre);
                        }
                    }
                };
                
                // Icono según tipo de documento
                const icono = cliente.tipo_documento === 'RUC' ? '🏢' : 
                             cliente.tipo_documento === 'CE' ? '🌍' : '👤';
                
                tr.innerHTML = `
                    <td>${icono}</td>
                    <td style="font-family: monospace;">${cliente.numero_documento}</td>
                    <td>
                        <div style="font-weight: 500;">${cliente.nombre}</div>
                        ${cliente.email ? `<small style="color: #6c757d;">${cliente.email}</small>` : ''}
                    </td>
                    <td>${cliente.telefono || '-'}</td>
                    <td class="${cliente.debe > 0 ? 'text-danger' : 'text-success'}">
                        ${cliente.debe > 0 ? 
                            `<a href="/deudas?cliente_id=${cliente.id}" style="color: inherit; text-decoration: none;" title="Ver deudas del cliente">
                                <i class="fa fa-credit-card"></i> S/ ${parseFloat(cliente.debe).toFixed(2)}
                            </a>` :
                            `S/ ${parseFloat(cliente.debe).toFixed(2)}`
                        }
                    </td>
                    <td>
                        <span class="badge ${cliente.estado === 'Activo' ? 'badge-success' : 'badge-danger'}">
                            ${cliente.estado}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button onclick="seleccionarParaPOS(${cliente.id}, '${cliente.nombre}')" 
                                    style="padding: 4px 8px; background: #28a745; color: white; border: none; border-radius: 3px; font-size: 11px; cursor: pointer;"
                                    title="Seleccionar para POS">🛒</button>
                            <a href="/clientes/${cliente.id}" style="padding: 4px 8px; background: #17a2b8; color: white; text-decoration: none; border-radius: 3px; font-size: 11px;">Ver</a>
                            <a href="/clientes/${cliente.id}/edit" style="padding: 4px 8px; background: #ffc107; color: black; text-decoration: none; border-radius: 3px; font-size: 11px;">Editar</a>
                        </div>
                    </td>
                `;
                
                tbody.appendChild(tr);
            });
        }

        function filtrarClientes() {
            const termino = document.getElementById('buscar-input').value.toLowerCase();
            
            if (termino === '') {
                renderClientes(clientes);
                return;
            }
            
            const clientesFiltrados = clientes.filter(cliente => 
                cliente.nombre.toLowerCase().includes(termino) ||
                cliente.numero_documento.includes(termino)
            );
            
            renderClientes(clientesFiltrados);
        }

        // Filtro en tiempo real
        document.getElementById('buscar-input').addEventListener('keyup', filtrarClientes);

        function mostrarModalBuscarDNI() {
            document.getElementById('modal-buscar-dni').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('dni-buscar').focus();
            }, 100);
        }

        function cerrarModalBuscarDNI() {
            document.getElementById('modal-buscar-dni').style.display = 'none';
            document.getElementById('dni-buscar').value = '';
        }

        function buscarClientePorDNI() {
            const dni = document.getElementById('dni-buscar').value.trim();
            
            if (dni.length !== 8) {
                alert('El DNI debe tener 8 dígitos');
                return;
            }
            
            if (!/^\d{8}$/.test(dni)) {
                alert('El DNI debe contener solo números');
                return;
            }
            
            // Redirigir al formulario de creación con DNI prellenado
            window.location.href = `{{ route('clientes.create') }}?dni=${dni}`;
        }

        function mostrarModalReniec() {
            document.getElementById('modal-reniec').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('documento-reniec').focus();
            }, 100);
        }

        function cerrarModalReniec() {
            document.getElementById('modal-reniec').style.display = 'none';
            document.getElementById('documento-reniec').value = '';
            document.getElementById('tipo-documento-info').textContent = 'Ingrese 8 dígitos para DNI o 11 para RUC';
            document.getElementById('tipo-documento-info').style.color = '#6c757d';
            document.getElementById('reniec-loading').style.display = 'none';
        }

        function validarTipoDocumento() {
            const documento = document.getElementById('documento-reniec').value;
            const info = document.getElementById('tipo-documento-info');
            
            if (documento.length === 8 && /^\d{8}$/.test(documento)) {
                info.textContent = '✅ DNI válido - Se consultará RENIEC';
                info.style.color = '#28a745';
            } else if (documento.length === 11 && /^\d{11}$/.test(documento)) {
                info.textContent = '✅ RUC válido - Se consultará SUNAT';
                info.style.color = '#28a745';
            } else if (documento.length > 0) {
                info.textContent = '❌ Formato inválido';
                info.style.color = '#dc3545';
            } else {
                info.textContent = 'Ingrese 8 dígitos para DNI o 11 para RUC';
                info.style.color = '#6c757d';
            }
        }

        function crearClienteDesdeReniec() {
            const documento = document.getElementById('documento-reniec').value.trim();
            
            if (documento.length !== 8 && documento.length !== 11) {
                alert('Debe ingresar un DNI (8 dígitos) o RUC (11 dígitos) válido');
                return;
            }
            
            if (!/^\d{8}$|^\d{11}$/.test(documento)) {
                alert('El documento debe contener solo números');
                return;
            }
            
            document.getElementById('reniec-loading').style.display = 'block';
            
            fetch(`{{ route('clientes.crear-desde-reniec') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ documento: documento })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('reniec-loading').style.display = 'none';
                
                if (data.success) {
                    alert(`✅ Cliente creado exitosamente:\n\n${data.data.nombre}\nDocumento: ${data.data.numero_documento}`);
                    cerrarModalReniec();
                    cargarClientes(); // Recargar la lista
                } else {
                    alert('❌ Error: ' + (data.error || 'No se pudo crear el cliente'));
                }
            })
            .catch(error => {
                document.getElementById('reniec-loading').style.display = 'none';
                alert('❌ Error de conexión: No se pudo consultar los datos');
                console.error('Error:', error);
            });
        }

        // Función para seleccionar cliente desde la lista hacia el POS
        function seleccionarParaPOS(clienteId, clienteNombre) {
            const cliente = clientes.find(c => c.id == clienteId);
            if (!cliente) {
                alert('Cliente no encontrado');
                return;
            }
            
            // Guardar cliente seleccionado en sessionStorage para que el POS lo detecte
            const clienteParaPOS = {
                id: cliente.id,
                tipo_documento: cliente.tipo_documento,
                numero_documento: cliente.numero_documento,
                nombre: cliente.nombre,
                direccion: cliente.direccion || '',
                email: cliente.email || '',
                telefono: cliente.telefono || '',
                debe: parseFloat(cliente.debe) || 0
            };
            
            sessionStorage.setItem('clienteSeleccionadoPOS', JSON.stringify(clienteParaPOS));
            
            // Limpiar indicador de navegación POS
            sessionStorage.removeItem('navegandoDesdePOS');
            
            // Confirmar y redirigir al POS
            if (confirm(`¿Seleccionar "${clienteNombre}" para la venta en el POS?`)) {
                window.location.href = '{{ route("pos.index") }}';
            }
        }

        // Función específica para doble click cuando viene desde POS
        function seleccionarClienteDirectoPOS(clienteId, clienteNombre) {
            const cliente = clientes.find(c => c.id == clienteId);
            if (!cliente) {
                alert('Cliente no encontrado');
                return;
            }
            
            // Guardar cliente seleccionado en sessionStorage para que el POS lo detecte
            const clienteParaPOS = {
                id: cliente.id,
                tipo_documento: cliente.tipo_documento,
                numero_documento: cliente.numero_documento,
                nombre: cliente.nombre,
                direccion: cliente.direccion || '',
                email: cliente.email || '',
                telefono: cliente.telefono || '',
                debe: parseFloat(cliente.debe) || 0
            };
            
            sessionStorage.setItem('clienteSeleccionadoPOS', JSON.stringify(clienteParaPOS));
            
            // Limpiar indicador de navegación POS
            sessionStorage.removeItem('navegandoDesdePOS');
            
            // Feedback visual inmediato
            const Toast = {
                fire: function(config) {
                    const toast = document.createElement('div');
                    toast.innerHTML = `
                        <div style="position: fixed; top: 20px; right: 20px; background: #28a745; color: white; 
                                    padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                                    z-index: 9999; font-weight: 600; font-size: 14px;">
                            ✅ ${config.title}: ${config.text}
                        </div>
                    `;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                }
            };
            
            Toast.fire({
                title: 'Cliente seleccionado',
                text: clienteNombre
            });
            
            // Redirigir al POS automáticamente después de un pequeño delay
            setTimeout(() => {
                window.location.href = '{{ route("pos.index") }}';
            }, 1000);
        }

        // Función específica para doble click cuando viene desde COTIZACIONES
        function seleccionarClienteDirectoCotizacion(clienteId, clienteNombre) {
            const cliente = clientes.find(c => c.id == clienteId);
            if (!cliente) {
                alert('Cliente no encontrado');
                return;
            }
            
            // Guardar cliente seleccionado en sessionStorage para que la cotización lo detecte
            const clienteParaCotizacion = {
                id: cliente.id,
                tipo_doc: cliente.tipo_documento,
                documento: cliente.numero_documento,
                nombre: cliente.nombre,
                direccion: cliente.direccion || '',
                email: cliente.email || '',
                telefono: cliente.telefono || '',
                debe: parseFloat(cliente.debe) || 0
            };
            
            sessionStorage.setItem('clienteSeleccionadoCotizacion', JSON.stringify(clienteParaCotizacion));
            
            // Limpiar indicador de navegación desde cotizaciones
            sessionStorage.removeItem('navegandoDesdeCotizacion');
            
            // Feedback visual inmediato
            const Toast = {
                fire: function(config) {
                    const toast = document.createElement('div');
                    toast.innerHTML = `
                        <div style="position: fixed; top: 20px; right: 20px; background: #17a2b8; color: white; 
                                    padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                                    z-index: 9999; font-weight: 600; font-size: 14px;">
                            📋 ${config.title}: ${config.text}
                        </div>
                    `;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                }
            };
            
            Toast.fire({
                title: 'Cliente seleccionado para cotización',
                text: clienteNombre
            });
            
            // Redirigir a cotizaciones automáticamente después de un pequeño delay
            setTimeout(() => {
                window.location.href = '{{ route("cotizaciones.create") }}';
            }, 1000);
        }
    </script>
@endsection
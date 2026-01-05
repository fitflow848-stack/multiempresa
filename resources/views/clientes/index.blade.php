@extends('layout.app')

@section('content')
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
                        <th style="width: 15%;">Crédito</th>
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
                document.getElementById('modo-pos-indicator').style.display = 'block';
                sessionStorage.setItem('navegandoDesdePOS', 'true');
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
                        S/ ${parseFloat(cliente.debe).toFixed(2)}
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
    </script>
@endsection
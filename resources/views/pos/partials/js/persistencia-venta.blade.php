<script>
    let ticket = [];
    let currentProduct = null;
    let autoSaveInterval = null;
    const AUTOSAVE_KEY = 'ventaPersistentePOS';
    const CLIENTE_KEY = 'clientePersistentePOS';

    // ========== SISTEMA DE PERSISTENCIA DE VENTAS ==========

    // Función para iniciar auto-guardado
    function iniciarAutoGuardado() {
        if (autoSaveInterval) {
            clearInterval(autoSaveInterval);
        }

        autoSaveInterval = setInterval(() => {
            if (ticket.length > 0 || (clienteActual && clienteActual.id)) {
                guardarVentaPersistente();
            }
        }, 5000); // Guardar cada 5 segundos
    }

    // Función para guardar venta de forma persistente
    function guardarVentaPersistente() {
        try {
            const ventaData = {
                ticket: ticket,
                cliente: clienteActual,
                timestamp: Date.now(),
                fecha: new Date().toISOString()
            };

            localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(ventaData));

            // También en sessionStorage como backup
            sessionStorage.setItem('ticketGuardadoPOS', JSON.stringify(ticket));
            if (clienteActual && clienteActual.id) {
                sessionStorage.setItem('clienteGuardadoPOS', JSON.stringify(clienteActual));
            }

            // console.log('Venta guardada automáticamente:', ventaData);
        } catch (error) {
            console.error('Error guardando venta persistente:', error);
        }
    }

    // Función para restaurar venta persistente
    function restaurarVentaPersistente() {
        try {
            const ventaGuardada = localStorage.getItem(AUTOSAVE_KEY);

            if (ventaGuardada) {
                const ventaData = JSON.parse(ventaGuardada);

                // Verificar que la venta no sea muy antigua (más de 24 horas)
                const tiempoTranscurrido = Date.now() - ventaData.timestamp;
                const unDia = 24 * 60 * 60 * 1000;

                if (tiempoTranscurrido < unDia && ventaData.ticket && ventaData.ticket.length > 0) {
                    // Restaurar ticket
                    ticket = ventaData.ticket || [];

                    // Restaurar cliente
                    if (ventaData.cliente && ventaData.cliente.id) {
                        clienteActual = ventaData.cliente;

                        // Actualizar UI del cliente
                        setTimeout(() => {
                            const clienteNombre = document.getElementById('cliente-info-nombre');
                            const clienteDoc = document.getElementById('cliente-info-documento');
                            if (clienteNombre) clienteNombre.textContent = clienteActual.nombre;
                            if (clienteDoc) clienteDoc.textContent = clienteActual.documento || '';
                        }, 100);
                    }

                    // Renderizar ticket restaurado
                    setTimeout(() => {
                        renderTicket();
                        const fechaFormateada = new Date(ventaData.fecha).toLocaleString('es-PE');
                        mostrarNotificacion(
                            `💾 Venta restaurada: ${ticket.length} productos (guardado: ${fechaFormateada})`
                        );
                    }, 200);

                    // console.log('Venta persistente restaurada:', ventaData);
                } else if (tiempoTranscurrido >= unDia) {
                    // Limpiar venta muy antigua
                    localStorage.removeItem(AUTOSAVE_KEY);
                    console.log('Venta antigua eliminada (más de 24 horas)');
                }
            }
        } catch (error) {
            console.error('Error restaurando venta persistente:', error);
            // Limpiar datos corruptos
            localStorage.removeItem(AUTOSAVE_KEY);
        }
    }

    // Sistema de venta persistente
    function inicializarSistemaVentaPersistente() {
        // Restaurar venta persistente si existe
        restaurarVentaPersistente();

        // Configurar auto-guardado cada 5 segundos
        iniciarAutoGuardado();

        // Guardar antes de cerrar/cambiar ventana
        window.addEventListener('beforeunload', function(e) {
            if (ticket.length > 0) {
                guardarVentaPersistente();
            }
        });

        // Guardar al cambiar de pestaña/ventana
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden' && ticket.length > 0) {
                guardarVentaPersistente();
            }
        });
    }

    // ========== FIN SISTEMA DE PERSISTENCIA ==========

    // Función para limpiar venta persistente al completar venta exitosamente
    function limpiarVentaCompletada() {
        try {
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
            try {
                const ventaData = JSON.parse(ventaGuardada);
                const fechaFormateada = new Date(ventaData.fecha).toLocaleString('es-PE');
                const total = ventaData.ticket.reduce((sum, item) => sum + (item.importe || 0), 0);
                console.log('Estado venta persistente:', {
                    productos: ventaData.ticket.length,
                    cliente: ventaData.cliente?.nombre || 'Sin cliente',
                    fecha: fechaFormateada,
                    total: total.toFixed(2)
                });
                return ventaData;
            } catch (error) {
                console.error('Error leyendo estado venta persistente:', error);
                return null;
            }
        } else {
            console.log('No hay venta persistente guardada');
            return null;
        }
    }

    function verificarClienteSeleccionado() {
        // El cliente ya se restauró en restaurarVentaPersistente si era necesario
        // Solo procesar sessionStorage si no hay venta persistente
        if (!clienteActual || !clienteActual.id) {
            // Restaurar cliente guardado si existe (compatibilidad con versión anterior)
            const clienteGuardado = sessionStorage.getItem('clienteGuardadoPOS');
            if (clienteGuardado) {
                try {
                    clienteActual = JSON.parse(clienteGuardado);

                    // Actualizar UI del cliente
                    document.getElementById('cliente-info-nombre').textContent = clienteActual.nombre;
                    document.getElementById('cliente-info-documento').textContent = clienteActual.documento || '';

                    // Limpiar cliente guardado
                    sessionStorage.removeItem('clienteGuardadoPOS');

                    console.log('Cliente restaurado desde sessionStorage:', clienteActual);
                } catch (error) {
                    console.error('Error al restaurar cliente desde sessionStorage:', error);
                    sessionStorage.removeItem('clienteGuardadoPOS');
                }
            }
        }

        // Procesar tickets de sessionStorage solo si no hay venta persistente
        if (ticket.length === 0) {
            const ticketGuardado = sessionStorage.getItem('ticketGuardadoPOS');
            if (ticketGuardado) {
                try {
                    const ticketSession = JSON.parse(ticketGuardado);
                    if (ticketSession.length > 0) {
                        ticket = ticketSession;
                        renderTicket();
                        mostrarNotificacion(`Se restauraron ${ticket.length} productos del ticket temporal`);
                    }

                    // Limpiar ticket guardado
                    sessionStorage.removeItem('ticketGuardadoPOS');
                } catch (error) {
                    console.error('Error al restaurar ticket desde sessionStorage:', error);
                    sessionStorage.removeItem('ticketGuardadoPOS');
                }
            }
        }

        // Luego verificar si hay cliente seleccionado
        const clienteData = sessionStorage.getItem('clienteSeleccionadoPOS');
        if (clienteData) {
            try {
                const cliente = JSON.parse(clienteData);
                clienteActual = cliente;

                // Actualizar UI del cliente en el footer
                document.getElementById('footer-cliente').innerText = cliente.nombre;

                // Actualizar también en el área de cliente si existe
                const clienteNombre = document.getElementById('cliente-info-nombre');
                const clienteDoc = document.getElementById('cliente-info-documento');
                if (clienteNombre) clienteNombre.textContent = cliente.nombre;
                if (clienteDoc) clienteDoc.textContent = cliente.numero_documento || '';

                // Mostrar notificación especial para clientes desde gestión
                mostrarNotificacion(`🛒 Cliente seleccionado desde gestión: ${cliente.nombre}`);

                // Auto-guardar con el nuevo cliente
                setTimeout(() => {
                    guardarVentaPersistente();
                }, 500);

                // Limpiar sessionStorage después de usar
                sessionStorage.removeItem('clienteSeleccionadoPOS');

                console.log('Cliente cargado desde gestión de clientes:', cliente);
            } catch (error) {
                console.error('Error al cargar cliente seleccionado:', error);
                sessionStorage.removeItem('clienteSeleccionadePOS');
            }
        } else {
            // Si no hay cliente seleccionado pero había uno guardado, restaurarlo
            const clienteGuardado = sessionStorage.getItem('clienteGuardadoPOS');
            if (clienteGuardado) {
                try {
                    clienteActual = JSON.parse(clienteGuardado);
                    document.getElementById('footer-cliente').innerText = clienteActual.nombre;
                    sessionStorage.removeItem('clienteGuardadoPOS');
                } catch (error) {
                    console.error('Error al restaurar cliente guardado:', error);
                    sessionStorage.removeItem('clienteGuardadoPOS');
                }
            }
        }
    }

    function verificarLotesPendientes() {
        const lotesData = sessionStorage.getItem('lotesSeleccionados');

        if (lotesData) {
            try {
                const datos = JSON.parse(lotesData);

                // Verificar que los datos no sean muy antiguos (5 minutos máximo)
                if (Date.now() - datos.timestamp < 300000) {
                    // Agregar cada lote al ticket
                    datos.lotes.forEach(lote => {
                        const productoParaTicket = {
                            id: `lote_${lote.lote_id}`,
                            producto_id: datos.producto.id,
                            producto_linea_id: datos.producto.product_linea_id || datos.producto
                                .producto_linea_id || null,
                            almacen_detalle_id: lote.lote_id,
                            nombre: datos.producto.nombre,
                            lote: lote.lote,
                            cantidad: lote.cantidad,
                            cantidad_disponible: lote.cantidad + 100,
                            precio: lote.precio,
                            importe: lote.importe,
                            pvp: lote.pvp,
                            pvc: lote.pvc,
                            fecha_vencimiento: lote.fecha_vencimiento,
                            es_lote_especifico: true,
                            descuento: 0,
                            descuentoFijo: 0,
                            descuentoTexto: '0%'
                        };

                        // Use aggregation helper so lot items merge with existing ticket entries
                        if (typeof agregarProductoAlTicket === 'function') {
                            agregarProductoAlTicket(productoParaTicket);
                        } else {
                            ticket.push(productoParaTicket);
                        }
                    });

                    // Actualizar la vista del ticket
                    renderTicket();

                    // Mostrar notificación
                    mostrarNotificacion(`Se agregaron ${datos.lotes.length} lote(s) al ticket`);
                }

                // Limpiar los datos de sessionStorage
                sessionStorage.removeItem('lotesSeleccionados');
            } catch (error) {
                console.error('Error procesando lotes pendientes:', error);
                sessionStorage.removeItem('lotesSeleccionados');
            }
        }
    }
</script>

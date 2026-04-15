<!-- SweetAlert2 para confirmaciones elegantes -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Función mejorada para cancelar venta con SweetAlert
    function cancelarVentaConSweetAlert() {
        if (ticket.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Ticket vacío',
                text: 'No hay productos en el ticket para cancelar',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        Swal.fire({
            title: '¿Cancelar venta?',
            text: 'Se perderán todos los productos del ticket y se reseteará el sistema',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, mantener'
        }).then((result) => {
            if (result.isConfirmed) {
                // Resetear ticket
                ticket = [];

                // Limpiar storage y venta persistente
                sessionStorage.removeItem('ticketGuardadoPOS');
                localStorage.removeItem('ticketPOS');
                sessionStorage.removeItem('clienteGuardadoPOS');

                // LIMPIAR VENTA PERSISTENTE
                localStorage.removeItem(AUTOSAVE_KEY);
                sessionStorage.removeItem(AUTOSAVE_KEY);
                window.pagaConManual = false;

                // Detener auto-guardado
                if (autoSaveInterval) {
                    clearInterval(autoSaveInterval);
                    autoSaveInterval = null;
                }

                // Resetear cliente a "Cliente Contable"
                window.clienteActual = {
                    id: null,
                    documento: '',
                    nombre: 'CLIENTE CONTABLE',
                    direccion: '',
                    telefono: '',
                    email: ''
                };

                // Actualizar UI del cliente
                const clienteNombre = document.getElementById('cliente-info-nombre');
                const clienteDoc = document.getElementById('cliente-info-documento');
                if (clienteNombre) clienteNombre.textContent = 'CLIENTE CONTABLE';
                if (clienteDoc) clienteDoc.textContent = '';

                // Recargar cliente contable para restaurar su ID real
                if (typeof crearClienteContable === 'function') crearClienteContable();

                // Limpiar búsqueda de productos
                const searchInput = document.querySelector('input[placeholder="Buscar productos..."]');
                if (searchInput) {
                    searchInput.value = '';
                }

                // Limpiar tabla de productos
                const productosTable = document.getElementById('productos-tbody');
                if (productosTable) {
                    productosTable.innerHTML = '';
                }

                // Renderizar ticket vacío
                renderTicket();

                // Notificación de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Venta cancelada!',
                    text: 'Sistema reseteado correctamente',
                    timer: 2000,
                    showConfirmButton: false
                });

                console.log('Venta cancelada y sistema reseteado');
            }
        });
    }

    // Función mejorada para limpiar ticket rápido con SweetAlert
    function limpiarTicketRapidoConSweetAlert() {
        if (ticket.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Ticket vacío',
                text: 'El ticket ya está vacío',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        Swal.fire({
            title: '¿Limpiar ticket?',
            text: 'Solo se eliminarán los productos, el cliente seleccionado se mantendrá',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Solo limpiar ticket, mantener cliente
                ticket = [];

                // Limpiar storage del ticket
                sessionStorage.removeItem('ticketGuardadoPOS');
                localStorage.removeItem('ticketPOS');
                
                // TAMBIÉN LIMPIAR PERSISTENCIA
                localStorage.removeItem(AUTOSAVE_KEY);
                window.pagaConManual = false;

                // Renderizar ticket vacío
                renderTicket();

                Swal.fire({
                    icon: 'success',
                    title: '¡Ticket limpiado!',
                    text: 'Productos eliminados correctamente',
                    timer: 1500,
                    showConfirmButton: false
                });

                console.log('Ticket limpiado, cliente mantenido');
            }
        });
    }

    // Función para resetear completamente sin confirmación (para debugging o casos especiales)
    function resetearSistemaCompleto() {
        // Resetear ticket
        ticket = [];

        // Limpiar todo el storage y venta persistente
        sessionStorage.clear();
        localStorage.removeItem('ticketPOS');
        localStorage.removeItem('ticketGuardadoPOS');
        localStorage.removeItem('clienteGuardadoPOS');

        // LIMPIAR VENTA PERSISTENTE COMPLETAMENTE
        localStorage.removeItem(AUTOSAVE_KEY);

        // Reiniciar auto-guardado
        if (autoSaveInterval) {
            clearInterval(autoSaveInterval);
        }
        iniciarAutoGuardado();

        // Resetear cliente a "Cliente Contable"
        window.clienteActual = {
            id: null,
            documento: '',
            nombre: 'CLIENTE CONTABLE',
            direccion: '',
            telefono: '',
            email: ''
        };

        // Actualizar UI del cliente
        const clienteNombre = document.getElementById('cliente-info-nombre');
        const clienteDoc = document.getElementById('cliente-info-documento');
        if (clienteNombre) clienteNombre.textContent = 'CLIENTE CONTABLE';
        if (clienteDoc) clienteDoc.textContent = '';

        // Recargar cliente contable para restaurar su ID real
        if (typeof crearClienteContable === 'function') crearClienteContable();

        // Limpiar búsqueda de productos
        const searchInputs = document.querySelectorAll('input[type="text"]');
        searchInputs.forEach(input => {
            if (input.placeholder && input.placeholder.includes('Buscar')) {
                input.value = '';
            }
        });

        // Limpiar tabla de productos
        const productosTable = document.getElementById('productos-tbody');
        if (productosTable) {
            productosTable.innerHTML = '';
        }

        // Renderizar ticket vacío
        renderTicket();

        console.log('Sistema completamente reseteado');
        mostrarNotificacion('🔄 Sistema completamente reseteado');
    }

    // Atajo de teclado para reseteo de emergencia: Ctrl + Shift + R
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.shiftKey && event.key === 'R') {
            event.preventDefault();
            if (confirm(
                    '¿RESETEO DE EMERGENCIA? Esto borrará TODO sin posibilidad de recuperación, incluyendo la venta persistente.'
                )) {
                resetearSistemaCompleto();
            }
        }
    });

    // Función que se debe llamar al completar exitosamente una venta
    // Esta función puede ser llamada desde el controlador o al confirmar la venta
    window.ventaCompletadaExitosamente = function() {
        console.log('🎉 Venta completada exitosamente - limpiando persistencia...');
        limpiarVentaCompletada();
        mostrarNotificacion('✅ Venta procesada exitosamente. Sistema listo para nueva venta.');
    };

    // Exponer funciones útiles globalmente para debugging
    window.posDebug = {
        mostrarEstado: mostrarEstadoVentaPersistente,
        limpiarVenta: limpiarVentaCompletada,
        guardarManual: guardarVentaPersistente
    };

    // ========== INICIALIZACIÓN DEL SISTEMA ==========

    // Inicializar sistema de persistencia cuando se carga la página
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar sistema de persistencia
        setTimeout(() => {
            inicializarSistemaVentaPersistente();
        }, 1500);
    });
</script>

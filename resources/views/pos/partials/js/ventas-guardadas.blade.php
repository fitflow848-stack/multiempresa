<script>
    /**
     * Gestión de Ventas Guardadas (Pre-ventas)
     */
    function guardarTicket() {
        if (typeof ticket === 'undefined' || ticket.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Ticket vacío',
                text: 'Agregue al menos un producto antes de guardar.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }

        const totalTicket = typeof calcularTotalTicket === 'function' ? 
            calcularTotalTicket() : 
            ticket.reduce((acc, current) => acc + (parseFloat(current.importe) || 0), 0);

        const clienteNombre = (typeof clienteActual !== 'undefined' && clienteActual && clienteActual.nombre) ? 
            clienteActual.nombre : 'Cliente General';

        Swal.fire({
            title: '¿Guardar ticket actual?',
            text: `Se guardará el ticket de ${clienteNombre} por un total de S/ ${totalTicket.toFixed(2)} para retomarlo luego.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Guardando...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const payload = {
                    total: totalTicket,
                    cliente_nombre: clienteNombre,
                    data: {
                        ticket: ticket,
                        cliente: typeof clienteActual !== 'undefined' ? clienteActual : null,
                        metodo_pago: (typeof metodoPagoActual !== 'undefined') ? metodoPagoActual : 'Efectivo',
                        credito: (typeof modoProforma !== 'undefined' && modoProforma === 'credito')
                    }
                };

                fetch('{{ route('pos.guardar-venta') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Limpiar ticket local
                        if (typeof cancelarVenta === 'function') {
                            cancelarVenta();
                        } else {
                            ticket = [];
                            if (typeof renderTicket === 'function') renderTicket();
                        }

                        // Success notification
                        Swal.fire({
                            icon: 'success',
                            title: '¡Guardado!',
                            text: 'La venta ha sido puesta en espera correctamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Refrescar lista si el modal está abierto (opcional)
                        actualizarListaVentasGuardadas();
                    } else {
                        throw new Error(data.message || 'Error desconocido');
                    }
                })
                .catch(error => {
                    console.error('Error al guardar ticket:', error);
                    Swal.fire('Error', 'No se pudo guardar la venta en el servidor.', 'error');
                });
            }
        });
    }

    function abrirModalVentasGuardadas() {
        const modalEl = document.getElementById('modalVentasGuardadas');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        
        actualizarListaVentasGuardadas();
        modal.show();
    }

    function actualizarListaVentasGuardadas() {
        const container = document.getElementById('lista-ventas-guardadas');
        if (!container) return;

        fetch('{{ route('pos.listar-ventas-guardadas') }}')
            .then(response => response.json())
            .then(ventas => {
                if (ventas.length === 0) {
                    container.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted small">
                                <i class='bx bx-info-circle d-block fs-3 mb-2 opacity-50'></i>
                                No hay ventas guardadas en espera.
                            </td>
                        </tr>
                    `;
                    return;
                }

                container.innerHTML = '';
                ventas.forEach(v => {
                    const row = document.createElement('tr');
                    const fecha = new Date(v.created_at).toLocaleString('es-PE', {
                        day: '2-digit', month: '2-digit', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });

                    row.innerHTML = `
                        <td class="ps-4 fw-medium text-dark">${fecha}</td>
                        <td class="text-muted">${v.cliente_nombre || 'Desconocido'}</td>
                        <td class="text-end fw-bold text-primary">S/ ${parseFloat(v.total).toFixed(2)}</td>
                        <td class="pe-4 text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-sm btn-soft-primary px-3 rounded-pill border-0" 
                                        onclick="cargarVentaGuardada(${v.id})" 
                                        style="background-color: #e0e7ff; color: #4338ca;">
                                    <i class='bx bx-redo me-1'></i> Retomar
                                </button>
                                <button type="button" class="btn btn-sm btn-soft-danger px-3 rounded-pill border-0" 
                                        onclick="eliminarVentaGuardada(${v.id})" 
                                        style="background-color: #fee2e2; color: #b91c1c;">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </td>
                    `;
                    
                    // Al hacer click en la fila (excepto en botones) también carga
                    row.onclick = (e) => {
                        if (!e.target.closest('button')) {
                            cargarVentaGuardada(v.id);
                        }
                    };

                    container.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error al listar ventas guardadas:', error);
                container.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Error al cargar datos.</td></tr>';
            });
    }

    function cargarVentaGuardada(id) {
        if (ticket.length > 0) {
            Swal.fire({
                title: 'Ticket en uso',
                text: 'El ticket actual no está vacío. ¿Desea descartarlo y cargar la venta guardada?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, descartar y cargar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarCargaVenta(id);
                }
            });
        } else {
            ejecutarCargaVenta(id);
        }
    }

    function ejecutarCargaVenta(id) {
        // Show loading
        Swal.fire({
            title: 'Cargando venta...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(`{{ url('pos/cargar-venta-guardada') }}/${id}`)
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const data = res.data;
                    
                    // 1. Restaurar ticket
                    ticket = data.ticket || [];
                    
                    // 2. Restaurar cliente
                    if (data.cliente) {
                        clienteActual = data.cliente;
                        
                        // Actualizar UI
                        const cNombre = document.getElementById('cliente-info-nombre');
                        const cDoc = document.getElementById('cliente-info-documento');
                        const fCliente = document.getElementById('footer-cliente');
                        
                        if (cNombre) cNombre.textContent = clienteActual.nombre;
                        if (cDoc) cDoc.textContent = clienteActual.documento || clienteActual.numero_documento || '';
                        if (fCliente) fCliente.innerText = `${clienteActual.nombre} - ${clienteActual.documento || clienteActual.numero_documento || ''}`;
                    }

                    // 3. Renderizar y cerrar modal
                    if (typeof renderTicket === 'function') renderTicket();
                    
                    const modalEl = document.getElementById('modalVentasGuardadas');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();

                    Swal.close();
                    
                    mostrarNotificacion('¡Venta retomada correctamente!');
                } else {
                    throw new Error(res.message);
                }
            })
            .catch(error => {
                console.error('Error al cargar venta guardada:', error);
                Swal.fire('Error', 'No se pudo retomar la venta solicitada.', 'error');
            });
    }

    function eliminarVentaGuardada(id) {
        Swal.fire({
            title: '¿Eliminar venta en espera?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('pos/eliminar-venta-guardada') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        actualizarListaVentasGuardadas();
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    console.error('Error al eliminar:', error);
                    Swal.fire('Error', 'Ocurrió un error al eliminar.', 'error');
                });
            }
        });
    }
</script>

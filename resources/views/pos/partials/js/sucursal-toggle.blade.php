<script>
    /**
     * Cambiar de sucursal en el POS y refrescar para actualizar stock
     */
    function cambiarSucursal(branchId) {
        if (!branchId) return;

        // Mostrar cargando
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Cambiando de local...',
                text: 'Esto actualizará el stock disponible.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        fetch('{{ route('branch.change.api') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ branch_id: branchId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Si el ticket está vacío, simplemente recargamos
                if (ticket.length === 0) {
                    location.reload();
                } else {
                    // Si hay ticket, preguntamos si desea mantenerlo (ojo: stock puede no ser el mismo)
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Local cambiado',
                            text: 'Se ha cambiado el local. ¿Desea mantener los productos en el ticket? (El stock se verificará al vender)',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Mantener Ticket',
                            cancelButtonText: 'Limpiar Ticket',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33'
                        }).then((result) => {
                            if (!result.isConfirmed) {
                                // Limpiar ticket y persistencia
                                ticket = [];
                                localStorage.removeItem(AUTOSAVE_KEY);
                                sessionStorage.removeItem('ticketGuardadoPOS');
                                localStorage.removeItem('ticketPOS');
                            } else {
                                // Guardar ticket persistente con el nuevo local
                                guardarVentaPersistente();
                            }
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', data.message || 'No se pudo cambiar de local', 'error');
                } else {
                    alert(data.message || 'No se pudo cambiar de local');
                }
            }
        })
        .catch(error => {
            console.error('Error al cambiar sucursal:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Error de conexión al cambiar de local', 'error');
            }
        });
    }
</script>

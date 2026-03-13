<!-- Modal para seleccionar formato de impresión de Proforma -->
<div class="modal fade" id="modalFormatosProforma" tabindex="-1" aria-hidden="true" style="z-index: 2100;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bx bx-printer me-2 text-primary"></i> Formato de Proforma</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="text-muted mb-4">Seleccione el formato para generar la cotización:</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <button type="button" 
                        class="btn btn-outline-primary d-flex flex-column align-items-center p-3 btn-proforma-format" 
                        data-format="default" style="width: 125px; border-width: 2px;">
                        <i class="bx bxs-file-pdf fs-1 mb-2"></i>
                        <span class="fw-bold">Hoja A4</span>
                    </button>
                    <button type="button" 
                        class="btn btn-outline-secondary d-flex flex-column align-items-center p-3 btn-proforma-format" 
                        data-format="media-a4" style="width: 125px; border-width: 2px;">
                        <i class="bx bxs-file-pdf fs-1 mb-2"></i>
                        <span class="fw-bold">Media A4</span>
                    </button>
                    <button type="button" 
                        class="btn btn-outline-info d-flex flex-column align-items-center p-3 btn-proforma-format" 
                        data-format="8cm" style="width: 125px; border-width: 2px;">
                        <i class="bx bx-receipt fs-1 mb-2"></i>
                        <span class="fw-bold">Ticket 8cm</span>
                    </button>
                    <button type="button" 
                        class="btn btn-outline-info d-flex flex-column align-items-center p-3 btn-proforma-format" 
                        data-format="5.8cm" style="width: 125px; border-width: 2px; filter: brightness(0.9);">
                        <i class="bx bx-receipt fs-1 mb-2"></i>
                        <span class="fw-bold">Ticket 5.8cm</span>
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Manejar el clic en los formatos de proforma
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-proforma-format')) {
            const btn = e.target.closest('.btn-proforma-format');
            const format = btn.getAttribute('data-format');
            
            // Cerrar el modal
            const modalEl = document.getElementById('modalFormatosProforma');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            
            // Proceder a guardar y emitir
            finalizarProformaConFormato(format);
        }
    });

    async function finalizarProformaConFormato(format) {
        // Mostrar cargando
        Swal.fire({
            title: 'Generando Proforma...',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Calcular totales
        let total_con_igv = 0;
        let total_igv = 0;

        ticket.forEach(item => {
            const itemTotal = parseFloat(item.precio || 0) * parseFloat(item.cantidad || 0);
            total_con_igv += itemTotal;

            if (item.tipo_impuesto !== 'exonerado') {
                const itemGravado = itemTotal / 1.18;
                total_igv += (itemTotal - itemGravado);
            }
        });

        const subtotal = total_con_igv - total_igv;
        const igv = total_igv;

        const datosVenta = {
            ticket: JSON.stringify(ticket),
            cliente: JSON.stringify(clienteActual || {}),
            subtotal: subtotal.toFixed(2),
            igv: igv.toFixed(2),
            total: total_con_igv.toFixed(2),
            tipo_documento: 'cotizacion',
            proforma: '1',
            _token: '{{ csrf_token() }}'
        };

        try {
            const response = await fetch('{{ route('cotizaciones.save-cotizacion') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(datosVenta)
            });

            const data = await response.json();

            if (data.success) {
                const cotizacionId = data.data.cotizacion_id || data.data.venta_id || data.id;
                
                // Preparar URL según formato
                let url = '{{ route('cotizaciones.pdfCotizacion', ':id') }}'.replace(':id', cotizacionId);
                url += (url.includes('?') ? '&' : '?') + 'format=' + format;

                // Abrir PDF
                window.open(url, '_blank');

                Swal.fire({
                    icon: 'success',
                    title: '¡Proforma Guardada!',
                    text: 'El documento se ha generado correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                });

                // Limpiar ticket y datos
                limpiarVentaCompletada();
                ticket = [];
                renderTicket();

                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar la proforma: ' + (data.message || 'Error desconocido')
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de Red',
                text: 'No se pudo conectar con el servidor.'
            });
        }
    }
</script>

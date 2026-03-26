<!-- Modal para seleccionar formato de impresión de Venta -->
<div class="modal fade" id="modalFormatosVenta" aria-hidden="true" style="z-index: 2500;"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bx bx-printer me-2 text-primary"></i> Imprimir
                    Comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    onclick="recargarPaginaVenta()"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="text-muted mb-4">Seleccione el formato de impresión:</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <button type="button"
                        class="btn btn-outline-primary d-flex flex-column align-items-center p-3 btn-venta-format"
                        data-format="default" style="width: 125px; border-width: 2px;">
                        <i class="bx bxs-file-pdf fs-1 mb-2"></i>
                        <span class="fw-bold small text-wrap">Hoja A4</span>
                    </button>
                    <button type="button"
                        class="btn btn-outline-secondary d-flex flex-column align-items-center p-3 btn-venta-format"
                        data-format="media-a4" style="width: 125px; border-width: 2px;">
                        <i class="bx bxs-file-pdf fs-1 mb-2"></i>
                        <span class="fw-bold small text-wrap">Media A4</span>
                    </button>
                    <button type="button"
                        class="btn btn-outline-info d-flex flex-column align-items-center p-3 btn-venta-format"
                        data-format="8cm" style="width: 125px; border-width: 2px;">
                        <i class="bx bx-receipt fs-1 mb-2"></i>
                        <span class="fw-bold small text-wrap">Voucher 8cm</span>
                    </button>
                    <button type="button"
                        class="btn btn-outline-info d-flex flex-column align-items-center p-3 btn-venta-format"
                        data-format="5.8cm" style="width: 125px; border-width: 2px; filter: brightness(0.9);">
                        <i class="bx bx-receipt fs-1 mb-2"></i>
                        <span class="fw-bold small text-wrap">Voucher 5.8cm</span>
                    </button>

                    <button type="button"
                        class="btn btn-success d-flex flex-column align-items-center p-3 btn-send-whatsapp-venta"
                        style="width: 125px; border-width: 2px;">
                        <i class="bx bxl-whatsapp fs-1 mb-2"></i>
                        <span class="fw-bold small text-wrap">WhatsApp</span>
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"
                    onclick="recargarPaginaVenta()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let ultimaVentaId = null;
    let ultimaVentaTotal = 0;
    window.imprimiendoRedirigiendo = false;

    function abrirModalFormatosVenta(id, total = 0) {
        ultimaVentaId = id;
        ultimaVentaTotal = total;
        window.imprimiendoRedirigiendo = false;

        const modalEl = document.getElementById('modalFormatosVenta');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    async function recargarPaginaVenta() {
        if (typeof limpiarVentaCompletada === 'function') {
            await limpiarVentaCompletada();
        }
        
        // En lugar de recargar toda la página (que interrumpe la impresión), 
        // simplemente reseteamos el estado del TPV vía JS
        if (typeof renderTicket === 'function') {
            renderTicket();
        }
        
        // Resetear campos de entrada
        const inputEntrega = document.getElementById('input-entrega');
        if (inputEntrega) {
            inputEntrega.value = '0.00';
            if (typeof window !== 'undefined') window.pagaConManual = false;
        }
        
        const inputObs = document.getElementById('input-observaciones');
        if (inputObs) inputObs.value = '';

        // Resetear selects si existen
        const metodoSelect = document.getElementById('medio-pago-select');
        if (metodoSelect) metodoSelect.selectedIndex = 0;
        
        const docSelect = document.getElementById('tipo-documento-select');
        if (docSelect) docSelect.selectedIndex = 0;

        // Cerrar el modal con Bootstrap
        const modalEl = document.getElementById('modalFormatosVenta');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        }
        
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('✅ Punto de venta listo');
        }
    }

    // Usar evento delegado para capturar clics en los botones de formato
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-venta-format')) {
            const btn = e.target.closest('.btn-venta-format');
            const format = btn.getAttribute('data-format');

            if (ultimaVentaId) {
                const isProforma = window.currentIsProforma || false;
                // Si es proforma, usar ruta de cotizaciones, de lo contrario pos/v
                const rawUrl = isProforma
                    ? `{{ url("cotizaciones/pdf") }}/${ultimaVentaId}?format=${format}`
                    : `{{ url("pos/v") }}/${ultimaVentaId}/pdf/${format}`;

                const url = `${rawUrl}${rawUrl.includes('?') ? '&' : '?'}print=1`;
                imprimirPDFv2(url);
                
                // Cerrar modal y limpiar automáticamente después de enviar a impresión
                setTimeout(() => {
                    recargarPaginaVenta();
                }, 2000); 
            }
        }

        if (e.target.closest('.btn-send-whatsapp-venta')) {
            const ventaId = window.currentVentaId;
            const ventaData = window.currentVentaData;

            if (!ventaId || !ventaData) return;

            let clientName = 'Cliente';
            let phone = '';

            try {
                const clientObj = window.clienteActual;
                clientName = clientObj ? (clientObj.nombre || clientObj.razon_social || 'Cliente') : 'Cliente';
                phone = clientObj ? (clientObj.telefono || clientObj.celular || '') : '';
            } catch (e) { }

            const modalEl = document.getElementById('modalFormatosVenta');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);

            // 1. Ocultar el modal de formatos temporalmente
            if (modalInstance) modalInstance.hide();

            // 2. Esperar a que se oculte el modal de bootstrap y luego llamar a SweetAlert
            setTimeout(() => {
                Swal.fire({
                    title: 'Enviar por WhatsApp',
                    text: `Ingrese el número de teléfono para enviar el comprobante ${ventaData.numero_completo}:`,
                    input: 'text',
                    inputValue: phone || '',
                    showCancelButton: true,
                    confirmButtonText: 'Enviar',
                    cancelButtonText: 'Cancelar',
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    allowEnterKey: true,
                    didOpen: () => {
                        const input = Swal.getInput();
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    },
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Debe ingresar un número de teléfono';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const targetPhone = result.value.replace(/\D/g, '');
                        const isProforma = window.currentIsProforma || false;
                        const pdfUrl = isProforma
                            ? `{{ url("cotizaciones/pdf") }}/${ventaId}?format=default`
                            : `{{ url("pos/v") }}/${ventaId}/pdf/default`;

                        const message = `Hola ${clientName}, le adjunto su comprobante ${ventaData.numero_completo} por un total de S/ ${ventaData.total}. Puede verlo/descargarlo aquí: ${pdfUrl}`;
                        const waUrl = `https://wa.me/${targetPhone.startsWith('51') ? targetPhone : '51' + targetPhone}?text=${encodeURIComponent(message)}`;

                        window.open(waUrl, '_blank');
                        
                        // Una vez enviado por whatsapp, limpiar venta y cerrar modal
                        setTimeout(() => {
                            recargarPaginaVenta();
                        }, 1000);
                    } else {
                        // Si canceló el envío de WhatsApp, re-mostramos el modal de formatos
                        setTimeout(() => {
                            if (modalInstance) modalInstance.show();
                        }, 300);
                    }
                });
            }, 300);
        }
    });

    function imprimirPDFv2(url) {
        if (typeof printJS !== 'undefined') {
            printJS({
                printable: url,
                type: 'pdf',
                showModal: true,
                modalMessage: 'Preparando documento...',
                onPrintDialogClose: () => {
                    console.log('Dialogo de impresión cerrado');
                },
                onError: (error) => {
                    console.error('Error con Print.js:', error);
                    window.open(url, '_blank');
                }
            });
        } else {
            window.open(url, '_blank');
        }
    }

    // No necesitamos el listener de hidden.bs.modal si el modal es estático y controlamos los botones
</script>
<!-- Modal para seleccionar formato de impresión de Venta -->
<div class="modal fade" id="modalFormatosVenta" tabindex="-1" aria-hidden="true" style="z-index: 2500;">
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

    function recargarPaginaVenta() {
        if (typeof limpiarVentaCompletada === 'function') {
            limpiarVentaCompletada();
        }
        window.location.reload();
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

            window.imprimiendoRedirigiendo = true;

            const modalEl = document.getElementById('modalFormatosVenta');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

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

                        // Redirigir al POS después de enviar
                        setTimeout(() => {
                            recargarPaginaVenta();
                        }, 1000);

                    } else {
                        // Limpiar aunque cancele si la venta ya se guardó
                        recargarPaginaVenta();
                    }
                });
            }, 300);
        }
    });

    function imprimirPDFv2(url) {
        window.imprimiendoRedirigiendo = true;
        // Print.js es más robusto para abrir el diálogo de impresión directamente
        if (typeof printJS !== 'undefined') {
            printJS({
                printable: url,
                type: 'pdf',
                showModal: true,
                modalMessage: 'Preparando documento...',
                onPrintDialogClose: () => {
                    setTimeout(recargarPaginaVenta, 1000);
                },
                onError: (error) => {
                    console.error('Error con Print.js:', error);
                    window.open(url, '_blank');
                    setTimeout(recargarPaginaVenta, 500);
                }
            });
        } else {
            window.open(url, '_blank');
            setTimeout(recargarPaginaVenta, 1000);
        }
    }

    // Detectar cierre del modal por cualquier vía
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('modalFormatosVenta');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                if (!window.imprimiendoRedirigiendo) {
                    recargarPaginaVenta();
                }
            });
        }
    });
</script>
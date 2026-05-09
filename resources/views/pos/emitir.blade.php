@extends('layout.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">

    <style>
        .productos-emitir {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
        }

        .productos-emitir table {
            width: 100%;
            border-collapse: collapse;
        }

        .productos-emitir th,
        .productos-emitir td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .productos-emitir th {
            background: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        .cliente-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>

    <div style="height: calc(100vh - 75px); display: flex; gap: 15px; padding: 10px; overflow: hidden;">

        <!-- COLUMNA IZQUIERDA: PRODUCTOS -->
        <div
            style="flex: 1; display: flex; flex-direction: column; background: #fff; border-radius: 8px; border: 1px solid #ddd; padding: 15px; overflow: hidden;">
            <h3
                style="margin: 0 0 15px 0; color: #b33; font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                Detalle de Productos
            </h3>

            <!-- Información del cliente -->
            @if (isset($clienteData) && $clienteData)
                <div class="cliente-info" style="margin-bottom: 15px;">
                    <strong>Cliente:</strong> {{ json_decode($clienteData)->nombre ?? 'Cliente Contado' }}
                    @if (json_decode($clienteData)->numero_documento ?? null)
                        - {{ json_decode($clienteData)->tipo_documento ?? 'DNI' }}:
                        {{ json_decode($clienteData)->numero_documento }}
                    @endif
                </div>
            @else
                <div class="cliente-info" style="margin-bottom: 15px;">
                    <strong>Cliente:</strong> Cliente Contado
                </div>
            @endif

            <!-- Lista de productos con scroll independiente -->
            <div class="productos-emitir" style="flex: 1; margin: 0; border: none; max-height: none;">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="width: 60px; text-align: center;">Cant.</th>
                            <th style="width: 80px; text-align: right;">P.U.</th>
                            <th style="width: 80px; text-align: right;">Desc.</th>
                            <th style="width: 80px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="productos-ticket">
                        @if (isset($ticketData) && $ticketData)
                            @foreach (json_decode($ticketData) as $item)
                                <tr>
                                    <td>{{ $item->nombre }}</td>
                                    <td style="text-align: center;">{{ $item->cantidad }}</td>
                                    <td style="text-align: right;">S/ {{ number_format($item->precio, 2) }}</td>
                                    <td style="text-align: right; color: #d63384;">
                                        S/
                                        {{ number_format($item->precio * $item->cantidad - ($item->importe ?? $item->precio * $item->cantidad), 2) }}
                                    </td>
                                    <td style="text-align: right; font-weight: bold;">
                                        S/ {{ number_format($item->importe ?? $item->precio * $item->cantidad, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Resumen de items al pie de la tabla -->
            <div style="margin-top: 10px; font-size: 12px; color: #666; text-align: right;">
                {{ isset($ticketData) ? count(json_decode($ticketData)) : 0 }} items en lista
            </div>
        </div>

        <!-- COLUMNA DERECHA: PAGO Y DOCUMENTO -->
        <div
            style="width: 400px; display: flex; flex-direction: column; background: #fff; border-radius: 8px; border: 1px solid #ddd; padding: 15px; overflow-y: auto;">

            <!-- SECCIÓN RESUMEN TOTAL -->
            <div
                style="background: #2d3436; color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <div
                    style="font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; opacity: 0.8;">
                    Total a Pagar</div>
                <div style="font-size: 32px; font-weight: 800; color: #00d2d3;">
                    S/ {{ number_format($total, 2) }}
                </div>
            </div>

            <!-- SECCIÓN PAGO -->
            <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; color: #0b8a7e; text-transform: uppercase;">Pago</h4>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px;">MEDIO
                        DE PAGO</label>
                    <select id="medio-pago" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                        @foreach ($metodos as $metodo)
                            <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-bottom: 12px;">
                    <div style="flex: 1;">
                        <label
                            style="display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px;">PAGA
                            CON (ENTREGA)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 8px; top: 8px; color: #666;">S/</span>
                            <input id="entrega" type="number" step="0.01"
                                value="{{ ($metodoPagoInput ?? 'contado') === 'credito' ? '0.00' : $total ?? '0.00' }}"
                                style="width:100%; padding:8px 8px 8px 30px; border:1px solid #ccc; border-radius:4px; font-weight: bold;"
                                onkeyup="calcularCambio()" onchange="calcularCambio()">
                        </div>
                    </div>
                    <div style="flex: 1;">
                        <label
                            style="display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px;">CAMBIO</label>
                        <div
                            style="padding: 9px; background: #e9ecef; border-radius: 4px; font-weight: bold; color: #b33; text-align: center;">
                            S/ <span id="cambio">0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Campo dinámico para Plazo (Crédito) -->
                <div id="seccion-plazo"
                    style="display: none; margin-bottom: 12px; background: #fff8e1; padding: 10px; border-radius: 4px; border: 1px solid #ffe082;">
                    <label
                        style="display: block; font-size: 12px; font-weight: 600; color: #795548; margin-bottom: 5px;">PLAZO
                        DE PAGO (DÍAS)</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input id="plazo_dias" type="number" value="30" min="1" max="365"
                            style="width:80px; padding:8px; border:1px solid #ccc; border-radius:4px; font-weight: bold; text-align: center;">
                        <span style="font-size: 11px; color: #8d6e63;">Días para el vencimiento de la deuda.</span>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN DOCUMENTO -->
            <div style="flex: 1;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; color: #17a2b8; text-transform: uppercase;">Documento</h4>

                <div style="background:#e3f2fd; padding:10px; border-radius:4px; margin-bottom:15px; font-size: 12px;">
                    <strong>{{ $isProforma ? 'PROFORMA / COTIZACIÓN' : ucfirst($tipoDocumento ?? 'boleta') }}</strong>
                    @if ($isProforma)
                        - Documento informativo de precios
                    @elseif ($tipoDocumento == 'ticket')
                        - Comprobante interno
                    @elseif($tipoDocumento == 'boleta')
                        - Consumidor Final
                    @elseif($tipoDocumento == 'factura')
                        - Con RUC
                    @endif
                </div>

                <div style="display:flex; gap:10px; margin-bottom: 12px;">
                    <div style="flex: 1;">
                        <label style="font-size: 11px; color: #666;">Serie</label>
                        <input type="text" id="serie" value="{{ $serieDocumento ?? ($company->serie_boleta ?? 'B001') }}"
                            style="width:100%; padding:6px; border:1px solid #ddd; background: #f9f9f9;" readonly>
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 11px; color: #666;">Número</label>
                        <input type="text" id="numero" value="0001"
                            style="width:100%; padding:6px; border:1px solid #ddd; background: #f9f9f9;" readonly>
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 11px; color: #666;">Fecha & Vendedor</label>
                    <div style="font-size: 12px; border-bottom: 1px dotted #ccc; padding-bottom: 4px;">
                        {{ now()->format('d/m/Y H:i') }} | {{ $user->name ?? 'User' }}
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <textarea id="observaciones" placeholder="Observaciones..."
                        style="width:100%; height:50px; border:1px solid #ddd; padding:8px; border-radius: 4px; resize: none; font-size: 12px;"></textarea>
                </div>

                <div style="display:flex; gap:10px; margin-bottom: 12px;">
                    <input type="text" id="guia-remision" placeholder="Guía Remisión Rem."
                        style="flex:1; padding:6px; border:1px solid #ddd; border-radius: 4px; font-size: 11px;">
                    <input type="text" id="guia-transporte" placeholder="Guía Rem. Trans."
                        style="flex:1; padding:6px; border:1px solid #ddd; border-radius: 4px; font-size: 11px;">
                </div>
            </div>
            <!-- BOTONES -->
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button onclick="cancel()"
                    style="flex: 1; padding: 12px; border: 1px solid #ddd; background: #fff; color: #555; border-radius: 4px; cursor: pointer; font-weight: 600;">Regresar</button>
                <button onclick="accept()"
                    style="flex: 2; padding: 12px; border: none; background: #6b2e51; color: #fff; border-radius: 4px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 6px rgba(107, 46, 81, 0.2);">{{ $isProforma ? 'CONFIRMAR PROFORMA' : 'CONFIRMAR VENTA' }}</button>
            </div>

        </div>
    </div>

    <!-- Modal para seleccionar formato de impresión -->
    <div class="modal fade" id="modalFormatosImpresion" aria-hidden="true" style="z-index: 2050;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="bx bx-printer me-1"></i> Imprimir Comprobante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <input type="hidden" id="imprimirVentaId">
                    <p class="mb-4" id="texto-venta-success"></p>
                    <p class="text-muted small mb-3">Presione el número o Enter para imprimir (por defecto: 8cm)</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <button type="button"
                            class="btn btn-primary d-flex flex-column align-items-center p-3 btn-print-format shadow-sm"
                            data-format="default" data-key="1" style="width: 120px; transition: transform 0.2s;">
                            <span class="badge bg-dark position-absolute top-0 start-0 m-1">1</span>
                            <i class="bx bxs-file-pdf fs-1 mb-2"></i>
                            <span class="small fw-bold">Hoja A4</span>
                        </button>
                        <button type="button"
                            class="btn btn-secondary d-flex flex-column align-items-center p-3 btn-print-format shadow-sm"
                            data-format="media-a4" data-key="2" style="width: 120px; transition: transform 0.2s;">
                            <span class="badge bg-dark position-absolute top-0 start-0 m-1">2</span>
                            <i class="bx bxs-file-pdf fs-1 mb-2"></i>
                            <span class="small fw-bold">Media A4</span>
                        </button>
                        <button type="button"
                            class="btn btn-info text-white d-flex flex-column align-items-center p-3 btn-print-format shadow-sm border border-2 border-info"
                            data-format="8cm" data-key="3" id="btn-format-default" style="width: 120px; transition: transform 0.2s;">
                            <span class="badge bg-dark position-absolute top-0 start-0 m-1">3</span>
                            <i class="bx bx-receipt fs-1 mb-2"></i>
                            <span class="small fw-bold">8cm ⏎</span>
                        </button>
                        <button type="button"
                            class="btn btn-info text-white d-flex flex-column align-items-center p-3 btn-print-format shadow-sm"
                            data-format="5.8cm" data-key="4" style="width: 120px; filter: brightness(0.9); transition: transform 0.2s;">
                            <span class="badge bg-dark position-absolute top-0 start-0 m-1">4</span>
                            <i class="bx bx-receipt fs-1 mb-2"></i>
                            <span class="small fw-bold">5.8cm</span>
                        </button>
                        <button type="button"
                            class="btn btn-success d-flex flex-column align-items-center p-3 btn-send-whatsapp shadow-sm"
                            data-key="5" style="width: 120px; transition: transform 0.2s;">
                            <span class="badge bg-dark position-absolute top-0 start-0 m-1">5</span>
                            <i class="bx bxl-whatsapp fs-1 mb-2"></i>
                            <span class="small fw-bold">WhatsApp</span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"
                        id="btn-cerrar-finalizar">Cerrar y Regresar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Datos del ticket pasados desde el controlador
        const ticketData = @json($ticketData ?? []);
        const clienteData = @json($clienteData ?? null);
        const totalVenta = parseFloat('{{ $total ?? 0 }}');
        const tipoDocumentoSeleccionado = '{{ $tipoDocumento ?? 'boleta' }}';
        const isProforma = '{{ $isProforma ?? 0 }}';
        const idCoti = '{{ $idCoti ?? '' }}';

        function calcularCambio() {
            const entrega = parseFloat(document.getElementById('entrega').value) || 0;
            const cambio = Math.max(0, entrega - totalVenta);
            document.getElementById('cambio').textContent = cambio.toFixed(2);

            // Mostrar/Ocultar sección de plazo si hay deuda
            const seccionPlazo = document.getElementById('seccion-plazo');
            if (entrega < totalVenta) {
                seccionPlazo.style.display = 'block';
            } else {
                seccionPlazo.style.display = 'none';
            }
        }

        function accept() {
            const entrega = parseFloat(document.getElementById('entrega').value) || 0;
            const deuda = Math.max(0, totalVenta - entrega);

            // Validar que si hay deuda, debe haber un cliente real (no genérico)
            if (deuda > 0) {
                const clienteObj = clienteData ? JSON.parse(clienteData) : null;
                const nombreCliente = clienteObj?.nombre || '';
                const documentoCliente = clienteObj?.numero_documento || '';

                // Lista de nombres de clientes genéricos que no pueden tener deuda
                const clientesGenericos = ['CLIENTE CONTABLE', 'Cliente Contado', 'CLIENTE CONTADO', 'Cliente Contable',
                    ''
                ];

                const esClienteGenerico = clientesGenericos.some(c =>
                    nombreCliente.toUpperCase().trim() === c.toUpperCase().trim()
                ) || !clienteObj || !clienteObj.id || documentoCliente === '00000000';

                if (esClienteGenerico) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Cliente requerido',
                            html: `<p>Para generar una <strong>venta a crédito</strong> (con deuda de S/ ${deuda.toFixed(2)}), debe seleccionar un cliente válido.</p><p>Por favor, seleccione un cliente antes de continuar.</p>`,
                            icon: 'warning',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        alert(
                            `Para generar una venta a crédito (con deuda de S/ ${deuda.toFixed(2)}), debe seleccionar un cliente válido.\n\nPor favor, seleccione un cliente antes de continuar.`
                        );
                    }
                    return;
                }
            }

            const datosEmision = {
                ticket: JSON.stringify(ticketData),
                cliente: JSON.stringify(clienteData),
                tipo_documento: tipoDocumentoSeleccionado,
                tipo_pago_id: document.getElementById('medio-pago').value,
                total: totalVenta,
                entrega: entrega,
                cambio: parseFloat(document.getElementById('cambio').textContent) || 0,
                deuda: deuda,
                genera_deuda: deuda > 0 ? 1 : 0,
                observaciones: document.getElementById('observaciones').value,
                guia_remision: document.getElementById('guia-remision').value,
                guia_transporte: document.getElementById('guia-transporte').value,
                serie: document.getElementById('serie').value,
                numero: document.getElementById('numero').value,
                plazo_dias: document.getElementById('plazo_dias').value || 30,
                id_coti: idCoti || null,
                _token: '{{ csrf_token() }}'
            };

            // Deshabilitar botón para evitar doble clic
            const btnAceptar = document.querySelector('button[onclick="accept()"]');
            if (btnAceptar) {
                btnAceptar.disabled = true;
                btnAceptar.textContent = 'Procesando...';
            }

            // Enviar datos al servidor
            const urlSave = (isProforma === '1' || isProforma === 1) ? '{{ route('cotizaciones.save-cotizacion') }}' :
                '{{ route('pos.save-venta') }}';

            // Mostrar cargando con Swal
            Swal.fire({
                title: 'Guardando venta...',
                text: 'Por favor espere mientras procesamos la información.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(urlSave, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(datosEmision)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ventaId = data.data.venta_id;
                        const numeroCompleto = data.data.numero_completo;

                        // Guardar IDs para la impresión
                        window.currentVentaId = ventaId;
                        window.currentVentaData = data.data;

                        Swal.close();

                        // Mostrar el modal de formatos
                        document.getElementById('texto-venta-success').innerHTML =
                            `¡Venta <b>${numeroCompleto}</b> guardada con éxito!<br>Total: <b>S/ ${data.data.total}</b><br><br>Elija el formato de impresión:`;

                        const modalImpresion = new bootstrap.Modal(document.getElementById('modalFormatosImpresion'));
                        modalImpresion.show();

                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Error desconocido al guardar la venta',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                        if (btnAceptar) {
                            btnAceptar.disabled = false;
                            btnAceptar.textContent = 'CONFIRMAR VENTA';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                    if (btnAceptar) {
                        btnAceptar.disabled = false;
                        btnAceptar.textContent = 'CONFIRMAR VENTA';
                    }
                });
        }

        // Manejar el clic en los formatos de impresión
        document.addEventListener('click', function (e) {
            if (e.target.closest('.btn-print-format')) {
                const btn = e.target.closest('.btn-print-format');
                const format = btn.getAttribute('data-format');
                const ventaId = window.currentVentaId;

                if (!ventaId) return;

                if (isProforma === '1' || isProforma === 1) {
                    url = '{{ route('cotizaciones.pdfCotizacion', ':id') }}'.replace(':id', ventaId);
                    url += (url.includes('?') ? '&' : '?') + 'format=' + format;
                } else {
                    url = '{{ route('pos.pdf', ['id' => ':id', 'format' => ':format']) }}'
                        .replace(':id', ventaId)
                        .replace(':format', format) + '?print=1';
                }

                // Método de impresión por Iframe para forzar el diálogo del navegador
                imprimirPDFv2(url);
            }

            if (e.target.closest('.btn-send-whatsapp')) {
                const ventaId = window.currentVentaId;
                const ventaData = window.currentVentaData;

                if (!ventaId || !ventaData) return;

                let clientName = 'Cliente';
                let phone = '';

                try {
                    const clientObj = clienteData ? JSON.parse(clienteData) : null;
                    clientName = clientObj ? clientObj.nombre : 'Cliente';
                    phone = clientObj ? clientObj.telefono : '';
                } catch (e) { }

                window.imprimiendoRedirigiendo = true;

                const modal = bootstrap.Modal.getInstance(document.getElementById('modalFormatosImpresion'));
                modal.hide();

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
                            if (input) input.focus();
                        },
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Debe ingresar un número de teléfono';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const targetPhone = result.value.replace(/\D/g, '');
                            const pdfUrl = '{{ url("pos/v") }}/' + ventaId + '/pdf/default';
                            const message = `Hola ${clientName}, le adjunto su comprobante ${ventaData.numero_completo} por un total de S/ ${ventaData.total}. Puede verlo/descargarlo aquí: ${pdfUrl}`;
                            const waUrl = `https://wa.me/${targetPhone.startsWith('51') ? targetPhone : '51' + targetPhone}?text=${encodeURIComponent(message)}`;

                            window.open(waUrl, '_blank');
                            
                            // Redirigir al POS después de enviar
                            setTimeout(() => {
                                limpiarYRedirigir();
                            }, 1000);

                        }
                    });
                }, 300);
            }
        });

        function imprimirPDFv2(url) {
            // Print.js es más robusto para abrir el diálogo de impresión directamente
            printJS({
                printable: url,
                type: 'pdf',
                showModal: true,
                modalMessage: 'Preparando documento...',
                onPrintDialogClose: () => {
                    // Una vez que el usuario interactúa con la ventana de impresión, redirigir
                    setTimeout(limpiarYRedirigir, 1000);
                },
                onError: (error) => {
                    console.error('Error con Print.js:', error);
                    // Fallback a ventana nueva si falla
                    window.open(url, '_blank');
                    setTimeout(limpiarYRedirigir, 500);
                }
            });
        }

        document.getElementById('btn-cerrar-finalizar').addEventListener('click', function () {
            limpiarYRedirigir();
        });

        // Atajos de teclado para el modal de impresión
        document.getElementById('modalFormatosImpresion').addEventListener('shown.bs.modal', function () {
            document.getElementById('btn-format-default').focus();
        });

        document.addEventListener('keydown', function (e) {
            const modal = document.getElementById('modalFormatosImpresion');
            if (!modal.classList.contains('show')) return;

            const keyMap = { '1': 'default', '2': 'media-a4', '3': '8cm', '4': '5.8cm', '5': 'whatsapp' };

            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('btn-format-default').click();
                return;
            }

            if (keyMap[e.key]) {
                e.preventDefault();
                if (e.key === '5') {
                    modal.querySelector('.btn-send-whatsapp').click();
                } else {
                    const btn = modal.querySelector(`[data-format="${keyMap[e.key]}"]`);
                    if (btn) btn.click();
                }
            }
        });

        // También cerrar al darle a la X del modal (si se usa data-bs-dismiss)
        document.getElementById('modalFormatosImpresion').addEventListener('hidden.bs.modal', function () {
            // Si el usuario cierra el modal sin imprimir, igual debemos redirigir para limpiar el ticket
            if (!window.imprimiendoRedirigiendo) {
                limpiarYRedirigir();
            }
        });

        function limpiarYRedirigir() {
            window.imprimiendoRedirigiendo = true;
            // Limpiar ticket y datos temporales
            try {
                localStorage.removeItem('ticketPOS');
                sessionStorage.removeItem('ticketPOS');
                localStorage.removeItem('ventaPersistentePOS');
                sessionStorage.removeItem('ticketGuardadoPOS');
                sessionStorage.removeItem('clienteGuardadoPOS');
            } catch (e) {
                console.warn('No se pudieron limpiar algunas claves de storage:', e);
            }

            // Redirigir al POS
            window.location.href = '{{ route('pos.index') }}';
        }

        function cancel() {
            if (confirm('¿Está seguro que desea cancelar? Se perderán todos los datos.')) {
                window.location = '{{ route('pos.index') }}';
            }
        }

        // Calcular cambio inicial
        document.addEventListener('DOMContentLoaded', function () {
            calcularCambio();

            // Obtener el siguiente número de serie
            obtenerSiguienteNumero();
        });

        // Función para obtener el siguiente número
        function obtenerSiguienteNumero() {
            const serie = document.getElementById('serie').value;

            fetch('{{ route('pos.obtener-siguiente-numero') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    serie: serie,
                    tipo_documento: tipoDocumentoSeleccionado
                })
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('numero').value = data.numero;
                })
                .catch(error => {
                    console.error('Error al obtener siguiente número:', error);
                });
        }
    </script>

@endsection
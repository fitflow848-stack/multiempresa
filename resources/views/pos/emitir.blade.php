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
                                    <td style="text-align: right;">S/
                                        {{ number_format($item->precio * $item->cantidad, 2) }}
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

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label
                            style="display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px;">PAGA
                            CON (ENTREGA)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 8px; top: 8px; color: #666;">S/</span>
                            <input id="entrega" type="number" step="0.01"
                                value="{{ ($metodoPagoInput ?? 'contado') === 'credito' ? '0.00' : ($total ?? '0.00') }}"
                                style="width:100%; padding:8px 8px 8px 30px; border:1px solid #ccc; border-radius:4px; font-weight: bold;"
                                onkeyup="calcularCambio()">
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
            </div>

            <!-- SECCIÓN DOCUMENTO -->
            <div style="flex: 1;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; color: #17a2b8; text-transform: uppercase;">Documento</h4>

                <div style="background:#e3f2fd; padding:10px; border-radius:4px; margin-bottom:15px; font-size: 12px;">
                    <strong>{{ ucfirst($tipoDocumento ?? 'boleta') }}</strong>
                    @if ($tipoDocumento == 'ticket')
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
                    style="flex: 2; padding: 12px; border: none; background: #6b2e51; color: #fff; border-radius: 4px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 6px rgba(107, 46, 81, 0.2);">CONFIRMAR
                    VENTA</button>
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
                id_coti: idCoti || null,
                _token: '{{ csrf_token() }}'
            };

            // Deshabilitar botón para evitar doble clic
            const btnAceptar = event.target;
            btnAceptar.disabled = true;
            btnAceptar.textContent = 'Procesando...';

            // IMPORTANTE: Abrir la ventana ANTES del fetch para evitar bloqueo de popups
            // Los navegadores solo permiten window.open() en respuesta directa al clic del usuario
            const pdfWindow = window.open('about:blank', '_blank');
            if (pdfWindow) {
                pdfWindow.document.write(
                    '<html><head><title>Cargando documento...</title></head><body style="display:flex;justify-content:center;align-items:center;height:100vh;font-family:Arial;"><h2>Generando documento, por favor espere...</h2></body></html>'
                );
            }

            // Enviar datos al servidor
            const urlSave = (isProforma === '1' || isProforma === 1) ? '{{ route('cotizaciones.save-cotizacion') }}' :
                '{{ route('pos.save-venta') }}';

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
                        // Construir URL del PDF
                        const ventaId = data.data.venta_id;
                        let urlA4 = '{{ route('pos.pdf', ['id' => ':id', 'format' => 'default']) }}'.replace(':id',
                            ventaId);
                        let url8cm = '{{ route('pos.pdf', ['id' => ':id', 'format' => '8cm']) }}'.replace(':id',
                            ventaId);

                        if (isProforma === '1' || isProforma === 1) {
                            urlA4 = '{{ route('cotizaciones.pdfCotizacion', ':id') }}'.replace(':id', ventaId);
                            url8cm = '{{ route('cotizaciones.pdfCotizacion8cm', ':id') }}'.replace(':id', ventaId);
                        }

                        const openUrl = (tipoDocumentoSeleccionado === 'ticket') ? url8cm : urlA4;

                        // Si hay deuda, mostrar información detallada primero
                        if (datosEmision.deuda > 0) {
                            // Cerrar la ventana de carga - el usuario verá el PDF después de confirmar
                            if (pdfWindow && !pdfWindow.closed) {
                                pdfWindow.close();
                            }

                            const mensajeDeuda = `
                                    <div style="text-align: left; padding: 10px;">
                                        <p><strong>✅ Venta guardada exitosamente</strong></p>
                                        <p>Número: <strong>${data.data.numero_completo}</strong></p>
                                        <p>Total: <strong>S/ ${data.data.total}</strong></p>
                                        <hr style="margin: 15px 0;">
                                        <p style="color: #dc3545; font-size: 18px;"><strong>⚠️ DEUDA GENERADA</strong></p>
                                        <p>Monto de deuda: <strong style="color: #dc3545; font-size: 20px;">S/ ${datosEmision.deuda.toFixed(2)}</strong></p>
                                        <p>Pago recibido: S/ ${datosEmision.entrega.toFixed(2)}</p>
                                        <p>Cliente: ${clienteData ? JSON.parse(clienteData).nombre : 'Cliente Contado'}</p>
                                    </div>
                                `;

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Venta con Deuda',
                                    html: mensajeDeuda,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: '📄 Ver Documento',
                                    cancelButtonText: 'Cerrar',
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#6c757d'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.open(openUrl, '_blank');
                                    }
                                    limpiarYRedirigir();
                                });
                            } else {
                                // Fallback sin SweetAlert
                                let mensaje =
                                    `¡Venta guardada exitosamente!\nNúmero: ${data.data.numero_completo}\nTotal: S/ ${data.data.total}`;
                                mensaje += `\n\n⚠️ DEUDA GENERADA: S/ ${datosEmision.deuda.toFixed(2)}`;
                                mensaje += `\nPago recibido: S/ ${datosEmision.entrega.toFixed(2)}`;
                                mensaje +=
                                    `\nCliente: ${clienteData ? JSON.parse(clienteData).nombre : 'Cliente Contado'}`;
                                mensaje += `\n\n¿Desea ver el documento?`;

                                if (confirm(mensaje)) {
                                    window.open(openUrl, '_blank');
                                }
                                limpiarYRedirigir();
                            }
                        } else {
                            // Sin deuda - comportamiento normal con SweetAlert
                            Swal.fire({
                                title: '¡Venta exitosa!',
                                html: `<p>Número: <strong>${data.data.numero_completo}</strong></p><p>Total: <strong>S/ ${data.data.total}</strong></p>`,
                                icon: 'success',
                                timer: 2500,
                                showConfirmButton: false
                            });

                            // Actualizar la URL de la ventana que ya abrimos
                            if (pdfWindow && !pdfWindow.closed) {
                                pdfWindow.location.href = openUrl;
                            }

                            limpiarYRedirigir();
                        }

                        function limpiarYRedirigir() {
                            // Limpiar ticket y datos temporales del localStorage/sessionStorage si existe
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
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Error desconocido al guardar la venta',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                        btnAceptar.disabled = false;
                        btnAceptar.textContent = 'CONFIRMAR VENTA';
                        // Cerrar la ventana de carga si hubo error
                        if (pdfWindow && !pdfWindow.closed) {
                            pdfWindow.close();
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
                    btnAceptar.disabled = false;
                    btnAceptar.textContent = 'CONFIRMAR VENTA';
                    // Cerrar la ventana de carga si hubo error
                    if (pdfWindow && !pdfWindow.closed) {
                        pdfWindow.close();
                    }
                });
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
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
                                        {{ number_format($item->precio * $item->cantidad, 2) }}</td>
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
                            <input id="entrega" type="number" step="0.01" value="{{ $total ?? '0.00' }}"
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
                        <input type="text" id="serie"
                            value="{{ $serieDocumento ?? ($company->serie_boleta ?? 'B001') }}"
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
                        let mensaje =
                            `¡Venta guardada exitosamente!\nNúmero: ${data.data.numero_completo}\nTotal: S/ ${data.data.total}`;

                        // Si hay deuda, mostrarla en el mensaje
                        if (datosEmision.deuda > 0) {
                            mensaje += `\n\n⚠️ DEUDA GENERADA: S/ ${datosEmision.deuda.toFixed(2)}`;
                            mensaje += `\nPago recibido: S/ ${datosEmision.entrega.toFixed(2)}`;
                            mensaje += `\nCliente: ${clienteData ? JSON.parse(clienteData).nombre : 'Cliente Contado'}`;
                        }

                        alert(mensaje);

                        // Abrir PDF correspondiente en nueva pestaña (8cm para tickets)
                        const ventaId = data.data.venta_id;
                        const urlA4 = '{{ route('pos.pdf', ['id' => ':id', 'format' => 'default']) }}'.replace(
                            ':id', ventaId);
                        const url8cm = '{{ route('pos.pdf', ['id' => ':id', 'format' => '8cm']) }}'.replace(
                            ':id', ventaId);
                        if (isProforma === '1' || isProforma === 1) {
                            urlA4 = '{{ route('cotizaciones.pdfCotizacion', ':id') }}'.replace(':id', ventaId);
                            url8cm = '{{ route('cotizaciones.pdfCotizacion8cm', ':id') }}'.replace(':id', ventaId);
                        }
                        const openUrl = (tipoDocumentoSeleccionado === 'ticket') ? url8cm : urlA4;
                        window.open(openUrl, '_blank');

                        // Limpiar ticket y datos temporales del localStorage/sessionStorage si existe
                        try {
                            localStorage.removeItem('ticketPOS');
                            sessionStorage.removeItem('ticketPOS');
                            // También eliminar venta persistente (auto-save) y backups en session
                            localStorage.removeItem('ventaPersistentePOS');
                            sessionStorage.removeItem('ticketGuardadoPOS');
                            sessionStorage.removeItem('clienteGuardadoPOS');
                        } catch (e) {
                            console.warn('No se pudieron limpiar algunas claves de storage:', e);
                        }

                        // Redirigir al POS
                        window.location.href = '{{ route('pos.index') }}';
                    } else {
                        alert('Error al guardar la venta: ' + (data.message || 'Error desconocido'));
                        btnAceptar.disabled = false;
                        btnAceptar.textContent = 'Aceptar';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al guardar la venta');
                    btnAceptar.disabled = false;
                    btnAceptar.textContent = 'Aceptar';
                });
        }

        function cancel() {
            if (confirm('¿Está seguro que desea cancelar? Se perderán todos los datos.')) {
                window.location = '{{ route('pos.index') }}';
            }
        }

        // Calcular cambio inicial
        document.addEventListener('DOMContentLoaded', function() {
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

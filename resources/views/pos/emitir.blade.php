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

    <div style="max-width:1200px;margin:20px auto;border:1px solid #ddd;padding:16px;background:#fff;">
        <h3 style="text-align:center;color:#b33;">Comprobante de venta</h3>

        <!-- Información del cliente -->
        @if (isset($clienteData) && $clienteData)
            <div class="cliente-info">
                <strong>Cliente:</strong> {{ json_decode($clienteData)->nombre ?? 'Cliente Contado' }}
                @if (json_decode($clienteData)->numero_documento ?? null)
                    - {{ json_decode($clienteData)->tipo_documento ?? 'DNI' }}:
                    {{ json_decode($clienteData)->numero_documento }}
                @endif
            </div>
        @else
            <div class="cliente-info">
                <strong>Cliente:</strong> Cliente Contado
            </div>
        @endif

        <!-- Lista de productos -->
        @if (isset($ticketData) && $ticketData)
            <div class="productos-emitir">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>P.U.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="productos-ticket">
                        @foreach (json_decode($ticketData) as $item)
                            <tr>
                                <td>{{ $item->nombre }}</td>
                                <td>{{ $item->cantidad }}</td>
                                <td>S/ {{ number_format($item->precio, 2) }}</td>
                                <td>S/ {{ number_format($item->precio * $item->cantidad, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div style="display:flex;gap:20px;">
            <div style="flex:1;border-right:1px solid #eee;padding-right:12px;">
                <div style="font-size:18px;font-weight:600;margin-bottom:8px;">TOTAL <span
                        style="float:right;font-size:22px;">S/ <span id="total-amount">{{ $total ?? '0.00' }}</span></span>
                </div>

                <div style="margin:12px 0;">
                    <label>ENTREGA</label>
                    <input id="entrega" type="number" step="0.01" value="{{ $total ?? '0.00' }}"
                        style="width:100%;padding:8px;margin-top:6px;border:1px solid #bcd;" onkeyup="calcularCambio()">
                </div>

                <div style="margin:12px 0;">
                    <label>CAMBIO</label>
                    <div style="color:#b33;margin-top:6px;font-weight:700;">S/ <span id="cambio">0.00</span></div>
                </div>

                <div style="margin:12px 0;">
                    <label>PAGO</label>
                    <select id="medio-pago" style="width:100%;padding:8px;margin-top:6px;">
                        @foreach ($metodos as $metodo)
                            <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-top:18px;display:flex;gap:8px;">
                    <button onclick="accept()"
                        style="background:#6b2e51;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">Aceptar</button>
                    <button onclick="cancel()"
                        style="background:#0b8a7e;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">Cancelar</button>
                </div>
            </div>

            <div style="flex:1;padding-left:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:12px;color:#666;">Fecha Emisión</div>
                        <div>{{ now()->format('d/m/Y H:i') }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:12px;color:#666;">Vendedor</div>
                        <div>{{ $user->name ?? '-' }}</div>
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <label>Observaciones</label>
                    <textarea id="observaciones" style="width:100%;min-height:80px;border:1px solid #bcd;padding:8px;"></textarea>
                </div>

                <div style="margin-top:12px;display:flex;gap:8px;">
                    <input type="text" id="guia-remision" placeholder="Guía Remisión Rem."
                        style="flex:1;padding:8px;border:1px solid #bcd;">
                    <input type="text" id="guia-transporte" placeholder="Guía Remisión Trans."
                        style="flex:1;padding:8px;border:1px solid #bcd;">
                </div>

                <div style="margin-top:12px;display:flex;gap:8px;align-items:center;">
                    <div style="width:60px;">Serie</div>
                    <input type="text" id="serie"
                        value="{{ $serieDocumento ?? ($company->serie_boleta ?? 'B001') }}"
                        style="width:80px;padding:8px;border:1px solid #bcd;" readonly>
                    <input type="text" id="numero" value="0001" style="flex:1;padding:8px;border:1px solid #bcd;"
                        readonly>
                </div>

                <!-- Información del tipo de documento seleccionado -->
                <div style="margin-top:12px;">
                    <label>Tipo de Comprobante Seleccionado</label>
                    <div style="background:#e3f2fd;padding:10px;border-radius:4px;margin-top:6px;">
                        <strong>{{ ucfirst($tipoDocumento ?? 'boleta') }}</strong>
                        @if ($tipoDocumento == 'ticket')
                            - Comprobante interno
                        @elseif($tipoDocumento == 'boleta')
                            - Para personas naturales
                        @elseif($tipoDocumento == 'factura')
                            - Para empresas con RUC
                        @elseif($tipoDocumento == 'nota-venta')
                            - Documento informativo
                        @endif
                    </div>
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
                        let mensaje = `¡Venta guardada exitosamente!\nNúmero: ${data.data.numero_completo}\nTotal: S/ ${data.data.total}`;
                        
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

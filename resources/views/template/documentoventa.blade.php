<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Electrónica</title>
    <style>
        /* Basic */
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 10px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
        }

        .header img {
            height: 40px;
            position: absolute;
            top: 0;
            left: 0;
        }

        .section {}

        .info-table,
        .items-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .info-table td,
        .items-table th,
        .items-table td,
        .summary-table th,
        .summary-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .info-table td {
            border: none;
            padding: 5px;
        }

        .text-right {
            text-align: right;
        }

        .bg-gray {
            background-color: #F4F4F4;
        }

        /* Reserve a comfortable footer area (not too large to avoid blank extra page) */
        .page-space-bottom {
            padding-bottom: 120px;
        }

        /* Footer QR: fixed so its position is predictable in PDF */
        .footer-qr {
            position: fixed;
            bottom: 18px;
            /* distancia desde el borde inferior de la página */
            right: 18px;
            /* distancia desde el borde derecho */
            width: 110px;
            text-align: center;
            font-size: 9px;
            line-height: 1.1;
        }

        .footer-qr img {
            width: 100px;
            height: 100px;
            display: block;
            margin: 0 auto;
        }

        .footer-qr .hash {
            margin-top: 6px;
            word-break: break-all;
            font-size: 8px;
            color: #222;
        }

        /* Small: make sure long tables don't break awkwardly */
        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div>
                <img src="{{ $logo }}">
                <div style="position: absolute;top: 40;left: 0;">
                    <span style="font-size: 12px;">
                        {{ $empresa->direccion_fiscal }} <br>
                        {{ $empresa->department }} - {{ $empresa->province }} - {{ $empresa->district }}<br>
                        Cel.: {{ $empresa->phone }}<br>
                        Correo : {{ $empresa->email }}<br>
                        Web : {{ $empresa->website }}</span>
                </div>
            </div>
            <div
                style="position: absolute;top: 0;right: 0;padding: 5px; text-align: center; width: 200px; border: 2px solid black; border-radius: 10px;">
                <p>RUC: 20489629551</p>
                <h1>{{ $tipoDocumento }} ELECTRÓNICA</h1>
                <p>NRO: {{ $venta->serie }}-{{ agregarCerosIzquierda($venta->numero) }}</p>
            </div>
        </div>

        @php
            // Cálculos
            $subtotal = 0.0;
            foreach ($servicios as $s) {
                $subtotal += floatval($s->costo ?? ($s->precio_unitario ?? 0));
            }
            // si por alguna razón no hay servicios, fallback a propuesta
            if ($subtotal <= 0) {
                $subtotal = floatval($propuesta->costo_unitario ?? 0);
            }

            $descuentoMonto = floatval($venta->descuento_monto ?? 0);
            $dto_total = $descuentoMonto;
            $op_exonerada = 0.0;
            $op_inafecta = 0.0;
            $op_gravada = max(0, $subtotal - $descuentoMonto);
            $op_gratuita = 0.0;
            $isc = floatval($venta->isc ?? 0.0);
            // IGV: en tu lógica anterior guardas el monto en venta->igv
            $igv = floatval($venta->igv ?? 0.0);

            // Si por seguridad no existe igv guardado y propuesta tiene porcentaje, recalculamos
            if (empty($igv) && !empty($propuesta->igv)) {
                $igv = round($op_gravada * floatval($propuesta->igv), 2);
            }

            $total_calculado = round($op_gravada + $igv + $isc, 2);
            $total_mostrar = floatval($venta->total ?? ($propuesta->costo_total ?? $total_calculado));

            $detraccion_aplica = boolval($venta->aplica_detraccion);
            $detraccion_pct = floatval($venta->detraccion_porcentaje ?? 0);
            $detraccion_monto = floatval($venta->detraccion_monto ?? 0);

            $total_neto_pendiente = floatval($venta->total_neto_pendiente ?? $total_mostrar - $detraccion_monto);
            $cuotas = [];
            if (!empty($venta->cuotas)) {
                $cuotas = json_decode($venta->cuotas, true);
            }
            $mostrar_cuotas = intval($venta->total_cuotas ?? 1) > 1 && !empty($cuotas);
        @endphp

        <!-- Información General -->
        <div class="section" style="margin-top: 120px;">
            <div style="width: 100%; text-align: center; padding: 1px; background-color: #EBEEF1">
                <h2>INFORMACIÓN GENERAL</h2>
            </div>
            <table class="info-table">
                <tr>
                    <td>Señor(es):</td>
                    <td>{{ $cliente->razon_social }}</td>
                    <td>Moneda:</td>
                    <td>{{ $venta->moneda == 1 ? 'Soles' : 'Dólares' }}</td>
                </tr>
                <tr>
                    <td>RUC:</td>
                    <td>{{ $cliente->numero_documento }}</td>
                    <td>Condición de Pago:</td>
                    <td>{{ $venta->id_tipo_pago == 1 ? 'Contado' : 'Credito' }}</td>
                </tr>
                <tr>
                    <td>Dirección:</td>
                    <td>{{ $cliente->direccion ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Fecha de Vencimiento:</td>
                    <td>{{ \Carbon\Carbon::parse($venta->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td>Fecha de Emisión:</td>
                    <td>{{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>

        <!-- Detalle de Productos -->
        <div class="section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="background-color: #EBEEF1; border: none;">CÓDIGO</th>
                        <th style="background-color: #EBEEF1; border: none;">CANT.</th>
                        <th style="background-color: #EBEEF1; border: none;">UNIDAD</th>
                        <th style="background-color: #EBEEF1; border: none;">DESCRIPCIÓN</th>
                        <th style="background-color: #EBEEF1; border: none;">PRECIO</th>
                        <th style="background-color: #EBEEF1; border: none;">IMPORTE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($servicios as $i => $servicio)
                        <tr class="{{ $i % 2 !== 0 ? 'bg-gray' : '' }}">
                            <td>{{ $servicio->id_servici ?? ($servicio->servicio_id ?? '') }}</td>
                            <td>1</td>
                            <td>001</td>
                            <td>{{ $servicio->nombre ?? ($servicio->nombre_servicio ?? '') }}</td>
                            <td class="text-right">
                                {{ number_format($servicio->costo ?? ($servicio->precio_unitario ?? 0), 2) }}</td>
                            <td class="text-right">{{ number_format($servicio->costo ?? ($servicio->importe ?? 0), 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Resumen -->
        <div class="section">
            <table class="summary-table">
                <thead>
                    <tr>
                        <th style="border: none; border-top: 1px solid black;">Sub Total:</th>
                        <th style="border: none; border-top: 1px solid black;">Dto. Total:</th>
                        <th style="border: none; border-top: 1px solid black;">Op. Exonerada:</th>
                        <th style="border: none; border-top: 1px solid black;">Op. Inafecta:</th>
                        <th style="border: none; border-top: 1px solid black;">Op. Gravada:</th>
                        <th style="border: none; border-top: 1px solid black;">Op. Gratuita:</th>
                        <th style="border: none; border-top: 1px solid black;">ISC:</th>
                        <th style="border: none; border-top: 1px solid black;">IGV
                            ({{ isset($propuesta->igv) ? $propuesta->igv * 100 : 18 }}%):</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: none; border-bottom: 1px solid black;">{{ number_format($subtotal, 2) }}
                        </td>
                        <td style="border: none; border-bottom: 1px solid black;">{{ number_format($dto_total, 2) }}
                        </td>
                        <td style="border: none; border-bottom: 1px solid black;">{{ number_format($op_exonerada, 2) }}
                        </td>
                        <td style="border: none; border-bottom: 1px solid black;">{{ number_format($op_inafecta, 2) }}
                        </td>
                        <td style="border: none; border-bottom: 1px solid black;">{{ number_format($op_gravada, 2) }}
                        </td>
                        <td style="border: none; border-bottom: 1px solid black;">{{ number_format($op_gratuita, 2) }}
                        </td>
                        <td style="border: none; border-bottom: 1px solid black;">{{ number_format($isc, 2) }}</td>
                        <td style="border: none; border-bottom: 1px solid black;">{{ number_format($igv, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="border: none; border-bottom: 1px solid black;"></td>
                        <td style="border: none; border-bottom: 1px solid black;">
                            <span style="font-weight: bold; font-size: 12px;">TOTAL:
                                S/{{ number_format($total_mostrar, 2) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; border-bottom: 1px solid black;"> SON :
                            {{ numeroALetras($total_mostrar) }} Soles.</td>
                        <td colspan="6" style="border: none; border-bottom: 1px solid black;"></td>
                        <td style="border: none; border-bottom: 1px solid black;">NRO. DE ITEMS :
                            {{ count($servicios) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Créditos / Cuotas / Detracción -->
            <table class="summary-table" style="margin-top: -1px;">
                <tbody>
                    <tr>
                        <td style="border: none;">INFORMACIÓN DEL CREDITO</td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;">INFORMACIÓN DE DETRACCIÓN</td>
                        <td style="border: none;"></td>
                    </tr>
                    <tr>
                        <td style="border: none;">
                            @if ($venta->id_tipo_pago == 2)
                                MONTO NETO PENDIENTE DE PAGO: S/{{ number_format($total_neto_pendiente, 2) }}
                            @else
                                MONTO PAGADO: S/{{ number_format($total_mostrar, 2) }}
                            @endif
                        </td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;">DETRACCIÓN APLICADA: {{ $detraccion_aplica ? 'SI' : 'NO' }}</td>
                        <td style="border: none;">
                            @if ($detraccion_aplica)
                                {{ $detraccion_pct }}% - S/{{ number_format($detraccion_monto, 2) }}
                            @else
                                {{ number_format(0, 2) }}
                            @endif
                        </td>
                    </tr>

                    @if ($venta->id_tipo_pago == 2)
                        {{-- Solo mostrar información de cuotas si es crédito --}}
                        <tr>
                            <td style="border: none;">TOTAL DE CUOTAS: {{ intval($venta->total_cuotas ?? 1) }}</td>
                            <td colspan="6" style="border: none;"></td>
                            <td style="border: none;"></td>
                        </tr>

                        @if ($mostrar_cuotas)
                            <tr>
                                <td style="border: none; background-color: #EBEEF1;">NRO. CUOTA</td>
                                <td style="border: none; background-color: #EBEEF1;">FECHA DE VENCIMIENTO</td>
                                <td style="border: none; background-color: #EBEEF1;">MONTO</td>
                                <td style="border: none;"></td>
                                <td style="border: none;"></td>
                                <td style="border: none;"></td>
                                <td style="border: none;"></td>
                                <td style="border: none;"></td>
                            </tr>

                            @foreach ($cuotas as $c)
                                <tr>
                                    <td style="border: none; border-bottom: 1px solid black;">CUOTA {{ $c['nro'] }}
                                    </td>
                                    <td style="border: none; border-bottom: 1px solid black;">
                                        {{ \Carbon\Carbon::parse($c['fecha_vencimiento'])->format('d/m/Y') }}</td>
                                    <td style="border: none; border-bottom: 1px solid black;">
                                        S/{{ number_format($c['monto'], 2) }}</td>
                                    <td colspan="5" style="border: none; border-bottom: 1px solid black;"></td>
                                </tr>
                            @endforeach
                        @else
                            {{-- Si es crédito pero solo una cuota --}}
                            <tr>
                                <td style="border: none; border-bottom: 1px solid black;">CUOTA 1</td>
                                <td style="border: none; border-bottom: 1px solid black;">
                                    {{ \Carbon\Carbon::parse($venta->fecha_vencimiento)->format('d/m/Y') }}</td>
                                <td style="border: none; border-bottom: 1px solid black;">
                                    S/{{ number_format($total_neto_pendiente, 2) }}</td>
                                <td colspan="5" style="border: none; border-bottom: 1px solid black;"></td>
                            </tr>
                        @endif
                    @endif

                </tbody>
            </table>

            <!-- Cuentas bancarias / detraccion info -->
            <table class="summary-table" style="margin-top: -1px;">
                <tbody>
                    <tr>
                        <td style="border: none;">CUENTAS BANCARIAS</td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;">N</td>
                        <td style="border: none;"></td>
                    </tr>
                    <tr>
                        <td style="border: none;"><b>{{ $empresa->bank }} {{ $empresa->account_type }}</b></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"><b>{{ $empresa->account_number }}</b></td>
                        <td style="border: none;"></td>
                    </tr>
                    <tr>
                        <td style="border: none;">CTA. CTE.: {{ $empresa->cci }}</td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Debug QR -->
        <!-- QR Debug: {{ $qr_image ? 'QR Encontrado (length: ' . strlen($qr_image) . ')' : 'QR NO encontrado' }} -->

        @if (!empty($qr_image))
            <!-- Footer QR: posición fija para PDF -->
            <div class="footer-qr">
                <img src="{{ $qr_image }}" alt="QR SUNAT">
                @if (!empty($qr_hash))
                    <div class="hash"><strong>Código Hash:</strong> {{ $qr_hash }}</div>
                @endif
            </div>
        @else
            <!-- QR no disponible - Debug -->
            <div class="footer-qr">
                <div style="border: 1px solid red; padding: 10px; text-align: center;">
                    <small>QR no disponible</small><br>
                    <small>QR_Image: {{ $qr_image ?? 'NULL' }}</small>
                </div>
            </div>
        @endif

    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0.8cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            /* Tamaño similar al de la imagen original */
            color: #1a1a1a;
            line-height: 1.2;
        }

        .container {
            width: 100%;
        }

        /* Tablas base */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        /* Estilo para los recuadros con bordes redondeados (evita el error de row width) */
        .rounded-box {
            border: 1px solid #333;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 8px;
            width: 100%;
            box-sizing: border-box;
            /* Crucial para Dompdf */
        }

        /* Encabezado */
        .header-box {
            border: none;
            margin-bottom: 15px;
        }

        .ruc-container {
            border: 1.5px solid #006BB6;
            border-radius: 10px;
            text-align: center;
            overflow: hidden;
            width: 220px;
        }

        .ruc-header {
            background-color: #006BB6;
            color: white;
            padding: 5px;
            font-weight: bold;
            font-size: 11px;
        }

        .ruc-body {
            padding: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Tabla de Productos - Estilo exacto Mio Cane */
        .items-table th {
            background-color: #006BB6;
            color: white;
            padding: 5px;
            border: 0.5px solid #006BB6;
            font-size: 8px;
        }

        .items-table td {
            padding: 4px;
            border: 0.5px solid #ccc;
            text-align: center;
        }

        /* Sección de Totales */
        .bottom-section {
            width: 100%;
            margin-top: 10px;
        }

        .total-box {
            float: right;
            width: 180px;
            border: 1px solid #ccc;
        }

        .total-box td {
            padding: 4px;
            border: 0.5px solid #ccc;
        }

        .bg-total {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .clear {
            clear: both;
        }

        /* Pie de página absoluto */
        .page-footer {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #333;
            border-top: 0.5px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="page-footer">
        {{ $empresa->ticket_footer_message ?? 'Gracias por su preferencia' }}
    </div>
    <div class="container">
        <table class="header-box">
            <tr>
                <td style="width: 20%;"><img src="{{ $logo }}" style="width: 120px;"></td>
                <td style="width: 45%; text-align: center; padding-top: 10px;">
                    <strong style="font-size: 11px;">{{ $empresa->nombre_comercial }}</strong><br>
                    {{ $empresa->direccion_fiscal }}<br>
                    {{ $empresa->email }}
                </td>
                <td style="width: 35%;" align="right">
                    <div class="ruc-container">
                        <div class="ruc-body">R.U.C.: {{ $empresa->ruc }}</div>
                        <div class="ruc-header">{{ $tipoDocumento }}</div>
                        <div class="ruc-body">Nro. {{ $venta->serie }}-{{ $venta->numero }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="rounded-box">
            <table style="border:none; margin:0;">
                <tr>
                    <td style="font-weight:bold; width: 100px;">Nombre / Razón Social</td>
                    <td>: {{ $cliente->nombre }}</td>
                    <td style="font-weight:bold; width: 80px;">Fecha de Emisión</td>
                    <td>: {{ $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y H:i') : ($venta->fecha ?? '-') }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Dirección</td>
                    <td>: {{ $cliente->direccion }}</td>
                    <td style="font-weight:bold;">Guía de Remisión</td>
                    <td>: {{ $venta->guia ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Cond. de Pago</td>
                    <td>: {{ $venta->condiciones_pago }}</td>
                    @if($venta->fecha_vencimiento && $venta->fecha_vencimiento->format('Y-m-d') !== $venta->fecha_emision->format('Y-m-d'))
                        <td style="font-weight:bold;">Fecha Venc.</td>
                        <td>: {{ $venta->fecha_vencimiento->format('d/m/Y') }}</td>
                    @endif
                </tr>
                <tr>
                    <td style="font-weight:bold;">RUC / DNI</td>
                    <td>: {{ $cliente->numero_documento }}</td>
                    <td style="font-weight:bold;">Método Pago</td>
                    <td>: {{ $venta->tipoPago->nombre ?? 'EFECTIVO' }}</td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>CÓDIGO</th>
                    <th style="width: 35%;">DESCRIPCIÓN</th>
                    <th>UNID.</th>
                    <th>CANTIDAD</th>
                    <th>DESC.</th>
                    <th>P. UNITARIO</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($servicios as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->servicio_id ?? $item->producto_id }}</td>
                        <td style="text-align: left;">
                            {{ str_replace('(Marca: ', '/ ', str_replace(')', '', $item->nombre_servicio ?? $item->descripcion)) }}
                        </td>
                        <td>UNIDAD</td>
                        <td>{{ number_format($item->cantidad, 2) }}</td>
                        <td style="text-align: right;">
                            {{ number_format(($item->precio_unitario * $item->cantidad) - $item->importe, 2) }}
                        </td>
                        <td style="text-align: right;">{{ number_format($item->precio_unitario, 2) }}</td>
                        <td style="text-align: right;">
                            {{ number_format($item->importe, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="bottom-section">
            <div style="float: left; width: 60%;">
                @if($empresa->bank)
                <div style="margin-bottom: 10px; padding: 5px; border: 0.5px solid #ccc; font-size: 8px;">
                    <strong style="color: #006BB6;">CUENTAS BANCARIAS:</strong><br>
                    <strong>Banco:</strong> {{ $empresa->bank }}<br>
                    <strong>{{ $empresa->account_type == 'corriente' ? 'Cta. Corriente' : 'Cta. Ahorros' }}:</strong> {{ $empresa->account_number }}
                    @if($empresa->cci)
                        | <strong>CCI:</strong> {{ $empresa->cci }}
                    @endif
                </div>
                @endif
                <strong>SON:</strong> {{ numeroALetras($venta->total) }}<br><br>
                <strong>Información Adicional:</strong><br>
                {{ $observaciones ?? 'Sin observaciones' }}
            </div>

            <table class="total-box">
                <tr>
                    <td>Total a pagar</td>
                    <td align="right">S/</td>
                    <td align="right">
                         {{ number_format($venta->total, 2) }}
                    </td>
                </tr>
                <tr>
                    <td>Total Descuento</td>
                    <td align="right">S/</td>
                    <td align="right">{{ number_format($venta->descuento_monto ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>IGV</td>
                    <td align="right">S/</td>
                    <td align="right">{{ number_format($venta->igv, 2) }}</td>
                </tr>
                <tr class="bg-total">
                    <td>Importe total</td>
                    <td align="right">S/</td>
                    <td align="right">{{ number_format($venta->total, 2) }}</td>
                </tr>
                @if ($venta->deuda)
                    <tr>
                        <td>Abonado</td>
                        <td align="right">S/</td>
                        <td align="right">{{ number_format($venta->deuda->monto_pagado, 2) }}</td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td>Pendiente</td>
                        <td align="right">S/</td>
                        <td align="right">{{ number_format($venta->deuda->monto_deuda, 2) }}</td>
                    </tr>
                @endif
                @if (isset($venta->vuelto) && $venta->vuelto > 0)
                    <tr>
                        <td>Recibido</td>
                        <td align="right">S/</td>
                        <td align="right">{{ number_format($venta->monto_recibido, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Vuelto</td>
                        <td align="right">S/</td>
                        <td align="right">{{ number_format($venta->vuelto, 2) }}</td>
                    </tr>
                @endif
            </table>
            <div class="clear"></div>
        </div>


        <div style="text-align: center; margin-top: 20px;">
            @if (isset($qr_image))
                <img src="{{ $qr_image }}" style="width: 80px;">
            @endif
            <br>
            <span style="font-size: 7px;">Representación impresa de la Factura Electrónica</span>
        </div>
    </div>
</body>

</html>

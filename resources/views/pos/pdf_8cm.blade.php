<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            /* Fuentes sans-serif imprimen más claro en térmicas */
            font-size: 9pt;
            color: #000;
            margin: 0;
            padding: 2px;
            line-height: 1.1;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: 800; /* Extra bold para térmicas */
        }

        /* Encabezado */
        .empresa-nombre {
            font-size: 11pt;
            font-weight: 900;
            margin-bottom: 2px;
        }

        .documento-caja {
            border: 1.5px solid #000;
            margin: 8px 0;
            padding: 4px;
            font-size: 11pt;
            font-weight: 900;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
            color: #000;
        }

        .table-items thead {
            border-bottom: 2px solid #000;
            border-top: 2px solid #000;
        }

        .table-items {
            table-layout: fixed;
        }

        .table-items td {
            vertical-align: top;
            padding: 3px 2px;
            font-size: 8.5pt;
            word-wrap: break-word;
        }

        .text-right {
            text-align: right;
        }

        .hr {
            border-top: 2px dashed #000;
            margin: 6px 0;
        }

        .qr-section {
            margin-top: 8px;
        }

        .qr-section img {
            width: 100px;
            height: 100px;
            /* Filtro para asegurar negro puro */
            filter: contrast(200%);
        }

        .monto-letras {
            font-size: 9pt;
            margin: 6px 0;
            font-style: italic;
        }
        
        .small {
            font-size: 8pt;
        }
    </style>
</head>

<body>
    <div class="center">
        @if (!empty($logo))
            <img src="{{ $logo }}" style="max-width: 150px; height: auto;">
        @endif

        <div class="bold empresa-nombre">{{ $empresa->razon_social ?? $empresa->nombre ?? '' }}</div>
        <div class="small">
            RUC: {{ $empresa->ruc ?? '20538381978' }}<br>
            {{ isset($venta) && $venta->sucursal_ref && $venta->sucursal_ref->direccion ? $venta->sucursal_ref->direccion : $empresa->direccion }}<br>
            Cel/Tel: {{ isset($venta) && $venta->sucursal_ref && $venta->sucursal_ref->telefono ? $venta->sucursal_ref->telefono : ($empresa->phone ?? '-') }}
        </div>

        <div class="documento-caja bold">
            @if(isset($venta->serie))
                {{ $venta->tipo_documento ?? 'TICKET' }}<br>
                {{ $venta->serie }}-{{ str_pad($venta->numero, 8, '0', STR_PAD_LEFT) }}
            @else
                {{ 'COTIZACIÓN' }}<br>
                {{ $venta->numero }}
            @endif
        </div>
    </div>

    <table>
        <tr>
            <td class="bold" width="30%">Fecha E:</td>
            <td>{{ ($venta->fecha_emision ?? $venta->fecha)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="bold">RUC/DNI:</td>
            <td>{{ $cliente->numero_documento }}</td>
        </tr>
        <tr>
            <td class="bold">Cliente:</td>
            <td class="uppercase">{{ $cliente->nombre }}</td>
        </tr>
        <tr>
            <td class="bold">Dirección:</td>
            <td class="uppercase small">{{ $cliente->direccion }}</td>
        </tr>
        <tr>
            <td class="bold">Método Pago:</td>
            <td class="uppercase">{{ $venta->tipoPago->nombre ?? 'EFECTIVO' }}</td>
        </tr>
        @if($venta->fecha_vencimiento && $venta->fecha_vencimiento->format('Y-m-d') !== ($venta->fecha_emision ?? $venta->fecha)->format('Y-m-d'))
        <tr>
            <td class="bold">Fecha V.:</td>
            <td>{{ $venta->fecha_vencimiento->format('d/m/Y') }}</td>
        </tr>
        @endif
        <tr>
            <td class="bold">Le atendió:</td>
            <td>{{ $venta->user->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr class="bold">
                <td width="10%">Cant</td>
                <td width="8%">U.M.</td>
                <td width="35%">Descripción</td>
                <td width="16%" class="text-right">P.U.</td>
                <td width="13%" class="text-right">Desc.</td>
                <td width="18%" class="text-right">Total</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($servicios as $item)
                @php
                    $umCodigo = $item->almacenIngresoDetalle?->producto?->unidadMedida?->codigo ?? 'NIU';
                    $nombreLimpio = str_replace(['(Precio Corp.)', '(Precio Publico)', '(Precio Pub.)'], '', $item->nombre_servicio ?? $item->descripcion ?? '');
                    $nombreLimpio = trim($nombreLimpio, ' /');
                @endphp
                <tr>
                    <td>{{ number_format($item->cantidad, 2) }}</td>
                    <td style="font-size: 7pt;">{{ $umCodigo }}</td>
                    <td style="font-size: 7.5pt; line-height: 1.1;">
                        {{ $nombreLimpio }}
                    </td>
                    <td class="text-right">{{ number_format($item->precio_unitario, 2) }}</td>
                    <td class="text-right">{{ number_format(($item->precio_unitario * $item->cantidad) - $item->subtotal, 2) }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="hr"></div>
    <table style="margin-left: auto; width: 70%;">
        <tr>
            <td class="text-right">Total a pagar:</td>
            <td class="text-right">S/
                 {{ number_format($venta->total, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">Total Descuento:</td>
            <td class="text-right">S/ {{ number_format($venta->descuento_monto ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">IGV:</td>
            <td class="text-right">S/ {{ number_format($venta->igv ?? $venta->total * 0.18, 2) }}</td>
        </tr>
        <tr class="bold">
            <td class="text-right">Importe total:</td>
            <td class="text-right">S/ {{ number_format($venta->total, 2) }}</td>
        </tr>
        @if ($venta->deuda)
            <tr>
                <td class="text-right">Abonado:</td>
                <td class="text-right">S/ {{ number_format($venta->deuda->monto_pagado, 2) }}</td>
            </tr>
            <tr class="bold">
                <td class="text-right">Pendiente:</td>
                <td class="text-right">S/ {{ number_format($venta->deuda->monto_deuda, 2) }}</td>
            </tr>
        @endif
        @if (isset($venta->vuelto) && $venta->vuelto > 0)
            <tr>
                <td class="text-right">Recibido:</td>
                <td class="text-right">S/ {{ number_format($venta->monto_recibido, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right">Vuelto:</td>
                <td class="text-right">S/ {{ number_format($venta->vuelto, 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="monto-letras">
        SON: <span class="uppercase">{{ $venta->monto_letras ?? numeroALetras($venta->total) }}</span>
    </div>

    @if (isset($venta->cuotas))
        <div class="hr"></div>
        <div class="center bold small">Cuotas de pago</div>
        <table>
            <tr class="bold small">
                <td>CUOTA</td>
                <td>FECHA</td>
                <td class="text-right">MONTO</td>
            </tr>
            @foreach ($venta->cuotas as $index => $cuota)
                <tr class="small">
                    <td>Cuota 00{{ $index + 1 }}</td>
                    <td>{{ $cuota->fecha }}</td>
                    <td class="text-right">S/ {{ number_format($cuota->monto, 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="hr"></div>

    @if($empresa->account_number || $empresa->bank)
    <div class="center small" style="border: 1px solid #000; padding: 4px; margin-bottom: 5px;">
        <div class="bold">CUENTAS BANCARIAS</div>
        @if($empresa->bank) {{ $empresa->bank }} @endif
        @if($empresa->account_type) ({{ $empresa->account_type == 'corriente' ? 'Cta. Corr.' : 'Cta. Aho.' }}) @endif
        <br>
        <strong>Cta: {{ $empresa->account_number }}</strong>
        @if($empresa->cci)
            <br>CCI: {{ $empresa->cci }}
        @endif
    </div>
    <div class="hr"></div>
    @endif

    <div class="center small">
        Representación impresa de la {{ $venta->tipo_documento ?? 'COTIZACIÓN' }}<br>
        Consulte en: <strong>{{ $empresa->website ?? 'www.tuempresa.com' }}</strong>
    </div>

    <div class="center qr-section">
        @if (!empty($qr_image))
            <img src="{{ $qr_image }}">
        @endif
        <div class="small">{{ $empresa->ticket_footer_message ?? 'Gracias por su preferencia...' }}</div>
    </div>
</body>

</html>

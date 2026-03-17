<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0px;
        }

        body {
            font-family: "Lucida Console", Monaco, monospace;
            /* Fuente tipo ticketera */
            font-size: 8pt;
            margin: 0;
            padding: 4px; /* Reducido de 8px para mejor aprovechamiento */
            line-height: 1.2;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        /* Encabezado */
        .empresa-nombre {
            font-size: 10pt;
            margin-bottom: 2px;
        }

        .documento-caja {
            border: 1px solid #000;
            margin: 10px 0;
            padding: 5px;
            font-size: 10pt;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .table-items thead {
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
        }

        .table-items td {
            vertical-align: top;
            padding: 2px 0;
        }

        .text-right {
            text-align: right;
        }

        .hr {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .qr-section {
            margin-top: 10px;
        }

        .qr-section img {
            width: 100px;
            height: 100px;
        }

        .monto-letras {
            font-size: 8pt;
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="center">
        @if (!empty($logo))
            <img src="{{ $logo }}" style="max-width: 150px; height: auto;">
        @endif

        <div class="bold empresa-nombre">{{ $empresa->nombre }}</div>
        <div class="small">
            RUC: {{ $empresa->ruc ?? '20538381978' }}<br>
            {{ $empresa->direccion }}<br>
            {{ $empresa->telefono }}
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
        <tr>
            <td class="bold">Cajero:</td>
            <td>{{ $venta->user->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr class="bold">
                <td width="8%">CNT</td>
                <td width="42%">DESCRIPCIÓN</td>
                <td width="10%" class="text-right">DESC.</td>
                <td width="18%" class="text-right">P.U.</td>
                <td width="22%" class="text-right">IMPORTE</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($servicios as $item)
                <tr>
                    <td>{{ number_format($item->cantidad, 0) }}</td>
                    <td class="uppercase">{{ str_replace('(Marca: ', '/ ', str_replace(')', '', $item->nombre_servicio ?? $item->descripcion)) }}</td>
                    <td class="text-right">{{ number_format(($item->precio_unitario * $item->cantidad) - $item->importe, 2) }}</td>
                    <td class="text-right">{{ number_format($item->precio_unitario, 2) }}</td>
                    <td class="text-right">{{ number_format($item->importe, 2) }}</td>
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

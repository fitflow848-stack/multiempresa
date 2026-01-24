<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guía de Remisión #{{ $guia->id ?? '' }}</title>
    <style>
        @page {
            margin: 0.8cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            line-height: 1.2;
        }

        /* Contenedor principal */
        .container {
            width: 100%;
        }

        /* Encabezado: Logo - Datos Empresa - RUC Box */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
        }

        .company-info {
            text-align: center;
            padding: 0 10px;
        }

        .company-name {
            font-size: 13px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }

        .ruc-container {
            border: 1.5px solid #006BB6;
            border-radius: 10px;
            text-align: center;
            width: 200px;
            overflow: hidden;
        }

        .ruc-header {
            background: #006BB6;
            color: white;
            padding: 4px;
            font-weight: bold;
            font-size: 11px;
        }

        .ruc-body {
            padding: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Recuadros redondeados */
        .rounded-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Tablas de Datos */
        .data-table {
            width: 100%;
            border: none;
        }

        .data-table td {
            padding: 2px 4px;
            border: none;
        }

        .label {
            font-weight: bold;
            width: 20%;
        }

        .separator {
            width: 5px;
        }

        /* Tabla de Productos (Estilo Mio Cane) */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .items-table th {
            background-color: #006BB6;
            color: white;
            padding: 6px;
            font-size: 8px;
            border: 0.5px solid #006BB6;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 5px;
            border: 0.5px solid #ccc;
            text-align: center;
        }

        .text-left {
            text-align: left !important;
        }

        /* Secciones inferiores */
        .two-cols {
            width: 100%;
            display: table;
            table-layout: fixed;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .qr-section {
            width: 100px;
            text-align: center;
            padding-left: 10px;
        }

        .footer-note {
            margin-top: 10px;
            font-size: 8px;
            color: #555;
            text-align: center;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>

    <div class="container">
        <table class="header-table">
            <tr>
                <td style="width: 25%;">
                    @if (isset($logo))
                        <img src="data:image/png;base64,{{ $logo }}" style="max-width: 150px;">
                    @else
                        <div style="font-weight: bold; font-size: 16px;">LOGO</div>
                    @endif
                </td>
                <td class="company-info" style="width: 45%;">
                    <div class="company-name">{{ $empresa->razon_social }}</div>
                    <div style="margin-top:4px;">
                        Dirección Fiscal: {{ $empresa->direccion_fiscal ?? 'Dirección no disponible' }}<br>
                        Email: {{ $empresa->email ?? '' }}
                    </div>
                </td>
                <td style="width: 30%;" align="right">
                    <div class="ruc-container">
                        <div class="ruc-body">R.U.C.: {{ $empresa->ruc ?? '00000000000' }}</div>
                        <div class="ruc-header">GUÍA DE REMISIÓN ELECTRÓNICA</div>
                        <div class="ruc-body">Nro. {{ $guia->serie ?? 'T001' }}-{{ $guia->numero ?? $guia->id }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="rounded-box">
            <table class="data-table">
                <tr>
                    <td class="label">Punto de Partida</td>
                    <td class="separator">:</td>
                    <td>{{ $guia->direccion_partida ?? '-' }}</td>
                    <td class="label">Fecha Traslado</td>
                    <td class="separator">:</td>
                    <td>{{ optional($guia)->fecha_traslado ? \Carbon\Carbon::parse($guia->fecha_traslado)->format('d/m/Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Punto de Llegada</td>
                    <td class="separator">:</td>
                    <td>{{ $guia->direccion_llegada ?? '-' }}</td>
                    <td class="label">Motivo Traslado</td>
                    <td class="separator">:</td>
                    <td>{{ $guia->motivo_traslado ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="two-cols">
            <div class="col" style="width: 75%;">
                <div class="rounded-box" style="margin-right: 5px;">
                    <table class="data-table">
                        <tr>
                            <td class="label">Destinatario</td>
                            <td class="separator">:</td>
                            <td>{{ $guia->razon_llegada ?? ($guia->destino ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td class="label">RUC/DNI</td>
                            <td class="separator">:</td>
                            <td>{{ $guia->ruc_llegada ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col qr-section">
                @if (isset($qrCode))
                    <div style="width: 80px; height: 80px; margin: 0 auto;">{!! $qrCode !!}</div>
                @elseif(isset($qr_image))
                    <img src="{{ $qr_image }}" style="width: 80px;">
                @endif
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">ITEM</th>
                    <th style="width: 12%;">CÓDIGO</th>
                    <th>DESCRIPCIÓN</th>
                    <th style="width: 10%;">SERIE</th>
                    <th style="width: 8%;">CANT.</th>
                    <th style="width: 10%;">U.M.</th>
                    <th style="width: 10%;">PESO TOT.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p->cod_sap ?? ($p->producto_id ?? '-') }}</td>
                        <td class="text-left">{{ $p->descripcion ?? ($p->nombre ?? '-') }}</td>
                        <td>{{ $p->serie ?? '-' }}</td>
                        <td>{{ $p->cantidad ?? ($p->cantidad_total ?? 0) }}</td>
                        <td>{{ $p->unidad_medida ?? ($p->unidad ?? 'UND') }}</td>
                        <td>{{ $p->peso }} KG</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No hay productos registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="rounded-box" style="margin-top: 10px;">
            <strong>Observaciones:</strong><br>
            {{ $guia->observacion ?? 'Sin observaciones adicionales' }}
        </div>

        <div class="footer-note">
            Representación impresa de la Guía de Remisión Electrónica remitente.<br>
            Consulte la validez de este documento en el portal de la SUNAT.
        </div>
    </div>
    <div style="width: 100%; margin-top: 15px; font-family: Arial, sans-serif;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 110px; vertical-align: top;">
                    <div style="border: 1px solid #ccc; border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="width: 90px; height: 90px;">' . $qrSvg . '</div>
                    </div>
                </td>

                <td style="padding: 0 10px; vertical-align: top;">
                    <div
                        style="border: 1px solid #ccc; border-radius: 8px; padding: 8px; min-height: 100px; position: relative; font-size: 9px;">
                        <div style="text-align: right; margin-bottom: 5px;">
                            <span
                                style="background: #006BB6; color: white; padding: 2px 8px; border-radius: 4px; font-weight: bold;">
                                Peso bruto (Kg): {{ $guia->peso_bruto }}
                            </span>
                        </div>

                        <div style="margin-bottom: 8px;">
                            <strong>Observaciones:</strong><br>
                            <span style="color: #444;">{!! nl2br($guia->observacion) !!}</span>
                        </div>

                        <div>
                            <strong>Destinatarios:</strong><br>
                            <span style="color: #444; font-size: 8px;">{!! $destinatariosHtml !!}</span>
                        </div>
                    </div>
                </td>

                <td style="width: 150px; vertical-align: top;">
                    <div
                        style="border: 1px solid #ccc; border-radius: 8px; padding: 8px; min-height: 100px; text-align: center;">
                        <div
                            style="margin-top: 70px; border-top: 1px solid #333; padding-top: 5px; font-weight: bold; font-size: 10px; color: #333;">
                            RECIBÍ CONFORME
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div
            style="width: 100%; margin-top: 10px; text-align: center; font-size: 8px; color: #666; border-top: 0.5px solid #eee; padding-top: 5px;">
            <strong>{{ $empresa->website }}</strong> -  {{ $empresa->direccion_fiscal }}
        </div>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guía de Remisión #{{ $guia->id ?? '' }}</title>
    <style>
        /* Reservamos espacio inferior para el footer fijo */
        @page {
            margin: 0.8cm 0.8cm 3.5cm 0.8cm; /* top right bottom left */
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        /* Contenedor principal */
        .container {
            width: 100%;
            box-sizing: border-box;
            padding-bottom: 6px; /* espacio adicional para evitar que contenido quede pegado al final */
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
            table-layout: fixed;
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

        /* Tabla de Productos */
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

        /* Bloque que estará justo encima del footer */
        .bottom-info {
            margin-top: 8px;
            margin-bottom: 6px;
        }

        .bottom-info .observ-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px;
            min-height: 50px;
            box-sizing: border-box;
            font-size: 9px;
        }

        .bottom-info .representacion {
            text-align: center;
            font-size: 8px;
            color: #555;
            margin-top: 6px;
        }

        /* Footer fijo */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3.2cm; /* ajustar si necesitas más/menos espacio */
            padding: 8px 0.8cm 6px 0.8cm;
            box-sizing: border-box;
            background: transparent;
            font-size: 9px;
        }

        /* Contenido interno del footer con layout tipo tabla (compatible con Dompdf) */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: top;
            padding: 6px;
        }

        /* caja del QR */
        .footer-qr {
            width: 110px;
        }

        .qr-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            height: 100px;
            box-sizing: border-box;
        }

        .qr-box img {
            width: 80px;
            height: 80px;
            display: block;
            margin: 0 auto;
        }

        /* caja central (observaciones, destinatarios) dentro del footer */
        .footer-middle {
            padding: 0 10px;
        }

        .footer-middle .inner-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px;
            min-height: 100px;
            box-sizing: border-box;
        }

        /* caja de firma */
        .footer-sign {
            width: 150px;
            text-align: center;
        }

        .footer-sign .sign-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px;
            min-height: 100px;
            text-align: center;
            box-sizing: border-box;
        }

        .footer-sign .sign-title {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-weight: bold;
            font-size: 10px;
            color: #333;
        }

        .footer-bottom-line {
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-top: 4px;
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
                    <td class="label">Emisor</td>
                    <td class="separator">:</td>
                    <td>{{ $empresa->razon_social ?? '-' }}</td>
                    <td class="label">RUC</td>
                    <td class="separator">:</td>
                    <td>{{ $empresa->ruc ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Punto de Partida</td>
                    <td class="separator">:</td>
                    <td>{{ $guia->direccion_partida ?? '-' }}</td>
                    <td class="label">Fecha Traslado</td>
                    <td class="separator">:</td>
                    <td>{{ optional($guia)->fecha_traslado ? \Carbon\Carbon::parse($guia->fecha_traslado)->format('d/m/Y') : '-' }}</td>
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

        
    </div>

    <!-- Footer fijo -->
    <div class="footer" role="contentinfo" aria-label="footer">
        <!-- Bloque que estará encima del footer -->
        <div class="bottom-info">
            <div class="observ-box">
                <strong>Observaciones:</strong><br>
                <span style="color: #444;">{!! nl2br(e($guia->observacion ?? 'Sin observaciones adicionales')) !!}</span>
            </div>

            <div class="representacion">
                Representación impresa de la Guía de Remisión Electrónica remitente.<br>
                Consulte la validez de este documento en el portal de la SUNAT.
            </div>
        </div>
        <table class="footer-table">
            <tr>
                <td class="footer-qr">
                    <div class="qr-box">
                        @if (!empty($qr_image))
                            <img src="{{ $qr_image }}" alt="QR">
                        @endif
                    </div>
                </td>

                <td class="footer-middle">
                    <div class="inner-box">
                        <div style="text-align: right; margin-bottom: 5px;">
                            <span style="background: #006BB6; color: white; padding: 2px 8px; border-radius: 4px; font-weight: bold;">
                                Peso bruto (Kg): {{ $guia->peso_bruto }}
                            </span>
                        </div>

                        <div style="margin-bottom: 6px;">
                            <strong>Destinatarios:</strong><br>
                            <span style="color: #444; font-size: 8px;">{!! $destinatariosHtml !!}</span>
                        </div>

                    </div>
                </td>

                <td class="footer-sign">
                    <div class="sign-box">
                        <div class="sign-title">RECIBÍ CONFORME</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-bottom-line">
            <strong>{{ $empresa->website }}</strong> - {{ $empresa->direccion_fiscal }}
        </div>
    </div>

</body>

</html>
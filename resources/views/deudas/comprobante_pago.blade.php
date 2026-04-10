<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Comprobante de Pago</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 72mm;
            padding: 4px 6px;
            color: #000;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 6px;
        }

        .header .company-name {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
        }

        .header .company-sub {
            font-size: 10px;
            margin-top: 2px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            padding: 3px 0;
            text-transform: uppercase;
        }

        .row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }

        .row .label {
            display: table-cell;
            width: 50%;
        }

        .row .value {
            display: table-cell;
            width: 50%;
            text-align: right;
        }

        .info-block {
            margin-bottom: 4px;
        }

        .info-block .info-label {
            font-weight: bold;
        }

        /* Sección Debe / Abonado / Pendiente */
        .deuda-resumen {
            margin: 6px 0;
        }

        .deuda-resumen .deuda-row {
            display: table;
            width: 100%;
            padding: 1px 0;
        }

        .deuda-resumen .deuda-row .dl {
            display: table-cell;
            width: 55%;
            font-weight: bold;
        }

        .deuda-resumen .deuda-row .dv {
            display: table-cell;
            width: 45%;
            text-align: right;
        }

        .deuda-resumen .deuda-row.pendiente-row .dl,
        .deuda-resumen .deuda-row.pendiente-row .dv {
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 3px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
        }

        .footer p {
            margin: 2px 0;
        }
    </style>
</head>

<body>

    {{-- ENCABEZADO EMPRESA --}}
    <div class="header">
        @if ($logo)
            <img src="{{ $logo }}" style="max-width:110px; max-height:50px; margin-bottom:4px;">
        @endif
        <div class="company-name">{{ $empresa->razon_social ?? 'EMPRESA' }}</div>
        @if ($empresa && $empresa->descripcion)
            <div class="company-sub">{{ $empresa->descripcion }}</div>
        @endif
        @if ($empresa && $empresa->direccion)
            <div>{{ isset($deuda) && $deuda->venta && $deuda->venta->sucursal_ref && $deuda->venta->sucursal_ref->direccion ? $deuda->venta->sucursal_ref->direccion : $empresa->direccion }}</div>
        @endif
        @php
            $telefonoSucursal = isset($deuda) && $deuda->venta && $deuda->venta->sucursal_ref && $deuda->venta->sucursal_ref->telefono ? $deuda->venta->sucursal_ref->telefono : ($empresa->telefono ?? null);
            $telefonos = array_filter([$telefonoSucursal, $empresa->celular ?? null]);
        @endphp
        @if (!empty($telefonos))
            <div>Telf.: {{ implode(' – ', $telefonos) }}</div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- TÍTULO --}}
    <div class="section-title">COMPROBANTE DE ABONO</div>

    {{-- DATOS VENTA ORIGINAL --}}
    <div class="divider"></div>
    <div class="info-block">
        <div><span class="info-label">Comprobante:</span> {{ $deuda->numero_comprobante }}</div>
        <div><span class="info-label">Tipo:</span> {{ strtoupper($deuda->tipo_documento ?? 'VENTA') }}</div>
    </div>

    <div class="divider"></div>

    {{-- DATOS CLIENTE --}}
    <div class="info-block">
        <div><span class="info-label">Cliente:</span> {{ $cliente->nombre }}</div>
        @if ($cliente->numero_documento)
            <div><span class="info-label">RUC/DNI:</span> {{ $cliente->numero_documento }}</div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- DEBE / ABONADO / PENDIENTE --}}
    <div class="deuda-resumen">
        <div class="deuda-row">
            <div class="dl">Debe:</div>
            <div class="dv">{{ number_format($saldoTotal + $montoAbonado, 2) }}</div>
        </div>
        <div class="deuda-row">
            <div class="dl">Abonado:</div>
            <div class="dv">{{ number_format($montoAbonado, 2) }}</div>
        </div>
        <div class="deuda-row pendiente-row">
            <div class="dl">Pendiente:</div>
            <div class="dv">{{ number_format($saldoTotal, 2) }}</div>
        </div>
    </div>

    <div class="divider"></div>

    {{-- DATOS DEL PAGO --}}
    <div class="info-block">
        <div><span class="info-label">Le atendió:</span> {{ $pago->user->name ?? 'Sistema' }}</div>
        <div>
            <span class="info-label">Fecha Emisión:</span>
            {{ $pago->fecha_pago->format('Y-m-d') }}&nbsp;&nbsp;{{ $pago->fecha_pago->format('H:i') }}
        </div>
        @if ($pago->metodo_pago)
            <div><span class="info-label">Método Pago:</span> {{ $pago->metodo_pago }}</div>
        @endif
        @if ($pago->codigo_comprobante)
            <div><span class="info-label">Recibo N°:</span> {{ $pago->codigo_comprobante }}</div>
        @endif
    </div>

    @if ($saldoTotal > 0)
        <div class="divider"></div>
        <div class="info-block">
            <div class="row">
                <div class="label bold">SALDO DEUDOR:</div>
                <div class="value bold">S/ {{ number_format($saldoTotal, 2) }}</div>
            </div>
        </div>
    @endif

    @if ($esPagoAcumulado)
        <div class="divider"></div>
        <div class="center" style="font-size: 9px; margin-top: 5px;">
            <strong>Pago acumulado de varias deudas</strong>
        </div>
    @endif

    @if ($pago->observaciones)
        <div class="divider"></div>
        <div><em>{{ $pago->observaciones }}</em></div>
    @endif

    <div class="divider"></div>

    {{-- PIE --}}
    <div class="footer">
        @if ($empresa && $empresa->ticket_footer_message)
            <p style="margin-bottom: 8px;"><strong>{{ $empresa->ticket_footer_message }}</strong></p>
        @endif
        <p>No se admiten devoluciones.</p>
        <p>¡Gracias por su pago!</p>
    </div>

</body>

</html>
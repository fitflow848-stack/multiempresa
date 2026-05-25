<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo de Cobro</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; width: 72mm; padding: 4px 6px; color: #000; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .header { text-align: center; margin-bottom: 6px; }
        .header .company-name { font-weight: bold; font-size: 13px; text-transform: uppercase; }
        .header .company-sub { font-size: 10px; margin-top: 2px; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .section-title { text-align: center; font-weight: bold; font-size: 12px; padding: 3px 0; text-transform: uppercase; }
        .row { display: table; width: 100%; margin-bottom: 2px; }
        .row .label { display: table-cell; width: 55%; font-weight: bold; }
        .row .value { display: table-cell; width: 45%; text-align: right; }
        .info-block { margin-bottom: 4px; }
        .info-block .info-label { font-weight: bold; }
        .resumen { margin: 6px 0; }
        .resumen .res-row { display: table; width: 100%; padding: 1px 0; }
        .resumen .res-row .rl { display: table-cell; width: 55%; font-weight: bold; }
        .resumen .res-row .rv { display: table-cell; width: 45%; text-align: right; }
        .resumen .res-row.pendiente .rl,
        .resumen .res-row.pendiente .rv { border-top: 1px solid #000; padding-top: 3px; }
        .footer { text-align: center; font-size: 10px; margin-top: 10px; }
        .footer p { margin: 2px 0; }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <div class="header">
        @if ($logo)
            <img src="{{ $logo }}" style="max-width:110px; max-height:50px; margin-bottom:4px;">
        @endif
        <div class="company-name">{{ $empresa->razon_social ?? 'EMPRESA' }}</div>
        @if ($empresa && $empresa->descripcion)
            <div class="company-sub">{{ $empresa->descripcion }}</div>
        @endif
        @if ($empresa && $empresa->direccion)
            <div>{{ $empresa->direccion }}</div>
        @endif
        @if ($empresa && ($empresa->phone ?? $empresa->celular ?? null))
            <div>Telf.: {{ implode(' – ', array_filter([$empresa->phone ?? null, $empresa->celular ?? null])) }}</div>
        @endif
    </div>

    <div class="divider"></div>
    <div class="section-title">RECIBO DE COBRO</div>
    <div class="divider"></div>

    {{-- DATOS DEL ACTIVO --}}
    <div class="info-block">
        <div><span class="info-label">Tipo:</span> {{ $activo->tipo->nombre ?? '-' }}</div>
        <div><span class="info-label">Concepto:</span> {{ $activo->nombre }}</div>
        @if ($activo->documento)
            <div><span class="info-label">Documento:</span> {{ $activo->documento }}</div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- RESUMEN COBRO --}}
    <div class="resumen">
        <div class="res-row">
            <div class="rl">Total Activo:</div>
            <div class="rv">S/ {{ number_format($activo->monto, 2) }}</div>
        </div>
        <div class="res-row">
            <div class="rl">Cobrado hoy:</div>
            <div class="rv">S/ {{ number_format($pago->monto, 2) }}</div>
        </div>
        <div class="res-row">
            <div class="rl">Total cobrado:</div>
            <div class="rv">S/ {{ number_format($activo->monto_cobrado, 2) }}</div>
        </div>
        <div class="res-row pendiente">
            <div class="rl">Pendiente:</div>
            <div class="rv">S/ {{ number_format($activo->monto_pendiente, 2) }}</div>
        </div>
    </div>

    <div class="divider"></div>

    {{-- DATOS DEL PAGO --}}
    <div class="info-block">
        <div><span class="info-label">Recibo N°:</span> {{ $pago->codigo_comprobante }}</div>
        <div><span class="info-label">Fecha:</span> {{ $pago->fecha_pago->format('d/m/Y H:i') }}</div>
        <div><span class="info-label">Método Pago:</span> {{ $pago->metodo_pago }}</div>
        @if ($pago->referencia)
            <div><span class="info-label">Referencia:</span> {{ $pago->referencia }}</div>
        @endif
        <div><span class="info-label">Le atendió:</span> {{ $pago->user->name ?? 'Sistema' }}</div>
    </div>

    @if ($pago->observaciones)
        <div class="divider"></div>
        <div><em>{{ $pago->observaciones }}</em></div>
    @endif

    @if ($activo->monto_pendiente <= 0)
        <div class="divider"></div>
        <div class="center bold" style="font-size:12px;">*** SALDADO ***</div>
    @endif

    <div class="divider"></div>

    <div class="footer">
        @if ($empresa && ($empresa->account_number || $empresa->bank))
            <div style="margin-bottom: 10px; text-align: left; border: 1px dashed #000; padding: 4px;">
                <div style="font-weight: bold; text-align: center; margin-bottom: 2px;">CUENTAS BANCARIAS</div>
                @if($empresa->bank) <strong>Banco:</strong> {{ $empresa->bank }}<br> @endif
                @if($empresa->account_number)
                    <strong>{{ $empresa->account_type == 'corriente' ? 'Cta. Corriente' : 'Cta. Ahorros' }}:</strong><br>
                    {{ $empresa->account_number }}
                @endif
                @if($empresa->cci) <br><strong>CCI:</strong> {{ $empresa->cci }} @endif
            </div>
        @endif
        @if ($empresa && $empresa->ticket_footer_message)
            <p style="margin-bottom: 8px;"><strong>{{ $empresa->ticket_footer_message }}</strong></p>
        @endif
        <p>¡Gracias!</p>
    </div>

</body>
</html>

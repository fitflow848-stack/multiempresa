<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Comprobante de Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 5px;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .logo {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 5px;
        }

        .company-name {
            font-weight: bold;
            font-size: 14px;
        }

        .info-row {
            margin-bottom: 3px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 8px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
        }

        .details {
            width: 100%;
            margin-bottom: 10px;
        }

        .total-section {
            text-align: right;
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        @if ($logo)
            <img src="{{ $logo }}" class="logo">
        @endif
        <div class="company-name">{{ $empresa->razon_social ?? 'EMPRESA' }}</div>
        <div>RUC: {{ $empresa->ruc ?? '00000000000' }}</div>
        <div>{{ $empresa->direccion ?? '' }}</div>
    </div>

    <div class="title">COMPROBANTE DE PAGO</div>

    <div class="info-row">
        <strong>N° Comprobante:</strong> {{ $pago->codigo_comprobante }}
    </div>
    <div class="info-row">
        <strong>Fecha:</strong> {{ $pago->fecha_pago->format('d/m/Y H:i A') }}
    </div>
    <div class="info-row">
        <strong>Cliente:</strong> {{ $cliente->nombre }}
    </div>
    <div class="info-row">
        <strong>Doc. Cliente:</strong> {{ $cliente->numero_documento }}
    </div>

    <div style="margin-top: 10px; margin-bottom: 5px; font-weight: bold;">
        Detalle del Pago:
    </div>

    <table class="details" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="2">Abono a deuda:</td>
        </tr>
        <tr>
            <td style="padding-left: 10px;">{{ $deuda->numero_comprobante }}</td>
            <td style="text-align: right;">{{ number_format($pago->monto, 2) }}</td>
        </tr>
    </table>

    <div class="total-section">
        <div><strong>Total Pagado: S/ {{ number_format($pago->monto, 2) }}</strong></div>
    </div>

    <div style="margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px;">
        <div class="info-row">
            Saldo Documento Actual: <span style="float:right;">S/ {{ number_format($deuda->monto_deuda, 2) }}</span>
        </div>
        <div class="info-row"
            style="margin-top: 5px; border-top: 1px solid #eee; padding-top: 5px; font-weight: bold; font-size: 12px;">
            SALDO TOTAL PENDIENTE: <span style="float:right;">S/ {{ number_format($saldoTotal, 2) }}</span>
        </div>
    </div>

    @if ($pago->observaciones)
        <div style="margin-top: 10px; font-style: italic;">
            Obs: {{ $pago->observaciones }}
        </div>
    @endif

    <div class="footer">
        <p>Usuario: {{ $pago->user->name ?? 'Sistema' }}</p>
        <p>¡Gracias por su pago!</p>
    </div>
</body>

</html>
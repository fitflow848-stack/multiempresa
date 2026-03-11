<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            padding: 10px;
            width: 200px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .total {
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
            font-size: 13px;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 9px;
        }

        .signature {
            margin-top: 40px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <strong>{{ $company->nombre_comercial ?? 'WOLVIX' }}</strong><br>
        RUC: {{ $company->ruc ?? '-' }}<br>
        {{ $company->direccion_fiscal ?? '' }}
    </div>

    <div class="center">
        <strong>COMPROBANTE DE ADELANTO</strong><br>
        (Personal / Sueldo)<br>
        Nro: #ADL-{{ str_pad($activo->id, 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="divider"></div>

    <div>
        <strong>Fecha:</strong> {{ $activo->fecha_registro->format('d/m/Y') }}<br>
        <strong>Personal:</strong> {{ $activo->nombre }}<br>
        <strong>Concepto:</strong> {{ $activo->tipo->nombre }}<br>
        @if ($activo->documento)
            <strong>Referencia:</strong> {{ $activo->documento }}<br>
        @endif
    </div>

    <div class="divider"></div>

    <div class="total">
        MONTO ADELANTADO: S/ {{ number_format($activo->monto, 2) }}
    </div>

    @if ($activo->observaciones)
        <div style="margin-top: 5px;">
            <strong>Obs:</strong> {{ $activo->observaciones }}
        </div>
    @endif

    <div class="signature">
        <div class="signature-line"></div>
        FIRMA DEL TRABAJADOR<br>
        DNI: __________________
    </div>

    <div class="footer">
        *** Documento de Control Interno ***<br>
        Software de Gestión WOLVIX
    </div>
</body>

</html>

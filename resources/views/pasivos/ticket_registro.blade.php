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
        <strong>COMPROBANTE DE RECONOCIMIENTO</strong><br>
        (Adelanto / Pasivo)<br>
        Nro: #REG-{{ str_pad($pasivo->id, 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="divider"></div>

    <div>
        <strong>Fecha:</strong> {{ $pasivo->created_at->format('d/m/Y H:i') }}<br>
        <strong>Persona:</strong> {{ $pasivo->empresa_persona }}<br>
        <strong>Concepto:</strong> {{ $pasivo->nombre }}<br>
        <strong>Tipo:</strong> {{ $pasivo->tipo->nombre }}<br>
        @if ($pasivo->documento)
            <strong>Referencia:</strong> {{ $pasivo->documento }}<br>
        @endif
        @if ($pasivo->metodo_pago)
            <strong>Método:</strong> {{ $pasivo->metodo_pago }}<br>
        @endif
        <strong>Registrado por:</strong> {{ $pasivo->user->name ?? 'Sistema' }}<br>
    </div>

    <div class="divider"></div>

    <div class="total" style="text-align: left; font-size: 11px;">
        MONTO TOTAL: S/ {{ number_format($pasivo->monto, 2) }}<br>
        MONTO PAGADO: S/ {{ number_format($pasivo->monto_pagado ?? 0, 2) }}<br>
        <div class="divider"></div>
        SALDO PENDIENTE: S/ {{ number_format($pasivo->saldo, 2) }}
    </div>

    @if($company->bank)
    <div class="divider"></div>
    <div style="font-size: 10px;">
        <strong>DEPÓSITOS A:</strong><br>
        {{ $company->bank }} ({{ $company->account_type == 'corriente' ? 'Cta. Corr.' : 'Cta. Aho.' }})<br>
        Cta: {{ $company->account_number }}
        @if($company->cci)
            <br>CCI: {{ $company->cci }}
        @endif
    </div>
    @endif

    @if ($pasivo->observaciones)
        <div style="margin-top: 5px;">
            <strong>Obs:</strong> {{ $pasivo->observaciones }}
        </div>
    @endif

    <div class="signature">
        <div class="signature-line"></div>
        FIRMA DEL TRABAJADOR / CLIENTE<br>
        DNI: __________________
    </div>

    <div class="footer">
        *** Documento de Control Interno ***<br>
        Software de Gestión WOLVIX
    </div>
</body>

</html>

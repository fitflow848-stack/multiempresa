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
            /* Ancho para ticket térmico */
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
            margin-top: 15px;
            text-align: center;
            font-size: 9px;
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
        <strong>RECIBO DE PAGO (PASIVO)</strong><br>
        Nro: #PAG-{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="divider"></div>

    <div>
        <strong>Fecha:</strong> {{ $pago->created_at->format('d/m/Y H:i') }}<br>
        <strong>Concepto:</strong> {{ $pago->pasivo->nombre }}<br>
        <strong>Tipo:</strong> {{ $pago->pasivo->tipo->nombre }}<br>
        <strong>Metodo:</strong> {{ $pago->metodo_pago }}<br>
        @if($pago->documento_pago)
            <strong>Ref:</strong> {{ $pago->documento_pago }}<br>
        @endif
        <strong>Usuario:</strong> {{ $pago->user->name }}
    </div>

    <div class="divider"></div>

    <div class="total">
        MONTO PAGADO: S/ {{ number_format($pago->monto, 2) }}
    </div>

    <div class="divider"></div>

    <div>
        <strong>Saldo Pendiente:</strong> S/ {{ number_format($pago->pasivo->saldo, 2) }}
    </div>

    @if($company->account_number || $company->bank)
    <div class="divider"></div>
    <div style="font-size: 10px;">
        <strong>DEPÓSITOS:</strong><br>
        @if($company->bank) {{ $company->bank }} @endif
        @if($company->account_type) ({{ $company->account_type == 'corriente' ? 'Cta. Corr.' : 'Cta. Aho.' }}) @endif
        @if($company->account_number) <br>Cta: {{ $company->account_number }} @endif
        @if($company->cci)
            <br>CCI: {{ $company->cci }}
        @endif
    </div>
    @endif

    <div class="footer">
        *** Gracias por su preferencia ***<br>
        Software de Gestión WOLVIX
    </div>
</body>

</html>
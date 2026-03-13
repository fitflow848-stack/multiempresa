<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Compra #{{ $compra->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .header td {
            vertical-align: top;
        }
        .logo {
            width: 150px;
        }
        .company-info {
            text-align: center;
        }
        .document-info {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
        }
        .section-title {
            background-color: #f2f2f2;
            padding: 5px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-table {
            width: 250px;
            margin-left: auto;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .totals-table .total-row {
            font-weight: bold;
            font-size: 13px;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 30%;">
                @if($logoPath)
                    <img src="{{ $logoPath }}" class="logo">
                @endif
            </td>
            <td style="width: 40%;" class="company-info">
                <strong>{{ $company->nombre_comercial ?? $company->razon_social }}</strong><br>
                {{ $company->direccion_fiscal }}<br>
                RUC: {{ $company->ruc }}<br>
                Email: {{ $company->email }}
            </td>
            <td style="width: 30%;">
                <div class="document-info">
                    <div style="font-size: 14px; font-weight: bold;">ORDEN DE COMPRA</div>
                    <div style="font-size: 16px; margin-top: 5px;">#{{ $compra->id }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">DATOS DEL PROVEEDOR</div>
    <table class="info-table">
        <tr>
            <td style="width: 15%;"><strong>Proveedor:</strong></td>
            <td style="width: 35%;">{{ $compra->proveedor->razon_social ?? $compra->proveedor_nombre ?? 'N/A' }}</td>
            <td style="width: 15%;"><strong>Fecha:</strong></td>
            <td style="width: 35%;">{{ $compra->fecha_emision ? \Carbon\Carbon::parse($compra->fecha_emision)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>RUC / DNI:</strong></td>
            <td>{{ $compra->proveedor->numero_documento ?? '-' }}</td>
            <td><strong>Moneda:</strong></td>
            <td>{{ strtoupper($compra->moneda ?? 'SOL') }}</td>
        </tr>
        <tr>
            <td><strong>Dirección:</strong></td>
            <td>{{ $compra->proveedor->direccion ?? '-' }}</td>
            <td><strong>Condición:</strong></td>
            <td>{{ $compra->credito ? 'CRÉDITO' : 'CONTADO' }}</td>
        </tr>
    </table>

    <div class="section-title">DETALLE DE LA COMPRA</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 55%;">Descripción</th>
                <th style="width: 10%;">Cant.</th>
                <th style="width: 15%;">Costo Unit.</th>
                <th style="width: 15%;">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compra->lineas as $i => $linea)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $linea->descripcion }}</td>
                    <td class="text-center">{{ $linea->cantidad }}</td>
                    <td class="text-right">{{ number_format($linea->costo, 2) }}</td>
                    <td class="text-right">{{ number_format($linea->costo * $linea->cantidad, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">S/ {{ number_format($compra->total_bruto, 2) }}</td>
        </tr>
        <tr>
            <td>Descuento:</td>
            <td class="text-right">S/ {{ number_format($compra->total_descuento, 2) }}</td>
        </tr>
        <tr>
            <td>Impuesto ({{ $compra->inc_impuesto ? 'Incl.' : 'Excl.' }}):</td>
            <td class="text-right">S/ {{ number_format($compra->total_impuesto, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td><strong>TOTAL A PAGAR:</strong></td>
            <td class="text-right"><strong>S/ {{ number_format($compra->total_pagar, 2) }}</strong></td>
        </tr>
    </table>

    <div class="footer">
        Generado el {{ date('d/m/Y H:i') }} por {{ $compra->usuario->name ?? 'Sistema' }}
    </div>
</body>
</html>

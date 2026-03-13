<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transferencia {{ $codigo }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            color: #2c3e50;
        }
        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-box {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .signatures {
            margin-top: 80px;
            width: 100%;
        }
        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
            border-top: 1px solid #333;
            margin: 0 2%;
            padding-top: 5px;
        }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->nombre_comercial ?? 'GENACK' }}</div>
        <div>RUC: {{ $company->ruc ?? '-' }}</div>
        <div class="doc-title">GUÍA DE TRANSFERENCIA INTERNA - #{{ $codigo }}</div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <div class="fw-bold">ORIGEN:</div>
            <div>Local: {{ $sucursalOrigen->nombre ?? 'N/A' }}</div>
            <div>Fecha: {{ $fecha->format('d/m/Y H:i') }}</div>
            <div>Emitido por: {{ Auth::user()->name }}</div>
        </div>
        <div class="info-box" style="margin-left: 15px;">
            <div class="fw-bold">DESTINO:</div>
            <div>Local: {{ $sucursalDestino->nombre ?? 'N/A' }}</div>
            <div>Referencia: {{ $codigo }}</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="8%">Cant.</th>
                <th>Descripción del Producto</th>
                <th width="15%">Lote</th>
                <th width="15%">Vence</th>
                <th width="15%">Código/CB</th>
            </tr>
        </thead>
        <tbody>
            @php $totalItems = 0; @endphp
            @foreach($transferencias as $t)
                <tr>
                    <td>{{ number_format($t->cantidad, 2) }}</td>
                    <td>{{ $t->producto->nombre }}</td>
                    <td>{{ $t->origenLote->lote ?? 'S/L' }}</td>
                    <td>{{ $t->origenLote->fecha_vencimiento ? $t->origenLote->fecha_vencimiento->format('d/m/Y') : '-' }}</td>
                    <td>{{ $t->producto->codigo_barras ?? '-' }}</td>
                </tr>
                @php $totalItems += $t->cantidad; @endphp
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <strong>Observaciones:</strong><br>
        {{ $transferencias->first()->observaciones ?? 'Sin observaciones' }}
    </div>

    <div class="signatures">
        <div class="signature-box">
            ENTREGADO POR (ORIGEN)<br>
            DNI: ________________
        </div>
        <div class="signature-box">
            RECIBIDO POR (DESTINO)<br>
            DNI: ________________
        </div>
    </div>

    <div class="footer">
        <small>Documento generado digitalmente para control interno.</small>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pase de Bóveda</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            background: #fff;
            color: #000;
        }
        .ticket {
            width: 80mm;
            margin: 0 auto;
            padding: 5px;
        }
        .header {
            text-align: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header h2 {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .header .tipo-badge {
            background: #000;
            color: #fff;
            padding: 3px 12px;
            font-size: 11px;
            font-weight: 900;
            display: inline-block;
            margin-top: 5px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        .label { color: #000; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .value { font-weight: 900; text-align: right; max-width: 60%; font-size: 13px; }
        .divider {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }
        .importe-box {
            border: 2px solid #000;
            text-align: center;
            padding: 10px;
            margin: 10px 0;
        }
        .importe-box .importe-label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #000; }
        .importe-box .importe-valor { font-size: 24px; font-weight: 900; }
        .firma-section {
            margin-top: 15px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        .firma-line {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 20px;
        }
        .firma-box {
            flex: 1;
            text-align: center;
        }
        .firma-box .linea {
            border-top: 1.5px solid #000;
            margin-top: 25px;
            padding-top: 5px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            font-weight: 700;
            border-top: 1.5px dashed #000;
            padding-top: 8px;
            color: #000;
        }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .ticket { border: none; margin: 0; padding: 5px; }
            @page { margin: 0; size: 80mm auto; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h2>🏦 PASE DE BÓVEDA</h2>
            <div class="tipo-badge">{{ strtoupper($tipo ?? 'TRANSFERENCIA') }}</div>
        </div>

        <div class="row">
            <span class="label">Fecha:</span>
            <span class="value">{{ $fecha ?? now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="row">
            <span class="label">Realizado por:</span>
            <span class="value">{{ $usuario ?? '---' }}</span>
        </div>

        <div class="divider"></div>

        <div class="row">
            <span class="label">Origen:</span>
            <span class="value">{{ $origen ?? '---' }}</span>
        </div>
        <div class="row">
            <span class="label">Destino:</span>
            <span class="value">{{ $destino ?? '---' }}</span>
        </div>

        <div class="divider"></div>

        <div class="importe-box">
            <div class="importe-label">Importe Transferido</div>
            <div class="importe-valor">S/ {{ number_format(floatval($importe ?? 0), 2) }}</div>
        </div>

        <div class="row">
            <span class="label">Concepto:</span>
            <span class="value">{{ $concepto ?? '---' }}</span>
        </div>

        @if(isset($id_origen) || isset($id_destino))
        <div class="divider"></div>
        <div class="row">
            <span class="label">Ref. sesión origen:</span>
            <span class="value">#{{ $id_origen ?? '---' }}</span>
        </div>
        <div class="row">
            <span class="label">Ref. sesión destino:</span>
            <span class="value">#{{ $id_destino ?? '---' }}</span>
        </div>
        @endif

        <div class="firma-section">
            <div style="font-size:10px; text-align:center; color:#555; margin-bottom:4px;">FIRMAS DE CONFORMIDAD</div>
            <div class="firma-line">
                <div class="firma-box">
                    <div class="linea">Entrega</div>
                </div>
                <div class="firma-box">
                    <div class="linea">Recibe / Autoriza</div>
                </div>
            </div>
        </div>

        <div class="footer">
            Documento generado el {{ now()->format('d/m/Y H:i:s') }}<br>
            Este comprobante no tiene valor tributario
        </div>
    </div>

    <div class="no-print" style="text-align:center; margin-top:16px;">
        <button onclick="window.print()" style="padding:8px 24px; background:#1a1a2e; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:14px;">
            🖨️ Imprimir
        </button>
        <button onclick="window.close()" style="padding:8px 16px; background:#eee; color:#333; border:none; border-radius:4px; cursor:pointer; font-size:14px; margin-left:8px;">
            Cerrar
        </button>
    </div>

    <script>
        // Auto print al abrir
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 400);
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pase de Bóveda</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #fff;
            color: #000;
        }
        .ticket {
            width: 80mm;
            margin: 0 auto;
            padding: 10px 8px;
            border: 1px dashed #aaa;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .header h2 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .tipo-badge {
            background: #000;
            color: #fff;
            padding: 2px 10px;
            border-radius: 3px;
            font-size: 11px;
            display: inline-block;
            margin-top: 4px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            line-height: 1.4;
        }
        .label { color: #555; font-size: 10px; text-transform: uppercase; }
        .value { font-weight: bold; text-align: right; max-width: 55%; word-break: break-word; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .importe-box {
            background: #f0f0f0;
            border: 2px solid #000;
            text-align: center;
            padding: 8px;
            margin: 8px 0;
            border-radius: 4px;
        }
        .importe-box .importe-label { font-size: 10px; color: #555; text-transform: uppercase; }
        .importe-box .importe-valor { font-size: 22px; font-weight: 900; letter-spacing: 1px; }
        .firma-section {
            margin-top: 12px;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }
        .firma-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
        }
        .firma-box {
            flex: 1;
            text-align: center;
        }
        .firma-box .linea {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 3px;
            font-size: 9px;
            text-transform: uppercase;
            color: #555;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
            color: #888;
            border-top: 1px dashed #aaa;
            padding-top: 6px;
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

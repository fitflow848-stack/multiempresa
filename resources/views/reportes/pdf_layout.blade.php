<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $report_title ?? 'Reporte' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18pt;
            color: #1a73e8;
        }

        .info {
            font-size: 9pt;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
        }

        td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 8.5pt;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 8pt;
            text-align: center;
            color: #777;
        }

        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7.5pt;
        }

        .bg-success {
            background-color: #d4edda;
            color: #155724;
        }

        .bg-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .bg-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .bg-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $report_title }}</h1>
        <div class="info">
            Fecha de Generación: {{ date('d/m/Y H:i') }}<br>
            Generado por: {{ Auth::user()->name }}
        </div>
    </div>

    @include($view)

    <div class="footer">
        Página <script type="text/php">echo $PAGE_NUM . " de " . $PAGE_COUNT;</script> - Wolvix Report System
        | Fecha de Impresión: {{ date('d/m/Y H:i:s') }}
    </div>
</body>

</html>
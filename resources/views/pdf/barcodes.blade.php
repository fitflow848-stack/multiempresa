<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Etiquetas de Código de Barras</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
        }
        .label-box {
            width: 31%;
            height: 3.2cm;
            display: inline-block;
            border: 1px dashed #bbb;
            margin: 1%;
            padding: 6px 4px 4px;
            text-align: center;
            vertical-align: top;
            overflow: hidden;
            box-sizing: border-box;
        }
        .product-name {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 4px;
            line-height: 1.2;
            overflow: hidden;
            max-height: 22pt;
        }
        .barcode-img {
            max-width: 100%;
            height: 1.4cm;
            display: block;
            margin: 0 auto;
        }
        .barcode-text {
            font-size: 7pt;
            margin-top: 2px;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="container">
        @foreach($items as $item)
            @for($i = 0; $i < $item['qty']; $i++)
                <div class="label-box">
                    <div class="product-name">{{ $item['name'] }}</div>
                    @if($item['barcode_base64'])
                        <img class="barcode-img" src="data:image/png;base64,{{ $item['barcode_base64'] }}" alt="barcode">
                    @else
                        <div style="border:1px dashed #ccc;padding:6px;font-size:7pt;color:#999;">Sin código</div>
                    @endif
                    <div class="barcode-text">{{ $item['code'] }}</div>
                </div>
            @endfor
        @endforeach
    </div>
</body>
</html>

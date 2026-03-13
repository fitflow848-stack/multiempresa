<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductosPlantillaExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([
            [
                '7751234567890', // CODIGO_BARRAS
                'PRODUCTO DE EJEMPLO', // NOMBRE
                'MARCA EJEMPLO', // MARCA
                'FAMILIA EJEMPLO', // FAMILIA
                'LABORATORIO EJEMPLO', // LABORATORIO
                'NIU', // UNIDAD_MEDIDA
                'gravado', // TIPO_IMPUESTO
                'CAJA X 100', // PRESENTACION
                '500MG', // CONCENTRACION
                '2026-12-31', // FECHA_VENCIMIENTO
                '10.00', // PRECIO_COMPRA
                '15.00', // PVP (Precio Venta Público)
                '14.50', // PVPD (Precio Venta Público con Descuento)
                '14.00', // PVC (Precio Venta Corporativo)
                '13.50', // PVCD (Precio Venta Corporativo con Descuento)
                '100', // STOCK_INICIAL
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'CODIGO_BARRAS',
            'NOMBRE',
            'MARCA',
            'FAMILIA',
            'LABORATORIO',
            'UNIDAD_MEDIDA',
            'TIPO_IMPUESTO',
            'PRESENTACION',
            'CONCENTRACION',
            'FECHA_VENCIMIENTO',
            'PRECIO_COMPRA',
            'PVP',
            'PVPD',
            'PVC',
            'PVCD',
            'STOCK_INICIAL',
        ];
    }
}

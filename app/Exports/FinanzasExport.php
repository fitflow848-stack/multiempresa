<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanzasExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    protected $operaciones;
    protected $titulo;

    public function __construct($operaciones, $titulo)
    {
        $this->operaciones = $operaciones;
        $this->titulo = $titulo;
    }

    public function view(): View
    {
        return view('finanzas_vendedor.export_excel', [
            'operaciones' => $this->operaciones,
            'titulo' => $this->titulo
        ]);
    }

    public function title(): string
    {
        return $this->titulo;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

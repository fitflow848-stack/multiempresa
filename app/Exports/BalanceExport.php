<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class BalanceExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $data;
    protected $fecha;

    public function __construct($data, $fecha)
    {
        $this->data = $data;
        $this->fecha = $fecha;
    }

    public function view(): View
    {
        return view('balance.excel', array_merge($this->data, ['fecha' => $this->fecha]));
    }

    public function title(): string
    {
        return 'Balance General ' . $this->fecha;
    }
}

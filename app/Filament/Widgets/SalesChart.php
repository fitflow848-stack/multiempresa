<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Ventas de los últimos 7 días';

    protected static ?int $sort = 2; // Debajo de las estadísticas

    protected function getData(): array
    {
        $data = Venta::select(
            DB::raw('DATE(fecha_emision) as date'),
            DB::raw('SUM(total) as total')
        )
            ->where('fecha_emision', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Ventas (S/)',
                    'data' => $data->map(fn($v) => (float) $v->total),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $data->map(fn($v) => Carbon::parse($v->date)->format('d M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

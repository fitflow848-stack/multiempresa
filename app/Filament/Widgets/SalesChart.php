<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Ventas de los últimos 30 días';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $user = auth()->guard('admin')->user() ?? auth()->guard('web')->user();
        $isSuperAdmin = $user?->hasRole('super_admin');
        $companyId = $user?->company_id;

        $query = Venta::select(
            DB::raw('DATE(fecha_emision) as date'),
            DB::raw('SUM(total) as total')
        )
            ->where('fecha_emision', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date');

        // Filtrar por empresa si no es super_admin
        if (!$isSuperAdmin && $companyId) {
            $query->where('id_empresa', $companyId);
        }

        $data = $query->get();

        // Rellenar días sin ventas con 0
        $days = collect(range(29, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $salesMap = $data->pluck('total', 'date');

        return [
            'datasets' => [
                [
                    'label' => 'Ventas (S/)',
                    'data' => $days->map(fn($d) => (float) ($salesMap[$d] ?? 0))->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => '#3b82f6',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $days->map(fn($d) => Carbon::parse($d)->format('d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

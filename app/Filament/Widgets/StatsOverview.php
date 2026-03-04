<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\CierreCaja;
use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->guard('admin')->user() ?? auth()->guard('web')->user();
        $isSuperAdmin = $user?->hasRole('super_admin');
        $companyId = $user?->company_id;

        // Base queries filtradas por empresa si no es super_admin
        $ventasQuery    = Venta::query();
        $clientesQuery  = Cliente::query();
        $cajasQuery     = CierreCaja::query();

        if (!$isSuperAdmin && $companyId) {
            $ventasQuery->where('id_empresa', $companyId);
            $clientesQuery->where('company_id', $companyId);
            // cajas abiertas dentro de la empresa
            $cajasQuery->where('id_empresa', $companyId);
        }

        // Ventas totales
        $totalVentas = $ventasQuery->sum('total');

        // Ventas de hoy
        $ventasHoy = (clone $ventasQuery)->whereDate('fecha_emision', now())->sum('total');

        // Ventas del mes actual
        $ventasMes = (clone $ventasQuery)
            ->whereYear('fecha_emision', now()->year)
            ->whereMonth('fecha_emision', now()->month)
            ->sum('total');

        // Variación vs mes anterior
        $ventasMesAnterior = (clone $ventasQuery)
            ->whereYear('fecha_emision', now()->subMonth()->year)
            ->whereMonth('fecha_emision', now()->subMonth()->month)
            ->sum('total');
        $variacion = $ventasMesAnterior > 0
            ? round((($ventasMes - $ventasMesAnterior) / $ventasMesAnterior) * 100, 1)
            : 0;
        $variacionTexto = ($variacion >= 0 ? '+' : '') . $variacion . '% vs mes anterior';
        $variacionColor = $variacion >= 0 ? 'success' : 'danger';

        // Clientes
        $totalClientes = $clientesQuery->count();
        $clientesNuevosHoy = (clone $clientesQuery)->whereDate('created_at', now())->count();

        // Cajas abiertas ahora
        $cajasAbiertas = (clone $cajasQuery)->whereNull('fecha_cierre')->count();

        // Sucursales / Empresas
        if ($isSuperAdmin) {
            $labelSucursales = 'Total Empresas';
            $countSucursales = Company::count();
            $descSucursales  = Sucursal::count() . ' sucursales activas';
        } else {
            $labelSucursales = 'Mis Sucursales';
            $countSucursales = Sucursal::where('company_id', $companyId)->count();
            $descSucursales  = 'Sucursales de tu empresa';
        }

        // Gráfico ventas últimos 7 días
        $chartData = collect(range(6, 0))->map(function ($daysAgo) use ($companyId, $isSuperAdmin) {
            $q = Venta::query();
            if (!$isSuperAdmin && $companyId) $q->where('id_empresa', $companyId);
            return (float) $q->whereDate('fecha_emision', now()->subDays($daysAgo))->sum('total');
        })->toArray();

        return [
            Stat::make('Ventas de Hoy', 'S/ ' . number_format($ventasHoy, 2))
                ->description('Actualizadas en tiempo real')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($chartData),

            Stat::make('Ventas del Mes', 'S/ ' . number_format($ventasMes, 2))
                ->description($variacionTexto)
                ->descriptionIcon($variacion >= 0 ? 'heroicon-m-arrow-up' : 'heroicon-m-arrow-down')
                ->color($variacionColor),

            Stat::make('Clientes Registrados', number_format($totalClientes))
                ->description($clientesNuevosHoy > 0 ? "+{$clientesNuevosHoy} nuevos hoy" : 'Sin nuevos clientes hoy')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Cajas Abiertas Ahora', $cajasAbiertas)
                ->description($cajasAbiertas > 0 ? 'Turnos activos en este momento' : 'Ninguna caja en operación')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($cajasAbiertas > 0 ? 'info' : 'gray'),

            Stat::make($labelSucursales, $countSucursales)
                ->description($descSucursales)
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Ventas Históricas', 'S/ ' . number_format($totalVentas, 2))
                ->description('Total acumulado del sistema')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('gray'),
        ];
    }
}

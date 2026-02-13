<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Sucursal;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        // Ventas Totales
        $totalVentas = Venta::sum('total');
        $ventasHoy = Venta::whereDate('fecha_emision', now())->sum('total');

        // Clientes
        $totalClientes = Cliente::count();

        // Sucursales / Empresas
        $labelSucursales = $isSuperAdmin ? 'Total Empresas' : 'Tus Sucursales';
        $countSucursales = $isSuperAdmin ? \App\Models\Company::count() : Sucursal::count();

        return [
            Stat::make('Ventas Totales', 'S/ ' . number_format($totalVentas, 2))
                ->description('Ventas históricas en el sistema')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Ventas de Hoy', 'S/ ' . number_format($ventasHoy, 2))
                ->description('Ventas registradas hoy')
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('info'),

            Stat::make('Total Clientes', $totalClientes)
                ->description('Clientes registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make($labelSucursales, $countSucursales)
                ->description('Infraestructura activa')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),
        ];
    }
}

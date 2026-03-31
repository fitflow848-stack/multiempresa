<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\CierreCaja;
use Illuminate\Database\Eloquent\Builder;

class CajasActivasWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '🟢 Cajas Abiertas Ahora';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('caja.nombre')
                    ->label('Caja')
                    ->weight('bold')
                    ->icon('heroicon-o-calculator'),

                TextColumn::make('caja.sucursal.nombre')
                    ->label('Sucursal')
                    ->badge()
                    ->color('info'),

                TextColumn::make('user.name')
                    ->label('Cajero en Turno')
                    ->icon('heroicon-o-user'),

                TextColumn::make('monto_apertura')
                    ->label('Apertura')
                    ->money('PEN')
                    ->alignRight(),

                TextColumn::make('created_at')
                    ->label('Abierta Hace')
                    ->since()
                    ->color('warning'),

                TextColumn::make('created_at')
                    ->label('Hora Inicio')
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
            ])
            ->poll('20s')
            ->emptyStateHeading('No hay cajas abiertas')
            ->emptyStateDescription('Ningún cajero tiene un turno activo en este momento.')
            ->emptyStateIcon('heroicon-o-calculator')
            ->striped();
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->guard('admin')->user() ?? auth()->guard('web')->user();
        $isSuperAdmin = $user?->hasRole('super_admin');
        $companyId = $user?->company_id;

        $query = CierreCaja::query()
            ->whereNull('fecha_cierre')
            ->with(['caja.sucursal', 'user']);

        if (!$isSuperAdmin && $companyId) {
            $query->where('id_empresa', $companyId);
        }

        $branchId = $user?->branch_id;
        if ($branchId) {
            $query->where('sucursal_id', $branchId);
        }

        return $query;
    }
}

<?php

namespace App\Filament\Resources\Sucursales\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class SucursalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn($record) => $record->company?->logo_url)
                    ->size(40),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn($record) => $record->direccion),
                TextColumn::make('company.razon_social')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->color('gray')
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: fn() => !auth()->guard('admin')->user()?->hasRole('super_admin')),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-o-phone')
                    ->toggleable(),
                TextColumn::make('cajas_count')
                    ->label('Cajas Registradas')
                    ->counts('cajas')
                    ->badge()
                    ->color(fn($state, $record) => $state >= ($record->numero_cajas ?? 1) ? 'success' : 'warning')
                    ->suffix(fn($state, $record) => " / " . ($record->numero_cajas ?? '1'))
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->label('Creada')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'razon_social')
                    ->hidden(fn() => !auth()->guard('admin')->user()?->hasRole('super_admin')),
                Filter::make('is_active')
                    ->label('Solo sucursales activas')
                    ->query(fn($query) => $query->where('is_active', true)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Sucursales')
                        ->modalDescription('Si eliminas una sucursal, se perderán las cajas y datos asociados. ¿Estás seguro?')
                        ->modalSubmitActionLabel('Sí, eliminar permanentemente'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

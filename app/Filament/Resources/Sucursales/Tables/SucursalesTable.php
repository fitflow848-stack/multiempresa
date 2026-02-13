<?php

namespace App\Filament\Resources\Sucursales\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn($record) => $record->company?->logo_url)
                    ->size(40),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('company.razon_social')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->direccion),
                TextColumn::make('telefono')
                    ->label('Teléfono'),
                TextColumn::make('numero_cajas')
                    ->label('N° Cajas')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                TextColumn::make('cajas_count')
                    ->label('Cajas Creadas')
                    ->counts('cajas')
                    ->badge()
                    ->color(fn($state, $record) => $state < $record->numero_cajas ? 'warning' : 'success')
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->label('Creada')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'razon_social'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

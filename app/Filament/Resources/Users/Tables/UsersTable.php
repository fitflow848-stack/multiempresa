<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('company.razon_social')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin asignar'),

                TextColumn::make('branches.nombre')
                    ->label('Sucursales')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->separator(', ')
                    ->placeholder('Sin sucursal'),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin_empresa' => 'warning',
                        'admin' => 'danger',
                        'supervisor' => 'warning',
                        'vendedor' => 'success',
                        'cajero' => 'info',
                        default => 'gray',
                    })
                    ->placeholder('Sin roles'),

                TextColumn::make('cajas.nombre')
                    ->label('Cajas')
                    ->badge()
                    ->color('primary')
                    ->separator(', ')
                    ->placeholder('Sin cajas')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->placeholder('No especificado')
                    ->icon('heroicon-o-phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Company filter
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->options(function () {
                        return \App\Models\Company::all()
                            ->mapWithKeys(fn($c) => [$c->id => $c->razon_social ?? 'Sin asignar'])
                            ->toArray();
                    }),

                // Branch filter
                SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->options(function () {
                        return \App\Models\Sucursal::all()
                            ->mapWithKeys(fn($s) => [$s->id => $s->nombre ?? 'Sin nombre'])
                            ->toArray();
                    }),

                // Roles filter
                SelectFilter::make('roles')
                    ->label('Rol')
                    ->options(function () {
                        return \Spatie\Permission\Models\Role::all()
                            ->mapWithKeys(fn($r) => [$r->id => $r->name ?? 'Sin nombre'])
                            ->toArray();
                    })
                    ->multiple(),

                Filter::make('is_active')
                    ->label('Solo usuarios activos')
                    ->query(fn($query) => $query->where('is_active', true)),

                Filter::make('has_company')
                    ->label('Con empresa asignada')
                    ->query(fn($query) => $query->whereNotNull('company_id')),

                Filter::make('has_cajas')
                    ->label('Con cajas asignadas')
                    ->query(fn($query) => $query->has('cajas')),
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
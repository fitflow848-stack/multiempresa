<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre del Rol')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'administrador' => 'danger',
                        'super_admin'   => 'danger',
                        'supervisor'    => 'warning',
                        'vendedor'      => 'success',
                        'cajero'        => 'info',
                        default         => 'gray',
                    }),

                TextColumn::make('company_name')
                    ->label('Empresa')
                    ->getStateUsing(function ($record) {
                        if (!$record->company_id) {
                            return 'Global (Plantilla)';
                        }
                        $company = \App\Models\Company::find($record->company_id);
                        return $company?->razon_social ?? "Empresa #{$record->company_id}";
                    })
                    ->badge()
                    ->color(fn ($state) => str_contains($state, 'Plantilla') ? 'gray' : 'primary')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('company', fn ($q) => $q->where('razon_social', 'like', "%{$search}%"))
                              ->orWhereNull('company_id');
                    })
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('company_id', $direction))
                    ->visible(fn () => auth('admin')->user()?->isSuperAdmin()),

                TextColumn::make('permissions_count')
                    ->label('Permisos')
                    ->counts('permissions')
                    ->sortable(),

                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->options(function () {
                        $options = ['' => 'Global (Plantilla)'];
                        \App\Models\Company::all()->each(function ($c) use (&$options) {
                            $options[$c->id] = $c->razon_social;
                        });
                        return $options;
                    })
                    ->visible(fn () => auth('admin')->user()?->isSuperAdmin()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}

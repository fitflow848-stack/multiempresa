<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->email)
                    ->icon('heroicon-o-user-circle')
                    ->weight('bold'),

                TextColumn::make('company.razon_social')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('Sin asignar'),

                TextColumn::make('branches.nombre')
                    ->label('Sucursales')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->separator(', ')
                    ->placeholder('Sin sucursal'),

                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->getStateUsing(function ($record) {
                        // Bypasear el team-scope de Spatie para mostrar los roles correctamente
                        return \Illuminate\Support\Facades\DB::table('model_has_roles')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->where('model_has_roles.model_id', $record->id)
                            ->where('roles.guard_name', 'admin')
                            ->where(function ($q) use ($record) {
                                $q->where('model_has_roles.company_id', $record->getRawOriginal('company_id'))
                                  ->orWhereNull('model_has_roles.company_id');
                            })
                            ->pluck('roles.name')
                            ->unique()
                            ->values()
                            ->toArray();
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'super_admin'   => 'danger',
                        'admin_empresa' => 'warning',
                        'admin'         => 'danger',
                        'administrador' => 'danger',
                        'supervisor'    => 'warning',
                        'vendedor'      => 'success',
                        'cajero'        => 'info',
                        default         => 'gray',
                    })
                    ->separator(', ')
                    ->placeholder('Sin roles'),

                TextColumn::make('cajas.nombre')
                    ->label('Cajas')
                    ->badge()
                    ->color('primary')
                    ->separator(', ')
                    ->placeholder('Sin cajas')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->placeholder('—')
                    ->icon('heroicon-o-phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Activo')
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
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->options(fn() => \App\Models\Company::all()
                        ->mapWithKeys(fn($c) => [$c->id => $c->razon_social ?? 'Sin asignar'])
                        ->toArray()),

                SelectFilter::make('roles')
                    ->label('Rol')
                    ->options(fn() => \Spatie\Permission\Models\Role::all()
                        ->mapWithKeys(fn($r) => [$r->id => $r->name])
                        ->toArray())
                    ->multiple(),

                Filter::make('is_active')
                    ->label('Solo usuarios activos')
                    ->query(fn($query) => $query->where('is_active', true)),

                Filter::make('sin_cajas')
                    ->label('Sin cajas asignadas')
                    ->query(fn($query) => $query->doesntHave('cajas')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                // Toggle Activar / Desactivar rápido
                Action::make('toggleActive')
                    ->label(fn($record) => $record->is_active ? 'Desactivar' : 'Activar')
                    ->icon(fn($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn($record) => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => $record->is_active ? 'Desactivar Usuario' : 'Activar Usuario')
                    ->modalDescription(fn($record) => $record->is_active
                        ? "¿Confirmas que deseas desactivar a {$record->name}? No podrá iniciar sesión."
                        : "¿Confirmas que deseas activar a {$record->name}?")
                    ->modalSubmitActionLabel('Sí, confirmar')
                    ->action(function ($record) {
                        $nuevoEstado = !$record->is_active;
                        $record->update(['is_active' => $nuevoEstado]);
                        Notification::make()
                            ->title($nuevoEstado ? '✅ Usuario activado' : '🚫 Usuario desactivado')
                            ->success()
                            ->send();
                    }),

                // Resetear contraseña
                Action::make('resetPassword')
                    ->label('Resetear Contraseña')
                    ->icon('heroicon-o-key')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Resetear Contraseña')
                    ->modalDescription(fn($record) => "Se generará una contraseña temporal para {$record->name}. Anótala antes de cerrar.")
                    ->modalSubmitActionLabel('Sí, resetear')
                    ->action(function ($record) {
                        $newPassword = Str::random(10);
                        $record->update(['password' => Hash::make($newPassword)]);
                        Notification::make()
                            ->title('🔑 Contraseña reseteada')
                            ->body("Nueva contraseña temporal: {$newPassword}")
                            ->warning()
                            ->persistent()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Usuarios Seleccionados')
                        ->modalDescription('Esta acción es irreversible. Se eliminarán permanentemente los usuarios seleccionados y todos sus datos asociados.')
                        ->modalSubmitActionLabel('Sí, eliminar permanentemente'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

<?php

namespace App\Filament\Resources\Cajas\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use App\Models\CierreCaja;

class CajasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-calculator')
                    ->description(fn($record) => $record->descripcion ? substr($record->descripcion, 0, 50) : null),

                TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('sucursal.company.razon_social')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin empresa')
                    ->toggleable(isToggledHiddenByDefault: false),

                // Estado en tiempo real: quién tiene la caja abierta ahora
                TextColumn::make('id')
                    ->label('Estado Actual')
                    ->formatStateUsing(function ($state) {
                        $cierre = CierreCaja::where('caja_id', $state)
                            ->whereNull('fecha_cierre')
                            ->with('user')
                            ->first();

                        if ($cierre) {
                            $nombre = $cierre->user?->name ?? 'Usuario desconocido';
                            $inicio = $cierre->created_at?->format('H:i') ?? '—';
                            return "🟢 En uso por {$nombre} desde {$inicio}";
                        }

                        return '⚪ Disponible';
                    })
                    ->badge()
                    ->color(function ($state) {
                        $abierta = CierreCaja::where('caja_id', $state)
                            ->whereNull('fecha_cierre')
                            ->exists();
                        return $abierta ? 'warning' : 'gray';
                    })
                    ->searchable(false)
                    ->sortable(false),

                TextColumn::make('users_count')
                    ->label('Operadores')
                    ->counts('users')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->alignCenter()
                    ->suffix(fn($state) => $state == 1 ? ' usuario' : ' usuarios'),

                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('is_boveda')
                    ->label('Bóveda')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->relationship('sucursal', 'nombre'),

                SelectFilter::make('company')
                    ->label('Empresa')
                    ->options(function () {
                        return \App\Models\Company::all()
                            ->mapWithKeys(fn($c) => [$c->id => $c->razon_social ?? 'Sin nombre'])
                            ->toArray();
                    })
                    ->query(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('sucursal', fn($q) => $q->where('company_id', $data['value']));
                        }
                    }),

                Filter::make('en_uso')
                    ->label('🟢 En uso ahora')
                    ->query(fn($query) => $query->whereHas('cierres', fn($q) => $q->whereNull('fecha_cierre'))),

                Filter::make('disponible')
                    ->label('⚪ Disponibles')
                    ->query(fn($query) => $query->whereDoesntHave('cierres', fn($q) => $q->whereNull('fecha_cierre'))),

                Filter::make('is_active')
                    ->label('Solo cajas activas')
                    ->query(fn($query) => $query->where('is_active', true))
                    ->default(true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->before(function ($record, $action) {
                        if ($record->getSaldo() > 0) {
                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body("La caja \"{$record->nombre}\" tiene un saldo de S/ " . number_format($record->getSaldo(), 2) . ". Debe estar en S/ 0.00 para poder eliminarla.")
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),

                // Forzar cierre de caja abierta
                Action::make('forzarCierre')
                    ->label('Forzar Cierre')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(function ($record) {
                        return CierreCaja::where('caja_id', $record->id)
                            ->whereNull('fecha_cierre')
                            ->exists();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Forzar Cierre de Caja')
                    ->modalDescription(fn($record) => "Se cerrará forzosamente la caja \"{$record->nombre}\". El turno activo será cerrado automáticamente.")
                    ->modalSubmitActionLabel('Sí, forzar cierre')
                    ->action(function ($record) {
                        $cierre = CierreCaja::where('caja_id', $record->id)
                            ->whereNull('fecha_cierre')
                            ->first();

                        if ($cierre) {
                            $cierre->update([
                                'fecha_cierre' => now(),
                                'observaciones' => ($cierre->observaciones ? $cierre->observaciones . ' | ' : '')
                                    . 'Cierre forzado por administrador desde panel.',
                            ]);

                            Notification::make()
                                ->title('✅ Caja cerrada forzosamente')
                                ->body("Se cerró el turno de {$cierre->user?->name} en {$record->nombre}.")
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(function ($records) {
                            $bloqueadas = [];
                            foreach ($records as $record) {
                                if ($record->getSaldo() > 0) {
                                    $bloqueadas[] = $record->nombre . ' (S/ ' . number_format($record->getSaldo(), 2) . ')';
                                } else {
                                    $record->delete();
                                }
                            }
                            if (!empty($bloqueadas)) {
                                Notification::make()
                                    ->title('Algunas cajas no se eliminaron')
                                    ->body('Las siguientes cajas tienen saldo mayor a S/ 0.00: ' . implode(', ', $bloqueadas))
                                    ->warning()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->defaultSort('sucursal_id', 'asc')
            ->poll('30s'); // Actualiza automáticamente cada 30 segundos
    }
}

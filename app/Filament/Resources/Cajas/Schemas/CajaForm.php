<?php

namespace App\Filament\Resources\Cajas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class CajaForm
{
    public static function schema(): array
    {
        return [
            Section::make('Información de la Caja')
                ->schema([
                    Select::make('sucursal_id')
                        ->label('Sucursal')
                        ->relationship(
                            'sucursal',
                            'nombre',
                            function ($query) {
                                $user = auth()->guard('admin')->user() ?? auth()->guard('web')->user();
                                if ($user && !$user->hasRole('super_admin')) {
                                    $query->where('company_id', $user->company_id);
                                }
                                return $query;
                            }
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->helperText('Seleccione la sucursal a la que pertenece esta caja'),
                    TextInput::make('nombre')
                        ->label('Nombre de la Caja')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('Ej: Caja 1, Caja Principal')
                        ->helperText('Nombre identificador de la caja'),
                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->maxLength(255)
                        ->rows(2)
                        ->placeholder('Descripción opcional de la caja'),
                    Toggle::make('is_active')
                        ->label('Caja Activa')
                        ->default(true)
                        ->helperText('Solo las cajas activas pueden ser asignadas a usuarios'),
                ])
                ->columns(2),

            Section::make('Usuarios Asignados')
                ->schema([
                    Select::make('users')
                        ->label('Usuarios que operan esta caja')
                        ->relationship(
                            'users',
                            'name',
                            function ($query) {
                                $user = auth()->guard('admin')->user() ?? auth()->guard('web')->user();
                                if ($user && !$user->hasRole('super_admin')) {
                                    $query->where('company_id', $user->company_id);
                                }
                                return $query;
                            }
                        )
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->helperText('Seleccione los usuarios que pueden operar en esta caja'),
                ]),
        ];
    }
}

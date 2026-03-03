<?php

namespace App\Filament\Resources\Tesorerias\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class TesoreriaForm
{
    public static function schema(): array
    {
        return [
            Section::make('Información de la Tesoreria')
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
                        ->helperText('Seleccione la sucursal a la que pertenece esta tesoreria'),
                    TextInput::make('nombre')
                        ->label('Nombre de la Tesoreria')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('Ej: Tesoreria 1, Tesoreria Principal')
                        ->helperText('Nombre identificador de la tesoreria'),
                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->maxLength(255)
                        ->rows(2)
                        ->placeholder('Descripción opcional de la tesoreria'),
                    Toggle::make('is_active')
                        ->label('Tesoreria Activa')
                        ->default(true)
                        ->helperText('Solo las tesorerias activas pueden ser asignadas a usuarios'),
                    \Filament\Forms\Components\Hidden::make('is_boveda')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Usuarios Asignados')
                ->schema([
                    Select::make('users')
                        ->label('Usuarios que operan esta tesoreria')
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
                        ->helperText('Seleccione los usuarios que pueden operar en esta tesoreria'),
                ]),
        ];
    }
}

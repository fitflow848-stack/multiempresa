<?php

namespace App\Filament\Resources\Sucursales\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class SucursalForm
{
    public static function schema(): array
    {
        return [
            Select::make('company_id')
                ->label('Empresa')
                ->relationship('company', 'razon_social')
                ->required(),
            TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
            TextInput::make('direccion')->label('Dirección')->maxLength(255),
            TextInput::make('telefono')->label('Teléfono')->maxLength(50),
        ];
    }
}

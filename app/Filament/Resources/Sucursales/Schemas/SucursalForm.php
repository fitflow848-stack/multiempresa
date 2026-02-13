<?php

namespace App\Filament\Resources\Sucursales\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;

class SucursalForm
{
    public static function schema(): array
    {
        return [
            Section::make('Información de la Sucursal')
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->relationship('company', 'razon_social')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false),
                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('direccion')
                        ->label('Dirección')
                        ->maxLength(255),
                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(50),
                    Toggle::make('is_active')
                        ->label('Sucursal Activa')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Logo de la Sucursal')
                ->schema([
                    FileUpload::make('logo')
                        ->label('Logo')
                        ->image()
                        ->directory('branch-logos')
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg'])
                        ->maxSize(2048)
                        ->imageResizeMode('contain')
                        ->helperText('Si no se sube, se usará el logo de la empresa.')
                        ->columnSpanFull(),
                ]),

            Section::make('Cajas Registradoras')
                ->schema([
                    Repeater::make('cajas')
                        ->relationship('cajas')
                        ->label('')
                        ->schema([
                            TextInput::make('nombre')
                                ->label('Nombre de la Caja')
                                ->required()
                                ->maxLength(100)
                                ->placeholder('Ej: Caja 1, Caja Principal'),
                            TextInput::make('descripcion')
                                ->label('Descripción')
                                ->maxLength(255)
                                ->placeholder('Descripción opcional'),
                            Toggle::make('is_active')
                                ->label('Activa')
                                ->default(true)
                                ->inline(false),
                        ])
                        ->columns(3)
                        ->collapsible()
                        ->defaultItems(1)
                        ->addActionLabel('Agregar Caja')
                        ->itemLabel(fn(array $state): ?string => $state['nombre'] ?? 'Nueva Caja'),
                ])
                ->description('Agregue y configure las cajas registradoras de esta sucursal'),
        ];
    }
}

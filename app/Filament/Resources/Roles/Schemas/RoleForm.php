<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Rol')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Rol')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Nombre único para el rol (ej: admin, vendedor, cajero)'),

                        TextInput::make('guard_name')
                            ->label('Guard')
                            ->default('web')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Guard por defecto para autenticación web'),
                    ])
                    ->columns(2),

                Section::make('Permisos')
                    ->schema([
                        Select::make('permissions')
                            ->label('Permisos Asignados')
                            ->multiple()
                            ->relationship('permissions', 'name')
                            ->options(Permission::all()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->helperText('Seleccione los permisos que tendrá este rol')
                            ->native(false),
                    ]),
            ]);
    }
}

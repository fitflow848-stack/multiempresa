<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function schema(): array
    {
        return [
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
                    CheckboxList::make('permissions')
                        ->label('Accesos Disponibles')
                        ->relationship(
                            'permissions', 
                            'name',
                            fn($query) => auth()->user()->isSuperAdmin() 
                                ? $query 
                                : $query->whereNotIn('name', [
                                    'empresas.crear', 'empresas.editar', 'empresas.eliminar',
                                    'sucursales.crear', 'sucursales.editar', 'sucursales.eliminar'
                                ])
                        )
                        ->searchable()
                        ->bulkToggleable()
                        ->columns(3)
                        ->gridDirection('vertical')
                        ->helperText('Seleccione los permisos que tendrá este rol'),
                ]),
        ];
    }
}

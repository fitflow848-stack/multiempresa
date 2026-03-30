<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

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
                        ->rules(function ($get, $record) {
                            return [
                                'required',
                                'max:255',
                                Rule::unique('roles', 'name')
                                    ->where('guard_name', 'admin')
                                    ->ignore($record?->id)
                            ];
                        })
                        ->helperText('Nombre único para el rol (ej: admin, vendedor, cajero)'),

                    TextInput::make('guard_name')
                        ->label('Guard')
                        ->default('admin')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Guard por defecto para autenticación en el panel administrativo'),
                ])
                ->columns(2),

            Section::make('Permisos')
                ->schema([
                    CheckboxList::make('permissions')
                        ->label('Accesos Disponibles')
                        ->relationship(
                            'permissions', 
                            'name',
                            fn($query) => $query->where('guard_name', 'admin') // Filtrar siempre por guard admin para evitar duplicados
                                ->when(!auth()->user()->isSuperAdmin(), fn($q) => 
                                    $q->whereNotIn('name', [
                                        'empresas.crear', 'empresas.eliminar',
                                        'sucursales.crear', 'sucursales.eliminar'
                                    ])
                                )
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

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
                            $user = auth()->user();
                            return [
                                'required',
                                'max:255',
                                Rule::unique('roles', 'name')
                                    ->where('guard_name', 'admin')
                                    ->where('company_id', $user->company_id)
                                    ->ignore($record?->id)
                            ];
                        })
                        ->helperText('Nombre único para el rol en esta empresa (ej: vendedor, cajero)'),

                    TextInput::make('company_id')
                        ->label('Empresa ID')
                        ->default(fn() => auth()->user()->company_id)
                        ->hidden()
                        ->dehydrated()
                        ->required(),

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
                        ->options(function () {
                            $query = Permission::where('guard_name', 'admin');
                            
                            // Filtrar permisos según el usuario
                            if (!auth()->user()->isSuperAdmin()) {
                                $query->whereNotIn('name', [
                                    'empresas.crear', 'empresas.eliminar',
                                    'sucursales.crear', 'sucursales.eliminar'
                                ]);
                            }
                            
                            return $query->pluck('name', 'id')->toArray();
                        })
                        ->searchable()
                        ->bulkToggleable()
                        ->columns(3)
                        ->gridDirection('vertical')
                        ->helperText('Seleccione los permisos que tendrá este rol'),
                ]),
        ];
    }
}

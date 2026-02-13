<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function schema(): array
    {
        return [
            Section::make('Información Personal')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20),

                    DatePicker::make('birth_date')
                        ->label('Fecha de Nacimiento')
                        ->maxDate(now()),

                    Select::make('gender')
                        ->label('Género')
                        ->options([
                            'male' => 'Masculino',
                            'female' => 'Femenino',
                            'other' => 'Otro'
                        ])
                        ->native(false),
                ])
                ->columns(2),

            Section::make('Configuración de Cuenta')
                ->schema([
                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->required(fn(string $operation): bool => $operation === 'create')
                        ->minLength(8)
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->dehydrated(fn($state) => filled($state))
                        ->revealable()
                        ->helperText('Mínimo 8 caracteres. Dejar vacío para mantener la contraseña actual.'),

                    Select::make('company_id')
                        ->label('Empresa')
                        ->relationship('company', 'razon_social')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->reactive()
                        ->required()
                        ->hidden(fn() => !auth()->user()->hasRole('super_admin'))
                        ->default(fn() => auth()->user()->company_id)
                        ->afterStateUpdated(function (callable $set) {
                            $set('branch_id', null);
                            $set('cajas', []);
                        }),

                    Select::make('branch_id')
                        ->label('Sucursal')
                        ->relationship(
                            'branch',
                            'nombre',
                            function ($query, $get) {
                                $companyId = $get('company_id') ?? auth()->user()->company_id;
                                if ($companyId) {
                                    $query->where('company_id', $companyId);
                                }
                                return $query;
                            }
                        )
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required()
                        ->placeholder(fn($get) => $get('company_id') ? 'Seleccione una sucursal' : 'Seleccione una empresa primero')
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('cajas', [])),

                    Select::make('roles')
                        ->label('Roles')
                        ->relationship(
                            'roles',
                            'name',
                            fn($query) => auth()->user()->hasRole('super_admin') ? $query : $query->where('name', '!=', 'super_admin')
                        )
                        ->multiple()
                        ->preload()
                        ->native(false),

                    Toggle::make('is_active')
                        ->label('Usuario Activo')
                        ->default(true)
                        ->helperText('Determina si el usuario puede acceder al sistema'),
                ])
                ->columns(2),

            Section::make('Asignación de Cajas')
                ->schema([
                    Select::make('cajas')
                        ->label('Cajas Asignadas')
                        ->relationship(
                            'cajas',
                            'nombre',
                            fn($query, $get) =>
                            $query->where('sucursal_id', $get('branch_id'))
                                ->where('is_active', true)
                        )
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->helperText('Seleccione las cajas en las que este usuario puede operar. Solo se muestran cajas activas de la sucursal seleccionada.')
                        ->placeholder('Seleccione una sucursal primero'),
                ])
                ->hidden(fn($get) => !$get('branch_id'))
                ->description('Las cajas disponibles dependen de la sucursal asignada al usuario.'),
        ];
    }
}

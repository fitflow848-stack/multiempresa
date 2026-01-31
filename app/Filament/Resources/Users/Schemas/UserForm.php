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
                        ->afterStateUpdated(fn(callable $set) => $set('branch_id', null)),

                    Select::make('branch_id')
                        ->label('Sucursal')
                        ->relationship(
                            'branch',
                            'nombre',
                            fn($query, $get) =>
                            $query->where('company_id', $get('company_id'))
                        )
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required() // Optional, remove if user can be without branch
                        ->placeholder('Seleccione una empresa primero')
                        ->hidden(fn($get) => ! $get('company_id')),

                    Select::make('roles')
                        ->label('Roles')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->options(Role::all()->pluck('name', 'id'))
                        ->native(false),

                    Toggle::make('is_active')
                        ->label('Usuario Activo')
                        ->default(true)
                        ->helperText('Determina si el usuario puede acceder al sistema'),
                ])
                ->columns(2),
        ];
    }
}

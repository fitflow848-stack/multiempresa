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
                        ->required(fn ($get) => auth()->user()->isSuperAdmin()) // Solo requerido para super_admin
                        ->visible(fn () => auth()->user()->isSuperAdmin()) // Solo visible para super_admin
                        ->default(fn () => auth()->user()->company_id) // Default a la empresa del usuario autenticado
                        ->dehydrated(true)
                        ->afterStateUpdated(function (callable $set) {
                            $set('branches', []);
                            $set('cajas', []);
                        })
                        ->helperText(function () {
                            if (auth()->user()->isSuperAdmin()) {
                                return 'Como Super Admin, puedes asignar cualquier empresa.';
                            }
                            $currentCompany = auth()->user()->company->razon_social ?? 'Sin empresa';
                            return "Se asignará automáticamente tu empresa: {$currentCompany}";
                        }),

                    Select::make('branches')
                        ->label('Sucursales Asignadas')
                        ->relationship(
                            'branches',
                            'nombre',
                            function ($query, $get) {
                                // Deshabilitar el GlobalScope de empresa para que el Super Admin pueda ver sucursales de otras empresas
                                $query->withoutGlobalScopes();

                                // Priorizar la empresa seleccionada en el select
                                $companyId = $get('company_id');

                                // Si no hay seleccionada y es Super Admin, mostrar todas las sucursales
                                if (!$companyId && auth()->user()->isSuperAdmin()) {
                                    return $query;
                                }

                                // Si no hay seleccionada y es usuario normal, usar su propia empresa
                                if (!$companyId) {
                                    $companyId = auth()->user()->company_id;
                                }

                                if ($companyId) {
                                    return $query->where('company_id', $companyId);
                                }

                                return $query->whereRaw('1 = 0');
                            }
                        )
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required(fn () => !auth()->user()->isSuperAdmin())
                        ->placeholder(fn($get) => $get('company_id') ? 'Seleccione sucursales' : 'Seleccione una empresa primero')
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('cajas', [])),

                    Select::make('roles')
                        ->label('Roles')
                        ->relationship(
                            'roles',
                            'name',
                            function ($query, $get) {
                                $authUser = auth()->user();
                                $query->where('roles.guard_name', 'admin');

                                if ($authUser->isSuperAdmin()) {
                                    $companyId = $get('company_id');
                                    if ($companyId) {
                                        // Solo los roles propios de la empresa seleccionada. Ya no se
                                        // incluyen las plantillas globales (company_id NULL): cada
                                        // empresa tiene su propio juego completo de roles, e incluir
                                        // también la plantilla duplicaba las opciones en el selector y
                                        // permitía asignar por error el rol "plantilla" en vez del propio.
                                        $query->where('roles.company_id', $companyId);
                                    }
                                    // Si no hay empresa seleccionada, no filtrar por company_id (mostrar todos)
                                } else {
                                    $rawCompanyId = $authUser->getRawOriginal('company_id') ?? $authUser->company_id;
                                    $query->where('roles.company_id', $rawCompanyId)
                                        ->whereNotIn('roles.name', ['super_admin']);
                                }

                                return $query->orderBy('roles.name');
                            }
                        )
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->native(false)
                        ->reactive()
                        ->default(fn () => []),

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
                            $query->withoutGlobalScopes()
                                ->whereIn('sucursal_id', $get('branches') ?? [])
                                ->where('is_active', true)
                        )
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->helperText('Seleccione las cajas en las que este usuario puede operar. Solo se muestran cajas activas de las sucursales seleccionadas.')
                        ->placeholder('Seleccione sucursales primero'),
                ])
                ->hidden(fn($get) => empty($get('branches')))
                ->description('Las cajas disponibles dependen de las sucursales asignadas al usuario.'),
        ];
    }
}

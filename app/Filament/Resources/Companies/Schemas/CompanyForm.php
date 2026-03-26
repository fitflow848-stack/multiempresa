<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\DB;

class CompanyForm
{
    public static function schema(): array
    {
        $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();
        // El administrador de la empresa debe poder editar sus propios datos, 
        // pero solo el super_admin puede crear empresas o deshabilitarlas completamente (is_active).
        // Sin embargo, para la mayoría de los campos descriptivos permitiremos la edición.
        $isNotAdmin = fn () => !($user?->isAdmin() || $user?->isAdminEmpresa() || $user?->isSuperAdmin());
        $isNotSuperAdmin = fn () => !($user?->isSuperAdmin());

        return [
            Wizard::make([
                Step::make('Datos Generales')
                    ->schema([
                        Section::make('Información Principal')
                            ->schema([
                                TextInput::make('ruc')
                                    ->label('RUC')
                                    ->required()
                                    ->length(11)
                                    ->disabled($isNotSuperAdmin) 
                                    ->suffixAction(
                                        Action::make('searchRuc')
                                            ->icon('heroicon-m-magnifying-glass')
                                            ->action(function ($state, callable $set) {
                                                if (strlen($state) !== 11) {
                                                    return;
                                                }
                                                
                                                $service = app(\App\Services\PeruConsultasService::class);
                                                $resultado = $service->consultarRuc($state);
                                                
                                                if (!isset($resultado['error']) && isset($resultado['razonSocial'])) {
                                                    $set('razon_social', $resultado['razonSocial']);
                                                    
                                                    if (!empty($resultado['nombreComercial'])) {
                                                        $set('nombre_comercial', $resultado['nombreComercial']);
                                                    }
                                                    
                                                    if (!empty($resultado['direccion'])) {
                                                        $set('direccion_fiscal', $resultado['direccion']);
                                                    }
                                                    
                                                    if (!empty($resultado['departamento'])) {
                                                        $set('department', $resultado['departamento']);
                                                    }
                                                    if (!empty($resultado['provincia'])) {
                                                        $set('province', $resultado['provincia']);
                                                    }
                                                    if (!empty($resultado['distrito'])) {
                                                        $set('district', $resultado['distrito']);
                                                        
                                                        // También setear el ubigeo interno automáticamente si lo tenemos
                                                        if (!empty($resultado['departamento']) && !empty($resultado['provincia'])) {
                                                            $dep = \App\Models\Departamento::where('dep_nombre', $resultado['departamento'])->first();
                                                            if ($dep) {
                                                                 $prov = \App\Models\Provincia::where('dep_codigo', $dep->dep_cod)->where('pro_nombre', $resultado['provincia'])->first();
                                                                 if ($prov) {
                                                                     $dist = \App\Models\Distrito::where('dep_codigo', $dep->dep_cod)->where('pro_codigo', $prov->pro_cod)->where('dis_nombre', $resultado['distrito'])->first();
                                                                     if ($dist) {
                                                                         $set('ubigeo', $dep->dep_cod . $prov->pro_cod . $dist->dis_codigo);
                                                                     }
                                                                 }
                                                            }
                                                        }
                                                    }
                                                }
                                            })
                                    ),
                                TextInput::make('razon_social')
                                    ->label('Razón Social')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled($isNotSuperAdmin),
                                TextInput::make('nombre_comercial')
                                    ->label('Nombre Comercial')
                                    ->maxLength(255)
                                    ->disabled($isNotSuperAdmin),
                                TextInput::make('tipo_contribuyente')
                                    ->label('Tipo de Contribuyente')
                                    ->maxLength(100)
                                    ->disabled($isNotSuperAdmin),
                                FileUpload::make('logo')
                                    ->label('Logo de la Empresa')
                                    ->image()
                                    ->directory('company-logos')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg'])
                                    ->maxSize(2048)
                                    ->imageResizeMode('contain')
                                    ->columnSpanFull()
                                    ->disabled($isNotSuperAdmin),
                            ])->columns(2),

                        Section::make('Representante Legal')
                            ->disabled($isNotSuperAdmin)
                            ->schema([
                                TextInput::make('rep_nombre')->label('Nombre'),
                                Select::make('rep_document_type')
                                    ->label('Tipo Documento')
                                    ->options([
                                        'DNI' => 'DNI',
                                        'CE' => 'CE',
                                    ]),
                                TextInput::make('rep_document_number')->label('N° Documento'),
                                TextInput::make('rep_cargo')->label('Cargo'),
                                TextInput::make('rep_email')->email()->label('Email'),
                                TextInput::make('rep_phone')->tel()->label('Teléfono'),
                            ])->columns(2),

                        Section::make('Datos de Contacto')
                            ->disabled($isNotSuperAdmin)
                            ->schema([
                                TextInput::make('email')->email()->label('Email'),
                                TextInput::make('phone')->tel()->label('Teléfono'),
                                TextInput::make('website')->url()->label('Web'),
                            ])->columns(2),
                    ]),

                Step::make('Configuración')
                    ->schema([
                        Section::make('Ubicación Fiscal')
                            ->disabled($isNotAdmin)
                            ->schema([
                                Select::make('department')
                                    ->label('Departamento')
                                    ->options(\App\Models\Departamento::pluck('dep_nombre', 'dep_nombre')->toArray())
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('province', null);
                                        $set('district', null);
                                        $set('ubigeo', null);
                                    })
                                    ->searchable(),
                                Select::make('province')
                                    ->label('Provincia')
                                    ->options(function (callable $get) {
                                        $depNombre = $get('department');
                                        if (! $depNombre) {
                                            return [];
                                        }
                                        $departamento = \App\Models\Departamento::where('dep_nombre', $depNombre)->first();
                                        if (!$departamento) return [];
                                        
                                        return \App\Models\Provincia::where('dep_codigo', $departamento->dep_cod)->pluck('pro_nombre', 'pro_nombre')->toArray();
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('district', null);
                                        $set('ubigeo', null);
                                    })
                                    ->searchable(),
                                Select::make('district')
                                    ->label('Distrito')
                                    ->options(function (callable $get) {
                                        $depNombre = $get('department');
                                        $proNombre = $get('province');
                                        if (! $depNombre || ! $proNombre) {
                                            return [];
                                        }
                                        $departamento = \App\Models\Departamento::where('dep_nombre', $depNombre)->first();
                                        if (!$departamento) return [];
                                        
                                        $provincia = \App\Models\Provincia::where('dep_codigo', $departamento->dep_cod)->where('pro_nombre', $proNombre)->first();
                                        if (!$provincia) return [];
                                        
                                        return \App\Models\Distrito::where('dep_codigo', $departamento->dep_cod)->where('pro_codigo', $provincia->pro_cod)->pluck('dis_nombre', 'dis_nombre')->toArray();
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                        $depNombre = $get('department');
                                        $proNombre = $get('province');
                                        if ($depNombre && $proNombre && $state) {
                                            $departamento = \App\Models\Departamento::where('dep_nombre', $depNombre)->first();
                                            if ($departamento) {
                                                $provincia = \App\Models\Provincia::where('dep_codigo', $departamento->dep_cod)->where('pro_nombre', $proNombre)->first();
                                                if ($provincia) {
                                                    $distrito = \App\Models\Distrito::where('dep_codigo', $departamento->dep_cod)
                                                        ->where('pro_codigo', $provincia->pro_cod)
                                                        ->where('dis_nombre', $state)->first();
                                                    if ($distrito) {
                                                        $set('ubigeo', $departamento->dep_cod . $provincia->pro_cod . $distrito->dis_codigo);
                                                        return;
                                                    }
                                                }
                                            }
                                        }
                                        $set('ubigeo', null);
                                    })
                                    ->searchable(),
                                Textarea::make('direccion_fiscal')
                                    ->label('Dirección Fiscal')
                                    ->columnSpanFull(),
                                TextInput::make('ubigeo')
                                    ->label('Ubigeo')
                                    ->readOnly()
                                    ->length(6),
                            ])->columns(2),

                        Section::make('Facturación y Certificado')
                            ->schema([
                                FileUpload::make('cert_file')
                                    ->label('Subir Firma Electrónica')
                                    ->directory('sunat/certificados')
                                    ->visibility('private')
                                    ->columnSpanFull()
                                    ->helperText('Puede arrastrar la firma aquí o dar click para subirlo'),
                                TextInput::make('sol_user')
                                    ->label('Usuario Sunat')
                                    ->placeholder('Usuario Sunat')
                                    ->prefixIcon('heroicon-m-user'),
                                TextInput::make('sol_password')
                                    ->label('Clave Sunat')
                                    ->placeholder('Clave Sunat')
                                    ->password()
                                    ->prefixIcon('heroicon-m-lock-closed'),
                                TextInput::make('sunat_client_id')
                                    ->label('Client ID (API SUNAT)')
                                    ->placeholder('Client ID (API SUNAT)')
                                    ->prefixIcon('heroicon-m-identification'),
                                TextInput::make('sunat_client_secret')
                                    ->label('Client Secret (API SUNAT)')
                                    ->placeholder('Client Secret (API SUNAT)')
                                    ->password()
                                    ->prefixIcon('heroicon-m-key'),
                            ])->columns(2),

                        Section::make('Régimen y Bancos')
                            ->schema([
                                TextInput::make('regimen')->label('Régimen'),
                                Toggle::make('afecto_igv')->label('Afecto IGV')->default(true),
                                TextInput::make('porcentaje_igv')->numeric()->default(18)->suffix('%'),
                                TextInput::make('bank')->label('Banco'),
                                Select::make('account_type')->label('Tipo de Cuenta')
                                    ->options([
                                        'corriente' => 'Corriente',
                                        'ahorro' => 'Ahorros',
                                    ]),
                                TextInput::make('account_number')->label('Cuenta'),
                                TextInput::make('cci')->label('CCI'),
                            ])->columns(2),

                        Section::make('Control Interno')
                            ->schema([
                                Toggle::make('is_active')->default(true)->disabled($isNotSuperAdmin),
                                DatePicker::make('fecha_alta')->disabled($isNotSuperAdmin),
                                Textarea::make('observations')->label('Observaciones')->columnSpanFull()->disabled($isNotSuperAdmin),
                                Textarea::make('ticket_footer_message')
                                    ->label('Frase de pie de página (Tickets/Facturas)')
                                    ->placeholder('Ej: Gracias por su compra. Vuelva pronto.')
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ]),

                Step::make('Sucursales y Documentos')
                    ->schema([
                        Repeater::make('sucursales')
                            ->relationship('sucursales')
                            ->label('Sucursales')
                            ->addable(fn ($get) => ! $isNotSuperAdmin())
                            ->deletable(fn ($get) => ! $isNotSuperAdmin())
                            ->schema([
                                Tabs::make('sucursal_tabs')
                                    ->tabs([
                                        Tab::make('Datos Básicos')
                                            ->icon('heroicon-o-building-office')
                                            ->disabled($isNotSuperAdmin)
                                            ->schema([
                                                TextInput::make('nombre')
                                                    ->required()
                                                    ->label('Nombre de Sucursal'),
                                                TextInput::make('direccion')
                                                    ->label('Dirección'),
                                                TextInput::make('telefono')
                                                    ->tel()
                                                    ->label('Teléfono'),
                                                FileUpload::make('logo')
                                                    ->label('Logo de la Sucursal')
                                                    ->image()
                                                    ->directory('branch-logos')
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg'])
                                                    ->maxSize(2048)
                                                    ->imageResizeMode('contain')
                                                    ->helperText('Si no se sube, usará el logo de la empresa'),
                                                Toggle::make('is_active')
                                                    ->label('Sucursal Activa')
                                                    ->default(true),
                                            ])
                                            ->columns(2),

                                        Tab::make('Cajas Registradoras')
                                            ->icon('heroicon-o-calculator')
                                            ->badge(fn($get) => count($get('cajas') ?? []))
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
                                            ]),

                                        Tab::make('Series de Documentos')
                                            ->icon('heroicon-o-document-text')
                                            ->badge(fn($get) => count($get('documents') ?? []))
                                            ->schema([
                                                Repeater::make('documents')
                                                    ->relationship('documents')
                                                    ->label('')
                                                    ->schema([
                                                        Select::make('sunat_document_id')
                                                            ->label('Tipo de Documento')
                                                            ->options(fn() => DB::table('documentos_sunat')->pluck('nombre', 'id_tido'))
                                                            ->required()
                                                            ->reactive()
                                                            ->afterStateUpdated(function ($state, callable $set) {
                                                                $defaultSeries = match ($state) {
                                                                    '1' => 'B001',
                                                                    '2' => 'F001',
                                                                    '3' => 'FC01',
                                                                    '4' => 'FD01',
                                                                    '11' => 'T001',
                                                                    default => null,
                                                                };
                                                                if ($defaultSeries) {
                                                                    $set('series', $defaultSeries);
                                                                }
                                                                $set('number', 1);
                                                            }),
                                                        TextInput::make('series')
                                                            ->label('Serie')
                                                            ->required(),
                                                        TextInput::make('number')
                                                            ->label('Correlativo Inicial')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->required(),
                                                    ])
                                                    ->columns(3)
                                                    ->collapsible()
                                                    ->addActionLabel('Agregar Serie')
                                                    ->itemLabel(fn(array $state): ?string => $state['series'] ?? 'Nueva Serie'),
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->addActionLabel('Agregar Sucursal')
                            ->itemLabel(fn(array $state): ?string => $state['nombre'] ?? 'Nueva Sucursal'),
                    ]),
            ])
                ->columnSpanFull()
                ->skippable() 
        ];
    }
}

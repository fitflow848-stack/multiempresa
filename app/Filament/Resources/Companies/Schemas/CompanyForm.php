<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\DB;

class CompanyForm
{
    public static function schema(): array
    {
        return [
            Wizard::make([
                Step::make('Datos Generales')
                    ->schema([
                        Section::make('Información Principal')
                            ->schema([
                                TextInput::make('ruc')
                                    ->label('RUC')
                                    ->required()
                                    ->length(11),
                                TextInput::make('razon_social')
                                    ->label('Razón Social')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('nombre_comercial')
                                    ->label('Nombre Comercial')
                                    ->maxLength(255),
                                TextInput::make('tipo_contribuyente')
                                    ->label('Tipo de Contribuyente')
                                    ->maxLength(100),
                                FileUpload::make('logo')
                                    ->label('Logo de la Empresa')
                                    ->image()
                                    ->directory('company-logos')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg'])
                                    ->maxSize(2048)
                                    ->imageResizeMode('contain')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Representante Legal')
                            ->schema([
                                TextInput::make('rep_nombre')->label('Nombre'),
                                Select::make('rep_document_type')
                                    ->label('Tipo Documento')
                                    ->options([
                                        'DNI' => 'DNI',
                                        'CE'  => 'CE',
                                    ]),
                                TextInput::make('rep_document_number')->label('N° Documento'),
                                TextInput::make('rep_cargo')->label('Cargo'),
                                TextInput::make('rep_email')->email()->label('Email'),
                                TextInput::make('rep_phone')->tel()->label('Teléfono'),
                            ])->columns(2),

                        Section::make('Datos de Contacto')
                            ->schema([
                                TextInput::make('email')->email()->label('Email'),
                                TextInput::make('phone')->tel()->label('Teléfono'),
                                TextInput::make('website')->url()->label('Web'),
                            ])->columns(2),
                    ]),

                Step::make('Configuración')
                    ->schema([
                        Section::make('Ubicación Fiscal')
                            ->schema([
                                TextInput::make('department')->label('Departamento'),
                                TextInput::make('province')->label('Provincia'),
                                TextInput::make('district')->label('Distrito'),
                                Textarea::make('direccion_fiscal')
                                    ->label('Dirección Fiscal')
                                    ->columnSpanFull(),
                                TextInput::make('ubigeo')->label('Ubigeo')->length(6),
                            ])->columns(2),

                        Section::make('Facturación y Certificado')
                            ->schema([
                                TextInput::make('sol_user')->label('Usuario SOL'),
                                TextInput::make('sol_password')
                                    ->label('Clave SOL')
                                    ->password()
                                    ->dehydrateStateUsing(fn($state) => $state ? encrypt($state) : null),
                                TextInput::make('sunat_local_code')->label('Código Local')->default('0000'),
                                FileUpload::make('cert_file')
                                    ->label('Archivo del Certificado')
                                    ->directory('sunat/certificados')
                                    ->visibility('private'),
                                TextInput::make('cert_password')->label('Contraseña del Certificado')->password(),
                                DatePicker::make('cert_expires_at')->label('Vencimiento'),
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
                                Toggle::make('is_active')->default(true),
                                DatePicker::make('fecha_alta'),
                                Textarea::make('observations')->label('Observaciones')->columnSpanFull(),
                            ])->columns(2),
                    ]),

                Step::make('Sucursales y Documentos')
                    ->schema([
                        Repeater::make('sucursales')
                            ->relationship('sucursales')
                            ->label('Sucursales')
                            ->schema([
                                TextInput::make('nombre')->required()->label('Nombre de Sucursal'),
                                TextInput::make('direccion')->label('Dirección'),
                                TextInput::make('telefono')->tel()->label('Teléfono'),

                                \Filament\Forms\Components\Repeater::make('documents')
                                    ->relationship('documents')
                                    ->label('Series de Documentos')
                                    ->schema([
                                        Select::make('sunat_document_id')
                                            ->label('Tipo de Documento')
                                            ->options(fn() => \Illuminate\Support\Facades\DB::table('documentos_sunat')->pluck('nombre', 'id_tido'))
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                // Asignar serie por defecto según el tipo
                                                $defaultSeries = match ($state) {
                                                    '1' => 'B001', // Boleta
                                                    '2' => 'F001', // Factura
                                                    '3' => 'FC01', // Nota Credito
                                                    '4' => 'FD01', // Nota Debito
                                                    '11' => 'T001', // Guia Remision
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
                                    ->itemLabel(fn(array $state): ?string => $state['series'] ?? null),
                            ])
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['nombre'] ?? null),
                    ]),
            ])
                ->columnSpanFull()
                ->skippable() // Permitir saltar pasos si es necesario (o quitar si se quiere estricto)
        ];
    }
}

<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class CompanyForm
{
    public static function schema(): array
    {
        return [

                /* =========================
                 * BÁSICO (SIEMPRE VISIBLE)
                 * ========================= */
                Section::make('Datos Básicos')
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
                            ->maxSize(2048) // 2MB máximo
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth(300)
                            ->imageResizeTargetHeight(300)
                            ->helperText('Formatos aceptados: PNG, JPG, JPEG. Tamaño máximo: 2MB')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                /* =========================
                 * REPRESENTANTE LEGAL
                 * ========================= */
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
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                /* =========================
                 * UBICACIÓN FISCAL
                 * ========================= */
                Section::make('Ubicación Fiscal')
                    ->schema([
                        TextInput::make('department')->label('Departamento'),
                        TextInput::make('province')->label('Provincia'),
                        TextInput::make('district')->label('Distrito'),
                        Textarea::make('direccion_fiscal')
                            ->label('Dirección Fiscal')
                            ->columnSpanFull(),
                        TextInput::make('ubigeo')->label('Ubigeo')->length(6),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                /* =========================
                 * SUNAT – FACTURACIÓN
                 * ========================= */
                Section::make('Facturación Electrónica (SUNAT)')
                    ->schema([
                        TextInput::make('sol_user')->label('Usuario SOL'),

                        TextInput::make('sol_password')
                            ->label('Clave SOL')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => $state ? encrypt($state) : null),

                        TextInput::make('sunat_local_code')
                            ->label('Código Local')
                            ->default('0000'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                /* =========================
                 * CERTIFICADO DIGITAL
                 * ========================= */
                Section::make('Certificado Digital')
                    ->schema([
                        FileUpload::make('cert_file')
                            ->label('Archivo del Certificado')
                            ->directory('sunat/certificados')
                            ->visibility('private'),

                        TextInput::make('cert_password')
                            ->label('Contraseña del Certificado')
                            ->password(),

                        DatePicker::make('cert_expires_at')
                            ->label('Vencimiento'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                /* =========================
                 * CONTACTO
                 * ========================= */
                Section::make('Datos de Contacto')
                    ->schema([
                        TextInput::make('email')->email()->label('Email'),
                        TextInput::make('phone')->tel()->label('Teléfono'),
                        TextInput::make('website')->url()->label('Web'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                /* =========================
                 * RÉGIMEN
                 * ========================= */
                Section::make('Régimen Tributario')
                    ->schema([
                        TextInput::make('regimen')->label('Régimen'),
                        Toggle::make('afecto_igv')->label('Afecto IGV')->default(true),
                        TextInput::make('porcentaje_igv')
                            ->numeric()
                            ->default(18)
                            ->suffix('%'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                /* =========================
                 * SERIES
                 * ========================= */
                Section::make('Series de Comprobantes')
                    ->schema([
                        TextInput::make('serie_factura')->default('F001'),
                        TextInput::make('serie_boleta')->default('B001'),
                        TextInput::make('serie_nota_credito')->default('FC01'),
                        TextInput::make('serie_nota_debito')->default('FD01'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                /* =========================
                 * BANCOS
                 * ========================= */
                Section::make('Información Bancaria')
                    ->schema([
                        TextInput::make('bank')->label('Banco'),
                        Select::make('account_type')->label('Tipo de Cuenta')
                            ->options([
                                'corriente' => 'Corriente',
                                'ahorro' => 'Ahorros',
                            ]),
                        TextInput::make('account_number')->label('Cuenta'),
                        TextInput::make('cci')->label('CCI'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                /* =========================
                 * CONTROL
                 * ========================= */
                Section::make('Control Interno')
                    ->schema([
                        Toggle::make('is_active')->default(true),
                        DatePicker::make('fecha_alta'),
                        Textarea::make('observations')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
        ];
    }
}

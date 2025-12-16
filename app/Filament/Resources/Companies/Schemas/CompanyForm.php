<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class CompanyForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre de la Empresa')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('ruc')
                    ->label('RUC')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                    
                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->maxLength(255),
                    
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20),
                    
                Textarea::make('address')
                    ->label('Dirección')
                    ->columnSpanFull()
                    ->rows(3),
                    
                Toggle::make('is_active')
                    ->label('Empresa Activa')
                    ->default(true)
                    ->helperText('Determina si la empresa está activa en el sistema'),
            ]);
    }
}

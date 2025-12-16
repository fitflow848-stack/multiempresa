<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;

class CompanyInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información de la Empresa')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),
                        
                        TextEntry::make('ruc')
                            ->label('RUC'),
                        
                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope'),
                        
                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone'),
                        
                        TextEntry::make('address')
                            ->label('Dirección')
                            ->columnSpanFull(),
                        
                        IconEntry::make('is_active')
                            ->label('Estado')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        
                        TextEntry::make('created_at')
                            ->label('Fecha de Creación')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),

                Section::make('Usuarios')
                    ->schema([
                        TextEntry::make('users_count')
                            ->label('Total de Usuarios')
                            ->state(fn ($record) => $record->users()->count()),
                    ]),
            ]);
    }
}

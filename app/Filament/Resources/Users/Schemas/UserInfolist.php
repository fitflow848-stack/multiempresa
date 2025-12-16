<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;

class UserInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información Personal')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),
                        
                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope'),
                        
                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone'),
                        
                        TextEntry::make('birth_date')
                            ->label('Fecha de Nacimiento')
                            ->date('d/m/Y'),
                        
                        TextEntry::make('gender')
                            ->label('Género')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'male' => 'Masculino',
                                'female' => 'Femenino',
                                'other' => 'Otro',
                                default => 'No especificado',
                            }),
                    ])
                    ->columns(2),

                Section::make('Información del Sistema')
                    ->schema([
                        TextEntry::make('company.name')
                            ->label('Empresa')
                            ->default('Sin empresa asignada'),
                        
                        TextEntry::make('roles.name')
                            ->label('Roles')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'admin' => 'danger',
                                'supervisor' => 'warning',
                                'vendedor' => 'success',
                                'cajero' => 'info',
                                default => 'gray',
                            }),
                        
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
            ]);
    }
}

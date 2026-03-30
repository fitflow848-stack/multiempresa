<?php

namespace App\Filament\Resources\Cajas;

use App\Filament\Resources\Cajas\Pages\ListCajas;
use App\Filament\Resources\Cajas\Pages\CreateCaja;
use App\Filament\Resources\Cajas\Pages\EditCaja;
use App\Filament\Resources\Cajas\Pages\ViewCaja;
use App\Filament\Resources\Cajas\Schemas\CajaForm;
use App\Filament\Resources\Cajas\Tables\CajasTable;
use App\Models\Caja;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CajaResource extends Resource
{
    protected static ?string $model = Caja::class;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->can('cajas.ver') || $user->hasRole(['super_admin', 'admin_empresa']));
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|UnitEnum|null $navigationGroup = 'Estructura';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(
            CajaForm::schema()
        );
    }

    public static function table(Table $table): Table
    {
        return CajasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCajas::route('/'),
            'create' => CreateCaja::route('/create'),
            'view' => ViewCaja::route('/{record}'),
            'edit' => EditCaja::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()->where('is_boveda', false);
        $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();
        if ($user && !$user->roles()->where('name', 'super_admin')->exists()) {
            $query->whereHas('sucursal', fn($q) => $q->where('company_id', $user->company_id));
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }
}

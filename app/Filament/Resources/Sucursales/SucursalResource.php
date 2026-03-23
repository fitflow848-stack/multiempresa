<?php

namespace App\Filament\Resources\Sucursales;

use App\Filament\Resources\Sucursales\Pages\ListSucursales;
use App\Filament\Resources\Sucursales\Pages\CreateSucursal;
use App\Filament\Resources\Sucursales\Pages\EditSucursal;
use App\Filament\Resources\Sucursales\Pages\ViewSucursal;
use App\Filament\Resources\Sucursales\Schemas\SucursalForm;
use App\Filament\Resources\Sucursales\Tables\SucursalesTable;
use App\Models\Sucursal;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SucursalResource extends Resource
{
    protected static ?string $model = Sucursal::class;

    // Use a known heroicon name to avoid SvgNotFound (reuse Company icon)
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = 'Estructura';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(
            SucursalForm::schema()
        );
    }

    public static function table(Table $table): Table
    {
        return SucursalesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSucursales::route('/'),
            'create' => CreateSucursal::route('/create'),
            'view' => ViewSucursal::route('/{record}'),
            'edit' => EditSucursal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();
        if ($user && !$user->roles()->where('name', 'super_admin')->exists()) {
            $query->where('company_id', $user->company_id);
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }
}

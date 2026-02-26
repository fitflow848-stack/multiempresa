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

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $modelLabel = 'Caja';

    protected static ?string $pluralModelLabel = 'Cajas';

    protected static ?int $navigationSort = 4;

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
        $query = parent::getEloquentQuery();
        $user = auth()->guard('admin')->user() ?? auth()->guard('web')->user();

        if ($user && !$user->hasRole('super_admin')) {
            $query->whereHas('sucursal', fn($q) => $q->where('company_id', $user->company_id));
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }
}

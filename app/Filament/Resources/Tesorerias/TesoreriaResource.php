<?php

namespace App\Filament\Resources\Tesorerias;

use App\Filament\Resources\Tesorerias\Pages\ListTesorerias;
use App\Filament\Resources\Tesorerias\Pages\CreateTesoreria;
use App\Filament\Resources\Tesorerias\Pages\EditTesoreria;
use App\Filament\Resources\Tesorerias\Pages\ViewTesoreria;
use App\Filament\Resources\Tesorerias\Schemas\TesoreriaForm;
use App\Filament\Resources\Tesorerias\Tables\TesoreriasTable;
use App\Models\Caja;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TesoreriaResource extends Resource
{
    protected static ?string $model = Caja::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Tesorería / Caja';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $modelLabel = 'Tesorería';

    protected static ?string $pluralModelLabel = 'Tesorerías';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(
            TesoreriaForm::schema()
        );
    }

    public static function table(Table $table): Table
    {
        return TesoreriasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTesorerias::route('/'),
            'create' => CreateTesoreria::route('/create'),
            'view' => ViewTesoreria::route('/{record}'),
            'edit' => EditTesoreria::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()->where('is_boveda', true);
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

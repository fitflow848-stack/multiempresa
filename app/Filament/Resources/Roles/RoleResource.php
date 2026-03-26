<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Tables\RolesTable;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use Spatie\Permission\Models\Role;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected static UnitEnum|string|null $navigationGroup = 'Usuarios y Accesos';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(
            RoleForm::schema()
        );
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    /**
     * El admin_empresa no puede ver ni tocar los roles de infraestructura del sistema.
     * El super_admin ve todos los roles.
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();

        // Siempre filtramos por el guard actual del panel para evitar duplicados visuales
        $query->where('guard_name', 'admin');

        if ($user && !$user->isSuperAdmin()) {
            // Los roles de sistema no deben ser editables por el dueño del negocio
            $systemRoles = ['super_admin', 'admin_empresa'];
            $query->whereNotIn('name', $systemRoles);
        }

        return $query;
    }

}

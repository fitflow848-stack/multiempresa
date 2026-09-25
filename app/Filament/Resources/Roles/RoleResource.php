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

    public static function canAccess(): bool
    {
        $user = auth('admin')->user();
        return $user && ($user->can('roles.ver', 'admin') || $user->hasRole(['super_admin', 'administrador']));
    }

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
     * El administrador (dueño del negocio) puede ver el rol "administrador" en la
     * lista (para saber qué permisos tiene), pero no editarlo ni borrarlo: eso lo
     * bloquea RolePolicy::update()/delete(), no esta consulta. El super_admin ve
     * todos los roles.
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();

        // Siempre filtramos por el guard actual del panel para evitar duplicados visuales
        $query->where('guard_name', 'admin');

        if ($user && !$user->isSuperAdmin()) {
            // El dueño del negocio solo ve roles de su propia empresa
            $query->where('company_id', $user->company_id);

            // "super_admin" nunca debe listarse para el dueño del negocio, ni
            // siquiera la copia que se sembró por accidente dentro de su propia
            // empresa al crearla (es una plantilla de infraestructura, no un rol
            // de negocio real).
            $query->where('name', '!=', 'super_admin');
        }

        return $query;
    }

}

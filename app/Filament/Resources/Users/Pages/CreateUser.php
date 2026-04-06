<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si el usuario no es super_admin, asignar automáticamente su empresa
        if (!$user->isSuperAdmin()) {
            $data['company_id'] = $user->company_id;
        }
        
        // Si no se especificó empresa pero el usuario tiene una, asignarla
        if (empty($data['company_id']) && $user->company_id) {
            $data['company_id'] = $user->company_id;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncRolesWithCompany($this->record);
    }

    private function syncRolesWithCompany($record): void
    {
        $companyId = $record->company_id;
        if (!$companyId) return;

        // Filament sync() no incluye company_id en el pivot, corregir
        DB::table('model_has_roles')
            ->where('model_id', $record->id)
            ->where('model_type', get_class($record))
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        // Sincronizar roles web: por cada rol admin asignado, asignar también el web
        $adminRoleNames = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $record->id)
            ->where('model_has_roles.model_type', get_class($record))
            ->where('roles.guard_name', 'admin')
            ->pluck('roles.name');

        foreach ($adminRoleNames as $roleName) {
            $webRole = DB::table('roles')
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->where('company_id', $companyId)
                ->first();

            if ($webRole) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $webRole->id,
                    'model_type' => get_class($record),
                    'model_id' => $record->id,
                    'company_id' => $companyId,
                ]);
            }
        }

        // Limpiar cache de Spatie
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

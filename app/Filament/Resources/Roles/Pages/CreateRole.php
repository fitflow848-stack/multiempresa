<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asegurar que el guard_name sea siempre 'admin'
        $data['guard_name'] = 'admin';
        
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Obtener el ID de la empresa del usuario actual
        $companyId = auth()->user()->company_id;

        // Crear el rol asegurando que incluimos el company_id
        $role = \Spatie\Permission\Models\Role::create([
            'name' => $data['name'],
            'guard_name' => 'admin',
            'company_id' => $companyId
        ]);

        // Asignar permisos
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            // Convertir IDs a nombres de permisos
            $permissionNames = \Spatie\Permission\Models\Permission::whereIn('id', $data['permissions'])
                ->where('guard_name', 'admin')
                ->pluck('name')
                ->toArray();
            
            $role->syncPermissions($permissionNames);
        }

        return $role;
    }
}

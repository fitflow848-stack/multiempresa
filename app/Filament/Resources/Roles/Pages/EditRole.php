<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\Concerns\SyncsRoleAcrossGuards;
use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    use SyncsRoleAcrossGuards;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar explícitamente los permisos relacionados
        if ($this->record) {
            $data['permissions'] = $this->record->permissions()
                ->where('guard_name', 'admin')
                ->pluck('id')
                ->toArray();
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Asegurar que el guard_name se mantenga como 'admin'
        $data['guard_name'] = 'admin';
        
        return $data;
    }

    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Actualizar el rol
        $record->update([
            'name' => $data['name'],
            'guard_name' => 'admin'
        ]);

        // Sincronizar permisos
        $permissionNames = [];
        if (isset($data['permissions'])) {
            // Convertir IDs a nombres de permisos
            $permissionNames = \Spatie\Permission\Models\Permission::whereIn('id', $data['permissions'])
                ->where('guard_name', 'admin')
                ->pluck('name')
                ->toArray();
        }
        $record->syncPermissions($permissionNames);

        // El panel solo edita el rol "admin"; replicar hacia su gemelo "web"
        // para que el cambio surta efecto en la app real (POS, sidebar, etc.).
        $this->syncWebGuardRole($record, $permissionNames);

        return $record;
    }
}

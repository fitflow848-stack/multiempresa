<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

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
}

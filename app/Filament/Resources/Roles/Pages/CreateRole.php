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
}

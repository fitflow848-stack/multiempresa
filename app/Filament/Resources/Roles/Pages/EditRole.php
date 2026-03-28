<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Asegurar que el guard_name se mantenga como 'admin'
        $data['guard_name'] = 'admin';
        
        return $data;
    }
}

<?php

namespace App\Filament\Resources\Tesorerias\Pages;

use App\Filament\Resources\Tesorerias\TesoreriaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTesoreria extends CreateRecord
{
    protected static string $resource = TesoreriaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

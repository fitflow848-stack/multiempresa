<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Services\Sunat;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var \App\Models\Company $record */
        $record = $this->record;
        if ($record->cert_file) {
            $sunatService = app(Sunat::class);
            if (Storage::exists($record->cert_file)) {
                $certContent = base64_encode(Storage::get($record->cert_file));

                $sunatService->guardarCertificado($record->ruc, $certContent);

                Notification::make()
                    ->title('Certificado enviado correctamente al API')
                    ->success()
                    ->send();
            }
        }
    }
}

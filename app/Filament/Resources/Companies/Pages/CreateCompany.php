<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\Company $record */
        $record = $this->record;

        if ($record->cert_file && $record->cert_password) {
            $sunatService = app(\App\Services\Sunat::class);

            if (\Illuminate\Support\Facades\Storage::exists($record->cert_file)) {
                $certContent = base64_encode(\Illuminate\Support\Facades\Storage::get($record->cert_file));

                $password = $record->cert_password;

                $sunatService->guardarCertificado($record->ruc, $password, $certContent);

                \Filament\Notifications\Notification::make()
                    ->title('Certificado enviado correctamente al API')
                    ->success()
                    ->send();
            }
        }
    }
}

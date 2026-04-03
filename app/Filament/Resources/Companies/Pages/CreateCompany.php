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

        // 1. Configuración de SUNAT (Existente)
        if ($record->cert_file && $record->cert_password) {
            $sunatService = app(\App\Services\Sunat::class);

            if (\Illuminate\Support\Facades\Storage::exists($record->cert_file)) {
                $certContent = base64_encode(\Illuminate\Support\Facades\Storage::get($record->cert_file));

                $sunatService->guardarCertificado($record->ruc, $certContent);

                \Filament\Notifications\Notification::make()
                    ->title('Certificado enviado correctamente al API')
                    ->success()
                    ->send();
            }
        }

        // 2. Sembrar ROLES por defecto para la nueva empresa
        // Copiamos los roles globales (que tienen company_id NULL)
        $templateRoles = \Spatie\Permission\Models\Role::whereNull('company_id')->get();

        foreach ($templateRoles as $template) {
            $newRole = \Spatie\Permission\Models\Role::create([
                'name' => $template->name,
                'guard_name' => $template->guard_name,
                'company_id' => $record->id
            ]);

            // Copiar los permisos del rol plantilla
            $permissionNames = $template->permissions()->pluck('name')->toArray();
            $newRole->syncPermissions($permissionNames);
        }
    }
}

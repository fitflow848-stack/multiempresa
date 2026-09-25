<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Concerns\HandlesSunatCertificado;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    use HandlesSunatCertificado;

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
        if ($record->cert_file && \Illuminate\Support\Facades\Storage::exists($record->cert_file)) {
            $this->enviarCertificadoASunat($record);
        }

        // 2. Sembrar ROLES por defecto para la nueva empresa
        // Copiamos los roles globales (que tienen company_id NULL)
        $templateRoles = \Spatie\Permission\Models\Role::whereNull('company_id')->get();

        foreach ($templateRoles as $template) {
            // firstOrCreate en vez de create: si ya existiera un rol con el mismo
            // nombre/guard para esta empresa (p.ej. una plantilla global duplicada),
            // evita que la excepción de índice único aborte todo el sembrado y deje
            // a la empresa sin roles.
            $newRole = \Spatie\Permission\Models\Role::firstOrCreate([
                'name' => $template->name,
                'guard_name' => $template->guard_name,
                'company_id' => $record->id,
            ]);

            // Copiar los permisos del rol plantilla
            $permissionNames = $template->permissions()->pluck('name')->toArray();
            $newRole->syncPermissions($permissionNames);
        }
    }
}

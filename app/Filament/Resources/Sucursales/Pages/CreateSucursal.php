<?php

namespace App\Filament\Resources\Sucursales\Pages;

use App\Filament\Resources\Sucursales\SucursalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSucursal extends CreateRecord
{
    protected static string $resource = SucursalResource::class;

    protected function afterCreate(): void
    {
        $sucursal = $this->record;

        // Check if the company already has a Bóveda
        $hasBoveda = \App\Models\Caja::where('company_id', $sucursal->company_id)
            ->where('is_boveda', true)
            ->exists();

        if (!$hasBoveda) {
            $boveda = \App\Models\Caja::create([
                'company_id' => $sucursal->company_id,
                'sucursal_id' => $sucursal->id,
                'nombre' => 'Caja General / Tesorería',
                'descripcion' => 'Bóveda principal de la empresa (Auto-generada)',
                'is_active' => true,
                'is_boveda' => true,
            ]);

            // Assign to users with super_admin or admin_empresa
            $users = \App\Models\User::role(['super_admin', 'admin_empresa'])
                ->where('company_id', $sucursal->company_id)
                ->get();

            if ($users->count() > 0) {
                $boveda->users()->syncWithoutDetaching($users->pluck('id'));
            }
        }
    }
}

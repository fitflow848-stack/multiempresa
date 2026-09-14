<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Concerns\HandlesSunatCertificado;
use App\Models\Caja;
use App\Models\CompanyDocument;
use App\Models\Sucursal;
use App\Services\Sunat;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EditCompany extends EditRecord
{
    use HandlesSunatCertificado;

    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $cleanData = $this->getRecord()->attributesToArray();

        $sucursales = $this->record->sucursales()
            ->withoutGlobalScopes()
            ->orderBy('id')
            ->get();

        $cleanData['sucursales_list'] = $sucursales->map(function ($sucursal) {

            $cajas = Caja::where('sucursal_id', $sucursal->id)
                ->withoutGlobalScopes()
                ->orderBy('id')
                ->get()
                ->unique(fn($c) => mb_strtolower(trim($c->nombre)))
                ->values()
                ->map(fn($caja) => [
                    'id'          => $caja->id,
                    'nombre'      => $caja->nombre,
                    'descripcion' => $caja->descripcion,
                    'is_active'   => (bool)$caja->is_active,
                ])
                ->toArray();

            $documents = CompanyDocument::where('branch_id', $sucursal->id)
                ->withoutGlobalScopes()
                ->orderBy('id')
                ->get()
                ->unique(fn($doc) => $doc->sunat_document_id . '-' . mb_strtolower(trim($doc->series)))
                ->values()
                ->map(fn($doc) => [
                    'id'                => $doc->id,
                    'sunat_document_id' => (string) $doc->sunat_document_id,
                    'series'            => $doc->series,
                    'number'            => $doc->number,
                ])
                ->toArray();

            return [
                'id'        => $sucursal->id,
                'nombre'    => $sucursal->nombre,
                'direccion' => $sucursal->direccion,
                'telefono'  => $sucursal->telefono,
                'logo'      => $sucursal->logo,
                'is_active' => (bool)$sucursal->is_active,
                'cajas_list'     => $cajas,
                'documents_list' => $documents,
            ];
        })->toArray();

        return $cleanData;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $sucursalesData = $data['sucursales_list'] ?? [];
        unset($data['sucursales_list']);

        return DB::transaction(function () use ($record, $data, $sucursalesData) {
            $record->fill($data)->save();

            $processedSucursalesIds = [];
            $nombresUsadosEnEsteEnvio = [];

            foreach ($sucursalesData as $sucursalData) {

                $sucursalId = $sucursalData['id'] ?? null;

                // 🔴 FIX CLAVE: si no viene ID pero solo hay una sucursal, reutilizarla
                if (!$sucursalId && count($sucursalesData) === 1) {
                    $sucursalId = Sucursal::where('company_id', $record->id)->value('id');
                }

                $nombreNuevo = trim($sucursalData['nombre'] ?? '');
                $nombreNormalizado = mb_strtolower($nombreNuevo);

                if ($nombreNormalizado !== '') {
                    // Dos sucursales con el mismo nombre en el mismo envío del formulario.
                    if (isset($nombresUsadosEnEsteEnvio[$nombreNormalizado])) {
                        $this->abortPorNombreDuplicado($nombreNuevo);
                    }
                    $nombresUsadosEnEsteEnvio[$nombreNormalizado] = true;

                    // La sucursal coincide (por nombre) con OTRA ya existente en la empresa.
                    $colisionConExistente = Sucursal::where('company_id', $record->id)
                        ->when($sucursalId, fn ($q) => $q->where('id', '!=', $sucursalId))
                        ->whereRaw('LOWER(TRIM(nombre)) = ?', [$nombreNormalizado])
                        ->exists();

                    if ($colisionConExistente) {
                        $this->abortPorNombreDuplicado($nombreNuevo);
                    }
                }

                if ($sucursalId) {
                    $sucursal = Sucursal::find($sucursalId);

                    if ($sucursal && $sucursal->company_id === $record->id) {
                        $sucursal->update([
                            'nombre'    => $sucursalData['nombre'] ?? $sucursal->nombre,
                            'direccion' => $sucursalData['direccion'] ?? $sucursal->direccion,
                            'telefono'  => $sucursalData['telefono'] ?? $sucursal->telefono,
                            'logo'      => $sucursalData['logo'] ?? $sucursal->logo,
                            'is_active' => array_key_exists('is_active', $sucursalData)
                                ? (bool)$sucursalData['is_active']
                                : $sucursal->is_active,
                        ]);

                        $processedSucursalesIds[] = $sucursal->id;
                    }

                } else {
                    // Solo crea si realmente no existe ninguna
                    $sucursal = Sucursal::create([
                        'company_id' => $record->id,
                        'nombre'     => $sucursalData['nombre'],
                        'direccion'  => $sucursalData['direccion'] ?? null,
                        'telefono'   => $sucursalData['telefono'] ?? null,
                        'logo'       => $sucursalData['logo'] ?? null,
                        'is_active'  => $sucursalData['is_active'] ?? true,
                    ]);

                    $sucursalId = $sucursal->id;
                    $processedSucursalesIds[] = $sucursalId;
                }

                if ($sucursalId) {
                    $this->syncCajas($sucursalId, $sucursalData['cajas_list'] ?? [], $record->id);
                    $this->syncDocuments($sucursalId, $sucursalData['documents_list'] ?? [], $record->id);
                }
            }

            // Eliminar sucursales que ya no están en el formulario. Si el envío
            // llegó sin ninguna sucursal procesada (probablemente un error, no
            // una intención real de vaciar la empresa), no borramos nada.
            $toDelete = empty($processedSucursalesIds)
                ? collect()
                : Sucursal::where('company_id', $record->id)
                    ->whereNotIn('id', $processedSucursalesIds)
                    ->get();

            foreach ($toDelete as $s) {
                try {
                    // Sucursal::deleting() se encarga de limpiar en cascada su
                    // información (ventas, compras, ingresos de almacén, etc.).
                    $s->delete();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('No se pudo eliminar la sucursal "' . $s->nombre . '"')
                        ->body($e->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();
                }
            }

            return $record;
        });
    }

    private function abortPorNombreDuplicado(string $nombre): never
    {
        Notification::make()
            ->title('Nombre de sucursal duplicado')
            ->body('Ya existe (o se repite) una sucursal llamada "' . $nombre . '" en esta empresa. Los nombres de sucursal deben ser únicos.')
            ->danger()
            ->persistent()
            ->send();

        throw new Halt();
    }

    private function syncCajas(int $sucursalId, array $cajasData, int $companyId): void
    {
        $processedIds = [];

        foreach ($cajasData as $cajaData) {

            if (!empty($cajaData['id'])) {
                $caja = Caja::find($cajaData['id']);

                if ($caja && $caja->sucursal_id === $sucursalId) {
                    $caja->update([
                        'nombre'      => $cajaData['nombre'] ?? $caja->nombre,
                        'descripcion' => $cajaData['descripcion'] ?? $caja->descripcion,
                        'is_active'   => array_key_exists('is_active', $cajaData)
                            ? (bool)$cajaData['is_active']
                            : $caja->is_active,
                    ]);

                    $processedIds[] = $caja->id;
                }

            } elseif (!empty($cajaData['nombre'])) {

                $existe = Caja::where('sucursal_id', $sucursalId)
                    ->where('nombre', $cajaData['nombre'])
                    ->first();

                if ($existe) {
                    $existe->update([
                        'descripcion' => $cajaData['descripcion'] ?? $existe->descripcion,
                        'is_active'   => $cajaData['is_active'] ?? $existe->is_active,
                    ]);

                    $processedIds[] = $existe->id;
                } else {
                    $nueva = Caja::create([
                        'sucursal_id' => $sucursalId,
                        'company_id'  => $companyId,
                        'nombre'      => $cajaData['nombre'],
                        'descripcion' => $cajaData['descripcion'] ?? null,
                        'is_active'   => $cajaData['is_active'] ?? true,
                        'is_boveda'   => false,
                    ]);

                    $processedIds[] = $nueva->id;
                }
            }
        }

        Caja::where('sucursal_id', $sucursalId)
            ->whereNotIn('id', array_filter($processedIds))
            ->where('is_boveda', false)
            ->whereDoesntHave('cierres')
            ->delete();
    }

    private function syncDocuments(int $sucursalId, array $documentsData, int $companyId): void
    {
        $processedIds = [];

        foreach ($documentsData as $docData) {

            if (!empty($docData['id'])) {
                $doc = CompanyDocument::find($docData['id']);

                if ($doc && $doc->branch_id === $sucursalId) {
                    $doc->update([
                        'sunat_document_id' => $docData['sunat_document_id'] ?? $doc->sunat_document_id,
                        'series'            => $docData['series'] ?? $doc->series,
                        'number'            => $docData['number'] ?? $doc->number,
                    ]);

                    $processedIds[] = $doc->id;
                }

            } elseif (!empty($docData['series']) && !empty($docData['sunat_document_id'])) {

                $existe = CompanyDocument::where('branch_id', $sucursalId)
                    ->where('sunat_document_id', $docData['sunat_document_id'])
                    ->where('series', $docData['series'])
                    ->first();

                if ($existe) {
                    $existe->update([
                        'number' => $docData['number'] ?? $existe->number
                    ]);

                    $processedIds[] = $existe->id;

                } else {
                    $nuevo = CompanyDocument::create([
                        'branch_id'         => $sucursalId,
                        'company_id'        => $companyId,
                        'sunat_document_id' => $docData['sunat_document_id'],
                        'series'            => $docData['series'],
                        'number'            => $docData['number'] ?? 1,
                    ]);

                    $processedIds[] = $nuevo->id;
                }
            }
        }

        CompanyDocument::where('branch_id', $sucursalId)
            ->whereNotIn('id', array_filter($processedIds))
            ->delete();
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record->cert_file && Storage::exists($record->cert_file)) {
            $this->enviarCertificadoASunat($record);
        }

        $this->fillForm();
    }
}
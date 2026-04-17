<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Caja;
use App\Models\CompanyDocument;
use App\Services\Sunat;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    /**
     * Carga los datos de la empresa y sus relaciones de forma segura y deduplicada.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 1. Carga limpia desde los atributos del modelo (evita duplicación por relaciones cargadas por Filament)
        $cleanData = $this->getRecord()->attributesToArray();

        // 2. Cargar Sucursales, Cajas y Documentos manualmente con deduplicación lógica
        $sucursales = $this->record->sucursales()
            ->withoutGlobalScopes()
            ->orderBy('id')
            ->get();

        $cleanData['sucursales_list'] = $sucursales->map(function ($sucursal) {
            // Cajas de la sucursal - Deduplicar por nombre (limpia lo que ya esté mal en la base de datos)
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

            // Documentos - Deduplicar por tipo y serie
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

    /**
     * Gestión manual de guardado para evitar duplicación y asegurar integridad.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $sucursalesData = $data['sucursales_list'] ?? [];
        unset($data['sucursales_list']);
        
        // Guardar empresa principal
        $record->fill($data)->save();

        foreach ($sucursalesData as $sucursalData) {
            $sucursalId = $sucursalData['id'] ?? null;

            if ($sucursalId) {
                $sucursal = \App\Models\Sucursal::find($sucursalId);
                if ($sucursal && $sucursal->company_id === $record->id) {
                    $sucursal->update([
                        'nombre'    => $sucursalData['nombre'] ?? $sucursal->nombre,
                        'direccion' => $sucursalData['direccion'] ?? $sucursal->direccion,
                        'telefono'  => $sucursalData['telefono'] ?? $sucursal->telefono,
                        'logo'      => $sucursalData['logo'] ?? $sucursal->logo,
                        'is_active' => array_key_exists('is_active', $sucursalData) ? (bool)$sucursalData['is_active'] : $sucursal->is_active,
                    ]);
                }
            } else {
                $sucursal = \App\Models\Sucursal::create([
                    'company_id' => $record->id,
                    'nombre'     => $sucursalData['nombre'],
                    'direccion'  => $sucursalData['direccion'] ?? null,
                    'telefono'   => $sucursalData['telefono'] ?? null,
                    'logo'       => $sucursalData['logo'] ?? null,
                    'is_active'  => $sucursalData['is_active'] ?? true,
                ]);
                $sucursalId = $sucursal->id;
            }

            if ($sucursalId) {
                $this->syncCajas($sucursalId, $sucursalData['cajas_list'] ?? [], $record->id);
                $this->syncDocuments($sucursalId, $sucursalData['documents_list'] ?? [], $record->id);
            }
        }

        return $record;
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
                        'is_active'   => array_key_exists('is_active', $cajaData) ? (bool)$cajaData['is_active'] : $caja->is_active,
                    ]);
                    $processedIds[] = $caja->id;
                }
            } elseif (!empty($cajaData['nombre'])) {
                // IDEMPOTENCIA
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

        // Limpiar lo que no esté en el form (deduplicación definitiva en BD)
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
                    $existe->update(['number' => $docData['number'] ?? $existe->number]);
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

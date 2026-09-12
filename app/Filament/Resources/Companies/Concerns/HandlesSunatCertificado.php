<?php

namespace App\Filament\Resources\Companies\Concerns;

use App\Models\Company;
use App\Services\Sunat;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

trait HandlesSunatCertificado
{
    /**
     * Envía el certificado digital de la empresa al API de SUNAT.
     *
     * Si el archivo subido es un .p12/.pfx, primero lo convierte a .pem
     * (equivalente a `openssl pkcs12 -in x.p12 -out x.pem -nodes`) usando
     * la contraseña del certificado, y guarda el .pem resultante junto al
     * original para no tener que repetir la conversión manualmente.
     */
    protected function enviarCertificadoASunat(Company $record): void
    {
        $rawContent = Storage::get($record->cert_file);
        $extension = strtolower(pathinfo($record->cert_file, PATHINFO_EXTENSION));

        $contenidoParaEnviar = $rawContent;

        if (in_array($extension, ['p12', 'pfx'])) {
            if (!$record->cert_password) {
                Notification::make()
                    ->title('Falta la contraseña del certificado')
                    ->body('Subiste un archivo .p12/.pfx pero no indicaste su contraseña; no se pudo convertir a .pem ni enviar a SUNAT.')
                    ->danger()
                    ->send();
                return;
            }

            $sunatService = app(Sunat::class);
            $pemContent = $sunatService->convertirP12APem($rawContent, $record->cert_password);

            if ($pemContent === null) {
                Notification::make()
                    ->title('No se pudo convertir el certificado')
                    ->body('Verifique que la contraseña del .p12/.pfx sea correcta.')
                    ->danger()
                    ->send();
                return;
            }

            $pemPath = preg_replace('/\.(p12|pfx)$/i', '.pem', $record->cert_file);
            Storage::put($pemPath, $pemContent);

            $contenidoParaEnviar = $pemContent;
        }

        $sunatService = app(Sunat::class);
        $sunatService->guardarCertificado($record->ruc, base64_encode($contenidoParaEnviar));

        Notification::make()
            ->title('Certificado enviado correctamente al API')
            ->success()
            ->send();
    }
}

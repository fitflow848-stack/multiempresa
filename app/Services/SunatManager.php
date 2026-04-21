<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\VentaSunat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * SunatManager: Orquesta la generación del XML, guardado y envío a SUNAT.
 * Usa internamente el cliente App\Services\Sunat (que ya tienes).
 */
class SunatManager
{
    protected Sunat $sunatClient;

    public function __construct(Sunat $sunatClient)
    {
        $this->sunatClient = $sunatClient;
    }

    /**
     * Genera el XML para la venta, guarda el XML en storage y crea/actualiza VentaSunat.
     * Retorna el array con los datos devueltos por el cliente (nombre_archivo, contenido_xml, hash, qr_info).
     *
     * Lanza Exception en caso de error.
     */
    /**
     * Procesar generación de XML para SUNAT
     */
    public function procesarXmlSunat(object $venta)
    {
        // Generar xml y guardar en VentaSunat (como estaba)
        $response = $this->generarXmlSunat($venta->id_venta);
        $responseArray = json_decode($response, true);
        if (isset($responseArray['estado']) && $responseArray['estado'] && isset($responseArray['data'])) {
            $data = $responseArray['data'];

            // Guardar XML en storage/app/public/xml_sunat (accesible vía /storage/xml_sunat/<file> si ejecutas storage:link)
            $folder = 'xml_sunat';
            $fileName = ($data['nombre_archivo'] ?? 'document') . '.xml';
            $storagePath = $folder . '/' . $fileName;

            // Asegurar que la carpeta exista en disk 'public'
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            // Preparar contenido XML y normalizar codificación a UTF-8 si es necesario
            $xmlContent = $data['contenido_xml'] ?? '';
            if (!mb_check_encoding($xmlContent, 'UTF-8')) {
                // si viene en otra codificación, convertir a UTF-8
                $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'ISO-8859-1');
            }

            try {
                // Guardar en disk 'public'
                Storage::disk('public')->put($storagePath, $xmlContent);
                Storage::disk('public')->setVisibility($storagePath, 'public');

                // Actualizar/crear registro VentaSunat con ruta relativa al storage
                VentaSunat::updateOrCreate(
                    ['id_venta' => $venta->id_venta],
                    [
                        'id_venta'   => $venta->id_venta,
                        'hash'       => $data['hash'] ?? '-',
                        'nombre_xml' => $data['nombre_archivo'] ?? null,
                        'qr_data'    => $data['qr_info'] ?? null,
                        'content_xml' => $xmlContent ?? null,
                        // campos opcionales para referencia local
                        'xml_path'   => $storagePath,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error("No fue posible guardar XML en storage para venta {$venta->id_venta}: " . $e->getMessage());
                // Como fallback, intentar guardar en public_path (antiguo comportamiento)
                try {
                    $folderPath = public_path('xml_sunat');
                    if (!file_exists($folderPath)) mkdir($folderPath, 0777, true);
                    file_put_contents($folderPath . '/' . $fileName, $xmlContent);
                    VentaSunat::updateOrCreate(
                        ['id_venta' => $venta->id_venta],
                        [
                            'id_venta'   => $venta->id_venta,
                            'hash'       => $data['hash'] ?? '-',
                            'nombre_xml' => $data['nombre_archivo'] ?? null,
                            'qr_data'    => $data['qr_info'] ?? null,
                            'content_xml' => $xmlContent ?? null,
                            'xml_path'   => 'public/xml_sunat/' . $fileName,
                        ]
                    );
                } catch (\Throwable $e2) {
                    Log::error("Fallback: tampoco se pudo guardar XML en public_path para venta {$venta->id_venta}: " . $e2->getMessage());
                }
            }
        } else {
            Log::error("generarXmlSunat failed for venta {$venta->id_venta}", ['response' => $response]);
        }
    }

    public function generarXmlSunat($id)
    {
        $venta = Venta::where('id_venta', $id)->first();
        if (!$venta) {
            return response()->json(['error' => 'Venta no encontrada.'], 404);
        }

        $cliente = Cliente::withoutGlobalScopes()->find($venta->id_cliente);
        $productos = VentaDetalle::where('id_venta', $venta->id_venta)->get();
        // Usar la función que arma el JSON con cuotas cuando corresponda
        $json  = $this->sunatClient->formatJsonXml($venta, $cliente, $productos, 0);
        $response = $this->sunatClient->getXmlSunat($json);
        return $response;
    }

    /**
     * Envía el XML guardado a SUNAT (obtiene VentaSunat, construye JSON y usa el cliente).
     * Guarda el CDR resultante en storage y actualiza Venta y VentaSunat.
     *
     * Devuelve array con resumen (nombre_cdr, storage_path, public_url).
     */
    public function enviarDocumento(Venta $venta): array
    {
        if ($venta->id_tido == 4) {
            throw new Exception('Los tickets no se envían a SUNAT.');
        }

        $ventaSunat = VentaSunat::where('id_venta', $venta->id_venta)->first();
        if (!$ventaSunat || empty($ventaSunat->nombre_xml) || empty($ventaSunat->content_xml)) {
            throw new Exception("Falta XML para la venta {$venta->id_venta}");
        }

        $json = $this->sunatClient->formatJsonFacturaBoleta($ventaSunat->nombre_xml, $ventaSunat->content_xml, $venta->id_empresa);
        $response = $this->sunatClient->sendDocumentoBoletaFactura($json);
        $data = json_decode($response);

        if (!$data || empty($data->nombre) || empty($data->cdr)) {
            $msg = $data->message ?? "Respuesta SUNAT inválida";
            $this->logAttempt($venta, 'error', $msg, $data);
            Log::error("Respuesta SUNAT inválida para venta {$venta->id_venta}", ['response' => $response]);
            throw new Exception("Respuesta SUNAT inválida: " . $msg);
        }

        $cdrRaw = $data->cdr;
        $cdrBinary = (is_string($cdrRaw) && base64_decode($cdrRaw, true) !== false) ? base64_decode($cdrRaw) : (string)$cdrRaw;

        $cdrFolder = 'cdrs';
        if (!Storage::disk('public')->exists($cdrFolder)) {
            Storage::disk('public')->makeDirectory($cdrFolder);
        }
        $fileName = $data->nombre;
        $storagePath = $cdrFolder . '/' . $fileName;
        Storage::disk('public')->put($storagePath, $cdrBinary);
        Storage::disk('public')->setVisibility($storagePath, 'public');

        // Marcar venta como enviada y actualizar VentaSunat
        $venta->update(['enviado_sunat' => 1]);
        $ventaSunat->update([
            'cdr_nombre' => $fileName,
            'cdr_path' => $storagePath,
        ]);

        $publicUrl = Storage::disk('public')->exists($storagePath) ? asset('storage/' . $storagePath) : null;

        $this->logAttempt($venta, 'success', 'Enviado correctamente', $data);

        return [
            'nombre_cdr' => $fileName,
            'storage_path' => $storagePath,
            'public_url' => $publicUrl,
        ];
    }

    /**
     * Procesa todas las ventas pendientes (opcional para cron). Devuelve resumen.
     */
    public function procesarPendientes(): array
    {
        $result = ['processed' => [], 'failed' => []];
        $ventas = Venta::withoutGlobalScopes()
            ->where('enviado_sunat', 0)
            ->where('estado', 1)
            ->whereIn('id_tido', [1, 2]) // 1: Boleta, 2: Factura
            ->where('fecha_emision', '>=', now()->subDays(2)->startOfDay())
            ->get();

        foreach ($ventas as $venta) {
            try {
                $this->enviarDocumento($venta);
                $result['processed'][] = $venta->id_venta;
            } catch (\Throwable $e) {
                $this->logAttempt($venta, 'error', $e->getMessage());
                Log::error("Error procesando pendiente venta {$venta->id_venta}: " . $e->getMessage());
                $result['failed'][] = ['id' => $venta->id_venta, 'error' => $e->getMessage()];
            }
        }

        return $result;
    }
    public function logAttempt(Venta $venta, string $status, ?string $message = null, $responseData = null)
    {
        try {
            $type = 'sale';
            if ($venta->id_tido == 5) $type = 'nc';
            
            \App\Models\SunatLog::create([
                'venta_id'               => $venta->id_venta,
                'documento_identificador' => $venta->serie . '-' . str_pad($venta->numero, 8, '0', STR_PAD_LEFT),
                'status'                 => $status,
                'message'                => $message,
                'response_data'          => $responseData,
                'type'                   => $type,
            ]);
        } catch (\Throwable $e) {
            Log::error("Error saving SunatLog: " . $e->getMessage());
        }
    }
}

<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Cliente;
use App\Models\Company;
use App\Models\VentaSunat;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfVentaService
{
    /**
     * Prepara y retorna todos los datos comunes para generar PDFs.
     * @param int $id
     * @param int $qrSize tamaño del QR (px)
     * @return array|null (venta, servicios, cliente, empresa, logo, qr_image, qr_hash, serie_numero, tipoDocumento, qr_text, ventaSunat)
     */
    private function prepareVentaData(int $id, int $qrSize = 200): ?array
    {
        $venta = Venta::with(['deuda', 'user', 'tipoPago', 'sucursal_ref'])->where('id_venta', $id)->first();
        if (!$venta) return null;

        $servicios = VentaDetalle::where('id_venta', $id)->ordenado()->get();

        // Cliente por defecto (VARIOS) o real
        if ($venta->id_cliente == 999999) {
            $cliente = (object)[
                'tipo_documento' => 'DNI',
                'numero_documento' => '99999999',
                'nombre' => 'CLIENTE VARIOS',
                'direccion' => 'SIN DIRECCION',
                'telefono' => '',
                'email' => ''
            ];
        } else {
            $cliente = Cliente::where('id', $venta->id_cliente)->first();
        }

        $empresa = Company::where('id', $venta->id_empresa)->first();
        $sucursal = $venta->sucursal_ref;

        // Obtener logo en base64 (sucursal, empresa o default)
        $logoBase64 = null;

        // 1. Prioridad: Logo de la sucursal
        if ($sucursal && $sucursal->logo) {
            $logoFilePath = $sucursal->logo_path ?? null;
            if ($logoFilePath && file_exists($logoFilePath)) {
                $logoBase64 = base64_encode(file_get_contents($logoFilePath));
            }
        }

        // 2. Si no hay logo de sucursal, usar logo de la empresa
        if (!$logoBase64 && $empresa && $empresa->logo) {
            $logoFilePath = $empresa->logo_path ?? null;
            if ($logoFilePath && file_exists($logoFilePath)) {
                $logoBase64 = base64_encode(file_get_contents($logoFilePath));
            }
        }
        if (!$logoBase64) {
            $defaultLogoPath = public_path('images/scorpion.png');
            if (file_exists($defaultLogoPath)) {
                $logoBase64 = base64_encode(file_get_contents($defaultLogoPath));
            }
        }

        $serie_numero = ($venta->serie ?? 'F001') . '-' . $this->agregarCerosIzquierda($venta->numero ?? 1, 4);
        $tipoDocumento = match ($venta->id_tido) {
            1 => 'Boleta',
            2 => 'Factura',
            3 => 'Nota de Venta',
            4 => 'Ticket',
            5 => 'Nota de Credito',
            default => 'Boleta',
        };

        // Preparar texto QR (reemplaza RUC por el de tu empresa si está en $empresa)
        $rucEmpresa = $empresa->ruc ?? '20489629551';
        $qr_text = "{$rucEmpresa}|01|{$serie_numero}|{$venta->total}|{$venta->total}|{$venta->fecha_emision}|6|" . ($cliente->numero_documento ?? '');

        // Buscar/crear VentaSunat y actualizar QR si aplica
        $ventaSunat = VentaSunat::where('id_venta', $venta->id_venta)->first();
        if (!$ventaSunat) {
            VentaSunat::create([
                'id_venta' => $venta->id_venta,
                'hash' => hash('sha1', $qr_text),
                'qr_data' => $qr_text
            ]);
            $ventaSunat = VentaSunat::where('id_venta', $venta->id_venta)->first();
        }

        $qr_image = null;
        $qr_hash = null;
        try {
            $svg = QrCode::format('svg')->size($qrSize)->generate($qr_text);
            $qr_image = 'data:image/svg+xml;base64,' . base64_encode($svg);
            $qr_hash = hash('sha1', $qr_text);

            if ($ventaSunat) {
                try {
                    $ventaSunat->qr_data = $qr_image;
                    $ventaSunat->hash = $qr_hash;
                    $ventaSunat->save();
                } catch (\Throwable $e) {
                    Log::debug("No se pudo actualizar ventaSunat con qr para venta {$venta->id_venta}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error generando QR SVG para venta {$venta->id_venta}: " . $e->getMessage());
        }

        return [
            'venta' => $venta,
            'servicios' => $servicios,
            'cliente' => $cliente,
            'empresa' => $empresa,
            'logo' => $logoBase64,
            'qr_image' => $qr_image,
            'qr_hash' => $qr_hash,
            'serie_numero' => $serie_numero,
            'tipoDocumento' => $tipoDocumento,
            'qr_text' => $qr_text,
            'ventaSunat' => $ventaSunat,
        ];
    }

    /**
     * Método unificado para emitir PDF de venta.
     * @param int $id
     * @param string $format 'default' (A4) o '8cm'
     * @param bool $saveOnly si true guarda el pdf en public/ventas/pdf y retorna la ruta
     */
    public function pdfVenta(int $id, string $format = 'default', bool $saveOnly = false, bool $autoPrint = false)
    {
        $qrSize = in_array($format, ['8cm', '5.8cm']) ? 150 : 200;
        $data = $this->prepareVentaData($id, $qrSize);

        if (!$data || !$data['venta']) {
            abort(404, 'Venta no encontrada');
        }

        if ($format === '8cm' || $format === '5.8cm') {
            $viewData = [
                'empresa' => $data['empresa'],
                'venta' => $data['venta'],
                'servicios' => $data['servicios'],
                'cliente' => $data['cliente'],
                'logo' => $data['logo'] ? 'data:image/png;base64,' . $data['logo'] : null,
                'qr_image' => $data['qr_image'],
            ];

            $cantidadItems = count($data['servicios']);
            $altoCalculado = 550 + ($cantidadItems * 30);
            
            // 80mm = 226.77pt, 58mm = 164.4pt
            // Ajustamos ligeramente hacia abajo (215pt ≈ 76mm) para dar margen seguro
            $width = ($format === '5.8cm') ? 158 : 215;
            $customPaper = [0, 0, $width, $altoCalculado];

            $pdf = Pdf::loadView('pos.pdf_8cm', $viewData)
                ->setPaper($customPaper, 'portrait');

            $fileName = ($format === '5.8cm' ? 'ticket-58mm-' : 'ticket-80mm-') . $data['serie_numero'] . '.pdf';
        } else {
            $viewData = [
                'title' => 'Boleta de Pago',
                'date' => date('m/d/Y'),
                'logo' => $data['logo'] ? 'data:image/png;base64,' . $data['logo'] : null,
                'cliente' => $data['cliente'],
                'servicios' => $data['servicios'],
                'venta' => $data['venta'],
                'qr_image' => $data['qr_image'],
                'qr_hash' => $data['qr_hash'],
                'tipoDocumento' => $data['tipoDocumento'],
                'empresa' => $data['empresa'],
            ];

            $pdf = Pdf::loadView('template.documentoventa', $viewData);
            
            if ($format === 'media-a4') {
                // Media hoja A4 = A5 landscape (210mm x 148.5mm)
                $pdf->setPaper([0, 0, 595.28, 420.94], 'portrait');
                $fileName = 'comprobante-media-a4-' . $data['serie_numero'] . '.pdf';
            } else {
                $pdf->setPaper('a4', 'portrait');
                $fileName = 'comprobante-a4-' . $data['serie_numero'] . '.pdf';
            }
        }

        if ($saveOnly) {
            $dir = public_path('ventas/pdf');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $pdfPath = $dir . "/venta_{$id}.pdf";
            $pdf->save($pdfPath);
            return $pdfPath;
        }

        // El auto-print ahora se maneja desde el frontend con Print.js para mayor compatibilidad
        return $pdf->stream($fileName);
    }

    /**
     * Si no existe la función global, mantenemos una aquí.
     */
    private function agregarCerosIzquierda($num, $length = 4): string
    {
        return str_pad((string)$num, $length, '0', STR_PAD_LEFT);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\GuiaDestinatario;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionProducto;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfController extends Controller
{
    public function guia_remision_pdf($id)
    {
        $guia = GuiaRemision::with(['departamentoPartida', 'provinciaPartida', 'distritoPartida', 'departamentoLlegada', 'provinciaLlegada', 'distritoLlegada'])->find($id);
        $productos = GuiaRemisionProducto::where('id_guia', $guia->id)->get();
        $destinatarios = GuiaDestinatario::where('id_guia', $guia->id)->get();
        $url = env('APP_URL') . '/guia/remision/' . $guia->id;
        $user = Auth::user();
        $empresa = Company::where('id', $user->company_id)->first();

        // Generar código QR como PNG
        $image = QrCode::format('png')
            ->size(150)
            ->margin(1)
            ->generate($url);

        $qr_image = 'data:image/png;base64,' . base64_encode($image);

        // Construir bloque de destinatarios
        $destinatariosHtml = '';
        foreach ($destinatarios as $destinatario) {
            $destinatariosHtml .= $destinatario->datos . ' - DNI: ' . $destinatario->documento . '.<br>';
        }

        // Obtener logo en base64 (empresa o default)
        $logoBase64 = null;
        if ($empresa && $empresa->logo) {
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

        $data = [
            'guia' => $guia,
            'productos' => $productos,
            // Cambiado: ahora la vista recibirá $qr_image
            'qr_image' => $qr_image,
            'destinatarios' => $destinatarios,
            'destinatariosHtml' => $destinatariosHtml,
            'empresa' => $empresa,
            'logo' => $logoBase64,
        ];

        $html = view('pdf.guia_remision', $data)->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('guia_remision.pdf', ['Attachment' => false]);
    }
}
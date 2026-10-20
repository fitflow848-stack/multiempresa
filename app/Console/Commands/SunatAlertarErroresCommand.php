<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\SunatManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SunatAlertarErroresCommand extends Command
{
    protected $signature = 'sunat:alertar-errores';

    protected $description = 'Envía por correo la lista de comprobantes que no llegaron a SUNAT o tienen error';

    public function handle(SunatManager $manager)
    {
        $ventas = $manager->pendientesConError()->get()->groupBy('id_empresa');
        $this->info('Empresas con comprobantes por revisar: ' . $ventas->count());

        foreach ($ventas as $idEmpresa => $lista) {
            $empresa = Company::withoutGlobalScopes()->find($idEmpresa);
            if (!$empresa) {
                continue;
            }
            $destino = $empresa->rep_email ?: $empresa->email;
            $lineas = $lista->take(40)->map(function ($v) use ($manager) {
                return "- {$v->serie}-{$v->numero} ({$v->fecha_emision}): " . ($manager->ultimoError($v->id_venta) ?? 'sin enviar');
            })->implode("\n") . ($lista->count() > 40 ? "\n... y " . ($lista->count() - 40) . ' más' : '');

            $texto = "Estos comprobantes de {$empresa->razon_social} no llegaron a SUNAT y necesitan revisión:\n\n{$lineas}\n\n"
                . 'El sistema los reintenta automáticamente cada día; si el error persiste, revíselos en Comprobantes.';

            if (!$destino || !filter_var($destino, FILTER_VALIDATE_EMAIL)) {
                Log::warning("Alerta SUNAT sin correo de destino para la empresa {$idEmpresa}", ['detalle' => $lineas]);
                $this->warn("Empresa {$idEmpresa}: sin correo configurado");
                continue;
            }

            try {
                Mail::raw($texto, fn($m) => $m->to($destino)->subject('Comprobantes pendientes de envío a SUNAT'));
                $this->info("Empresa {$idEmpresa}: aviso enviado a {$destino} ({$lista->count()} comprobantes)");
            } catch (\Throwable $e) {
                Log::error("No se pudo enviar la alerta SUNAT a {$destino}: " . $e->getMessage());
                $this->error("Empresa {$idEmpresa}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}

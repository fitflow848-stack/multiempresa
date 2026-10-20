<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Venta;
use App\Services\Sunat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SunatConsultarBajasCommand extends Command
{
    protected $signature = 'sunat:consultar-bajas';

    protected $description = 'Consulta en SUNAT el resultado de los comunicados de baja pendientes y lo deja en la observación de la venta';

    public function handle(Sunat $sunat)
    {
        $ventas = Venta::withoutGlobalScopes()
            ->where('estado', 0)
            ->where('observacion', 'like', '%COMUNICADO DE BAJA SUNAT:%')
            ->where('observacion', 'not like', '%BAJA ACEPTADA%')
            ->where('observacion', 'not like', '%BAJA RECHAZADA%')
            ->where('updated_at', '>=', now()->subDays(15))
            ->get();

        $this->info('Bajas pendientes de consulta: ' . $ventas->count());

        foreach ($ventas as $venta) {
            if (!preg_match('/COMUNICADO DE BAJA SUNAT: ([^\]\s]+)/', (string) $venta->observacion, $m)) {
                continue;
            }
            $ticket = $m[1];
            $empresa = Company::withoutGlobalScopes()->find($venta->id_empresa);
            if (!$empresa) {
                continue;
            }

            try {
                $respuesta = json_decode($sunat->consultarBaja($ticket, [
                    'endpoint' => $empresa->sunat_produccion ? 'produccion' : 'beta',
                    'ruc' => $empresa->ruc,
                    'usuario' => $empresa->sol_user,
                    'clave' => $empresa->sol_password,
                ]), true);

                if (empty($respuesta['procesado'])) {
                    $this->line("{$venta->serie}-{$venta->numero}: SUNAT aún procesa el ticket {$ticket}");
                    continue;
                }

                $marca = !empty($respuesta['estado'])
                    ? ' [BAJA ACEPTADA]'
                    : ' [BAJA RECHAZADA: ' . mb_substr((string) ($respuesta['mensaje'] ?? 'sin detalle'), 0, 60) . ']';

                $venta->observacion = mb_substr(trim($venta->observacion . $marca), 0, 220);
                $venta->save();

                if (empty($respuesta['estado'])) {
                    Log::error("Baja rechazada por SUNAT para {$venta->serie}-{$venta->numero}", ['respuesta' => $respuesta]);
                    $this->error("{$venta->serie}-{$venta->numero}: baja RECHAZADA");
                } else {
                    $this->info("{$venta->serie}-{$venta->numero}: baja aceptada");
                }
            } catch (\Throwable $e) {
                Log::error("Error consultando baja {$ticket}: " . $e->getMessage());
                $this->error("{$venta->serie}-{$venta->numero}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}

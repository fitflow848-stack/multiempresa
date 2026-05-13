<?php

namespace App\Helpers;

use App\Models\TipoActivo;
use App\Models\TipoActivoCorriente;
use App\Models\TipoPasivo;
use App\Models\ActivoCorriente;
use App\Models\Pasivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingHelper
{
    /**
     * Asegura que una empresa tenga los tipos de activos y pasivos por defecto.
     */
    public static function ensureDefaults(int $companyId)
    {
        // 1. ACTIVO CORRIENTE
        $activosCorrientes = [
            'Bancos' => ['descripcion' => 'Cuentas bancarias de la empresa', 'afecta_caja' => true],
            'Anticipo a Proveedores' => ['descripcion' => 'Pagos realizados por adelantado a proveedores', 'afecta_caja' => true],
            'Adelantos a Personal' => ['descripcion' => 'Adelantos de sueldo entregados al personal', 'afecta_caja' => true],
            'Otros' => ['descripcion' => 'Otros activos corrientes no especificados', 'afecta_caja' => false]
        ];

        foreach ($activosCorrientes as $nombre => $config) {
            TipoActivoCorriente::firstOrCreate(
                ['company_id' => $companyId, 'nombre' => $nombre],
                ['descripcion' => $config['descripcion'], 'afecta_caja' => $config['afecta_caja']]
            );
        }

        // 2. ACTIVO NO CORRIENTE (TipoActivo model)
        $activosNoCorrientes = [
            'Activo Fijo' => 'Bienes muebles e inmuebles residenciales o de oficina',
            'Intangibles' => 'Software, patentes, marcas y otros intangibles',
            'Detracciones' => 'Depósito de detracciones ante la SUNAT',
            'Otros' => 'Otros activos no corrientes'
        ];

        foreach ($activosNoCorrientes as $nombre => $descripcion) {
            TipoActivo::firstOrCreate(
                ['company_id' => $companyId, 'nombre' => $nombre],
                ['descripcion' => $descripcion]
            );
        }

        // 3. PASIVO CORRIENTE
        $pasivosCorrientes = [
            'Compras a Crédito' => 'Deudas con proveedores por compras a crédito',
            'Adelanto de Clientes' => 'Pagos adelantados por servicios o productos no entregados',
            'Deuda Bancos' => 'Préstamos y obligaciones bancarias a corto plazo',
            'Cxp Terceros' => 'Cuentas por pagar a terceros',
            'Aporte' => 'Aporte de capital o socios',
            'Beneficio' => 'Beneficios sociales por pagar',
            'Impuestos o Renta' => 'Obligaciones tributarias pendientes',
            'Otros' => 'Otros pasivos corrientes'
        ];

        foreach ($pasivosCorrientes as $nombre => $descripcion) {
            TipoPasivo::firstOrCreate(
                ['company_id' => $companyId, 'nombre' => $nombre],
                ['descripcion' => $descripcion]
            );
        }

        // 4. LIMPIEZA: Mover "Adelantos Personal" que se registraron como Pasivos
        self::movePersonalAdvancesToAssets($companyId);
    }

    /**
     * Mueve los registros de adelantos a personal de la tabla Pasivos a Activos Corrientes
     */
    private static function movePersonalAdvancesToAssets(int $companyId)
    {
        // Buscar tipos en pasivos que sugieran adelantos personal (usamos query directo para evitar scopes si es necesario)
        $tiposPasivosErroneos = TipoPasivo::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('nombre', 'like', '%adelanto%personal%')
                    ->orWhere('nombre', 'like', '%adelantos%personal%');
            })->get();

        if ($tiposPasivosErroneos->isEmpty()) return;

        // Obtener o crear el tipo correcto en activos
        $tipoCorrecto = TipoActivoCorriente::withoutGlobalScopes()->firstOrCreate(
            ['company_id' => $companyId, 'nombre' => 'Adelantos a Personal'],
            ['descripcion' => 'Adelantos de sueldo entregados al personal']
        );

        foreach ($tiposPasivosErroneos as $tPasivo) {
            DB::transaction(function () use ($tPasivo, $tipoCorrecto) {
                // Obtener pasivos ignorando scopes para asegurar limpieza total
                $pasivos = Pasivo::withoutGlobalScopes()->where('tipo_pasivo_id', $tPasivo->id)->get();

                foreach ($pasivos as $pasivo) {
                    // Solo crear el activo si el pasivo tiene monto pendiente
                    $montoPendiente = $pasivo->monto - ($pasivo->monto_pagado ?? 0);
                    
                    if ($montoPendiente > 0 || !$pasivo->is_settled) {
                        ActivoCorriente::create([
                            'company_id' => $pasivo->company_id,
                            'sucursal_id' => $pasivo->sucursal_id,
                            'tipo_activo_corriente_id' => $tipoCorrecto->id,
                            'nombre' => $pasivo->empresa_persona,
                            'monto' => $montoPendiente,
                            'fecha_registro' => $pasivo->fecha_registro,
                            'documento' => $pasivo->documento,
                            'observaciones' => $pasivo->nombre,
                            'user_id' => $pasivo->user_id,
                            'is_settled' => ($pasivo->estado === 'pagado' || $pasivo->is_settled),
                            'tipo_adelanto' => 'personal'
                        ]);
                    }

                    // Limpiar pagos asociados para evitar fallos de FK al borrar el pasivo
                    DB::table('pasivo_pagos')->where('pasivo_id', $pasivo->id)->delete();
                    
                    // Eliminar el pasivo original
                    $pasivo->delete();
                }

                // Verificar si todavía existen pasivos asociados (por si acaso hubo inconsistencia de datos)
                $count = Pasivo::withoutGlobalScopes()->where('tipo_pasivo_id', $tPasivo->id)->count();
                if ($count === 0) {
                    $tPasivo->delete();
                }
            });
        }
    }
}

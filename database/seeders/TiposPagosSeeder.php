<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoPago;

class TiposPagosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposPago = [
            // EFECTIVO
            [
                'nombre' => 'Efectivo',
                'codigo' => 'EFE',
                'descripcion' => 'Pago en efectivo (billetes y monedas)',
                'icono' => '💵',
                'color' => '#28a745',
                'activo' => true,
                'requiere_referencia' => false,
                'es_digital' => false,
                'es_efectivo' => true,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 1
            ],

            // BILLETERAS DIGITALES
            [
                'nombre' => 'Yape',
                'codigo' => 'YAP',
                'descripcion' => 'Pago con Yape del BCP',
                'icono' => '📱',
                'color' => '#722F8C',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 2
            ],
            [
                'nombre' => 'Plin',
                'codigo' => 'PLN',
                'descripcion' => 'Pago con Plin de Interbank',
                'icono' => '📱',
                'color' => '#FF6B35',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 3
            ],
            [
                'nombre' => 'Tunki',
                'codigo' => 'TNK',
                'descripcion' => 'Pago con Tunki del Banco de la Nación',
                'icono' => '📱',
                'color' => '#1E88E5',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 4
            ],
            [
                'nombre' => 'Lukita',
                'codigo' => 'LUK',
                'descripcion' => 'Pago con Lukita del Banco de Crédito',
                'icono' => '📱',
                'color' => '#E91E63',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 5
            ],

            // TRANSFERENCIAS BANCARIAS
            [
                'nombre' => 'Transferencia BCP',
                'codigo' => 'TRA_BCP',
                'descripcion' => 'Transferencia bancaria del Banco de Crédito del Perú',
                'icono' => '🏦',
                'color' => '#003366',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 6
            ],
            [
                'nombre' => 'Transferencia Interbank',
                'codigo' => 'TRA_IBK',
                'descripcion' => 'Transferencia bancaria de Interbank',
                'icono' => '🏦',
                'color' => '#FF6B35',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 7
            ],
            [
                'nombre' => 'Transferencia BBVA',
                'codigo' => 'TRA_BBVA',
                'descripcion' => 'Transferencia bancaria del BBVA',
                'icono' => '🏦',
                'color' => '#004481',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 8
            ],
            [
                'nombre' => 'Transferencia Scotiabank',
                'codigo' => 'TRA_SCO',
                'descripcion' => 'Transferencia bancaria de Scotiabank',
                'icono' => '🏦',
                'color' => '#C8102E',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 9
            ],
            [
                'nombre' => 'Transferencia Banco de la Nación',
                'codigo' => 'TRA_BN',
                'descripcion' => 'Transferencia del Banco de la Nación',
                'icono' => '🏦',
                'color' => '#1E88E5',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 10
            ],
            [
                'nombre' => 'Transferencia CCI',
                'codigo' => 'TRA_CCI',
                'descripcion' => 'Transferencia interbancaria (CCI)',
                'icono' => '🏦',
                'color' => '#6c757d',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 11
            ],

            // TARJETAS DE DÉBITO
            [
                'nombre' => 'Tarjeta de Débito Visa',
                'codigo' => 'TD_VISA',
                'descripcion' => 'Pago con tarjeta de débito Visa',
                'icono' => '💳',
                'color' => '#1A1F71',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 3.5,
                'comision_fija' => 0,
                'orden' => 12
            ],
            [
                'nombre' => 'Tarjeta de Débito Mastercard',
                'codigo' => 'TD_MAST',
                'descripcion' => 'Pago con tarjeta de débito Mastercard',
                'icono' => '💳',
                'color' => '#EB001B',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 3.5,
                'comision_fija' => 0,
                'orden' => 13
            ],

            // TARJETAS DE CRÉDITO
            [
                'nombre' => 'Tarjeta de Crédito Visa',
                'codigo' => 'TC_VISA',
                'descripcion' => 'Pago con tarjeta de crédito Visa',
                'icono' => '💳',
                'color' => '#1A1F71',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 4.5,
                'comision_fija' => 0,
                'orden' => 14
            ],
            [
                'nombre' => 'Tarjeta de Crédito Mastercard',
                'codigo' => 'TC_MAST',
                'descripcion' => 'Pago con tarjeta de crédito Mastercard',
                'icono' => '💳',
                'color' => '#EB001B',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 4.5,
                'comision_fija' => 0,
                'orden' => 15
            ],

            // OTROS MÉTODOS
            [
                'nombre' => 'PayPal',
                'codigo' => 'PAYPAL',
                'descripcion' => 'Pago con PayPal',
                'icono' => '💰',
                'color' => '#0070ba',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 5.4,
                'comision_fija' => 0.30,
                'orden' => 16
            ],
            [
                'nombre' => 'PagoEfectivo',
                'codigo' => 'PAGEF',
                'descripcion' => 'Pago a través de PagoEfectivo',
                'icono' => '🏪',
                'color' => '#FF9500',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => true,
                'es_efectivo' => false,
                'comision_porcentaje' => 2.95,
                'comision_fija' => 0,
                'orden' => 17
            ],
            [
                'nombre' => 'Depósito Bancario',
                'codigo' => 'DEP',
                'descripcion' => 'Depósito directo en cuenta bancaria',
                'icono' => '🏦',
                'color' => '#17a2b8',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => false,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 18
            ],
            [
                'nombre' => 'Cheque',
                'codigo' => 'CHQ',
                'descripcion' => 'Pago con cheque',
                'icono' => '📄',
                'color' => '#6f42c1',
                'activo' => true,
                'requiere_referencia' => true,
                'es_digital' => false,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 19
            ],
            [
                'nombre' => 'Pago Mixto',
                'codigo' => 'MULTI',
                'descripcion' => 'Combinación de múltiples métodos de pago (efectivo + digital)',
                'icono' => '🔄',
                'color' => '#fd7e14',
                'activo' => true,
                'requiere_referencia' => false,
                'es_digital' => false,
                'es_efectivo' => false,
                'comision_porcentaje' => 0,
                'comision_fija' => 0,
                'orden' => 20
            ]
        ];

        foreach ($tiposPago as $tipo) {
            TipoPago::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}

<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

use App\Traits\BelongsToSucursal;
use Illuminate\Support\Facades\DB;

class CierreCaja extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;

    

    protected $companyForeignKey = 'id_empresa';
    protected $sucursalForeignKey = 'sucursal_id';
    
    protected $table = 'cierre_cajas';

    protected $fillable = [
        'user_id',
        'caja_id',
        'id_empresa',
        'sucursal_id',
        'fecha_cierre',
        'monto_apertura', // Saldo Inicial
        'monto_cierre',   // Cierre Caja (Efectivo Real)
        'ingresos',
        'egresos',        // Gastos
        'aportaciones',   // Nuevo: Dinero extra ingresado
        'sustracciones',  // Nuevo: Retiros de caja
        'observaciones',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
    ];

    /**
     * Relación con el usuario que realiza el cierre
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la caja
     */
    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    /**
     * Relación con el desglose del arqueo físico
     */
    public function arqueo()
    {
        return $this->hasOne(ArqueoCaja::class, 'cierre_id');
    }

    /**
     * Calcula dinámicamente los totales basados en las operaciones y ventas vinculadas
     * Sólo devuelve los totales, no modifica la base de datos (ideal para "ver" una caja abierta)
     */
    public function calcularTotalesDinamicos()
    {
        $movimientos = DB::select("( SELECT
                v.created_at AS fecha_emision,
                'Ingreso - Venta' AS operacion,
                'ingreso' AS tipo_movimiento,
                COALESCE(c.nombre, 'Cliente Contable') AS cliente_nombre,
                CONCAT( v.serie, ' ', v.numero ) AS concepto,
                tp.nombre AS metodo_pago,
                tp.es_efectivo,
                CASE 
                    WHEN d.id IS NULL THEN v.total
                    ELSE (v.total - (d.monto_deuda + COALESCE((SELECT SUM(monto) FROM deuda_pagos WHERE deuda_id = d.id), 0)))
                END AS importe,
                u.name AS usuario,
                v.id_venta AS id_movimiento,
                'venta' AS origen_movimiento
                FROM
                    ventas v
                    LEFT JOIN clientes c ON c.id = v.id_cliente
                    INNER JOIN users u ON u.id = v.id_usuario 
                    LEFT JOIN tipos_pagos tp ON tp.id = v.id_tipo_pago
                    LEFT JOIN deudas d ON d.venta_id = v.id_venta
                WHERE
                    v.cierre_caja_id = :cierre_id AND v.estado != 0 
                ) UNION
                (
                SELECT
                    o.created_at AS fecha_emision,
                    o.partida AS operacion,
                    o.tipo AS tipo_movimiento,
                    o.tipo AS cliente_nombre,
                    o.concepto,
                    o.metodo_pago,
                    o.es_efectivo,
                    o.importe,
                    u.name AS usuario,
                    o.id AS id_movimiento,
                    'operacion' AS origen_movimiento
                FROM
                    operaciones_caja o
                INNER JOIN users u ON u.id = o.user_id 
                where o.cierre_caja_id = :cierre_id_2
                ) ORDER BY fecha_emision DESC", ['cierre_id' => $this->id, 'cierre_id_2' => $this->id]);

        $ingresosTotal = 0;
        $egresosTotal = 0;
        $aportacionesTotal = 0;
        $sustraccionesTotal = 0;

        $ingresosEfectivo = 0;
        $egresosEfectivo = 0;
        $aportacionesEfectivo = 0;
        $sustraccionesEfectivo = 0;

        $ingresosPorMetodo = [];

        foreach ($movimientos as $mov) {
            $importe = floatval($mov->importe);
            $esEfectivo = (bool) ($mov->es_efectivo ?? false);
            $tipoMov = strtolower($mov->tipo_movimiento ?? '');
            $metodoPago = $mov->metodo_pago ?? 'Sin método';

            // Agrupar ingresos no efectivo
            if ($tipoMov === 'ingreso' && !$esEfectivo) {
                if (!isset($ingresosPorMetodo[$metodoPago])) {
                    $ingresosPorMetodo[$metodoPago] = 0;
                }
                $ingresosPorMetodo[$metodoPago] += $importe;
            }

            switch ($tipoMov) {
                case 'ingreso':
                    $ingresosTotal += $importe;
                    if ($esEfectivo) $ingresosEfectivo += $importe;
                    break;
                case 'gasto':
                    $egresosTotal += $importe;
                    if ($esEfectivo) $egresosEfectivo += $importe;
                    break;
                case 'aportacion':
                case 'aporte':
                    $aportacionesTotal += $importe;
                    if ($esEfectivo) $aportacionesEfectivo += $importe;
                    break;
                case 'sustraccion':
                case 'retiro':
                    $sustraccionesTotal += $importe;
                    if ($esEfectivo) $sustraccionesEfectivo += $importe;
                    break;
            }
        }

        return [
            'ingresos_efectivo' => $ingresosEfectivo,
            'egresos_efectivo' => $egresosEfectivo,
            'aportaciones_efectivo' => $aportacionesEfectivo,
            'sustracciones_efectivo' => $sustraccionesEfectivo,
            'ingresos_total' => $ingresosTotal,
            'egresos_total' => $egresosTotal,
            'ingresos_por_metodo' => $ingresosPorMetodo,
            'movimientos' => $movimientos
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deuda extends Model
{
    use HasFactory;

    protected $table = 'deudas';

    protected $fillable = [
        'cliente_id',
        'venta_id',
        'numero_comprobante',
        'tipo_documento',
        'monto_total',
        'monto_pagado',
        'monto_deuda',
        'fecha_venta',
        'fecha_vencimiento',
        'estado',
        'observaciones',
        'user_id',
        'sucursal_id'
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto_deuda' => 'decimal:2',
    ];

    // Estados de la deuda
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PARCIAL = 'parcial';
    const ESTADO_PAGADA = 'pagada';
    const ESTADO_VENCIDA = 'vencida';

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function pagos()
    {
        return $this->hasMany(DeudaPago::class)->orderBy('fecha_pago', 'desc');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_PARCIAL]);
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', self::ESTADO_VENCIDA)
            ->orWhere('fecha_vencimiento', '<', now());
    }

    // Métodos auxiliares
    public function marcarComoPagada()
    {
        $this->update([
            'monto_pagado' => $this->monto_total, // Marcar el total como pagado
            'monto_deuda' => 0,
            'estado' => self::ESTADO_PAGADA
        ]);
    }

    public function aplicarPago($montoPago, $observaciones = null)
    {
        $nuevoMontoPagado = $this->monto_pagado + $montoPago;
        $nuevaDeuda = $this->monto_total - $nuevoMontoPagado;

        $estado = $nuevaDeuda <= 0 ? self::ESTADO_PAGADA : self::ESTADO_PARCIAL;

        $this->update([
            'monto_pagado' => $nuevoMontoPagado,
            'monto_deuda' => max(0, $nuevaDeuda),
            'estado' => $estado,
            'observaciones' => $observaciones
        ]);

        return $this;
    }

    public function getDiasVencidosAttribute()
    {
        if (!$this->fecha_vencimiento || $this->estado === self::ESTADO_PAGADA) {
            return 0;
        }

        return max(0, now()->diffInDays($this->fecha_vencimiento, false));
    }
}

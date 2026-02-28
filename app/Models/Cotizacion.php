<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToSucursal;

class Cotizacion extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    
    
    protected $table = 'cotizaciones';

    protected $fillable = [
        'company_id',
        'sucursal_id',
        'cliente_id',
        'usuario_id',
        'numero',
        'fecha',
        'vigencia',
        'subtotal',
        'descuento_total',
        'igv',
        'total',
        'observaciones',
        'estado'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'vigencia' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    /**
     * Relación con la empresa
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relación con el cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con el usuario que creó la cotización
     */
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los detalles de la cotización
     */
    public function detalles()
    {
        return $this->hasMany(CotizacionDetalle::class);
    }

    /**
     * Relación con las ventas generadas
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_coti');
    }

    /**
     * Scope para obtener cotizaciones de una empresa específica
     */
    public function scopeEmpresa($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope para obtener cotizaciones por estado
     */
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para obtener cotizaciones vigentes
     */
    public function scopeVigentes($query)
    {
        return $query->where('vigencia', '>=', now())
            ->where('estado', 'pendiente');
    }

    /**
     * Scope para obtener cotizaciones vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('vigencia', '<', now())
            ->where('estado', 'pendiente');
    }

    /**
     * Verificar si la cotización está vigente
     */
    public function getEstaVigenteAttribute()
    {
        return $this->vigencia >= now() && $this->estado === 'pendiente';
    }

    /**
     * Verificar si la cotización está vencida
     */
    public function getEstaVencidaAttribute()
    {
        return $this->vigencia < now() && $this->estado === 'pendiente';
    }

    /**
     * Obtener el color del estado para la interfaz
     */
    public function getColorEstadoAttribute()
    {
        return match ($this->estado) {
            'pendiente' => $this->esta_vencida ? 'danger' : 'warning',
            'aprobada' => 'success',
            'rechazada' => 'danger',
            'vencida' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Obtener el texto del estado
     */
    public function getTextoEstadoAttribute()
    {
        return match ($this->estado) {
            'pendiente' => $this->esta_vencida ? 'Vencida' : 'Pendiente',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
            'vencida' => 'Vencida',
            default => 'Sin estado'
        };
    }

    /**
     * Boot del modelo para eventos
     */
    protected static function boot()
    {
        parent::boot();

        // Actualizar cotizaciones vencidas automáticamente
        static::retrieved(function ($cotizacion) {
            if ($cotizacion->esta_vencida && $cotizacion->estado === 'pendiente') {
                $cotizacion->update(['estado' => 'vencida']);
            }
        });
    }
}

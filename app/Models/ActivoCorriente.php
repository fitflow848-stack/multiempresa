<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use App\Traits\BelongsToSucursal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivoCorriente extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    
    protected $fillable = [
        'company_id',
        'sucursal_id',
        'tipo_activo_corriente_id',
        'nombre',
        'monto',
        'fecha_registro',
        'documento',
        'observaciones',
        'user_id',
        'proveedor_id',
        'cierre_caja_id',
        'id_operacion_caja',
        'is_settled',
        'tipo_adelanto',
        'metodo_pago'
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'monto' => 'decimal:2',
        'is_settled' => 'boolean'
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoActivoCorriente::class, 'tipo_activo_corriente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cierreCaja()
    {
        return $this->belongsTo(CierreCaja::class);
    }

    public function operacionCaja()
    {
        return $this->belongsTo(OperacionCaja::class, 'id_operacion_caja');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(\App\Models\Proveedor::class);
    }
}

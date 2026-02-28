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
        'observaciones'
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'monto' => 'decimal:2'
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoActivoCorriente::class, 'tipo_activo_corriente_id');
    }
}

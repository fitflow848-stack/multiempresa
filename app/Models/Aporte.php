<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Aporte extends Model
{
    use HasFactory, BelongsToCompany;
    
    protected $fillable = [
        'company_id',
        'tipo_aporte_id',
        'nombre',
        'monto',
        'fecha_registro',
        'documento',
        'observaciones',
        'user_id'
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'monto' => 'decimal:2'
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoAporte::class, 'tipo_aporte_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si este aporte tiene una operación de caja asociada
     */
    public function operacionCaja()
    {
        return $this->hasOne(OperacionCaja::class, 'concepto', 'nombre')
            ->where('tipo', 'ingreso')
            ->where('partida', 'like', 'Aporte - %');
    }

    /**
     * Obtener operación de caja por importe y fecha similar
     */
    public function getOperacionCaja()
    {
        return OperacionCaja::where('tipo', 'ingreso')
            ->where('importe', $this->monto)
            ->whereDate('created_at', $this->fecha_registro)
            ->where('partida', 'like', '%' . $this->nombre . '%')
            ->first();
    }
}

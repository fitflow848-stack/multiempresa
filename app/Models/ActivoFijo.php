<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivoFijo extends Model
{
    use HasFactory;

    protected $table = 'activos_fijos';

    protected $fillable = [
        'tipo_activo_id',
        'nombre',
        'monto',
        'fecha_adquisicion',
        'documento',
        'observaciones'
    ];

    protected $casts = [
        'fecha_adquisicion' => 'date',
        'monto' => 'decimal:2'
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoActivo::class, 'tipo_activo_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlmacenIngreso extends Model
{
    protected $table = 'almacen_ingresos';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'fecha',
        'observacion'
    ];

    public function detalles()
    {
        return $this->hasMany(AlmacenIngresoDetalle::class, 'ingreso_id');
    }
}

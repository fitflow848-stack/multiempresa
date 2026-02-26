<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Model;

class AlmacenIngreso extends Model
{
    use HasFactory, BelongsToCompany;
    protected $table = 'almacen_ingresos';

    protected $fillable = [
        'company_id',
        'empresa_id',
        'user_id',
        'fecha',
        'observacion'
    ];

    public function detalles()
    {
        return $this->hasMany(AlmacenIngresoDetalle::class, 'ingreso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

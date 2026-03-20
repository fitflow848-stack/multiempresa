<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSucursal;

class AlmacenIngreso extends Model
{
    protected $companyForeignKey = 'empresa_id';
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    
    protected $table = 'almacen_ingresos';

    protected $fillable = [
        'company_id',
        'empresa_id',
        'sucursal_id',
        'user_id',
        'compra_id',
        'fecha',
        'observacion'
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function detalles()
    {
        return $this->hasMany(AlmacenIngresoDetalle::class, 'ingreso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}

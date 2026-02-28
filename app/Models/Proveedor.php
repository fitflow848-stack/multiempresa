<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToSucursal;

class Proveedor extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    

    protected $companyForeignKey = 'id_empresa';
    protected $sucursalForeignKey = 'sucursal_id';
    
    protected $table = 'proveedores';

    protected $fillable = [
        'id_empresa',
        'sucursal_id',
        'ruc',
        'nombre_comercial',
        'nombre_legal',
        'direccion',
        'localidad',
        'codigo_postal',
        'ubigeo',
        'email',
        'telefono',
    ];
}
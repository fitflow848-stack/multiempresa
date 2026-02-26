<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory, BelongsToCompany;

    protected $companyForeignKey = 'id_empresa';
    
    protected $table = 'proveedores';

    protected $fillable = [
        'id_empresa',
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
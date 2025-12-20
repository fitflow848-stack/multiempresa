<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

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
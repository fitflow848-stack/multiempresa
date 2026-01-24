<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaRemisionProducto extends Model
{
    use HasFactory;
    protected $table = 'guia_remision_producto';

    protected $fillable = [
        'id_guia',
        'cod_sap',
        'tipo',
        'descripcion',
        'serie',
        'cantidad',
        'unidad_medida',
        'peso',
    ];
}

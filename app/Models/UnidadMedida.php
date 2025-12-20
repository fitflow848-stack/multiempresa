<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidades_medida';

    protected $fillable = [
        'codigo',   // p.ej. NIU, KG, LT
        'nombre',   // p.ej. "NIU - UNIDAD (BIENES)"
    ];
}
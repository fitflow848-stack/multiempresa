<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory, BelongsToCompany;
    
    protected $table = 'unidades_medida';

    protected $fillable = [
        'company_id',
        'codigo',   // p.ej. NIU, KG, LT
        'nombre',   // p.ej. "NIU - UNIDAD (BIENES)"
    ];
}
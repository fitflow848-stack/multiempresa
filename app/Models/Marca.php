<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory, BelongsToCompany;
    
    protected $table = 'marcas';

    protected $fillable = [
        'company_id',
        'nombre',
        // agrega otros campos si los necesitas (p. ej. slug, descripcion)
    ];
}